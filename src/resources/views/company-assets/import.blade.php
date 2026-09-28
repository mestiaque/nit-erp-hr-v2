@extends('admin.layouts.app')

@section('title')
<title>Import Assets</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    @include('hr::partials.excel-import-page', [
        'title' => 'Import Assets from Excel',
        'backUrl' => route('hr-center.company-assets.index'),
        'templateUrl' => route('hr-center.company-assets.import.template'),
        'previewUrl' => route('hr-center.company-assets.import.preview'),
        'saveUrl' => route('hr-center.company-assets.import.save'),
        'recheckUrl' => route('hr-center.company-assets.import.recheck'),
        'columns' => [
            ['key' => 'asset_code', 'label' => 'Asset ID'],
            ['key' => 'category', 'label' => 'Category'],
            ['key' => 'item', 'label' => 'Item'],
            ['key' => 'brand', 'label' => 'Brand'],
            ['key' => 'model', 'label' => 'Model'],
            ['key' => 'quantity', 'label' => 'Qty', 'align' => 'right'],
            ['key' => 'location', 'label' => 'Location / Dept'],
            ['key' => 'purchase_date', 'label' => 'Purchase Date'],
            ['key' => 'supplier_vendor', 'label' => 'Supplier / Vendor'],
            ['key' => 'unit_cost', 'label' => 'Unit Cost', 'align' => 'right'],
            ['key' => 'useful_life_years', 'label' => 'Useful Life (Yrs)', 'align' => 'right'],
            ['key' => 'depreciation_method', 'label' => 'Depreciation Method'],
            ['key' => 'salvage_value', 'label' => 'Salvage Value', 'align' => 'right'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => 'Action'],
        ],
    ])
</div>
@endsection
