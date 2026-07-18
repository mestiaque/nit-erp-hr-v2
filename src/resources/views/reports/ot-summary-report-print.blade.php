@extends('printMaster2')

@section('title', 'OT Summary Report - ' . $fromLabel . ' To ' . $toLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; overflow-wrap:break-word; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
/* Scoped to OT Summary specifically — forces identical column widths across every
   section's table, instead of each one auto-sizing to its own longest content. */
.t-ot-summary { table-layout:fixed; }
.grand-total-box { margin-top:8px; padding:6px 10px; background:#f4f4f4; border:1px solid #999; font-size:11px; font-weight:700; display:inline-block; }

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
    $reportType = 'ot-summary';
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">OT Summary Report &mdash; {{ $fromLabel }} to {{ $toLabel }}</div>

@if($employees->isEmpty())
    <p style="text-align:center;color:#888;padding:12px 0;">No employees found.</p>
@else
    @include('hr::reports.job-cards.ot-summary')
@endif
@endsection
