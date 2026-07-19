@php
    $language = $language ?? data_get($request ?? null, 'language', 'en');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('--', '--');

    $companyName = $isBangla
        ? (hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na)
        : (hr_factory('name') ?? general()->name ?? hr_factory('bn_name') ?? $na);
    $companyAddress = $isBangla
        ? (hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na)
        : (hr_factory('address') ?? general()->address ?? hr_factory('bn_address') ?? $na);
    $companyContact = $isBangla
        ? en2bnNumber(hr_factory('contact_number') ?? hr_factory('contact_number') ?? general()->contact ?? $na)
        : (hr_factory('contact_number') ?? general()->contact ?? hr_factory('contact_number') ?? $na);
    $issueDate = now()->format('d/m/Y');
    $joinDate = $isBangla ? bn_date($employee->joining_date, 'd/m/Y') : $fmtDate($employee->joining_date);

    $designationAttr = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
    $departmentAttr = optional(\ME\Hr\Models\HrDepartment::find($employee->department_id));
    $sectionAttr = optional(\ME\Hr\Models\HrSection::find($employee->section_id));
    $designationModel = optional($employee->designation);
    $departmentModel = optional($employee->department);

    $designation = $isBangla
        ? ($designationModel->bn_name
            ?? data_get($employee, 'designation_bn_name')
            ?? data_get($designationAttr, 'bn_name')
            ?? $designationModel->name
            ?? data_get($employee, 'designation_name')
            ?? data_get($designationAttr, 'name')
            ?? $na)
        : ($designationModel->name
            ?? data_get($employee, 'designation_name')
            ?? data_get($designationAttr, 'name')
            ?? $designationModel->bn_name
            ?? data_get($employee, 'designation_bn_name')
            ?? data_get($designationAttr, 'bn_name')
            ?? $na);

    $department = $isBangla
        ? ($departmentModel->bn_name
            ?? data_get($employee, 'department_bn_name')
            ?? data_get($departmentAttr, 'bn_name')
            ?? $departmentModel->name
            ?? data_get($employee, 'department_name')
            ?? data_get($departmentAttr, 'name')
            ?? $na)
        : ($departmentModel->name
            ?? data_get($employee, 'department_name')
            ?? data_get($departmentAttr, 'name')
            ?? $departmentModel->bn_name
            ?? data_get($employee, 'department_bn_name')
            ?? data_get($departmentAttr, 'bn_name')
            ?? $na);

    $section = $isBangla
        ? (data_get($sectionAttr, 'bn_name')
            ?? data_get($employee, 'section_bn_name')
            ?? data_get($sectionAttr, 'name')
            ?? data_get($employee, 'section')
            ?? $na)
        : (data_get($sectionAttr, 'name')
            ?? data_get($employee, 'section')
            ?? data_get($sectionAttr, 'bn_name')
            ?? data_get($employee, 'section_bn_name')
            ?? $na);

    $employeeTypeValue = data_get($employee, 'employee_type');
    if (is_numeric($employeeTypeValue)) {
        $classificationAttr = optional(\ME\Hr\Models\HrClassification::find((int) $employeeTypeValue));
        $classification = $isBangla
            ? (data_get($classificationAttr, 'bn_name') ?? data_get($classificationAttr, 'name') ?? $na)
            : (data_get($classificationAttr, 'name') ?? data_get($classificationAttr, 'bn_name') ?? $na);
    } else {
        $classification = !blank($employeeTypeValue) ? (string) $employeeTypeValue : $na;
    }

    $employeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na)
        : (data_get($employee, 'name') ?? data_get($employee, 'bn_name') ?? $na);
    $nameLength = function_exists('mb_strlen')
        ? mb_strlen(trim((string) $employeeName))
        : strlen(trim((string) $employeeName));
    $nameFontSize = 12;
    if ($nameLength > 34) {
        $nameFontSize = 8;
    } elseif ($nameLength > 28) {
        $nameFontSize = 9;
    } elseif ($nameLength > 22) {
        $nameFontSize = 10;
    } elseif ($nameLength > 16) {
        $nameFontSize = 11;
    }
    $bloodGroup = data_get($employee, 'blood_group', $na);
    $emergency = $isBangla ? en2bnNumber(data_get($employee, 'emergency_mobile', data_get($employee, 'emergency_contact_no', data_get($employee, 'mobile', $na)))) : data_get($employee, 'emergency_contact_no', data_get($employee, 'emergency_mobile', data_get($employee, 'mobile', $na)));
    $idNumber = data_get($employee, 'employee_id', data_get($employee, 'id', $na));
    $permanentAddress = $isBangla
        ? (data_get($employee, 'permanent_address_bn') ?? data_get($employee, 'permanent_address') ?? data_get($employee, 'address', $na))
        : (data_get($employee, 'permanent_address') ?? data_get($employee, 'address') ?? data_get($employee, 'permanent_address_bn', $na));

    if ($joinDate === 'N/A') {
        $joinDate = $na;
    }
@endphp

<div class="id-card-sheet">
    <div class="id-card-side id-card-front">
        <div class="id-card-logo-wrap">
            @if(!blank(general()->logo()))
                <img src="{{ asset(general()->logo()) }}" alt="{{ $t('কোম্পানির লোগো', 'Company Logo') }}" class="id-card-logo">
            @endif
        </div>
        <h4 class="id-card-company">{{ $companyName }}</h4>
        <p class="id-card-address">{{ $companyAddress }}</p>

        <div class="id-card-strip">{{ $t('পরিচয়পত্র', 'ID CARD') }}</div>

        <div class="id-card-photo-wrap">
            <img src="{{ asset($employee->image()) }}" alt="{{ $t('কর্মচারীর ছবি', 'Employee Photo') }}" class="id-card-photo">
        </div>

        <table class="id-card-info">
            <tr><td>{{ $t('নাম', 'Name') }}</td><td class="name-cell"><span class="name-value" style="font-size: {{ $nameFontSize }}px;">: {{ $employeeName }}</span></td></tr>
            <tr><td>{{ $t('পদবি', 'Designation') }}</td><td>: {{ $designation }}</td></tr>
            <tr><td>{{ $t('আইডি নং', 'ID No.') }}</td><td>: {{ $idNumber }}</td></tr>
            <tr><td>{{ $t('বিভাগ', 'Dept.') }}</td><td>: {{ $department }}</td></tr>
            <tr><td>{{ $t('সেকশন', 'Section') }}</td><td>: {{ $section }}</td></tr>
            <tr><td>{{ $t('যোগদান', 'Join Date') }}</td><td>: {{ $joinDate }}</td></tr>
            <tr><td>{{ $t('শ্রেণি', 'Classification') }}</td><td>: {{ $classification }}</td></tr>
            <tr><td>{{ $t('ইস্যু তারিখ', 'Issue Date') }}</td><td>: {{ $joinDate }}</td></tr>

        </table>

        <div class="id-sign-row">
            <div>
                <div class="id-sign-line"></div>
                <div class="id-sign-label">{{ $t('কর্মচারীর স্বাক্ষর', 'Staff Signature') }}</div>
            </div>
            <div>
                <div class="id-sign-line"></div>
                <div class="id-sign-label">{{ $t('কর্তৃপক্ষের স্বাক্ষর', 'Authority Signature') }}</div>
            </div>
        </div>
    </div>

    <div class="id-card-side id-card-back">
        <div class="id-card-logo-wrap">
            @if(!blank(general()->logo()))
                <img src="{{ asset(general()->logo()) }}" alt="{{ $t('কোম্পানির লোগো', 'Company Logo') }}" class="id-card-logo">
            @endif
        </div>

        <p class="id-back-head">{{ $t('রক্তের গ্রুপ', 'Blood Group') }} : <strong>{{ $bloodGroup }}</strong></p>
        <p class="id-back-head">{{ $t('স্থায়ী ঠিকানা', 'Permanent Address') }}</p>
        <p class="id-back-text">{{ $permanentAddress }}</p>

        <p class="id-back-head" style="margin-top: 10px;">{{ $t('জরুরি যোগাযোগ নম্বর', 'Emergency Contact No.') }}:</p>
        <p class="id-back-text"><strong>{{ $emergency }}</strong></p>

        <p class="id-back-text" style="margin-top: 8px;">
            {{ $t('কার্ডটি পেলে নিচের ঠিকানায় বা নিকটস্থ অফিসে ফেরত দিন।', 'Please return to the following address or nearest office station.') }}
        </p>
        <p class="id-back-company"><strong>{{ $companyName }}</strong></p>
        <p class="id-back-text">{{ $companyAddress }}</p>
        <p class="id-back-text">{{ $t('যোগাযোগ নম্বর', 'Contact No.') }}: {{ $companyContact }}</p>

        <div class="id-card-strip id-card-strip-bottom">{{ $t('মেয়াদ: চাকরির শেষ তারিখ পর্যন্ত।', 'Exp. Date: Up to the last date of job.') }}</div>
    </div>
</div>
<style>
    .name-cell {
        white-space: nowrap;
    }

    .name-value {
        display: inline-block;
        max-width: 140px;
        white-space: nowrap;
        line-height: 1.1;
    }
</style>
