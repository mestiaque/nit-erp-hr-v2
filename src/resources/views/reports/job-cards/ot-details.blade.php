


@if($reportType === 'ot-details')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn ($bn, $en) => $isBangla ? $bn : $en;
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;
        $bySection = $groups ?? $employees->groupBy('section_id');
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $fmtDate = fn($d) => $isBangla ? bn_date($d) : $d;
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
    @endphp

    @foreach($bySection as $sectionId => $sectionEmps)

        @php
            $firstEmp = $sectionEmps->first();
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();

            $employeeData = $employeeDataFn($firstEmp);
            $section = $employeeData['section'] ?? '';
        @endphp

        <div class="report-head">
            <h3>{{ $employeeData['company_name'] ?? '' }}</h3>
            <p>{{ $employeeData['company_address'] ?? '' }}</p>
        </div>

        <div class="sub-title mt-0">{{ $t('ওটি বিস্তারিত', 'OT Details') }} ({{ $isBangla ? bn_date($fromLabel) : $fromLabel }} {{ $t('থেকে', 'To') }} {{ $isBangla ? bn_date($toLabel) : $toLabel }})</div>
        @if(($groupBy ?? 'section') !== 'none')
        <div class="section-title">{{ isset($groupLabel) ? $groupLabel((string) $sectionId) : $t('সেকশন', 'Section') . ': ' . $section }}</div>
        @endif

        <table class="t mb-4" style="margin-bottom: 3rem">
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
                    <th>{{ $t('মোট ওটি', 'To. OT') }}</th>
                    @if(hr_factory('factory_no') == 2)
                        <th>{{ $t('অতিরিক্ত ওটি', 'Extra OT') }}</th>
                    @endif
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

                        $attendance = collect($data['attendance'])->keyBy(function($a){
                            return \Carbon\Carbon::parse($a['date'])->format('Y-m-d');
                        });

                        $totalOT = $data['summary']['totalComplianceOt'];
                    @endphp

                    <tr>
                        <td class="tc">{{ $fmtNum($loop->iteration) }}</td>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employeeData['employee_name'] ?? $employee->name }}</td>
                        <td>{{ $employeeData['designation'] ?? ($designationMap->get($employee->designation_id, $t('প্রযোজ্য নয়', 'N/A'))) }}</td>
                        <td class="tc">
                            @if($isBangla)
                                {{ $employee->joining_date ? bn_date($employee->joining_date) : '-' }}
                            @else
                                {{ $employee->joining_date ? (is_string($employee->joining_date) ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-y') : (method_exists($employee->joining_date, 'format') ? $employee->joining_date->format('d-M-y') : '-') ) : '-' }}
                            @endif
                        </td>
                        <td>{{ $employeeData['section'] ?? ($sectionMap->get($employee->section_id, $t('প্রযোজ্য নয়', 'N/A'))) }}</td>
                        <td>{{ $employeeData['sub_section'] ?? ($subSectionMap->get($employee->hr_sub_section_id ?? $employee->sub_section_id ?? null, $t('প্রযোজ্য নয়', 'N/A'))) }}</td>
                        <td>{{ $employeeData['line'] ?? ($lineMap->get($employee->line_number, $t('প্রযোজ্য নয়', 'N/A'))) }}</td>

                        @foreach($dates as $d)
                            @php
                                $row = $attendance[$d->format('Y-m-d')] ?? null;
                                $ot = $row['compliance_ot'] ?? 0;
                            @endphp

                            <td class="tc">
                                {{ $fmtNum(number_format($ot, 2)) }}
                            </td>
                        @endforeach

                        <td class="tc">
                            {{ $fmtNum(number_format($totalOT, 2)) }}
                        </td>
                        @if(hr_factory('factory_no') == 2)
                            @php
                                $extraOt = $data['summary']['totalExtraOt'] ?? 0;
                            @endphp
                            <td class="tc">
                                {{ $fmtNum(number_format($extraOt, 2)) }}
                            </td>
                        @endif
                    </tr>

                @endforeach
            </tbody>
        </table>

    @endforeach
@endif
