<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrAssetCategory;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeAsset;

class HrEmployeeAssetController extends Controller
{
    public const ACCESSORIES = [
        'Charger', 'Battery', 'Laptop Bag', 'Mouse', 'Keyboard', 'SIM Card',
        'Helmet', 'Vehicle Key(s)', 'Registration Copy', 'Insurance Copy',
        'Fuel Card', 'User Manual',
    ];

    public const PURPOSES = [
        'Official Duty', 'Sales & Marketing', 'Merchandising',
        'Factory Operations', 'Management Use', 'Travel',
    ];

    public const CONDITIONS = ['Excellent', 'Good', 'Fair', 'Requires Minor Repair'];

    public function index(Request $request)
    {
        $query = HrEmployeeAsset::with(['employee.department', 'category'])->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_no', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('serial_no', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->paginate(20)->appends($request->query());

        return view('hr::employee-assets.index', [
            'assets'  => $assets,
            'request' => $request,
        ]);
    }

    public function create()
    {
        return view('hr::employee-assets.form', [
            'asset'      => new HrEmployeeAsset(),
            'employees'  => HrEmployee::naturalOrderById()->get(['id', 'employee_id', 'name']),
            'categories' => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'accessories'=> self::ACCESSORIES,
            'purposes'   => self::PURPOSES,
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $asset = HrEmployeeAsset::create(array_merge($validated, [
            'asset_no' => $this->nextAssetNo(),
            'status'   => 'Active',
        ]));

        return redirect()->route('hr-center.employee-assets.index')
            ->with('success', 'Asset handover created successfully.')
            ->with('printed_asset_id', $asset->id);
    }

    public function edit(int $id)
    {
        $asset = HrEmployeeAsset::findOrFail($id);

        return view('hr::employee-assets.form', [
            'asset'      => $asset,
            'employees'  => HrEmployee::naturalOrderById()->get(['id', 'employee_id', 'name']),
            'categories' => HrAssetCategory::where('status', 'active')->orderBy('name')->get(),
            'accessories'=> self::ACCESSORIES,
            'purposes'   => self::PURPOSES,
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $asset = HrEmployeeAsset::findOrFail($id);
        $asset->update($this->validated($request));

        return redirect()->route('hr-center.employee-assets.index')
            ->with('success', 'Asset handover updated successfully.');
    }

    public function returnAsset(Request $request, int $id): RedirectResponse
    {
        $asset = HrEmployeeAsset::findOrFail($id);

        $validated = $request->validate([
            'return_date'          => 'required|date',
            'received_by'          => 'nullable|string|max:150',
            'condition_on_return'  => 'nullable|string|max:30',
            'damage_cost'          => 'nullable|numeric|min:0',
        ]);

        $asset->update(array_merge($validated, ['status' => 'Returned']));

        return redirect()->route('hr-center.employee-assets.index')
            ->with('success', 'Asset marked as returned.');
    }

    public function print(int $id)
    {
        $asset = HrEmployeeAsset::with(['employee.department', 'employee.designation', 'employee.workingPlace', 'category'])
            ->findOrFail($id);

        return view('hr::employee-assets.print', [
            'asset'       => $asset,
            'accessories' => self::ACCESSORIES,
            'purposes'    => self::PURPOSES,
        ]);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'employee_id'           => 'required|exists:hr_employees,id',
            'asset_category_id'     => 'nullable|exists:hr_asset_categories,id',
            'reporting_manager'     => 'nullable|string|max:150',
            'asset_description'     => 'nullable|string|max:255',
            'brand'                 => 'nullable|string|max:150',
            'model'                 => 'nullable|string|max:150',
            'color'                 => 'nullable|string|max:100',
            'serial_no'             => 'nullable|string|max:150',
            'engine_no'             => 'nullable|string|max:150',
            'registration_no'       => 'nullable|string|max:150',
            'asset_code'            => 'nullable|string|max:100',
            'purchase_value'        => 'nullable|numeric|min:0',
            'accessories'           => 'nullable|array',
            'accessories_others'    => 'nullable|string|max:255',
            'purpose_of_issue'      => 'nullable|array',
            'purpose_others'        => 'nullable|string|max:255',
            'issued_date'           => 'required|date',
            'expected_return_date'  => 'nullable|date',
            'condition_at_handover' => 'nullable|string|max:30',
            'handover_remarks'      => 'nullable|string|max:1000',
        ]);

        $validated['accessories'] = $validated['accessories'] ?? [];
        $validated['purpose_of_issue'] = $validated['purpose_of_issue'] ?? [];

        return $validated;
    }

    private function nextAssetNo(): string
    {
        $year  = now()->year;
        $count = HrEmployeeAsset::whereYear('created_at', $year)->count() + 1;

        return sprintf('AST-%d-%04d', $year, $count);
    }
}
