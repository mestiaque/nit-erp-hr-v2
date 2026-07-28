@extends('printMaster2')
@section('contents')
@include('hr::partials.report-loader')
<script>
    (function () {
        if (typeof HrLoader === 'undefined') return;
        HrLoader.showWithTimeout('Loading Report', 10000);
        window.addEventListener('load', function () { HrLoader.hide(); });
    })();
</script>

@php
	$reportType = request('report_type', $reportType ?? 'salary');
@endphp

@if($reportType === 'extra_ot')
	@include('hr::reports.payslip.extra-ot-pay-slip-print')
@else
	@include('hr::reports.payslip.individual-pay-slip')
@endif

@endsection
