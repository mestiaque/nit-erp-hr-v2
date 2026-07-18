<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrLock;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrSubSection;
use ME\Hr\Models\HrClassification;
use ME\Hr\Models\HrDesignation;

use Illuminate\Routing\Controller;
use function view;
use function redirect;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $employee = $request->input('employee');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $options = \ME\Hr\Services\HrOptionsService::getOptions();

        $employees = HrEmployee::query();
        if ($employee) {
            $employees->where(function($q) use ($employee) {
                $q->where('name', 'like', "%$employee%")
                  ->orWhere('employee_id', 'like', "%$employee%")
                  ->orWhere('personal_contact', 'like', "%$employee%")
                  ;
            });
        }

        if ($request->filled('department')) {
            $values = array_filter(array_map('intval', (array) $request->department));
            if (!empty($values)) {
                $employees->whereIn('department_id', $values);
            }
        }

        if ($request->filled('section')) {
            $values = array_filter(array_map('intval', (array) $request->section));
            if (!empty($values)) {
                $employees->whereIn('section_id', $values);
            }
        }

        if ($request->filled('sub_section')) {
            $values = array_filter(array_map('intval', (array) $request->sub_section));
            if (!empty($values)) {
                $employees->whereIn('sub_section_id', $values);
            }
        }

        if ($request->filled('classification')) {
            $values = array_filter(array_map('intval', (array) $request->classification));
            if (!empty($values)) {
                $employees->whereIn('classification_id', $values);
            }
        }

        if ($request->filled('designation')) {
            $values = array_filter(array_map('intval', (array) $request->designation));
            if (!empty($values)) {
                $employees->whereIn('designation_id', $values);
            }
        }

        $employees = $employees->naturalOrderById()->get();

        // Determine date range
        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom);
            $end = Carbon::parse($dateTo);
            $dates = [];
            for ($date = $start; $date->lte($end); $date->addDay()) {
                $dates[] = $date->copy()->toDateString();
            }
        } else {
            $date = $request->input('date', Carbon::today()->toDateString());
            $dates = [$date];
        }

        // NOTE: intentionally not filtering this query by $status. 'Absent' and
        // 'Punch Missing' are derived below, not stored values — pre-filtering the raw
        // attendance rows by status='Absent' would match zero rows and empty out the
        // whole lookup map, making every employee (including genuinely present ones)
        // show up as absent. The $status filter is applied after derivation instead.
        $attendanceQuery = HrAttendance::query();
        if ($dateFrom && $dateTo) {
            $attendanceQuery->whereBetween('date', [$dateFrom, $dateTo]);
        } else {
            $attendanceQuery->where('date', $dates[0]);
        }
        $attendances = $attendanceQuery->get()->keyBy(function($a) {
            return $a->employee_id . '_' . $a->date;
        });

        $shiftMap = HrShift::all()->keyBy('id');

        $attendanceList = [];
        foreach ($employees as $emp) {
            $exitedAt = $this->resolveExitedAt($emp);
            foreach ($dates as $date) {
                // A resigned/lefty employee has no attendance to show or edit past
                // their exit date — mirrors the cutoff already enforced in
                // EmployeeAttendanceService::getEmployeeAttendanceByDate() for reports.
                if ($exitedAt && Carbon::parse($date)->gt($exitedAt)) {
                    continue;
                }
                $key = $emp->id . '_' . $date;
                $attendance = $attendances[$key] ?? null;
                $shift = $shiftMap[$emp->shift_id ?? null] ?? null;
                $attendanceList[] = [
                    'employee' => $emp,
                    'attendance' => $attendance,
                    'shift' => $shift,
                    'date' => $date,
                    'status' => $attendance->status ?? 'Absent',
                ];
            }
        }

        // Re-derive via calculateStatus() — the same single source of truth used on
        // save — instead of a separate ad-hoc missing-field check. That ad-hoc check
        // flagged "Punch Missing" the moment out_time was blank even for today/future
        // dates, e.g. an employee who punched in this morning and simply hasn't left
        // yet; calculateStatus() already knows to only call it Punch Missing once the
        // date has actually passed.
        foreach ($attendanceList as &$row) {
            if (!$row['attendance']) {
                $row['status'] = 'Absent';
                continue;
            }
            $row['status'] = $this->calculateStatus($row['attendance'], $row['shift']);
        }
        unset($row);

        // Apply the status filter here, against the derived status, now that every
        // row's real Present/Late/Absent/Punch Missing value is known. The dropdown only
        // offers Present/Absent/Late — "Present" is a broad bucket covering any status
        // that shows the employee had at least some punch data (in and/or out time),
        // as opposed to a genuine no-show. This is a display-only filter on this list
        // page; it doesn't touch the stored status value or any report.
        if ($status) {
            $statusGroups = [
                'Present' => ['Present', 'Late', 'Punch Missing', 'Early Exit', 'Late and Early Exit', 'Late and Punch Missing'],
                'Absent'  => ['Absent'],
                'Late'    => ['Late'],
            ];
            $allowedStatuses = $statusGroups[$status] ?? [$status];
            $attendanceList = array_values(array_filter(
                $attendanceList,
                fn ($row) => in_array($row['status'], $allowedStatuses, true)
            ));
        }

        // For backward compatibility with view, we might still pass a single date variable (first date) but we'll change view to use row date.
        // We'll keep the variable $date as the first date or today.
        $date = $dates[0] ?? Carbon::today()->toDateString();

        return view('hr::attendances.index', compact('attendanceList', 'date', 'status', 'employee', 'dateFrom', 'dateTo', 'options'));
    }

    public function edit($userId, $date)
    {
        $attendance = HrAttendance::where('employee_id', $userId)->where('date', $date)->first();
        $employee = HrEmployee::query()->findOrFail($userId);
        $shift = HrShift::find($employee->shift_id);
        return view('hr::attendances.edit', compact('attendance', 'employee', 'shift', 'date'));
    }

    /**
     * Mirrors EmployeeAttendanceService::getEmployeeAttendanceByDate()'s cutoff —
     * a resigned/lefty employee has no attendance past their exit date.
     */
    private function resolveExitedAt(HrEmployee $employee): ?Carbon
    {
        $status = strtolower((string) ($employee->employment_status ?? ''));
        if (in_array($status, ['lefty', 'left', 'resign', 'resigned'], true) && !blank($employee->exited_at)) {
            return Carbon::parse($employee->exited_at)->startOfDay();
        }
        return null;
    }

    /**
     * A row can be individually locked (bulk-locked period touched it), or the
     * whole period can be locked with no row yet existing for this date — check both.
     */
    private function isAttendanceLocked(HrEmployee $employee, string $date, ?HrAttendance $attendance): bool
    {
        if ($attendance && $attendance->is_locked) {
            return true;
        }

        $day = Carbon::parse($date);
        return HrLock::isLocked('attendance', $day->year, $day->month, $employee->department_id);
    }

    public function update(Request $request, $userId, $date)
    {
        $employee = HrEmployee::query()->findOrFail($userId);
        $shift = HrShift::find($employee->shift_id);
        $existing = HrAttendance::where('employee_id', $userId)->where('date', $date)->first();
        if ($this->isAttendanceLocked($employee, $date, $existing)) {
            return back()->with('error', 'This date is locked — attendance cannot be edited until it is unlocked.');
        }
        $exitedAt = $this->resolveExitedAt($employee);
        if ($exitedAt && Carbon::parse($date)->gt($exitedAt)) {
            return back()->with('error', 'This employee exited on ' . $exitedAt->format('d-M-Y') . ' — attendance cannot be edited after that date.');
        }
        $attendance = $existing ?? new HrAttendance(['employee_id' => $userId, 'date' => $date]);
        $attendance->in_time = $request->input('in_time');
        $attendance->out_time = $request->input('out_time');
        $attendance->remarks = $request->input('remarks');
        $attendance->status = $this->calculateStatus($attendance, $shift);
        
        // Calculate overtime minutes: out_time - shift closing time if out_time is after shift end
        if ($attendance->out_time && $shift) {
            $outTime = Carbon::parse($attendance->out_time);
            $shiftEnd = Carbon::parse($shift->end_time);
            if ($outTime->gt($shiftEnd)) {
                $attendance->total_ot_minute = $shiftEnd->diffInMinutes($outTime);
            } else {
                $attendance->total_ot_minute = 0;
            }
        } else {
            $attendance->total_ot_minute = 0;
        }

        // Full worked duration — this is what the WPHP (weekend-to-regular full-day-OT)
        // rule in EmployeeAttendanceService reads; without it that feature silently no-ops
        // for any manually-entered attendance row.
        if ($attendance->in_time && $attendance->out_time) {
            $inTime  = Carbon::parse($attendance->in_time);
            $outTime = Carbon::parse($attendance->out_time);
            if ($outTime->lt($inTime)) {
                $outTime->addDay(); // overnight shift
            }
            $attendance->total_working_minute = (int) $inTime->diffInMinutes($outTime);
        } else {
            $attendance->total_working_minute = 0;
        }

        $attendance->save();

        // Preserve filter parameters after update
        $query = [];
        foreach (['employee', 'status', 'date_from', 'date_to', 'department', 'section', 'sub_section', 'classification', 'designation'] as $param) {
            if ($request->filled($param)) {
                $query[$param] = $request->input($param);
            }
        }
        return redirect()->route('hr-center.attendances.index', $query)->with('success', 'Attendance updated successfully.');
    }

    public function bulkUpdate(Request $request, $employeeId)
    {
        $employee = HrEmployee::findOrFail($employeeId);
        $shift    = HrShift::find($employee->shift_id);
        $rows     = $request->input('rows', []);
        $skippedLocked = 0;
        $exitedAt = $this->resolveExitedAt($employee);

        foreach ($rows as $row) {
            $existing = HrAttendance::where('employee_id', $employeeId)->where('date', $row['date'])->first();
            if ($this->isAttendanceLocked($employee, $row['date'], $existing)) {
                $skippedLocked++;
                continue; // locked date — skip it, still save the rest of the batch
            }
            if ($exitedAt && Carbon::parse($row['date'])->gt($exitedAt)) {
                continue; // employee had already exited by this date — nothing to save
            }
            $attendance = $existing ?? new HrAttendance(['employee_id' => $employeeId, 'date' => $row['date']]);
            $attendance->in_time  = $row['in_time']  ?: null;
            $attendance->out_time = $row['out_time'] ?: null;
            $attendance->remarks  = $row['remarks']  ?? null;
            $attendance->status   = $this->calculateStatus($attendance, $shift);

            if ($attendance->out_time && $shift) {
                $outTime  = Carbon::parse($attendance->out_time);
                $shiftEnd = Carbon::parse($shift->end_time);
                $attendance->total_ot_minute = $outTime->gt($shiftEnd) ? $shiftEnd->diffInMinutes($outTime) : 0;
            } else {
                $attendance->total_ot_minute = 0;
            }

            if ($attendance->in_time && $attendance->out_time) {
                $inTime  = Carbon::parse($attendance->in_time);
                $outTime = Carbon::parse($attendance->out_time);
                if ($outTime->lt($inTime)) {
                    $outTime->addDay();
                }
                $attendance->total_working_minute = (int) $inTime->diffInMinutes($outTime);
            } else {
                $attendance->total_working_minute = 0;
            }

            $attendance->save();
        }

        $query = [];
        foreach (['employee', 'status', 'date_from', 'date_to', 'department', 'section', 'sub_section', 'classification', 'designation'] as $param) {
            if ($request->filled($param)) {
                $query[$param] = $request->input($param);
            }
        }

        $message = 'Attendance saved for ' . $employee->name . '.';
        if ($skippedLocked > 0) {
            $message .= " ({$skippedLocked} locked date(s) were skipped.)";
        }
        return redirect()->route('hr-center.attendances.index', $query)->with('success', $message);
    }

    private function calculateStatus($attendance, $shift)
    {
        // If either in_time or out_time is missing
        if (!$attendance->in_time || !$attendance->out_time) {
            $inMissing  = !$attendance->in_time;
            $outMissing = !$attendance->out_time;

            // Today (or a future date) isn't over yet — a missing out_time just means the
            // employee hasn't left yet, same as a fresh machine punch. Only call it a genuine
            // "Punch Missing" once the date has actually passed.
            $isPastDate = $attendance->date && Carbon::parse($attendance->date)->lt(Carbon::today());

            if ($shift) {
                $late = false;
                if (!$inMissing) {
                    $in = Carbon::parse($attendance->in_time);
                    $lateAllow = $shift->late_allow_time ? Carbon::parse($shift->late_allow_time) : Carbon::parse($shift->start_time);
                    $late = $in->gt($lateAllow);
                }

                if (!$isPastDate && !$inMissing && $outMissing) {
                    return $late ? 'Late' : 'Present';
                }

                if ($late && ($inMissing || $outMissing)) {
                    return 'Late and Punch Missing';
                }
                return 'Punch Missing';
            }

            if (!$isPastDate && !$inMissing && $outMissing) {
                return 'Present';
            }
            return 'Punch Missing';
        }

        if (!$shift) {
            return 'Present';
        }

        $in = Carbon::parse($attendance->in_time);
        $out = Carbon::parse($attendance->out_time);
    $shiftStart = Carbon::parse($shift->start_time);
    $shiftEnd = Carbon::parse($shift->end_time);
    $lateAllow = $shift->late_allow_time ? Carbon::parse($shift->late_allow_time) : $shiftStart;

        $isLate = $in->gt($lateAllow);
        $isEarlyExit = $out->lt($shiftEnd);

        if ($isLate && $isEarlyExit) {
            return 'Late and Early Exit';
        } elseif ($isLate) {
            return 'Late';
        } elseif ($isEarlyExit) {
            return 'Early Exit';
        }
        return 'Present';
    }
}


//late, early exit, late and punch missing, late and early exit, punch missing
//add thos status in attendance status calculation logic if needed, must be calculate with shift
