@extends('printMaster2')

@section('title', 'Daily Manpower Report')

@push('css')
<style>
    /* .r-wrap { font-size: 11px; } */
    .head {
        text-align: center;
        margin-bottom: 12px;
    }
    .head h3 {
        margin: 0 0 4px;
    }
    .sub {
        font-size: 12px;
        margin-bottom: 10px;
    }
    .logo-box {
        width: 52px;
        height: 52px;
        border: 1px solid #bdbdbd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: #1f4f99;
    }
    .co h3 { margin: 0; font-size: 15px; font-weight: 700; }
    .co p { margin: 1px 0; font-size: 11px; }
    .title-bar {
        width: 260px;
        margin: 0 auto 2px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: #000000;
        line-height: 24px;
    }
    .as-on {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .day-stat {
        font-size: 11px;
        margin-bottom: 8px;
    }
    .day-stat span {
        margin-right: 30px;
    }
    .t {
        width: 100%;
        border-collapse: collapse;
        border: 1px dotted #7d7d7d;
        margin-bottom: 8px;
    }
    .t th,
    .t td {
        border: 1px solid #b7b7b7;
        padding: 3px 5px;
        line-height: 1.15;
    }
    .t thead th {
        text-align: center;
        font-weight: 700;
        background: #f0f0f0;
    }
    .tl { text-align: left; }
    .tc { text-align: center; }
    .b { font-weight: 700; }
    .total-row td {
        font-weight: 700;
        background: #f7f7f7;
    }





    /* Hide printmaster top fixed action bar on screen as well for this specific report */

</style>
@endpush

@section('contents')
@php
    $company = general()->title ?? 'Company Name';
    $address = general()->address_one ?? '';
    $reportDateLabel = \Carbon\Carbon::parse($reportDate)->format('d-M-y');
    $dayPresentPct = ($grand['manpower_total'] ?? 0) > 0
        ? number_format((($grand['present_total'] ?? 0) * 100) / ($grand['manpower_total'] ?? 1), 2)
        : '0.00';
    $dayAbsentPct = ($grand['manpower_total'] ?? 0) > 0
        ? number_format((($grand['absent'] ?? 0) * 100) / ($grand['manpower_total'] ?? 1), 2)
        : '0.00';
@endphp

<div class="">
<div class="head">
    <h3>{{ general()->title ?? 'Company Name' }}</h3>
    <div>{{ general()->address_one ?? data_get(general(), 'address') }}</div>
</div>

    <div class="title-bar">Daily Manpower Report</div>
    <div class="as-on">As on Date&nbsp;&nbsp;{{ $reportDateLabel }}</div>

    <div class="day-stat">
        <span><strong>Day Present</strong> = {{ $dayPresentPct }}%</span>
        <span><strong>Day Absent</strong> = {{ $dayAbsentPct }}%</span>
    </div>

    <table class="t">
        <thead>
            <tr>
                <th rowspan="2" class="tl" style="width: 28%;">Section</th>
                <th colspan="2">Man power</th>
                <th rowspan="2" style="width: 7%;">Total</th>
                <th colspan="3">Present</th>
                <th colspan="2">Others</th>
                <th rowspan="2" style="width: 14%;">Remarks</th>
            </tr>
            <tr>
                <th style="width: 6%;">Female</th>
                <th style="width: 6%;">Male</th>
                <th style="width: 6%;">Leave</th>
                <th style="width: 6%;">Present</th>
                <th style="width: 6%;">Total</th>
                <th style="width: 6%;">Absent</th>
                <th style="width: 6%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td class="tl">{{ $row['section'] }}</td>
                    <td class="tc">{{ $row['female'] }}</td>
                    <td class="tc">{{ $row['male'] }}</td>
                    <td class="tc b">{{ $row['manpower_total'] }}</td>
                    <td class="tc">{{ $row['leave'] }}</td>
                    <td class="tc">{{ $row['present'] }}</td>
                    <td class="tc b">{{ $row['present_total'] }}</td>
                    <td class="tc">{{ $row['absent'] }}</td>
                    <td class="tc b">{{ $row['others_total'] }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="tc">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(($rows->count() ?? 0) > 0)
            <tfoot>
                <tr class="total-row">
                    <td class="tc b">Total</td>
                    <td class="tc">{{ $grand['female'] }}</td>
                    <td class="tc">{{ $grand['male'] }}</td>
                    <td class="tc b">{{ $grand['manpower_total'] }}</td>
                    <td class="tc">{{ $grand['leave'] }}</td>
                    <td class="tc">{{ $grand['present'] }}</td>
                    <td class="tc b">{{ $grand['present_total'] }}</td>
                    <td class="tc">{{ $grand['absent'] }}</td>
                    <td class="tc b">{{ $grand['others_total'] }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
@endsection
