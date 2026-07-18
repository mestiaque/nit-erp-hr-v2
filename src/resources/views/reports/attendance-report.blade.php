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
                        <label class="mb-1">From Date</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from ?? date('Y-m-01') }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">To Date</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to ?? date('Y-m-d') }}">
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
                        <label class="mb-1">Salary Type</label>
                        <select name="salary_type[]" class="form-control form-control-sm select2" multiple>
                            <option value="fixed_rate" @selected(in_array('fixed_rate', (array)$request->salary_type))>Fixed Rate</option>
                            <option value="price_rate" @selected(in_array('price_rate', (array)$request->salary_type))>Price Rate</option>
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
                        <label class="mb-1">Shift (Today)</label>
                        <div>
                            @foreach($options['shifts'] as $item)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="today_shifts[]"
                                           id="ts_{{ $item->id }}" value="{{ $item->id }}"
                                           @checked(in_array((string)$item->id, (array)$request->today_shifts))>
                                    <label class="form-check-label" for="ts_{{ $item->id }}">{{ $item->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Shift (Last Day)</label>
                        <div>
                            @foreach($options['shifts'] as $item)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="lastday_shifts[]"
                                           id="ls_{{ $item->id }}" value="{{ $item->id }}"
                                           @checked(in_array((string)$item->id, (array)$request->lastday_shifts))>
                                    <label class="form-check-label" for="ls_{{ $item->id }}">{{ $item->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Attendance Type</label>
                        <select name="att_type" class="form-control form-control-sm">
                            <option value="">All</option>
                            @foreach($attendanceTypes as $key => $label)
                                <option value="{{ $key }}" @selected($request->att_type === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Group By</label>
                        <select name="group_by" class="form-control form-control-sm">
                            @foreach($groupByOptions as $key => $label)
                                <option value="{{ $key }}" @selected(($request->group_by ?? 'section') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                        <a href="{{ route('hr-center.reports.show', $reportKey) }}" class="btn btn-light btn-sm">Reset</a>
                        <button type="submit" name="print" value="1" formtarget="_blank" class="btn btn-primary btn-sm">
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
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'All',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
