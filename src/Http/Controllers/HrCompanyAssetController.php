<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Http\Controllers\Concerns\ExportsReportsToExcel;
use ME\Hr\Models\HrAssetCategory;
use ME\Hr\Models\HrCompanyAsset;
use ME\Hr\Models\HrDepartment;

class HrCompanyAssetController extends Controller
{
    use ExportsReportsToExcel;

    public function index(Request $request)
    {
        $query = HrCompanyAsset::with(['category', 'department'])->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('supplier_vendor', 'like', "%{$search}%");
            });
        }

        if ($request->filled('asset_category_id')) {
            $query->where('asset_category_id', $request->asset_category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->paginate(20)->appends($request->query());

        return view('hr::company-assets.index', [
            'assets'      => $assets,
            'request'     => $request,
            'categories'  => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => HrDepartment::orderBy('name')->get(),
            'statuses'    => HrCompanyAsset::STATUSES,
        ]);
    }

    public function create()
    {
        return view('hr::company-assets.form', [
            'asset'       => new HrCompanyAsset(),
            'categories'  => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => HrDepartment::orderBy('name')->get(),
            'statuses'    => HrCompanyAsset::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        HrCompanyAsset::create(array_merge($validated, [
            'asset_code' => $this->nextAssetCode(),
        ]));

        return redirect()->route('hr-center.company-assets.index')
            ->with('success', 'Asset created successfully.');
    }

    public function edit(int $id)
    {
        $asset = HrCompanyAsset::findOrFail($id);

        return view('hr::company-assets.form', [
            'asset'       => $asset,
            'categories'  => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => HrDepartment::orderBy('name')->get(),
            'statuses'    => HrCompanyAsset::STATUSES,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $asset = HrCompanyAsset::findOrFail($id);
        $asset->update($this->validated($request));

        return redirect()->route('hr-center.company-assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        HrCompanyAsset::findOrFail($id)->delete();

        return redirect()->route('hr-center.company-assets.index')
            ->with('success', 'Asset deleted.');
    }

    public function reportScreen(Request $request)
    {
        return view('hr::reports.company-asset-report', [
            'request'     => $request,
            'categories'  => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'departments' => HrDepartment::orderBy('name')->get(),
            'statuses'    => HrCompanyAsset::STATUSES,
        ]);
    }

    public function reportPrint(Request $request)
    {
        if (! $request->boolean('_render')) {
            return view('hr::partials.report-loader-render', [
                'request' => $request,
            ]);
        }

        $query = HrCompanyAsset::with(['category', 'department']);

        if ($request->filled('asset_category_id')) {
            $values = array_filter((array) $request->asset_category_id);
            if (! empty($values)) {
                $query->whereIn('asset_category_id', $values);
            }
        }

        if ($request->filled('department_id')) {
            $values = array_filter((array) $request->department_id);
            if (! empty($values)) {
                $query->whereIn('department_id', $values);
            }
        }

        if ($request->filled('status')) {
            $values = array_filter((array) $request->status);
            if (! empty($values)) {
                $query->whereIn('status', $values);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('purchase_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('purchase_date', '<=', $request->to);
        }

        $assets = $query->orderBy('department_id')->orderBy('asset_code')->get();

        return $this->viewOrXlsx($request, 'hr::reports.company-asset-report-print', [
            'assets'      => $assets,
            'totalQty'    => $assets->sum('quantity'),
            'totalCost'   => $assets->sum('total_acquisition_cost'),
        ], 'current-asset-report');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'asset_category_id' => 'nullable|exists:hr_asset_categories,id',
            'description'       => 'required|string|max:255',
            'quantity'          => 'required|integer|min:1',
            'department_id'     => 'nullable|exists:hr_departments,id',
            'purchase_date'     => 'nullable|date',
            'supplier_vendor'   => 'nullable|string|max:150',
            'unit_cost'         => 'nullable|numeric|min:0',
            'useful_life_years' => 'nullable|integer|min:0',
            'status'            => 'required|in:' . implode(',', HrCompanyAsset::STATUSES),
            'remarks'           => 'nullable|string|max:1000',
        ]);
    }

    private function nextAssetCode(): string
    {
        $year  = now()->year;
        $count = HrCompanyAsset::whereYear('created_at', $year)->count() + 1;

        return sprintf('CA-%d-%04d', $year, $count);
    }
}
