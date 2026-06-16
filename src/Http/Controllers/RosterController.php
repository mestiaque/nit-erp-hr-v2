<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrShiftRosterEmployee as Roaster;
use ME\Hr\Models\HrEmployee as Employee;
use ME\Hr\Models\HrShift as Shift;
use ME\Hr\Models\HrSection as Section;
use ME\Hr\Models\HrSubSection as SubSection;

class RosterController extends Controller
{
    public function index()
    {
        $rosters = Roaster::with(['employee', 'shift'])->orderBy('roster_date', 'desc')->paginate(30);
        $employees = Employee::query()->get();
        $masterData = \App\Services\HrOptionsService::getOptions();
        $shifts = $masterData['shifts'];
        $sections = $masterData['sections'];
        $subSections = $masterData['subSections'];

        return view('hr::rosters.index', compact('rosters', 'employees', 'shifts', 'sections', 'subSections'));
    }

    public function create()
    {
        $employees = Employee::query()->get();
        $masterData = \App\Services\HrOptionsService::getOptions();
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
        Roaster::create([
            'employee_id' => $data['employee_id'] ?? null,
            'shift_id' => $data['shift_id'],
            'roster_date' => $data['date'],
            'remarks' => $data['remarks'] ?? null,
        ]);
        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster assigned successfully.');
    }

    public function destroy($id)
    {
        $roster = Roaster::findOrFail($id);
        $roster->delete();
        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster deleted.');
    }
}
