@extends('printMaster2')

@section('title', 'Resignation Application')

@section('contents')
@php
    $na = '';
    $separation = $employee->separation;

    $companyName    = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
    $companyAddress = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;

    $employeeName = data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na;

    $designationModel = optional($employee->designation);
    $designation = $designationModel->bn_name ?? $designationModel->name ?? data_get($employee, 'designation_name') ?? $na;

    $sectionModel = optional($employee->section);
    $section = $sectionModel->bn_name ?? $sectionModel->name ?? data_get($employee, 'section_name') ?? $na;

    $employeeId = data_get($employee, 'employee_id', $na);
    $joinDate   = blank($employee->join_date) ? $na : bn_date($employee->join_date, 'd/m/Y');
    $resignDate = blank($employee->exited_at) ? $na : bn_date($employee->exited_at, 'd/m/Y');
    $reason     = data_get($separation, 'remarks', $na);
@endphp

<div class="resign-letter ">
    <p class="rl-date">তারিখঃ {{ $resignDate ?: '----------------' }}</p>

    <p class="rl-block">
        বরাবর,<br>
        মহা ব্যবস্থাপক<br>
        প্রশাসন ও মানবসম্পদ বিভাগ<br>
        {{ $companyName }}<br>
        {{ $companyAddress }}
    </p>

    <p class="rl-subject"><strong>বিষয়ঃ-</strong> চাকুরী হইতে অব্যহতি জন্য আবেদন।</p>

    <p class="rl-salutation">জনাব,</p>

    <p class="rl-body">
        বিনিত নিবেদন এই যে, আমি <span class="rl-fill">{{ $employeeName }}</span> পদবীঃ <span class="rl-fill">{{ $designation }}</span>
        আইডি নংঃ <span class="rl-fill">{{ $employeeId }}</span> সেকশনঃ <span class="rl-fill">{{ $section }}</span>
        গত <span class="rl-fill">{{ $joinDate ?: '----------------' }}</span> তারিখ হতে চাকরি করতেছি। আমার
        <span class="rl-fill">{{ $reason ?: '----------------' }}</span> সমস্যার কারনে
        <span class="rl-fill">{{ $resignDate ?: '----------------' }}</span> ইং তারিখ হইতে আমি চাকরি করিতে পারিব না।
    </p>

    <p class="rl-body">
        অতএব জনাবের নিকট আকুল আবেদন এই যে, উপরোক্ত বিষয়টি বিবেচনা করিয়া আমাকে চাকুরী হইতে অব্যহতি প্রদানে মহোদয়ের মর্জি হয়।
    </p>

    <div class="rl-sign-row">
        <div class="rl-sign-left">
            <div class="rl-sign-line"></div>
            <p>ব্যবস্থাপক (এইচ আর ও এডমিন)</p>
        </div>
        <div class="rl-sign-right">
            <p class="rl-nibedok">নিবেদক</p>
            <p>নামঃ {{ $employeeName }}</p>
            <p>পদবীঃ {{ $designation }}</p>
            <p>আইডি নংঃ {{ $employeeId }}</p>
            <p style="margin-top: 3rem">স্বাক্ষরঃ ------------------------</p>
            <p class="rl-thumb">টিপসহিঃ <span class="rl-thumb-box"></span></p>
        </div>
    </div>
</div>

@push('css')
<style>
    body { font-family: 'Noto Sans Bengali', 'SolaimanLipi', Arial, sans-serif; }
    .resign-letter { font-size: 18px; line-height: 1.4; padding: 20px 10px; width: 210mm; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,.1); }
    .rl-date { margin-bottom: 36px; }
    .rl-block { margin-bottom: 30px; }
    .rl-subject { margin-bottom: 30px; }
    .rl-salutation { margin-bottom: 10px; }
    .rl-body { text-align: justify; margin-bottom: 30px; }
    .rl-fill { border-bottom: 1px dotted #000; padding: 0 6px; display: inline-block; min-width: 70px; text-align: center; }
    .rl-sign-row { display: flex; justify-content: space-between; margin-top: 140px; }
    .rl-sign-left { width: 45%; text-align: center; }
    .rl-sign-line { border-top: 1px solid #000; margin-bottom: 10px; }
    .rl-sign-right { width: 35%; }
    .rl-sign-right p { margin-bottom: 14px; }
    .rl-nibedok { font-weight: bold; }
    .rl-thumb { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
    .rl-thumb-box { display: inline-block; width: 45mm; height: 18mm; border: 1px solid #000; }

    @media print {
        .resign-letter { box-shadow: none; }
    }
</style>
@endpush
@endsection
