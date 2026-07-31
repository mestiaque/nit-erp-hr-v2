@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @include('hr::reports.partials.bonus-report-print-content')
@endsection
