<?php
namespace ME\Hr\Http\Controllers;

use ME\Hr\Models\ProductionRate;
use ME\Hr\Models\ProductionRateProcess;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ProductionRateController extends Controller
{
    public function index()
    {
        $rates = ProductionRate::all();
        return view('hr::production-rate.index', compact('rates'));
    }

    public function create()
    {
        return view('hr::production-rate.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'local_agent' => 'nullable|string',
            'buyer' => 'nullable|string',
            'style_name' => 'nullable|string',
            'style_number' => 'nullable|string',
            'gauge' => 'nullable|string',
            'order_qty' => 'nullable|integer',
            'merchandiser' => 'nullable|string',
            'process' => 'nullable|string',
            'rate' => 'nullable|numeric',
            'pro_process' => 'nullable|string',
        ]);
        ProductionRate::create($data);
        return redirect()->route('hr-center.production-rate.index')->with('success', 'Production Rate created successfully.');
    }

    public function edit($id)
    {
        $rate = ProductionRate::findOrFail($id);
        return view('hr::production-rate.edit', compact('rate'));
    }

    public function update(Request $request, $id)
    {
        $rate = ProductionRate::findOrFail($id);
        $data = $request->validate([
            'local_agent' => 'nullable|string',
            'buyer' => 'nullable|string',
            'style_name' => 'nullable|string',
            'style_number' => 'nullable|string',
            'gauge' => 'nullable|string',
            'order_qty' => 'nullable|integer',
            'merchandiser' => 'nullable|string',
            'process' => 'nullable|string',
            'rate' => 'nullable|numeric',
            'pro_process' => 'nullable|string',
        ]);
        $rate->update($data);
        return redirect()->route('hr-center.production-rate.index')->with('success', 'Production Rate updated successfully.');
    }

    public function destroy($id)
    {
        $rate = ProductionRate::findOrFail($id);
        $rate->delete();
        return redirect()->route('hr-center.production-rate.index')->with('success', 'Production Rate deleted successfully.');
    }

    // Modal progress assignment logic
    public function assignProgress(Request $request, $id)
    {
        $rate = ProductionRate::findOrFail($id);
        // Save new processes if provided
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'processes' => 'required|array',
                'processes.*.process' => 'required|string',
                'processes.*.rate' => 'required|numeric',
                'processes.*.pro_process' => 'nullable|string',
            ]);
            // Remove old processes and save new
            $rate->processes()->delete();
            foreach ($data['processes'] as $proc) {
                $rate->processes()->create($proc);
            }
            return response()->json(['status' => 'success']);
        }
        // For GET: return all processes for modal display
        $processes = $rate->processes()->get();
        return response()->json(['processes' => $processes]);
    }
}
