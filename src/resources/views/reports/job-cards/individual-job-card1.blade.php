@if(in_array($reportType, ['job-card', 'job-card-lock']))
    @php
        // ---- Helper Functions ----
        $t = fn (string $bn, string $en) => ($language ?? data_get($request ?? null, 'language', 'bn')) === 'bn' ? $bn : $en;
        $allowOtHour = hr_factory('allow_ot_hour') ?? 2;
        $allowOtMin = $allowOtHour * 60;
        $fmtTime   = fn($t) => $t ? Carbon\Carbon::parse($t)->format('h:i A') : '-';
        $fmtOT     = fn($min) => $min > 0 ? number_format($min / 60, 2) : '0.00';
        $getAtt    = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
        $dayName   = function(Carbon\Carbon $d, $isBangla) {
            $en = $d->format('l');
            if ($isBangla) {
                $bnMap = [
                    'Saturday'   => 'শনিবার',
                    'Sunday'     => 'রবিবার',
                    'Monday'     => 'সোমবার',
                    'Tuesday'    => 'মঙ্গলবার',
                    'Wednesday'  => 'বুধবার',
                    'Thursday'   => 'বৃহস্পতিবার',
                    'Friday'     => 'শুক্রবার',
                ];
                return $bnMap[$en] ?? $en;
            }
            return $en;
        };

        $statusMap = [
            'Present'  => $t('উপস্থিত', 'Present'),
            'A'  => $t('অনুপস্থিত', 'Absent'),
            'EO' => $t('আগে বের হয়েছে', 'Early Out'),
            'WO' => $t('সাপ্তাহিক ছুটি', 'Weekly Off'),
            'GH' => $t('সরকারি ছুটি', 'Govt Holiday'),
            'L'  => $t('ছুটি', 'Leave'),
        ];
        $getStatus = function($employee, $date, $holidays, $getAtt) {
            $att = $getAtt($employee->id, $date->toDateString());
            // leave found?
            $leave = \ME\Hr\Models\HrEmployeeLeave::where('employee_id', $employee->id)
                ->whereDate('leave_from', '<=', $date->toDateString())
                ->whereDate('leave_to', '>=', $date->toDateString())->first();

            if ($leave) return 'L';

            $dateStr = $date->format('Y-m-d');
            $isHoliday = $holidays->contains(function($h) use ($dateStr) {
                return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
            });
            if ($isHoliday) return 'GH';

            $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
            $dayOfWeek = strtolower($date->format('l'));

            // RegularToWeekend logic
            $isRegularToWeekend = \ME\Hr\Models\HrRegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)
                ->where('type', 'weekend')
                ->where('status', 1)
                ->exists();
            $isWeekendToRegular = \ME\Hr\Models\HrRegularToWeekend::where('section_id', $employee->section_id)
                ->where('date', $dateStr)
                ->where('type', 'regular')
                ->where('status', 1)
                ->exists();

            // If it's normally weekend, but set to regular, treat as regular
            if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                // Do not treat as weekend
            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                return 'WO';
            }

            return $att ? ($att->status ?: ($att->in_time ? 'P' : 'A')) : 'A';
        };
        $calcOtHrs = function($otMinRaw, $factoryNo, $fmtOT) use ($allowOtMin) {
            if ($factoryNo == null || $factoryNo == 0) return $fmtOT($otMinRaw);
            if ($factoryNo == 1) return $fmtOT(min($otMinRaw, $allowOtMin)); // max allowOtMin hrs
            if ($factoryNo == 2) return $fmtOT(min($otMinRaw, $allowOtMin )); // max allowOtMin hrs
            return $fmtOT($otMinRaw);
        };
        // ---- End helpers ----
    @endphp

    @forelse($employees as $employee)
        @php
            $isLocked = false;
            if ($reportType === 'job-card-lock') {
                $other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true);
                $lockKey = 'job_card_lock';
                $key = $from . '_' . $to;
                $isLocked = !empty($other[$lockKey][$key]);
            }
            // Employee data
            $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
            $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
            $language = $language ?? data_get($request ?? null, 'language', 'bn');
            $isBangla = $language === 'bn';
            $na = $t('প্রযোজ্য নয়', 'N/A');
            $companyName = $employeeData['company_name'];
            $companyAddress = $employeeData['company_address'];
            $designation = $employeeData['designation'] ?? $na;
            $department = $employeeData['department'] ?? $na;
            $section = $employeeData['section'] ?? $na;
            $employeeName = $employeeData['employee_name'];
            $shift = $employeeData['shift'] ?? null;
            $factoryNo = hr_factory('factory_no');
            $classification = $employeeData['job_type'] ?? $na;
            $holidays = $hrOptions['holidays'] ?? collect();

            $shiftStart = $shift->shift_starting_time ?? null;
            $shiftEnd = $shift->shift_closing_time ?? null;
            $shiftStartDisplay = $isBangla ? bn_time($shiftStart) : ($shiftStart ? Carbon\Carbon::parse($shiftStart)->format('h:i A') : '-');
            $shiftEndDisplay   = $isBangla ? bn_time($shiftEnd)   : ($shiftEnd   ? Carbon\Carbon::parse($shiftEnd)->format('h:i A')   : '-');
        @endphp

        <div class="report-head">
            <h3>{{ $companyName }}</h3>
            <p>{{ $companyAddress }}</p>
        </div>

        <div class="sub-title">
            {{ $t('জব কার্ড', 'Job Card') }} {{ $reportType === 'job-card-lock' ? $t('(লক)', '(Lock)') : '' }}
            ({{ $t(bn_date($fromLabel), $fromLabel) }} {{ $t('থেকে', 'To') }} {{ $t(bn_date($toLabel), $toLabel) }})
            @if($isLocked) <span class="badge-lock">{{ $t('লকড', 'LOCKED') }}</span> @endif
        </div>

        <table class="info-grid">
            <tr>
                <td>{{ $t('কর্মী আইডি', 'Employee ID') }}</td><td>{{ $employee->employee_id }}</td>
                <td>{{ $t('বিভাগ', 'Department') }}</td><td>{{ $department }}</td>
            </tr>
            <tr>
                <td>{{ $t('নাম', 'Name') }}</td><td>{{ $employeeName }}</td>
                <td>{{ $t('সেকশন', 'Section') }}</td><td>{{ $section }}</td>
            </tr>
            <tr>
                <td>{{ $t('শ্রেণীবিভাগ', 'Classification') }}</td><td>{{ $classification }}</td>
                <td>{{ $t('পদবী', 'Designation') }}</td><td>{{ $designation }}</td>
            </tr>
            <tr>
                <td>{{ $t('যোগদানের তারিখ', 'Join Date') }}</td>
                <td>{{ $isBangla ? bn_date($employee->joining_date) : ($employee->joining_date ? Carbon\Carbon::parse($employee->joining_date)->format('d-M-y') : 'N/A') }}</td>
                <td></td><td></td>
            </tr>
        </table>

        <table class="t">
            <thead>
                <tr>
                    <th style="width:30px">{{ $t('ক্রমিক', 'SL') }}</th>
                    <th>{{ $t('তারিখ', 'Date') }}</th>
                    <th>{{ $t('শিফট', 'Shift') }}</th>
                    <th>{{ $t('বার', 'Day') }}</th>
                    <th>{{ $t('প্রবেশের সময়', 'In Time') }}</th>
                    <th>{{ $t('বাহিরের সময়', 'Out Time') }}</th>
                    <th>{{ $t('ওটি ঘণ্টা', 'OT Hrs') }}</th>
                    @if($factoryNo == 2)
                        <th>{{ $t('এক্সট্রা ওটি', 'Extra OT') }}</th>
                    @endif
                    <th>{{ $t('স্থিতি', 'Status') }}</th>
                    <th>{{ $t('মন্তব্য', 'Remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dates as $d)
                    @php
                        $statusRaw = $getStatus($employee, $d, $holidays, $getAtt);
                        $att = $getAtt($employee->id, $d->toDateString());
                        $allowMin = $allowOtMin;
                        $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                        $rawOtMin = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                        // default capped OT (used for factory 1 & fallback)
                        $cappedOtMin = ($factoryNo == 1 || $factoryNo == 2)
                            ? min($rawOtMin, $allowMin)
                            : $rawOtMin;
                        // factory 2 extra OT
                        $extraOtMin = ($factoryNo == 2 && $rawOtMin > $allowMin)
                            ? ($rawOtMin - $allowMin)
                            : 0;
                        // $otHrs = $calcOtHrs($otMinRaw, $factoryNo, $fmtOT);
                        $otHrs = $fmtOT($cappedOtMin);
                        $extraOtHrs = $fmtOT($extraOtMin);
                        $displayOut = null;
                        if ($att && $att->out_time) {
                            $actualOut = Carbon\Carbon::parse($att->out_time);
                            $shiftEndTime = Carbon\Carbon::parse($shiftEnd);
                            if ($factoryNo == 1) {
                                $maxOut = $shiftEndTime->copy()->addHours((float)$allowOtHour);
                                $displayOut = $actualOut->gt($maxOut) ? $maxOut : $actualOut;
                            }
                            elseif ($factoryNo == 2) {
                                // 👉 always show actual out time
                                $displayOut = $actualOut;
                            }
                            else {
                                $displayOut = $actualOut;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="tc">{{ $isBangla ? en2bnNumber($loop->iteration) : $loop->iteration }}</td>
                        <td class="tc">{{ $isBangla ? bn_date($d) : $d->format('d/M/Y') }}</td>
                        <td class="tc">{{ $isBangla ? $shift->bn_name : $shift->name }}</td>
                        <td class="tc">{{ $dayName($d, $isBangla) }}</td>
                        <td class="tc">
                            @if($att && $att->in_time)
                                {{ $isBangla ? bn_time($att->in_time) : $fmtTime($att->in_time) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="tc">
                            @if($displayOut)
                                {{ $isBangla ? bn_time($displayOut) : $fmtTime($displayOut) }}
                            @elseif($att && $att->out_time)
                                {{ $isBangla ? bn_time($att->out_time) : $fmtTime($att->out_time) }}
                                @else
                                -
                            @endif
                        </td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($otHrs) : $otHrs }}</td>
                        @if($factoryNo == 2)
                            <td class="tc">
                                {{ $isBangla ? en2bnNumber($extraOtHrs) : $extraOtHrs }}
                            </td>
                        @endif
                        <td class="tc">{{ $statusMap[$statusRaw] ?? $statusRaw }}</td>
                        <td>{{ $att->remarks ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="tc">{{ $t('কোনো তারিখ নেই।', 'No dates in range.') }}</td></tr>
                @endforelse
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-right"><b>{{ $t('মোট ওটি ঘণ্টা', 'Total OT Hrs') }}: </b></td>
                        <td class="text-center">
                            @php
                                $totalOTMin = 0;
                                foreach ($dates as $d) {
                                    $att = $getAtt($employee->id, $d->toDateString());
                                    $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                                    $totalOTMin += ($factoryNo == null || $factoryNo == 0)
                                        ? $otMinRaw
                                        : ($factoryNo == 1
                                            ? min($otMinRaw, $allowOtMin)
                                            : ($factoryNo == 2 ? min($otMinRaw, $allowOtMin) : $otMinRaw)
                                        );
                                }
                                $totalOTHrs = $fmtOT($totalOTMin);
                            @endphp
                            <b>{{ $isBangla ? en2bnNumber($totalOTHrs) : $totalOTHrs }}</b>
                        </td>
                        @if($factoryNo == 2)
                        <td class="text-center">
                            @php
                                $totalExtraOTMin = 0;
                                foreach ($dates as $d) {
                                    $att = $getAtt($employee->id, $d->toDateString());
                                    $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                                    if ($factoryNo == 2 && $otMinRaw > $allowOtMin) {
                                        $totalExtraOTMin += ($otMinRaw - $allowOtMin);
                                    }
                                }
                                $totalExtraOTHrs = $fmtOT($totalExtraOTMin);
                            @endphp
                            <b>{{ $isBangla ? en2bnNumber($totalExtraOTHrs) : $totalExtraOTHrs }}</b>
                        </td>
                        @endif
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </tbody>
        </table>


        {{-- Summary Calculation --}}
        @php
            $totals = [
                'totalDays' => count($dates),
                'totalGovHolidays' => 0,
                'totalWeekendDays' => 0,
                'totalWorkingDays' => 0,
                'totalAbsent' => 0, 'totalLeave' => 0, 'totalPresent' => 0, 'totalLate' => 0,
                'totalPM' => 0, 'totalEO' => 0, 'totalLEO' => 0, 'totalLPM' => 0,
                'totalAttendance' => 0
            ];
            $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
            foreach ($dates as $d) {
                $statusRaw = $getStatus($employee, $d, $holidays, $getAtt);
                $dateStr = $d->format('Y-m-d');
                $att = $getAtt($employee->id, $dateStr);

                $dayOfWeek = strtolower($d->format('l'));
                $isHoliday = $holidays->contains(function($h) use ($dateStr) {
                    return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
                });
                $isWeekend = ($dayOfWeek === $empWeekend);

                if ($isHoliday) $totals['totalGovHolidays']++;
                elseif ($isWeekend) $totals['totalWeekendDays']++;
                else $totals['totalWorkingDays']++;

                if ($statusRaw === 'A') $totals['totalAbsent']++;
                elseif ($statusRaw === 'L') $totals['totalLeave']++;
                elseif ($statusRaw === 'P') $totals['totalPresent']++;

                if ($att) {
                    $st = strtoupper($att->status ?? '');
                    if ($st === 'LATE') $totals['totalLate']++;
                    if ($st === 'PM') $totals['totalPM']++;
                    if ($st === 'EO') $totals['totalEO']++;
                    if ($st === 'LEO') $totals['totalLEO']++;
                    if ($st === 'LPM') $totals['totalLPM']++;
                    if ($att->in_time) $totals['totalAttendance']++;
                }
            }
        @endphp
        <table class="info-grid">
            <tr>
                <td>{{ $t('মাসের মোট দিন', 'Total Days in Month') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalDays']) : $totals['totalDays'] }}</td>
                <td>{{ $t('কার্যদিবস', 'Working Days') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalWorkingDays']) : $totals['totalWorkingDays'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('সরকারি ছুটি', 'Govt. Holidays') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalGovHolidays']) : $totals['totalGovHolidays'] }}</td>
                <td>{{ $t('সাপ্তাহিক ছুটি', 'Weekend Days') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalWeekendDays']) : $totals['totalWeekendDays'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('অনুপস্থিত', 'Absent Days') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalAbsent']) : $totals['totalAbsent'] }}</td>
                <td>{{ $t('ছুটি', 'Leave Days') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalLeave']) : $totals['totalLeave'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('উপস্থিত', 'Present Days') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalPresent']) : $totals['totalPresent'] }}</td>
                <td>{{ $t('মোট উপস্থিতি', 'Total Attendance') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalAttendance']) : $totals['totalAttendance'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('বিলম্ব', 'Late') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalLate']) : $totals['totalLate'] }}</td>
                <td>{{ $t('পাঞ্চ মিসিং', 'Punch Missing') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalPM']) : $totals['totalPM'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('আগে বের হয়েছে', 'Early Out') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalEO']) : $totals['totalEO'] }}</td>
                <td>{{ $t('বিলম্ব ও আগে বের', 'Late & Early Out') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalLEO']) : $totals['totalLEO'] }}</td>
            </tr>
            <tr>
                <td>{{ $t('বিলম্ব ও পাঞ্চ মিসিং', 'Late & Punch Missing') }}</td>
                <td>{{ $isBangla ? en2bnNumber($totals['totalLPM']) : $totals['totalLPM'] }}</td>
                <td></td><td></td>
            </tr>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @empty
        <p>{{ $t('কোনো কর্মী পাওয়া যায়নি।', 'No employees found.') }}</p>
    @endforelse
@endif
