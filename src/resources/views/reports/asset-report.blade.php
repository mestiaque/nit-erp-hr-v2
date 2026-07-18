@extends('admin.layouts.app')

@section('title')
<title>Asset Report</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Asset Report</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.asset-report-print') }}" target="_blank">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Employee ID(s) <small class="text-muted">(use , for multiple)</small></label>
                        <input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="e.g. A00001,A00002">
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
                        <label class="mb-1">Classification</label>
                        <select name="classification[]" class="form-control form-control-sm select2" multiple>
                            @foreach($options['classifications'] as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->classification))>{{ $item->name }}</option>
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
                        <label class="mb-1">Employee Status</label>
                        <select name="employee_status[]" class="form-control form-control-sm select2" multiple>
                            <option value="regular" @selected(in_array('regular', (array)$request->employee_status))>Regular</option>
                            <option value="lefty" @selected(in_array('lefty', (array)$request->employee_status))>Lefty</option>
                            <option value="resign" @selected(in_array('resign', (array)$request->employee_status))>Resign</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Asset Category</label>
                        <select name="asset_category[]" class="form-control form-control-sm select2" multiple>
                            @foreach($categories as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->asset_category))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Asset Status</label>
                        <select name="asset_status[]" class="form-control form-control-sm select2" multiple>
                            <option value="Active" @selected(in_array('Active', (array)$request->asset_status))>Active</option>
                            <option value="Returned" @selected(in_array('Returned', (array)$request->asset_status))>Returned</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Issued Date From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Issued Date To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to }}">
                    </div>

                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="w-100 d-flex gap-2">
                            <a href="{{ route('hr-center.reports.asset-report') }}" class="btn btn-light btn-sm w-50 mr-2"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                            <button type="submit" class="btn btn-primary btn-sm w-50"><i class="fa-solid fa-print"></i> Print</button>
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
$('.select2').select2({
    placeholder: 'All',
    allowClear: true,
    width: '100%'
});
</script>
@endpush
