@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection

@section('contents')
@include('hr::partials.report-loader')
<div class="flex-grow-1 p-4">
    @php
        $selectedReportType = $request->report_type ?: 'database';
    @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $reportTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>

        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.show', $reportKey) }}" target="_self">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee ID(s) <small class="text-muted">(use , for multiple)</small></label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="B00144,B00145">
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
                        <label class="mb-1">Block/Line</label>
                        <select name="line_number[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['lines'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->line_number))>{{ trim(($item->name ?? '') . (filled($item->slug ?? null) ? ' - ' . $item->slug : '')) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Salary Type</label>
                        <select name="salary_type[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['salaryTypes'] as $item)
                                <option value="{{ $item['id'] }}" @selected(in_array((string) $item['id'], (array) $request->salary_type))>{{ $item['name'] }}</option>
                            @endforeach
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

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Shift</label>
                        <select name="shift[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['shifts'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->shift))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Gender</label>
                        <select name="gender[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['gender'] as $item)
                                <option value="{{ $item }}" @selected(in_array((string) $item, (array) $request->gender))>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee Status</label>
                        <select name="employee_status[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['employeeStatuses'] as $item)
                                <option value="{{ $item['id'] }}" @selected(in_array((string) $item['id'], (array) $request->employee_status))>{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Language</label>
                        <select name="language" class="form-control form-control-sm select2">
                            <option value="bn" @selected(($request->language ?? 'bn') === 'bn')>Bangla</option>
                            <option value="en" @selected(($request->language ?? 'en') === 'en')>English</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Group By <small class="text-muted">(Database/Details print)</small></label>
                        <select name="group_by" class="form-control form-control-sm">
                            @foreach($groupByOptions as $key => $label)
                                <option value="{{ $key }}" @selected(($request->group_by ?? 'none') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Report Type</label>
                        <select name="report_type" class="form-control form-control-sm select2">
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}" @selected($selectedReportType === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="xlsx" id="xlsxFlag" value="0">
                    <div class="col-md-3 align-self-end mb-3">
                        <div class="w-100 d-flex gap-2">
                            <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm flex-fill"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                            <button type="submit" name="print" value="1" class="btn btn-primary btn-sm flex-fill" formtarget="_blank" id="reportPrintBtn"><i class="fa-solid fa-print"></i> Print</button>
                            <button type="submit" name="print" value="1" class="btn btn-success btn-sm flex-fill" formtarget="_blank" id="reportExcelBtn"><i class="fa-solid fa-file-excel"></i> Excel</button>
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
        var xlsxFlag = document.getElementById('xlsxFlag');
        if (btn) {
            btn.addEventListener('click', function () {
                // Reset in case Excel was clicked earlier in this same page load —
                // the hidden field otherwise stays '1' and Print would export xlsx too.
                if (xlsxFlag) xlsxFlag.value = '0';
                if (typeof HrLoader === 'undefined') return;
                HrLoader.showWithTimeout('Generating Report', 8000);
                setTimeout(function () { HrLoader.hide(); }, 1500);
            });
        }

        var excelBtn = document.getElementById('reportExcelBtn');
        if (excelBtn && xlsxFlag) {
            excelBtn.addEventListener('click', function () {
                xlsxFlag.value = '1';
            });
        }
    })();

    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
