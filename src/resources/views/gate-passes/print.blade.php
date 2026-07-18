@extends('printMaster2')

@section('title', 'Gate Pass - ' . $gatePass->pass_no)

@push('css')
<style>
.gp-wrap { max-width: 480px; margin: 0 auto; border: 1.5px solid #333; padding: 14px 18px; }
.gp-head { text-align: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #999; }
.gp-head h3 { margin: 0 0 2px; font-size: 15px; }
.gp-head p { margin: 0; font-size: 10.5px; color: #444; }
.gp-title { text-align: center; font-size: 13px; font-weight: 700; margin: 8px 0 12px; text-transform: uppercase; letter-spacing: .5px; }
.gp-pass-no { text-align: right; font-size: 11px; font-weight: 700; margin-bottom: 8px; }
.gp-table { width: 100%; border-collapse: collapse; font-size: 11.5px; margin-bottom: 14px; }
.gp-table td { padding: 4px 2px; vertical-align: top; }
.gp-table td.label { width: 38%; color: #444; font-weight: 600; }
.gp-status { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10.5px; font-weight: 700; }
.gp-status-active { background: #e7f1ff; color: #0d6efd; }
.gp-status-returned { background: #eaf7ee; color: #198754; }
.gp-sign-row { display: flex; justify-content: space-between; margin-top: 30px; }
.gp-sign-box { text-align: center; width: 45%; }
.gp-sign-box .line { border-top: 1px solid #333; margin-bottom: 3px; }

@media print {
    @page { size: A5; margin: 8mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
@endphp

<div class="gp-wrap">
    <div class="gp-head">
        @if(!blank(optional(general())->logo()))
            <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:36px;margin-bottom:4px;">
        @endif
        <h3>{{ $company }}</h3>
        <p>{{ $address }}</p>
    </div>

    <div class="gp-title">Employee Gate Pass</div>
    <div class="gp-pass-no">Pass No: {{ $gatePass->pass_no }}</div>

    <table class="gp-table">
        <tr>
            <td class="label">Employee ID</td>
            <td>{{ $gatePass->employee->employee_id ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Employee Name</td>
            <td>{{ $gatePass->employee->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td>{{ $gatePass->employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td>
            <td>{{ $gatePass->employee->designation->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Out Time</td>
            <td>{{ optional($gatePass->out_time)->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">In Time</td>
            <td>{{ optional($gatePass->in_time)->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="label">Duration</td>
            <td>{{ $gatePass->duration_minutes }} Minutes</td>
        </tr>
        <tr>
            <td class="label">Reason</td>
            <td>{{ $gatePass->reason }}</td>
        </tr>
        @if($gatePass->remarks)
        <tr>
            <td class="label">Remarks</td>
            <td>{{ $gatePass->remarks }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Status</td>
            <td>
                <span class="gp-status {{ $gatePass->status === 'Active' ? 'gp-status-active' : 'gp-status-returned' }}">
                    {{ $gatePass->status }}
                </span>
            </td>
        </tr>
    </table>

    <div class="gp-sign-row">
        <div class="gp-sign-box">
            <div class="line"></div>
            Employee Signature
        </div>
        <div class="gp-sign-box">
            <div class="line"></div>
            Authorized By (HR)
        </div>
    </div>
</div>
@endsection
