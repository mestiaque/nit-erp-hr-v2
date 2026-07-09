<?php

namespace ME\Hr\Services;

use Illuminate\Support\Facades\Schema;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeLeave;
use ME\Hr\Models\HrEmployeeOtherTransaction;
use ME\Hr\Models\HrEmployeeSalaryIncrement;
use ME\Hr\Models\HrHoliday;
use ME\Hr\Models\HrLeaveInfo;
use ME\Hr\Models\HrRegularToWeekend;

class FinalSettlementCalculator
{
    /**
     * Compute suggested Final Settlement figures for an employee (unpaid salary,
     * earned-leave encashment, gratuity, outstanding advances). Pure calculation —
     * nothing is persisted here; the caller decides whether/how to use the numbers.
     *
     * Gratuity follows the standard Bangladesh Labour Act 2006 rule: employees with
     * 5+ completed years of continuous service receive 30 days' wages (i.e. one
     * month's last-drawn basic salary) per completed year of service.
     */
    public static function suggest(HrEmployee $employee): array
    {
        $sal = [];
        if (function_exists('hr_employee_salary')) {
            $sal = hr_employee_salary($employee) ?: [];
            $sal = HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $employee->id);
        }

        $lastBasic = (float) ($sal['basic'] ?? 0);
        $lastGross = (float) ($sal['gross'] ?? 0);

        $joinDate = $employee->join_date ? \Carbon\Carbon::parse($employee->join_date) : null;
        $exitDate = $employee->exited_at ? \Carbon\Carbon::parse($employee->exited_at) : now();

        $serviceYears = $joinDate ? (int) $joinDate->diffInYears($exitDate) : 0;

        // ── Gratuity: 5+ completed years -> 1 month's last-drawn basic per year ──
        $meetsServiceRule = $serviceYears >= 5;
        $gratuityEligible = $meetsServiceRule && $lastBasic > 0;
        $gratuityAmount = $gratuityEligible ? round($lastBasic * $serviceYears, 2) : 0.0;
        $gratuityNote = match (true) {
            !$joinDate => 'Join date not set — cannot determine service length.',
            $gratuityEligible => "{$serviceYears} completed year(s) of service — eligible for gratuity (30 days' basic per year).",
            $meetsServiceRule => "{$serviceYears} completed year(s) of service — eligible by service length, but last-drawn basic salary is unknown/zero, so gratuity could not be calculated.",
            default => "{$serviceYears} completed year(s) of service — gratuity requires 5+ years, not eligible.",
        };

        // ── Earned leave encashment (same accrual/taken convention as HrEmployeeController::leavesPrint()) ──
        $earnLeaveDays = self::calculateYearlyEarnLeave($employee);
        $earnCodes = ['EL', 'AL', 'EARN'];
        $earnLeaveTypeIds = Schema::hasTable((new HrLeaveInfo())->getTable())
            ? HrLeaveInfo::query()
                ->get(['id', 'code'])
                ->filter(fn ($lt) => in_array(strtoupper(trim($lt->code ?? '')), $earnCodes))
                ->pluck('id')
            : collect();

        $takenEarnDays = $earnLeaveTypeIds->isNotEmpty()
            ? (int) round(
                HrEmployeeLeave::query()
                    ->where('employee_id', $employee->id)
                    ->whereIn('leave_type_id', $earnLeaveTypeIds)
                    ->get()
                    ->sum(fn ($r) => (float) ($r->total_days ?? 0))
            )
            : 0;

        $leaveEncashmentDays = max($earnLeaveDays - $takenEarnDays, 0);
        $leaveEncashmentAmount = $lastGross > 0 ? round($leaveEncashmentDays * ($lastGross / 30), 2) : 0.0;

        // ── Unpaid salary for the partial exit month ──
        // Day-of-month already equals "days from the 1st through this date inclusive" —
        // avoids diffInDays(), whose sign/precision behaviour varies across Carbon versions.
        $unpaidSalaryDays = (int) $exitDate->day;
        $unpaidSalaryAmount = $lastGross > 0 ? round($unpaidSalaryDays * ($lastGross / 30), 2) : 0.0;

        // ── Outstanding advances/IOUs ──
        $advanceDeduction = (float) HrEmployeeOtherTransaction::query()
            ->where('employee_id', $employee->id)
            ->sum('advance_iou');

        $netPayable = round(
            $unpaidSalaryAmount + $leaveEncashmentAmount + $gratuityAmount - $advanceDeduction,
            2
        );

        return [
            'last_basic_salary' => $lastBasic ?: null,
            'last_gross_salary' => $lastGross ?: null,
            'service_years' => $serviceYears,
            'unpaid_salary_days' => $unpaidSalaryDays,
            'unpaid_salary_amount' => $unpaidSalaryAmount,
            'leave_encashment_days' => $leaveEncashmentDays,
            'leave_encashment_amount' => $leaveEncashmentAmount,
            'gratuity_amount' => $gratuityAmount,
            'advance_deduction' => round($advanceDeduction, 2),
            'other_earnings' => 0,
            'other_deductions' => 0,
            'net_payable' => $netPayable,
            'notes' => $gratuityNote,
        ];
    }

    /**
     * Earned-leave accrual for the current year: 1 day per 18 days actually worked
     * (excluding weekends/holidays), per the standard Bangladesh Labour Act formula.
     * Mirrors HrEmployeeController::calculateYearlyEarnLeave() exactly.
     */
    public static function calculateYearlyEarnLeave(HrEmployee $employee): int
    {
        $year      = now()->year;
        $yearStart = "{$year}-01-01";
        $yearEnd   = "{$year}-12-31";
        $today     = now()->format('Y-m-d');
        $scanEnd   = $today < $yearEnd ? $today : $yearEnd;

        $empWeekend = strtolower($employee->weekend ?? 'friday');

        $holidays = HrHoliday::query()
            ->where('status', 1)
            ->where('from_date', '<=', $yearEnd)
            ->where('to_date',   '>=', $yearStart)
            ->get(['from_date', 'to_date']);

        $rtwByDate = HrRegularToWeekend::query()
            ->where('section_id', $employee->section_id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->where('status', 1)
            ->get(['date', 'type'])
            ->keyBy(fn ($r) => (string) $r->date);

        $attendedDates = array_flip(
            HrAttendance::query()
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$yearStart, $scanEnd])
                ->whereNotNull('in_time')
                ->pluck('date')
                ->map(fn ($d) => (string) $d)
                ->toArray()
        );

        $attendCount = 0;
        $current     = \Carbon\Carbon::parse($yearStart);
        $end         = \Carbon\Carbon::parse($scanEnd);

        while ($current->lte($end)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = strtolower($current->format('l'));
            $swap      = $rtwByDate->get($dateStr);

            $isRegularToWeekend = $swap && $swap->type === 'weekend';
            $isWeekendToRegular = $swap && $swap->type === 'half_day';

            $isWeekendDay = ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || $isRegularToWeekend;
            $isHoliday    = $holidays->contains(fn ($h) => $dateStr >= $h->from_date && $dateStr <= $h->to_date);

            if (!$isWeekendDay && !$isHoliday && array_key_exists($dateStr, $attendedDates)) {
                $attendCount++;
            }

            $current->addDay();
        }

        return (int) floor(($attendCount / 18) * 30);
    }
}
