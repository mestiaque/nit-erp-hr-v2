<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use ME\Hr\Models\HrEmployee as User;
use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrRegularToWeekend as RegularToWeekend;

class EmployeeAttendanceService
{
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

        $policy = (string) data_get($designation, 'weekend_allowance_count', 'ot_by_worked_hour');
        $policy = $policy !== '' ? $policy : 'ot_by_worked_hour';

        $salary = function_exists('hr_employee_salary') ? hr_employee_salary($employee) : [];
        $gross = (float) ($salary['gross'] ?? data_get($employee, 'gross_salary', 0));
        $basic = (float) ($salary['basic'] ?? data_get($employee, 'basic_salary', 0));
        $otRate = (float) ($salary['ot_rate'] ?? (($basic > 0) ? round(($basic / 208) * 2, 2) : 0));
        $fixedPerDay = (float) data_get($designation, 'holiday_allowance', 0);

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

        // ── Designation effectiveness: OT flags & meal allowances ──────────
        $designation = $employee->designation
            ?? \ME\Hr\Models\HrDesignation::find($employee->designation_id);

        $isOtBasisWphp    = (bool) data_get($designation, 'is_ot_basis_wphp',     false);
        $isOtBasisMain    = (bool) data_get($designation, 'is_ot_basis_main',     true);
        $isOtBasisOthers1 = (bool) data_get($designation, 'is_ot_basis_others_1', true);
        $isOtBasisOthers2 = (bool) data_get($designation, 'is_ot_basis_others_2', true);

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

        $weekendToRegularDays      = 0;
        $weekendToRegularOtMinutes = 0;

        if (!$attendanceMap) $attendanceMap = collect();
        if (!$holidays)      $holidays      = collect();

        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin  = $allowOtHour * 60;
        $empWeekend  = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');

        $result = [];

        foreach ($dates as $d) {
            $dateStr = $d->format('Y-m-d');
            $att     = $attendanceMap[$employee->id . '_' . $dateStr] ?? null;

            // Leave
            $leave = $leaveByDate[$dateStr] ?? null;
            if ($leave) {
                $leaveBucket = self::normalizeLeaveType($leave);
                $leaveSummary[$leaveBucket] = ($leaveSummary[$leaveBucket] ?? 0) + 1;
            }

            // Holiday
            $isHoliday = $holidays->contains(fn ($h) => $dateStr >= $h->from_date && $dateStr <= $h->to_date);

            // Weekend / Regular-To-Weekend / Weekend-To-Regular
            $dayOfWeek          = strtolower($d->format('l'));
            $isRegularToWeekend = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)->where('type', 'weekend')->where('status', 1)->exists();
            $isWeekendToRegular = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)->where('type', 'half_day')->where('status', 1)->exists();

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
            } elseif (($factoryNo == 1 || $factoryNo == 2) && $isWeekendForCompliance) {
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

            // In/Out visibility: an actual (unconverted) weekly holiday never shows punch
            // times, even if the employee happened to punch in/out — only a day that's
            // been explicitly converted to a working day (weekend-to-regular) shows
            // attendance normally. This is independent of the WPHP flag, which only
            // affects whether the full worked time counts as OT (see below), not visibility.
            if ($isWeekend) {
                $inTime  = null;
                $outTime = null;
            } else {
                $inTime  = $att && $att->in_time  ? $att->in_time  : null;
                $outTime = $att && $att->out_time ? $att->out_time : null;
            }

            // ── OT calculation with designation flags ─────────────────────
            $otMinRaw = $att ? (int) ($att->overtime_minutes ?? 0) : 0;

            // WPHP: on a converted (weekend-to-regular) working day, the full worked
            // time counts as OT when this flag is ON.
            if ($isOtBasisWphp && $isWeekendToRegular && $att && $att->in_time) {
                $otMinRaw = max($otMinRaw, (int) ($att->total_working_minute ?? 0));
            }

            // Zero out OT when not enabled for this factory / designation
            if (!$otEnabled) {
                $otMinRaw = 0;
            }

            $actualOt = round($otMinRaw / 60, 2);

            if ($factoryNo == 1) {
                $weekendBlocksOt = $isWeekendForCompliance && !$isOtBasisWphp;
                $complianceOt    = $weekendBlocksOt ? 0 : round(min($otMinRaw, $allowOtMin) / 60, 2);
                $extraOt         = null;
            } elseif ($factoryNo == 2) {
                $weekendBlocksOt = $isWeekendForCompliance && !$isOtBasisWphp;
                $complianceOt    = $weekendBlocksOt ? 0 : round(min($otMinRaw, $allowOtMin) / 60, 2);
                $extraOt         = $weekendBlocksOt ? 0 : ($otMinRaw > $allowOtMin ? round(($otMinRaw - $allowOtMin) / 60, 2) : 0);
            } else {
                // Factory null/0: weekend OT only when WPHP is ON
                $complianceOt = (($isWeekend || $isWeekendForCompliance) && !$isOtBasisWphp) ? 0 : $actualOt;
                $extraOt      = null;
            }
            // ─────────────────────────────────────────────────────────────

            // ── Meal allowance eligibility ────────────────────────────────
            $workedHours    = $att ? round((int) ($att->total_working_minute ?? 0) / 60, 2) : 0;
            $tiffinEligible = $minTiffinHour > 0 && $workedHours >= $minTiffinHour && !$leave && !$isHoliday;
            $nightEligible  = $minNightHour  > 0 && $workedHours >= $minNightHour  && !$leave && !$isHoliday;
            $dinnerEligible = $minDinnerHour > 0 && $workedHours >= $minDinnerHour && !$leave && !$isHoliday;
            // ─────────────────────────────────────────────────────────────

            if ($isWeekendToRegular && $att && ($att->in_time || $att->out_time || $otMinRaw > 0)) {
                $weekendToRegularDays++;
                $weekendToRegularOtMinutes += max($otMinRaw, 0);
            }
            $result[] = [
                'date'            => $d->format('d-m-Y'),
                'day'             => $d->format('l'),
                'shift'           => $employee->shift->name ?? null,
                'shift_bn'        => $employee->shift->bn_name ?? ($employee->shift->name ?? null),
                'in_time'         => $inTime  ?? '-',
                'out_time'        => $outTime ?? '-',
                'status_key'      => $status,
                'status'          => $status_display,
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

            // OT totals from per-row values (already designation-flag-adjusted)
            // totalOt  = actual (uncapped) OT after designation flags; totalComplianceOt = capped compliance OT
            $totals['totalOt']           += $row['actual_ot']    ?? 0;
            $totals['totalComplianceOt'] += $row['compliance_ot'] ?? 0;
            $totals['totalExtraOt']      += $row['extra_ot']      ?? 0;

            // Use per-row status_key (already accounts for RegularToWeekend swaps)
            $sk = $row['status_key'];
            if ($sk === 'holiday')       $totals['totalGovHolidays']++;
            elseif ($sk === 'weekend')   $totals['totalWeekendDays']++;
            else                         $totals['totalWorkingDays']++;

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

        $weekendToRegular = self::calculateWeekendToRegularAllowance($employee, [
            'weekend_to_regular_days'       => $weekendToRegularDays,
            'weekend_to_regular_ot_minutes' => $weekendToRegularOtMinutes,
            'working_days'                  => $totals['totalWorkingDays'] ?? 0,
        ]);

        $totals['totalWeekendToRegularDays']    = $weekendToRegularDays;
        $totals['totalWeekendToRegularOtHours'] = round($weekendToRegularOtMinutes / 60, 2);
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
