@extends('printMaster2')

@section('title', 'Asset Handover - ' . $asset->asset_no)

@push('css')
<style>
.form-wrap { max-width: 720px; margin: 0 auto; border: 1.5px solid #333; padding: 16px 22px; }
.form-title { text-align: center; font-size: 15px; font-weight: 700; margin-bottom: 2px; }
.form-doc-no { text-align: center; font-size: 11px; margin-bottom: 14px; }
.sec-title { font-size: 12.5px; font-weight: 700; color: #1a3a5c; margin: 14px 0 6px; }
.kv-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 11px; }
.kv-table td { border: 1px solid #999; padding: 4px 8px; }
.kv-table td.label { width: 38%; font-weight: 600; background: #f8f9fb; }
.kv-table td.val { border-bottom: 1px dotted #333; }
.check-list { font-size: 11px; line-height: 1.9; }
.check-box { display: inline-block; width: 10px; height: 10px; border: 1px solid #333; margin-right: 4px; vertical-align: middle; }
.check-box.checked { background: #333; }
.decl-text { font-size: 10.5px; line-height: 1.5; }
.decl-text ol { margin: 4px 0 0; padding-left: 18px; }
.sign-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
.sign-table td { border: 1px solid #999; padding: 8px; vertical-align: top; }
.sign-table td.role { width: 32%; font-weight: 600; background: #f8f9fb; }
.sign-table .sign-line { margin: 10px 0 2px; }
.attach-list { font-size: 10.5px; margin: 4px 0 0; padding-left: 18px; }
.status-badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 10.5px; font-weight: 700; }
.status-active { background: #e7f1ff; color: #0d6efd; }
.status-returned { background: #eaf7ee; color: #198754; }

@media print {
    @page { size: A4; margin: 10mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $accessorySelected = $asset->accessories ?? [];
    $purposeSelected = $asset->purpose_of_issue ?? [];
@endphp

<div class="form-wrap">
    <div class="form-title">{{ strtoupper($company) }}</div>
    <div class="form-title">COMPANY ASSET HANDOVER &amp; RESPONSIBILITY FORM</div>
    <div class="form-doc-no">Document No.: {{ $asset->asset_no }} &nbsp;|&nbsp; Status:
        <span class="status-badge {{ $asset->status === 'Active' ? 'status-active' : 'status-returned' }}">{{ $asset->status }}</span>
    </div>

    <div class="sec-title">A. Employee Information</div>
    <table class="kv-table">
        <tr><td class="label">Employee Name</td><td class="val">{{ $asset->employee->name ?? '-' }}</td></tr>
        <tr><td class="label">Employee ID</td><td class="val">{{ $asset->employee->employee_id ?? '-' }}</td></tr>
        <tr><td class="label">Designation</td><td class="val">{{ $asset->employee->designation->name ?? '-' }}</td></tr>
        <tr><td class="label">Department</td><td class="val">{{ $asset->employee->department->name ?? '-' }}</td></tr>
        <tr><td class="label">Factory/Office</td><td class="val">{{ $asset->employee->workingPlace->name ?? '-' }}</td></tr>
        <tr><td class="label">Mobile Number</td><td class="val">{{ $asset->employee->mobile ?? '-' }}</td></tr>
        <tr><td class="label">Reporting Manager</td><td class="val">{{ $asset->reporting_manager ?: '-' }}</td></tr>
    </table>

    <div class="sec-title">B. Asset Information</div>
    <table class="kv-table">
        <tr><td class="label">Asset Category</td><td class="val">{{ $asset->category->name ?? '-' }}</td></tr>
        <tr><td class="label">Asset Description</td><td class="val">{{ $asset->asset_description ?: '-' }}</td></tr>
        <tr><td class="label">Brand</td><td class="val">{{ $asset->brand ?: '-' }}</td></tr>
        <tr><td class="label">Model</td><td class="val">{{ $asset->model ?: '-' }}</td></tr>
        <tr><td class="label">Color</td><td class="val">{{ $asset->color ?: '-' }}</td></tr>
        <tr><td class="label">Serial/IMEI/Chassis No.</td><td class="val">{{ $asset->serial_no ?: '-' }}</td></tr>
        <tr><td class="label">Engine No. (Vehicle)</td><td class="val">{{ $asset->engine_no ?: '-' }}</td></tr>
        <tr><td class="label">Registration No.</td><td class="val">{{ $asset->registration_no ?: '-' }}</td></tr>
        <tr><td class="label">Company Asset Code</td><td class="val">{{ $asset->asset_code ?: '-' }}</td></tr>
        <tr><td class="label">Purchase Value (BDT)</td><td class="val">{{ $asset->purchase_value ? number_format((float) $asset->purchase_value, 2) : '-' }}</td></tr>
    </table>

    <div class="sec-title">C. Accessories Provided</div>
    <div class="check-list">
        @foreach($accessories as $item)
            <span class="check-box {{ in_array($item, $accessorySelected) ? 'checked' : '' }}"></span> {{ $item }}&nbsp;&nbsp;
        @endforeach
        <span class="check-box {{ $asset->accessories_others ? 'checked' : '' }}"></span> Others: {{ $asset->accessories_others ?: '_______________' }}
    </div>

    <div class="sec-title">D. Purpose of Issue</div>
    <div class="check-list">
        @foreach($purposes as $item)
            <span class="check-box {{ in_array($item, $purposeSelected) ? 'checked' : '' }}"></span> {{ $item }}&nbsp;&nbsp;
        @endforeach
        <span class="check-box {{ $asset->purpose_others ? 'checked' : '' }}"></span> Others: {{ $asset->purpose_others ?: '_______________' }}
    </div>

    <div class="sec-title">E. Handover Details</div>
    <table class="kv-table">
        <tr><td class="label">Issued Date</td><td class="val">{{ optional($asset->issued_date)->format('d M Y') }}</td></tr>
        <tr><td class="label">Expected Return Date</td><td class="val">{{ optional($asset->expected_return_date)->format('d M Y') ?: '-' }}</td></tr>
    </table>

    <div class="sec-title">Employee Declaration</div>
    <div class="decl-text">
        I acknowledge receipt of the above company asset in good working condition.
        <ol>
            <li>The asset remains the sole property of {{ $company }}.</li>
            <li>The asset shall be used only for official purposes unless otherwise approved.</li>
            <li>I will protect the asset from loss, theft, misuse, or damage.</li>
            <li>I will immediately report any loss, accident, theft, or malfunction.</li>
            <li>I will not sell, transfer, lend, or allow unauthorized use.</li>
            <li>I will return the asset upon resignation, termination, transfer, or upon company request.</li>
            <li>If loss or damage occurs due to negligence, the company may recover the cost in accordance with company policy and applicable law.</li>
            <li>For company vehicles, I will maintain a valid driving license and obey all traffic laws.</li>
        </ol>
    </div>

    <div class="sec-title">Condition at Handover</div>
    <div class="check-list">
        @foreach(['Excellent', 'Good', 'Fair', 'Requires Minor Repair'] as $cond)
            <span class="check-box {{ $asset->condition_at_handover === $cond ? 'checked' : '' }}"></span> {{ $cond }}&nbsp;&nbsp;
        @endforeach
    </div>
    <div class="decl-text">Remarks: {{ $asset->handover_remarks ?: '_______________________________________________' }}</div>

    <div class="sec-title">Asset Return Details</div>
    <table class="kv-table">
        <tr><td class="label">Return Date</td><td class="val">{{ optional($asset->return_date)->format('d M Y') ?: '-' }}</td></tr>
        <tr><td class="label">Received By</td><td class="val">{{ $asset->received_by ?: '-' }}</td></tr>
        <tr><td class="label">Condition Upon Return</td><td class="val">{{ $asset->condition_on_return ?: '-' }}</td></tr>
        <tr><td class="label">Outstanding Damage Cost</td><td class="val">{{ $asset->damage_cost ? number_format((float) $asset->damage_cost, 2) : '-' }}</td></tr>
    </table>

    <div class="sec-title">Signatures</div>
    <table class="sign-table">
        <tr>
            <td class="role">Employee</td>
            <td>Name: ______________________<div class="sign-line">Signature: ______________________</div>Date: ______________________</td>
        </tr>
        <tr>
            <td class="role">Issued By (Administration/IT)</td>
            <td>Name: ______________________<div class="sign-line">Signature: ______________________</div>Date: ______________________</td>
        </tr>
        <tr>
            <td class="role">Verified By (Department Head)</td>
            <td>Name: ______________________<div class="sign-line">Signature: ______________________</div>Date: ______________________</td>
        </tr>
        <tr>
            <td class="role">Approved By (HR &amp; Admin Manager)</td>
            <td>Name: ______________________<div class="sign-line">Signature: ______________________</div>Date: ______________________</td>
        </tr>
        <tr>
            <td class="role">Final Approval (Managing Director/Director)</td>
            <td>Name: ______________________<div class="sign-line">Signature: ______________________</div>Date: ______________________</td>
        </tr>
    </table>

    <div class="sec-title">Recommended Attachments</div>
    <ul class="attach-list">
        <li>Asset photographs</li>
        <li>Purchase invoice copy</li>
        <li>Registration &amp; Insurance (for vehicles)</li>
        <li>IMEI/Serial verification</li>
        <li>Employee NID copy</li>
        <li>Valid Driving License copy (for vehicles)</li>
    </ul>
</div>
@endsection
