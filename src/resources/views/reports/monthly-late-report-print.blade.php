@extends('printMaster2')

@section('title', 'Monthly Late Report')

@push('css')
<style>


    .r-wrap {
        font-size: 11px;
    }

    .r-head {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid #bfbfbf;
        padding-bottom: 8px;
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

    .co h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
    }

    .co p {
        margin: 1px 0;
        font-size: 11px;
    }

    .title-bar {
        width: 240px;
        margin: 0 auto 2px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        background: #8c8c8c;
        color: #fff;
        line-height: 24px;
    }

    .date-bar {
        text-align: center;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        margin: 8px 0;
    }

    .emp-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 20rem;
        margin: 3px 0 6px;
        font-size: 12px;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .label {
        min-width: 64px;
        font-weight: 700;
    }

    .colon {
        width: 8px;
        text-align: center;
    }

    .t {
        /* width: 58%; */
        border-collapse: collapse;
        /* margin: 0 0 2px 96px; */
    }

    .t th,
    .t td {
        border: 1px solid #444;
        padding: 3px 6px;
        font-size: 11px;
    }

    .t th {
        font-weight: 700;
        background: #f0f0f0;
    }

    .tc {
        text-align: center;
    }

    .total-line {
        margin: 2px 0 16px 96px;
        font-size: 12px;
        font-weight: 700;
    }

    .page-break {
        page-break-after: always;
    }
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
    $fromLabel = \Carbon\Carbon::parse($from)->format('d/m/Y');
    $toLabel = \Carbon\Carbon::parse($to)->format('d/m/Y');
@endphp

<div class="text-center">
<div class="head">
    @if(!blank(general()->logo()))
        <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ hr_factory('name') ?? 'Company Name' }}</h3>
    <div>{{ hr_factory('address') ?? '' }}</div>
</div>

    <div class="title-bar">Monthly Late Report</div>
    <div class="date-bar">Dated from {{ $fromLabel }} to {{ $toLabel }}</div>

    @forelse($lateBySection as $sectionRows)
        @php
            $sectionTitle = data_get($sectionRows->first(), 'section', 'N/A');
        @endphp

        <div class="section-title">Section <span class="colon">:</span> {{ $sectionTitle }}</div>

        @foreach($sectionRows as $empRow)
            <div class="emp-grid">
                <div class="info-row">
                    <span class="label">Card No.</span><span class="colon">:</span><span>{{ $empRow['card_no'] }}</span>
                </div>
                <div class="info-row mr-auto">
                    <span class="label">Designation</span><span class="colon">:</span><span>{{ $empRow['designation'] }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Name</span><span class="colon">:</span><span>{{ $empRow['name'] }}</span>
                </div>
                <div class="info-row">
                    <span class="label">DOJ</span><span class="colon">:</span><span>{{ $empRow['doj'] ? \Carbon\Carbon::parse($empRow['doj'])->format('d/m/Y') : '-' }}</span>
                </div>
            </div>

            <table class="t">
                <thead>
                    <tr>
                        <th style="width: 20%;">Date</th>
                        <th style="width: 24%;">Shift</th>
                        <th style="width: 18%;">In Time</th>
                        <th style="width: 18%;">Out Time</th>
                        <th style="width: 20%;" class="tc">Late in Minute</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($empRow['late_rows'] as $lateRow)
                        <tr>
                            <td>{{ $lateRow['date'] }}</td>
                            <td>{{ $lateRow['shift'] ?? '-' }}</td>
                            <td>{{ $lateRow['in_time'] }}</td>
                            <td>{{ $lateRow['out_time'] }}</td>
                            <td class="tc">{{ $lateRow['late_minute'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total-line">
                Total Late Days <span class="colon">:</span> {{ $empRow['total_late_days'] }}
            </div>
        @endforeach
    @empty
        <p>No late records found for selected period.</p>
    @endforelse
</div>
@endsection

