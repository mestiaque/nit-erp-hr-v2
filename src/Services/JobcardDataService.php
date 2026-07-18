<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use ME\Hr\Models\HrEmployeeLeave as Leave;
use ME\Hr\Models\HrRegularToWeekend as RegularToWeekend;

class JobcardDataService {
    public function getEmployeeJobcardData($employee, $dates, $language = 'bn') {
        $rows = [];
        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin  = $allowOtHour * 60;
        $isBangla    = $language === 'bn';
        $factoryNo   = (int) (hr_factory('factory_no') ?? 0);

        // Load designation and OT basis flags once
        $designation      = $employee->designation ?? ($employee->designation_id ? \ME\Hr\Models\HrDesignation::find($employee->designation_id) : null);
        $isOtBasisWphp    = (bool) data_get($designation, 'is_ot_basis_wphp',     false);
        $isOtBasisMain    = (bool) data_get($designation, 'is_ot_basis_main',     true);
        $isOtBasisOthers1 = (bool) data_get($designation, 'is_ot_basis_others_1', true);
        $isOtBasisOthers2 = (bool) data_get($designation, 'is_ot_basis_others_2', true);
        $otEnabled = match (true) {
            ($factoryNo === 1) => $isOtBasisOthers1,
            ($factoryNo === 2) => $isOtBasisOthers2,
            default             => $isOtBasisMain,
        };

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

        // Build attendance map once for the full range, keyed by date string
        $dateStrings      = collect($dates)->map(fn($d) => $d instanceof Carbon ? $d->toDateString() : (string)$d);
        $attendanceByDate = \ME\Hr\Models\HrAttendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$dateStrings->first(), $dateStrings->last()])
            ->get()
            ->keyBy(fn($a) => $a->date instanceof Carbon ? $a->date->toDateString() : substr((string)$a->date, 0, 10));
        $getAtt = fn($uid, $dateStr) => $attendanceByDate->get($dateStr);

        // Fetch holidays and shift once
        $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
        $holidays  = collect($hrOptions['holidays'] ?? []);
        $shift     = $employee->shift ?? null;

        foreach($dates as $date) {
            $dStr = $date instanceof Carbon ? $date->toDateString() : $date;
            $att  = $attendanceByDate->get($dStr);

            // ----- STATUS -----
            $dayMeta            = $this->getStatusForDay($employee, $date, $holidays, $getAtt);
            $statusCode         = $dayMeta['code'];
            $isOnWeekend        = $dayMeta['is_weekend'];
            $isWeekendToRegular = $dayMeta['is_weekend_to_regular'];
            $statusLabel = $isBangla ? ($statusMapBn[$statusCode] ?? $statusCode) : ($statusMapEn[$statusCode] ?? $statusCode);

            // ----- SHIFT -----
            $shiftName = $isBangla ? ($shift->bn_name ?? '') : ($shift->name ?? '');

            // ----- DAY -----
            $enDay = $date instanceof Carbon ? $date->format('l') : Carbon::parse($date)->format('l');
            $bnDay = $dayNamesBn[$enDay] ?? $enDay;
            $day = $isBangla ? $bnDay : $enDay;

            $shiftEndTime = $shift && $shift->end_time ? Carbon::parse($shift->end_time) : null;
            $actualOut    = $att && $att->out_time ? Carbon::parse($att->out_time) : null;
            $fmtOrDash    = fn ($t) => $t ? ($isBangla ? bn_time($t) : $fmtTime($t)) : '-';

            // ----- In/Out time + OT, by day type and factory compliance mode -----
            $otMinRaw = $att ? (int) ($att->overtime_minutes ?? 0) : 0;
            if (!$otEnabled) {
                $otMinRaw = 0;
            }

            if ($isOnWeekend) {
                // A genuine (unconverted) weekly holiday. Compliance modes never show
                // attendance worked on a real weekend day, regardless of the WPHP flag.
                // Actual shows the real punch, and when the designation's OT basis is WPHP
                // the WHOLE worked span counts as OT (a weekend has no "regular shift"
                // portion to subtract out), not just the excess over shift end.
                if ($factoryNo === 1 || $factoryNo === 2) {
                    $intime = '-';
                    $outtime = '-';
                    $rawOtMin = 0;
                    $cappedOtMin = 0;
                    $extraOtMin = 0;
                } else {
                    $intime  = $att && $att->in_time ? $fmtOrDash($att->in_time) : '-';
                    $outtime = $fmtOrDash($actualOut);
                    if ($isOtBasisWphp && $att && $att->in_time) {
                        $otMinRaw = max($otMinRaw, (int) ($att->total_working_minute ?? 0));
                    }
                    $rawOtMin    = $otMinRaw;
                    $cappedOtMin = $rawOtMin;
                    $extraOtMin  = 0;
                }
            } elseif ($isWeekendToRegular) {
                // A weekend day converted to a working day is shown exactly like an
                // ordinary shift day — capped at the shift's own hours, zero OT — in every
                // factory mode. The real extra time worked is compensated separately via
                // the Weekend-to-Regular allowance, not through the job card's OT columns.
                $intime  = $att && $att->in_time ? $fmtOrDash($att->in_time) : '-';
                $outtime = $fmtOrDash($shiftEndTime ?: $actualOut);
                $rawOtMin    = 0;
                $cappedOtMin = 0;
                $extraOtMin  = 0;
            } else {
                // Regular working day (also covers holiday/leave/absent rows, where
                // in_time/out_time are naturally empty).
                $intime = $att && $att->in_time ? $fmtOrDash($att->in_time) : '-';

                $displayOut = $actualOut;
                if ($att && $att->out_time && $factoryNo == 1 && $shiftEndTime) {
                    $maxOut = $shiftEndTime->copy()->addHours((float) $allowOtHour);
                    $displayOut = $actualOut->gt($maxOut) ? $maxOut : $actualOut;
                }
                $outtime = $fmtOrDash($displayOut);

                $rawOtMin    = $otMinRaw;
                $cappedOtMin = ($factoryNo === 1 || $factoryNo === 2) ? min($rawOtMin, $allowOtMin) : $rawOtMin;
                $extraOtMin  = ($factoryNo === 2 && $rawOtMin > $allowOtMin) ? ($rawOtMin - $allowOtMin) : 0;
            }

            $actualOt     = $fmtOT($rawOtMin);
            $complianceOt = $fmtOT($cappedOtMin);
            $extraOt      = $fmtOT($extraOtMin);

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
    public function getSectionJobcardData($employees, $dates, $language = 'bn')
    {
        $data = [];
        foreach ($employees as $employee) {
            $data[$employee->id] = $this->getEmployeeJobcardData($employee, $dates, $language);
        }
        return $data;
    }

    /**
     * Returns ['code' => status code, 'is_weekend' => genuine unconverted weekly holiday,
     * 'is_weekend_to_regular' => weekend day explicitly converted to a working day].
     * is_weekend and is_weekend_to_regular are mutually exclusive by construction — a
     * weekend-to-regular day never resolves to the 'WO' code below.
     */
    public function getStatusForDay($employee, $date, $holidays, $getAtt): array
    {
        $att = $getAtt($employee->id, $date->toDateString());
        // leave check
        $leave = Leave::where('employee_id', $employee->id)
            ->whereDate('leave_from', '<=', $date->toDateString())
            ->whereDate('leave_to', '>=', $date->toDateString())->first();
        if ($leave) return ['code' => 'L', 'is_weekend' => false, 'is_weekend_to_regular' => false];

        $dateStr = $date->format('Y-m-d');
        $isHoliday = $holidays->contains(function($h) use ($dateStr) {
            return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
        });
        if($isHoliday) return ['code' => 'GH', 'is_weekend' => false, 'is_weekend_to_regular' => false];

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
            ->where('type', 'regular')
            ->where('status', 1)
            ->exists();

        // For compliance, do NOT treat weekend2regular as regular (weekend will always be shown except for null/0 factory)
        $factoryNo = hr_factory('factory_no');
        $isWeekend = false;
        if ($factoryNo == 1 || $factoryNo == 2) {
            if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                // Do not treat as weekend, treat as working
            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                $isWeekend = true;
            }
        } else { // factory null / 0: treat weekend2regular as regular day!
            if ($isRegularToWeekend) $isWeekend = true;
            elseif ($dayOfWeek === $empWeekend && !$isWeekendToRegular) $isWeekend = true;
            // else: treat as regular working
        }

        if ($isWeekend) {
            return ['code' => 'WO', 'is_weekend' => true, 'is_weekend_to_regular' => false];
        }

        $code = $att ? ($att->status ?: ($att->in_time ? 'P' : 'A')) : 'A';
        return ['code' => $code, 'is_weekend' => false, 'is_weekend_to_regular' => $isWeekendToRegular];
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

        $factoryNo   = hr_factory('factory_no');
        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $shift       = $employee->shift ?? null;

        $infoRows = $this->getEmployeeJobcardData($employee, $dates, $language);
        $holidays = collect(\ME\Hr\Services\HrOptionsService::getOptions()['holidays'] ?? []);
        $summary  = method_exists($this, 'getEmployeeSummary')
            ? $this->getEmployeeSummary($infoRows, $dates, $holidays, $employee, $shift, $factoryNo, $allowOtHour, $language)
            : [];

        // Also return employeeData for Blade info table
        $employeeData = [
            'company_name'  => $employee->company_name  ?? '',
            'company_address' => $employee->company_address ?? '',
            'employee_id'   => $employee->employee_id   ?? '',
            'department'    => optional($employee->department)->name ?? '',
            'employee_name' => $employee->name          ?? '',
            'section'       => optional($employee->section)->name ?? '',
            'job_type'      => $employee->job_type      ?? '',
            'designation'   => optional($employee->designation)->name ?? '',
            'joining_date'  => $employee->joining_date  ?? '',
        ];

        return compact('employee', 'employeeData', 'infoRows', 'summary', 'dates', 'factoryNo', 'allowOtHour', 'holidays', 'shift');
    }
}
