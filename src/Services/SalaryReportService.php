<?php

namespace App\Services;

use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrDesignation as Designation;

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
        $otRate = (float) ($employeeData['salary']['ot_rate'] ?? $sal['ot_rate'] ?? 0);

        $attendancePack = EmployeeAttendanceService::getEmployeeAttendanceByDate($emp->id, $from, $to);
        $summary = $attendancePack['summary'] ?? [];
        $leave   = $attendancePack['leave']   ?? [];

        $otHours      = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($summary['totalComplianceOt'] ?? 0)
            : (float) ($summary['totalOt'] ?? 0);
        $otAmount     = round($otHours * $otRate, 2);
        $otAdjustment = $otAmount - (float) ($salaryReport['ot'] ?? 0);

        $present = (int) ($summary['totalPresentAll'] ?? 0);
        $absent  = (int) ($summary['totalAbsent'] ?? 0);

        $leaveDays          = (int) ($summary['totalLeave'] ?? 0);
        $hasNoAbsentOrLeave = $absent === 0 && $leaveDays === 0;
        $attendanceBonusBase = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($sal['attendance_bonus_com'] ?? 0)
            : (float) ($sal['attendance_bonus'] ?? 0);

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
        $stamp        = (float) ($salaryReport['stamp'] ?? $sal['stamp_amount'] ?? 0);
        $deductOther  = (float) (
            $earnDeductSummary['otherDeduct']
            ?? $earnDeductSummary['other_deduct']
            ?? $earnDeductSummary['others_deduct']
            ?? 0
        );

        $knownDeduct = $deductAbsent + $loan + $deductFood + $mobile + $jr + $stamp;
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

        // Extra facility from designation
        $extraFacility = 0.0;
        if ($emp->designation_id) {
            static $designationCache = [];
            if (!isset($designationCache[$emp->designation_id])) {
                $designationCache[$emp->designation_id] = Designation::find($emp->designation_id);
            }
            $extraFacility = (float) ($designationCache[$emp->designation_id]->extra_facility ?? 0);
        }

        return [
            'gross'          => (float) ($salaryReport['gross'] ?? $sal['gross'] ?? 0),
            'basic'          => (float) ($salaryReport['basic'] ?? $sal['basic'] ?? 0),
            'house_rent'     => (float) ($sal['house'] ?? 0),
            'medical'        => (float) ($sal['medical'] ?? 0),
            'transport'      => (float) ($sal['transport'] ?? 0),
            'food_allow'     => (float) ($sal['food'] ?? 0),
            'total_earn'     => (float) ($salaryReport['total_earn'] ?? 0) + $otAdjustment,
            'total_deduct'   => (float) ($salaryReport['total_deduct'] ?? 0),
            'net'            => (float) ($salaryReport['net'] ?? 0) + $otAdjustment,
            'ot'             => $otAmount,
            'ot_hours'       => $otHours,
            'ot_rate'        => $otRate,
            'present'        => $present,
            'absent'         => $absent,
            'wh'             => (int) ($leave['weekly']   ?? 0),
            'fh'             => (int) ($leave['festival'] ?? 0),
            'leaves_by_code' => $leavesByCode,
            'att_bonus'      => $attBonus,
            'allow_other'    => $allowOther,
            'arrear'         => $arrear,
            'deduct_absent'  => $deductAbsent,
            'deduct_other'   => $deductOther,
            'loan'           => $loan,
            'deduct_food'    => $deductFood,
            'mobile'         => $mobile,
            'jr'             => $jr,
            'stamp'          => $stamp,
            'wph_days'       => (int)   ($summary['totalWeekendToRegularDays']   ?? 0),
            'wph_amount'     => (float) ($summary['totalWeekendToRegularAmount'] ?? 0),
            'extra_facility' => $extraFacility,
        ];
    }
}
