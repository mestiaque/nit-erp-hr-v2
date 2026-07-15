<?php

namespace ME\Hr\Services;

use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrDesignation as Designation;
use ME\Hr\Models\HrEmployeeSalarySnapshot;
use ME\Hr\Models\HrLock;

class SalaryReportService
{
    /**
     * Get all salary-related data for a single employee for a date range.
     *
     * Pass $employeeDataFn (from HrOptionsService::getOptionsForEmployee()) so the
     * expensive options query runs only once per page, not once per employee.
     */
    public static function getEmployeeSalaryData(
        $emp,
        string $from,
        string $to,
        $request = null,
        ?callable $employeeDataFn = null
    ): array {
        // A locked (salary-approved) period is frozen to whatever was true at lock
        // time — no matter what attendance/increment/salary_info changes happen
        // afterward, the report must keep showing the locked snapshot, not a live
        // recalculation. Only applies when the period is a single calendar month.
        $toDate = \Carbon\Carbon::parse($to);
        if (\Carbon\Carbon::parse($from)->isSameMonth($toDate)) {
            $snapshot = HrEmployeeSalarySnapshot::where('employee_id', $emp->id)
                ->where('lock_year', $toDate->year)
                ->where('lock_month', $toDate->month)
                ->first();
            if ($snapshot
                && HrLock::isLocked('salary', $toDate->year, $toDate->month, $emp->department_id)
                && is_array($snapshot->raw_data)
            ) {
                return $snapshot->raw_data;
            }
        }

        if ($employeeDataFn === null) {
            $employeeDataFn = HrOptionsService::getOptionsForEmployee();
        }

        $factoryNo    = (int) (hr_factory('factory_no') ?? 0);
        $employeeData = $employeeDataFn($emp, $request, null, null, null, null);
        $salaryReport = $employeeData['getSalaryReport']($from, $to);
        $earnDeductSummary = isset($employeeData['getEarningsDeductionsSummary'])
            && is_callable($employeeData['getEarningsDeductionsSummary'])
            ? $employeeData['getEarningsDeductionsSummary']($from, $to)
            : [];

        $sal    = hr_employee_salary($emp);
        $sal    = \ME\Hr\Models\HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $emp->id);
        $otRate = (float) ($employeeData['salary']['ot_rate'] ?? $sal['ot_rate'] ?? 0);

        $attendancePack = EmployeeAttendanceService::getEmployeeAttendanceByDate($emp->id, $from, $to);
        $summary = $attendancePack['summary'] ?? [];
        $leave   = $attendancePack['leave']   ?? [];

        // Attendance-based OT (worked overtime hours x rate) and the manual OT(+/-)
        // adjustment from Earnings & Deductions (a separate bonus/penalty, unrelated
        // to attendance) are two independent things and must both apply — the manual
        // adjustment is already folded into $salaryReport['total_earn']/['total_deduct']/['net']
        // by getSalaryReport(), so attendance OT is simply added on top, not swapped in.
        $otHours  = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($summary['totalComplianceOt'] ?? 0)
            : (float) ($summary['totalOt'] ?? 0);
        $otAmount = round($otHours * $otRate, 2);

        $present = (int) ($summary['totalPresentAll'] ?? 0);
        $absent  = (int) ($summary['totalAbsent'] ?? 0);

        $leaveDays          = (int) ($summary['totalLeave'] ?? 0);
        $hasNoAbsentOrLeave = $absent === 0 && $leaveDays === 0;

        // Attendance bonus: host-app salary → employee salary_info (designation-synced) → designation → 0
        $empSi = $emp->salaryInfo;
        $designation = $emp->designation ?? ($emp->designation_id ? Designation::find($emp->designation_id) : null);
        $attendanceBonusBase = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($sal['attendance_bonus_com']
                ?? $empSi?->attendance_bonus_com
                ?? $designation?->attendance_bonus_com
                ?? 0)
            : (float) ($sal['attendance_bonus']
                ?? $empSi?->attendance_bonus
                ?? $designation?->attendance_bonus
                ?? 0);

        $attBonus = (float) (
            $salaryReport['attendance_bonus']
            ?? $salaryReport['att_bonus']
            ?? $earnDeductSummary['attendanceBonus']
            ?? $earnDeductSummary['attendance_bonus']
            ?? ($hasNoAbsentOrLeave ? $attendanceBonusBase : 0)
        );
        $allowOther = (float) (
            $earnDeductSummary['otherEarn']
            ?? $earnDeductSummary['other_earn']
            ?? $earnDeductSummary['others_earn']
            ?? $earnDeductSummary['otherAllowance']
            ?? $earnDeductSummary['other_allowance']
            ?? 0
        );
        $arrear = (float) (
            $salaryReport['arrear']
            ?? $earnDeductSummary['arrear']
            ?? $earnDeductSummary['salary_arrear']
            ?? 0
        );
        $deductAbsent = (float) ($summary['deductAbsent'] ?? $salaryReport['absent_deduct'] ?? 0);
        $loan         = (float) ($earnDeductSummary['advanceIou'] ?? $earnDeductSummary['loan'] ?? 0);
        $deductFood   = (float) ($earnDeductSummary['foodDeduct'] ?? $earnDeductSummary['food_deduct'] ?? 0);
        $mobile       = (float) ($earnDeductSummary['mobile'] ?? $earnDeductSummary['mobile_deduct'] ?? 0);
        $jr           = (float) ($earnDeductSummary['jr'] ?? $earnDeductSummary['join_resign'] ?? 0);

        // Stamp: fixed amount configured on the active factory.
        $stamp = (float) (hr_factory('stamp_amount') ?? 0);

        // Tax: employee's salary_info.tax, either a flat amount or a % of salary.
        // The % base follows factory compliance mode: Actual (0/null) -> Gross,
        // Comp-1/Comp-2 (1/2) -> Basic.
        $taxRaw    = (float) ($empSi?->tax ?? 0);
        $taxCalcBy = (string) ($empSi?->tax_calculate_by ?? 'amount');
        $taxBase   = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($salaryReport['basic'] ?? $sal['basic'] ?? 0)
            : (float) ($salaryReport['gross'] ?? $sal['gross'] ?? 0);
        $tax       = $taxCalcBy === 'percent' ? round($taxBase * ($taxRaw / 100), 2) : $taxRaw;

        $deductOther  = (float) (
            $earnDeductSummary['otherDeduct']
            ?? $earnDeductSummary['other_deduct']
            ?? $earnDeductSummary['others_deduct']
            ?? 0
        );

        $knownDeduct = $deductAbsent + $loan + $deductFood + $mobile + $jr + $stamp + $tax;
        if ($deductOther <= 0 && $knownDeduct < (float) ($salaryReport['total_deduct'] ?? 0)) {
            $deductOther = (float) ($salaryReport['total_deduct'] ?? 0) - $knownDeduct;
        }

        // Per-LeaveInfo code counts from Leave records within the period
        $leavesByCode = [];
        $empLeaves = Leave::with('leaveType')
            ->where('employee_id', $emp->id)
            ->whereDate('leave_from', '<=', $to)
            ->whereDate('leave_to', '>=', $from)
            ->get();
        foreach ($empLeaves as $lv) {
            $code = strtoupper((string) ($lv->leaveType->code ?? ''));
            if (!$code) continue;
            $lvFrom = \Carbon\Carbon::parse(max($lv->leave_from, $from));
            $lvTo   = \Carbon\Carbon::parse(min($lv->leave_to, $to));
            $days   = max(0, (int) $lvFrom->diffInDays($lvTo) + 1);
            $leavesByCode[$code] = ($leavesByCode[$code] ?? 0) + $days;
        }

        // Extra facility = Car & Fuel + Phone & Internet + Extra Facility, each already
        // resolved employee-salary_info-first, falling back to designation, by hr_employee_salary().
        $extraFacility = (float) ($sal['car_fuel'] ?? 0)
            + (float) ($sal['phone_internet'] ?? 0)
            + (float) ($sal['extra_facility'] ?? 0);

        // Meal allowances (tiffin / night / dinner) from attendance pack
        $meal        = $attendancePack['meal'] ?? [];
        $tiffinTotal = (float) ($meal['tiffin_total'] ?? 0);
        $nightTotal  = (float) ($meal['night_total']  ?? 0);
        $dinnerTotal = (float) ($meal['dinner_total']  ?? 0);
        $mealTotal   = $tiffinTotal + $nightTotal + $dinnerTotal;

        return [
            'gross'                => (float) ($salaryReport['gross'] ?? $sal['gross'] ?? 0),
            'basic'                => (float) ($salaryReport['basic'] ?? $sal['basic'] ?? 0),
            'house_rent'           => (float) ($sal['house'] ?? 0),
            'medical'              => (float) ($sal['medical'] ?? 0),
            'transport'            => (float) ($sal['transport'] ?? 0),
            'food_allow'           => (float) ($sal['food'] ?? 0),
            'total_earn'           => (float) ($salaryReport['total_earn'] ?? 0) + $otAmount + $extraFacility,
            'total_deduct'         => (float) ($salaryReport['total_deduct'] ?? 0) + $tax + $stamp,
            'net'                  => (float) ($salaryReport['net'] ?? 0) + $otAmount + $extraFacility - $tax - $stamp,
            'ot'                   => $otAmount,
            'ot_hours'             => $otHours,
            'ot_rate'              => $otRate,
            'present'              => $present,
            'absent'               => $absent,
            'wh'                   => (int) ($leave['weekly']   ?? 0),
            'fh'                   => (int) ($leave['festival'] ?? 0),
            'leaves_by_code'       => $leavesByCode,
            'att_bonus'            => $attBonus,
            'allow_other'          => $allowOther,
            'arrear'               => $arrear,
            'deduct_absent'        => $deductAbsent,
            'deduct_other'         => $deductOther,
            'loan'                 => $loan,
            'deduct_food'          => $deductFood,
            'mobile'               => $mobile,
            'jr'                   => $jr,
            'tax'                  => $tax,
            'stamp'                => $stamp,
            'wph_days'             => (int)   ($summary['totalWeekendToRegularDays']   ?? 0),
            'wph_amount'           => (float) ($summary['totalWeekendToRegularAmount'] ?? 0),
            'extra_facility'       => $extraFacility,
            'tiffin_total'         => $tiffinTotal,
            'night_total'          => $nightTotal,
            'dinner_total'         => $dinnerTotal,
            'meal_total'           => round($mealTotal, 2),
            'tiffin_eligible_days' => (int) ($meal['tiffin_eligible_days'] ?? 0),
            'night_eligible_days'  => (int) ($meal['night_eligible_days']  ?? 0),
            'dinner_eligible_days' => (int) ($meal['dinner_eligible_days']  ?? 0),
        ];
    }

    /**
     * Aggregated department/section totals for the Wages & Salary Summary report.
     */
    public static function buildWagesSummaryData($employees, string $from, string $to, $request): array
    {
        $employeeDataFn = HrOptionsService::getOptionsForEmployee();
        $byDept = $employees->groupBy('department_id');

        $keys = ['emp', 'basic', 'house_rent', 'medical', 'transport', 'ot', 'gross', 'earn', 'deduct', 'net', 'present', 'absent'];
        $grandTotals = array_fill_keys($keys, 0);
        $summaryData = [];

        foreach ($byDept as $deptId => $deptEmps) {
            foreach ($deptEmps->groupBy('section_id') as $secId => $secEmps) {
                $row = array_fill_keys($keys, 0);
                $row['dept_id'] = $deptId;
                $row['sec_id'] = $secId;
                $row['emp'] = $secEmps->count();

                foreach ($secEmps as $emp) {
                    $sd = self::getEmployeeSalaryData($emp, $from, $to, $request, $employeeDataFn);
                    $row['basic'] += $sd['basic'];
                    $row['house_rent'] += $sd['house_rent'];
                    $row['medical'] += $sd['medical'];
                    $row['transport'] += $sd['transport'];
                    $row['ot'] += $sd['ot'];
                    $row['gross'] += $sd['gross'];
                    $row['earn'] += $sd['total_earn'];
                    $row['deduct'] += $sd['total_deduct'];
                    $row['net'] += $sd['net'];
                    $row['present'] += $sd['present'];
                    $row['absent'] += $sd['absent'];
                }

                $summaryData[] = $row;
                foreach ($keys as $k) {
                    $grandTotals[$k] += $row[$k];
                }
            }
        }

        return [
            'byDeptSummary' => collect($summaryData)->groupBy('dept_id'),
            'grandTotals' => $grandTotals,
        ];
    }

    /**
     * Per-employee bonus amounts (grouped by department) for the Bonus report.
     */
    public static function buildBonusReportData($employees, $request, string $to): array
    {
        $bonusPolicies = collect();
        $bonusTitle = null;
        $bonusByDept = [];
        $bonusGrandTotal = 0;
        $bonusGrandEmp = 0;
        $hasPctPolicy = false;

        $bonusTitleId = $request->input('bonus_title');
        if (filled($bonusTitleId)) {
            $bonusTitle = \ME\Hr\Models\HrBonusTitle::find($bonusTitleId);
            $bonusPolicies = \ME\Hr\Models\HrBonusPolicy::query()->where('bonus_title_id', $bonusTitleId)->where('status', 'active')->get();
            if ($bonusPolicies->isEmpty()) {
                $bonusPolicies = \ME\Hr\Models\HrBonusPolicy::query()->where('bonus_title_id', $bonusTitleId)->get();
            }
        }

        if ($bonusPolicies->isNotEmpty()) {
            $bonusReferenceDate = \Carbon\Carbon::parse($request->input('up_to_date') ?: $to);

            foreach ($employees->groupBy('department_id') as $deptId => $deptEmps) {
                $rows = [];
                foreach ($deptEmps as $emp) {
                    $bd = self::computeEmployeeBonus($emp, $bonusPolicies, $bonusReferenceDate);
                    if ($bd['policy'] === null) {
                        continue;
                    }
                    $rows[] = ['emp' => $emp, 'bd' => $bd];
                    $bonusGrandTotal += $bd['bonus'];
                    $bonusGrandEmp++;
                    if ($bd['percent'] !== null) {
                        $hasPctPolicy = true;
                    }
                }
                if (!empty($rows)) {
                    $bonusByDept[$deptId] = $rows;
                }
            }
        }

        return compact('bonusPolicies', 'bonusTitle', 'bonusByDept', 'bonusGrandTotal', 'bonusGrandEmp', 'hasPctPolicy');
    }

    public static function computeEmployeeBonus($emp, $bonusPolicies, \Carbon\Carbon $bonusReferenceDate): array
    {
        $sal = hr_employee_salary($emp);
        $gross = (float) ($sal['gross'] ?? $emp->gross_salary ?? 0);
        $basic = (float) ($sal['basic'] ?? $emp->basic_salary ?? 0);
        $productionBase = (float) ($sal['production_salary'] ?? $sal['production'] ?? $sal['total_production'] ?? 0);

        $joiningDate = $emp->joining_date ? \Carbon\Carbon::parse($emp->joining_date) : null;
        $serviceMonths = $joiningDate ? max(0, (int) $joiningDate->diffInMonths($bonusReferenceDate, false)) : null;

        $matchedPolicy = $bonusPolicies->filter(function ($policy) use ($emp, $serviceMonths) {
            $designationMatch = !$policy->designation_id || (int) $policy->designation_id === (int) $emp->designation_id;
            $sectionMatch = !$policy->section_id || (int) $policy->section_id === (int) $emp->section_id;

            $monthFrom = is_null($policy->month_from) ? null : (int) $policy->month_from;
            $monthTo = is_null($policy->month_to) ? null : (int) $policy->month_to;

            $monthMatch = true;
            if (!is_null($serviceMonths)) {
                if (!is_null($monthFrom)) {
                    $monthMatch = $monthMatch && ($serviceMonths >= $monthFrom);
                }
                if (!is_null($monthTo)) {
                    $monthMatch = $monthMatch && ($serviceMonths <= $monthTo);
                }
            } elseif (!is_null($monthFrom) || !is_null($monthTo)) {
                $monthMatch = false;
            }

            return $designationMatch && $sectionMatch && $monthMatch;
        })->sortByDesc(function ($policy) {
            return (is_null($policy->designation_id) ? 0 : 4)
                + (is_null($policy->section_id) ? 0 : 2)
                + (is_null($policy->month_from) ? 0 : 1)
                + (is_null($policy->month_to) ? 0 : 1);
        })->first();

        $bonus = 0.0;
        $policyLabel = '—';
        $percent = null;

        if ($matchedPolicy) {
            $amountType = strtolower($matchedPolicy->amount_type ?? 'percent');
            $salaryBasis = strtolower($matchedPolicy->salary_basis ?? 'gross');
            $base = match ($salaryBasis) {
                'basic' => $basic,
                'production' => $productionBase,
                default => $gross,
            };
            if ($amountType === 'fixed') {
                $bonus = (float) $matchedPolicy->amount;
            } else {
                $percent = (float) $matchedPolicy->amount;
                $bonus = round($base * $percent / 100, 2);
            }
            $policyLabel = $matchedPolicy->name . ($percent !== null ? " ({$percent}%)" : '');
        }

        $jobAge = 'N/A';
        if ($joiningDate) {
            $diff = $joiningDate->diff($bonusReferenceDate);
            $jobAge = sprintf('%dy %dm %dd', $diff->y, $diff->m, $diff->d);
        }

        return [
            'bonus' => $bonus,
            'basic' => $basic,
            'gross' => $gross,
            'policy' => $matchedPolicy,
            'policy_label' => $policyLabel,
            'job_age' => $jobAge,
            'percent' => $percent,
        ];
    }

    /**
     * Detailed per-employee rows (grouped by department/section) for the Salary Sheet report.
     * Shared by both 'fixed' and 'production' report types, and by both the SFL and non-SFL
     * salary-sheet blade layouts — the row data is identical, only the column layout differs.
     */
    public static function buildSalarySheetData($employees, string $from, string $to, $request, $leaveInfos): array
    {
        $employeeDataFn = HrOptionsService::getOptionsForEmployee();

        $periodStart = \Carbon\Carbon::parse($from);
        $periodEnd = \Carbon\Carbon::parse($to);
        $totalMonthDays = (int) $periodStart->daysInMonth;
        $totalPeriodDays = (int) $periodStart->diffInDays($periodEnd) + 1;

        $dayMap = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
        $weekendRaw = (string) (hr_factory('weekend') ?? 'Friday');
        $weekendNames = collect(preg_split('/\s*,\s*/', $weekendRaw))
            ->filter(fn ($v) => filled($v))
            ->map(fn ($v) => strtolower(trim((string) $v)))
            ->values();
        if ($weekendNames->isEmpty()) {
            $weekendNames = collect(['friday']);
        }
        $weekendDayNumbers = $weekendNames->map(fn ($name) => $dayMap[$name] ?? null)->filter(fn ($n) => !is_null($n))->unique()->values()->all();
        if (empty($weekendDayNumbers)) {
            $weekendDayNumbers = [\Carbon\Carbon::FRIDAY];
        }

        $datePeriod = collect(\Carbon\CarbonPeriod::create($periodStart->copy()->startOfDay(), '1 day', $periodEnd->copy()->startOfDay()));
        $weekendDateMap = $datePeriod
            ->filter(fn ($d) => in_array($d->dayOfWeek, $weekendDayNumbers, true))
            ->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
            ->all();

        try {
            $rtwRows = \ME\Hr\Models\HrRegularToWeekend::query()
                ->whereDate('date', '>=', $periodStart->toDateString())
                ->whereDate('date', '<=', $periodEnd->toDateString())
                ->where('status', 1)
                ->get(['date', 'type']);

            foreach ($rtwRows as $rtw) {
                $dateKey = \Carbon\Carbon::parse($rtw->date)->format('Y-m-d');
                if (strtolower((string) $rtw->type) === 'weekend') {
                    $weekendDateMap[$dateKey] = true;
                } elseif (strtolower((string) $rtw->type) === 'regular') {
                    unset($weekendDateMap[$dateKey]);
                }
            }
        } catch (\Throwable $e) {
            // Keep base weekend calculation when regular_to_weekends table/data is unavailable.
        }

        $holidayDateMap = [];
        try {
            $holidayRows = \ME\Hr\Models\HrHoliday::query()
                ->where('status', 1)
                ->where(fn ($q) => $q->whereNull('type')->orWhere('type', 'not like', '%Weekly%'))
                ->where(function ($q) use ($periodStart, $periodEnd) {
                    $q->whereBetween('from_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                        ->orWhereBetween('to_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                        ->orWhere(function ($q2) use ($periodStart, $periodEnd) {
                            $q2->where('from_date', '<=', $periodStart->toDateString())
                                ->where('to_date', '>=', $periodEnd->toDateString());
                        });
                })
                ->get(['from_date', 'to_date']);

            foreach ($holidayRows as $holiday) {
                $hStart = \Carbon\Carbon::parse($holiday->from_date)->startOfDay();
                $hEndRaw = filled($holiday->to_date) ? $holiday->to_date : $holiday->from_date;
                $hEnd = \Carbon\Carbon::parse($hEndRaw)->startOfDay();

                if ($hStart->lt($periodStart->copy()->startOfDay())) {
                    $hStart = $periodStart->copy()->startOfDay();
                }
                if ($hEnd->gt($periodEnd->copy()->startOfDay())) {
                    $hEnd = $periodEnd->copy()->startOfDay();
                }

                if ($hStart->lte($hEnd)) {
                    foreach (\Carbon\CarbonPeriod::create($hStart, '1 day', $hEnd) as $hDate) {
                        $holidayDateMap[$hDate->format('Y-m-d')] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Keep holiday count 0 when holidays table/data is unavailable.
        }

        $weekendCount = count($weekendDateMap);
        $otherHolidayCount = count(array_diff_key($holidayDateMap, $weekendDateMap));
        $totalWorkingDays = max(0, $totalPeriodDays - $weekendCount - $otherHolidayCount);
        $deductionMonthDays = 30;
        $factoryNo = (int) (hr_factory('factory_no') ?? 0);

        $grandBase = [
            'emp' => 0, 'basic' => 0, 'house' => 0, 'medical' => 0,
            'transport' => 0, 'food' => 0, 'salary_total' => 0,
            'pr' => 0, 'wh' => 0, 'fh' => 0, 'ab' => 0, 'earn_days' => 0,
            'att_bonus' => 0, 'deduct_absent' => 0, 'loan' => 0, 'tax' => 0, 'stamp' => 0,
            'deduct_other' => 0, 'wph_days' => 0, 'wph_amount' => 0,
            'other_earn' => 0, 'gross' => 0, 'payable' => 0,
            'ot_hours' => 0, 'ot_rate' => 0, 'ot_total' => 0,
            'extra_facility' => 0, 'net' => 0, 'deduction_total' => 0,
        ];
        foreach ($leaveInfos as $li) {
            $grandBase['leave_' . strtoupper($li->code)] = 0;
        }
        $grand = $grandBase;
        $sheetRows = [];

        foreach ($employees->groupBy('department_id') as $deptId => $deptEmps) {
            foreach ($deptEmps->groupBy('section_id') as $secId => $secEmps) {
                $secEmps = self::sortEmployeesByNaturalId($secEmps);
                $rows = [];
                $secTotals = $grandBase;

                foreach ($secEmps as $emp) {
                    $sd = self::getEmployeeSalaryData($emp, $from, $to, $request, $employeeDataFn);

                    $salaryTotal = $sd['basic'] + $sd['house_rent'] + $sd['medical'] + $sd['transport'] + $sd['food_allow'];
                    $otRate = (float) ($sd['ot_rate'] ?? 0);
                    $presentDays = (int) ($sd['present'] ?? 0);
                    $absentDays = (int) ($sd['absent'] ?? 0);
                    $attBonus = (float) ($sd['att_bonus'] ?? 0);
                    $loan = (float) ($sd['loan'] ?? 0);
                    $tax = (float) ($sd['tax'] ?? 0);
                    $stamp = (float) ($sd['stamp'] ?? 0);
                    $deductOther = (float) ($sd['deduct_other'] ?? 0);
                    $otherEarn = (float) ($sd['allow_other'] ?? 0) + (float) ($sd['arrear'] ?? 0);
                    $wphAmount = (float) ($sd['wph_amount'] ?? 0);
                    $otAmount = (float) ($sd['ot'] ?? 0);
                    $extraFacility = (float) ($sd['extra_facility'] ?? 0);

                    $absentBase = ($factoryNo === 1 || $factoryNo === 2) ? $sd['basic'] : $sd['gross'];
                    $deductAbsent = $absentDays > 0 ? round(($absentBase / $deductionMonthDays) * $absentDays, 2) : 0;
                    $looksLikeNoPresentFullPay = $presentDays === 0
                        && $absentDays > 0
                        && (float) ($sd['net'] ?? 0) >= (float) ($sd['gross'] ?? 0);
                    if ($looksLikeNoPresentFullPay && $deductAbsent <= 0 && $deductionMonthDays > 0) {
                        $deductAbsent = round(($absentBase / $deductionMonthDays) * $absentDays, 2);
                    }

                    $payableSalary = max(0, ($salaryTotal + $attBonus + $wphAmount + $otherEarn) - $deductAbsent);
                    $deductionTotal = (float) ($sd['total_deduct'] ?? 0);
                    if ($looksLikeNoPresentFullPay) {
                        $deductionTotal = max($deductionTotal, $deductAbsent + $loan + $tax + $stamp + $deductOther);
                    }
                    $netSalary = $looksLikeNoPresentFullPay
                        ? max(0, $payableSalary + $otAmount + $extraFacility - ($loan + $tax + $stamp + $deductOther))
                        : (float) ($sd['net'] ?? 0);

                    $row = [
                        'emp' => $emp,
                        'basic' => $sd['basic'],
                        'house' => $sd['house_rent'],
                        'medical' => $sd['medical'],
                        'transport' => $sd['transport'],
                        'food' => $sd['food_allow'],
                        'salary_total' => $salaryTotal,
                        'pr' => $presentDays,
                        'wh' => $sd['wh'],
                        'fh' => $sd['fh'],
                        'ab' => $absentDays,
                        'earn_days' => $totalMonthDays - $absentDays,
                        'att_bonus' => $attBonus,
                        'deduct_absent' => $deductAbsent,
                        'loan' => $loan,
                        'tax' => $tax,
                        'stamp' => $stamp,
                        'deduct_other' => $deductOther,
                        'wph_days' => $sd['wph_days'],
                        'wph_amount' => $wphAmount,
                        'other_earn' => $otherEarn,
                        'gross' => $sd['gross'],
                        'payable' => $payableSalary,
                        'ot_hours' => $sd['ot_hours'],
                        'ot_rate' => $otRate,
                        'ot_total' => $otAmount,
                        'extra_facility' => $extraFacility,
                        'net' => $netSalary,
                        'deduction_total' => $deductionTotal,
                    ];
                    foreach ($leaveInfos as $li) {
                        $code = strtoupper($li->code);
                        $row['leave_' . $code] = (int) ($sd['leaves_by_code'][$code] ?? 0);
                    }
                    $rows[] = $row;

                    $secTotals['emp']++;
                    $grand['emp']++;
                    foreach (array_keys($grandBase) as $k) {
                        if ($k === 'emp') {
                            continue;
                        }
                        $secTotals[$k] = ($secTotals[$k] ?? 0) + ($row[$k] ?? 0);
                        $grand[$k] = ($grand[$k] ?? 0) + ($row[$k] ?? 0);
                    }
                }

                if (!empty($rows)) {
                    $sheetRows[] = ['dept_id' => $deptId, 'sec_id' => $secId, 'rows' => $rows, 'totals' => $secTotals];
                }
            }
        }

        return [
            'sheetRows' => $sheetRows,
            'grand' => $grand,
            'totalMonthDays' => $totalMonthDays,
            'totalWorkingDays' => $totalWorkingDays,
            'weekendCount' => $weekendCount,
            'otherHolidayCount' => $otherHolidayCount,
            'inWords' => self::numberToWords((int) round($grand['net'])),
        ];
    }

    public static function sortEmployeesByNaturalId($employees)
    {
        return $employees->sort(function ($a, $b) {
            preg_match('/^([A-Za-z]*)(\d+)$/', $a->employee_id, $ma);
            preg_match('/^([A-Za-z]*)(\d+)$/', $b->employee_id, $mb);
            $prefixA = strtoupper($ma[1] ?? '');
            $prefixB = strtoupper($mb[1] ?? '');
            if ($prefixA !== $prefixB) {
                return strcmp($prefixA, $prefixB);
            }
            return (int) ($ma[2] ?? 0) <=> (int) ($mb[2] ?? 0);
        })->values();
    }

    public static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen',
        ];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        $convertBelowThousand = function ($n) use ($ones, $tens) {
            $text = '';
            $hundreds = intdiv($n, 100);
            $rest = $n % 100;
            if ($hundreds > 0) {
                $text .= $ones[$hundreds] . ' hundred';
                if ($rest > 0) {
                    $text .= ' ';
                }
            }
            if ($rest > 0) {
                if ($rest < 20) {
                    $text .= $ones[$rest];
                } else {
                    $text .= $tens[intdiv($rest, 10)];
                    $u = $rest % 10;
                    if ($u > 0) {
                        $text .= '-' . $ones[$u];
                    }
                }
            }
            return trim($text);
        };

        $scales = [1000000000 => 'billion', 1000000 => 'million', 1000 => 'thousand', 1 => ''];
        $parts = [];
        foreach ($scales as $base => $label) {
            if ($number >= $base) {
                $chunk = intdiv($number, $base);
                $number %= $base;
                if ($chunk > 0) {
                    $piece = $convertBelowThousand($chunk);
                    if ($label !== '') {
                        $piece .= ' ' . $label;
                    }
                    $parts[] = trim($piece);
                }
            }
        }

        return trim(implode(' ', $parts));
    }
}
