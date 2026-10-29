@extends('printMaster2')

@section('title', 'Absence Letter')

{{--
    Absence letters for a lefty employee (Bangladesh Labour Act 2006, section 27(3A)), laid out
    like the company's own templates. What the templates mark in yellow comes from data: the
    employee, their addresses and the Final Settlement form (absent date, 1st / 2nd / 3rd letter
    dates). The rest of the text is the templates' own wording.
--}}
@php
    $hideGeneratedNote = true;

    $selectedOption = data_get($settlement, 'final_settlement_option');
    $letterNo = ['1st Letter' => 1, '2nd Letter' => 2, '3rd Letter' => 3][$selectedOption] ?? null;

    $bnDate = fn ($date) => $date ? bn_date($date, 'd/m/Y') : '';
    $absentDate = data_get($settlement, 'absent_date');
    $letterDates = [
        1 => data_get($settlement, 'letter_1_date'),
        2 => data_get($settlement, 'letter_2_date'),
        3 => data_get($settlement, 'letter_3_date'),
    ];

    // সূত্রঃ সুহানা/মানবসম্পদ ও কমপ্লায়েন্স/<year>/<month>/<letter no> - year and month of the first day absent.
    $refDate = $absentDate ?: ($letterNo ? $letterDates[$letterNo] : null);
    $refBase = $refDate ? \Carbon\Carbon::parse($refDate) : now();
    $reference = 'সুহানা/মানবসম্পদ ও কমপ্লায়েন্স/' . en2bnNumber($refBase->format('Y') . '/' . $refBase->format('n') . '/' . sprintf('%02d', $letterNo ?? 1));

    $designationModel = $employee->designation_id ? \ME\Hr\Models\HrDesignation::find($employee->designation_id, ['id', 'name', 'bn_name']) : null;
    $departmentModel = $employee->department;

    $employeeName = $employee->bn_name ?: $employee->name;
    $info = [
        'নামঃ' => $employeeName,
        'পদবীঃ' => ($designation_bn ?? null) ?: ($designationModel?->bn_name ?: $designationModel?->name),
        'আইডি.নংঃ' => en2bnNumber((string) $employee->employee_id),
        'ডিপার্টমেন্টঃ' => $departmentModel?->bn_name ?: $departmentModel?->name,
        'যোগদানের তারিখঃ' => $bnDate($employee->join_date),
    ];
    $address = fn (string $type) => [
        'নামঃ' => $employeeName,
        'পিতাঃ' => $employee->father_name_bn ?: $employee->father_name,
        'গ্রাম/মহল্লাঃ' => $employee->{"{$type}_village_bn"} ?: $employee->{"{$type}_village"},
        'ডাকঘরঃ' => $employee->{"{$type}_post_office_bn"} ?: $employee->{"{$type}_post_office"},
        'থানাঃ' => $employee->{"{$type}_upazila_bn"} ?: $employee->{"{$type}_upazila"},
        'জেলাঃ' => $employee->{"{$type}_district_bn"} ?: $employee->{"{$type}_district"},
    ];

    // A date in the body; blank dashes when that date isn't entered yet.
    $d = fn ($date) => e($bnDate($date) ?: '__/__/____');
@endphp

@push('css')
<style>
    @page { size: A4 portrait; margin: 14mm 16mm; }
    .container { max-width: 190mm; }
    .al { font-size: 13.5px; line-height: 1.65; color: #000; }
    .al .co { text-align: center; font-size: 30px; font-weight: 800; line-height: 1.2; }
    .al .co-addr { text-align: center; font-size: 14px; }
    .al .act { text-align: center; font-size: 16px; font-weight: 700; margin: 10px 0 14px; }
    .al .info { display: flex; gap: 16px; margin: 6px 0 18px; }
    .al .info > div { flex: 1; }
    .al .info table { width: auto; margin: 0; border: 0; }
    .al .info td { border: 0; padding: 0 4px 0 0; font-size: 13.5px; background: none; vertical-align: top; }
    .al .info td.k { white-space: nowrap; }
    .al .info .h { font-weight: 700; text-decoration: underline; min-height: 1.65em; }
    .al .subject { margin-bottom: 14px; }
    .al .subject b { text-decoration: underline; }
    .al p { margin: 0 0 12px; text-align: justify; }
    .al .closing { margin-top: 18px; }
    .al .sign { margin-top: 58px; }
</style>
@endpush

@section('contents')
<div class="al">
    <div class="co">সুহানা ফ্যাশন্স লিঃ</div>
    <div class="co-addr">কাঠগড়া, জিরাবো, আশুলিয়া, ঢাকা।</div>
    <div class="act">(অনুমোদিত অনুপস্থিতির জন্য বাংলাদেশ শ্রম আইন'২০০৬ এর ২৭ (৩ক) ধারা মোতাবেক)</div>

    <div>সূত্রঃ {{ $reference }}</div>
    <div>তারিখ: {{ $d($letterNo ? $letterDates[$letterNo] : null) }} ইং রেজিস্ট্রার ডাকে প্রেরিত</div>

    <div class="info">
        <div>
            <table>@foreach($info as $k => $v)<tr><td class="k">{{ $k }}</td><td>{{ $v }}</td></tr>@endforeach</table>
        </div>
        @foreach(['present' => 'বর্তমান ঠিকানাঃ', 'permanent' => 'স্থায়ী ঠিকানাঃ'] as $type => $heading)
            <div>
                <div class="h">{{ $heading }}</div>
                <table>@foreach($address($type) as $k => $v)<tr><td class="k">{{ $k }}</td><td>{{ $v }}</td></tr>@endforeach</table>
            </div>
        @endforeach
    </div>

    @if($letterNo === 3)
        <div class="subject"><b>বিষয়ঃ বাংলাদেশ শ্রম আইন, ২০০৬ এর ২৭(৩ক) ধারা মতে চাকুরী হতে অব্যহতি গ্রহন প্রদান।</b></div>
    @else
        <div class="subject"><b>বিষয়ঃ</b> অননুমোদিত অনুপস্থিতির কারনব্যাখ্যা সহ অনতিবিলম্বে কাজে যোগদান প্রসঙ্গে।</div>
    @endif

    <p>জনাবা/জনাব,</p>

    @if($letterNo === 1)
        <p>আপনি গত {{ $d($absentDate) }} ইং তারিখ হতে অদ্যাবধি বিনা নোটিশে এবং কর্তৃপক্ষের বিনা অনুমতিতে কারখানায় অনুপস্থিত রয়েছেন। আপনার এ ধরনের অনুপস্থিতির কারনে কারখানায় উৎপাদন ব্যহত হচ্ছে বিধায় অনতিবিলম্বে আপনাকে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদানের অথবা আপনার এ ধরনের বিনা নোটিশে বা উদ্ধর্তন কর্তৃপক্ষের অনুমতি ব্যতিরেকে কারখানায় ১০ দিনের অধিক সময় অনুপস্থিত থাকার বিষয়ে ব্যাখ্যা প্রদানের নির্দেশ প্রদান করা হল।</p>
        <p>আপনি অদ্য হতে ১০ দিনের মধ্যে কারখানায় যোগদান করতে ব্যর্থ বা আপনার এধরনের অননুমোদিত অনুপস্থিতির কারন উক্ত সময়ের মধ্যে কর্তৃপক্ষের নিকট ব্যাখ্যা প্রদান করতে ব্যর্থ হন তবে কর্তৃপক্ষ আপনার বিরুদ্ধে পরবর্তী আইনানুগ ব্যবস্থা গ্রহনকরবে।</p>
    @elseif($letterNo === 2)
        <p>আপনি গত {{ $d($absentDate) }} ইং তারিখ হতে অদ্যাবধি কারখানায় অননুমোদিত ভাবে অনুপস্থিত রয়েছেন এবং উক্ত অনুপস্থিতির বিষয়ে কারখানা কর্তৃপক্ষকে পূর্বে কোন প্রকার নোটিশ প্রদান বা অবহিত করেননি।</p>
        <p>আপনাকে অনতিবিলম্বে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদান বা উক্ত অনুপস্থিতির কারন ব্যাখ্যা করনের নির্দেশ প্রদান করে গত {{ $d($letterDates[1]) }} ইং তারিখ একটি চিঠি ইস্যু করা হয়। আপনি প্রদত্ত সময় অতিক্রম করা স্বত্তেও অদ্যাবধি কাজে যোগদান বা অনুপস্থিতির কারন ব্যাখ্যা করতে ব্যর্থ হন।</p>
        <p>তথাপি আপনার আত্মপক্ষ সমর্থনের ন্যায় বিচারের স্বার্থে এবং আইনগত অধিকার সংরক্ষনে আপনাকে অনতিবিলম্বে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদানের অথবা আপনার এ ধরনের বিনা নোটিশে বা উদ্ধর্তন কর্তৃপক্ষের অনুমতি ব্যতিরেকে কারখানায় অনুপস্থিত থাকার বিষয়ে ব্যাখ্যা প্রদানের জন্য পুনরায় ১০ দিন সময় প্রদান করা হল।</p>
        <p>আপনি অদ্য হতে ১০ দিনের মধ্যে যদি অনুপস্থিতির কারন ব্যাখ্যা সহ কারখানায় যোগদান করতে ব্যর্থ হন বা আপনার এধরনের অননুমোদিত অনুপস্থিতির কারন উক্ত সময়ের মধ্যে কর্তৃপক্ষের নিকট ব্যাখ্যা প্রদান করতে ব্যর্থ হন তবে বাংলাদেশ শ্রম-আইন, ২০০৬ এর ২৭(৩ক) ধারা মতে আপনি চাকুরী হতে অব্যহতি গ্রহন করেছেন এবং কোম্পানীর সাথে আপনার যাবতীয় সম্পর্ক ছিন্ন হয়েছে বলে গন্য হবে।</p>
    @elseif($letterNo === 3)
        <p>আপনি গত {{ $d($absentDate) }} ইং তারিখ হতে অদ্যাবধি কারখানায় অনুমোদিত ভাবে অনুপস্থিত রয়েছেন এবং এ অনুপস্থিতির বিষয়ে কারখানা কর্তৃপক্ষকে পূর্বে কোন প্রকার নোটিশ প্রদান বা অবহিত করেননি।</p>
        <p>আপনাকে অনতিবিলম্বে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদান বা উক্ত অনুপস্থিতির কারন ব্যাখ্যা দানের জন্য ১০ দিনের সময় দিয়ে গত {{ $d($letterDates[1]) }} ইং তারিখ একটি চিঠি ইস্যু করা হয়।</p>
        <p>আপনি উক্ত সময়ের মধ্যে কাজে যোগদান বা আপনার অনুপস্থিতির কারন ব্যাখ্যা করতে ব্যর্থ হওয়ায় পুনরায় আপনাকে ১০ দিনের সময় দিয়ে {{ $d($letterDates[2]) }} ইং তারিখে আরও একটি চিঠি ইস্যু করা হয়। আপনি দ্বিতীয়বারও প্রদত্ত সময়ের মধ্যে কাজে যোগদান বা আপনার অনুপস্থিতির কারন ব্যাখ্যা করতে ব্যর্থ হন।</p>
        <p>যেহেতু আপনাকে বাংলাদেশ শ্রম-আইন, ২০০৬ এর ২৭(৩ক) ধারা মতে অনতিবিলম্বে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদান বা উক্ত অনুপস্থিতির কারন ব্যাখ্যা দানের জন্য আপনার আইনগত অধিকার সংরক্ষন করে দুইবার সময় প্রদান করা স্বত্ত্বেও আপনি যথা সময়ে অনুপস্থিতির কারন ব্যাখ্যা সহ কাজে যোগদান করতে অথবা অনুপস্থিতির কারন ব্যাখ্যা করতে ব্যর্থ হয়েছেন সেহেতু আপনি বাংলাদেশ শ্রম-আইন, ২০০৬ এর ২৭(৩ক) ধারা মতে চাকুরী হতে আপনার অনুপস্থিতির দিন থেকে অর্থাৎ, গত {{ $d($absentDate) }} ইং তারিখ হতে অব্যহতি গ্রহন করেছেন বলে গন্য হল এবং আপনাকে আইনগত ভাবে চাকুরী থেকে অব্যাহতি প্রদান করা হইল।</p>
    @else
        <p style="color:#b91c1c">Select 1st, 2nd or 3rd Letter in Final Settlement Info, then print again.</p>
    @endif

    <div class="closing">
        ধন্যবাদান্তে,<br>
        সুহানা ফ্যাশন্স লিঃ<br>
        পক্ষে,
    </div>

    <div class="sign">
        <b>ব্যবস্থাপক (মানব সম্পদ,প্রশাসন ও কমপ্লায়েন্স)</b><br>
        <b>সুহানা ফ্যাশন্স লিঃ</b><br>
        অনুলিপি : ব্যক্তিগত নথি
    </div>
</div>
@endsection
