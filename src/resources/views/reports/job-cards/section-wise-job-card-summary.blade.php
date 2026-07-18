@if(in_array($reportType, ['job-card-summary', 'job-card-summary-lock']))
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $bySection = $groups ?? $employees->groupBy('section_id');
    @endphp

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            // প্রথম কর্মী থেকে section metadata
            $firstEmp = $sectionEmps->first();
            $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
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
        @if(($groupBy ?? 'section') !== 'none')
        <div class="section-title">{{ isset($groupLabel) ? $groupLabel((string) $sectionId) : $t('সেকশন', 'Section') . ': ' . $section }}</div>
        @endif

        <div style="">
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
                    @if($factoryNo == 2)
                        <th>{{ $t('অতিরিক্ত ওটি (ঘণ্টা)', 'Extra OT (hrs)') }}</th>
                    @endif
                    <th>{{ $t('মোট উপস্থিতি', 'Total Attendance') }}</th>
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

                        $attendance = $data['attendance'];
                        $summary = $data['summary'];
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

                        {{-- Daily cells --}}
                        @foreach($attendance as $row)
                            <td class="tc" style="font-size:9px;">
                                {{ att_status($row['status'], $language) }}
                            </td>
                        @endforeach

                        {{-- Summary --}}
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalPresentAll']) : $summary['totalPresentAll'] }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalAbsent']) : $summary['totalAbsent'] }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalLeave']) : $summary['totalLeave'] }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalWeekendDays']) : $summary['totalWeekendDays'] }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalGovHolidays']) : $summary['totalGovHolidays'] }}</td>
                        <td class="tc">{{ $isBangla ? en2bnNumber(number_format($summary['totalComplianceOt'], 2)) : number_format($summary['totalComplianceOt'], 2) }}</td>
                        @if($factoryNo == 2)
                            <td class="tc">{{ $isBangla ? en2bnNumber(number_format($summary['totalExtraOt'], 2)) : number_format($summary['totalExtraOt'], 2) }}</td>
                        @endif
                        <td class="tc">{{ $isBangla ? en2bnNumber($summary['totalAttendance']) : $summary['totalAttendance'] }}</td>
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
