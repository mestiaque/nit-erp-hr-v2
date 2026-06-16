@php
    $language = $language ?? data_get($request ?? null, 'language', 'en');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');

    $companyName = $isBangla
        ? (hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na)
        : (hr_factory('name') ?? general()->name ?? hr_factory('bn_name') ?? $na);
    $companyAddress = $isBangla
        ? (hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na)
        : (hr_factory('address') ?? general()->address ?? hr_factory('bn_address') ?? $na);

    $employeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na)
        : (data_get($employee, 'name') ?? data_get($employee, 'bn_name') ?? $na);

    $designationModel = optional($employee->designation);
    $designation = $isBangla
        ? ($designationModel->bn_name ?? $designationModel->name ?? data_get($employee, 'designation_bn_name') ?? data_get($employee, 'designation_name') ?? $na)
        : ($designationModel->name ?? data_get($employee, 'designation_name') ?? $designationModel->bn_name ?? data_get($employee, 'designation_bn_name') ?? $na);

    $sectionModel = optional($employee->section);
    $section = $isBangla
        ? ($sectionModel->bn_name ?? $sectionModel->name ?? data_get($employee, 'section_bn_name') ?? data_get($employee, 'section_name') ?? $na)
        : ($sectionModel->name ?? data_get($employee, 'section_name') ?? $sectionModel->bn_name ?? data_get($employee, 'section_bn_name') ?? $na);

    $joiningDate = blank($employee->joining_date) ? $na : \Illuminate\Support\Carbon::parse($employee->joining_date)->format('d/m/Y');
    $today = now()->format('d/m/Y');
@endphp

<div class="letter-box" style="border:none; padding:0; margin-top:0;">
    <div class="company-head" style="margin-bottom:10px;">
        <h3 style="margin:0; font-size:22px;">{{ $companyName }}</h3>
        <div style="font-size:13px;">{{ $companyAddress }}</div>
        <div style="margin-top:4px; font-weight:700; font-size:16px;">{{ $t('চাকরির নিশ্চয়তাপত্র', 'Employment Letter') }}</div>
    </div>

    <p style="font-size:12px; line-height:1.7; margin-bottom:8px;">{{ $t('তারিখ', 'Date') }}: {{ $today }}</p>
    <p style="font-size:12px; line-height:1.7; margin-bottom:8px;">{{ $t('প্রাপক', 'Employee') }}: {{ $employeeName }}</p>

    <p style="font-size:12px; line-height:1.7; text-align:justify; margin-bottom:10px;">
        {{ $t('এই মর্মে প্রত্যয়ন করা যাচ্ছে যে, আপনি আমাদের প্রতিষ্ঠানে', 'This is to certify that you are employed in our organization as') }}
        <strong>{{ $designation }}</strong>
        {{ $t('পদে,', 'in the') }}
        <strong>{{ $section }}</strong>
        {{ $t('সেকশনে,', 'section,') }}
        {{ $t('তারিখ', 'since') }} {{ $joiningDate }}
        {{ $t('থেকে কর্মরত আছেন।', '.') }}
    </p>

    <p style="font-size:12px; line-height:1.7; text-align:justify; margin-bottom:10px;">
        {{ $t('কোম্পানির নীতিমালা ও প্রযোজ্য শ্রম আইন অনুযায়ী আপনার চাকরির শর্তাবলী কার্যকর থাকবে।', 'Your employment remains subject to company policy and applicable labor laws.') }}
    </p>

    <p style="font-size:12px; line-height:1.7; margin-top:24px;">
        {{ $t('কর্তৃপক্ষের স্বাক্ষর', 'Authorized Signature') }}: ______________________
    </p>
</div>
