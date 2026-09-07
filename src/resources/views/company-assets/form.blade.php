@extends('admin.layouts.app')

@section('title')
<title>{{ $asset->id ? 'Edit' : 'Create' }} Company Asset</title>
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
        <h4 class="mb-0">{{ $asset->id ? 'Edit' : 'Create' }} Company Asset</h4>
        <a href="{{ route('hr-center.company-assets.index') }}" class="btn btn-light btn-sm">Back</a>
    </div>

    <form method="POST" action="{{ $asset->id ? route('hr-center.company-assets.update', $asset->id) : route('hr-center.company-assets.store') }}">
        @csrf
        @if($asset->id) @method('PUT') @endif

        <div class="card sec-card">
            <div class="card-header">Asset Information</div>
            <div class="card-body">
                <div class="row">
                    @if($asset->id)
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Asset ID</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $asset->asset_code }}" disabled>
                    </div>
                    @endif
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Category</label>
                        <select name="asset_category_id" class="form-control form-control-sm select2">
                            <option value="">— Select Category —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected($asset->asset_category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label mb-1">Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description', $asset->description) }}" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Quantity <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="quantity" class="form-control form-control-sm" value="{{ old('quantity', $asset->quantity ?? 1) }}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Location / Dept</label>
                        <select name="department_id" class="form-control form-control-sm select2">
                            <option value="">— Select —</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected($asset->department_id == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Supplier / Vendor</label>
                        <input type="text" name="supplier_vendor" class="form-control form-control-sm" value="{{ old('supplier_vendor', $asset->supplier_vendor) }}">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Unit Cost ($)</label>
                        <input type="number" step="0.01" min="0" name="unit_cost" class="form-control form-control-sm" value="{{ old('unit_cost', $asset->unit_cost) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Useful Life (Years)</label>
                        <input type="number" min="0" name="useful_life_years" class="form-control form-control-sm" value="{{ old('useful_life_years', $asset->useful_life_years) }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control form-control-sm" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $asset->status ?? 'In Use') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-2">
                        <label class="form-label mb-1">Remarks</label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2">{{ old('remarks', $asset->remarks) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-sm px-4">Save</button>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
$('.select2').select2({ width: '100%' });
</script>
@endpush
