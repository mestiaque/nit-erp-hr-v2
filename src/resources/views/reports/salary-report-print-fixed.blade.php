@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @php($salaryPrintMode = 'fixed')
    @if(ENV('FACTORY') === 'SFL')
        @include('hr::reports.partials.salary-report-print-content-sfl')
    @else
        @include('hr::reports.partials.salary-report-print-content')
    @endif
@endsection
