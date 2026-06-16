<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrRegularToWeekend;
use ME\Hr\Models\HrSection;

class RegularToWeekendController extends Controller
{
    public function index(Request $request)
    {
        $query = HrRegularToWeekend::query();
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }
        $items = $query->orderByDesc('id')->paginate(20)->appends($request->query());
        $sections = HrSection::orderBy('name')->get(['id', 'name']);
        return view('hr::regular-to-weekend.index', compact('items', 'sections', 'request'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section_id' => 'required',
            'date' => 'required|date',
            'type' => 'required|in:weekend,half_day',
            'status' => 'nullable|integer|in:0,1',
        ]);
        $data['status'] = (int) ($data['status'] ?? 1);
        HrRegularToWeekend::create($data);
        return back()->with('success', 'Entry created successfully.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'section_id' => 'required',
            'date' => 'required|date',
            'type' => 'required|in:weekend,half_day',
            'status' => 'nullable|integer|in:0,1',
        ]);
        $data['status'] = (int) ($data['status'] ?? 1);
        $item = HrRegularToWeekend::findOrFail($id);
        $item->update($data);
        return back()->with('success', 'Entry updated successfully.');
    }
}
