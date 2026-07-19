@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $reportTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.show', $reportKey) }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="mb-1" id="fromLabel">From</label>
                        <input type="date" name="from" id="fromInput"
                               value="{{ $request->from }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1" id="toLabel">To</label>
                        <input type="date" name="to" value="{{ $request->to }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Classification</label>
                        <select name="classification[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->classification))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Department</label>
                        <select name="department[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['departments'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->department))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Section</label>
                        <select name="section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['sections'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->section))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['subSections'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->sub_section))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Working Place</label>
                        <select name="working_place[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['workingPlaces'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->working_place))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Salary Type</label>
                        <select name="salary_type[]" class="form-control form-control-sm select2" multiple>
                            <option value="fixed_rate" @selected(in_array('fixed_rate', (array) $request->salary_type))>Fixed Rate</option>
                            <option value="price_rate" @selected(in_array('price_rate', (array) $request->salary_type))>Price Rate</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Designation</label>
                        <select name="designation[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['designations'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->designation))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3" id="minAbsentDaysWrap" style="display:none;">
                        <label class="mb-1">Min. Consecutive Absent Days</label>
                        <input type="number" min="1" name="min_absent_days" class="form-control form-control-sm" value="{{ $request->input('min_absent_days', 3) }}" placeholder="3">
                    </div>
                    <div class="col-md-3 mb-3" id="attWithOtDateWrap" style="display:none;">
                        <label class="mb-1">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $request->input('date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3" id="incrementPercentWrap" style="display:none;">
                        <label class="mb-1">Increment %</label>
                        <input type="number" step="0.01" min="0" name="increment_percent" class="form-control form-control-sm" value="{{ $incrementPercent }}">
                    </div>
                    <div class="col-md-3 mb-3" id="effectiveDateWrap" style="display:none;">
                        <label class="mb-1">Effective Date</label>
                        <input type="date" name="effective_date" class="form-control form-control-sm" value="{{ $effectiveDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Group By</label>
                        <select name="group_by" class="form-control form-control-sm">
                            @foreach($groupByOptions as $key => $label)
                                <option value="{{ $key }}" @selected(($request->group_by ?? 'none') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Report Type</label>
                        <select name="report_type" id="monthlyReportType" class="form-control form-control-sm">
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}" @selected($reportType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end mb-3">
                        <div class="w-100 d-flex gap-2">
                            <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm w-50 mr-2"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                            <button type="submit" name="print" value="1" class="btn btn-primary btn-sm w-50" formtarget="_blank"><i class="fa-solid fa-print"></i> Print</button>
                        </div>
                    </div>
                </div>


            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    (function() {
        const reportTypeEl = document.getElementById('monthlyReportType');
        const incrementWrap = document.getElementById('incrementPercentWrap');
        const effectiveWrap = document.getElementById('effectiveDateWrap');
        const minAbsentWrap = document.getElementById('minAbsentDaysWrap');
        const attWithOtDateWrap = document.getElementById('attWithOtDateWrap');
        const fromLabel     = document.getElementById('fromLabel');
        const toLabel       = document.getElementById('toLabel');
        const fromInput     = document.getElementById('fromInput');

        function toggleIncrementFields() {
            const value = reportTypeEl ? reportTypeEl.value : '';
            const showIncrement = value === 'increment' || value === 'increment-summary';
            const showLongAbsent = value === 'long-absent';
            const showAttWithOt = value === 'attendance-with-ot';

            if (incrementWrap) incrementWrap.style.display = showIncrement ? 'block' : 'none';
            if (effectiveWrap) effectiveWrap.style.display = showIncrement ? 'block' : 'none';
            if (minAbsentWrap) minAbsentWrap.style.display = showLongAbsent ? 'block' : 'none';
            if (attWithOtDateWrap) attWithOtDateWrap.style.display = showAttWithOt ? 'block' : 'none';

            // Change From/To labels to clarify join-date / increment-date filtering
            if (fromLabel) fromLabel.textContent = showIncrement ? 'Join / Inc Date From' : 'From';
            if (toLabel)   toLabel.textContent   = showIncrement ? 'Join / Inc Date To'   : 'To';
        }

        if (reportTypeEl) {
            reportTypeEl.addEventListener('change', toggleIncrementFields);
            toggleIncrementFields();
        }

        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    })();
</script>
@endpush
