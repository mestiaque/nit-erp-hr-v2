@extends('admin.layouts.app')

@section('title')
<title>Asset Management</title>
@endsection

@push('css')
<style>
    .badge-soft-in-use { background:#e7f1ff; color:#0d6efd; font-weight:600; padding:4px 10px; border-radius:20px; font-size:11px; }
    .badge-soft-under-repair { background:#fff3e6; color:#fd7e14; font-weight:600; padding:4px 10px; border-radius:20px; font-size:11px; }
    .badge-soft-disposed { background:#eceef1; color:#6c757d; font-weight:600; padding:4px 10px; border-radius:20px; font-size:11px; }
    .badge-soft-retired { background:#fdeaea; color:#dc3545; font-weight:600; padding:4px 10px; border-radius:20px; font-size:11px; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Asset Management</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('hr-center.reports.company-asset-report') }}" class="btn btn-sm rounded-pill px-3 ui-act ui-act-print" title="Asset Register Report"><i class="fa-solid fa-print"></i> Asset Register Report</a>
                @can('hr_company_asset.import')
                <a href="{{ route('hr-center.company-assets.import') }}" class="btn btn-sm rounded-pill px-3 ui-act ui-act-excel" title="Import Excel"><i class="fa-solid fa-file-excel"></i> Import Excel</a>
                @endcan
                <a href="{{ route('hr-center.company-assets.create') }}" class="btn btn-sm rounded-pill px-3 ui-act ui-act-create" title="Add Asset"><i class="fa-solid fa-plus"></i> Add Asset</a>
            </div>
        </div>
        <div class="card-body">


            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $request->search }}"
                           class="form-control form-control-sm" placeholder="Asset code, description, supplier...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Category</label>
                    <select name="asset_category_id" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected($request->asset_category_id == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Location / Dept</label>
                    <select name="location_id" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}" @selected($request->location_id == $l->id)>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected($request->status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm ui-act ui-act-filter" title="Filter"><i class="fa-solid fa-filter"></i> Filter</button>
                    <a href="{{ route('hr-center.company-assets.index') }}" class="btn btn-sm ui-act ui-act-reset" title="Reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Asset ID</th>
                            <th>Category</th>
                            <th>Item</th>
                            <th>Brand / Model</th>
                            <th width="70">Qty</th>
                            <th>Location / Dept</th>
                            <th>Purchase Date</th>
                            <th>Supplier / Vendor</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Total Acquisition Cost</th>
                            <th class="text-center">Useful Life (Yrs)</th>
                            <th class="text-center">Age (Yrs)</th>
                            <th width="110">Status</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $index => $asset)
                        <tr>
                            <td>{{ $assets->firstItem() + $index }}</td>
                            <td>{{ $asset->asset_code }}</td>
                            <td>{{ $asset->category->name ?? '-' }}</td>
                            <td>{{ $asset->description }}</td>
                            <td>{{ trim(($asset->brand ?? '') . ' ' . ($asset->model ?? '')) ?: '-' }}</td>
                            <td>{{ $asset->quantity }}</td>
                            <td>{{ $asset->location->name ?? '-' }}</td>
                            <td>{{ optional($asset->purchase_date)->format('d M Y') ?? '-' }}</td>
                            <td>{{ $asset->supplier_vendor ?? '-' }}</td>
                            <td class="text-end">{{ $asset->unit_cost !== null ? number_format((float) $asset->unit_cost, 2) : '-' }}</td>
                            <td class="text-end">{{ $asset->total_acquisition_cost !== null ? number_format($asset->total_acquisition_cost, 2) : '-' }}</td>
                            <td class="text-center">{{ $asset->useful_life_years ?? '-' }}</td>
                            <td class="text-center">{{ $asset->age_years ?? '-' }}</td>
                            <td>
                                <span class="badge-soft-{{ \Illuminate\Support\Str::slug($asset->status) }}">{{ $asset->status }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('hr-center.company-assets.edit', $asset->id) }}" class="btn-custom ui-act ui-act-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form method="POST" action="{{ route('hr-center.company-assets.destroy', $asset->id) }}" style="display:inline" onsubmit="return confirm('Delete this asset?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom ui-act ui-act-delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="text-center text-muted">No assets found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $assets->links('pagination::bootstrap-5') }}

        </div>
    </div>
</div>
@endsection
