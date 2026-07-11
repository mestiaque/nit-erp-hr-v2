@extends('printMaster2')

@section('title',
    ($isSingleDay ?? true)
        ? ('Daily Absent Report - ' . \Carbon\Carbon::parse($toDate ?? now()->toDateString())->format('d/m/Y'))
        : ('Absent Report - '
            . \Carbon\Carbon::parse($fromDate ?? now()->toDateString())->format('d/m/Y')
            . ' to '
            . \Carbon\Carbon::parse($toDate ?? now()->toDateString())->format('d/m/Y'))
)

@push('css')
<style>
.report-shell { font-size: 11px; }
.report-head { text-align:center; margin-bottom:8px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title {
    text-align:center;
    font-size:12px;
    font-weight:700;
    margin:6px 0 8px;
}
.section-head {
    font-size:11px;
    font-weight:700;
    margin:8px 0 2px;
}
.table-wrap { margin-bottom:8px; }
.t {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.t th, .t td {
    border: 1px solid #8f8f8f;
    padding: 3px 4px;
    vertical-align: top;
}
.t th {
    background: #f3f3f3;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}
.tc { text-align: center; }
.section-total {
    font-size: 11px;
    font-weight: 700;
    margin-top: 2px;
}
.section-total-row td {
    font-weight: 700;
    background: #fafafa;
}
.empty-note {
    border: 1px solid #8f8f8f;
    padding: 6px;
    font-size: 11px;
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
    $isSingleDay = $isSingleDay ?? true;
    $fromDate = $fromDate ?? now()->toDateString();
    $toDate = $toDate ?? now()->toDateString();
    $dateText = $isSingleDay
        ? \Carbon\Carbon::parse($toDate)->format('d/m/Y')
        : \Carbon\Carbon::parse($fromDate)->format('d/m/Y') . ' to ' . \Carbon\Carbon::parse($toDate)->format('d/m/Y');
    $titleText = $isSingleDay ? 'Daily Absent Report' : 'Absent Report';
    $bySection = collect($rows ?? [])->groupBy('section_id');
@endphp

<div class="report-shell">
    <div class="report-head">
        @if(!blank(general()->logo()))
            <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
        @endif
        <h3>{{ $company }}</h3>
        <p>{{ $address ?: 'N/A' }}</p>
    </div>

    <div class="sub-title">{{ $titleText }} &mdash; {{ $dateText }}</div>

    @forelse($bySection as $sectionId => $sectionEmps)
        <div class="section-head">
            Section: {{ $loop->iteration }}.{{ $sectionMap->get($sectionId, 'N/A') }}
        </div>

        <div class="table-wrap">
            <table class="t">
                <thead>
                    <tr>
                        @if(!$isSingleDay)
                            <th style="width:10%;" class="tc">Date</th>
                        @endif
                        <th style="width:14%;">CardNo</th>
                        <th style="width:23%;">Name</th>
                        <th style="width:22%;">Designation</th>
                        <th style="width:14%;">Floor</th>
                        <th style="width:15%;">Line</th>
                        <th style="width:6%;" class="tc">In Time</th>
                        <th style="width:6%;" class="tc">Out Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sectionEmps as $row)
                        @php
                            $inTime = data_get($row, 'in_time', '0');
                            $outTime = data_get($row, 'out_time', '0');
                            $inTime = strlen((string) $inTime) >= 5 ? substr((string) $inTime, 0, 5) : (string) $inTime;
                            $outTime = strlen((string) $outTime) >= 5 ? substr((string) $outTime, 0, 5) : (string) $outTime;
                            $rowDate = data_get($row, 'date');
                            $rowDateText = $rowDate ? \Carbon\Carbon::parse($rowDate)->format('d/m/Y') : '-';
                        @endphp
                        <tr>
                            @if(!$isSingleDay)
                                <td class="tc">{{ $rowDateText }}</td>
                            @endif
                            <td>{{ data_get($row, 'employee_id', 'N/A') }}</td>
                            <td>{{ data_get($row, 'name', 'N/A') }}</td>
                            <td>{{ data_get($row, 'designation', 'N/A') }}</td>
                            <td>{{ data_get($row, 'floor', 'N/A') }}</td>
                            <td>{{ data_get($row, 'line', 'N/A') }}</td>
                            <td class="tc">{{ $inTime ?: '0' }}</td>
                            <td class="tc">{{ $outTime ?: '0' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="section-total-row">
                        <td colspan="{{ $isSingleDay ? 7 : 8 }}">Section Total: {{ $sectionEmps->count() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div class="empty-note">No absent employee found.</div>
    @endforelse
</div>
@endsection
