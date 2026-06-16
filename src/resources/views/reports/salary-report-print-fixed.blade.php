@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @php($salaryPrintMode = 'fixed')
    @include('hr::reports.partials.salary-report-print-content')
@endsection
