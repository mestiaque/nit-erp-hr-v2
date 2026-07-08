@once
@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @page {
        size: A4;
        margin: 0;
    }

    .sfl-front-grid,
    .sfl-back-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5mm;
    }

    .sfl-page-break {
        flex-basis: 100%;
        width: 100%;
        height: 0;
        page-break-after: always;
    }

    .sfl-card-side {
        width: 60mm;
        height: 95mm;
        background: #fff;
        overflow: hidden;
        box-sizing: border-box;
        position: relative;
        display: flex;
        flex-direction: column;
        border: 0.3mm solid #999;
        page-break-inside: avoid;
    }

    /* Header / footer decoration (div-based, replaces the old SVG) */
    .sfl-decor {
        position: relative;
        width: 100%;
        height: 5.6mm;
        flex-shrink: 0;
        overflow: hidden;
    }
    .sfl-decor-shape {
        position: absolute;
        inset: 0;
    }
    .sfl-decor-shape.is-navy { background: #11294a; }
    .sfl-decor-shape.is-gold { background: #dcae3a; }

    /* Front card */
    .sfl-decor-fh-navy {
        clip-path: polygon(0% 0%, 40% 0%, 37% 100%, 0% 100%);
        height: 1.5rem;
        z-index: 11;
    }
    .sfl-decor-fh-gold {
        clip-path: polygon(39.5% 0%, 76% 0%, 73% 100%, 37.5% 100%);
        height: 16px;
        z-index: 1;
    }
    .sfl-decor-ff-navy {
        clip-path: polygon(30.4% 0%, 68.4% 0%, 64.8% 100%, 27.8% 100%);
        height: 16px;
        margin-top: 5px;
    }
    .sfl-decor-ff-gold {
        clip-path: polygon(66.9% 0%, 100% 0%, 100% 100%, 63.3% 100%);
        height: 1.5rem;
    }

    /* Back card (colors swapped) */
    /*.sfl-decor-bh-gold { clip-path: polygon(0% 0%, 40% 0%, 37% 100%, 0% 100%); height: 1rem; }*/
    /*.sfl-decor-bh-navy { clip-path: polygon(40.5% 0%, 100% 0%, 100% 100%, 37.5% 100%); height: 1.5rem; }*/
    /*.sfl-decor-bf-gold { clip-path: polygon(0% 0%, 38% 0%, 35% 100%, 0% 100%); height: 1.5rem; }*/
    /*.sfl-decor-bf-navy { clip-path: polygon(38.5% 0%, 100% 0%, 100% 100%, 35.5% 100%); height: 1rem; }*/
    
    
    
        .sfl-decor-bh-navy {
        clip-path: polygon(0% 0%, 40% 0%, 37% 100%, 0% 100%);
        height: 1.5rem;
        z-index: 11;
    }
    .sfl-decor-bh-gold {
        clip-path: polygon(39.5% 0%, 76% 0%, 73% 100%, 37.5% 100%);
        height: 16px;
        z-index: 1;
    }
    .sfl-decor-bf-navy {
        clip-path: polygon(30.4% 0%, 68.4% 0%, 64.8% 100%, 27.8% 100%);
        height: 16px;
        margin-top: 5px;
    }
    .sfl-decor-bf-gold {
        clip-path: polygon(66.9% 0%, 100% 0%, 100% 100%, 63.3% 100%);
        height: 1.5rem;
            z-index: 1;
    }


    /* Logo row */
    .sfl-logo-area {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1mm 1.5mm;
        flex-shrink: 0;
    }
    .sfl-logo-img {
        height: 10mm;
        flex-shrink: 0;
        object-fit: contain;
    }
    .sfl-logo-text {
        margin-left: 1.5mm;
        min-width: 0;
    }
    .sfl-company-name {
        font-size: 2.9mm;
        font-weight: 800;
        color: #dcae3a;
        line-height: 1.15;
        margin: 0;
        word-break: break-word;
    }

    /* Photo */
    .sfl-photo-wrap {
        text-align: center;
        flex-shrink: 0;
        margin: 0.8mm 0;
    }
    .sfl-photo {
        width: 21mm;
        height: 23mm;
        object-fit: cover;
        border: 0.6mm solid #11294a;
        border-radius: 1.6mm;
        display: inline-block;
    }

    /* Info grid */
    .sfl-info {
        flex: 1;
        padding: 0 1.7mm;
        font-size: 2.35mm;
        line-height: 1.35;
        display: grid;
        grid-template-columns: auto 1mm 1fr;
        align-content: start;
        overflow: hidden;
    }
    .sfl-info-label {
        font-weight: 700;
        white-space: nowrap;
        padding-bottom: 0.4mm;
    }
    .sfl-info-colon {
        text-align: center;
        font-weight: 700;
        padding-bottom: 0.4mm;
    }
    .sfl-info-value {
        font-weight: 700;
        word-break: break-word;
        padding-bottom: 0.4mm;
    }

    /* Signature */
    .sfl-sign-row {
        display: flex;
        justify-content: flex-end;
        padding: 0 2.2mm;
        margin-bottom: 1mm;
        flex-shrink: 0;
    }
    .sfl-sign-line {
        width: 20mm;
        border-top: 0.4px solid #333;
        margin-bottom: 0.4mm;
    }
    .sfl-sign-label {
        font-size: 2mm;
        text-align: center;
        color: #333;
    }

    /* Back content */
    .sfl-back-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 2mm;
        overflow: hidden;
    }
    .sfl-ribbon {
        background: #11294a;
        color: #fff;
        font-size: 2.9mm;
        font-weight: 700;
        padding: 1mm 5.5mm;
        margin: 1mm 0;
        clip-path: polygon(4mm 0%, calc(100% - 4mm) 0%, 100% 50%, calc(100% - 4mm) 100%, 4mm 100%, 0% 50%);
        text-align: center;
        letter-spacing: 0.2mm;
    }
    .sfl-terms {
        list-style: none;
        margin: 0.6mm 0;
        padding: 0 0.6mm;
        font-size: 2.1mm;
        line-height: 1.4;
        color: #111;
        font-weight: 700;
        align-self: stretch;
    }
    .sfl-terms li {
        margin-bottom: 0.9mm;
    }
    .sfl-squares {
        display: flex;
        gap: 1.1mm;
        margin: 1mm 0;
    }
    .sfl-sq {
        width: 1.7mm;
        height: 1.7mm;
        background: #11294a;
        display: inline-block;
    }
    .sfl-found-msg {
        font-size: 2mm;
        font-weight: 700;
        text-align: center;
        line-height: 1.4;
        margin: 0.6mm 0;
        color: #111;
    }
    .sfl-contact {
        font-size: 2.1mm;
        font-weight: 700;
        color: #111;
        align-self: stretch;
        line-height: 1.5;
        font-style: normal;
    }
    .sfl-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 1.1mm;
        margin-bottom: 0.6mm;
    }
    .sfl-contact-icon {
        flex-shrink: 0;
        font-size: 2.2mm;
        line-height: 1.5;
    }
</style>
@endpush
@endonce

@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');

    $designationAttr = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
    $departmentAttr  = optional(\ME\Hr\Models\HrDepartment::find($employee->department_id));
    $sectionAttr     = optional(\ME\Hr\Models\HrSection::find($employee->section_id));

    $employeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na)
        : (data_get($employee, 'name') ?? data_get($employee, 'bn_name') ?? $na);

    $sflDesignation = $isBangla
        ? ($designationAttr->bn_name ?? $designationAttr->name ?? $na)
        : ($designationAttr->name ?? $designationAttr->bn_name ?? $na);

    $sflDepartment = $isBangla
        ? ($departmentAttr->bn_name ?? $departmentAttr->name ?? $na)
        : ($departmentAttr->name ?? $departmentAttr->bn_name ?? $na);

    $sflSection = $isBangla
        ? ($sectionAttr->bn_name ?? $sectionAttr->name ?? $na)
        : ($sectionAttr->name ?? $sectionAttr->bn_name ?? $na);

    $bloodGroup = data_get($employee, 'blood_group', $na);
    $nidNumber  = data_get($employee, 'nid', data_get($employee, 'national_id', ''));

    $mobile = data_get($employee, 'mobile', data_get($employee, 'emergency_mobile', $na));
    if ($isBangla && function_exists('en2bnNumber')) {
        $mobile = en2bnNumber((string) $mobile);
    }

    if ($isBangla) {
        $permanentAddress = implode(', ', array_filter([
            $employee->permanent_village_bn ?? $employee->permanent_village,
            $employee->permanent_post_office_bn ?? $employee->permanent_post_office,
            $employee->permanent_upazila_bn ?? $employee->permanent_upazila,
            $employee->permanent_district_bn ?? $employee->permanent_district,
        ]));
    } else {
        $permanentAddress = implode(', ', array_filter([
            $employee->permanent_village,
            $employee->permanent_post_office,
            $employee->permanent_upazila,
            $employee->permanent_district,
        ]));
    }
    if (blank($permanentAddress)) {
        $permanentAddress = data_get($employee, 'permanent_address') ?? data_get($employee, 'address') ?? $na;
    }

    $joinDateRaw = data_get($employee, 'joining_date') ?? data_get($employee, 'join_date');
    $joinDate = '';
    if ($joinDateRaw) {
        try {
            $joinDate = $isBangla && function_exists('bn_date')
                ? bn_date($joinDateRaw, 'd/m/Y')
                : \Carbon\Carbon::parse($joinDateRaw)->format('d/m/Y');
        } catch (\Throwable $e) {
            $joinDate = (string) $joinDateRaw;
        }
    }

    $idNumber = data_get($employee, 'employee_id', data_get($employee, 'id', $na));
@endphp

<div class="sfl-card-side">

    {{-- Header decoration --}}
    <div class="sfl-decor">
        <div class="sfl-decor-shape is-navy sfl-decor-fh-navy"></div>
        <div class="sfl-decor-shape is-gold sfl-decor-fh-gold"></div>
    </div>

    {{-- Logo row --}}
    <div class="sfl-logo-area">
        <div class="sfl-logo-text">
            <img src="{{ asset(general()->logo()) }}" alt="{{ $employeeName }}" class="sfl-logo-img">
        </div>
    </div>

    {{-- Photo --}}
    <div class="sfl-photo-wrap">
        <img src="{{ asset($employee->image()) }}" alt="{{ $employeeName }}" class="sfl-photo">
    </div>

    {{-- Info grid --}}
    <div class="sfl-info">
        <span class="sfl-info-label">{{ $t('বাহকের নাম', 'Name') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $employeeName }}</span>

        <span class="sfl-info-label">{{ $t('কার্ড নং', 'Card No.') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $idNumber }}</span>

        <span class="sfl-info-label">{{ $t('পদবী', 'Designation') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $sflDesignation }}</span>

        <span class="sfl-info-label">{{ $t('বিভাগ/শাখা', 'Dept./Section') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $sflDepartment }}</span>

        <span class="sfl-info-label">{{ $t('রক্তের গ্রুপ', 'Blood Group') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $bloodGroup }}</span>

        @if($nidNumber)
        <span class="sfl-info-label">{{ $t('পরিচয়পত্র নং', 'NID No.') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $nidNumber }}</span>
        @endif

        <span class="sfl-info-label">{{ $t('মোবাইল', 'Mobile') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $mobile }}</span>

        <span class="sfl-info-label">{{ $t('স্থায়ী ঠিকানা', 'Perm. Address') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $permanentAddress }}</span>

        <span class="sfl-info-label">{{ $t('যোগদান', 'Join Date') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $joinDate ?: $na }}</span>

        <span class="sfl-info-label">{{ $t('মেয়াদ', 'Validity') }}</span>
        <span class="sfl-info-colon">:</span>
        <span class="sfl-info-value">{{ $t('পরবর্তী আদেশ পর্যন্ত', 'Until further notice') }}</span>
    </div>

    {{-- Signature --}}
    <div class="sfl-sign-row">
        <div>
            <div class="sfl-sign-line"></div>
            <div class="sfl-sign-label">{{ $t('কর্তৃপক্ষের স্বাক্ষর', 'Authority Signature') }}</div>
        </div>
    </div>

    {{-- Footer decoration --}}
    <div class="sfl-decor">
        <div class="sfl-decor-shape is-navy sfl-decor-ff-navy"></div>
        <div class="sfl-decor-shape is-gold sfl-decor-ff-gold"></div>
    </div>

</div>
