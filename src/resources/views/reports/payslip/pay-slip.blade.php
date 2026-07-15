@extends('admin.layouts.app')
@section('title')
<title>Pay Slip</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-body">
            <form method="GET" action="" class=" mb-4">
                <div class="row">

                    <div class="col-md-3 mb-2">
                        <label>Employee ID(s)</label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ request('employee_ids') }}" placeholder="Comma separated">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Month</label>
                        <select name="month" class="form-control form-control-sm">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ request('month', now()->month) == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Year</label>
                        <select name="year" class="form-control form-control-sm">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ request('year', now()->year) == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Classification</label>
                        <select name="classification[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('classification')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Department</label>
                        <select name="department[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['departments'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('department')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Section</label>
                        <select name="section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['sections'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('section')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Sub-Section</label>
                        <select name="sub_section[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['subSections'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('sub_section')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Shift</label>
                        <select name="shift[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['shifts'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('shift')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Working Place</label>
                        <select name="working_place[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['workingPlaces'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('working_place')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Block / Line</label>
                        <select name="line_number[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['lines'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('line_number')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Designation</label>
                        <select name="designation[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['designations'] as $item)
                                <option value="{{ $item->id }}" {{ in_array((string)$item->id, (array) request('designation')) ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Employee Status</label>
                        <select name="employee_status[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['employeeStatuses'] as $item)
                                <option value="{{ $item['id'] }}" {{ in_array((string)$item['id'], (array) request('employee_status')) ? 'selected' : '' }}>{{ $item['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Language</label>
                        <select name="language" class="form-control form-control-sm">
                            @foreach($languages as $key => $label)
                                <option value="{{ $key }}" {{ request('language', 'bn') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label>Pay Slip Type</label>
                        <select name="report_type" class="form-control form-control-sm">
                            @foreach($reportTypes as $key => $label)
                                <option value="{{ $key }}" {{ request('report_type', 'salary') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Generate</button>
                        <button type="button" class="btn btn-success" id="printBtn">Print</button>
                    </div>
                </div>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var printBtn = document.getElementById('printBtn');
                var form = printBtn.closest('form');
                printBtn.addEventListener('click', function(e) {
                    var printInput = form.querySelector('input[name="print"]');
                    if (!printInput) {
                        printInput = document.createElement('input');
                        printInput.type = 'hidden';
                        printInput.name = 'print';
                        printInput.value = '1';
                        form.appendChild(printInput);
                    }
                    var url = form.action || window.location.href;
                    var params = new URLSearchParams(new FormData(form)).toString();
                    window.open(url + (url.includes('?') ? '&' : '?') + params, '_blank');
                });
            });
            </script>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
