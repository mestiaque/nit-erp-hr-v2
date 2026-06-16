@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @php($salaryPrintMode = 'bonus')
    @include('hr::reports.partials.salary-report-print-content')
@endsection
