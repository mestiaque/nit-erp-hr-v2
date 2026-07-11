@extends('printMaster2')

@section('title', 'Attendance Report - ' . $dateLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title  { font-size:12px; font-weight:700; margin:8px 0 4px; }
.section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.tr { text-align:right; }
.present { color:green; font-weight:700; }
.absent  { color:red; }
.section-summary { font-size:10px; margin:2px 0 6px; }
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
@endphp

<div class="report-head">
    @if(!blank(general()->logo()))
        <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">Attendance Report &mdash; {{ $dateLabel }}</div>

@php
    $bySection = $employees->groupBy('section_id');
    $grandPresent = $grandAbsent = $grandLate = $grandLeave = $grandWeekend = $grandHoliday = 0;
    $grandOt = 0;
@endphp

@forelse($bySection as $sectionId => $sectionEmps)
    <div class="section-title">Section: {{ $sectionMap->get($sectionId, 'N/A') }}</div>

    @php
        $secPresent = $secAbsent = $secLate = $secLeave = $secWeekend = $secHoliday = 0;
        $secOt = 0;
    @endphp

    <table class="t">
        <thead>
            <tr>
                <th>SI</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Leave</th>
                <th>Weekend</th>
                <th>Holiday</th>
                <th>OT Hrs</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sectionEmps as $employee)
                @php
                    $row      = $attendanceByEmployee->get($employee->id, []);
                    $present  = $row['present']  ?? 0;
                    $absent   = $row['absent']   ?? 0;
                    $late     = $row['late']     ?? 0;
                    $leave    = $row['leave']    ?? 0;
                    $weekend  = $row['weekend']  ?? 0;
                    $holiday  = $row['holiday']  ?? 0;
                    $otHrs    = number_format((float)($row['ot_hours'] ?? 0), 2);

                    $secPresent += $present; $secAbsent += $absent; $secLate += $late;
                    $secLeave += $leave; $secWeekend += $weekend; $secHoliday += $holiday;
                    $secOt += (float)($row['ot_hours'] ?? 0);

                    $desigName = $designationMap->get($employee->designation_id, '—');
                @endphp
                <tr>
                    <td class="tc">{{ $loop->iteration }}</td>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $desigName }}</td>
                    <td class="tc present">{{ $present }}</td>
                    <td class="tc {{ $absent > 0 ? 'absent' : '' }}">{{ $absent }}</td>
                    <td class="tc">{{ $late }}</td>
                    <td class="tc">{{ $leave }}</td>
                    <td class="tc">{{ $weekend }}</td>
                    <td class="tc">{{ $holiday }}</td>
                    <td class="tr">{{ $otHrs }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="tc">No data.</td></tr>
            @endforelse
            {{-- Section subtotal row --}}
            @if($sectionEmps->count() > 1)
            <tr style="font-weight:700; background:#f5f5f5;">
                <td colspan="4" class="tc">Section Total ({{ $sectionEmps->count() }} emp)</td>
                <td class="tc present">{{ $secPresent }}</td>
                <td class="tc {{ $secAbsent > 0 ? 'absent' : '' }}">{{ $secAbsent }}</td>
                <td class="tc">{{ $secLate }}</td>
                <td class="tc">{{ $secLeave }}</td>
                <td class="tc">{{ $secWeekend }}</td>
                <td class="tc">{{ $secHoliday }}</td>
                <td class="tr">{{ number_format($secOt, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    @php
        $grandPresent += $secPresent; $grandAbsent += $secAbsent; $grandLate += $secLate;
        $grandLeave += $secLeave; $grandWeekend += $secWeekend; $grandHoliday += $secHoliday;
        $grandOt += $secOt;
    @endphp
@empty
    <p>No employees found.</p>
@endforelse

@if($employees->count() > 0)
<table class="t" style="margin-top:12px;">
    <thead>
        <tr>
            <th colspan="4">Grand Total ({{ $employees->count() }} employees)</th>
            <th>Present</th>
            <th>Absent</th>
            <th>Late</th>
            <th>Leave</th>
            <th>Weekend</th>
            <th>Holiday</th>
            <th>OT Hrs</th>
        </tr>
    </thead>
    <tbody>
        <tr style="font-weight:700;">
            <td colspan="4" class="tc"></td>
            <td class="tc present">{{ $grandPresent }}</td>
            <td class="tc {{ $grandAbsent > 0 ? 'absent' : '' }}">{{ $grandAbsent }}</td>
            <td class="tc">{{ $grandLate }}</td>
            <td class="tc">{{ $grandLeave }}</td>
            <td class="tc">{{ $grandWeekend }}</td>
            <td class="tc">{{ $grandHoliday }}</td>
            <td class="tr">{{ number_format($grandOt, 2) }}</td>
        </tr>
    </tbody>
</table>
@endif

@endsection
