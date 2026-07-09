@extends('printMaster2')

@section('title', 'Final Settlement Statement')

@section('contents')
@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');

    $companyName = $isBangla
        ? (hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na)
        : (hr_factory('name') ?? general()->name ?? hr_factory('bn_name') ?? $na);
    $companyAddress = $isBangla
        ? (hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na)
        : (hr_factory('address') ?? general()->address ?? hr_factory('bn_address') ?? $na);

    $employeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na)
        : (data_get($employee, 'name') ?? data_get($employee, 'bn_name') ?? $na);

    $designationModel = data_get($employee, 'designation');
    if (!$designationModel && data_get($employee, 'designation_id')) {
        $designationModel = \ME\Hr\Models\HrDesignation::query()
            ->select(['id', 'name', 'bn_name'])
            ->find(data_get($employee, 'designation_id'));
    }
    $designation = $isBangla
        ? ($designation_bn ?? data_get($designationModel, 'bn_name') ?? data_get($designationModel, 'name') ?? $na)
        : ($designation_en ?? data_get($designationModel, 'name') ?? data_get($designationModel, 'bn_name') ?? $na);

    $departmentModel = optional($employee->department);
    $department = $isBangla
        ? ($departmentModel->bn_name ?? $departmentModel->name ?? $na)
        : ($departmentModel->name ?? $departmentModel->bn_name ?? $na);

    $joinDate = $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d/m/Y') : $na;
    $exitDate = $employee->exited_at ? \Carbon\Carbon::parse($employee->exited_at)->format('d/m/Y') : $na;
    $issueDate = now()->format('d/m/Y');

    $fmt = fn ($v) => number_format((float) ($v ?? 0), 2);

    $unpaid    = (float) optional($settlement)->unpaid_salary_amount;
    $leave     = (float) optional($settlement)->leave_encashment_amount;
    $gratuity  = (float) optional($settlement)->gratuity_amount;
    $otherEarn = (float) optional($settlement)->other_earnings;
    $advance   = (float) optional($settlement)->advance_deduction;
    $otherDed  = (float) optional($settlement)->other_deductions;
    $netPayable = optional($settlement)->net_payable !== null
        ? (float) $settlement->net_payable
        : ($unpaid + $leave + $gratuity + $otherEarn - $advance - $otherDed);
@endphp

<div style="text-align:center; margin-bottom:14px;">
    <h3 style="margin:0;">{{ $companyName }}</h3>
    <div>{{ $companyAddress }}</div>
    <div style="margin-top:6px; font-weight:700; font-size:16px;">{{ $t('চূড়ান্ত পাওনা বিবরণী (Final Settlement Statement)', 'Final Settlement Statement') }}</div>
</div>

<table style="margin-bottom: 12px;">
    <tr>
        <th style="width: 20%;">{{ $t('নাম', 'Name') }}</th>
        <td style="width: 30%;">{{ $employeeName }}</td>
        <th style="width: 20%;">{{ $t('কার্ড নং', 'Card No.') }}</th>
        <td style="width: 30%;">{{ $employee->employee_id ?? $employee->id }}</td>
    </tr>
    <tr>
        <th>{{ $t('পদবী', 'Designation') }}</th>
        <td>{{ $designation }}</td>
        <th>{{ $t('বিভাগ', 'Department') }}</th>
        <td>{{ $department }}</td>
    </tr>
    <tr>
        <th>{{ $t('যোগদানের তারিখ', 'Join Date') }}</th>
        <td>{{ $joinDate }}</td>
        <th>{{ $t('অব্যাহতির তারিখ', 'Exit Date') }}</th>
        <td>{{ $exitDate }}</td>
    </tr>
    <tr>
        <th>{{ $t('চাকরিকাল', 'Service Length') }}</th>
        <td>{{ optional($settlement)->service_years ?? 0 }} {{ $t('বছর', 'year(s)') }}</td>
        <th>{{ $t('ইস্যুর তারিখ', 'Issue Date') }}</th>
        <td>{{ $issueDate }}</td>
    </tr>
</table>

<table style="margin-bottom: 12px;">
    <thead>
        <tr>
            <th style="width:55%;">{{ $t('বিবরণ', 'Description') }}</th>
            <th style="width:15%;">{{ $t('দিন', 'Days') }}</th>
            <th style="width:30%; text-align:right;">{{ $t('পরিমাণ (টাকা)', 'Amount (BDT)') }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $t('অপরিশোধিত বেতন (আংশিক মাস)', 'Unpaid Salary (partial month)') }}</td>
            <td>{{ optional($settlement)->unpaid_salary_days ?? 0 }}</td>
            <td style="text-align:right;">{{ $fmt($unpaid) }}</td>
        </tr>
        <tr>
            <td>{{ $t('অর্জিত ছুটি নগদায়ন', 'Earned Leave Encashment') }}</td>
            <td>{{ optional($settlement)->leave_encashment_days ?? 0 }}</td>
            <td style="text-align:right;">{{ $fmt($leave) }}</td>
        </tr>
        <tr>
            <td>{{ $t('গ্র্যাচুইটি', 'Gratuity') }}</td>
            <td>—</td>
            <td style="text-align:right;">{{ $fmt($gratuity) }}</td>
        </tr>
        <tr>
            <td>{{ $t('অন্যান্য প্রাপ্য (+)', 'Other Earnings (+)') }}</td>
            <td>—</td>
            <td style="text-align:right;">{{ $fmt($otherEarn) }}</td>
        </tr>
        <tr>
            <td>{{ $t('অগ্রিম/ধার কর্তন (-)', 'Advance / IOU Deduction (-)') }}</td>
            <td>—</td>
            <td style="text-align:right;">- {{ $fmt($advance) }}</td>
        </tr>
        <tr>
            <td>{{ $t('অন্যান্য কর্তন (-)', 'Other Deductions (-)') }}</td>
            <td>—</td>
            <td style="text-align:right;">- {{ $fmt($otherDed) }}</td>
        </tr>
        <tr class="grandtotal-row">
            <td colspan="2" style="font-weight:700;">{{ $t('নিট প্রদেয়', 'Net Payable') }}</td>
            <td style="text-align:right; font-weight:700;">{{ $fmt($netPayable) }}</td>
        </tr>
    </tbody>
</table>

@if(optional($settlement)->calculation_notes)
<div style="margin-bottom: 12px; font-size: 12px;">
    <strong>{{ $t('মন্তব্য', 'Notes') }}:</strong> {{ $settlement->calculation_notes }}
</div>
@endif

<div style="margin-bottom: 12px; font-size: 12px;">
    <strong>{{ $t('অবস্থা', 'Status') }}:</strong> {{ ucfirst(optional($settlement)->settlement_status ?? 'draft') }}
</div>

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">{{ $t('কর্মচারীর স্বাক্ষর', 'Employee Signature') }}</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">{{ $t('কর্তৃপক্ষের স্বাক্ষর', 'Authority Signature') }}</div>
    </div>
</div>
@endsection
