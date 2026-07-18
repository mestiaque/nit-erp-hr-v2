@extends('printMaster2')

@section('title', 'Monthly Attendance Status')

@push('css')
<style>
.report-wrap { font-size: 10px; }
.report-head {
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #c8c8c8;
    padding-bottom: 8px;
    margin-bottom: 10px;
}
.logo-box {
    width: 50px;
    height: 50px;
    border: 1px solid #bdbdbd;
    color: #1f4f99;
    font-weight: 700;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.company h3 { margin: 0; font-size: 15px; font-weight: 700; }
.company p { margin: 1px 0; font-size: 11px; }
.main-title {
    width: 300px;
    margin: 0 auto 5px;
    text-align: center;
    background: #bdbdbd;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    line-height: 24px;
}
.sub-date {
    text-align: center;
    font-weight: 700;
    font-size: 11px;
    margin-bottom: 8px;
}
.section-line {
    font-size: 11px;
    font-weight: 700;
    margin: 6px 0 2px;
}
.t {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 8px;
    border: 1px dotted #8e8e8e;
}
.t th,
.t td {
    border: 1px solid #b9b9b9;
    padding: 2px 3px;
    line-height: 1.1;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.t thead th { text-align: center; font-weight: 700; }
.tc { text-align: center; }
.card-col { width: 70px; }
.name-col { width: 135px; }
.sum-col { width: 24px; }
.day-col { width: 19px; }
.legend { font-size: 10px; margin-top: 4px; }
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
    $fromLabel = \Carbon\Carbon::parse($from)->format('d/m/Y');
    $toLabel = \Carbon\Carbon::parse($to)->format('d/m/Y');
    $sectionGroups = $groups ?? $employees->groupBy('section_id');
@endphp

<div class="report-wrap">
    <div class="head text-center">
        <div class="company">
            @if(!blank(general()->logo()))
                <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
            @endif
            <h2>{{ $company }}</h2>
            <p>{{ $address }}</p>
        </div>
    </div>

    <div class="main-title">Monthly Attendance Status</div>
    <div class="sub-date">Dated from&nbsp; {{ $fromLabel }} &nbsp;&nbsp; to {{ $toLabel }}</div>

    @forelse($sectionGroups as $sectionId => $sectionEmployees)
        @if(($groupBy ?? 'section') !== 'none')
        <div class="section-line">{{ isset($groupLabel) ? $groupLabel((string) $sectionId) : $sectionMap->get($sectionId, 'N/A') }}</div>
        @endif

        <table class="t">
            <thead>
                <tr>
                    <th class="card-col">Card No.</th>
                    <th class="name-col">Name</th>
                    <th class="sum-col">P</th>
                    <th class="sum-col">HD</th>
                    <th class="sum-col">L</th>
                    @foreach($days as $dayDate)
                        <th class="day-col">{{ \Carbon\Carbon::parse($dayDate)->format('j') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sectionEmployees as $employee)
                    @php
                        $row = $attendanceStatusByEmployee->get($employee->id, ['status_by_date' => [], 'p' => 0, 'hd' => 0, 'l' => 0]);
                        $statusByDate = $row['status_by_date'] ?? [];
                    @endphp
                    <tr>
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $employee->name }}</td>
                        <td class="tc">{{ $row['p'] ?? 0 }}</td>
                        <td class="tc">{{ $row['hd'] ?? 0 }}</td>
                        <td class="tc">{{ $row['l'] ?? 0 }}</td>
                        @foreach($days as $dayDate)
                            <td class="tc" style="white-space: nowrap">{{ $statusByDate[$dayDate] ?? 'A' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>No employees found.</p>
    @endforelse

    <div class="legend">Legend: P = Present, A = Absent, HD = Holiday/Weekend, L = Leave</div>
</div>
@endsection
