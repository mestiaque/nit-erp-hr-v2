@extends('printMaster2')

@section('title', 'Final Settlement Form')

@push('css')
<style>
    .sf-page { width:210mm; min-height:297mm; margin:0 auto; padding:10mm 12mm; box-sizing:border-box; background:#fff; color:#000; font-size:12px; line-height:1.45; box-shadow:0 0 10px rgba(0,0,0,.15); }
    .sf-head { text-align:center; }
    .sf-head h1 { margin:0; font-size:24px; font-weight:700; }
    .sf-head h2 { margin:0; font-size:16px; font-weight:700; }
    .sf-head h3 { margin:2px 0 0; font-size:14px; font-weight:400; }
    .sf-date { text-align:right; font-size:14px; font-weight:700; margin:4px 0 10px; }
    .sf-v { display:inline-block; min-width:60px; border-bottom:1px dotted #000; padding:0 4px; font-weight:600; }
    .sf-t { width:100%; border-collapse:collapse; }
    .sf-t td { padding:1px 4px; vertical-align:top; white-space:nowrap; }
    .sf-no { width:26px; }
    .sf-r { text-align:right; white-space:nowrap; }
    .sf-amt { width:90px; text-align:right; white-space:nowrap; font-weight:600; }
    .sf-total td { font-weight:700; }
    .sf-total .sf-amt { border-top:1px solid #000; }
    .sf-net { border:1.5px solid #000 !important; }
    .sf-sign-box { display:inline-block; width:110px; height:34px; border:1.5px solid #000; vertical-align:middle; }
    .sf-decl { margin-top:12px; text-align:justify; }
    .sf-signs { display:flex; justify-content:space-between; margin-top:70px; gap:12px; }
    .sf-signs div { flex:1; text-align:center; border-top:1px solid #000; padding-top:2px; }
    .sf-emp-sign { margin-top:50px; width:200px; text-align:center; border-top:1px solid #000; padding-top:2px; }
    @media print { @page { size:A4; margin:8mm; } .sf-page { box-shadow:none; margin:0; min-height:0; } }
</style>
@endpush

@section('contents')
@php
    $f = $form;
    $money = fn ($v) => (float) $v < 0 ? '(' . number_format(abs((float) $v), 2) . ')' : ((float) $v == 0 ? '-' : number_format((float) $v, 2));
    $num = fn ($v) => en2bnNumber((string) $v);
    $companyName = hr_factory('bn_name') ?? hr_factory('name') ?? optional(general())->name;
    $companyAddress = hr_factory('bn_address') ?? hr_factory('address') ?? optional(general())->address;
    $designation = data_get($employee, 'designation.bn_name') ?: data_get($employee, 'designation.name');
    $sectionDept = collect([data_get($employee, 'section.bn_name') ?: data_get($employee, 'section.name'), data_get($employee, 'department.bn_name') ?: data_get($employee, 'department.name')])->filter()->implode(' / ');
    $svc = $f['service'];
    $duration = $svc ? trim(($svc->y ? $num($svc->y) . ' বছর ' : '') . ($svc->m ? $num($svc->m) . ' মাস ' : '') . $num($svc->d) . ' দিন') : '';
    $months = array_pad($f['months'], 3, null);
@endphp

<div class="sf-page">
    <div class="sf-head">
        <h1>{{ $companyName }}</h1>
        <h2>{{ $companyAddress }}</h2>
        <h3>চূড়ান্ত নিষ্পত্তিকরণ ফরম-{{ $num($f['date']->year) }}</h3>
    </div>
    <div class="sf-date">তারিখঃ <span class="sf-v">{{ bn_date($f['date']) }}</span></div>

    <div>অত্র প্রতিষ্ঠানের চাকুরী হইতে পদত্যাগ/চাকুরী অবসান/চাকুরীচ্যুতি/অথবা বরখাস্ত এর পরিপ্রেক্ষিতে,</div>
    <table class="sf-t">
        <tr>
            <td style="width:110px;">জনাব/জনাবাঃ</td><td><span class="sf-v" style="min-width:240px;">{{ $employee->bn_name ?: $employee->name }}</span></td>
            <td class="sf-r">পদবীঃ</td><td><span class="sf-v" style="min-width:120px;">{{ $designation }}</span> গ্রেড- <span class="sf-v" style="min-width:30px;">{{ $employee->grade }}</span></td>
        </tr>
        <tr>
            <td>কার্ড নংঃ</td><td colspan="3"><span class="sf-v" style="min-width:240px;">{{ $employee->employee_id }}</span> এর চূড়ান্ত নিষ্পত্তিকরণ ফরম প্রেরণ করা হইল।</td>
        </tr>
    </table>

    <table class="sf-t" style="margin-top:8px;">
        <tr>
            <td class="sf-no">১।</td><td colspan="2">সেকশন/বিভাগঃ <span class="sf-v" style="min-width:180px;">{{ $sectionDept }}</span></td>
            <td colspan="2">লাইন নং- <span class="sf-v">{{ data_get($employee, 'floorLine.name') }}</span></td>
        </tr>
        <tr>
            <td class="sf-no">২।</td><td colspan="2">যোগদানের তারিখঃ <span class="sf-v">{{ $f['join'] ? bn_date($f['join']) : '' }}</span></td>
            <td colspan="2">চাকুরীর অবসানের তারিখঃ <span class="sf-v">{{ $f['exit'] ? bn_date($f['exit']) : '' }}</span> &nbsp; মেয়াদকালঃ <span class="sf-v">{{ $duration }}</span></td>
        </tr>
        <tr>
            <td class="sf-no">৩।</td><td colspan="2">বেতন/পারিশ্রমিক/ভাতা এর বর্তমান হারঃ</td>
            <td class="sf-r">(ক) মোট বেতন মাসিক টাকাঃ</td><td class="sf-amt">{{ $money($f['gross']) }}</td>
        </tr>
        <tr><td></td><td colspan="2"></td><td class="sf-r">(খ) মূল বেতন মাসিক টাকাঃ</td><td class="sf-amt">{{ $money($f['basic']) }}</td></tr>
        <tr><td></td><td colspan="2"></td><td class="sf-r">(গ) অন্যান্য ভাতা বেতন মাসিক বাড়ী ভাড়া বাবদ টাকাঃ</td><td class="sf-amt">{{ $money($f['house']) }}</td></tr>
        <tr><td></td><td colspan="3" class="sf-r">(ঘ) অন্যান্য ভাতা বেতন মাসিক মেডিকেল ভাতা খাদ্য ভাতা ও গাড়ী ভাড়ার ভাতা বাবদ টাকাঃ</td><td class="sf-amt">{{ $money($f['other_allowance']) }}</td></tr>

        <tr>
            <td class="sf-no">৪।</td><td colspan="4">ওভারটাইম ভাতার হারঃ ঘন্টা প্রতি টাকাঃ <span class="sf-v">{{ number_format($f['ot_rate'], 2) }}</span></td>
        </tr>

        @foreach($months as $i => $m)
        <tr>
            <td class="sf-no">{{ $i === 0 ? '৫।' : '' }}</td>
            <td style="width:190px;">{{ $i === 0 ? 'প্রদেয় বেতন/পারিশ্রমিক/ভাতাঃ' : '' }}</td>
            <td><span class="sf-v">{{ $m ? bn_date($m['month'], 'F') : '' }}</span> মাসের</td>
            <td class="sf-r"><span class="sf-v" style="min-width:30px;">{{ $m ? $num($m['days']) : '' }}</span> দিনের জন্য টাকাঃ</td>
            <td class="sf-amt">{{ $m ? $money($m['amount']) : '-' }}</td>
        </tr>
        @endforeach

        <tr>
            <td class="sf-no">৬।</td><td>হাজিরা বোনাসঃ</td>
            <td colspan="2"><span class="sf-v">{{ collect($f['months'])->where('att_bonus', '>', 0)->map(fn ($m) => bn_date($m['month'], 'F'))->implode(', ') }}</span> মাসের জন্য</td>
            <td class="sf-amt">{{ $money($f['att_bonus']) }}</td>
        </tr>
        <tr>
            <td class="sf-no">৭।</td><td>প্রদেয় ওভারটাইম ভাতাঃ</td>
            <td><span class="sf-v">{{ collect($f['months'])->where('ot_hours', '>', 0)->map(fn ($m) => bn_date($m['month'], 'F'))->implode(', ') }}</span> মাসের জন্য</td>
            <td class="sf-r"><span class="sf-v" style="min-width:30px;">{{ $f['ot_hours'] ? $num(rtrim(rtrim(number_format($f['ot_hours'], 2), '0'), '.')) : '' }}</span> ঘন্টার জন্য টাকাঃ</td>
            <td class="sf-amt">{{ $money($f['ot_amount']) }}</td>
        </tr>
        <tr>
            <td class="sf-no">৮।</td>
            <td colspan="2">সার্ভিস বেনিফিটের জন্য মোট দিন <span class="sf-v" style="min-width:30px;">{{ $f['benefit_days'] ? $num($f['benefit_days']) : '' }}</span>
                @if($f['benefit_days'])<small>({{ $num($f['years']) }} বছর × {{ $num($f['benefit_days_per_year']) }} দিন)</small>@endif
            </td>
            <td class="sf-r">সার্ভিস বেনিফিটের জন্য টাকাঃ</td>
            <td class="sf-amt">{{ $money($f['benefit_amount']) }}</td>
        </tr>
        <tr>
            <td class="sf-no">৯।</td><td>অতিরিক্ত ডিউটিঃ</td>
            <td><span class="sf-v" style="min-width:30px;">{{ $f['extra_days'] ? $num($f['extra_days']) : '' }}</span> দিন</td>
            <td class="sf-r">দিনের জন্য টাকাঃ</td>
            <td class="sf-amt">{{ $money($f['extra_amount']) }}</td>
        </tr>
        <tr>
            <td class="sf-no">১০।</td><td colspan="3">পূর্বের মোট জমাকৃত অর্জিত ছুটির সংখ্যা <span class="sf-v" style="min-width:50px;">&nbsp;</span> দিন</td><td></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">{{ $num($f['leave_year']) }} সালের মোট (জানুয়ারী-ডিসেম্বর) (উপস্থিত {{ $num($f['leave_present_days']) }} দিন / ১৮) = <span class="sf-v" style="min-width:30px;">{{ $num($f['leave_earned']) }}</span> দিন
                @if($f['leave_taken'])<small>(ভোগকৃত {{ $num($f['leave_taken']) }} দিন বাদে)</small>@endif
            </td>
            <td></td>
        </tr>
        <tr>
            <td></td><td colspan="2">মোট অর্জিত ছুটিঃ <span class="sf-v" style="min-width:30px;">{{ $num($f['leave_days']) }}</span></td>
            <td class="sf-r">দিনের জন্য টাকাঃ</td><td class="sf-amt">{{ $money($f['leave_amount']) }}</td>
        </tr>
        <tr class="sf-total"><td></td><td colspan="3" class="sf-r">মোট টাকাঃ</td><td class="sf-amt">{{ $money($f['total_earn']) }}</td></tr>

        <tr>
            <td class="sf-no">১১।</td><td>সমন্বয় সাধন (যদি থাকে):</td>
            <td colspan="2">(ক) অগ্রিম উত্তোলন</td><td class="sf-amt">{{ $money($f['advance']) }}</td>
        </tr>
        <tr><td></td><td></td><td colspan="2">(খ) লোন বাবদ</td><td class="sf-amt">{{ $money($f['loan']) }}</td></tr>
        <tr><td></td><td></td><td colspan="2">(গ) ইনকাম ট্যাক্স</td><td class="sf-amt">{{ $money($f['tax']) }}</td></tr>
        <tr><td></td><td></td><td colspan="2">(ঘ) রেভিনিউ স্ট্যাম্প</td><td class="sf-amt">{{ $money($f['stamp']) }}</td></tr>
        <tr class="sf-total"><td></td><td colspan="3" class="sf-r">মোট সমন্বয় টাকাঃ</td><td class="sf-amt">{{ $money($f['total_deduct']) }}</td></tr>
        <tr class="sf-total"><td></td><td colspan="3" class="sf-r">প্রদেয় টাকাঃ</td><td class="sf-amt sf-net">{{ $money($f['net']) }}</td></tr>
        <tr><td></td><td colspan="3" class="sf-r" style="padding-top:8px;">স্বাক্ষর</td><td class="sf-r" style="padding-top:8px;"><span class="sf-sign-box"></span></td></tr>
    </table>

    <div class="sf-decl">
        {{ $companyName }} হইতে আমার প্রাপ্য ও বকেয়া সম্পূর্ণ চূড়ান্ত নিষ্পত্তিকরণ বাবদ = <strong>{{ $money($f['net']) }}</strong>
        (কথায়ঃ {{ \ME\Hr\Services\SalaryReportService::numberToWordsBn((int) round(abs($f['net']))) }} টাকা মাত্র) এর প্রাপ্তি স্বীকার করছি এবং অঙ্গীকার করছি যে,
        এই প্রতিষ্ঠানে আমার আর কোন আর্থিক কিংবা অন্যান্য দাবী নাই। যদি কখনও দাবী করি তাহলে তাহা সর্বদালতে বাতিল বলিয়া অগ্রাহ্য হইবে।
    </div>

    <div class="sf-emp-sign">শ্রমিক/কর্মচারীর স্বাক্ষর</div>

    <div class="sf-signs">
        <div>প্রস্তুতকারী</div>
        <div>হিসাব শাখা</div>
        <div>এইচ আর এ্যাডমিন</div>
        <div>ম্যানেজিং ডিরেক্টর</div>
    </div>
</div>
@endsection
