@extends('printMaster2')

@php $hideGeneratedNote = true; @endphp

@section('title', 'Advance Application - ' . ($application->employee->employee_id ?? ''))

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap" rel="stylesheet">
<style>
@media print {
    @page { size: A4; margin: 15mm; }
    body { margin: 0; }
}
/* A real Bengali web font everywhere (table cells included): with Nikosh missing, Windows fell
   back to Arial, which can't join conjuncts like ঞ্জ in মঞ্জুর. */
.adv-letter, .adv-letter * { font-family: 'Noto Sans Bengali', 'Nirmala UI', 'SolaimanLipi', 'Nikosh', sans-serif !important; }
.adv-letter { max-width: 800px; margin: auto; color: #000; line-height: 1.75; padding: 10px; font-size: 15px; }
.adv-letter p { margin: 0 0 14px; }
.adv-bottom { width: 100%; border-collapse: collapse; margin-top: 10px; }
.adv-bottom { margin-bottom: 0; }
/* printMaster2 borders every td - the outer layout table must stay borderless. */
.adv-bottom > tbody > tr > td { vertical-align: top; border: 0 !important; padding: 0; font-size: 14px; }
.adv-office { border: 1px solid #000; width: 100%; border-collapse: collapse; line-height: 1.6; margin-bottom: 0; }
.adv-office td { border: 1px solid #000 !important; padding: 6px 8px; vertical-align: top; font-size: 13px; }
.adv-office .adv-office-head { text-align: center; font-weight: 700; background: #e7e7e7; }
.adv-office .adv-office-title { font-weight: 700; }
.adv-blank { display: inline-block; border-bottom: 1px solid #000; min-width: 140px; }
.adv-blank-sm { display: inline-block; border-bottom: 1px solid #000; min-width: 34px; }
.adv-gap { height: 22px; }
</style>
@endpush

@section('contents')
@php
    $na = '';
    $company  = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
    $address  = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;
    $employee = $application->employee;

    $employeeName    = $employee->bn_name ?? $employee->name ?? $na;
    $designationName = optional($employee->designation)->bn_name ?? optional($employee->designation)->name ?? $na;
    $departmentName  = optional($employee->department)->bn_name ?? optional($employee->department)->name ?? $na;
    $employeeId      = en2bnNumber((string) ($employee->employee_id ?? ''));
    $mobile          = en2bnNumber((string) ($employee->personal_contact ?? ''));

    $amount = (float) $application->amount;
    $bnAmount = en2bnNumber(number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2));
    $reason = $application->reason ?: \ME\Hr\Models\HrAdvanceApplication::DEFAULT_REASON;
    $deductionMonth = $application->deduction_month ? bn_date($application->deduction_month, 'F, Y') . ' ' : '';
@endphp

<div class="adv-letter">
    @if(report_logo_url())<div style="text-align:center;margin-bottom:6px;"><img src="{{ report_logo_url() }}" alt="Logo" style="height:50px;max-width:160px;object-fit:contain;"></div>@endif
    <p>তারিখ: {{ bn_date($application->application_date, 'd F, Y') }}</p>

    <p>
        বরাবর,<br>
        ব্যবস্থাপনা পরিচালক<br>
        {{ $company }}<br>
        {{ $address }}
    </p>

    <p style="margin-top:22px;">বিষয়: অগ্রিম বেতনের জন্য আবেদন।</p>

    <p style="margin-top:22px;">মহোদয়,</p>

    <p style="text-align: justify;">
        বিনীত নিবেদন এই যে, আমি {{ $employeeName }}, আপনার প্রতিষ্ঠানে {{$departmentName}} এ {{ $designationName }} হিসেবে কর্মরত আছি। আমার আইডি নম্বর {{ $employeeId }}।
        আমি অত্যন্ত জরুরি {{ $reason }} কারণে আর্থিক সমস্যার সম্মুখীন হয়েছি। এই পরিস্থিতি মোকাবিলার জন্য আমার কিছু জরুরি অর্থের প্রয়োজন।
        এমতাবস্থায়, আগামী মাসের বেতন থেকে {{ $bnAmount }} টাকা অগ্রিম প্রদান করলে আমি এই সাময়িক সমস্যাটি কাটিয়ে উঠতে পারব।
        এই অগ্রিম নেওয়া টাকাটি পরবর্তী {{ $deductionMonth }}মাসের বেতন থেকে কেটে নেওয়ার জন্য অনুরোধ করছি।
    </p>

    <p style="text-align: justify;">
        অতএব, বিনীত প্রার্থনা এই যে, আমার বর্তমান পরিস্থিতি বিবেচনা করে আমাকে উক্ত পরিমাণ টাকা অগ্রিম বেতন হিসেবে প্রদানের অনুমতি দিয়ে বাধিত করবেন।
    </p>

    <table class="adv-bottom">
        <tr>
            <td style="width:42%; padding-right:16px;">
                <p style="margin-top:6px;">ধন্যবাদান্তে,</p>
                <div style="margin-top:56px; width:150px; border-top:1px solid #000;"></div>
                <div style="padding-left:28px;">স্বাক্ষর</div>
                <div style="margin-top:6px; line-height:1.7;">
                    {{ $employeeName }}<br>
                    {{ $designationName }}<br>
                    {{ $departmentName }}<br>
                    {{ $mobile }}
                </div>
            </td>
            <td style="width:58%;">
                <table class="adv-office">
                    <tr><td class="adv-office-head">অনুমোদন অংশ (For Office Use Only)</td></tr>
                    <tr>
                        <td>
                            <div class="adv-office-title">সুপারিশকারী/বিভাগীয় প্রধানের মন্তব্য (Department Head Recommendation):</div>
                            মন্তব্য: <span class="adv-blank" style="min-width:250px;"></span><br>
                            সুপারিশ: &nbsp;[ &nbsp;] অনুমোদিত &nbsp;/&nbsp; [ &nbsp;] বাতিল
                            <div class="adv-gap"></div>
                            স্বাক্ষর ও তারিখ: <span class="adv-blank"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="adv-office-title">মানবসম্পদ বিভাগ (HR Department Verification):</div>
                            আবেদনকারীর বর্তমান বকেয়া/পূর্বের অগ্রিম: <span class="adv-blank"></span><br>
                            যোগ্যতা যাচাই: &nbsp;[ &nbsp;] যথাযথ &nbsp;/&nbsp; [ &nbsp;] যথাযথ নয়
                            <div class="adv-gap"></div>
                            স্বাক্ষর ও তারিখ: <span class="adv-blank"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="adv-office-title">চূড়ান্ত অনুমোদনকারী (Final Approving Authority):</div>
                            সিদ্ধান্ত: &nbsp;[ &nbsp;] মঞ্জুর করা হলো &nbsp;/&nbsp; [ &nbsp;] নামঞ্জুর করা হলো।
                            <div class="adv-gap"></div>
                            অনুমোদিত টাকার পরিমাণ: <span class="adv-blank"></span> টাকা।<br>
                            স্বাক্ষর (ব্যবস্থাপনা পরিচালক):
                            <div style="height:44px;"></div>
                            <span class="adv-blank" style="min-width:120px;"></span>
                            &nbsp; তারিখ: <span class="adv-blank-sm"></span> / <span class="adv-blank-sm"></span> / <span class="adv-blank-sm" style="min-width:50px;"></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
@endsection
