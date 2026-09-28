@extends('printMaster2')

@section('title', 'Current Asset Report')

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:9px; }
.t th, .t td { border:1px solid #555; padding:3px 4px; }
.t th { background:#1f4e79; color:#fff; text-align:center; }
.t tbody tr:nth-child(odd) { background:#ffffcc; }
.tc { text-align:center; }
.tr { text-align:right; }
.status-in-use { color:#0d6efd; font-weight:700; }
.status-under-repair { color:#fd7e14; font-weight:700; }
.status-disposed { color:#6c757d; font-weight:700; }
.status-retired { color:#dc3545; font-weight:700; }
.grand-total-row td { background:#eef1d4 !important; font-weight:700; }

@media print {
    @page { size: A4 landscape; margin: 6mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
    $fmt = fn ($v) => $v !== null ? number_format((float) $v, 2) : '-';
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">Current Asset Report</div>

@if($assets->isEmpty())
    <p style="text-align:center;color:#888;padding:12px 0;">No assets found.</p>
@else
    <table class="t">
        <thead>
            <tr>
                <th>Asset ID</th>
                <th>Category</th>
                <th>Item</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Quantity</th>
                <th>Location / Dept</th>
                <th>Purchase Date</th>
                <th>Supplier / Vendor</th>
                <th>Unit Cost (BDT)</th>
                <th>Total Acquisition Cost (BDT)</th>
                <th>Useful Life (Years)</th>
                <th>Depreciation Method</th>
                <th>Salvage Value (BDT)</th>
                <th>Age (Years)</th>
                <th>Annual Depreciation (BDT)</th>
                <th>Accumulated Depreciation (BDT)</th>
                <th>Net Book Value (BDT)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                <tr>
                    <td class="tc">{{ $asset->asset_code }}</td>
                    <td>{{ $asset->category->name ?? '-' }}</td>
                    <td>{{ $asset->description }}</td>
                    <td>{{ $asset->brand ?: '-' }}</td>
                    <td>{{ $asset->model ?: '-' }}</td>
                    <td class="tc">{{ $asset->quantity }}</td>
                    <td>{{ $asset->location->name ?? '-' }}</td>
                    <td class="tc">{{ optional($asset->purchase_date)->format('d M Y') ?? '-' }}</td>
                    <td>{{ $asset->supplier_vendor ?? '-' }}</td>
                    <td class="tr">{{ $fmt($asset->unit_cost) }}</td>
                    <td class="tr">{{ $fmt($asset->total_acquisition_cost) }}</td>
                    <td class="tc">{{ $asset->useful_life_years ?? '-' }}</td>
                    <td>{{ $asset->depreciation_method ?: '-' }}</td>
                    <td class="tr">{{ $fmt($asset->salvage_value) }}</td>
                    <td class="tc">{{ $asset->age_years ?? '-' }}</td>
                    <td class="tr">{{ $fmt($asset->annual_depreciation) }}</td>
                    <td class="tr">{{ $fmt($asset->accumulated_depreciation) }}</td>
                    <td class="tr">{{ $fmt($asset->net_book_value) }}</td>
                    <td class="tc status-{{ \Illuminate\Support\Str::slug($asset->status) }}">{{ $asset->status }}</td>
                </tr>
            @endforeach
            <tr class="grand-total-row">
                <td colspan="5" class="tr">Grand Total:</td>
                <td class="tc">{{ $totalQty }}</td>
                <td colspan="3"></td>
                <td class="tr"></td>
                <td class="tr">{{ $fmt($totalCost) }}</td>
                <td colspan="7"></td>
            </tr>
        </tbody>
    </table>
@endif
@endsection
