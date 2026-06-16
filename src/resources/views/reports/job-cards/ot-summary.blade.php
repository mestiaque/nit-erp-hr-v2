@if($reportType === 'ot-summary')
    @php
        $language = $language ?? data_get($request ?? null, 'language', 'bn');
        $isBangla = $language === 'bn';
        $t = fn ($bn, $en) => $isBangla ? $bn : $en;
        $fmtNum = fn($n) => $isBangla ? en2bnNumber($n) : $n;

        $bySection = $employees->groupBy('section_id');

        // 🔥 ONE CALL → all attendance
        $attendanceData = \ME\Hr\Services\EmployeeAttendanceService::getSectionWiseAttendance(
            $employees->pluck('id')->toArray(),
            $from,
            $to
        );

        $options = \ME\Hr\Services\HrOptionsService::getOptions($request ?? null);
    @endphp

    @foreach($bySection as $sectionId => $sectionEmps)

        @php
            $sectionNameObj = $options['sections']->where('id', $sectionId)->first();
            $sectionName = $sectionNameObj
                ? ($isBangla ? $sectionNameObj->bn_name : $sectionNameObj->name)
                : $t('প্রযোজ্য নয়', 'N/A');

            $byDesignation = $sectionEmps->groupBy('designation_id');
        @endphp

        <div class="section-title">
            {{ $t('সেকশন', 'Section') }}: {{ $sectionName }}
        </div>

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
                    @if(hr_factory('factory_no') == 2)
                        <th>{{ $t('অতিরিক্ত ওটি', 'Extra OT') }}</th>
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
                                $empData = $attendanceData[$sectionId][$emp->id]['attendance'] ?? [];

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
                        @if(hr_factory('factory_no') == 2)
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
                                @endphp
                                {{ $fmtNum(number_format($extraOT, 2)) }}
                            </td>
                        @endif
                    </tr>

                @endforeach
            </tbody>
        </table>

    @endforeach
@endif
