
@if($reportType === 'attendance-summary')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $totalDays = count($dates);
        $bySection = $groups ?? $employees->groupBy('section_id');
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;
        $fmtDate = fn($d) => $isBangla ? bn_date($d) : $d;
    @endphp

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            $firstEmp = $sectionEmps->first();
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
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
        @if(($groupBy ?? 'section') !== 'none')
        <div class="section-title">{{ isset($groupLabel) ? $groupLabel((string) $sectionId) : $t('সেকশন', 'Section') . ': ' . $section }}</div>
        @endif

        <div style="">
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
                    <th>{{ $t('আগে বের হয়েছে', 'Early Exit') }}</th>
                    <th>{{ $t('পাঞ্চ মিসিং', 'Punch Missing') }}</th>
                    <th>{{ $t('বিলম্ব ও আগে বের হয়েছে', 'Late & Early Exit') }}</th>
                    <th>{{ $t('বিলম্ব ও পাঞ্চ মিসিং', 'Late & Punch Missing') }}</th>
                    <th>{{ $t('অনুপস্থিত', 'Absent') }}</th>
                    <th>{{ $t('ছুটি', 'Leave') }}</th>
                    @if(ENV('FACTORY') === 'SFL')
                        <th>{{ $t('ছুটি', 'Holiday') }}</th>
                    @else
                        <th>{{ $t('সাপ্তাহিক ছুটি', 'Weekend') }}</th>
                        <th>{{ $t('সরকারি ছুটি', 'Govt. Holiday') }}</th>
                    @endif
                    <th>{{ $t('উপস্থিত', 'Present') }}</th>
                    <th>{{ $t('উপার্জিত দিন', 'Earn Days') }}</th>
                    <th>{{ $t('ওটি (ঘণ্টা)', 'OT (hrs)') }}</th>
                    @if(hr_factory('factory_no') == 2)
                        <th>{{ $t('অতিরিক্ত ওটি (ঘণ্টা)', 'Extra OT (hrs)') }}</th>
                    @endif
                    <th>{{ $t('মন্তব্য', 'Remarks') }}</th>
                </tr>
            </thead>
            <tbody>



                @foreach($sectionEmps as $employee)
                    @php
                        $data = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                            $employee->id,
                            $from,
                            $to
                        );

                        $summary = $data['summary'];

                        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);

                        $earnDays = $summary['totalPresent']
                            + $summary['totalLeave']
                            + $summary['totalWeekendDays']
                            + $summary['totalGovHolidays'];
                    @endphp

                    <tr style="">
                        <td class="tc">{{ $fmtNum($loop->iteration) }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employeeData['employee_name'] ?? $employee->name }}</td>
                        <td>{{ $employeeData['designation'] ?? 'N/A' }}</td>
                        <td>{{ $employeeData['department'] ?? 'N/A' }}</td>
                        <td>{{ $employeeData['section'] ?? 'N/A' }}</td>
                        <td>{{ $employeeData['sub_section'] ?? 'N/A' }}</td>
                        <td>{{ $employeeData['line'] }}</td>
                        <td>{{ $employeeData['working_place'] ?? 'N/A' }}</td>
                        <td>{{ $employeeData['job_type'] ?? 'N/A' }}</td>

                        <td class="tc">
                            {{ $isBangla
                                ? ($employee->joining_date ? bn_date($employee->joining_date) : '-')
                                : ($employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-y') : '-')
                            }}
                        </td>

                        <td class="tc">{{ $fmtNum($summary['totalDays']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalLate']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalEO']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalPM']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalLEO']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalLPM']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalAbsent']) }}</td>
                        <td class="tc">{{ $fmtNum($summary['totalLeave']) }}</td>
                        @if(ENV('FACTORY') === 'SFL')
                            <td class="tc">{{ $fmtNum($summary['totalWeekendDays'] + $summary['totalGovHolidays']) }}</td>
                        @else
                            <td class="tc">{{ $fmtNum($summary['totalWeekendDays']) }}</td>
                            <td class="tc">{{ $fmtNum($summary['totalGovHolidays']) }}</td>
                        @endif
                        <td class="tc">{{ $fmtNum($summary['totalPresent']) }}</td>
                        <td class="tc">{{ $fmtNum($earnDays) }}</td>

                        <td class="tc">
                            {{ $isBangla
                                ? en2bnNumber(number_format($summary['totalComplianceOt'], 2))
                                : number_format($summary['totalComplianceOt'], 2)
                            }}
                        </td>
                        @if(hr_factory('factory_no') == 2)
                            <td class="tc">
                                {{ $isBangla
                                    ? en2bnNumber(number_format($summary['totalExtraOt'], 2))
                                    : number_format($summary['totalExtraOt'], 2)
                                }}
                            </td>
                        @endif

                        <td></td>
                    </tr>
                @endforeach
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
