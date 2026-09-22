@extends('printMaster2')

@php $hideGeneratedNote = true; @endphp

@section('title', 'Show Cause Notice - ' . ($notice->employee->employee_id ?? ''))

@push('css')
<style>
@media print {
    @page { size: A4; margin: 15mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $na = 'প্রযোজ্য নয়';
    $company = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
    $address = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;
    $employee = $notice->employee;
    $employeeName = $employee->bn_name ?? $employee->name ?? $na;
    $designationName = optional($employee->designation)->bn_name ?? optional($employee->designation)->name ?? $na;
    $departmentName = optional($employee->department)->bn_name ?? optional($employee->department)->name ?? $na;
    $bd = fn ($date) => $date ? bn_date($date, 'd-m-Y') : '';
    $bnDays = en2bnNumber($notice->deduction_days);
    $bnDaysWords = \ME\Hr\Services\SalaryReportService::numberToWordsBn((int) $notice->deduction_days);
@endphp

<div style="font-family: 'Nikosh', 'Arial', sans-serif; max-width: 800px; margin: auto; color: #000; line-height: 1.8; padding: 10px;">

    <div style="text-align:center; margin-bottom:10px;">
        <h2 style="margin:0; color:#1a3a5c;">{{ $company }}</h2>
        <div style="font-size: 13px;">{{ $address }}</div>
    </div>

    <div style="text-align:center; font-weight:700; font-size:17px; text-decoration: underline; margin: 18px 0 22px;">
        কারণ দর্শানো নোটিশ
    </div>

    <div style="margin-bottom:6px;"><strong>স্মারক নং:</strong> {{ $notice->memo_no }}</div>
    <div style="margin-bottom:18px;"><strong>তারিখ:</strong> {{ $bd($notice->notice_date) }}</div>

    <div style="margin-bottom:18px;">
        <strong>প্রাপক:</strong><br>
        নাম: &nbsp; {{ $employeeName }}<br>
        পদবী: &nbsp; {{ $designationName }}<br>
        বিভাগ: &nbsp; {{ $departmentName }}<br>
        আইডি নং: &nbsp; {{ $employee->employee_id ?? '-' }}
    </div>

    <div style="margin-bottom:18px;">
        <strong>বিষয়: কাজে অবহেলার কারণে {{ $bnDays }} দিনের বেতন কর্তন প্রসঙ্গে।</strong>
    </div>

    <div style="text-align: justify;">
        <p style="margin-bottom:14px;">মহোদয়/মহোদয়া,</p>

        <p style="margin-bottom:14px;">
            উপরে উল্লেখিত বিষয়ের প্রেক্ষিতে আপনাকে জানানো যাচ্ছে যে,
            @if($notice->incident_date)
                {{ $bd($notice->incident_date) }} তারিখে
            @endif
            {{ $notice->incident_description }}। এর ফলে প্রতিষ্ঠানের কাজের গতি হ্রাস ও ক্ষতি সাধন হয়েছে। এর আগেও আপনাকে মৌখিকভাবে কাজের প্রতি যত্নশীল হওয়ার নির্দেশ দেয়া হয়েছিল। কিন্তু আপনার কর্মপদ্ধতিতে কোনো পরিবর্তন লক্ষ্য করা যায়নি।
        </p>

        <p style="margin-bottom:14px;">
            আপনার বিরুদ্ধে প্রতিষ্ঠানের নিয়মাবলীর ধারা অনুযায়ী, আপনার এই গুরুতর অবহেলার কারণে আপনার {{ $bnDays }} ({{ $bnDaysWords }}) দিনের মূল বেতন কর্তন করা হবে। প্রতিষ্ঠানের স্বার্থে আপনার সহযোগিতা কাম্য।
        </p>

        <p style="margin-bottom:0;">ধন্যবাদসহ,</p>
    </div>

    <div style="margin-top:60px; display:flex; justify-content:space-between; text-align:center;">
        <div style="width:32%;">
            _______________<br>
            {{ $employeeName }}
        </div>
        <div style="width:32%;">
            _______________<br>
            ম্যানেজার (এইচআর ও প্রশাসন)
        </div>
        <div style="width:32%;">
            _______________<br>
            জেনারেল ম্যানেজার
        </div>
    </div>

</div>
@endsection
