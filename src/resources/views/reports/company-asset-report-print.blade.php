@extends('printMaster2')

@section('title', 'Current Asset Report')

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title { font-size:12px; font-weight:700; margin:8px 0 4px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
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
    @page { size: A4 landscape; margin: 7mm; }
    body { margin: 0; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
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
                <th>Description</th>
                <th>Quantity</th>
                <th>Location / Dept</th>
                <th>Purchase Date</th>
                <th>Supplier / Vendor</th>
                <th>Unit Cost ($)</th>
                <th>Total Acquisition Cost</th>
                <th>Useful Life (Years)</th>
                <th>Age (Years)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assets as $asset)
                <tr>
                    <td class="tc">{{ $asset->asset_code }}</td>
                    <td>{{ $asset->category->name ?? '-' }}</td>
                    <td>{{ $asset->description }}</td>
                    <td class="tc">{{ $asset->quantity }}</td>
                    <td>{{ $asset->department->name ?? '-' }}</td>
                    <td class="tc">{{ optional($asset->purchase_date)->format('d M Y') ?? '-' }}</td>
                    <td>{{ $asset->supplier_vendor ?? '-' }}</td>
                    <td class="tr">{{ $asset->unit_cost !== null ? number_format((float)$asset->unit_cost, 2) : '-' }}</td>
                    <td class="tr">{{ $asset->total_acquisition_cost !== null ? number_format($asset->total_acquisition_cost, 2) : '-' }}</td>
                    <td class="tc">{{ $asset->useful_life_years ?? '-' }}</td>
                    <td class="tc">{{ $asset->age_years ?? '-' }}</td>
                    <td class="tc status-{{ \Illuminate\Support\Str::slug($asset->status) }}">{{ $asset->status }}</td>
                </tr>
            @endforeach
            <tr class="grand-total-row">
                <td colspan="3" class="tr">Grand Total:</td>
                <td class="tc">{{ $totalQty }}</td>
                <td colspan="3"></td>
                <td class="tr"></td>
                <td class="tr">{{ number_format((float)$totalCost, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>
@endif
@endsection
