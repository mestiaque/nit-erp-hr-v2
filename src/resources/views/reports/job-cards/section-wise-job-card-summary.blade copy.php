@if(in_array($reportType, ['job-card-summary', 'job-card-summary-lock']))
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $bySection = $employees->groupBy('section_id');
        $fmtOT     = fn($min) => $min > 0 ? number_format($min / 60, 2) : '0.00';
        // AttendanceMap থেকে attendance খোঁজার হেল্পার
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
        // OT Clocking হেল্পার (factory wise logic)
        $getOtHour = function($min, $factoryNo, $fmtOT) {
            if ($factoryNo == null || $factoryNo == 0)       return $fmtOT($min); // actual
            elseif ($factoryNo == 1)                         return $fmtOT(min($min, 120)); // max 2 hrs
            elseif ($factoryNo == 2)                         return $fmtOT(min($min, 240)); // max 4 hrs
            else                                             return $fmtOT($min); // fallback
        };
    @endphp

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            // প্রথম কর্মী থেকে section metadata
            $firstEmp = $sectionEmps->first();
            $hrOptions = \App\Services\HrOptionsService::getOptions();
            $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
            $employeeData = $employeeDataFn($firstEmp, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
            $companyName = $employeeData['company_name'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $companyAddress = $employeeData['company_address'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $section = $employeeData['section'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $subSection = $employeeData['sub_section'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $designation = $employeeData['designation'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $department = $employeeData['department'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $line = $employeeData['line'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $workingPlace = $employeeData['working_place'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $factoryNo = hr_factory('factory_no');
            $holidays = $hrOptions['holidays'] ?? collect();
        @endphp
        <div class="report-head">
            <h3>{{ $companyName }}</h3>
            <p>{{ $companyAddress }}</p>
        </div>

        <div class="sub-title">
            {{ $t('জব কার্ড সারাংশ', 'Job Card Summary') }} {{ $reportType === 'job-card-summary-lock' ? $t('(লক)', '(Lock)') : '' }}
            ({{ $isBangla ? bn_date($fromLabel) : $fromLabel }} {{ $t('থেকে', 'To') }} {{ $isBangla ? bn_date($toLabel) : $toLabel }})
        </div>
        <div class="section-title">{{ $t('সেকশন', 'Section') }}: {{ $section }}</div>

        <div style="overflow-x:auto;">
        <table class="t">
            <thead>
                <tr>
                    <th>{{ $t('ক্রমিক', 'SI') }}</th>
                    <th>{{ $t('কর্মী আইডি', 'Emp. ID') }}</th>
                    <th>{{ $t('নাম', 'Name') }}</th>
                    <th>{{ $t('পদবী', 'Designation') }}</th>
                    <th>{{ $t('যোগদানের তারিখ', 'DOJ') }}</th>
                    <th>{{ $t('সেকশন', 'Section') }}</th>
                    <th>{{ $t('সাব-সেকশন', 'Sub-Section') }}</th>
                    <th>{{ $t('ব্লক/লাইন', 'Block/Line') }}</th>
                    @foreach($dates as $d)
                        <th class="tc" style="min-width:28px;">{{ $isBangla ? en2bnNumber($d->format('d')) : $d->format('d') }}</th>
                    @endforeach
                    <th>{{ $t('উপস্থিত', 'Present') }}</th>
                    <th>{{ $t('অনুপস্থিত', 'Absent') }}</th>
                    <th>{{ $t('ছুটি', 'Leave') }}</th>
                    <th>{{ $t('সাপ্তাহিক ছুটি', 'Weekend') }}</th>
                    <th>{{ $t('সরকারি ছুটি', 'Holiday') }}</th>
                    <th>{{ $t('ওটি (ঘণ্টা)', 'OT (hrs)') }}</th>
                    <th>{{ $t('মোট উপস্থিতি', 'Total Attendance') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sectionEmps as $employee)
                    @php
                        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
                        $designation = $employeeData['designation'] ?? $t('প্রযোজ্য নয়', 'N/A');
                        $section = $employeeData['section'] ?? $t('প্রযোজ্য নয়', 'N/A');
                        $subSection = $employeeData['sub_section'] ?? $t('প্রযোজ্য ��য়', 'N/A');
                        $line = $employeeData['line'] ?? $t('প্রযোজ্য নয়', 'N/A');
                        $workingPlace = $employeeData['working_place'] ?? $t('প্রযোজ্য নয়', 'N/A');
                        // summary counters
                        $totalPresent = $totalAbsent = $totalLeave = $totalWeekend = $totalHoliday = $totalOTMin = $totalAttendance = 0;
                        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
                    @endphp
                    <tr>
                        <td class="tc">{{ $isBangla ? en2bnNumber($loop->iteration) : $loop->iteration }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employeeData['employee_name'] ?? '--' }}</td>
                        <td>{{ $designation }}</td>
                        <td class="tc">
                            @php
                                $joiningDate = $employee->joining_date;
                            @endphp
                            @if($isBangla)
                                {{ $joiningDate ? bn_date($joiningDate) : '-' }}
                            @else
                                {{ $joiningDate ? (is_string($joiningDate) ? \Carbon\Carbon::parse($joiningDate)->format('d-M-y') : (method_exists($joiningDate, 'format') ? $joiningDate->format('d-M-y') : '-') ) : '-' }}
                            @endif
                        </td>
                        <td>{{ $section }}</td>
                        <td>{{ $subSection }}</td>
                        <td>{{ $workingPlace }}</td>
                        @foreach($dates as $d)
                            @php
                                $dateStr = $d->format('Y-m-d');
                                // Leave আগে চেক করুন:
                                $leave = \ME\Hr\Models\Leave::where('employee_id', $employee->id)
                                    ->whereDate('start_date', '<=', $dateStr)
                                    ->whereDate('end_date',   '>=', $dateStr)
                                    ->first();
                                $att = $getAtt($employee->id, $dateStr);
                                $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;

                                // Holiday, weekend detection
                                $isHoliday = $holidays->contains(function($h) use ($dateStr) {
                                    return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
                                });
                                $dayOfWeek = strtolower($d->format('l'));
                                // RegularToWeekend logic
                                $isRegularToWeekend = \ME\Hr\Models\RegularToWeekend::where('section_id', $employee->section_id)
                                    ->where('date', $dateStr)
                                    ->where('type', 'weekend')
                                    ->where('is_active', 1)
                                    ->exists();
                                $isWeekendToRegular = \ME\Hr\Models\RegularToWeekend::where('section_id', $employee->section_id)
                                    ->where('date', $dateStr)
                                    ->where('type', 'regular')
                                    ->where('is_active', 1)
                                    ->exists();
                                $isWeekend = false;
                                if ($dayOfWeek === $empWeekend && $isWeekendToRegular) {
                                    $isWeekend = false;
                                } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                                    $isWeekend = true;
                                }
                                // Status/Count logic (Leave > Holiday > Weekend > Present/Absent)
                                if ($leave) {
                                    $totalLeave++;
                                    $cell = $t('ছুটি', 'L');
                                } elseif ($isHoliday) {
                                    $totalHoliday++;
                                    $cell = $t('সরকারি ছুটি', 'H');
                                } elseif ($isWeekend) {
                                    $totalWeekend++;
                                    $cell = $t('সাপ্তাহিক ছুটি', 'W');
                                } else {
                                    $statusRaw = $att ? ($att->status ?: ($att->in_time ? 'P' : 'A')) : 'A';
                                    if ($statusRaw === 'A') {
                                        $totalAbsent++;
                                        $cell = $t('অনুপস্থিত', 'A');
                                    } elseif ($statusRaw === 'P' || $statusRaw == 'Present') {
                                        $totalPresent++;
                                        $cell = $t('উপস্থিত', 'P');
                                    } else {
                                        $cell = $statusRaw;
                                    }
                                }
                                // OT-তে leave, holiday, weekend-এ OT নাই, শুধু present বা attendance থাকলে যোগ হবে:
                                if (!$leave && !$isHoliday && !$isWeekend) {
                                    $totalOTMin += ($factoryNo == 1) ? min($otMinRaw, 120) : (($factoryNo == 2) ? min($otMinRaw, 240) : $otMinRaw);
                                    if ($att && $att->in_time) $totalAttendance++;
                                }
                            @endphp
                            <td class="tc" style="font-size:9px;">{{ $cell }}</td>
                        @endforeach
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalPresent) : $totalPresent }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalAbsent) : $totalAbsent }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalLeave) : $totalLeave }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalWeekend) : $totalWeekend }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalHoliday) : $totalHoliday }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber(number_format($totalOTMin/60,2)) : number_format($totalOTMin/60,2) }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($totalAttendance) : $totalAttendance }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 16 + count($dates) }}" class="tc">{{ $t('কোনো তথ্য নেই।', 'No data.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @empty
        <p>{{ $t('কোনো কর্মী পাওয়া যায়নি।', 'No employees found.') }}</p>
    @endforelse
@endif
