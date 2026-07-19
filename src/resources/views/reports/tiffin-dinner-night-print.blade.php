@extends('printMaster2')

@section('title', $reportTypeLabel . ' Report - ' . $dateLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.tr { text-align:right; }
.summary-row td { background:#e8f5e9; font-weight:700; }

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

<div class="sub-title">{{ $reportTypeLabel }} Allowance Report &mdash; {{ $dateLabel }}</div>

@if($dailyRows->isEmpty() && $monthlyRows->isEmpty())
    <p style="text-align:center;color:#888;padding:12px 0;">No eligible {{ strtolower($reportTypeLabel) }} allowance records found for this period.</p>
@endif

@if($dailyRows->isNotEmpty())
    <div class="section-title">Daily Payment — Eligible Records</div>
    <table class="t">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Designation</th>
                <th>Date</th>
                <th>Day</th>
                <th>Worked Hours</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dailyRows as $i => $row)
                <tr>
                    <td class="tc">{{ $i + 1 }}</td>
                    <td class="tc">{{ $row['employee']->employee_id ?? '-' }}</td>
                    <td>{{ $row['employee']->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->department)->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->section)->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->designation)->name ?? 'N/A' }}</td>
                    <td class="tc">{{ $row['date'] }}</td>
                    <td class="tc">{{ $row['day'] }}</td>
                    <td class="tc">{{ $row['worked_hours'] }}</td>
                    <td class="tr">{{ number_format($row['amount'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="9" class="tr">Total Daily Payable Amount:</td>
                <td class="tr">{{ number_format($dailyTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif

@if($monthlyRows->isNotEmpty())
    <div class="section-title">Monthly Payment — Eligibility Summary</div>
    <table class="t">
        <thead>
            <tr>
                <th>SL</th>
                <th>Emp. ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Section</th>
                <th>Designation</th>
                <th>Total Eligible Days</th>
                <th>Allowance Rate</th>
                <th>Total Payable Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyRows as $i => $row)
                <tr>
                    <td class="tc">{{ $i + 1 }}</td>
                    <td class="tc">{{ $row['employee']->employee_id ?? '-' }}</td>
                    <td>{{ $row['employee']->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->department)->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->section)->name ?? 'N/A' }}</td>
                    <td>{{ optional($row['employee']->designation)->name ?? 'N/A' }}</td>
                    <td class="tc">{{ $row['eligible_days'] }}</td>
                    <td class="tr">{{ number_format($row['rate'], 2) }}</td>
                    <td class="tr">{{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="summary-row">
                <td colspan="8" class="tr">Total Monthly Payable Amount:</td>
                <td class="tr">{{ number_format($monthlyTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif
@endsection
