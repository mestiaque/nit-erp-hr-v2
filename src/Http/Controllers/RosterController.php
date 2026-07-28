<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrShiftRosterEmployee;
use ME\Hr\Models\HrEmployeeShiftRule;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrSubSection;

class RosterController extends Controller
{
    public function index()
    {
        $rosters = HrShiftRosterEmployee::with(['employee', 'shift'])->orderBy('roster_date', 'desc')->paginate(30);
        $rules = HrEmployeeShiftRule::with(['employee', 'primaryShift', 'alternateShifts.shift'])->get();
        $employees = HrEmployee::query()->naturalOrderById()->get();
        $masterData = \ME\Hr\Services\HrOptionsService::getOptions();
        $shifts = $masterData['shifts'];
        $sections = $masterData['sections'];
        $subSections = $masterData['subSections'];

        return view('hr::rosters.index', compact('rosters', 'rules', 'employees', 'shifts', 'sections', 'subSections'));
    }

    // GET /rosters/create and /rosters/{id}/edit now just land on the index page,
    // which handles create/edit entirely through modals — kept as routes (rather
    // than removed) so any existing bookmark/link to them still resolves somewhere
    // sensible instead of 404ing.
    public function create(Request $request)
    {
        return redirect()->route('hr-center.rosters.index');
    }

    public function store(Request $request)
    {
        $data = $this->validateRoster($request);

        if (!empty($data['auto_roster'])) {
            $this->applyAutoRoster($data);
        } else {
            // updateOrCreate, not create(): hr_shift_roster_employees has a unique
            // (employee_id, roster_date) constraint — a plain create() throws an uncaught
            // DB error if this employee already has a roster row for that date.
            HrShiftRosterEmployee::updateOrCreate(
                ['employee_id' => $data['employee_id'] ?? null, 'roster_date' => $data['date']],
                ['shift_id' => $data['shift_id'], 'remarks' => $data['remarks'] ?? null]
            );
        }

        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster assigned successfully.');
    }

    public function edit($id)
    {
        return redirect()->route('hr-center.rosters.index');
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateRoster($request);

        if (!empty($data['auto_roster'])) {
            $this->applyAutoRoster($data);
        } else {
            HrShiftRosterEmployee::findOrFail($id)->update([
                'employee_id' => $data['employee_id'] ?? null,
                'shift_id' => $data['shift_id'],
                'roster_date' => $data['date'],
                'remarks' => $data['remarks'] ?? null,
            ]);
        }

        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster updated successfully.');
    }

    public function rulesDestroy($id)
    {
        HrEmployeeShiftRule::findOrFail($id)->delete();
        return redirect()->route('hr-center.rosters.index')->with('success', 'Auto Roster rule deleted.');
    }

    private function validateRoster(Request $request): array
    {
        return $request->validate([
            'employee_id' => 'nullable|exists:hr_employees,id|required_if:auto_roster,1',
            'shift_id' => 'required',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'auto_roster' => 'nullable|boolean',
            'alt_shift_ids' => 'required_if:auto_roster,1|nullable|array|min:1',
            'alt_shift_ids.*' => 'required|exists:hr_shifts,id',
            'day_of_week' => 'required_if:auto_roster,1|nullable|integer|between:0,6',
        ]);
    }

    /**
     * Auto Roster is a standing per-employee rule (one row per employee, upserted
     * on employee_id). "Shift" is the rule's baseline (primary) shift and "Date"
     * is the anchor from which the chosen day-of-week rotates weekly through the
     * ordered alt_shift_ids list and then primary last, wrapping back to the
     * start — see HrEmployee::resolveShiftForDate(). Deactivating/deleting a rule
     * is a separate, explicit action (rulesDestroy()), not a side effect of this.
     */
    private function applyAutoRoster(array $data): void
    {
        $rule = HrEmployeeShiftRule::updateOrCreate(
            ['employee_id' => $data['employee_id']],
            [
                'primary_shift_id' => $data['shift_id'],
                'day_of_week' => $data['day_of_week'],
                'anchor_date' => $data['date'],
                'is_active' => true,
            ]
        );

        // Full resync rather than diffing: rosters are edited rarely enough that a
        // delete-and-reinsert is simpler and guarantees sort_order always matches
        // the submitted order exactly.
        $rule->alternateShifts()->delete();
        foreach (array_values($data['alt_shift_ids']) as $index => $shiftId) {
            $rule->alternateShifts()->create(['shift_id' => $shiftId, 'sort_order' => $index]);
        }
    }

    /**
     * Bulk-assign page: filter employees by department/section/sub-section, pick
     * several, and assign one shift/date to all of them at once.
     */
    public function assign(Request $request)
    {
        $masterData = \ME\Hr\Services\HrOptionsService::getOptions();
        $departments = $masterData['departments'];
        $sections = $masterData['sections'];
        $subSections = $masterData['subSections'];
        $shifts = $masterData['shifts'];

        $employeesQuery = HrEmployee::query();
        if ($request->filled('department_id')) {
            $employeesQuery->where('department_id', $request->department_id);
        }
        if ($request->filled('section_id')) {
            $employeesQuery->where('section_id', $request->section_id);
        }
        if ($request->filled('sub_section_id')) {
            $employeesQuery->where('sub_section_id', $request->sub_section_id);
        }
        $employees = $employeesQuery->naturalOrderById()->get();

        // If exactly one sub-section is filtered and it has a default Roster Shift,
        // offer it as the pre-selected shift (still editable) on the assign form.
        $defaultShiftId = null;
        if ($request->filled('sub_section_id')) {
            $defaultShiftId = $subSections->firstWhere('id', (int) $request->sub_section_id)?->roster_shift_id;
        }

        return view('hr::rosters.assign', compact(
            'departments', 'sections', 'subSections', 'shifts', 'employees', 'defaultShiftId'
        ));
    }

    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:hr_employees,id',
            'shift_id' => 'required',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        foreach ($data['employee_ids'] as $employeeId) {
            HrShiftRosterEmployee::updateOrCreate(
                ['employee_id' => $employeeId, 'roster_date' => $data['date']],
                ['shift_id' => $data['shift_id'], 'remarks' => $data['remarks'] ?? null]
            );
        }

        $count = count($data['employee_ids']);
        return redirect()->route('hr-center.rosters.index')
            ->with('success', "Roster assigned to {$count} employee(s).");
    }

    public function destroy($id)
    {
        $roster = HrShiftRosterEmployee::findOrFail($id);
        $roster->delete();
        return redirect()->route('hr-center.rosters.index')->with('success', 'Roster deleted.');
    }
}
