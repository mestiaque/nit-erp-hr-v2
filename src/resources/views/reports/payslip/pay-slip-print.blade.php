@extends('printMaster2')
@section('contents')

@php
	$reportType = request('report_type', $reportType ?? 'salary');
@endphp

@if($reportType === 'extra_ot')
	@include('hr::reports.payslip.extra-ot-pay-slip-print')
@else
	@include('hr::reports.payslip.individual-pay-slip')
@endif

@endsection
