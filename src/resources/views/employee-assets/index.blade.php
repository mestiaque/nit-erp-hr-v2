@extends('admin.layouts.app')

@section('title')
<title>Employee Asset Management</title>
@endsection

@push('css')
<style>
    .badge-soft-active { background: #e7f1ff; color: #0d6efd; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
    .badge-soft-returned { background: #eaf7ee; color: #198754; font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 11px; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Employee Asset Management</h4>
            <a href="{{ route('hr-center.employee-assets.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus"></i> Create Handover
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $request->search }}"
                           class="form-control form-control-sm" placeholder="Asset No, Code, Serial, Employee ID or Name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="Active" @selected($request->status === 'Active')>Active</option>
                        <option value="Returned" @selected($request->status === 'Returned')>Returned</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hr-center.employee-assets.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Asset No</th>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Brand / Model</th>
                            <th>Asset Code</th>
                            <th>Issued Date</th>
                            <th width="90">Status</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $index => $asset)
                        <tr>
                            <td>{{ $assets->firstItem() + $index }}</td>
                            <td>{{ $asset->asset_no }}</td>
                            <td>{{ $asset->employee->employee_id ?? '-' }} &mdash; {{ $asset->employee->name ?? 'N/A' }}</td>
                            <td>{{ $asset->category->name ?? 'N/A' }}</td>
                            <td>{{ $asset->brand }} {{ $asset->model }}</td>
                            <td>{{ $asset->asset_code }}</td>
                            <td>{{ optional($asset->issued_date)->format('d M Y') }}</td>
                            <td>
                                @if($asset->status === 'Active')
                                    <span class="badge-soft-active">Active</span>
                                @else
                                    <span class="badge-soft-returned">Returned</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('hr-center.employee-assets.print', $asset->id) }}" class="btn-custom" title="Print" target="_blank">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('hr-center.employee-assets.edit', $asset->id) }}" class="btn-custom yellow" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                @if($asset->status === 'Active')
                                    <button type="button" class="btn-custom" title="Mark Returned"
                                            data-toggle="modal" data-target="#ReturnAssetModal"
                                            data-id="{{ $asset->id }}"
                                            data-asset-no="{{ $asset->asset_no }}">
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No asset handover found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $assets->links() }}

        </div>
    </div>
</div>

{{-- ===================== RETURN MODAL ===================== --}}
<div class="modal fade" id="ReturnAssetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="ReturnAssetForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Asset Returned <span id="return_asset_no" class="text-muted"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-2">
                        <label class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Received By</label>
                        <input type="text" name="received_by" class="form-control form-control-sm" placeholder="Name of receiving officer">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label">Condition Upon Return</label>
                        <select name="condition_on_return" class="form-control form-control-sm">
                            <option value="">— Select —</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Requires Minor Repair">Requires Minor Repair</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Outstanding Damage Cost (BDT)</label>
                        <input type="number" step="0.01" min="0" name="damage_cost" class="form-control form-control-sm" placeholder="0.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
document.querySelectorAll('[data-target="#ReturnAssetModal"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('ReturnAssetForm').action =
            '{{ url("admin/hr-center/employee-assets") }}/' + this.dataset.id + '/return';
        document.getElementById('return_asset_no').textContent = '(' + this.dataset.assetNo + ')';
    });
});

@if(session('printed_asset_id'))
    window.open('{{ route("hr-center.employee-assets.print", session("printed_asset_id")) }}', '_blank');
@endif
</script>
@endpush
