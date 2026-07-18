@extends('printMaster2')

@section('title', 'Gate Pass Report - ' . $fromLabel . ' To ' . $toLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.status-active { color:#0d6efd; font-weight:700; }
.status-returned { color:#198754; font-weight:700; }
.emp-total-row td { background:#fafafa; font-weight:700; }

@media print {
    @page { size: A4 landscape; margin: 7mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">Gate Pass Report &mdash; {{ $fromLabel }} to {{ $toLabel }}</div>

@if($gatePasses->isEmpty())
    <p style="text-align:center;color:#888;padding:12px 0;">No gate pass records found.</p>
@else
    <table class="t">
        <thead>
            <tr>
                <th>SL</th>
                <th>Pass No</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Designation</th>
                <th>Out Time</th>
                <th>In Time</th>
                <th>Duration</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if($isRange)
                @php $sl = 1; @endphp
                @foreach($groupedGatePasses as $employeeId => $rows)
                    @foreach($rows as $gatePass)
                        <tr>
                            <td class="tc">{{ $sl++ }}</td>
                            <td class="tc">{{ $gatePass->pass_no }}</td>
                            <td class="tc">{{ $gatePass->employee->employee_id ?? '-' }}</td>
                            <td>{{ $gatePass->employee->name ?? 'N/A' }}</td>
                            <td>{{ $gatePass->employee->department->name ?? 'N/A' }}</td>
                            <td>{{ $gatePass->employee->section->name ?? 'N/A' }}</td>
                            <td>{{ $gatePass->employee->designation->name ?? 'N/A' }}</td>
                            <td class="tc">{{ optional($gatePass->out_time)->format('d-M-Y h:i A') }}</td>
                            <td class="tc">{{ optional($gatePass->in_time)->format('d-M-Y h:i A') }}</td>
                            <td class="tc">{{ $gatePass->duration_minutes }} min</td>
                            <td>{{ $gatePass->reason }}</td>
                            <td class="tc status-{{ strtolower($gatePass->status) }}">{{ $gatePass->status }}</td>
                        </tr>
                    @endforeach
                    <tr class="emp-total-row">
                        <td colspan="9" class="tc" style="text-align:right;">Total Duration ({{ $rows->first()->employee->name ?? 'N/A' }}):</td>
                        <td class="tc">{{ $rows->sum('duration_minutes') }} min</td>
                        <td colspan="2"></td>
                    </tr>
                @endforeach
            @else
                @foreach($gatePasses as $i => $gatePass)
                    <tr>
                        <td class="tc">{{ $i + 1 }}</td>
                        <td class="tc">{{ $gatePass->pass_no }}</td>
                        <td class="tc">{{ $gatePass->employee->employee_id ?? '-' }}</td>
                        <td>{{ $gatePass->employee->name ?? 'N/A' }}</td>
                        <td>{{ $gatePass->employee->department->name ?? 'N/A' }}</td>
                        <td>{{ $gatePass->employee->section->name ?? 'N/A' }}</td>
                        <td>{{ $gatePass->employee->designation->name ?? 'N/A' }}</td>
                        <td class="tc">{{ optional($gatePass->out_time)->format('d-M-Y h:i A') }}</td>
                        <td class="tc">{{ optional($gatePass->in_time)->format('d-M-Y h:i A') }}</td>
                        <td class="tc">{{ $gatePass->duration_minutes }} min</td>
                        <td>{{ $gatePass->reason }}</td>
                        <td class="tc status-{{ strtolower($gatePass->status) }}">{{ $gatePass->status }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
@endif
@endsection
