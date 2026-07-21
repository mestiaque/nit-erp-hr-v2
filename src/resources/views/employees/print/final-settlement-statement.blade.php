@extends('printMaster2')

@section('title', 'Full & Final Settlement Form')

@push('css')
<style>
    .fs-page {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        padding: 8mm 9mm;
        box-sizing: border-box;
        background: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        font-size: 12px;
        line-height: 1.3;
    }
    .fs-head { text-align:center; margin-bottom:4px; }
    .fs-head img { max-height:30px; margin-bottom:2px; }
    .fs-head h2 { margin:0 0 1px; font-size:15px; text-transform:uppercase; letter-spacing:.3px; }
    .fs-head p { margin:0; font-size:9.5px; color:#333; }
    .fs-title-bar { text-align:center; margin:6px 0 2px; padding:3px 0; }
    .fs-title-bar h3 { margin:0; font-size:12.5px; letter-spacing:.3px; }
    .fs-title-bar span { font-size:9px; color:#444; }
    .fs-law-note { text-align:center; font-size:8.5px; color:#555; margin-bottom:4px; }

    .fs-meta { display:flex; justify-content:space-between; font-size:9.5px; margin-bottom:5px; }

    .fs-part-title { font-size:11px; font-weight:700; background:#e9ecef; padding:2px 6px; margin:8px 0 4px; }
    .fs-part-title .part-no { display:inline-block; width:16px; height:16px; line-height:16px; text-align:center; background:#222; color:#fff; border-radius:50%; font-size:9.5px; margin-right:5px; }

    .fs-info-wrap { display:flex; gap:8px; margin-bottom:4px; align-items:flex-start; }
    .fs-photo { width:20mm; height:24mm; object-fit:cover; border:1px solid #888; flex-shrink:0; }
    .fs-photo-placeholder { width:20mm; height:24mm; border:1px solid #888; display:flex; align-items:center; justify-content:center; color:#999; font-size:8.5px; flex-shrink:0; }

    .fs-table { width:100%; border-collapse:collapse; margin-bottom:4px; }
    .fs-table th, .fs-table td { border:1px solid #666; padding:2px 6px; font-size:9.5px; vertical-align:top; }
    .fs-table th { width:24%; background:#f4f5f6; text-align:left; font-weight:600; }

    .fs-fin-wrap { display:flex; gap:8px; margin-bottom:4px; }
    .fs-fin-col { flex:1; }
    .fs-fin-col-title { text-align:center; font-weight:700; font-size:9.5px; padding:2px; color:#fff; }
    .fs-fin-col-title.payable { background:#0b6b0b; }
    .fs-fin-col-title.deduct { background:#a51616; }
    .fs-fin-table { width:100%; border-collapse:collapse; }
    .fs-fin-table th, .fs-fin-table td { border:1px solid #666; padding:2px 4px; font-size:8.7px; }
    .fs-fin-table th { background:#f4f5f6; }
    .fs-fin-table .law-ref { display:block; font-size:7.5px; color:#555; font-style:italic; }
    .fs-fin-table .tr { text-align:right; white-space:nowrap; }
    .fs-fin-table .total-row td { font-weight:700; background:#f0f0f0; }

    .fs-net-row { display:flex; justify-content:flex-end; margin:5px 0 2px; margin-top: -55px}
    .fs-net-box { border:2px solid #222; padding:4px 12px; text-align:right; min-width:220px; }
    .fs-net-box .lbl { font-size:10px; font-weight:700; }
    .fs-net-box .amt { font-size:13px; font-weight:700; }
    .fs-inwords { text-align:right; font-size:9.5px; margin-bottom:3px; }

    .fs-declaration { border:1px solid #666; padding:6px 8px; font-size:9.5px; line-height:1.45; text-align:justify; background:#fcfcfc; }
    .fs-declaration strong { color:#000; }

    .fs-signoff-grid { display:flex; justify-content:space-between; margin-top:40px; gap:8px; }
    .fs-signoff-box { flex:1; text-align:center; }
    .fs-signoff-line { border-top:1px solid #333; padding-top:2px; font-size:9.5px; margin-top:20px; }
    .fs-signoff-role { font-size:8.5px; color:#555; }

    .fs-emp-sign-row { display:flex; justify-content:space-between; margin-top:50px; }
    .fs-emp-sign-box { width:45%; text-align:center; }
    .fs-emp-sign-line { border-top:1px solid #333; padding-top:2px; font-size:9.5px; }

    .fs-checkbox-list { list-style:none; margin:0 0 4px; padding:0; display:flex; flex-wrap:wrap; gap:2px 14px; font-size:9.5px; }
    .fs-checkbox-list li { white-space:nowrap; }
    .fs-checkbox-list .law-ref { font-size:8px; color:#555; font-style:italic; }

    .fs-fill-line { display:inline-block; border-bottom:1px dotted #333; min-width:70px; }

    .fs-pay-method { font-size:9.5px; margin:4px 0; }
    .fs-pay-method .fs-checkbox-list { margin-bottom:2px; }

    .fs-notes { margin:4px 0; font-size:9px; border:1px solid #ccc; padding:3px 6px; background:#fcfcfc; }
    .fs-status-badge { display:inline-block; padding:1px 8px; border-radius:10px; font-size:9px; font-weight:700; }
    .status-draft { background:#fff3cd; color:#7a5b00; }
    .status-approved { background:#d1ecf1; color:#0c5460; }
    .status-paid { background:#d4edda; color:#155724; }

    @media print {
        @page { size: A4; margin: 8mm; }
        body { margin: 0; }
        .fs-page { box-shadow: none; margin: 0; min-height: 0; }
    }
</style>
@endpush

@section('contents')
@php
    $na = 'প্রযোজ্য নয়';

    $companyName    = hr_factory('bn_name') ?? hr_factory('name') ?? optional(general())->name ?? $na;
    $companyAddress = hr_factory('bn_address') ?? hr_factory('address') ?? optional(general())->address ?? $na;

    $employeeName = data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na;

    $designationModel = data_get($employee, 'designation');
    if (!$designationModel && data_get($employee, 'designation_id')) {
        $designationModel = \ME\Hr\Models\HrDesignation::query()
            ->select(['id', 'name', 'bn_name'])
            ->find(data_get($employee, 'designation_id'));
    }
    $designation = $designation_bn ?? data_get($designationModel, 'bn_name') ?? data_get($designationModel, 'name') ?? $na;

    $departmentModel = optional($employee->department);
    $department = $departmentModel->bn_name ?? $departmentModel->name ?? $na;
    $sectionModel = optional($employee->section);
    $section = $sectionModel->bn_name ?? $sectionModel->name ?? $na;

    $joinDate  = $employee->join_date ? bn_date($employee->join_date) : $na;
    $exitDate  = $employee->exited_at ? bn_date($employee->exited_at) : $na;
    // Issue Date = the 3rd (final) notice letter date when one exists, since that's the
    // date the settlement was formally triggered — falls back to today only when no
    // 3rd letter date has been recorded.
    $issueDate = optional($settlement)->third_letter_date
        ? bn_date($settlement->third_letter_date)
        : bn_date(now());

    $fmtDate = fn ($v) => blank($v) ? $na : bn_date($v);
    $fmt     = fn ($v) => en2bnNumber(number_format((float) ($v ?? 0), 2));
    $bnNum   = fn ($v) => en2bnNumber((string) $v);

    // Service length — computed from join/exit date when both are known (more accurate
    // than the manually-entered service_years figure), falling back to it otherwise.
    if ($employee->join_date && $employee->exited_at) {
        $svc = \Carbon\Carbon::parse($employee->join_date)->diff(\Carbon\Carbon::parse($employee->exited_at));
        $serviceLength = ($svc->y > 0 ? $bnNum($svc->y) . ' বছর ' : '') . ($svc->m > 0 ? $bnNum($svc->m) . ' মাস ' : '') . $bnNum($svc->d) . ' দিন';
    } else {
        $serviceLength = $bnNum((int) (optional($settlement)->service_years ?? 0)) . ' বছর';
    }

    // Separation type — which of the 4 statutory categories applies. Detected from
    // employment_status where the vocabulary allows; left unchecked (for manual tick)
    // when the status doesn't map to a known category.
    $sepStatus = strtolower((string) ($employee->employment_status ?? optional($employee->separation)->status ?? ''));
    $separationType = match (true) {
        in_array($sepStatus, ['terminated', 'termination', 'discharge', 'dismissed'], true) => 'termination',
        in_array($sepStatus, ['retrenched', 'retrenchment'], true) => 'retrenchment',
        in_array($sepStatus, ['retired', 'retirement'], true) => 'retirement',
        in_array($sepStatus, ['resign', 'resigned', 'lefty', 'left'], true) => 'resignation',
        default => null,
    };

    $lastBasic  = (float) optional($settlement)->last_basic_salary;
    $lastGross  = (float) optional($settlement)->last_gross_salary;
    $unpaid     = (float) optional($settlement)->unpaid_salary_amount;
    $leave      = (float) optional($settlement)->leave_encashment_amount;
    $gratuity   = (float) optional($settlement)->gratuity_amount;
    $otherEarn  = (float) optional($settlement)->other_earnings;
    $advance    = (float) optional($settlement)->advance_deduction;
    $otherDed   = (float) optional($settlement)->other_deductions;
    $totalEarn  = $unpaid + $leave + $gratuity + $otherEarn;
    $totalDeduct = $advance + $otherDed;
    $netPayable = optional($settlement)->net_payable !== null
        ? (float) $settlement->net_payable
        : ($totalEarn - $totalDeduct);

    $statusKey = optional($settlement)->settlement_status ?? 'draft';
    $statusLabel = ['draft' => 'খসড়া', 'approved' => 'অনুমোদিত', 'paid' => 'পরিশোধিত'][$statusKey] ?? ucfirst($statusKey);

    $photo = method_exists($employee, 'image') ? $employee->image('md') : null;
@endphp

<div class="fs-page">
<div class="fs-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo">
    @endif
    <h2>{{ $companyName }}</h2>
    <p>{{ $companyAddress }}</p>
</div>

<div class="fs-title-bar">
    <h3>চূড়ান্ত নিষ্পত্তি এবং বকেয়া অর্থ পরিশোধের ঘোষণা ফরম (Full &amp; Final Settlement Form)</h3>
    <span>(বাংলাদেশ শ্রম আইন, ২০০৬ এর ধারা ২৬, ২৭, ৩০, ১১৭ ও শ্রম বিধিমালা, ২০১৫ এর বিধি ১০৭ অনুযায়ী)</span>
</div>
<div class="fs-law-note">
    শ্রম আইনের ধারা ৩০ অনুযায়ী, চাকরি অবসানের তারিখ থেকে ৩০ (ত্রিশ) কার্যদিবসের মধ্যে কর্মচারীর সকল পাওনা পরিশোধ করা মালিকের আইনগত বাধ্যবাধকতা।
</div>

<div class="fs-meta">
    <span><strong>রেফারেন্স নং:</strong> FFS/{{ $employee->employee_id ?? $employee->id }}/{{ now()->format('Y') }}</span>
    <span><strong>ইস্যু তারিখ:</strong> {{ $issueDate }}</span>
    <span><strong>অবস্থা:</strong> <span class="fs-status-badge status-{{ $statusKey }}">{{ $statusLabel }}</span></span>
</div>

{{-- ═══════════ অংশ ১: কর্মচারীর ব্যক্তিগত ও চাকুরির তথ্য ═══════════ --}}
<div class="fs-part-title"><span class="part-no">১</span>কর্মচারীর ব্যক্তিগত ও চাকুরির তথ্য</div>

<div class="fs-info-wrap">
    @if($photo && $photo !== 'medies/profile.png')
        <img src="{{ asset($photo) }}" alt="{{ $employeeName }}" class="fs-photo">
    @else
        <div class="fs-photo-placeholder">ছবি নেই</div>
    @endif

    <table class="fs-table" style="flex:1;">
        <tr>
            <th>নাম</th><td>{{ $employeeName }}</td>
            <th>আইডি নং</th><td>{{ $employee->employee_id ?? $employee->id }}</td>
        </tr>
        <tr>
            <th>পদবী</th><td>{{ $designation }}</td>
            <th>বিভাগ / সেকশন</th><td>{{ $department }} / {{ $section }}</td>
        </tr>
        <tr>
            <th>যোগদানের তারিখ</th><td>{{ $joinDate }}</td>
            <th>শেষ কর্মদিবস</th><td>{{ $exitDate }}</td>
        </tr>
        <tr>
            <th>মোট চাকুরিকাল</th><td colspan="3">{{ $serviceLength }}</td>
        </tr>
        <tr>
            <th>সর্বশেষ বেসিক বেতন</th><td>{{ $fmt($lastBasic) }} টাকা</td>
            <th>সর্বশেষ গ্রস বেতন</th><td>{{ $fmt($lastGross) }} টাকা</td>
        </tr>
    </table>
</div>

<div style="font-size:11.5px; font-weight:600; margin-bottom:4px;">চাকরি অবসানের ধরন (টিক চিহ্ন দিন):</div>
<ul class="fs-checkbox-list">
    <li>{{ $separationType === 'resignation' ? '☑' : '☐' }} পদত্যাগ (Resignation) <span class="law-ref">[ধারা ২৭]</span></li>
    <li>{{ $separationType === 'termination' ? '☑' : '☐' }} মালিক কর্তৃক অব্যাহতি (Termination) <span class="law-ref">[ধারা ২৬]</span></li>
    <li>{{ $separationType === 'retrenchment' ? '☑' : '☐' }} ছাঁটাই (Retrenchment) <span class="law-ref">[ধারা ২০]</span></li>
    <li>{{ $separationType === 'retirement' ? '☑' : '☐' }} অবসর গ্রহণ (Retirement) <span class="law-ref">[ধারা ২৮]</span></li>
</ul>

@if(optional($settlement)->absent_date || optional($settlement)->first_letter_date || optional($settlement)->second_letter_date || optional($settlement)->third_letter_date)
<table class="fs-table">
    <tr>
        <th>অনুপস্থিতির তারিখ থেকে</th><td>{{ $fmtDate(optional($settlement)->absent_date) }}</td>
        <th>১ম নোটিশ</th><td>{{ $fmtDate(optional($settlement)->first_letter_date) }}</td>
    </tr>
    <tr>
        <th>২য় নোটিশ</th><td>{{ $fmtDate(optional($settlement)->second_letter_date) }}</td>
        <th>৩য় নোটিশ</th><td>{{ $fmtDate(optional($settlement)->third_letter_date) }}</td>
    </tr>
</table>
@endif

{{-- ═══════════ অংশ ২: আর্থিক হিসাব ═══════════ --}}
<div class="fs-part-title"><span class="part-no">২</span>আর্থিক হিসাব (পাওনা ও কর্তন)</div>

<div class="fs-fin-wrap">
    <div class="fs-fin-col">
        <div class="fs-fin-col-title payable">প্রাপ্য / পাওনা সমূহ (Payables)</div>
        <table class="fs-fin-table">
            <thead>
                <tr><th style="width:8%;">নং</th><th style="width:62%;">বিবরণ</th><th style="width:30%;">টাকা</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="tc">১</td>
                    <td>বকেয়া দিনের কার্যকালীন মজুরি (বকেয়া বেতন, {{ $bnNum(optional($settlement)->unpaid_salary_days ?? 0) }} দিন)<span class="law-ref">[ধারা ৩০]</span></td>
                    <td class="tr">{{ $fmt($unpaid) }}</td>
                </tr>
                <tr>
                    <td class="tc">২</td>
                    <td>নোটিশ পিরিয়ডের পরিবর্তে নোটিশ পে (যদি প্রযোজ্য হয়)<span class="law-ref">[ধারা ২৬/২৭]</span></td>
                    <td class="tr"><span class="fs-fill-line">&nbsp;</span></td>
                </tr>
                <tr>
                    <td class="tc">৩</td>
                    <td>অব্যবহৃত/অর্জিত ছুটি নগদায়ন ({{ $bnNum(optional($settlement)->leave_encashment_days ?? 0) }} দিন)<span class="law-ref">[ধারা ১১৭ ও বিধি ১০৭]</span></td>
                    <td class="tr">{{ $fmt($leave) }}</td>
                </tr>
                <tr>
                    <td class="tc">৪</td>
                    <td>গ্র্যাচুইটি বা আনুতোষিক (৫+ বছর চাকুরিকালের জন্য)<span class="law-ref">[ধারা ২(১৮)]</span></td>
                    <td class="tr">{{ $fmt($gratuity) }}</td>
                </tr>
                <tr>
                    <td class="tc">৫</td>
                    <td>ভবিষ্যৎ তহবিল বা প্রভিডেন্ট ফান্ড (PF) — নিজস্ব ও মালিকের অংশ<span class="law-ref">[ধারা ২৪১–২৪৯]</span></td>
                    <td class="tr">{{ $fmt($otherEarn) }}</td>
                </tr>
                <tr>
                    <td class="tc">৬</td>
                    <td>প্রফিট/ওয়ার্কার্স প্রফিট পার্টিসিপেশন ফান্ডের লভ্যাংশ/মুনাফা (WPPF)<span class="law-ref">[ধারা ২৩২]</span></td>
                    <td class="tr"><span class="fs-fill-line">&nbsp;</span></td>
                </tr>
                <tr>
                    <td class="tc">৭</td>
                    <td>অন্যান্য বকেয়া/বোনাস/ভাতা (যদি থাকে)</td>
                    <td class="tr"><span class="fs-fill-line">&nbsp;</span></td>
                </tr>
                <tr class="total-row">
                    <td colspan="2">মোট প্রাপ্য টাকা (ক)</td>
                    <td class="tr">{{ $fmt($totalEarn) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="fs-fin-col">
        <div class="fs-fin-col-title deduct">কর্তন সমূহ (Deductions)</div>
        <table class="fs-fin-table">
            <thead>
                <tr><th style="width:8%;">নং</th><th style="width:62%;">বিবরণ</th><th style="width:30%;">টাকা</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td class="tc">১</td>
                    <td>নোটিশ পিরিয়ড না দেওয়ার কারণে কর্তন (যদি প্রযোজ্য হয়)<span class="law-ref">[ধারা ২৭(৩)]</span></td>
                    <td class="tr">{{ $fmt($otherDed) }}</td>
                </tr>
                <tr>
                    <td class="tc">২</td>
                    <td>অগ্রিম বেতন বা অফিস লোন সমন্বয়</td>
                    <td class="tr">{{ $fmt($advance) }}</td>
                </tr>
                <tr>
                    <td class="tc">৩</td>
                    <td>উৎস কর কর্তন (TDS/Income Tax)<span class="law-ref">[আয়কর আইন]</span></td>
                    <td class="tr"><span class="fs-fill-line">&nbsp;</span></td>
                </tr>
                <tr>
                    <td class="tc">৪</td>
                    <td>কোম্পানির সম্পদ ও মালামাল ক্ষতিপূরণ/অননুমোদিত বকেয়া</td>
                    <td class="tr"><span class="fs-fill-line">&nbsp;</span></td>
                </tr>
                <tr class="total-row">
                    <td colspan="2">মোট কর্তনকৃত টাকা (খ)</td>
                    <td class="tr">{{ $fmt($totalDeduct) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="fs-net-row">
    <div class="fs-net-box">
        <div class="lbl">সর্বমোট নিট পরিশোধযোগ্য টাকা (ক - খ)</div>
        <div class="amt">{{ $fmt($netPayable) }} টাকা</div>
    </div>
</div>
<div class="fs-inwords">
    কথায়: {{ \ME\Hr\Services\SalaryReportService::numberToWordsBn((int) round($netPayable)) }} টাকা মাত্র
</div>

@if(optional($settlement)->calculation_notes)
<div class="fs-notes">
    <strong>মন্তব্য:</strong> {{ $settlement->calculation_notes }}
</div>
@endif

{{-- ═══════════ অংশ ৩: কর্মচারীর চূড়ান্ত ঘোষণা ও দায়মুক্তি ═══════════ --}}
<div class="fs-part-title"><span class="part-no">৩</span>কর্মচারীর চূড়ান্ত ঘোষণা ও দায়মুক্তি</div>
<div class="fs-declaration">
    আমি, <strong>{{ $employeeName }}</strong> (আইডি নং: <strong>{{ $employee->employee_id ?? $employee->id }}</strong>), এই মর্মে ঘোষণা করছি যে,
    বাংলাদেশ শ্রম আইন, ২০০৬-এর <strong>ধারা ৩০</strong> অনুযায়ী আমার প্রাপ্য সকল বেতন-ভাতা, অব্যবহৃত অর্জিত ছুটির নগদায়ন, গ্র্যাচুইটি/আনুতোষিক,
    প্রভিডেন্ট ফান্ডের পাওনা এবং অন্যান্য সকল আর্থিক সুবিধা উপরে উল্লেখিত <strong>{{ $fmt($netPayable) }} টাকা</strong> আমি সম্পূর্ণরূপে বুঝিয়া পাইয়াছি।
    এই টাকা গ্রহণের মাধ্যমে আমার চাকুরিকালীন সময়ের সাথে সম্পর্কিত সকল হিসাব চূড়ান্তভাবে নিষ্পত্তি হইল এবং ভবিষ্যতে
    <strong>{{ $companyName }}</strong>-এর নিকট আমার আর কোনো প্রকার আর্থিক, আইনগত বা অন্য কোনো দাবি নাই এবং থাকিবে না।
    আমি স্বেচ্ছায় ও সজ্ঞানে এই দায়মুক্তি পত্রে স্বাক্ষর করিতেছি।
</div>

<div class="fs-pay-method">
    <div style="font-weight:600; margin-bottom:4px;">অর্থ প্রাপ্তির মাধ্যম:</div>
    <ul class="fs-checkbox-list">
        <li>☐ নগদ (Cash)</li>
        <li>☐ চেক (Cheque)</li>
        <li>☐ ব্যাংক ট্রান্সফার (Bank Transfer)</li>
    </ul>
    চেক/অ্যাকাউন্ট নম্বর: <span class="fs-fill-line" style="min-width:180px;">&nbsp;</span>
    &nbsp;&nbsp; ব্যাংকের নাম: <span class="fs-fill-line" style="min-width:180px;">&nbsp;</span>
</div>

<div class="fs-emp-sign-row">
    <div class="fs-emp-sign-box">
        <div class="fs-emp-sign-line">কর্মচারীর স্বাক্ষর ও তারিখ</div>
    </div>
    <div class="fs-emp-sign-box">
        <div class="fs-emp-sign-line">সাক্ষীর স্বাক্ষর ও তারিখ</div>
    </div>
</div>

{{-- ═══════════ অংশ ৪: অফিসিয়াল অনুমোদন ও সাইন-অফ ═══════════ --}}
<div class="fs-part-title"><span class="part-no">৪</span>অফিসিয়াল অনুমোদন ও সাইন-অফ</div>
<div class="fs-signoff-grid">
    <div class="fs-signoff-box">
        <div class="fs-signoff-line">হিসাব প্রস্তুতকারী</div>
        <div class="fs-signoff-role">(HR/Payroll)</div>
    </div>
    <div class="fs-signoff-box">
        <div class="fs-signoff-line">বিভাগীয় প্রধান</div>
        <div class="fs-signoff-role">(HOD Clearance)</div>
    </div>
    <div class="fs-signoff-box">
        <div class="fs-signoff-line">যাচাইকারী</div>
        <div class="fs-signoff-role">(Finance &amp; Accounts)</div>
    </div>
    <div class="fs-signoff-box">
        <div class="fs-signoff-line">চূড়ান্ত অনুমোদনকারী</div>
        <div class="fs-signoff-role">(Management/MD)</div>
    </div>
</div>

</div>
@endsection
