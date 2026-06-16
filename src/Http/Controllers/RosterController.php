<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrShiftRosterEmployee;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrSubSection;

class RosterController extends Controller
{
    public function index()
    {
        $rosters = HrShiftRosterEmployee::with(['employee', 'shift'])->orderBy('roster_date', 'desc')->paginate(30);
        $employees = HrEmployee::query()->get();
        $masterData = \ME\Hr\Services\HrOptionsService::getOptions();
        $shifts = $masterData['shifts'];
        $sections = $masterData['sections'];
        $subSections = $masterData['subSections'];

        return view('hr::rosters.index', compact('rosters', 'employees', 'shifts', 'sections', 'subSections'));
    }

    public function create()
    {
        $employees = HrEmployee::query()->get();
        $masterData = \ME\Hr\Services\HrOptionsService::getOptions();
        $shifts = $masterData['shifts'];
        $sections = $masterData['sections'];
        $subSections = $masterData['subSections'];
        return view('hr::rosters.create', compact('employees', 'shifts', 'sections', 'subSections'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|exists:hr_employees,id',
            'shift_id' => 'required',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);
        HrShiftRosterEmployee::create([
            'employee_id' => $data['employee_id'] ?? null,
            'shift_id' => $data['shift_id'],
            'roster_date' => $data['date'],
            'remarks' => $data['remarks'] ?? null,
        ]);
        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster assigned successfully.');
    }

    public function destroy($id)
    {
        $roster = HrShiftRosterEmployee::findOrFail($id);
        $roster->delete();
        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster deleted.');
    }
}
