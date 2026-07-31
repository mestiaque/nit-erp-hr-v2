@extends('printMaster2')

@section('title', 'Employee Report - Details')

@push('css')
<style>
    .report-head {
        text-align: center;
        margin-bottom: 14px;
    }
    .report-head h3 {
        margin: 0 0 4px;
    }
    .meta-line {
        margin-bottom: 10px;
        font-size: 12px;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #222;
        padding: 4px 5px;
        font-size: 11px;
        vertical-align: top;
    }
    .report-table thead th {
        text-align: center;
        background: #f2f2f2;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
</style>
@endpush

@section('contents')
<div class="report-head text-center">
    @if(!blank(general()->logo()))
        <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ hr_factory('name') ?? 'Company Name' }}</h3>
    <div>{{ hr_factory('address') ?? '' }}</div>
    <strong>Employee Report - Details</strong>
</div>
<div class="meta-line">
    <strong>Print Date:</strong> {{ now()->format('d-m-Y h:i A') }}
    <span style="margin-left: 18px;"><strong>Total Employee:</strong> {{ $detailsRows->count() }}</span>
</div>
@forelse($groups as $groupKey => $groupRows)
@if($groupBy !== 'none')
    <div class="section-title">{{ $groupLabel((string) $groupKey) }}</div>
@endif
<table class="report-table">
    <thead>
        <tr>
            <th>S.L</th>
            <th>Working Place</th>
            <th>Emp. ID</th>
            <th>Name</th>
            <th>Join Date</th>
            <th>Job Age</th>
            <th>DOB</th>
            <th>Age</th>
            <th>Sex</th>
            <th>Department</th>
            <th>Section</th>
            <th>Sub Section</th>
            <th>Designation</th>
            <th>Contact No.</th>
            <th>Grade</th>
            <th>Classification</th>
            <th>Line/Block</th>
            <th>Shift</th>
            <th>WeekEnd</th>
            <th>Gross Salary</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupRows as $row)
            <tr>
                <td>{{ $row['sl'] }}</td>
                <td>{{ $row['working_place'] ?? 'N/A' }}</td>
                <td>{{ $row['employee_id'] ?? 'N/A' }}</td>
                <td>{{ $row['name'] ?? 'N/A' }}</td>
                <td>{{ $row['join_date'] ?? 'N/A' }}</td>
                <td>{{ $row['job_age'] ?? 'N/A' }}</td>
                <td>{{ $row['dob'] ?? 'N/A' }}</td>
                <td>{{ $row['age'] ?? 'N/A' }}</td>
                <td>{{ $row['sex'] ?? 'N/A' }}</td>
                <td>{{ $row['department'] ?? 'N/A' }}</td>
                <td>{{ $row['section'] ?? 'N/A' }}</td>
                <td>{{ $row['sub_section'] ?? 'N/A' }}</td>
                <td>{{ $row['designation'] ?? 'N/A' }}</td>
                <td>{{ $row['contact_no'] ?? 'N/A' }}</td>
                <td>{{ $row['grade'] ?? 'N/A' }}</td>
                <td>{{ $row['classification'] ?? 'N/A' }}</td>
                <td>{{ $row['line_block'] ?? 'N/A' }}</td>
                <td>{{ $row['shift'] ?? 'N/A' }}</td>
                <td>{{ $row['weekend'] ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format((float) ($row['gross_salary'] ?? 0), 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@empty
    <p style="text-align:center;color:#888;padding:12px 0;">No employee found.</p>
@endforelse
@endsection
