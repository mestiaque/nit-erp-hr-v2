@if($reportType === 'ot-summary')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;
        $fmtDate = fn($d) => $isBangla ? bn_date($d) : $d;
        $bySection = $employees->groupBy('section_id');
        $getAtt = fn($uid, $date) => ($attendanceMap->get($uid . '_' . $date) ?? collect())->first();
    @endphp

    @php
        // Try to get company info from first employee if possible
        $firstEmp = $employees->first();
        $companyName = '';
        $companyAddress = '';
        if ($firstEmp) {
            $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
            $employeeData = $employeeDataFn($firstEmp, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
            $companyName = $employeeData['company_name'] ?? '';
            $companyAddress = $employeeData['company_address'] ?? '';
            // $designation = $employeeData['designation'] ?? '';
            $options = \App\Services\HrOptionsService::getOptions($request ?? null);
        }
    @endphp

    <div class="report-head">
        <h3>{{ $companyName }}</h3>
        <p>{{ $companyAddress }}</p>
    </div>

    <div class="sub-title">{{ $t('ওটি সারাংশ', 'OT Summary') }} ({{ $isBangla ? bn_date($fromLabel) : $fromLabel }} {{ $t('থেকে', 'To') }} {{ $isBangla ? bn_date($toLabel) : $toLabel }})</div>

    @forelse($bySection as $sectionId => $sectionEmps)
        @php
            $section = $options['sections']->where('id', $sectionId)->first();
            $section = $section ? ($isBangla ? $section->bn_name : $section->name) : $t('প্রযোজ্য নয়', 'N/A');
        @endphp
        <div class="section-title">{{ $t('সেকশন', 'Section') }}: {{ $section }}</div>

        @php
            $byDesignation = $sectionEmps->groupBy('designation_id');
        @endphp

        <div style="overflow-x:auto;">
        <table class="t">
            <thead>
                <tr>
                    <th>{{ $t('ক্রমিক', 'SI') }}</th>
                    <th>{{ $t('পদবী', 'Designation') }}</th>
                    <th>{{ $t('সেকশন', 'Section') }}</th>
                    @foreach($dates as $d)
                        <th class="tc" style="min-width:28px;">{{ $isBangla ? en2bnNumber($d->format('d')) : $d->format('d') }}</th>
                    @endforeach
                    <th>{{ $t('মোট ওটি', 'To. OT') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($byDesignation as $desigId => $desigEmps)
                    @php
                        $dayTotals = [];
                        $grandOTMin = 0;
                        $factoryNo = hr_factory('factory_no');
                    @endphp
                    @foreach($dates as $d)
                        @php $dayTotals[$d->toDateString()] = 0; @endphp
                    @endforeach
                    @php
                        foreach($dates as $d) {
                            $dateStr = $d->toDateString();
                            $dayOT = 0;
                            foreach($desigEmps as $emp) {
                                $att = $getAtt($emp->id, $dateStr);
                                // RegularToWeekend logic
                                $empWeekend = strtolower($emp->otherInfo()['profile']['weekend'] ?? 'friday');
                                $dayOfWeek = strtolower($d->format('l'));
                                $isRegularToWeekend = \ME\Hr\Models\RegularToWeekend::where('section_id', $emp->section_id)
                                    ->where('date', $dateStr)
                                    ->where('type', 'weekend')
                                    ->where('is_active', 1)
                                    ->exists();
                                $isWeekendToRegular = \ME\Hr\Models\RegularToWeekend::where('section_id', $emp->section_id)
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
                                    $dayOT += $otMin;
                                }
                            }
                            $dayTotals[$dateStr] = $dayOT;
                            $grandOTMin += $dayOT;
                        }
                    @endphp
                    <tr>
                        <td class="tc">{{ $fmtNum($loop->iteration) }}</td>
                        <td>
                            @php
                                $destination = $options['designations']->where('id', $desigId)->first();
                            @endphp
                            {{ $destination ? ( $isBangla ? $destination->bn_name : $destination->name ?? $t('প্রযোজ্য নয়', 'N/A')) : $t('প্রযোজ্য নয়', 'N/A') }}
                        </td>
                        <td>
                            @php
                                $section = $options['sections']->where('id', $sectionId)->first();
                                $section = $section ? ($isBangla ? $section->bn_name : $section->name) : $t('প্রযোজ্য নয়', 'N/A');
                            @endphp
                            {{ $section }}
                        </td>
                        @foreach($dates as $d)
                            <td class="tc" style="font-size:9px;">
                                {{ $dayTotals[$d->toDateString()] > 0 ? $fmtNum(number_format($dayTotals[$d->toDateString()]/60,2)) : $fmtNum(0) }}
                            </td>
                        @endforeach
                        <td class="tc">{{ $fmtNum(number_format($grandOTMin/60, 2)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ 4 + count($dates) }}" class="tc">{{ $t('কোনো তথ্য নেই।', 'No data.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    @empty
        <p>{{ $t('কোনো কর্মী পাওয়া যায়নি।', 'No employees found.') }}</p>
    @endforelse
@endif
