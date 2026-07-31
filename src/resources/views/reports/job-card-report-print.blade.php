@extends('printMaster2')

@section('title', 'Job Card Report - ' . $fromLabel . ' To ' . $toLabel)

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
.info-grid { width:100%; border-collapse:collapse; margin-bottom:6px; font-size:10px; }
.info-grid td { padding:2px 6px; border:1px solid #ccc; width:25%; }
.info-grid td:nth-child(odd) { font-weight:700; background:#f5f5f5; }
.page-break { page-break-after: always; }
.badge-lock { background:#e74c3c; color:#fff; padding:1px 5px; border-radius:3px; font-size:9px; }
.grand-total-box { margin-top:8px; padding:6px 10px; background:#f4f4f4; border:1px solid #999; font-size:11px; font-weight:700; display:inline-block; }
.t th, .t td { overflow-wrap:break-word; }
/* Scoped to OT Summary specifically — forces identical column widths across every
   section's table, instead of each one auto-sizing to its own longest content. */
.t-ot-summary { table-layout:fixed; }
</style>
@endpush


@section('contents')

{{-- =============================================================== --}}
{{-- JOB CARD (individual per employee) --}}
{{-- =============================================================== --}}
@include('hr::reports.job-cards.individual-job-card')

{{-- =============================================================== --}}
{{-- JOB CARD SUMMARY (section-wise, day columns) --}}
{{-- =============================================================== --}}
@include('hr::reports.job-cards.section-wise-job-card-summary')

{{-- =============================================================== --}}
{{-- ATTENDANCE SUMMARY (section-wise) --}}
{{-- =============================================================== --}}
@include('hr::reports.job-cards.section-wise-attendance-summary')

{{-- =============================================================== --}}
{{-- OT DETAILS (section-wise, day-OT columns) --}}
{{-- =============================================================== --}}
@include('hr::reports.job-cards.ot-details')

{{-- =============================================================== --}}
{{-- OT SUMMARY (designation/section grouping, day columns) --}}
{{-- =============================================================== --}}
@include('hr::reports.job-cards.ot-summary')

@endsection
