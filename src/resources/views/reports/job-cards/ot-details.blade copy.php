@if($reportType === 'ot-details')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;
        $fmtDate = fn($d) => $isBangla ? bn_date($d) : $d;
        $bySection = $employees->groupBy('section_id');
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
    @endphp

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            $firstEmp = $sectionEmps->first();
            $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
            $employeeData = $employeeDataFn($firstEmp, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
            $companyName = $employeeData['company_name'] ?? '';
            $companyAddress = $employeeData['company_address'] ?? '';
            $section = $employeeData['section'] ?? '';
        @endphp
        <div class="report-head">
            <h3>{{ $companyName }}</h3>
            <p>{{ $companyAddress }}</p>
        </div>

        <div class="sub-title">{{ $t('ওটি বিস্তারিত', 'OT Details') }} ({{ $isBangla ? bn_date($fromLabel) : $fromLabel }} {{ $t('থেকে', 'To') }} {{ $isBangla ? bn_date($toLabel) : $toLabel }})</div>
        <div class="section-title">{{ $t('সেকশন', 'Section') }}: {{ $section }}</div>

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
                    <th>{{ $t('মোট ওটি', 'To. OT') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sectionEmps as $employee)
                    @php
                        $totalOTMin = 0;
                        $factoryNo = hr_factory('factory_no');
                        $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
                        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
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
                                $dateStr = $d->format('Y-m-d');
                                $att   = $getAtt($employee->id, $dateStr);
                                // RegularToWeekend logic
                                $empWeekend = strtolower($employee->otherInfo()['profile']['weekend'] ?? 'friday');
                                $dayOfWeek = strtolower($d->format('l'));
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
                                $otMinRaw = $att ? (int)($att->overtime_minutes ?? 0) : 0;
                                if (!$isWeekend) {
                                    if ($factoryNo == 1) {
                                        $otMin = min($otMinRaw, 120);
                                    } elseif ($factoryNo == 2) {
                                        $otMin = min($otMinRaw, 240);
                                    } else {
                                        $otMin = $otMinRaw;
                                    }
                                    $totalOTMin += $otMin;
                                } else {
                                    $otMin = 0;
                                }
                            @endphp
                            <td class="tc" style="font-size:9px;">
                                {{ $otMin > 0 ? $fmtNum(number_format($otMin/60,2)) : $fmtNum(0) }}
                            </td>
                        @endforeach
                        <td class="tc">{{ $fmtNum(number_format($totalOTMin/60, 2)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 9 + count($dates) }}" class="tc">{{ $t('কোনো তথ্য নেই।', 'No data.') }}</td></tr>
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
