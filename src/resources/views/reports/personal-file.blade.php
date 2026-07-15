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
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <div class="border rounded p-3">
                        <form method="get" action="{{ route('hr-center.reports.show', $reportKey) }}" target="_self">
                            <div class="row">
                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Employee ID(s) (,)</label>
                                    <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="EMP001,EMP002">
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">From</label>
                                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from }}">
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Subsection</label>
                                    <select name="subsection[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['subsections'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->subsection))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Designation</label>
                                    <select name="designation[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['designations'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->designation))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Classification</label>
                                    <select name="classification[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['classifications'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->classification))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Department</label>
                                    <select name="department[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['departments'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->department))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Section</label>
                                    <select name="section[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['sections'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->section))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Subsection</label>
                                    <select name="subsection[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['subsections'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->subsection))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Shift</label>
                                    <select name="shift[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['shifts'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->shift))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Working Place</label>
                                    <select name="working_place[]" class="form-control form-control-sm select2" multiple>
                                        @foreach($options['workingPlaces'] as $item)
                                            <option value="{{ $item->id }}" @selected(in_array((string) $item->id, (array) $request->working_place))>{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Employee Status</label>
                                    <select name="employee_status[]" class="form-control form-control-sm select2" multiple>
                                        <option value="regular" @selected(in_array('regular', (array) $request->employee_status))>Regular</option>
                                        <option value="lefty" @selected(in_array('lefty', (array) $request->employee_status))>Lefty</option>
                                        <option value="resign" @selected(in_array('resign', (array) $request->employee_status))>Resign</option>
                                    </select>
                                </div>

                                <div class="mb-2 col-md-3">
                                    <label class="mb-1">Language</label>
                                    <select name="language" class="form-control form-control-sm">
                                        <option value="bn" @selected(($request->language ?? 'bn') === 'bn')>Bangla</option>
                                        <option value="en" @selected(($request->language ) === 'en')>English</option>
                                    </select>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label class="mb-1">Report Type</label>
                                    <select name="report_type" class="form-control form-control-sm select2" required>
                                        @foreach($reportTypes as $key => $label)
                                            <option value="{{ $key }}" @selected(($request->report_type ?? 'id-card') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('report_type')
                                        <small class="text-danger d-block">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="d-flex align-items-center mb-2 col-md-3 float-right">
                                    <button type="submit" class="btn btn-secondary btn-sm w-100 mr-1">Filter</button>
                                    <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm w-100">Reset</a>
                                    <button type="button" id="personalFilePrintBtn" class="btn btn-primary btn-sm w-100 mr-1 no-loader">Print </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Section</th>
                                    <th>Shift</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->employee_id }}</td>
                                        <td>{{ $employee->name }}</td>
                                        <td>{{ optional($employee->department)->name }}</td>
                                        <td>{{ optional($employee->section)->name }}</td>
                                        <td>{{ optional($employee->shift)->name }}</td>
                                        <td>{{ ucfirst((string) ($employee->employment_status ?? 'regular')) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No employee found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    (function() {
        const printButton = document.getElementById('personalFilePrintBtn');
        const reportForm = printButton ? printButton.closest('form') : null;

        if (!printButton || !reportForm) {
            return;
        }

        function hideLoaderIfVisible() {
            if (typeof XLoader !== 'undefined' && typeof XLoader.hide === 'function') {
                XLoader.hide();
            }
        }

        function openPrintTab() {
            try {
                if (typeof reportForm.reportValidity === 'function' && !reportForm.reportValidity()) {
                    hideLoaderIfVisible();
                    return false;
                }

                const formData = new FormData(reportForm);
                formData.set('print', '1');

                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    params.append(key, String(value));
                }

                const url = reportForm.action + (reportForm.action.includes('?') ? '&' : '?') + params.toString();
                window.open(url, '_blank');
            } finally {
                // Always hide loader, even if error
                hideLoaderIfVisible();
                // Hide loader again after a short delay in case any async loader is triggered
                setTimeout(hideLoaderIfVisible, 300);
                setTimeout(hideLoaderIfVisible, 800);
            }
            return false;
        }

        // Right/middle/modified clicks can trigger global loader logic without unloading this page.
        printButton.addEventListener('mousedown', function(event) {
            if (event.button === 2 || event.ctrlKey || event.metaKey) {
                event.stopPropagation();
                hideLoaderIfVisible();
            }
        }, true);

        printButton.addEventListener('contextmenu', hideLoaderIfVisible);
        printButton.addEventListener('auxclick', function(event) {
            event.preventDefault();
            event.stopPropagation();
            openPrintTab();
        });

        printButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            openPrintTab();
            return false;
        });

        printButton.addEventListener('mouseup', function(event) {
            if (event.button === 2 || event.ctrlKey || event.metaKey) {
                hideLoaderIfVisible();
            }
        });
    })();
    
    $('.select2').select2({
        placeholder: 'All',
        allowClear: true,
        width: '100%'
    });
</script>
@endpush
