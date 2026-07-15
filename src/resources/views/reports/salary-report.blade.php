@extends('admin.layouts.app')

@php
    $salaryReportType = $request->report_type ?? 'fixed';
    $salaryReportLabel = $reportTypes[$salaryReportType] ?? ucfirst($salaryReportType);
    $salaryReportPageTitle = $salaryReportLabel . ' Report';
@endphp

@section('title')
<title>{{ $salaryReportPageTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $salaryReportPageTitle }}</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
@php
    $salaryReportRoutes = [
        'fixed' => route('hr-center.reports.fixed-salary-print'),
        'production' => route('hr-center.reports.production-salary-print'),
        'bonus' => route('hr-center.reports.bonus-salary-print'),
        'wages-salary-summary' => route('hr-center.reports.wages-salary-summary-print'),
    ];
    $salaryScreenRoutes = [
        'fixed' => route('hr-center.reports.fixed-salary'),
        'production' => route('hr-center.reports.production-salary'),
        'bonus' => route('hr-center.reports.bonus-salary'),
        'wages-salary-summary' => route('hr-center.reports.wages-salary-summary'),
    ];

    // Use central HR options service for all lookups
    $hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
    $departmentMap = collect($hrOptions['departments'])->pluck('name', 'id');
    $sectionMap = collect($hrOptions['sections'])->pluck('name', 'id');
    $subSectionMap = collect($hrOptions['subSections'])->pluck('name', 'id');
    $designationMap = collect($hrOptions['designations'])->pluck('name', 'id');
@endphp

            <form method="get" action="{{ $salaryScreenRoutes[$salaryReportType] }}">
                <input type="hidden" name="report_type" value="{{ $salaryReportType }}">
                <div class="row">

                    {{-- <div class="col-12 mb-2">
                        <div class="btn-group" role="group">
                            @foreach($reportTypes as $typeKey => $typeLabel)
                                <button type="button"
                                        class="btn btn-sm {{ $salaryReportType === $typeKey ? 'btn-primary' : 'btn-outline-primary' }}"
                                        data-report-type="{{ $typeKey }}"
                                        data-route="{{ $salaryReportRoutes[$typeKey] }}">
                                    {{ $typeLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div> --}}

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="mb-1">Employee ID(s) <small class="text-muted">(use , for multiple)</small></label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="B00144,B00145">
                    </div>

                    <div class="col-md-3 mb-3" id="bonus-title-field" style="{{ $salaryReportType === 'bonus' ? '' : 'display:none;' }}">
                        <label class="mb-1">Bonus Title <span class="text-danger">*</span></label>
                        <select name="bonus_title" class="form-control form-control-sm">
                            <option value="">-- Select Bonus Title --</option>
                            @foreach($bonusTitles as $bt)
                                <option value="{{ $bt->id }}" @selected((string)$request->bonus_title === (string)$bt->id)>{{ $bt->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3" id="bonus-upto-field" style="{{ $salaryReportType === 'bonus' ? '' : 'display:none;' }}">
                        <label class="mb-1">Up To Date <small class="text-muted">(for job age calc)</small></label>
                        <input type="date" name="up_to_date" class="form-control form-control-sm"
                               value="{{ $request->up_to_date ?? date('Y-m-d') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Department</label>
                        <select name="department[]" class="form-control form-control-sm select2" multiple>
                            @foreach($departmentMap as $id => $name)
                                <option value="{{ $id }}" @selected(in_array((string)$id, (array)$request->department))>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Section</label>
                        <select name="section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($sectionMap as $id => $name)
                                <option value="{{ $id }}" @selected(in_array((string)$id, (array)$request->section))>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Sub-Section</label>
                        <select name="sub_section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($subSectionMap as $id => $name)
                                <option value="{{ $id }}" @selected(in_array((string)$id, (array)$request->sub_section))>{{ $name }}</option>
                            @endforeach
                        </select>
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
                        <label class="mb-1">Working Place</label>
                        <select name="working_place[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['workingPlaces'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->working_place))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Block / Line</label>
                        <select name="line_number[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['lines'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->line_number))>{{ $item->name }}{{ $item->slug ? ' - '.$item->slug : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Designation</label>
                        <select name="designation[]" class="form-control form-control-sm select2" multiple>
                            @foreach(\ME\Hr\Models\HrDesignation::orderBy('name')->get(['id','name']) as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->designation))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee Status</label>
                        <select name="employee_status[]" class="form-control form-control-sm select2" multiple>
                            <option value="regular" @selected(in_array('regular', (array)$request->employee_status))>Regular</option>
                            <option value="lefty" @selected(in_array('lefty', (array)$request->employee_status))>Lefty</option>
                            <option value="resign" @selected(in_array('resign', (array)$request->employee_status))>Resign</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Language</label>
                        <select name="language" class="form-control form-control-sm">
                            <option value="en" @selected(($request->language ?? 'en') === 'en')>English</option>
                            <option value="bn" @selected($request->language === 'bn')>বাংলা</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Payment Mode</label>
                        <div>
                            @foreach($paymentModes as $mode)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="payment_modes[]"
                                           id="pm_{{ Str::slug($mode) }}" value="{{ $mode }}"
                                           @checked(in_array($mode, (array)$request->payment_modes))>
                                    <label class="form-check-label" for="pm_{{ Str::slug($mode) }}">{{ $mode }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>



                    <div class="col-md-3 mb-3">
                        <label class="mb-1 d-block">With Picture</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="with_picture" value="1"
                                   id="withPicture" @checked($request->boolean('with_picture'))>
                            <label class="form-check-label" for="withPicture">Show Photo</label>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1 d-block">Lock Salary</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="lock_salary" value="1"
                                   id="lockSalary" @checked($request->boolean('lock_salary'))>
                            <label class="form-check-label" for="lockSalary">Apply Lock</label>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                        <a href="{{ $salaryScreenRoutes[$salaryReportType] }}" class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" id="printSubmitBtn" formaction="{{ $salaryReportRoutes[$salaryReportType] }}"
                                formtarget="_blank" class="btn btn-primary btn-sm">
                            Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var reportTypeInput = document.querySelector('input[name="report_type"]');
    var bonusTitleField = document.getElementById('bonus-title-field');
    var bonusUptoField  = document.getElementById('bonus-upto-field');

    function toggleBonusFields() {
        var isBonus = reportTypeInput && reportTypeInput.value === 'bonus';
        if (bonusTitleField) bonusTitleField.style.display = isBonus ? '' : 'none';
        if (bonusUptoField)  bonusUptoField.style.display  = isBonus ? '' : 'none';
    }

    var reportTypeButtons = document.querySelectorAll('[data-report-type]');
    var printSubmitBtn = document.getElementById('printSubmitBtn');

    reportTypeButtons.forEach(function (el) {
        el.addEventListener('click', function () {
            if (reportTypeInput) reportTypeInput.value = el.dataset.reportType;
            if (printSubmitBtn) printSubmitBtn.setAttribute('formaction', el.dataset.route);

            reportTypeButtons.forEach(function (btn) {
                btn.classList.toggle('btn-primary', btn === el);
                btn.classList.toggle('btn-outline-primary', btn !== el);
            });

            toggleBonusFields();
        });
    });

    toggleBonusFields();
});

$('.select2').select2({
    placeholder: 'All',
    allowClear: true,
    width: '100%'
});
</script>
@endpush
