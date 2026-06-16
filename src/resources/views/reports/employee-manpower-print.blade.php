@extends('printMaster2')

@section('title', 'Employee Report - Manpower Summary')

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
    .subtotal-row td {
        background: #d8f3c8;
        font-weight: 700;
    }
    .grandtotal-row td {
        background: #bfe7b8;
        font-weight: 700;
    }
</style>
@endpush

@section('contents')
@php
    // Option maps — built from $options passed by controller
    $subSectionMap   = collect($options['subSections'] ?? [])->keyBy('id');
    $departmentMap   = collect($options['departments'] ?? [])->pluck('name', 'id');
    $sectionMap      = collect($options['sections'] ?? [])->pluck('name', 'id');
    $designationMap  = collect($options['designations'] ?? [])->pluck('name', 'id');
    $grandTotalEmployees = (int) data_get($manpowerRows->firstWhere('row_type', 'grand_total'), 'recruited', 0);
@endphp
<div class="report-head text-center">
    <h3>{{ general()->title ?? 'Company Name' }}</h3>
    <div>{{ general()->address_one ?? data_get(general(), 'address') }}</div>
    <strong>Employee Report - Manpower Summary</strong>
</div>
<div class="meta-line">
    <strong>Print Date:</strong> {{ now()->format('d-m-Y h:i A') }}
    <span style="margin-left: 18px;"><strong>Total Employee:</strong> {{ $grandTotalEmployees }}</span>
</div>
<table class="report-table">
    <thead>
        <tr>
            <th>SL</th>
            <th>Department</th>
            <th>Section</th>
            <th>Sub Section</th>
            <th>Designation</th>
            <th>Approve Manpower</th>
            <th>Recruited</th>
            <th>Deviation</th>
            <th>Total Gross Salary(TK)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($manpowerRows as $row)
            <tr class="{{ $row['row_type'] === 'grand_total' ? 'grandtotal-row' : ($row['row_type'] === 'total' ? 'subtotal-row' : '') }}">
                <td class="text-center">{{ $row['sl'] }}</td>
                <td>{{ $row['department'] }}</td>
                <td>{{ $row['section'] }}</td>
                <td>{{ $row['sub_section'] }}</td>
                <td>{{ $row['designation'] }}</td>
                <td class="text-center">{{ $row['approve_manpower'] }}</td>
                <td class="text-center">{{ $row['recruited'] }}</td>
                <td class="text-center">{{ $row['deviation'] }}</td>
                <td class="text-right">{{ number_format($row['total_gross_salary'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No data found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
