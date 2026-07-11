<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrLock;
use ME\Hr\Models\HrShift;

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

        $employees = HrEmployee::query();
        if ($employee) {
            $employees->where(function($q) use ($employee) {
                $q->where('name', 'like', "%$employee%")
                  ->orWhere('employee_id', 'like', "%$employee%")
                  ->orWhere('personal_contact', 'like', "%$employee%")
                  ;
            });
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
            foreach ($dates as $date) {
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

        // Update Punch Missing/Absent status if needed
        foreach ($attendanceList as &$row) {
            if (!$row['attendance']) {
                $row['status'] = 'Absent';
            } elseif (!$row['attendance']->in_time || !$row['attendance']->out_time) {
                $row['status'] = 'Punch Missing';
            }
        }
        unset($row);

        // Apply the status filter here, against the derived status, now that every
        // row's real Present/Late/Absent/Punch Missing value is known.
        if ($status) {
            $attendanceList = array_values(array_filter(
                $attendanceList,
                fn ($row) => $row['status'] === $status
            ));
        }

        // For backward compatibility with view, we might still pass a single date variable (first date) but we'll change view to use row date.
        // We'll keep the variable $date as the first date or today.
        $date = $dates[0] ?? Carbon::today()->toDateString();

        return view('hr::attendances.index', compact('attendanceList', 'date', 'status', 'employee', 'dateFrom', 'dateTo'));
    }

    public function edit($userId, $date)
    {
        $attendance = HrAttendance::where('employee_id', $userId)->where('date', $date)->first();
        $employee = HrEmployee::query()->findOrFail($userId);
        $shift = HrShift::find($employee->shift_id);
        return view('hr::attendances.edit', compact('attendance', 'employee', 'shift', 'date'));
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
        foreach (['employee', 'status', 'date_from', 'date_to'] as $param) {
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

        foreach ($rows as $row) {
            $existing = HrAttendance::where('employee_id', $employeeId)->where('date', $row['date'])->first();
            if ($this->isAttendanceLocked($employee, $row['date'], $existing)) {
                $skippedLocked++;
                continue; // locked date — skip it, still save the rest of the batch
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
        foreach (['employee', 'status', 'date_from', 'date_to'] as $param) {
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
            if ($shift) {
                $inMissing = !$attendance->in_time;
                $outMissing = !$attendance->out_time;
                $late = false;
                if (!$inMissing) {
                    $in = Carbon::parse($attendance->in_time);
                    $lateAllow = $shift->late_allow_time ? Carbon::parse($shift->late_allow_time) : Carbon::parse($shift->start_time);
                    $late = $in->gt($lateAllow);
                }
                if ($late && ($inMissing || $outMissing)) {
                    return 'Late and Punch Missing';
                }
                return 'Punch Missing';
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
