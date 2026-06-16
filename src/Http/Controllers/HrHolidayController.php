<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use ME\Hr\Models\HrHoliday as Holiday;
use Illuminate\Routing\Controller;

class HrHolidayController extends Controller
{
    private const TYPES = [
        'public',
        'optional',
        'factory',
        'compensatory',
    ];

    public function index(Request $request)
    {
        $query = Holiday::latest();

        if ($request->filled('search')) {
            $query->where('purpose', 'like', '%' . $request->search . '%')
                  ->orWhere('type', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $holidays = $query->paginate(20)->appends($request->query());

        return view('hr::holidays.index', [
            'holidays' => $holidays,
            'request'  => $request,
            'types'    => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'purpose'   => 'required|string|max:200',
            'type'      => 'required|string|in:public,optional,factory,compensatory',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'remarks'   => 'nullable|string|max:500',
            'status'    => 'nullable|integer|in:0,1',
        ]);

        $validated['status'] = (int) ($validated['status'] ?? 1);

        Holiday::create($validated);

        return redirect()->route('hr-center.holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'purpose'   => 'required|string|max:200',
            'type'      => 'required|string|in:public,optional,factory,compensatory',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'remarks'   => 'nullable|string|max:500',
            'status'    => 'nullable|integer|in:0,1',
        ]);

        $validated['status'] = (int) ($validated['status'] ?? 1);

        $holiday->update($validated);

        return redirect()->route('hr-center.holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Holiday::findOrFail($id)->delete();

        return redirect()->route('hr-center.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
