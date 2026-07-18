@if($reportType === 'ot-summary')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn ($bn, $en) => $isBangla ? $bn : $en;
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;

        $bySection = $groups ?? $employees->groupBy('section_id');

        // 🔥 ONE CALL → all attendance
        $attendanceData = \ME\Hr\Services\EmployeeAttendanceService::getSectionWiseAttendance(
            $employees->pluck('id')->toArray(),
            $from,
            $to
        );

        $options = \ME\Hr\Services\HrOptionsService::getOptions($request ?? null);

        $pageGrandOT = 0;
        $pageGrandExtraOT = 0;

        // Fixed, consistent column widths across every section's table — otherwise each
        // table auto-sizes its own columns based on that section's longest designation
        // name, so the same column ends up a different width from section to section.
        $otShowExtraCol = hr_factory('factory_no') == 2;
        $otDayColWidth  = round((100 - 3 - 16 - 11 - 7 - ($otShowExtraCol ? 7 : 0)) / max($dates->count(), 1), 3);
    @endphp

    @foreach($bySection as $sectionId => $sectionEmps)

        @php
            if (isset($groupLabel) && ($groupBy ?? 'section') !== 'section') {
                $sectionName = $groupLabel((string) $sectionId);
            } else {
                $sectionNameObj = $options['sections']->where('id', $sectionId)->first();
                $sectionName = $sectionNameObj
                    ? ($isBangla ? $sectionNameObj->bn_name : $sectionNameObj->name)
                    : $t('প্রযোজ্য নয়', 'N/A');
            }
            $groupColLabel = ($groupBy ?? 'section') === 'section' ? $t('সেকশন', 'Section') : ($groupByAxisLabel ?? $t('সেকশন', 'Section'));

            $byDesignation = $sectionEmps->groupBy('designation_id');
        @endphp

        @if(($groupBy ?? 'section') !== 'none')
        <div class="section-title">
            {{ $groupColLabel }}: {{ $sectionName }}
        </div>
        @endif

        <table class="t t-ot-summary">
            <thead>
                <tr>
                    <th style="width:3%;">{{ $t('ক্রমিক', 'SI') }}</th>
                    <th style="width:16%;">{{ $t('পদবী', 'Designation') }}</th>
                    <th style="width:11%;">{{ $groupColLabel }}</th>
                    @foreach($dates as $d)
                        <th class="tc" style="width:{{ $otDayColWidth }}%;">{{ $isBangla ? en2bnNumber($d->format('d')) : $d->format('d') }}</th>
                    @endforeach
                    <th style="width:7%;">{{ $t('মোট ওটি', 'To. OT') }}</th>
                    @if($otShowExtraCol)
                        <th style="width:7%;">{{ $t('অতিরিক্ত ওটি', 'Extra OT') }}</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach($byDesignation as $desigId => $desigEmps)

                    @php
                        $dayTotals = [];
                        $grandOT = 0;

                        foreach($dates as $d){
                            $dateKey = $d->format('Y-m-d');
                            $dayTotals[$dateKey] = 0;

                            foreach($desigEmps as $emp){
                                // Keyed by the employee's real section_id regardless of which
                                // axis this report is currently grouped/displayed by — the
                                // outer loop var below may now hold a department id etc.
                                $empData = $attendanceData[$emp->section_id][$emp->id]['attendance'] ?? [];

                                $row = collect($empData)->first(function($r) use ($dateKey){
                                    return \Carbon\Carbon::parse($r['date'])->format('Y-m-d') === $dateKey;
                                });

                                $dayTotals[$dateKey] += $row['compliance_ot'] ?? 0;
                            }

                            $grandOT += $dayTotals[$dateKey];
                        }

                        $designationObj = $options['designations']->where('id', $desigId)->first();
                        $designationName = $designationObj
                            ? ($isBangla ? $designationObj->bn_name : $designationObj->name)
                            : $t('প্রযোজ্য নয়', 'N/A');

                        $pageGrandOT += $grandOT;
                    @endphp

                    <tr>
                        <td class="tc">{{ $loop->iteration }}</td>
                        <td>{{ $designationName }}</td>
                        <td>{{ $sectionName }}</td>

                        @foreach($dates as $d)
                            @php $val = $dayTotals[$d->format('Y-m-d')] ?? 0; @endphp
                            <td class="tc">
                                {{ $fmtNum(number_format($val, 2)) }}
                            </td>
                        @endforeach

                        <td class="tc">
                            {{ $fmtNum(number_format($grandOT, 2)) }}
                        </td>
                        @if($otShowExtraCol)
                            <td class="tc">
                                {{-- 🔥 EXTRA OT CALCULATION --}}
                                @php
                                    $extraOT = 0;

                                    foreach($desigEmps as $emp){
                                        $empData = $attendanceData[$sectionId][$emp->id]['attendance'] ?? [];

                                        foreach($empData as $row){
                                            $extraOT += $row['extra_ot'] ?? 0;
                                        }
                                    }

                                    $pageGrandExtraOT += $extraOT;
                                @endphp
                                {{ $fmtNum(number_format($extraOT, 2)) }}
                            </td>
                        @endif
                    </tr>

                @endforeach
            </tbody>
        </table>

    @endforeach

    <div class="grand-total-box">
        {{ $t('সর্বমোট ওটি (সকল সেকশন)', 'Grand Total OT (All Sections)') }}: {{ $fmtNum(number_format($pageGrandOT, 2)) }}
        @if($otShowExtraCol)
            &nbsp;|&nbsp;
            {{ $t('সর্বমোট অতিরিক্ত ওটি', 'Grand Total Extra OT') }}: {{ $fmtNum(number_format($pageGrandExtraOT, 2)) }}
        @endif
    </div>
@endif
