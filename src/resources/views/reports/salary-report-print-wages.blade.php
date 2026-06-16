@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @php($salaryPrintMode = 'wages')
    @include('hr::reports.partials.salary-report-print-content')
@endsection
