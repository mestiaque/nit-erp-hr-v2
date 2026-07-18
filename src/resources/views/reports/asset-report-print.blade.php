@extends('printMaster2')

@section('title', 'Asset Report - ' . $fromLabel . ' To ' . $toLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.status-active { color:#0d6efd; font-weight:700; }
.status-returned { color:#198754; font-weight:700; }
.emp-total-row td { background:#fafafa; font-weight:700; }

@media print {
    @page { size: A4 landscape; margin: 7mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">Asset Report &mdash; {{ $fromLabel }} to {{ $toLabel }}</div>

@if($assets->isEmpty())
    <p style="text-align:center;color:#888;padding:12px 0;">No asset handover records found.</p>
@else
    <table class="t">
        <thead>
            <tr>
                <th>SL</th>
                <th>Asset No</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Designation</th>
                <th>Category</th>
                <th>Brand / Model</th>
                <th>Asset Code</th>
                <th>Issued Date</th>
                <th>Expected Return</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if($isRange)
                @php $sl = 1; @endphp
                @foreach($groupedAssets as $employeeId => $rows)
                    @foreach($rows as $asset)
                        <tr>
                            <td class="tc">{{ $sl++ }}</td>
                            <td class="tc">{{ $asset->asset_no }}</td>
                            <td class="tc">{{ $asset->employee->employee_id ?? '-' }}</td>
                            <td>{{ $asset->employee->name ?? 'N/A' }}</td>
                            <td>{{ $asset->employee->department->name ?? 'N/A' }}</td>
                            <td>{{ $asset->employee->section->name ?? 'N/A' }}</td>
                            <td>{{ $asset->employee->designation->name ?? 'N/A' }}</td>
                            <td>{{ $asset->category->name ?? 'N/A' }}</td>
                            <td>{{ trim(($asset->brand ?? '') . ' ' . ($asset->model ?? '')) ?: '-' }}</td>
                            <td>{{ $asset->asset_code ?: '-' }}</td>
                            <td class="tc">{{ optional($asset->issued_date)->format('d-M-Y') }}</td>
                            <td class="tc">{{ optional($asset->expected_return_date)->format('d-M-Y') ?: '-' }}</td>
                            <td class="tc status-{{ strtolower($asset->status) }}">{{ $asset->status }}</td>
                        </tr>
                    @endforeach
                    <tr class="emp-total-row">
                        <td colspan="12" class="tc" style="text-align:right;">Total Assets Held ({{ $rows->first()->employee->name ?? 'N/A' }}):</td>
                        <td class="tc">{{ $rows->count() }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($assets as $i => $asset)
                    <tr>
                        <td class="tc">{{ $i + 1 }}</td>
                        <td class="tc">{{ $asset->asset_no }}</td>
                        <td class="tc">{{ $asset->employee->employee_id ?? '-' }}</td>
                        <td>{{ $asset->employee->name ?? 'N/A' }}</td>
                        <td>{{ $asset->employee->department->name ?? 'N/A' }}</td>
                        <td>{{ $asset->employee->section->name ?? 'N/A' }}</td>
                        <td>{{ $asset->employee->designation->name ?? 'N/A' }}</td>
                        <td>{{ $asset->category->name ?? 'N/A' }}</td>
                        <td>{{ trim(($asset->brand ?? '') . ' ' . ($asset->model ?? '')) ?: '-' }}</td>
                        <td>{{ $asset->asset_code ?: '-' }}</td>
                        <td class="tc">{{ optional($asset->issued_date)->format('d-M-Y') }}</td>
                        <td class="tc">{{ optional($asset->expected_return_date)->format('d-M-Y') ?: '-' }}</td>
                        <td class="tc status-{{ strtolower($asset->status) }}">{{ $asset->status }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
@endif
@endsection
