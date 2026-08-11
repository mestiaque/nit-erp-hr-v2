<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeGatePass;

class HrEmployeeGatePassController extends Controller
{
    private const REASONS = [
        'ব্যক্তিগত কাজ',
        'চিকিৎসা / জরুরী প্রয়োজন',
        'অফিসিয়াল কাজ',
        'ব্যাংক সংক্রান্ত কাজ',
        'পারিবারিক জরুরী প্রয়োজন',
        'মিটিং / প্রশিক্ষণ',
        'ক্রয় সংক্রান্ত কাজ',
        'সরকারি অফিসের কাজ',
        'বাসা/বাড়ির কাজ',
        'অন্যান্য',
    ];

    public function index(Request $request)
    {
        $query = HrEmployeeGatePass::with(['employee.department'])->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pass_no', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        $gatePasses = $query->paginate(20)->appends($request->query());

        $employees = HrEmployee::with('department')
            ->naturalOrderById()
            ->get(['id', 'employee_id', 'name', 'department_id']);

        return view('hr::gate-passes.index', [
            'gatePasses' => $gatePasses,
            'employees'  => $employees,
            'reasons'    => self::REASONS,
            'request'    => $request,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id'      => 'required|exists:hr_employees,id',
            'date'             => 'required|date',
            'out_time'         => 'required|date_format:H:i',
            'in_time'          => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:1',
            'reason'           => 'required|string|max:100',
            'remarks'          => 'nullable|string|max:1000',
        ]);

        $outTime = Carbon::parse($validated['date'] . ' ' . $validated['out_time']);

        $inTime = null;
        if (!empty($validated['in_time'])) {
            $inTime = Carbon::parse($validated['date'] . ' ' . $validated['in_time']);
            if ($inTime->lte($outTime)) {
                $inTime->addDay(); // gate pass crosses midnight
            }
        }

        $duration = $validated['duration_minutes'] ?? ($inTime ? (int) $outTime->diffInMinutes($inTime) : 0);

        $gatePass = HrEmployeeGatePass::create([
            'pass_no'          => $this->nextPassNo(),
            'employee_id'      => $validated['employee_id'],
            'out_time'         => $outTime,
            'in_time'          => $inTime,
            'duration_minutes' => $duration,
            'reason'           => $validated['reason'],
            'remarks'          => $validated['remarks'] ?? null,
            'status'           => 'Active',
        ]);

        return redirect()->route('hr-center.gate-passes.index')
            ->with('success', 'Gate pass created successfully.')
            ->with('printed_gate_pass_id', $gatePass->id);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $gatePass = HrEmployeeGatePass::findOrFail($id);

        $validated = $request->validate([
            'out_time'         => 'required|date',
            'in_time'          => 'nullable|date|after:out_time',
            'duration_minutes' => 'nullable|integer|min:1',
            'reason'           => 'required|string|max:100',
            'remarks'          => 'nullable|string|max:1000',
            'status'           => 'required|in:Active,Returned',
        ]);

        $outTime  = Carbon::parse($validated['out_time']);
        $inTime   = !empty($validated['in_time']) ? Carbon::parse($validated['in_time']) : null;
        $duration = $validated['duration_minutes'] ?? ($inTime ? (int) $outTime->diffInMinutes($inTime) : $gatePass->duration_minutes);

        $gatePass->update([
            'out_time'         => $outTime,
            'in_time'          => $inTime,
            'duration_minutes' => $duration,
            'reason'           => $validated['reason'],
            'remarks'          => $validated['remarks'] ?? null,
            'status'           => $validated['status'],
        ]);

        return redirect()->route('hr-center.gate-passes.index')
            ->with('success', 'Gate pass updated successfully.');
    }

    public function print(int $id)
    {
        $gatePass = HrEmployeeGatePass::with(['employee.department', 'employee.designation'])->findOrFail($id);

        return view('hr::gate-passes.print', compact('gatePass'));
    }

    private function nextPassNo(): string
    {
        $year  = now()->year;
        $count = HrEmployeeGatePass::whereYear('created_at', $year)->count() + 1;

        return sprintf('GP-%d-%04d', $year, $count);
    }
}
