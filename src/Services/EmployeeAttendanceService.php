<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use ME\Hr\Models\HrEmployee as User;
use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrRegularToWeekend as RegularToWeekend;

class EmployeeAttendanceService
{
    /**
     * OT only starts counting once the employee has worked past shift end by AT LEAST
     * the configured grace period ("OT Count After Shift End (min)") — it's a minimum
     * threshold, not a per-day deduction. E.g. a 40-minute grace means leaving 39
     * minutes late shows 0 OT, but leaving 40 (or 50, or 60+) minutes late shows the
     * FULL 40 (or 50, or 60+) minutes worked — the grace amount itself is never
     * subtracted off once the threshold is met. The grace period is Designation ->
     * Factory: a designation value > 0 wins, otherwise the factory's value applies.
     * Single source of truth for this rule — every OT-writing/reading site in the
     * system should call this rather than reimplementing the threshold check.
     */
    public static function calculateOvertimeMinutes(?\ME\Hr\Models\HrShift $shift, string $dateStr, ?string $inTime, ?string $outTime, $employee = null): int
    {
        if (!$shift || !$shift->end_time || !$outTime) {
            return 0;
        }

        $shiftEnd = Carbon::parse($dateStr . ' ' . $shift->end_time, 'Asia/Dhaka');
        $out      = Carbon::parse($dateStr . ' ' . $outTime, 'Asia/Dhaka');

        if ($out->lte($shiftEnd)) {
            return 0;
        }

        // Cap OT at out_time_start if defined — but only when it's actually a sensible
        // cap (after shift end). A misconfigured shift with out_time_start earlier than
        // end_time would otherwise clamp $effectiveOut backward past $shiftEnd, turning
        // a positive OT span into a large negative one.
        $otCap = ($shift->out_time_start && Carbon::parse($dateStr . ' ' . $shift->out_time_start, 'Asia/Dhaka')->gt($shiftEnd))
            ? Carbon::parse($dateStr . ' ' . $shift->out_time_start, 'Asia/Dhaka')
            : $out;
        $effectiveOut = $out->lt($otCap) ? $out : $otCap;

        $graceMinutes        = self::resolveOtGraceMinutes($employee);
        $minutesPastShiftEnd = (int) $shiftEnd->diffInMinutes($effectiveOut);
        // Threshold, not a deduction: below the grace period counts as 0 OT; at or
        // above it, the full minutes worked past shift end count (grace isn't shaved off).
        $minutesPastGrace    = $minutesPastShiftEnd >= $graceMinutes ? $minutesPastShiftEnd : 0;

        return self::applyMinimumOtBucketing($minutesPastGrace);
    }

    /**
     * OT grace period ("OT Count After Shift End (min)") — Designation overrides Factory
     * whenever the designation's value is set and > 0; otherwise the factory's value is
     * used (including when the designation's value is 0/null).
     */
    public static function resolveOtGraceMinutes($employeeOrDesignation = null): int
    {
        $designation = null;
        if ($employeeOrDesignation instanceof \ME\Hr\Models\HrDesignation) {
            $designation = $employeeOrDesignation;
        } elseif ($employeeOrDesignation instanceof User) {
            $designation = $employeeOrDesignation->designation
                ?? ($employeeOrDesignation->designation_id ? \ME\Hr\Models\HrDesignation::find($employeeOrDesignation->designation_id) : null);
        }

        $designationGrace = data_get($designation, 'ot_grace_minutes');
        if (is_numeric($designationGrace) && (float) $designationGrace > 0) {
            return (int) $designationGrace;
        }

        return (int) (hr_factory('ot_grace_minutes') ?? 0);
    }

    /**
     * Factory's minimum_ot_minutes buckets OT into whole-hour blocks past the grace
     * cutoff: each 60-minute block only counts (as a full 60 minutes) if at least
     * minimum_ot_minutes of it was actually worked, otherwise that partial block counts
     * as 0. A 0/null minimum_ot_minutes (the default for every factory except ANR)
     * disables bucketing entirely and the raw minute count passes through unchanged.
     */
    public static function applyMinimumOtBucketing(int $minutesPastGrace): int
    {
        $minimumOtMinutes = (int) (hr_factory('minimum_ot_minutes') ?? 0);
        if ($minimumOtMinutes <= 0 || $minutesPastGrace <= 0) {
            return max(0, $minutesPastGrace);
        }

        $fullHours        = intdiv($minutesPastGrace, 60);
        $remainder        = $minutesPastGrace % 60;
        $countedRemainder = $remainder >= $minimumOtMinutes ? 60 : 0;

        return $fullHours * 60 + $countedRemainder;
    }

    private static function normalizeLeaveType($leave): string
    {
        $code = strtolower((string) data_get($leave, 'leaveType.code', ''));
        $name = strtolower((string) data_get($leave, 'leaveType.name', ''));
        $type = trim($code . ' ' . $name);

        if (str_contains($type, 'casual') || preg_match('/\bcl\b/', $type)) {
            return 'casual';
        }
        if (str_contains($type, 'sick') || preg_match('/\bsl\b/', $type)) {
            return 'sick';
        }
        if (str_contains($type, 'earned') || preg_match('/\bel\b/', $type)) {
            return 'earned';
        }
        if (str_contains($type, 'weekly') || str_contains($type, 'weekend') || preg_match('/\bwo\b/', $type)) {
            return 'weekly';
        }
        if (str_contains($type, 'festival') || str_contains($type, 'fest')) {
            return 'festival';
        }
        if (str_contains($type, 'maternity') || preg_match('/\bml\b/', $type)) {
            return 'maternity';
        }

        return 'general';
    }

    public static function calculateWeekendToRegularAllowance(User $employee, array $context = []): array
    {
        $designation = $employee->designation;
        if (!$designation && $employee->designation_id) {
            $designation = \ME\Hr\Models\HrDesignation::find($employee->designation_id);
        }

        // Compliance mode (Factory No 1/2) prices weekend/holiday work off the
        // designation's "(Comp)" fields instead of the "(Main)" ones used for Actual
        // (Factory No 0) — same policy options, different configured value per mode.
        $factoryNo = (int) (hr_factory('factory_no') ?? 0);
        $isCompliance = in_array($factoryNo, [1, 2], true);

        $policyField = $isCompliance ? 'weekend_allowance_count_comp' : 'weekend_allowance_count';
        $policy = (string) data_get($designation, $policyField, 'ot_by_worked_hour');
        $policy = $policy !== '' ? $policy : 'ot_by_worked_hour';

        $salary = function_exists('hr_employee_salary') ? hr_employee_salary($employee) : [];
        $gross = (float) ($salary['gross'] ?? data_get($employee, 'gross_salary', 0));
        $basic = (float) ($salary['basic'] ?? data_get($employee, 'basic_salary', 0));
        $otRate = (float) ($salary['ot_rate'] ?? (($basic > 0) ? round(($basic / 208) * 2, 2) : 0));

        $allowanceField = $isCompliance ? 'holiday_allowance_comp' : 'holiday_allowance';
        $fixedPerDay = (float) data_get($designation, $allowanceField, 0);

        $workedDays = (float) ($context['weekend_to_regular_days'] ?? 0);
        $otMinutes = (float) ($context['weekend_to_regular_ot_minutes'] ?? 0);
        $otHours = round($otMinutes / 60, 2);
        $workingDays = max((int) ($context['working_days'] ?? 26), 1);

        $calculationType = 'ot_by_worked_hour';
        $amount = 0.0;

        switch ($policy) {
            case 'fixed_amount':
                $calculationType = 'fixed_amount';
                $amount = $fixedPerDay * $workedDays;
                break;
            case 'gross_month_day':
                $calculationType = 'fixed_formula';
                $amount = ($gross > 0 ? ($gross / 30) : 0) * $workedDays;
                break;
            case 'basic_working_day':
                $calculationType = 'fixed_formula';
                $amount = ($basic > 0 ? ($basic / $workingDays) : 0) * $workedDays;
                break;
            case 'basic_104_2_5':
                $calculationType = 'fixed_formula';
                $amount = ($basic > 0 ? (($basic / 104) * 2.5) : 0) * $workedDays;
                break;
            case 'ot_by_worked_hour':
            default:
                $calculationType = 'ot_by_worked_hour';
                $amount = $otHours * $otRate;
                break;
        }

        return [
            'policy' => $policy,
            'calculation_type' => $calculationType,
            'worked_days' => $workedDays,
            'ot_minutes' => (int) round($otMinutes),
            'ot_hours' => $otHours,
            'ot_rate' => $otRate,
            'fixed_per_day' => $fixedPerDay,
            'amount' => round($amount, 2),
        ];
    }

    public static function getEmployeeAttendanceByDate($employeeId, $fromDate, $toDate)
    {
        $employee  = \ME\Hr\Models\HrEmployee::findOrFail($employeeId);
        $from      = Carbon::parse($fromDate);
        $to        = Carbon::parse($toDate);
        $factoryNo = hr_factory('factory_no');
        $holidays  = \ME\Hr\Services\HrOptionsService::getOptions()['holidays'] ?? collect();

        // A resigned/left employee's data stops on their exit date — every report that
        // shares this engine (attendance, job card, salary sheet, OT summary, ...) must
        // treat any date past it as "not employed", not as absent, so it neither shows
        // fabricated attendance nor gets wrongly deducted for days they no longer worked.
        $exitedAt = null;
        $empExitStatus = strtolower((string) ($employee->employment_status ?? ''));
        if (in_array($empExitStatus, ['lefty', 'left', 'resign', 'resigned'], true) && !blank($employee->exited_at)) {
            $exitedAt = Carbon::parse($employee->exited_at)->startOfDay();
        }

        // A day that hasn't happened yet obviously has no attendance row either — without
        // this, every future date inside an in-progress month's range (e.g. requesting the
        // whole current month on the 19th) fell through to the "no attendance row" branch
        // below and was counted as Absent, wrongly inflating absent-day counts and any
        // deduction/earn-days math derived from them. Reuses the same 'not_employed'
        // exclusion as the resignation cutoff above — a day that hasn't occurred yet is
        // just as much "not counted" as a day after the employee left.
        $today = Carbon::today();

        // ── Designation effectiveness: OT flags & meal allowances ──────────
        $designation = $employee->designation
            ?? \ME\Hr\Models\HrDesignation::find($employee->designation_id);

        $isOtBasisWphp    = (bool) data_get($designation, 'is_ot_basis_wphp',     false);
        $isOtBasisMain    = (bool) data_get($designation, 'is_ot_basis_main',     true);
        $isOtBasisOthers1 = (bool) data_get($designation, 'is_ot_basis_others_1', true);
        $isOtBasisOthers2 = (bool) data_get($designation, 'is_ot_basis_others_2', true);
        $isFixedPunch     = (bool) data_get($designation, 'is_fixed_punch',       false);

        $otEnabled = match (true) {
            ($factoryNo == 1) => $isOtBasisOthers1,
            ($factoryNo == 2) => $isOtBasisOthers2,
            default            => $isOtBasisMain,
        };

        // Employee-level salary_info overrides designation; fall back to designation
        $si             = $employee->salaryInfo;
        $tiffinAmount   = (float) ($si?->tiffin_allowance  ?? data_get($designation, 'tiffin_allowance',  0));
        $minTiffinHour  = (float) ($si?->min_tiffin_hour   ?? data_get($designation, 'min_tiffin_hour',   0));
        $nightAmount    = (float) ($si?->night_allowance    ?? data_get($designation, 'night_allowance',   0));
        $minNightHour   = (float) ($si?->min_night_hour     ?? data_get($designation, 'min_night_hour',    0));
        $dinnerAmount   = (float) ($si?->dinner_allowance   ?? data_get($designation, 'dinner_allowance',  0));
        $minDinnerHour  = (float) ($si?->min_dinner_hour    ?? data_get($designation, 'min_dinner_hour',   0));
        $paymentWay     = strtolower($si?->payment_way ?? data_get($designation, 'payment_way', 'daily'));
        // ───────────────────────────────────────────────────────────────────

        $attendanceMap = \ME\Hr\Models\HrAttendance::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$fromDate, $toDate])
            ->get()
            ->keyBy(function ($a) {
                return $a->employee_id . '_' . \Carbon\Carbon::parse($a->date)->format('Y-m-d');
            });

        $dates = [];
        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $leaveSummary = [
            'casual'   => 0,
            'sick'     => 0,
            'earned'   => 0,
            'weekly'   => 0,
            'festival' => 0,
            'general'  => 0,
            'maternity'=> 0,
        ];

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->whereDate('leave_from', '<=', $toDate)
            ->whereDate('leave_to', '>=', $fromDate)
            ->get();

        $leaveByDate = [];
        foreach ($leaves as $leave) {
            $leaveFrom = Carbon::parse($leave->leave_from)->max($from)->startOfDay();
            $leaveTo   = Carbon::parse($leave->leave_to)->min($to)->startOfDay();
            for ($leaveDate = $leaveFrom->copy(); $leaveDate->lte($leaveTo); $leaveDate->addDay()) {
                $leaveByDate[$leaveDate->format('Y-m-d')] = $leave;
            }
        }

        $offDayWorkedDays      = 0;
        $offDayWorkedOtMinutes = 0;

        if (!$attendanceMap) $attendanceMap = collect();
        if (!$holidays)      $holidays      = collect();

        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin  = $allowOtHour * 60;
        $empWeekend  = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');

        $result = [];

        foreach ($dates as $d) {
            $dateStr = $d->format('Y-m-d');

            if (($exitedAt && $d->gt($exitedAt)) || $d->gt($today)) {
                $result[] = [
                    'date'            => $d->format('d-m-Y'),
                    'day'             => $d->format('l'),
                    'shift'           => null,
                    'shift_bn'        => null,
                    'in_time'         => '-',
                    'out_time'        => '-',
                    'status_key'      => 'not_employed',
                    'status'          => 'N/A',
                    'holiday_type'    => null,
                    'actual_ot'       => 0.0,
                    'compliance_ot'   => 0.0,
                    'extra_ot'        => null,
                    'worked_hours'    => 0,
                    'tiffin_eligible' => false,
                    'night_eligible'  => false,
                    'dinner_eligible' => false,
                    'remarks'         => '',
                ];
                continue;
            }

            $att     = $attendanceMap[$employee->id . '_' . $dateStr] ?? null;

            // Leave
            $leave = $leaveByDate[$dateStr] ?? null;
            if ($leave) {
                $leaveBucket = self::normalizeLeaveType($leave);
                $leaveSummary[$leaveBucket] = ($leaveSummary[$leaveBucket] ?? 0) + 1;
            }

            // Holiday
            $matchedHoliday = $holidays->first(fn ($h) => $dateStr >= $h->from_date && $dateStr <= $h->to_date);
            $isHoliday      = (bool) $matchedHoliday;

            // Weekend / Regular-To-Weekend / Weekend-To-Regular
            $dayOfWeek          = strtolower($d->format('l'));
            $isRegularToWeekend = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)->where('type', 'weekend')->where('status', 1)->exists();
            $isWeekendToRegular = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)->where('type', 'regular')->where('status', 1)->exists();

            $isWeekend            = false;
            $isWeekendForCompliance = false;
            if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                $isWeekend              = false;
                $isWeekendForCompliance = ($factoryNo == 1 || $factoryNo == 2);
            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                $isWeekend              = true;
                $isWeekendForCompliance = true;
            }

            // Status string
            if ($leave) {
                $status = 'leave'; $status_display = 'Leave';
            } elseif (($factoryNo == 1 || $factoryNo == 2) && $isWeekendForCompliance && !$isWeekendToRegular) {
                // A weekend-to-regular day must show its real attendance status (Present/
                // Absent/etc.), not "Weekend" — $isWeekendForCompliance is also true for
                // this case (see the branch above), so it must be excluded here explicitly.
                $status = 'weekend'; $status_display = 'Weekend';
            } elseif ($isHoliday) {
                $status = 'holiday'; $status_display = 'Holiday';
            } elseif ($isWeekend) {
                $status = 'weekend'; $status_display = 'Weekend';
            } elseif ($att) {
                $status         = !empty($att->status) ? str_replace(' ', '_', strtolower($att->status)) : 'present';
                $status_display = ucwords(str_replace('_', ' ', $status));
            } else {
                $status = 'absent'; $status_display = 'Absent';
            }

            $resolvedShift = $employee->resolveShiftForDate($d);
            $shiftMinutes  = 0;
            if ($resolvedShift && $resolvedShift->start_time && $resolvedShift->end_time) {
                $shiftStartDur = Carbon::parse($resolvedShift->start_time);
                $shiftEndDur   = Carbon::parse($resolvedShift->end_time);
                if ($shiftEndDur->lte($shiftStartDur)) {
                    $shiftEndDur->addDay(); // overnight shift
                }
                $shiftMinutes = (int) $shiftStartDur->diffInMinutes($shiftEndDur);
            }

            // Designation "Is Fixed Punch": once an employee has punched at all that day,
            // their effective in/out time is snapped to the shift's own start/end time
            // instead of the real clock time — for designations whose exact punch time
            // shouldn't matter. A day with no attendance record at all is untouched (still
            // resolves to Absent as normal); this only normalizes an existing punch.
            $effectiveInTime  = $att->in_time  ?? null;
            $effectiveOutTime = $att->out_time ?? null;
            if ($isFixedPunch && $att && ($effectiveInTime || $effectiveOutTime) && $resolvedShift && $resolvedShift->start_time && $resolvedShift->end_time) {
                $effectiveInTime  = $resolvedShift->start_time;
                $effectiveOutTime = $resolvedShift->end_time;
            }

            // ── In/Out visibility + OT, by day type and factory compliance mode ────
            // Recomputed live from the shift end time + the factory's OT grace period,
            // rather than trusting the stored total_ot_minute column — every report that
            // shares this engine (Job Card, Salary, Wages Summary, OT Summary, ...) must
            // reflect the current grace-period setting immediately, including for
            // attendance rows that were synced/saved before the setting was changed.
            $otMinRaw = $otEnabled
                ? self::calculateOvertimeMinutes($resolvedShift, $dateStr, $effectiveInTime, $effectiveOutTime, $employee)
                : 0;

            // A genuine (unconverted) off day — either the weekly holiday or a factory
            // holiday, but NOT a weekend-to-regular swap day (that behaves like an
            // ordinary shift day instead, see the elseif below).
            $isGenuineOffDay = ($isWeekend || $isHoliday) && !$isWeekendToRegular;

            if ($isGenuineOffDay) {
                // Compliance modes never show attendance/OT worked on a real off day,
                // regardless of the WPHP flag — the Weekend/Holiday Pay allowance
                // (calculateWeekendToRegularAllowance(), below) is still paid in every
                // mode via the designation's Main (Actual) / Comp (Compliance) fields;
                // that calculation is independent of this display-only suppression.
                // Actual shows the real punch, and when the designation's OT basis is WPHP
                // the WHOLE worked span counts as OT (an off day has no "regular shift"
                // portion to subtract out), not just the excess over shift end.
                if ($factoryNo == 1 || $factoryNo == 2) {
                    $inTime       = null;
                    $outTime      = null;
                    $actualOt     = 0.0;
                    $complianceOt = 0.0;
                    $extraOt      = null;
                } else {
                    $inTime  = $att && $effectiveInTime  ? $effectiveInTime  : null;
                    $outTime = $att && $effectiveOutTime ? $effectiveOutTime : null;

                    $otMinRawForActual = $otMinRaw;
                    if ($otEnabled && $isOtBasisWphp && $att && $att->in_time) {
                        $otMinRawForActual = max($otMinRaw, (int) ($att->total_working_minute ?? 0));
                    }
                    $actualOt     = round($otMinRawForActual / 60, 2);
                    $complianceOt = $isOtBasisWphp ? $actualOt : 0.0;
                    $extraOt      = null;
                }
            } elseif ($isWeekendToRegular) {
                // A weekend day converted to a working day is shown exactly like an
                // ordinary shift day — capped at the shift's own hours, zero OT — in every
                // factory mode. The real extra time worked is compensated separately via
                // the Weekend-to-Regular allowance (calculateWeekendToRegularAllowance()),
                // not through the job card's OT columns.
                $inTime  = $att && $effectiveInTime ? $effectiveInTime : null;
                $outTime = ($resolvedShift && $resolvedShift->end_time)
                    ? $resolvedShift->end_time
                    : ($att && $effectiveOutTime ? $effectiveOutTime : null);
                $actualOt     = 0.0;
                $complianceOt = 0.0;
                $extraOt      = ($factoryNo == 2) ? 0.0 : null;
            } else {
                // Regular working day (also covers holiday/leave/absent rows, where
                // in_time/out_time are naturally empty).
                $inTime  = $att && $effectiveInTime  ? $effectiveInTime  : null;
                $outTime = $att && $effectiveOutTime ? $effectiveOutTime : null;

                $actualOt = round($otMinRaw / 60, 2);

                if ($factoryNo == 1 || $factoryNo == 2) {
                    $cappedExcessMin = min($otMinRaw, $allowOtMin);
                    $complianceOt    = round($cappedExcessMin / 60, 2);
                    $extraOt         = ($factoryNo == 2 && $otMinRaw > $allowOtMin)
                        ? round(($otMinRaw - $allowOtMin) / 60, 2)
                        : ($factoryNo == 2 ? 0.0 : null);

                    // Comp-1 shows no Extra OT column, so time worked beyond the compliance
                    // cap is instead hidden by capping the displayed Out Time itself;
                    // Comp-2 shows the real Out Time since Extra OT already accounts for
                    // the difference.
                    if ($factoryNo == 1 && $outTime && $shiftMinutes > 0 && $otMinRaw > 0) {
                        $cappedOut = Carbon::parse($dateStr . ' ' . $resolvedShift->end_time)
                            ->addMinutes($cappedExcessMin);
                        $outTime = $cappedOut->format('H:i:s');
                    }
                } else {
                    $complianceOt = $actualOt;
                    $extraOt      = null;
                }
            }
            // ─────────────────────────────────────────────────────────────────────

            // ── Meal allowance eligibility ────────────────────────────────
            $workedHours    = $att ? round((int) ($att->total_working_minute ?? 0) / 60, 2) : 0;
            $tiffinEligible = $minTiffinHour > 0 && $workedHours >= $minTiffinHour && !$leave && !$isHoliday;
            $nightEligible  = $minNightHour  > 0 && $workedHours >= $minNightHour  && !$leave && !$isHoliday;
            $dinnerEligible = $minDinnerHour > 0 && $workedHours >= $minDinnerHour && !$leave && !$isHoliday;
            // ─────────────────────────────────────────────────────────────

            // WP & HP (Weekend/Holiday Pay) is earned only for a genuine off day the
            // employee actually attended — not the swap-converted ($isWeekendToRegular)
            // days, which are paid as ordinary working days instead. This check uses the
            // raw attendance record ($att), independent of the compliance-mode display
            // suppression above, since the allowance is still paid in every factory mode.
            if ($isGenuineOffDay && $att && ($att->in_time || $att->out_time)) {
                $offDayWorkedDays++;
                $offDayWorkedOtMinutes += max((int) ($att->total_working_minute ?? 0), 0);
            }
            $result[] = [
                'date'            => $d->format('d-m-Y'),
                'day'             => $d->format('l'),
                'shift'           => $resolvedShift->name ?? null,
                'shift_bn'        => $resolvedShift->bn_name ?? ($resolvedShift->name ?? null),
                'in_time'         => $inTime  ?? '-',
                'out_time'        => $outTime ?? '-',
                'status_key'      => $status,
                'status'          => $status_display,
                'holiday_type'    => $matchedHoliday->type ?? null,
                'actual_ot'       => $actualOt,
                'compliance_ot'   => $complianceOt,
                'extra_ot'        => $extraOt,
                'worked_hours'    => $workedHours,
                'tiffin_eligible' => $tiffinEligible,
                'night_eligible'  => $nightEligible,
                'dinner_eligible' => $dinnerEligible,
                'remarks'         => $att->remarks ?? '',
            ];
        }

        // ── Totals ────────────────────────────────────────────────────────
        $totals = [
            'totalDays'         => count($dates),
            'totalGovHolidays'  => 0,
            'totalGovHolidaysFestival' => 0,
            'totalGovHolidaysGeneral'  => 0,
            'totalWeekendDays'  => 0,
            'totalWorkingDays'  => 0,
            'totalAbsent'       => 0,
            'totalLeave'        => 0,
            'totalPresent'      => 0,
            'totalPresentAll'   => 0,
            'totalLate'         => 0,
            'totalPM'           => 0,
            'totalEO'           => 0,
            'totalLEO'          => 0,
            'totalLPM'          => 0,
            'totalAttendance'   => 0,
            'totalOt'           => 0,
            'totalComplianceOt' => 0,
            'totalExtraOt'      => 0,
        ];

        $tiffinEligibleDays = 0;
        $nightEligibleDays  = 0;
        $dinnerEligibleDays = 0;

        foreach ($dates as $idx => $d) {
            $row     = $result[$idx];
            $dateStr = $d->format('Y-m-d');

            // Days after the employee's exit date never happened for them — excluded
            // entirely from every total (not counted as absent, working, or anything else).
            if ($row['status_key'] === 'not_employed') {
                continue;
            }

            // OT totals from per-row values (already designation-flag-adjusted)
            // totalOt  = actual (uncapped) OT after designation flags; totalComplianceOt = capped compliance OT
            $totals['totalOt']           += $row['actual_ot']    ?? 0;
            $totals['totalComplianceOt'] += $row['compliance_ot'] ?? 0;
            $totals['totalExtraOt']      += $row['extra_ot']      ?? 0;

            // Use per-row status_key (already accounts for RegularToWeekend swaps)
            $sk = $row['status_key'];
            if ($sk === 'holiday') {
                $totals['totalGovHolidays']++;
                // Factory holidays are typed Festival or General (see HrHolidayController) —
                // anything else (unset, or a legacy pre-replacement type still on an old
                // record) defaults to General.
                if (strtolower((string) ($row['holiday_type'] ?? '')) === 'festival') {
                    $totals['totalGovHolidaysFestival']++;
                } else {
                    $totals['totalGovHolidaysGeneral']++;
                }
            } elseif ($sk === 'weekend') {
                $totals['totalWeekendDays']++;
            } else {
                $totals['totalWorkingDays']++;
            }

            if ($sk === 'leave')                                              $totals['totalLeave']++;
            if ($sk === 'absent')                                             $totals['totalAbsent']++;
            if ($sk === 'present')                                            $totals['totalPresent']++;
            if ($sk === 'late')                                               $totals['totalLate']++;
            if (in_array($sk, ['punch_missing', 'pm']))                       $totals['totalPM']++;
            if (in_array($sk, ['early_exit', 'eo']))                          $totals['totalEO']++;
            if (in_array($sk, ['late_and_early_exit', 'leo']))                $totals['totalLEO']++;
            if (in_array($sk, ['late_and_punch_missing', 'lpm']))             $totals['totalLPM']++;

            $isOnWeekendOrHoliday = in_array($sk, ['weekend', 'holiday']);
            if ($row['in_time'] && $row['in_time'] !== '-' && !$isOnWeekendOrHoliday) $totals['totalAttendance']++;
            if ($row['in_time'] && $row['in_time'] !== '-')                            $totals['totalPresentAll']++;

            // Meal eligible day counting
            if ($row['tiffin_eligible']) $tiffinEligibleDays++;
            if ($row['night_eligible'])  $nightEligibleDays++;
            if ($row['dinner_eligible']) $dinnerEligibleDays++;
        }

        $leaveSummary['weekly']   = (int) ($totals['totalWeekendDays']  ?? 0);
        $leaveSummary['festival'] = (int) ($totals['totalGovHolidays']  ?? 0);
        // Factory-holiday day count split by type (Festival / General) — distinct from the
        // 'festival'/'general' buckets above, which are actual employee leave-type tallies.
        $leaveSummary['holiday_festival'] = (int) ($totals['totalGovHolidaysFestival'] ?? 0);
        $leaveSummary['holiday_general']  = (int) ($totals['totalGovHolidaysGeneral']  ?? 0);

        // ── Meal totals (daily vs monthly payment way) ────────────────────
        if ($paymentWay === 'monthly') {
            $tiffinTotal = $tiffinEligibleDays > 0 ? $tiffinAmount : 0.0;
            $nightTotal  = $nightEligibleDays  > 0 ? $nightAmount  : 0.0;
            $dinnerTotal = $dinnerEligibleDays > 0 ? $dinnerAmount  : 0.0;
        } else {
            $tiffinTotal = $tiffinEligibleDays * $tiffinAmount;
            $nightTotal  = $nightEligibleDays  * $nightAmount;
            $dinnerTotal = $dinnerEligibleDays * $dinnerAmount;
        }

        $meal = [
            'tiffin_eligible_days' => $tiffinEligibleDays,
            'night_eligible_days'  => $nightEligibleDays,
            'dinner_eligible_days' => $dinnerEligibleDays,
            'tiffin_amount'        => $tiffinAmount,
            'night_amount'         => $nightAmount,
            'dinner_amount'        => $dinnerAmount,
            'tiffin_total'         => round($tiffinTotal, 2),
            'night_total'          => round($nightTotal,  2),
            'dinner_total'         => round($dinnerTotal, 2),
            'meal_total'           => round($tiffinTotal + $nightTotal + $dinnerTotal, 2),
            'payment_way'          => $paymentWay,
        ];
        // ─────────────────────────────────────────────────────────────────

        // WP & HP (Weekend/Holiday Pay) is earned per genuine off day (weekly-holiday or
        // factory-holiday, not a weekend-to-regular swap day) the employee actually
        // attended — $offDayWorkedDays/$offDayWorkedOtMinutes above only increment on a
        // real attendance record for that day. Paid in every factory mode (Actual uses
        // the designation's Main fields, Compliance uses its Comp fields — see
        // calculateWeekendToRegularAllowance()), independent of whether this specific
        // report/mode's Job Card columns display that day's punch or not. This is a
        // distinct payment from the plain OT column, which separately (and additionally)
        // pays for hours actually worked when the designation's "Is OT Basis (WPHP)"
        // flag is on — the two are not mutually exclusive.
        $weekendToRegular = self::calculateWeekendToRegularAllowance($employee, [
            'weekend_to_regular_days'       => $offDayWorkedDays,
            'weekend_to_regular_ot_minutes' => $offDayWorkedOtMinutes,
            'working_days'                  => $totals['totalWorkingDays'] ?? 0,
        ]);

        $totals['totalWeekendToRegularDays']    = $offDayWorkedDays;
        $totals['totalWeekendToRegularOtHours'] = round($offDayWorkedOtMinutes / 60, 2);
        $totals['totalWeekendToRegularAmount']  = $weekendToRegular['amount'] ?? 0;

        return [
            'attendance'       => $result,
            'summary'          => $totals,
            'leave'            => $leaveSummary,
            'weekend_to_regular' => $weekendToRegular,
            'meal'             => $meal,
        ];
    }

    public static function getSectionWiseAttendance($employeeIds, $fromDate, $toDate)
    {
        $result = [];
        $employees = \ME\Hr\Models\HrEmployee::whereIn('id', $employeeIds)->get();
        foreach ($employees as $employee) {
            $sectionId = $employee->section_id;
            $result[$sectionId][$employee->id] = self::getEmployeeAttendanceByDate(
                $employee->id,
                $fromDate,
                $toDate
            );
        }
        return $result;
    }

}
