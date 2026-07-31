@extends('printMaster2')

@section('title', 'OT Sheet Report - ' . $fromLabel . ' To ' . $toLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.group-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; table-layout:fixed; border-collapse:collapse; margin-bottom:10px; font-size:8px; }
.t th, .t td { border:1px solid #555; padding:1.5px 2px; overflow-wrap:break-word; }
.t th { background:#eef1d4; text-align:center; white-space:nowrap; font-size:7.5px; }
.t tr.total-row td { background:#f0f0f0; font-weight:700; }
.tc { text-align:center; white-space:nowrap; }
.tr { text-align:right; white-space:nowrap; }
.tl { text-align:left; }

@media print {
    @page { size: A4 landscape; margin: 6mm; }
    body { margin: 0; }
    /* Only force a fresh page between two group tables — each group starts clean
       rather than splitting a group's header across pages. The grand-total table
       (t-grand) is deliberately excluded so it prints wherever it actually fits,
       instead of always being pushed to a new page even when there's room left. */
    .t-group + .t-group { page-break-before: always; }
    .t tbody tr { page-break-inside: avoid; }
    .t tr.total-row { page-break-inside: avoid; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';

    // Single source of truth for which columns appear on the sheet and how wide each
    // one is — flip 'show' to false to drop a column everywhere (header, body, tfoot,
    // grand total, colgroup) without touching markup in four different places.
    // 'date_wise_ot' has no fixed width — its width is split evenly across the actual
    // date columns below, since the number of dates varies per report period.
    $columns = [
        'sl_no'        => ['label' => 'Sl-No',               'width' => 2,   'show' => true],
        'card_no'      => ['label' => 'Card No',              'width' => 4,   'show' => true],
        'name'         => ['label' => 'Name',                 'width' => 7,   'show' => true],
        'join_date'    => ['label' => 'Join Date',            'width' => 4.5, 'show' => false],
        'designation'  => ['label' => 'Designation',          'width' => 6,   'show' => true],
        'department'   => ['label' => 'Department',           'width' => 4.5, 'show' => true],
        'section'      => ['label' => 'Section',              'width' => 4.5, 'show' => true],
        'grade'        => ['label' => 'Grade',                'width' => 2.5, 'show' => false],
        'basic'        => ['label' => 'Basic',                'width' => 3,   'show' => false],
        'house_rent'   => ['label' => 'H.R',                  'width' => 3,   'show' => false],
        'medical'      => ['label' => 'M/A',                  'width' => 3,   'show' => false],
        'food'         => ['label' => 'F/A',                  'width' => 3,   'show' => false],
        'transport'    => ['label' => 'T/A',                  'width' => 3,   'show' => false],
        'gross'        => ['label' => 'Gross Salary',         'width' => 4,   'show' => false],
        'date_wise_ot' => ['label' => 'Date Wise OT (Hours)', 'width' => null, 'show' => true],
        'total_ot'     => ['label' => 'Total OT',             'width' => 4.5, 'show' => true],
        'ot_rate'      => ['label' => 'OT Rate',               'width' => 4,   'show' => true],
        'ot_amount'    => ['label' => 'OT Amount',             'width' => 5,   'show' => true],
    ];

    $fixedWidthSum = collect($columns)->filter(fn ($c) => $c['show'] && $c['width'] !== null)->sum('width');
    $dayColWidth = round((100 - $fixedWidthSum) / max($dates->count(), 1), 3);

    // Left-side colspan for the tfoot/grand-total summary label cell — spans the
    // shown identity columns only (Sl-No .. Grade); the money columns after it
    // (Basic .. Gross) still render as their own summed footer cells.
    $leadColspan = collect($columns)->takeUntil(fn ($c, $k) => $k === 'basic')->filter(fn ($c) => $c['show'])->count();
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">OT Sheet Report &mdash; {{ $fromLabel }} to {{ $toLabel }}</div>

@if(empty($sheetRows))
    <p style="text-align:center;color:#888;padding:12px 0;">No employees found.</p>
@else
    @php $sl = 1; @endphp
    @foreach($sheetRows as $group)
        @if(($groupBy ?? 'department_section') !== 'none')
        <div class="group-title">
            {{ isset($groupLabel) ? $groupLabel($group['group_key']) : 'N/A' }}
        </div>
        @endif

        <table class="t t-group">
            @include('hr::reports.partials.ot-sheet-colgroup')
            <thead>
                <tr>
                    @if($columns['sl_no']['show'])<th rowspan="2">{{ $columns['sl_no']['label'] }}</th>@endif
                    @if($columns['card_no']['show'])<th rowspan="2">{{ $columns['card_no']['label'] }}</th>@endif
                    @if($columns['name']['show'])<th rowspan="2">{{ $columns['name']['label'] }}</th>@endif
                    @if($columns['join_date']['show'])<th rowspan="2">{{ $columns['join_date']['label'] }}</th>@endif
                    @if($columns['designation']['show'])<th rowspan="2">{{ $columns['designation']['label'] }}</th>@endif
                    @if($columns['department']['show'])<th rowspan="2">{{ $columns['department']['label'] }}</th>@endif
                    @if($columns['section']['show'])<th rowspan="2">{{ $columns['section']['label'] }}</th>@endif
                    @if($columns['grade']['show'])<th rowspan="2">{{ $columns['grade']['label'] }}</th>@endif
                    @if($columns['basic']['show'])<th rowspan="2">{{ $columns['basic']['label'] }}</th>@endif
                    @if($columns['house_rent']['show'])<th rowspan="2">{{ $columns['house_rent']['label'] }}</th>@endif
                    @if($columns['medical']['show'])<th rowspan="2">{{ $columns['medical']['label'] }}</th>@endif
                    @if($columns['food']['show'])<th rowspan="2">{{ $columns['food']['label'] }}</th>@endif
                    @if($columns['transport']['show'])<th rowspan="2">{{ $columns['transport']['label'] }}</th>@endif
                    @if($columns['gross']['show'])<th rowspan="2">{{ $columns['gross']['label'] }}</th>@endif
                    @if($columns['date_wise_ot']['show'])<th colspan="{{ $dates->count() }}">{{ $columns['date_wise_ot']['label'] }}</th>@endif
                    @if($columns['total_ot']['show'])<th rowspan="2">{{ $columns['total_ot']['label'] }}</th>@endif
                    @if($columns['ot_rate']['show'])<th rowspan="2">{{ $columns['ot_rate']['label'] }}</th>@endif
                    @if($columns['ot_amount']['show'])<th rowspan="2">{{ $columns['ot_amount']['label'] }}</th>@endif
                </tr>
                @if($columns['date_wise_ot']['show'])
                <tr>
                    @foreach($dates as $d)
                        <th class="tc">{{ $d->format('j') }}</th>
                    @endforeach
                </tr>
                @endif
            </thead>
            <tbody>
                @foreach($group['rows'] as $row)
                    @php
                        $employee = $row['emp'];
                        $desigEntry = $designationMap->get($employee->designation_id, []);
                        $sectionName = $employee->section->name ?? 'N/A';
                        $departmentName = $employee->department->name ?? 'N/A';
                    @endphp
                    <tr>
                        @if($columns['sl_no']['show'])<td class="tc">{{ $sl++ }}</td>@endif
                        @if($columns['card_no']['show'])<td class="tc">{{ $employee->employee_id }}</td>@endif
                        @if($columns['name']['show'])<td>{{ $language === 'bn' && $employee->bn_name ? $employee->bn_name : $employee->name }}</td>@endif
                        @if($columns['join_date']['show'])<td class="tc">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-Y') : '-' }}</td>@endif
                        @if($columns['designation']['show'])<td>{{ $desigEntry['name'] ?? 'N/A' }}</td>@endif
                        @if($columns['department']['show'])<td>{{ $departmentName }}</td>@endif
                        @if($columns['section']['show'])<td>{{ $sectionName }}</td>@endif
                        @if($columns['grade']['show'])<td class="tc">{{ $desigEntry['grade'] ?? '-' }}</td>@endif
                        @if($columns['basic']['show'])<td class="tr">{{ number_format($row['basic']) }}</td>@endif
                        @if($columns['house_rent']['show'])<td class="tr">{{ number_format($row['house']) }}</td>@endif
                        @if($columns['medical']['show'])<td class="tr">{{ number_format($row['medical']) }}</td>@endif
                        @if($columns['food']['show'])<td class="tr">{{ number_format($row['food']) }}</td>@endif
                        @if($columns['transport']['show'])<td class="tr">{{ number_format($row['transport']) }}</td>@endif
                        @if($columns['gross']['show'])<td class="tr"><strong>{{ number_format($row['gross']) }}</strong></td>@endif
                        @if($columns['date_wise_ot']['show'])
                            @foreach($dates as $d)
                                @php $val = $row['days'][$d->format('Y-m-d')] ?? 0; @endphp
                                <td class="tc">{{ $val > 0 ? number_format($val, 1) : '-' }}</td>
                            @endforeach
                        @endif
                        @if($columns['total_ot']['show'])<td class="tc"><strong>{{ number_format($row['ot_hours'], 2) }}</strong></td>@endif
                        @if($columns['ot_rate']['show'])<td class="tc">{{ number_format($row['ot_rate'], 2) }}</td>@endif
                        @if($columns['ot_amount']['show'])<td class="tr"><strong>{{ number_format($row['ot_amount']) }}</strong></td>@endif
                    </tr>
                @endforeach
                {{-- A real <tfoot> repeats itself on every printed page a tall table spans
                     (per the CSS table-pagination spec), which made this totals row look
                     like it was appearing mid-group with only a couple of rows above it.
                     Kept as an ordinary <tbody> row instead — those never auto-repeat. --}}
                <tr class="total-row">
                    <td colspan="{{ $leadColspan }}" class="tl">{{ isset($groupLabel) ? $groupLabel($group['group_key']) : 'Total' }} &mdash; Total ({{ $group['totals']['emp'] }} Employees)</td>
                    @if($columns['basic']['show'])<td class="tr">{{ number_format($group['totals']['basic']) }}</td>@endif
                    @if($columns['house_rent']['show'])<td class="tr">{{ number_format($group['totals']['house']) }}</td>@endif
                    @if($columns['medical']['show'])<td class="tr">{{ number_format($group['totals']['medical']) }}</td>@endif
                    @if($columns['food']['show'])<td class="tr">{{ number_format($group['totals']['food']) }}</td>@endif
                    @if($columns['transport']['show'])<td class="tr">{{ number_format($group['totals']['transport']) }}</td>@endif
                    @if($columns['gross']['show'])<td class="tr">{{ number_format($group['totals']['gross']) }}</td>@endif
                    @if($columns['date_wise_ot']['show'])
                        @foreach($dates as $d)
                            @php $val = $group['totals']['day_' . $d->format('Y-m-d')] ?? 0; @endphp
                            <td class="tc">{{ $val > 0 ? number_format($val, 1) : '-' }}</td>
                        @endforeach
                    @endif
                    @if($columns['total_ot']['show'])<td class="tc">{{ number_format($group['totals']['ot_hours'], 2) }}</td>@endif
                    @if($columns['ot_rate']['show'])<td class="tc">-</td>@endif
                    @if($columns['ot_amount']['show'])<td class="tr">{{ number_format($group['totals']['ot_amount']) }}</td>@endif
                </tr>
            </tbody>
        </table>
    @endforeach

    <table class="t t-grand">
        @include('hr::reports.partials.ot-sheet-colgroup')
        <thead>
            <tr>
                <th colspan="{{ $leadColspan }}" class="tl">Grand Total ({{ $grand['emp'] }} Employees)</th>
                @if($columns['basic']['show'])<th class="tr">{{ number_format($grand['basic']) }}</th>@endif
                @if($columns['house_rent']['show'])<th class="tr">{{ number_format($grand['house']) }}</th>@endif
                @if($columns['medical']['show'])<th class="tr">{{ number_format($grand['medical']) }}</th>@endif
                @if($columns['food']['show'])<th class="tr">{{ number_format($grand['food']) }}</th>@endif
                @if($columns['transport']['show'])<th class="tr">{{ number_format($grand['transport']) }}</th>@endif
                @if($columns['gross']['show'])<th class="tr">{{ number_format($grand['gross']) }}</th>@endif
                @if($columns['date_wise_ot']['show'])
                    @foreach($dates as $d)
                        @php $val = $grand['day_' . $d->format('Y-m-d')] ?? 0; @endphp
                        <th class="tc">{{ $val > 0 ? number_format($val, 1) : '-' }}</th>
                    @endforeach
                @endif
                @if($columns['total_ot']['show'])<th class="tc">{{ number_format($grand['ot_hours'], 2) }}</th>@endif
                @if($columns['ot_rate']['show'])<th class="tc">-</th>@endif
                @if($columns['ot_amount']['show'])<th class="tr">{{ number_format($grand['ot_amount']) }}</th>@endif
            </tr>
        </thead>
    </table>
@endif
@endsection
