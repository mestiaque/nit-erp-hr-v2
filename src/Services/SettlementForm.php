<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use ME\Hr\Models\HrAdvanceApplication;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeLeave;
use ME\Hr\Models\HrEmployeeSalaryIncrement;
use ME\Hr\Models\HrLeaveInfo;
use ME\Hr\Models\HrLock;

/**
 * Figures for the factory's own "চূড়ান্ত নিষ্পত্তিকরণ ফরম" (print: employees.print.settlement-form).
 * Pure calculation, nothing is saved.
 *
 *  - One day's wage = gross / 30 (service benefit, earned leave).
 *  - Unpaid salary: the exit month, from the 1st to the exit date (earlier months are paid through
 *    the normal salary sheet; skipped too when the exit month's salary is already locked).
 *    Pay = gross / days in month × days not absent. The form's other two month rows print blank.
 *  - Service benefit, Labour Act section 27(4) as amended: per completed year, 7 days' wage when the
 *    whole service is up to 3 years, 15 days when more than 3 and under 10, 30 days from 10 years.
 *    Only for Resign (including the auto Resign after the 3rd absence letter); Lefty and the rest get none.
 *  - Earned leave of the exit year = days present / 18, less earned leave taken that year. Earlier
 *    years' balance is not stored, so it prints blank for HR to fill by hand.
 *  - Deductions: advances not yet due before the exit (Earnings & Deductions > Advance), the
 *    advance / IOU entries and income tax of the unpaid months, and the revenue stamp.
 */
class SettlementForm
{
    private const RESIGN = ['resign', 'resigned'];

    private const EARN_LEAVE_CODES = ['EL', 'AL', 'EARN'];

    public static function build(HrEmployee $employee): array
    {
        $employee->loadMissing(['department', 'section', 'designation', 'floorLine', 'salaryInfo']);

        $sal = function_exists('hr_employee_salary') ? (hr_employee_salary($employee) ?: []) : [];
        $sal = HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $employee->id);

        $gross = round((float) ($sal['gross'] ?? 0), 2);
        $basic = round((float) ($sal['basic'] ?? 0), 2);
        $house = round((float) ($sal['house'] ?? 0), 2);
        $otherAllowance = round((float) ($sal['medical'] ?? 0) + (float) ($sal['food'] ?? 0) + (float) ($sal['transport'] ?? 0), 2);
        $otRate = (float) ($sal['ot_rate'] ?? ($basic > 0 ? round($basic / 208 * 2, 2) : 0));
        $dayWage = $gross / 30;

        $join = $employee->join_date ? Carbon::parse($employee->join_date)->startOfDay() : null;
        $exit = $employee->exited_at ? Carbon::parse($employee->exited_at)->startOfDay() : today();
        $service = $join ? $join->diff($exit) : null;
        $years = $service ? $service->y : 0;

        // ── Unpaid salary: the exit month (kept as a list, the form has three month rows) ──
        $months = [];
        $employeeDataFn = HrOptionsService::getOptionsForEmployee();
        for ($i = 0; $i >= 0; $i--) {
            $start = $exit->copy()->startOfMonth()->subMonthsNoOverflow($i);
            $from = $join && $join->gt($start) ? $join->copy() : $start;
            $to = $i === 0 ? $exit->copy() : $start->copy()->endOfMonth()->startOfDay();
            if ($from->gt($to) || HrLock::isLocked('salary', $start->year, $start->month, $employee->department_id)) {
                continue;
            }
            $d = SalaryReportService::getEmployeeSalaryData($employee, $from->toDateString(), $to->toDateString(), null, $employeeDataFn);
            $days = max(0, (int) ($d['employed_days'] ?? 0) - (int) ($d['absent'] ?? 0));
            $fullMonth = $from->isSameDay($start) && $to->isSameDay($start->copy()->endOfMonth()->startOfDay());
            $months[] = [
                'label' => $start->format('F'),
                'month' => $start,
                'days' => $days,
                'amount' => round($gross / $start->daysInMonth * $days, 2),
                'att_bonus' => $fullMonth ? (float) ($d['att_bonus'] ?? 0) : 0.0,
                'ot_hours' => (float) ($d['ot_hours'] ?? 0),
                'ot_amount' => (float) ($d['ot'] ?? 0),
                'extra_days' => (int) ($d['wph_days'] ?? 0),
                'extra_amount' => (float) ($d['wph_amount'] ?? 0),
                'iou' => (float) ($d['loan'] ?? 0),
                'tax' => (float) ($d['tax'] ?? 0),
            ];
        }
        $sum = fn (string $k) => round(array_sum(array_column($months, $k)), 2);

        // ── Service benefit, section 27(4) ──
        $resigned = in_array(strtolower((string) $employee->employment_status), self::RESIGN, true);
        $benefitDaysPerYear = match (true) {
            ! $resigned || ! $join || $years < 1 => 0,
            $service->y < 3 || ($service->y === 3 && $service->m === 0 && $service->d === 0) => 7,
            $years < 10 => 15,
            default => 30,
        };
        $benefitDays = $benefitDaysPerYear * $years;
        $benefitAmount = round($benefitDays * $dayWage, 2);

        // ── Earned leave of the exit year ──
        $yearStart = $exit->copy()->startOfYear();
        $leaveFrom = $join && $join->gt($yearStart) ? $join->copy() : $yearStart;
        $presentDays = 0;
        if ($leaveFrom->lte($exit)) {
            $pack = EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $leaveFrom->toDateString(), $exit->toDateString());
            $presentDays = (int) ($pack['summary']['totalAttendance'] ?? 0);
        }
        $earned = intdiv($presentDays, 18);
        $elTypeIds = HrLeaveInfo::query()->get(['id', 'code'])
            ->filter(fn ($t) => in_array(strtoupper(trim((string) $t->code)), self::EARN_LEAVE_CODES, true))->pluck('id');
        $taken = $elTypeIds->isEmpty() ? 0 : (int) HrEmployeeLeave::query()
            ->where('employee_id', $employee->id)->where('status', 'approved')
            ->whereIn('leave_type_id', $elTypeIds)
            ->whereDate('leave_from', '>=', $yearStart->toDateString())->whereDate('leave_from', '<=', $exit->toDateString())
            ->get()->sum(fn ($l) => (float) ($l->total_days ?? 0));
        $leaveDays = max(0, $earned - $taken);
        $leaveAmount = round($leaveDays * $dayWage, 2);

        // ── Deductions ──
        $firstUnpaid = $months ? $months[0]['month'] : $exit->copy()->addMonthNoOverflow()->startOfMonth();
        $advance = round((float) HrAdvanceApplication::query()->where('employee_id', $employee->id)
            ->where(fn ($q) => $q->whereNull('deduction_month')->orWhere('deduction_month', '>=', $firstUnpaid->toDateString()))
            ->sum('amount'), 2);
        $loan = $sum('iou');
        $tax = $sum('tax');
        $stamp = (float) (hr_factory('stamp_amount') ?? 0) ?: 10.0;

        $totalEarn = round($sum('amount') + $sum('att_bonus') + $sum('ot_amount') + $sum('extra_amount') + $benefitAmount + $leaveAmount, 2);
        $totalDeduct = round($advance + $loan + $tax + $stamp, 2);

        return [
            'date' => today(),
            'gross' => $gross,
            'basic' => $basic,
            'house' => $house,
            'other_allowance' => $otherAllowance,
            'ot_rate' => $otRate,
            'join' => $join,
            'exit' => $employee->exited_at ? $exit : null,
            'service' => $service,
            'years' => $years,
            'months' => $months,
            'att_bonus' => $sum('att_bonus'),
            'ot_hours' => $sum('ot_hours'),
            'ot_amount' => $sum('ot_amount'),
            'extra_days' => (int) $sum('extra_days'),
            'extra_amount' => $sum('extra_amount'),
            'resigned' => $resigned,
            'benefit_days_per_year' => $benefitDaysPerYear,
            'benefit_days' => $benefitDays,
            'benefit_amount' => $benefitAmount,
            'leave_year' => $exit->year,
            'leave_present_days' => $presentDays,
            'leave_earned' => $earned,
            'leave_taken' => $taken,
            'leave_days' => $leaveDays,
            'leave_amount' => $leaveAmount,
            'advance' => $advance,
            'loan' => $loan,
            'tax' => $tax,
            'stamp' => $stamp,
            'total_earn' => $totalEarn,
            'total_deduct' => $totalDeduct,
            'net' => round($totalEarn - $totalDeduct, 2),
        ];
    }
}
