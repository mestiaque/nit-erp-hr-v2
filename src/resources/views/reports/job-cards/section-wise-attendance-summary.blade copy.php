
@if($reportType === 'attendance-summary')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $totalDays = count($dates);
        $bySection = $employees->groupBy('section_id');
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;
        $fmtDate = fn($d) => $isBangla ? bn_date($d) : $d;
    @endphp

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            $firstEmp = $sectionEmps->first();
            $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
            $employeeData = $employeeDataFn($firstEmp, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
            $companyName = $employeeData['company_name'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $companyAddress = $employeeData['company_address'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $section = $employeeData['section'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $subSection = $employeeData['sub_section'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $department = $employeeData['department'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $line = $employeeData['line'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $workingPlace = $employeeData['working_place'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $workingPlace = $employeeData['working_place'] ?? $t('প্রযোজ্য নয়', 'N/A');
            $classification = $employeeData['job_type'] ?? $t('প্রযোজ্য নয়', 'N/A');
        @endphp
        <div class="report-head">
            <h3>{{ $companyName }}</h3>
            <p>{{ $companyAddress }}</p>
        </div>

        <div class="sub-title">{{ $t('উপস্থিতি সারাংশ', 'Attendance Summary') }} ({{ $isBangla ? bn_date($fromLabel) : $fromLabel }} {{ $t('থেকে', 'To') }} {{ $isBangla ? bn_date($toLabel) : $toLabel }})</div>
        <div class="section-title">{{ $t('সেকশন', 'Section') }}: {{ $section }}</div>

        <div style="overflow-x:auto;">
        <table class="t">
            <thead>
                <tr>
                    <th>{{ $t('ক্রমিক', 'SI') }}</th>
                    <th>{{ $t('কর্মী আইডি', 'Employee ID') }}</th>
                    <th>{{ $t('নাম', 'Name') }}</th>
                    <th>{{ $t('পদবী', 'Designation') }}</th>
                    <th>{{ $t('বিভাগ', 'Department') }}</th>
                    <th>{{ $t('সেকশন', 'Section') }}</th>
                    <th>{{ $t('সাব-সেকশন', 'Sub-Section') }}</th>
                    <th>{{ $t('ব্লক/লাইন', 'Block/Line') }}</th>
                    <th>{{ $t('কর্মস্থল', 'Working Place') }}</th>
                    <th>{{ $t('শ্রেণীবিভাগ', 'Classification') }}</th>
                    <th>{{ $t('যোগদানের তারিখ', 'Join Date') }}</th>
                    <th>{{ $t('মাসের দিন', 'Total Days') }}</th>
                    <th>{{ $t('বিলম্ব', 'Late') }}</th>
                    <th>{{ $t('অনুপস্থিত', 'Absent') }}</th>
                    <th>{{ $t('ছুটি', 'Leave') }}</th>
                    <th>{{ $t('সাপ্তাহিক ছুটি', 'Weekend') }}</th>
                    <th>{{ $t('সরকারি ছুটি', 'Govt. Holiday') }}</th>
                    <th>{{ $t('উপস্থিত', 'Present') }}</th>
                    <th>{{ $t('উপার্জিত দিন', 'Earn Days') }}</th>
                    <th>{{ $t('ওটি (ঘণ্টা)', 'OT (hrs)') }}</th>
                    <th>{{ $t('মন্তব্য', 'Remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sectionEmps as $employee)
                    @php
                        $present = 0; $late = 0; $absent = 0;
                        $leave   = 0; $weekend = 0; $holiday = 0;
                        $totalOTMin = 0;
                        $hrOptions = \App\Services\HrOptionsService::getOptions();
                        $hrHolidays = $hrOptions['holidays'] ?? collect();
                        $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
                        $factoryNo = hr_factory('factory_no');
                        foreach($dates as $d) {
                            $dateStr = $d->format('Y-m-d');
                            $att = $getAtt($employee->id, $dateStr);
                            // Leave detection (priority)
                            $leaveObj = \ME\Hr\Models\Leave::where('employee_id', $employee->id)
                                ->whereDate('start_date', '<=', $dateStr)
                                ->whereDate('end_date', '>=', $dateStr)
                                ->first();
                            // Holiday logic
                            $isHoliday = $hrHolidays->contains(function($h) use ($dateStr) {
                                return ($dateStr >= $h->from_date && $dateStr <= $h->to_date);
                            });
                            // Weekend/RegularToWeekend logic
                            $dayOfWeek = strtolower($d->format('l'));
                            // Check RegularToWeekend model for both types
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
                                // Do not treat as weekend
                                $isWeekend = false;
                            } elseif ($isRegularToWeekend || ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || ($att && !empty($att->regular_to_weekend))) {
                                $isWeekend = true;
                            }
                            if ($leaveObj) {
                                $leave++;
                                $statusRaw = 'L';
                            } elseif ($isHoliday) {
                                $holiday++;
                                $statusRaw = 'GH';
                            } elseif ($isWeekend) {
                                $weekend++;
                                $statusRaw = 'WO';
                            } else {
                                $statusRaw = $att ? ($att->status ?: ($att->in_time ? 'P' : 'A')) : 'A';
                                if ($statusRaw === 'P' || $statusRaw == 'Present') {
                                    $present++;
                                    if ((int)($att->late_time ?? 0) > 0) $late++;
                                } else {
                                    $absent++;
                                }
                            }
                            // OT logic (apply factory capping)
                            $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                            if (!$leaveObj && !$isHoliday && !$isWeekend) {
                                if ($factoryNo == 1) {
                                    $totalOTMin += min($otMinRaw, 120);
                                } elseif ($factoryNo == 2) {
                                    $totalOTMin += min($otMinRaw, 240);
                                } else {
                                    $totalOTMin += $otMinRaw;
                                }
                            }
                        }
                        $earnDays = $present + $leave + $weekend + $holiday;
                        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
                    @endphp
                    <tr>
                        <td class="tc">{{ $fmtNum($loop->iteration) }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employeeData['employee_name'] ?? $employee->name }}</td>
                        <td>{{ $employeeData['designation'] ?? ($designationMap->get($employee->designation_id, 'N/A')) }}</td>
                        <td>{{ $employeeData['department'] ?? ($departmentMap->get($employee->department_id ?? null, 'N/A')) }}</td>
                        <td>{{ $employeeData['section'] ?? ($sectionMap->get($employee->section_id, 'N/A')) }}</td>
                        <td>{{ $employeeData['sub_section'] ?? ($subSectionMap->get($employee->hr_sub_section_id ?? $employee->sub_section_id ?? null, 'N/A')) }}</td>
                        <td>{{ $employeeData['line'] }}</td>
                        <td>{{ $employeeData['working_place'] ?? $t('প্রযোজ্য নয়', 'N/A') }}</td>
                        <td>{{ $employeeData['job_type'] ?? $t('প্রযোজ্য নয়', 'N/A') }}</td>
                        <td class="tc">
                            @if($isBangla)
                                {{ $employee->joining_date ? bn_date($employee->joining_date) : '-' }}
                            @else
                                {{ $employee->joining_date ? (is_string($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-y') : (method_exists($employee->joining_date, 'format') ? $employee->joining_date->format('d-M-y') : '-') ) : '-' }}
                            @endif
                        </td>
                        <td class="tc">{{ $fmtNum($totalDays) }}</td>
                        <td class="tc">{{ $fmtNum($late) }}</td>
                        <td class="tc">{{ $fmtNum($absent) }}</td>
                        <td class="tc">{{ $fmtNum($leave) }}</td>
                        <td class="tc">{{ $fmtNum($weekend) }}</td>
                        <td class="tc">{{ $fmtNum($holiday) }}</td>
                        <td class="tc">{{ $fmtNum($present) }}</td>
                        <td class="tc">{{ $fmtNum($earnDays) }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber(number_format($totalOTMin/60, 2)) : number_format($totalOTMin/60, 2) }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr><td colspan="21" class="tc">{{ $t('কোনো তথ্য নেই।', 'No data.') }}</td></tr>
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
