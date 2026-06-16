<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrRegularToWeekend as RegularToWeekend;

class JobcardDataService {
    public function getEmployeeJobcardData($employee, $dates) {
        $rows = [];
        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin = $allowOtHour * 60;
        $t = fn (string $bn, string $en) => ($language ?? data_get($request ?? null, 'language', 'bn')) === 'bn' ? $bn : $en;
        $isBangla = ($language ?? data_get($request ?? null, 'language', 'bn')) === 'bn';
        $factoryNo = hr_factory('factory_no');

        // Use these for translations if needed
        $dayNamesBn = [
            'Saturday'   => 'শনিবার',
            'Sunday'     => 'রবিবার',
            'Monday'     => 'সোমবার',
            'Tuesday'    => 'মঙ্গলবার',
            'Wednesday'  => 'বুধবার',
            'Thursday'   => 'বৃহস��পতিবার',
            'Friday'     => 'শুক্রবার',
        ];
        $statusMapBn = [
            'P'   => 'উপস্থিত',
            'A'   => 'অনুপস্থিত',
            'EO'  => 'আগে বের হয়েছে',
            'WO'  => 'সাপ্তাহিক ছুটি',
            'GH'  => 'সরকারি ছুটি',
            'L'   => 'ছুটি',
            'PM'  => 'পাঞ্চ মিসিং',
            'LATE'=> 'বিলম্ব',
            'LEO' => 'বিলম্ব ও আগে বের',
            'LPM' => 'বিলম্ব ও পাঞ্চ মিসিং',
        ];
        $statusMapEn = [
            'P'   => 'Present',
            'A'   => 'Absent',
            'EO'  => 'Early Out',
            'WO'  => 'Weekend',
            'GH'  => 'Govt Holiday',
            'L'   => 'Leave',
            'PM'  => 'Punch Missing',
            'LATE'=> 'Late',
            'LEO' => 'Late & Early Out',
            'LPM' => 'Late & Punch Missing',
        ];

        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');

        $fmtTime = function($t) { return $t ? Carbon::parse($t)->format('h:i A') : '-'; };
        $fmtOT   = function($min) { return $min > 0 ? number_format($min / 60, 2) : '0.00'; };

        // Helper to get attendance for date


        foreach($dates as $date) {
            $attendanceMap = \ME\Hr\Models\HrAttendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('employee_id');
            $getAtt = function($uid, $dateStr) use ($attendanceMap) {
                /** @var Collection $attendanceMap */
                return ($attendanceMap->get($uid . '_' . $dateStr) ?? collect())->first();
            };
            $dStr = $date instanceof Carbon ? $date->toDateString() : $date;
            $att = $getAtt($employee->id, $dStr);
            // Fetch holidays from HrOptionsService
            $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
            $holidays = collect($hrOptions['holidays'] ?? []);
            // Fetch shift from employee
            $shift = $employee->shift ?? null;

            // ----- STATUS -----
            $statusCode = $this->getStatusForDay($employee, $date, $holidays, $getAtt);
            $statusLabel = $isBangla ? ($statusMapBn[$statusCode] ?? $statusCode) : ($statusMapEn[$statusCode] ?? $statusCode);

            // ----- SHIFT -----
            $shiftName = $isBangla ? ($shift->bn_name ?? '') : ($shift->name ?? '');

            // ----- DAY -----
            $enDay = $date instanceof Carbon ? $date->format('l') : Carbon::parse($date)->format('l');
            $bnDay = $dayNamesBn[$enDay] ?? $enDay;
            $day = $isBangla ? $bnDay : $enDay;

            // ----- In/Out time -----
            $intime = $att && $att->in_time ? ($isBangla ? bn_time($att->in_time) : $fmtTime($att->in_time)) : '-';
            // Out time: factory rules apply
            $displayOut = null;
            $actualOut = $att && $att->out_time ? Carbon::parse($att->out_time) : null;
            $shiftEndTime = $shift && $shift->end_time ? Carbon::parse($shift->end_time) : null;
            if($att && $att->out_time){
                if($factoryNo == 1){
                    if ($shiftEndTime) {
                        $maxOut = $shiftEndTime->copy()->addHours((float)$allowOtHour);
                        $displayOut = $actualOut->gt($maxOut) ? $maxOut : $actualOut;
                    } else {
                        $displayOut = $actualOut;
                    }
                } elseif ($factoryNo == 2) {
                    $displayOut = $actualOut; // always actual
                } elseif ($factoryNo === null || $factoryNo == 0) {
                    $displayOut = $actualOut; // for null/0, just show actual (regular logic)
                } else {
                    $displayOut = $actualOut;
                }
            }
            $outtime = $displayOut ? ($isBangla ? bn_time($displayOut) : $fmtTime($displayOut)) : ($att && $att->out_time ? ($isBangla ? bn_time($att->out_time) : $fmtTime($att->out_time)) : '-');

            // ----- OT -------
            $otMinRaw = $att ? (int)($att->total_ot_minute ?? 0) : 0;
            $rawOtMin = $otMinRaw;
            $cappedOtMin = ($factoryNo == 1 || $factoryNo == 2) ? min($rawOtMin, $allowOtMin) : $rawOtMin;
            $extraOtMin = ($factoryNo == 2 && $rawOtMin > $allowOtMin) ? ($rawOtMin - $allowOtMin) : 0;

            // For factory null/0 -- punch in to punch out is all actual ot
            // For 1/2 -- compliance ot is capped OT (capped at allowOtMin), actual_ot is att->overtime_minutes, extra_ot for 2 if any
            $actualOt = $fmtOT($rawOtMin);
            $complianceOt = $fmtOT($cappedOtMin);
            $extraOt = $fmtOT($extraOtMin);

            // All relevant data row
            $rows[] = [
                'date' => $isBangla ? bn_date($date) : ($date instanceof Carbon ? $date->format('d/m/Y') : $date),
                'status_code' => $statusCode,
                'status_label' => $statusLabel,
                'shift' => $shiftName,
                'day' => $day,
                'intime' => $intime,
                'outtime' => $outtime,
                'actual_ot' => $actualOt,
                'compliance_ot' => $complianceOt,
                'extra_ot' => $extraOt,
                'remarks' => $att->remarks ?? '',
                'in_time_raw' => $att->in_time ?? null,
                'out_time_raw' => $att->out_time ?? null,
            ];
        }

        return $rows;
    }


    // SECTION version: all employees in section, keyed by emp ID
    public function getSectionJobcardData($employees, $dates, $attendanceMap, $holidays, $shiftMap, $factoryNo, $allowOtHour = 2, $language = 'bn')
    {
        $data = [];
        foreach ($employees as $employee) {
            $shift = $shiftMap[$employee->id] ?? null;
            $data[$employee->id] = $this->getEmployeeJobcardData($employee, $dates, $attendanceMap, $holidays, $shift, $factoryNo, $allowOtHour, $language);
        }
        return $data;
    }

    // The status logic --- copy your getStatus
    public function getStatusForDay($employee, $date, $holidays, $getAtt)
    {
        $att = $getAtt($employee->id, $date->toDateString());
        // leave check
        $leave = Leave::where('employee_id', $employee->id)
            ->whereDate('leave_from', '<=', $date->toDateString())
            ->whereDate('leave_to', '>=', $date->toDateString())->first();
        if ($leave) return 'L';

        $dateStr = $date->format('Y-m-d');
        $isHoliday = $holidays->contains(function($h) use ($dateStr) {
            return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
        });
        if($isHoliday) return 'GH';

        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
        $dayOfWeek = strtolower($date->format('l'));

        // RegularToWeekend
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

        // For compliance, do NOT treat weekend2regular as regular (weekend will always be shown except for null/0 factory)
        $factoryNo = hr_factory('factory_no');
        if ($factoryNo == 1 || $factoryNo == 2) {
            if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                // Do not treat as weekend, treat as working
            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                return 'WO';
            }
        } else { // factory null / 0: treat weekend2regular as regular day!
            if ($isRegularToWeekend) return 'WO';
            elseif ($dayOfWeek === $empWeekend && !$isWeekendToRegular) return 'WO';
            // else: treat as regular working
        }

        return $att ? ($att->status ?: ($att->in_time ? 'P' : 'A')) : 'A';
    }


        /**
     * New method: Accepts only employee ID, from, to, and language. Fetches all required data internally.
     */
    public function getEmployeeJobcardDataById($employeeId, $from, $to, $language = 'bn') {
        $employee = \ME\Hr\Models\HrEmployee::findOrFail($employeeId);
        $dates = collect();
        $cur = \Carbon\Carbon::parse($from);
        $end = \Carbon\Carbon::parse($to);
        while ($cur->lte($end)) {
            $dates->push($cur->copy());
            $cur->addDay();
        }

        $attendanceMap = \ME\Hr\Models\HrAttendance::query()
            ->where('employee_id', $employeeId)
            ->whereBetween('date', [$from, $to])
            ->get()
            ->groupBy(fn ($a) => $a->employee_id . '_' . $a->date);

        // Holidays logic (mimic old logic, adjust as needed)
        $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
        $holidays = collect($hrOptions['holidays'] ?? []);

        // Shift logic (mimic old logic, adjust as needed)
        $shift = $employee->shift ?? null;

        $factoryNo = hr_factory('factory_no');
        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;

        $infoRows = $this->getEmployeeJobcardData(
            $employee,
            $dates,
            $attendanceMap,
            $holidays,
            $shift,
            $factoryNo,
            $allowOtHour,
            $language
        );
        $summary = method_exists($this, 'getEmployeeSummary')
            ? $this->getEmployeeSummary($infoRows, $dates, $holidays, $employee, $shift, $factoryNo, $allowOtHour, $language)
            : [];

        // Also return employeeData for Blade info table
        $employeeData = [
            'company_name' => $employee->company_name ?? '',
            'company_address' => $employee->company_address ?? '',
            'employee_id' => $employee->employee_id ?? '',
            'department' => optional($employee->department)->name ?? '',
            'employee_name' => $employee->name ?? '',
            'section' => optional($employee->section)->name ?? '',
            'job_type' => $employee->job_type ?? '',
            'designation' => optional($employee->designation)->name ?? '',
            'joining_date' => $employee->joining_date ?? '',
        ];

        return compact('employee', 'employeeData', 'infoRows', 'summary', 'dates', 'factoryNo', 'allowOtHour', 'holidays', 'shift');
    }
}
