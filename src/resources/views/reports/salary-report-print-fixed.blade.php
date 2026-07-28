@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@section('contents')
@include('hr::partials.report-loader')
<script>
    (function () {
        if (typeof HrLoader === 'undefined') return;
        HrLoader.showWithTimeout('Loading Report', 10000);
        window.addEventListener('load', function () { HrLoader.hide(); });
    })();
</script>
    @if(ENV('FACTORY') === 'SFL')
        @include('hr::reports.partials.salary-sheet-print-content-sfl')
    @else
        @include('hr::reports.partials.salary-sheet-print-content')
    @endif
@endsection
