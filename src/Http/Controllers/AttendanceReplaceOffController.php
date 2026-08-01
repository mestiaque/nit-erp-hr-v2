<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrAttendanceReplaceOff;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrLock;
use ME\Hr\Services\EmployeeAttendanceService;

/**
 * "Day Swap" (a.k.a. Replace Off) — factory-wide compensatory day-off swap.
 *
 * The office stays open on worked_date (normally a weekend/holiday) and everyone
 * takes their compensatory day off on replace_date instead. Applying a swap:
 *   1. Moves every employee's hr_attendances row for worked_date onto replace_date,
 *      recalculating OT against each employee's own shift as resolved for
 *      replace_date — the moved day is treated exactly like a regular working day.
 *   2. Leaves worked_date permanently blocked (see the active-swap check added to
 *      AttendanceMachineController::applyPunchToAttendance()) so a later machine
 *      sync can never re-introduce attendance/salary impact on that date.
 *
 * This intentionally does NOT auto-reverse an already-applied move on cancel — by
 * the time someone cancels a swap, replace_date's attendance may already have been
 * viewed/edited/paid against, so blindly moving it back could silently corrupt
 * unrelated changes. Cancelling only lifts the worked_date block for future syncs.
 */
class AttendanceReplaceOffController extends Controller
{
    public function index(Request $request)
    {
        $query = HrAttendanceReplaceOff::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('worked_date')) {
            $query->where('worked_date', $request->worked_date);
        }

        $items = $query->orderByDesc('id')->paginate(20)->appends($request->query());

        return view('hr::attendance-replace-off.index', compact('items', 'request'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'worked_date' => 'required|date',
            'replace_date' => 'required|date|different:worked_date',
            'remarks' => 'nullable|string',
        ]);

        $workedDate = $data['worked_date'];
        $replaceDate = $data['replace_date'];

        if (HrAttendanceReplaceOff::active()->where('worked_date', $workedDate)->exists()) {
            return back()->withErrors(['worked_date' => 'This date already has an active day swap.'])->withInput();
        }

        $movedCount = 0;
        $skippedLocked = 0;
        $skippedConflict = 0;

        DB::transaction(function () use ($workedDate, $replaceDate, $data, &$movedCount, &$skippedLocked, &$skippedConflict) {
            // Locks the rows for the remainder of this transaction so a concurrent
            // machine-sync punch can't land on worked_date in the gap between reading
            // and moving/blocking it.
            $attendances = HrAttendance::where('date', $workedDate)->lockForUpdate()->get();

            $workedDay = Carbon::parse($workedDate);

            foreach ($attendances as $attendance) {
                // A locked attendance row (or a period-level lock covering its month)
                // is immutable everywhere else in this system — the swap must respect
                // that too, rather than silently moving payroll-locked data.
                $employee = HrEmployee::with('shift', 'shiftRule.primaryShift', 'shiftRule.alternateShifts.shift')
                    ->find($attendance->employee_id);

                if (!$employee) {
                    continue;
                }

                if ($attendance->is_locked
                    || HrLock::isLocked('attendance', $workedDay->year, $workedDay->month, $employee->department_id)
                ) {
                    $skippedLocked++;
                    continue;
                }

                // Don't clobber a pre-existing attendance row on replace_date (e.g. a
                // stray punch already there) — skip it for HR to resolve manually;
                // every other employee's move still proceeds.
                $conflict = HrAttendance::where('employee_id', $attendance->employee_id)
                    ->where('date', $replaceDate)
                    ->exists();
                if ($conflict) {
                    $skippedConflict++;
                    continue;
                }

                $shift = $employee->resolveShiftForDate($replaceDate);

                $attendance->date = $replaceDate;

                if ($attendance->in_time && $attendance->out_time) {
                    $inTime  = Carbon::parse($replaceDate . ' ' . $attendance->in_time, 'Asia/Dhaka');
                    $outRaw  = Carbon::parse($replaceDate . ' ' . $attendance->out_time, 'Asia/Dhaka');
                    // out_time is stored as a time-of-day only; if it looks earlier than
                    // in_time it's really an overnight punch on the following day.
                    $outTime = $outRaw->lt($inTime) ? $outRaw->copy()->addDay() : $outRaw;

                    $attendance->total_working_minute = (int) $inTime->diffInMinutes($outTime);
                    // Single source of truth for OT (see its own doc-comment) — computed
                    // fresh against replace_date's own shift, exactly like a regular day.
                    $attendance->total_ot_minute = EmployeeAttendanceService::calculateOvertimeMinutes(
                        $shift,
                        $replaceDate,
                        $attendance->in_time,
                        $attendance->out_time,
                        $employee
                    );
                }

                $attendance->save();
                $movedCount++;
            }

            HrAttendanceReplaceOff::create([
                'worked_date' => $workedDate,
                'replace_date' => $replaceDate,
                'status' => 'active',
                'moved_attendance_count' => $movedCount,
                'remarks' => $data['remarks'] ?? null,
            ]);
        });

        $message = "Day swap applied: {$movedCount} attendance record(s) moved from {$workedDate} to {$replaceDate}. "
            . "{$workedDate} is now blocked from any further attendance sync.";
        if ($skippedLocked > 0) {
            $message .= " {$skippedLocked} record(s) were skipped because they're locked.";
        }
        if ($skippedConflict > 0) {
            $message .= " {$skippedConflict} record(s) were skipped because the employee already has attendance on {$replaceDate} — resolve those manually.";
        }

        return back()->with('success', $message);
    }

    /**
     * Lifts the worked_date block only — does NOT move already-relocated attendance
     * back. See the class doc-comment for why.
     */
    public function cancel($id)
    {
        $item = HrAttendanceReplaceOff::findOrFail($id);
        $item->update(['status' => 'cancelled']);

        return back()->with('success', "Swap cancelled — {$item->worked_date->toDateString()} can accept attendance again. Already-moved attendance on {$item->replace_date->toDateString()} was left as-is.");
    }
}
