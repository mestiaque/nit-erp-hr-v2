@extends('admin.layouts.app')

@section('title')
<title>{{ $asset->id ? 'Edit' : 'Create' }} Asset Handover</title>
@endsection

@push('css')
<style>
    .sec-card { border-radius: 12px; border: 1px solid #eef0f4; margin-bottom: 16px; }
    .sec-card .card-header { background: #f8f9fb; font-weight: 700; font-size: 14px; border-radius: 12px 12px 0 0; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $asset->id ? 'Edit' : 'Create' }} Asset Handover</h4>
        <a href="{{ route('hr-center.employee-assets.index') }}" class="btn btn-light btn-sm">Back</a>
    </div>

    <form method="POST" action="{{ $asset->id ? route('hr-center.employee-assets.update', $asset->id) : route('hr-center.employee-assets.store') }}">
        @csrf
        @if($asset->id) @method('PUT') @endif

        {{-- A. Employee Information --}}
        <div class="card sec-card">
            <div class="card-header">A. Employee Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label mb-1">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control form-control-sm select2" required>
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($asset->employee_id == $employee->id)>{{ $employee->employee_id }} — {{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Name, Designation, Department are taken from the employee record.</small>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label mb-1">Reporting Manager</label>
                        <input type="text" name="reporting_manager" class="form-control form-control-sm" value="{{ old('reporting_manager', $asset->reporting_manager) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- B. Asset Information --}}
        <div class="card sec-card">
            <div class="card-header">B. Asset Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label mb-1">Asset Category</label>
                        <select name="asset_category_id" class="form-control form-control-sm select2">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($asset->asset_category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-2">
                        <label class="form-label mb-1">Asset Description</label>
                        <input type="text" name="asset_description" class="form-control form-control-sm" value="{{ old('asset_description', $asset->asset_description) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Brand</label>
                        <input type="text" name="brand" class="form-control form-control-sm" value="{{ old('brand', $asset->brand) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Model</label>
                        <input type="text" name="model" class="form-control form-control-sm" value="{{ old('model', $asset->model) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Color</label>
                        <input type="text" name="color" class="form-control form-control-sm" value="{{ old('color', $asset->color) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Serial / IMEI / Chassis No.</label>
                        <input type="text" name="serial_no" class="form-control form-control-sm" value="{{ old('serial_no', $asset->serial_no) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Engine No. <small class="text-muted">(Vehicle)</small></label>
                        <input type="text" name="engine_no" class="form-control form-control-sm" value="{{ old('engine_no', $asset->engine_no) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Registration No.</label>
                        <input type="text" name="registration_no" class="form-control form-control-sm" value="{{ old('registration_no', $asset->registration_no) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Company Asset Code</label>
                        <input type="text" name="asset_code" class="form-control form-control-sm" value="{{ old('asset_code', $asset->asset_code) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Purchase Value (BDT)</label>
                        <input type="number" step="0.01" min="0" name="purchase_value" class="form-control form-control-sm" value="{{ old('purchase_value', $asset->purchase_value) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- C. Accessories Provided --}}
        <div class="card sec-card">
            <div class="card-header">C. Accessories Provided</div>
            <div class="card-body">
                <div class="row">
                    @php $selectedAccessories = old('accessories', $asset->accessories ?? []); @endphp
                    @foreach($accessories as $item)
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="accessories[]" value="{{ $item }}"
                                       id="acc_{{ Str::slug($item) }}" @checked(in_array($item, (array) $selectedAccessories))>
                                <label class="form-check-label" for="acc_{{ Str::slug($item) }}">{{ $item }}</label>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-4 mt-2">
                        <label class="form-label mb-1">Others</label>
                        <input type="text" name="accessories_others" class="form-control form-control-sm" value="{{ old('accessories_others', $asset->accessories_others) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- D. Purpose of Issue --}}
        <div class="card sec-card">
            <div class="card-header">D. Purpose of Issue</div>
            <div class="card-body">
                <div class="row">
                    @php $selectedPurposes = old('purpose_of_issue', $asset->purpose_of_issue ?? []); @endphp
                    @foreach($purposes as $item)
                        <div class="col-md-3 mb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="purpose_of_issue[]" value="{{ $item }}"
                                       id="purpose_{{ Str::slug($item) }}" @checked(in_array($item, (array) $selectedPurposes))>
                                <label class="form-check-label" for="purpose_{{ Str::slug($item) }}">{{ $item }}</label>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-md-4 mt-2">
                        <label class="form-label mb-1">Others</label>
                        <input type="text" name="purpose_others" class="form-control form-control-sm" value="{{ old('purpose_others', $asset->purpose_others) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- E. Handover Details --}}
        <div class="card sec-card">
            <div class="card-header">E. Handover Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Issued Date <span class="text-danger">*</span></label>
                        <input type="date" name="issued_date" class="form-control form-control-sm" value="{{ old('issued_date', optional($asset->issued_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Expected Return Date</label>
                        <input type="date" name="expected_return_date" class="form-control form-control-sm" value="{{ old('expected_return_date', optional($asset->expected_return_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Condition at Handover</label>
                        <select name="condition_at_handover" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            @foreach($conditions as $condition)
                                <option value="{{ $condition }}" @selected(old('condition_at_handover', $asset->condition_at_handover) === $condition)>{{ $condition }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mb-2">
                        <label class="form-label mb-1">Remarks</label>
                        <textarea name="handover_remarks" class="form-control form-control-sm" rows="2">{{ old('handover_remarks', $asset->handover_remarks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary btn-sm px-4">
                {{ $asset->id ? 'Update' : 'Save & Print' }}
            </button>
            <a href="{{ route('hr-center.employee-assets.index') }}" class="btn btn-light btn-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
$('.select2').select2({
    placeholder: 'Search...',
    allowClear: true,
    width: '100%'
});
</script>
@endpush
