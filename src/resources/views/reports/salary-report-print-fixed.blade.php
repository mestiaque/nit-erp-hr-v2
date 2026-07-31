@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
    @if(general()->company_s_code == 'SFL')
        @include('hr::reports.partials.salary-sheet-print-content-sfl')
    @else
        @include('hr::reports.partials.salary-sheet-print-content')
    @endif
@endsection
