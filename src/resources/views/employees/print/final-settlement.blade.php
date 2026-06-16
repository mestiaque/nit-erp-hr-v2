@extends('printMaster2')

@section('title', 'Final Settlement Letter')

@section('contents')
@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
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

    $fatherName = data_get($employee, 'father_name', $na);
    $motherName = data_get($employee, 'mother_name', $na);
    $presentVillage = data_get($employee, 'present_village', $na);
    $presentPostOffice = data_get($employee, 'present_post_office', $na);
    $presentUpazila = data_get($employee, 'present_upazila', $na);
    $presentDistrict = data_get($employee, 'present_district', $na);
    $permanentVillage = data_get($employee, 'permanent_village', $na);
    $permanentPostOffice = data_get($employee, 'permanent_post_office', $na);
    $permanentUpazila = data_get($employee, 'permanent_upazila', $na);
    $permanentDistrict = data_get($employee, 'permanent_district', $na);

    $designationModel = data_get($employee, 'designation');
    if (!$designationModel && data_get($employee, 'designation_id')) {
        $designationModel = \ME\Hr\Models\HrDesignation::query()
            ->select(['id', 'name', 'bn_name'])
            ->find(data_get($employee, 'designation_id'));
    }
    $designation = $isBangla
        ? ($designation_bn ?? data_get($designationModel, 'bn_name') ?? data_get($designationModel, 'name') ?? data_get($employee, 'designation_bn_name') ?? data_get($employee, 'designation_name') ?? $na)
        : ($designation_en ?? data_get($designationModel, 'name') ?? data_get($employee, 'designation_name') ?? data_get($designationModel, 'bn_name') ?? data_get($employee, 'designation_bn_name') ?? $na);

    $sectionAttr = $employee->section_id
        ? \ME\Hr\Models\HrSection::query()->find($employee->section_id, ['id', 'name', 'bn_name'])
        : null;
    $section = $isBangla
        ? (data_get($sectionAttr, 'bn_name') ?? data_get($sectionAttr, 'name') ?? $na)
        : (data_get($sectionAttr, 'name') ?? data_get($sectionAttr, 'bn_name') ?? $na);

    $departmentModel = optional($employee->department);
    $department = $isBangla
        ? ($departmentModel->bn_name ?? $departmentModel->name ?? data_get($employee, 'department_bn_name') ?? data_get($employee, 'department_name') ?? $na)
        : ($departmentModel->name ?? data_get($employee, 'department_name') ?? $departmentModel->bn_name ?? data_get($employee, 'department_bn_name') ?? $na);

    $presentAddress = trim(implode(', ', array_filter([
        $presentVillage,
        $presentPostOffice,
        $presentUpazila,
        $presentDistrict,
    ])));
    $presentAddress = $presentAddress !== '' ? $presentAddress : $na;

    $permanentAddress = trim(implode(', ', array_filter([
        $permanentVillage,
        $permanentPostOffice,
        $permanentUpazila,
        $permanentDistrict,
    ])));
    $permanentAddress = $permanentAddress !== '' ? $permanentAddress : $na;

    $applicationDate = now()->format('d/m/Y');

    $gender = strtolower(trim((string) ($employee->gender ?? '')));
    $salutation = match(true) {
        in_array($gender, ['male', 'পুরুষ']) => 'জনাব',
        in_array($gender, ['female', 'মহিলা', 'নারী']) => 'জনাবা',
        default => 'জনাব/জনাবা',
    };
@endphp
@php
    $selectedOption = data_get($settlement, 'final_settlement_option');
    $absentDate = data_get($settlement, 'absent_date');
    $firstLetterDate = data_get($settlement, 'letter_1_date');
    $secondLetterDate = data_get($settlement, 'letter_2_date');
    $today = now()->format('Y-m-d');
    $absentDays = 0;
    if (!empty($absentDate)) {
        try {
            $absentDays = (int) \Carbon\Carbon::parse($absentDate)->diffInDays(now()) + 1;
        } catch (\Throwable $e) {
            $absentDays = 0;
        }
    }

    $finalSettlementDeadline = now()->addDays(7)->format('Y-m-d');
    $letterLabel = [
        '1st Letter' => 'প্রথম চিঠি: কারণ দর্শানোর নোটিশ (Show Cause Notice)',
        '2nd Letter' => 'দ্বিতীয় চিঠি: পুনরায় যোগদানের নির্দেশ ও চূড়ান্ত সতর্কীকরণ',
        '3rd Letter' => 'তৃতীয় চিঠি: চূড়ান্ত সেটেলমেন্ট ও অব্যাহতি',
    ][$selectedOption] ?? '';
@endphp

<div style="text-align:center; margin-bottom:10px;">
    <h3 style="margin:0;">{{ $companyName }}</h3>
    <div>{{ $companyAddress }}</div>
    <div style="margin-top:4px; font-weight:700; font-size:16px;">{{ $t($letterLabel, $letterLabel) }}</div>
</div>

{{-- <table style="margin-bottom: 8px;">
    <tr>
        <th style="width: 18%;">তারিখ</th>
        <td style="width: 32%;">{{ $applicationDate }}</td>
        <th style="width: 18%;">রেফারেন্স</th>
        <td style="width: 32%;">FS/{{ $employee->employee_id ?? $employee->id }}/{{ now()->format('Ymd') }}</td>
    </tr>
    <tr>
        <th>নাম</th>
        <td>{{ $employeeName }}</td>
        <th>কার্ড নং</th>
        <td>{{ $employee->employee_id ?? $employee->id }}</td>
    </tr>
    <tr>
        <th>পদবী</th>
        <td>{{ $designation }}</td>
        <th>বিভাগ</th>
        <td>{{ $department }}</td>
    </tr>
    <tr>
        <th>সেকশন</th>
        <td>{{ $section }}</td>
        <th>মোট অনুপস্থিতি</th>
        <td>{{ $absentDays }} দিন</td>
    </tr>
    <tr>
        <th>পিতার নাম</th>
        <td>{{ $fatherName }}</td>
        <th>মাতার নাম</th>
        <td>{{ $motherName }}</td>
    </tr>
    <tr>
        <th>বর্তমান ঠিকানা</th>
        <td colspan="3">{{ $presentAddress }}</td>
    </tr>
    <tr>
        <th>স্থায়ী ঠিকানা</th>
        <td colspan="3">{{ $permanentAddress }}</td>
    </tr>
</table> --}}

<div style="margin-top: 20px; line-height: 1.8; font-size: 14px;">
    @if($selectedOption === '1st Letter')
        <p><strong>বিষয়: অননুমোদিত অনুপস্থিতির জন্য কারণ দর্শানোর নোটিশ।</strong></p>
        <p>{{ $salutation }} {{ $employeeName }}, কার্ড নং- {{ $employee->employee_id ?? $employee->id }}, পদবী- {{ $designation }}</p>
        <p>
            আপনাকে জানানো যাচ্ছে যে, আপনি গত {{ $absentDate ?: 'N/A' }} তারিখ হতে অদ্যবধি {{ $absentDays }} দিন যাবত
            কর্তৃপক্ষের কোনো অনুমতি ছাড়াই কর্মস্থলে অনুপস্থিত রয়েছেন। বাংলাদেশ শ্রম আইন মোতাবেক বিনা অনুমতিতে
            ১০ দিনের অধিক অনুপস্থিত থাকা একটি "অসদাচরণ"।
        </p>
        <p>
            আপনার দায়িত্বপ্রাপ্ত বিভাগ {{ $department }} এবং সেকশন {{ $section }}-এর কার্যক্রমে আপনার অনুপস্থিতির কারণে
            উৎপাদন পরিকল্পনা, কর্মপ্রবাহ এবং টিম ম্যানেজমেন্টে নেতিবাচক প্রভাব পড়ছে। প্রতিষ্ঠানের নীতিমালা অনুযায়ী,
            অনুপস্থিতির ক্ষেত্রে যথাযথ কর্তৃপক্ষকে অবহিত করা ও অনুমোদন গ্রহণ বাধ্যতামূলক।
        </p>
        <p>
            এমতাবস্থায়, কেন আপনার বিরুদ্ধে আইনানুগ ব্যবস্থা গ্রহণ করা হবে না, তা অত্র পত্র প্রাপ্তির ০৭ (সাত) দিনের
            মধ্যে লিখিতভাবে জানানোর জন্য নির্দেশ দেওয়া হলো।
        </p>
        <p>
            আপনার লিখিত ব্যাখ্যায় অনুপস্থিতির কারণ, প্রমাণক নথি (যদি থাকে), এবং পুনরায় কাজে যোগদানের সম্ভাব্য
            তারিখ উল্লেখ করতে হবে। নির্ধারিত সময়ের মধ্যে ব্যাখ্যা দাখিল না করলে বিষয়টি একতরফাভাবে নিষ্পত্তি করে
            আইনানুগ পরবর্তী ব্যবস্থা গ্রহণ করা হবে।
        </p>
        <p>ধন্যবাদান্তে,</p>
    @elseif($selectedOption === '2nd Letter')
        <p><strong>বিষয়: কর্মস্থলে যোগদানের চূড়ান্ত নোটিশ ও সতর্কীকরণ।</strong></p>
        <p>{{ $salutation }} {{ $employeeName }}, কার্ড নং- {{ $employee->employee_id ?? $employee->id }}, পদবী- {{ $designation }}</p>
        <p>
            গত {{ $firstLetterDate ?: 'N/A' }} তারিখে আপনাকে অনুপস্থিতির কারণ দর্শানোর নোটিশ পাঠানো হয়েছিল,
            যার কোনো উত্তর আমরা পাইনি। আপনি এখনও কর্মস্থলে অনুপস্থিত। আপনার এই আচরণ প্রতিষ্ঠানের উৎপাদন ও
            শৃঙ্খলার পরিপন্থী।
        </p>
        <p>
            বারংবার অনুপস্থিতি এবং প্রতিষ্ঠানের সাথে যোগাযোগ না রাখার ফলে আপনার দায়িত্বাধীন কাজগুলোতে বিলম্ব,
            সহকর্মীদের উপর অতিরিক্ত চাপ এবং প্রতিষ্ঠানের আর্থিক ও প্রশাসনিক ক্ষতির ঝুঁকি সৃষ্টি হয়েছে। এটি শ্রম আইন,
            নিয়োগপত্রের শর্তাবলি এবং প্রতিষ্ঠানিক আচরণবিধির লঙ্ঘন হিসেবে বিবেচিত।
        </p>
        <p>
            আপনাকে অত্র নোটিশ প্রাপ্তির ০৩ (তিন) দিনের মধ্যে কর্মস্থলে যোগদান করে আপনার অনুপস্থিতির সন্তোষজনক
            লিখিত ব্যাখ্যা প্রদানের চূড়ান্ত সুযোগ দেওয়া হলো। অন্যথায়, শ্রম আইন অনুযায়ী আপনার নিয়োগ বাতিল করার
            প্রক্রিয়া শুরু করা হবে।
        </p>
        <p>
            নির্ধারিত সময়ের মধ্যে যোগদান বা গ্রহণযোগ্য ব্যাখ্যা প্রদান করতে ব্যর্থ হলে, পরবর্তী নোটিশ ছাড়াই আপনার
            বিরুদ্ধে চূড়ান্ত প্রশাসনিক ব্যবস্থা নেওয়া হবে এবং বিষয়টি কর্মী নথিতে সংরক্ষিত থাকবে।
        </p>
        <p>ধন্যবাদান্তে,</p>
    @elseif($selectedOption === '3rd Letter')
        <p><strong>বিষয়: দীর্ঘস্থায়ী অনুপস্থিতির কারণে চাকুরীর অবসান সংক্রান্ত চূড়ান্ত নোটিশ।</strong></p>
        <p>{{ $salutation }} {{ $employeeName }}, কার্ড নং- {{ $employee->employee_id ?? $employee->id }}, পদবী- {{ $designation }}</p>
        <p>
            আপনি গত {{ $absentDate ?: 'N/A' }} থেকে কর্মস্থলে অননুমোদিতভাবে অনুপস্থিত। ইতিপূর্বে আপনাকে
            {{ $firstLetterDate ?: 'N/A' }} এবং {{ $secondLetterDate ?: 'N/A' }} তারিখে দুটি নোটিশ রেজিস্টার্ড
            ডাকযোগে পাঠানো সত্ত্বেও আপনি কোনো যোগাযোগ করেননি।
        </p>
        <p>
            আপনার বিরুদ্ধে আনা অভিযোগসমূহের বিষয়ে আত্মপক্ষ সমর্থনের যথাযথ সুযোগ প্রদান করা হলেও আপনি তা গ্রহণ
            করেননি এবং প্রতিষ্ঠানের সাথে কোনো প্রকার কার্যকর যোগাযোগ স্থাপন করেননি। ফলে বিষয়টি শ্রম আইন ও
            কোম্পানির প্রযোজ্য বিধান অনুযায়ী নিষ্পত্তি করা আবশ্যক হয়ে পড়েছে।
        </p>
        <p>
            এমতাবস্থায়, বাংলাদেশ শ্রম আইন ২০০৬ এর ধারা ২৭(৩ক) অনুযায়ী, আপনি স্বেচ্ছায় চাকুরীতে ইস্তফা দিয়েছেন
            বলে গণ্য করা হলো। অত্র {{ $today }} হতে আপনার নাম অত্র প্রতিষ্ঠানের মাস্টার রোল হতে কর্তন করা হলো।
            আপনার পাওনাদি বা ফাইনাল সেটেলমেন্ট (Final Settlement) গ্রহণের জন্য আগামী {{ $finalSettlementDeadline }}
            এর মধ্যে মানব সম্পদ বিভাগে যোগাযোগ করতে বলা হলো।
        </p>
        <p>
            নির্ধারিত সময়ের মধ্যে উপস্থিত হয়ে আপনার বকেয়া পাওনা নিষ্পত্তি, প্রতিষ্ঠানের নথি হালনাগাদ, এবং যদি থাকে
            কোম্পানির সম্পদ/আইডি কার্ড/ইউনিফর্ম ফেরত দেওয়ার অনুরোধ করা হলো। পরবর্তী কোনো দাবি-দাওয়া প্রতিষ্ঠানের
            নীতিমালা ও প্রযোজ্য আইন অনুযায়ী বিবেচিত হবে।
        </p>
        <p>ধন্যবাদান্তে,</p>
    @endif
</div>

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">[কর্তৃপক্ষের স্বাক্ষর]</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">[প্রতিষ্ঠানের নাম ও সিল]</div>
    </div>
</div>
@endsection
