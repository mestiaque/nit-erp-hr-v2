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
           $employee = \ME\Hr\Models\HrEmployee::findOrFail($employeeId);
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        $factoryNo = hr_factory('factory_no');
        $holidays = \ME\Hr\Services\HrOptionsService::getOptions()['holidays'] ?? collect();

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
            'casual' => 0,
            'sick' => 0,
            'earned' => 0,
            'weekly' => 0,
            'festival' => 0,
            'general' => 0,
            'maternity' => 0,
        ];

        $leaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->whereDate('leave_from', '<=', $toDate)
            ->whereDate('leave_to', '>=', $fromDate)
            ->get();

        $leaveByDate = [];
        foreach ($leaves as $leave) {
            $leaveFrom = Carbon::parse($leave->leave_from)->max($from)->startOfDay();
            $leaveTo = Carbon::parse($leave->leave_to)->min($to)->startOfDay();

            for ($leaveDate = $leaveFrom->copy(); $leaveDate->lte($leaveTo); $leaveDate->addDay()) {
                $leaveByDate[$leaveDate->format('Y-m-d')] = $leave;
            }
        }

        $weekendToRegularDays = 0;
        $weekendToRegularOtMinutes = 0;

        if (!$attendanceMap) $attendanceMap = collect();
        if (!$holidays) $holidays = collect();

        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin = $allowOtHour * 60;

        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');

        $result = [];

        foreach ($dates as $d) {
            $dateStr = $d->format('Y-m-d');
            // $att = ($attendanceMap->get($employee->id . '_' . $dateStr) ?? collect())->first();
            $att = $attendanceMap[$employee->id . '_' . $dateStr] ?? null;

            // Leave
            $leave = $leaveByDate[$dateStr] ?? null;
            if ($leave) {
                $leaveBucket = self::normalizeLeaveType($leave);
                $leaveSummary[$leaveBucket] = ($leaveSummary[$leaveBucket] ?? 0) + 1;
            }

            // Holiday
            $isHoliday = $holidays->contains(function ($h) use ($dateStr) {
                return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
            });

            // Weekend / Regular To Weekend / Weekend To Regular
            $dayOfWeek = strtolower($d->format('l'));
            $isRegularToWeekend = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)
                ->where('type', 'weekend')
                ->where('status', 1)
                ->exists();
            $isWeekendToRegular = RegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)
                ->where('type', 'half_day')
                ->where('status', 1)
                ->exists();

            // Determine isWeekend, isWeekendForCompliance
            $isWeekend = false;
            $isWeekendForCompliance = false;
            if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                // Normally weekend, but set to regular ONLy for factoryNo 0/null
                $isWeekend = false;
                $isWeekendForCompliance = ($factoryNo == 1 || $factoryNo == 2);
            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                $isWeekend = true;
                $isWeekendForCompliance = true;
            }

            // Decide status string
            if ($leave) {
                $status = 'leave';
                $status_display = 'Leave';
            }elseif (($factoryNo == 1 || $factoryNo == 2) && $isWeekendForCompliance) {
                $status = 'weekend';
                $status_display = 'Weekend';
            } elseif ($isHoliday) {
                $status = 'holiday';
                $status_display = 'Holiday';
            } elseif ($isWeekend) {
                $status = 'weekend';
                $status_display = 'Weekend';
            } elseif ($att) {
                if (!empty($att->status)) {
                    $status = str_replace(' ', '_', strtolower($att->status));
                    $status_display = ucwords(str_replace('_', ' ', $status));
                } else {
                    $status = 'present';
                    $status_display = 'Present';
                }
            } else {
                $status = 'absent';
                $status_display = 'Absent';
            }

            // For Factory 1/2: If this day is weekend for compliance, then **do not count OT or in/out**
            if (($factoryNo == 1 || $factoryNo == 2) && $isWeekendForCompliance) {
                $inTime = null;
                $outTime = null;
                $complianceOt = 0;
                $extraOt = ($factoryNo == 2) ? 0 : null;
            } else {
                // Show real time values
                $inTime = $att && $att->in_time ? $att->in_time : null;
                $outTime = $att && $att->out_time ? $att->out_time : null;
            }

            // OT calculations
            $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
            $actualOt = round($otMinRaw / 60, 2); // True OT (not capped)
            // Compliance and extra OT logic
            if ($factoryNo == 1) {
                $complianceOt = ($isWeekendForCompliance) ? 0 : round(min($otMinRaw, $allowOtMin) / 60, 2);
                $extraOt = null;
            } elseif ($factoryNo == 2) {
                $complianceOt = ($isWeekendForCompliance) ? 0 : round(min($otMinRaw, $allowOtMin) / 60, 2);
                $extraOt = ($isWeekendForCompliance) ? 0 : ($otMinRaw > $allowOtMin ? round(($otMinRaw - $allowOtMin) / 60, 2) : 0);
            } else {
                // Factory 0/null
                $complianceOt = $actualOt;
                $extraOt = null;
            }

            if ($isWeekendToRegular && $att && ($att->in_time || $att->out_time || $otMinRaw > 0)) {
                $weekendToRegularDays++;
                $weekendToRegularOtMinutes += max($otMinRaw, 0);
            }

            $result[] = [
                'date' => $d->format('d-m-Y'),
                'day' => $d->format('l'),
                'shift' => $att && isset($att->shift->name) ? $att->shift->name : null,
                'in_time' => $inTime ?? '-',
                'out_time' => $outTime ?? '-',
                'status_key' => $status,
                'status' => $status_display,
                'compliance_ot' => $complianceOt,
                'extra_ot' => $extraOt,
                'remarks' => $att->remarks ?? '',
            ];
        }


        $totals = [
            'totalDays' => count($dates),
            'totalGovHolidays' => 0,
            'totalWeekendDays' => 0,
            'totalWorkingDays' => 0,
            'totalAbsent' => 0,
            'totalLeave' => 0,
            'totalPresent' => 0,
            'totalPresentAll' => 0, // including holidays/weekends with in_time
            'totalLate' => 0,
            'totalPM' => 0,
            'totalEO' => 0,
            'totalLEO' => 0,
            'totalLPM' => 0,
            'totalAttendance' => 0,
            'totalOt' => 0,
            'totalComplianceOt' => 0,
            'totalExtraOt' => 0,
        ];
        // Weekend info
        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');

        foreach ($dates as $idx => $d) {
            $row = $result[$idx]; // assuming you fill $result[] as above

            $dateStr = $d->format('Y-m-d');
            $att = $attendanceMap->get($employee->id . '_' . $dateStr);

            $totals['totalOt'] += $att ? round(($att->overtime_minutes ?? 0) / 60, 2) : 0;
            $totals['totalComplianceOt'] += $row['compliance_ot'] ?? 0;
            $totals['totalExtraOt'] += $row['extra_ot'] ?? 0;

            $dayOfWeek = strtolower($d->format('l'));
            $isHoliday = $holidays->contains(function ($h) use ($dateStr) {
                return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
            });
            $isWeekend = ($dayOfWeek === $empWeekend);

            if ($isHoliday) $totals['totalGovHolidays']++;
            elseif ($isWeekend) $totals['totalWeekendDays']++;
            else $totals['totalWorkingDays']++;

            if ($row['status_key'] === 'leave') $totals['totalLeave']++;
            if ($row['status_key'] === 'absent') $totals['totalAbsent']++;
            if ($row['status_key'] === 'present') $totals['totalPresent']++;
            if ($row['status_key'] === 'late') $totals['totalLate']++;
            if ($row['status_key'] === 'punch_missing') $totals['totalPM']++;
            if ($row['status_key'] === 'early_exit') $totals['totalEO']++;
            if ($row['status_key'] === 'late_and_early_exit') $totals['totalLEO']++;
            if ($row['status_key'] === 'late_and_punch_missing') $totals['totalLPM']++;

            // Mark attendance (in_time present and not holiday or weekend)
            if ($row['in_time'] && $row['in_time'] !== '-' && !$isHoliday && !$isWeekend) $totals['totalAttendance']++;
            if ($row['in_time'] && $row['in_time'] !== '-') $totals['totalPresentAll']++;


            // Status details from att record (if available)
            if ($att) {
                $st = strtoupper($att->status ?? '');
                // dd("Date: $dateStr, Status: $st");
                // if ($st === 'LATE') $totals['totalLate']++;
                if ($st === 'PM' || $st == 'PUNCH MISSING') $totals['totalPM']++;
                if ($st === 'EO' || $st == 'EARLY EXIT') $totals['totalEO']++;
                if ($st === 'LEO' || $st == 'LATE AND EARLY EXIT') $totals['totalLEO']++;
                if ($st === 'LPM' || $st == 'LATE AND PUNCH MISSING') $totals['totalLPM']++;
            }
        }

        // Payslip requirement: weekly leave should reflect monthly weekends,
        // and festival leave should reflect factory holidays in the range.
        $leaveSummary['weekly'] = (int) ($totals['totalWeekendDays'] ?? 0);
        $leaveSummary['festival'] = (int) ($totals['totalGovHolidays'] ?? 0);

        $weekendToRegular = self::calculateWeekendToRegularAllowance($employee, [
            'weekend_to_regular_days' => $weekendToRegularDays,
            'weekend_to_regular_ot_minutes' => $weekendToRegularOtMinutes,
            'working_days' => $totals['totalWorkingDays'] ?? 0,
        ]);

        $totals['totalWeekendToRegularDays'] = $weekendToRegularDays;
        $totals['totalWeekendToRegularOtHours'] = round($weekendToRegularOtMinutes / 60, 2);
        $totals['totalWeekendToRegularAmount'] = $weekendToRegular['amount'] ?? 0;

        return [
            'attendance' => $result,
            'summary' => $totals,
            'leave' => $leaveSummary,
            'weekend_to_regular' => $weekendToRegular,
        ];

        // return $result;
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
