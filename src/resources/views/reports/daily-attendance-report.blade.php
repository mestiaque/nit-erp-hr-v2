@extends('admin.layouts.app')

@section('title')
<title>Daily Attendance Report</title>
@endsection

@section('contents')
@include('hr::partials.report-loader')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Daily Attendance Report</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.daily-attendance-report-print') }}" target="_blank">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">From Date</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from ?? date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">To Date</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to ?? date('Y-m-d') }}">
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
                        <label class="mb-1">Employee ID(s) <small class="text-muted">(use , for multiple)</small></label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="B00144,B00145">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Classification</label>
                        <select name="classification[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->classification))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Department</label>
                        <select name="department[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['departments'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->department))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Section</label>
                        <select name="section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['sections'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->section))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['subSections'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->sub_section))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Designation</label>
                        <select name="designation[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['designations'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->designation))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Shift</label>
                        <select name="shift[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['shifts'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->shift))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="_render" id="renderFlag" value="0">
                    <input type="hidden" name="xlsx" id="xlsxFlag" value="0">
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="w-100 d-flex gap-2">
                            <a href="{{ route('hr-center.reports.daily-attendance-report') }}" class="btn btn-light btn-sm flex-fill"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                            <button type="submit" id="reportPrintBtn" class="btn btn-primary btn-sm flex-fill"><i class="fa-solid fa-print"></i> Print</button>
                            <button type="submit" id="reportExcelBtn" class="btn btn-success btn-sm flex-fill"><i class="fa-solid fa-file-excel"></i> Excel</button>
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
(function () {
    var btn = document.getElementById('reportPrintBtn');
    var renderFlag = document.getElementById('renderFlag');
    var xlsxFlag = document.getElementById('xlsxFlag');
    if (btn) {
        btn.addEventListener('click', function () {
            // Reset in case Excel was clicked earlier in this same page load —
            // the hidden fields otherwise stay '1' and Print would export xlsx too.
            if (renderFlag) renderFlag.value = '0';
            if (xlsxFlag) xlsxFlag.value = '0';
            if (typeof HrLoader === 'undefined') return;
            HrLoader.showWithTimeout('Generating Report', 8000);
            setTimeout(function () { HrLoader.hide(); }, 1500);
        });
    }

    // Excel export skips the print page's async loading-shell entirely (that shell
    // fetches the report as HTML and document.write()s it in — feeding it a binary
    // .xlsx download instead would just corrupt the page) by requesting the fully
    // rendered report directly via _render=1, same as the shell's own background
    // fetch does, plus xlsx=1 so the controller streams a file instead of a view.
    var excelBtn = document.getElementById('reportExcelBtn');
    if (excelBtn && renderFlag && xlsxFlag) {
        excelBtn.addEventListener('click', function () {
            renderFlag.value = '1';
            xlsxFlag.value = '1';
        });
    }
})();

$('.select2').select2({
    placeholder: 'All',
    allowClear: true,
    width: '100%'
});
</script>
@endpush
