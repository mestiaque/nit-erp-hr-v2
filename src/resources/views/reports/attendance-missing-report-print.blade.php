@extends('printMaster2')

@section('title', 'Attendance Missing Report - ' . \Carbon\Carbon::parse($from)->format('d/m/Y') . ' to ' . \Carbon\Carbon::parse($to)->format('d/m/Y'))

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
.date-info {
    text-align:right;
    font-size:11px;
    margin-bottom:8px;
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
    $company = general()->title ?? 'Company Name';
    $address = general()->address_one ?? '';
    $dateRange = \Carbon\Carbon::parse($from)->format('d/m/Y') . ' To: ' . \Carbon\Carbon::parse($to)->format('d/m/Y');
@endphp

<div class="report-shell">
    <div class="report-head">
        <h3>{{ $company }}</h3>
        <p>{{ $address ?: 'N/A' }}</p>
    </div>

    <div class="sub-title">Attendance Missing</div>

    <div class="date-info">From : {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} To: {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</div>

    @forelse($attendanceBySection as $sectionId => $sectionRecords)
        <div class="section-head">
            Section &nbsp;&nbsp;{{ $loop->iteration }}.{{ $sectionMap->get($sectionId, 'N/A') }}
        </div>

        <div class="table-wrap">
            <table class="t">
                <thead>
                    <tr>
                        <th style="width:12%;">Card No.</th>
                        <th style="width:28%;">Name</th>
                        <th style="width:15%;">Date</th>
                        <th style="width:22%;">Punching Time</th>
                        <th style="width:23%;">Missing Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sectionRecords as $record)
                        @php
                            $employee = $employeeMap->get($record->employee_id);
                            $inTime = $record->in_time ? substr((string)$record->in_time, 0, 5) : '-';
                            $outTime = $record->out_time ? substr((string)$record->out_time, 0, 5) : '-';
                            $punchingTime = ($inTime !== '-' ? $inTime : '') . ($inTime !== '-' && $outTime !== '-' ? ' - ' : '') . ($outTime !== '-' ? $outTime : '');
                            if (!$punchingTime) $punchingTime = '-';
                        @endphp
                        <tr>
                            <td>{{ $employee ? $employee->employee_id : 'N/A' }}</td>
                            <td>{{ $employee ? $employee->name : 'N/A' }}</td>
                            <td class="tc">{{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}</td>
                            <td>{{ $punchingTime }}</td>
                            <td>
                                @php
                                    $missingTypes = [];
                                    if (empty($record->in_time)) $missingTypes[] = 'In Time';
                                    if (empty($record->out_time)) $missingTypes[] = 'Out Time';
                                @endphp
                                {{-- {{ implode(' / ', $missingTypes) . ' Missing' }} --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="section-total-row">
                        <td colspan="5">Section Total: {{ $sectionRecords->count() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @empty
        <div class="empty-note">No attendance missing record found.</div>
    @endforelse
</div>
@endsection
