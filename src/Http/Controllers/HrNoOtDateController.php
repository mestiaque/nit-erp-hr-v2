<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use ME\Hr\Models\HrLock;
use ME\Hr\Models\HrNoOtDate;

/** No-OT Dates: days on which nobody earns OT — job card, OT sheet and salary all count 0 OT for them. */
class HrNoOtDateController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(hasChildPermission('hr_no_ot_date', 'list'), 403);

        $dates = HrNoOtDate::query()
            ->when($request->filled('year'), fn ($q) => $q->whereYear('date', $request->year))
            ->orderByDesc('date')
            ->paginate(30)
            ->appends($request->query());

        return view('hr::no-ot-dates.index', compact('dates', 'request'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(hasChildPermission('hr_no_ot_date', 'add'), 403);

        $data = $request->validate([
            'date'   => 'required|date|unique:hr_no_ot_dates,date',
            'reason' => 'nullable|string|max:255',
        ], ['date.unique' => 'This date is already a No-OT date.']);

        $this->guardSalaryLock($data['date']);

        HrNoOtDate::create($data);

        return redirect()->route('hr-center.no-ot-dates.index')
            ->with('success', 'No-OT date ' . Carbon::parse($data['date'])->format('d M Y') . ' added — OT for that day is now 0 in every report and salary.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(hasChildPermission('hr_no_ot_date', 'delete'), 403);

        $row = HrNoOtDate::findOrFail($id);
        $this->guardSalaryLock($row->date->toDateString());
        $row->delete();

        return redirect()->route('hr-center.no-ot-dates.index')
            ->with('success', 'No-OT date ' . $row->date->format('d M Y') . ' removed — OT for that day is counted again.');
    }

    /**
     * A locked salary month is read from its saved snapshot, so changing a No-OT date inside it
     * would not change that salary — and the screens would disagree. Unlock the month first.
     */
    private function guardSalaryLock(string $date): void
    {
        $day = Carbon::parse($date);
        $locked = HrLock::query()
            ->where('module', 'salary')
            ->where('lock_year', $day->year)
            ->where('lock_month', $day->month)
            ->where('is_locked', true)
            ->exists();

        if ($locked) {
            throw ValidationException::withMessages([
                'date' => 'Salary for ' . $day->format('F Y') . ' is locked. Unlock that month first, then change its No-OT dates.',
            ]);
        }
    }
}
