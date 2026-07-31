@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @include('hr::reports.partials.salary-summary-print-content')
@endsection
