@extends('printMaster2')

@section('title', 'Attendance Report With OT')

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
        width: 300px;
        margin: 0 auto 10px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        background: #bfbfbf;
        color: #fff;
        line-height: 24px;
    }

    .section-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        font-weight: 700;
        margin: 8px 0 2px;
    }

    .t {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #b7b7b7;
        margin-bottom: 12px;
    }

    .t th,
    .t td {
        border: 1px solid #b7b7b7;
        padding: 3px 5px;
        line-height: 1.2;
        font-size: 11px;
    }

    .t thead th {
        text-align: left;
        font-weight: 700;
        background: #f0f0f0;
    }

    .tc {
        text-align: center;
    }

    .tr {
        text-align: right;
    }

    .total-row td {
        font-weight: 700;
        background: #f7f7f7;
    }
</style>
@endpush

@section('contents')
@php
    $company = general()->title ?? 'Company Name';
    $address = general()->address_one ?? '';
    $reportDateLabel = \Carbon\Carbon::parse($reportDate)->format('d/m/Y');
    $rowsBySection = $rows->groupBy('section_id');
@endphp

<div class=" text-center">
<div class="head">
    <h3>{{ general()->title ?? 'Company Name' }}</h3>
    <div>{{ general()->address_one ?? data_get(general(), 'address') }}</div>
</div>

    <div class="title-bar">Attendance Report With OT</div>

    @forelse($rowsBySection as $sectionRows)
        @php
            $sectionTitle = data_get($sectionRows->first(), 'section', 'N/A');
        @endphp

        <div class="section-line">
            <div>Section: {{ $sectionTitle }}</div>
            <div>Date: {{ $reportDateLabel }}</div>
        </div>

        <table class="t">
            <thead>
                <tr>
                    <th style="width: 16%;">CardNo</th>
                    <th style="width: 24%;">Name</th>
                    <th style="width: 22%;">DesignationName</th>
                    <th class="tc" style="width: 12%;">In Time</th>
                    <th class="tc" style="width: 6%;">Late</th>
                    <th class="tc" style="width: 12%;">Out Time</th>
                    <th class="tc" style="width: 10%;">Over Time</th>
                    <th class="tc" style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sectionRows as $row)
                    @php
                        $formatTime = function ($value) {
                            if (blank($value) || $value === '-') {
                                return '-';
                            }

                            try {
                                return \Carbon\Carbon::parse($value)->format('h:i:s a');
                            } catch (\Throwable $e) {
                                return '-';
                            }
                        };

                        $inTime = $formatTime(data_get($row, 'in_time'));
                        $outTime = $formatTime(data_get($row, 'out_time'));
                    @endphp
                    <tr>
                        <td>{{ $row['card_no'] ?: '-' }}</td>
                        <td>{{ $row['name'] ?: '-' }}</td>
                        <td>{{ $row['designation'] ?: '-' }}</td>
                        <td class="tc">{{ $inTime }}</td>
                        <td class="tc">{{ $row['late'] }}</td>
                        <td class="tc">{{ $outTime }}</td>
                        <td class="tc">{{ number_format((float) $row['ot_hours'], 2) }}h</td>
                        <td class="tc">{{ $row['status'] ?: '-' }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" class="tr">Section Total :</td>
                    <td class="tc">{{ $sectionRows->count() }}</td>
                    <td colspan="5"></td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>No employees found.</p>
    @endforelse
</div>
@endsection
