@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @include('hr::reports.partials.salary-sheet-print-content')
@endsection
