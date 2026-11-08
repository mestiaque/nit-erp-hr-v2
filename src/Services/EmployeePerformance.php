<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Hr\Models\HrEmployee;

/**
 * Overview + 5-star rating shown on the employee view's Profile tab.
 *
 * Measured over the last 6 months (or since joining / the first punch, if later), from data already
 * recorded - nothing is entered by hand:
 *   Attendance  60%  present days / (present + absent) working days
 *   Punctuality 15%  share of present days without Late / Early Exit
 *   Discipline  15%  disciplinary notices: none = full, each one takes half off
 *   Punching    10%  share of present days without Punch Missing
 * Approved leave and weekends/holidays are neutral (never counted against the employee).
 */
class EmployeePerformance
{
    public const MONTHS = 6;

    public static function for(HrEmployee $employee): array
    {
        $to = now()->startOfDay();
        $from = $to->copy()->subMonthsNoOverflow(self::MONTHS)->addDay();
        if (! blank($employee->join_date)) {
            $from = $from->max(Carbon::parse($employee->join_date)->startOfDay());
        }
        // Not before the first punch either: days before attendance was recorded for this
        // employee (machine not set up yet, data not imported) would all count as absent.
        $firstPunch = DB::table('hr_attendances')->where('employee_id', $employee->id)
            ->where(fn ($q) => $q->whereNotNull('in_time')->orWhereNotNull('out_time'))->min('date');
        if ($firstPunch) {
            $from = $from->max(Carbon::parse($firstPunch)->startOfDay());
        }

        // Same day-by-day attendance the Attendance tab and salary sheet use: an absent day is
        // usually not stored as a row, it is a working day with no punch. Read month by month
        // for the dashboard's trend charts; the totals are the sum of the months.
        $monthly = [];
        for ($m = $from->copy()->startOfMonth(); $m->lte($to); $m->addMonth()) {
            $mFrom = $m->copy()->max($from);
            $mTo = $m->copy()->endOfMonth()->startOfDay()->min($to);
            try {
                $ms = EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $mFrom->toDateString(), $mTo->toDateString())['summary'] ?? [];
            } catch (\Throwable $e) {
                report($e);
                $ms = [];
            }
            $monthly[] = [
                'month' => $m->format('M y'),
                'present' => (int) ($ms['totalAttendance'] ?? 0),
                'absent' => (int) ($ms['totalAbsent'] ?? 0),
                'late_or_early' => (int) ($ms['totalLate'] ?? 0) + (int) ($ms['totalEO'] ?? 0) + (int) ($ms['totalLEO'] ?? 0),
                'punch_missing' => (int) ($ms['totalPM'] ?? 0) + (int) ($ms['totalLPM'] ?? 0),
                'leave' => (float) ($ms['totalLeave'] ?? 0),
                'ot_hours' => round((float) ($ms['totalOt'] ?? 0), 1),
            ];
        }
        $total = fn (string $k) => collect($monthly)->sum($k);

        $present = (int) $total('present');
        $absent = (int) $total('absent');
        $lateOrEarly = (int) $total('late_or_early');
        $punchMissing = (int) $total('punch_missing');
        $otHours = round((float) $total('ot_hours'), 1);
        $leaveDays = (float) $total('leave');

        $notices = Schema::hasTable('hr_disciplinary_notices')
            ? DB::table('hr_disciplinary_notices')->where('employee_id', $employee->id)
                ->whereBetween('notice_date', [$from->toDateString(), $to->toDateString()])->count()
            : 0;

        $worked = $present + $absent;
        $hasData = $worked > 0;

        $parts = [
            'Attendance' => ['weight' => 60, 'score' => $hasData ? $present / $worked : null,
                'detail' => "{$present} present / {$absent} absent"],
            'Punctuality' => ['weight' => 15, 'score' => $present > 0 ? 1 - $lateOrEarly / $present : null,
                'detail' => "{$lateOrEarly} late / early exit"],
            'Discipline' => ['weight' => 15, 'score' => $hasData ? max(0, 1 - 0.5 * $notices) : null,
                'detail' => $notices ? "{$notices} disciplinary notice(s)" : 'No disciplinary notice'],
            'Punching' => ['weight' => 10, 'score' => $present > 0 ? 1 - $punchMissing / $present : null,
                'detail' => "{$punchMissing} punch missing"],
        ];

        $rating = $hasData
            ? round(collect($parts)->sum(fn ($p) => $p['weight'] * $p['score']) / 100 * 5, 1)
            : null;

        return [
            'from' => $from,
            'to' => $to,
            'has_data' => $hasData,
            'rating' => $rating,
            'label' => self::label($rating),
            'parts' => $parts,
            'present' => $present,
            'absent' => $absent,
            'attendance_pct' => $hasData ? round($present / $worked * 100) : null,
            'late_or_early' => $lateOrEarly,
            'punch_missing' => $punchMissing,
            'leave_days' => $leaveDays,
            'ot_hours' => $otHours,
            'notices' => $notices,
            'monthly' => $monthly,
        ];
    }

    /** [text, bootstrap colour] for a rating. */
    public static function label(?float $rating): array
    {
        return match (true) {
            $rating === null => ['Not enough data', 'secondary'],
            $rating >= 4.5 => ['Excellent', 'success'],
            $rating >= 3.5 => ['Good', 'primary'],
            $rating >= 2.5 => ['Average', 'warning'],
            default => ['Needs Improvement', 'danger'],
        };
    }
}
