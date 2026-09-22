<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrDisciplinaryNotice;
use ME\Hr\Models\HrEmployee;

class HrDisciplinaryNoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = HrDisciplinaryNotice::with('employee')->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($eq) use ($search) {
                $eq->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $notices = $query->paginate(20)->appends($request->query());

        return view('hr::disciplinary-notices.index', [
            'notices'   => $notices,
            'request'   => $request,
            'employees' => HrEmployee::naturalOrderById()->get(['id', 'employee_id', 'name']),
        ]);
    }

    public function create()
    {
        return view('hr::disciplinary-notices.form', [
            'notice'    => new HrDisciplinaryNotice(),
            'employees' => HrEmployee::naturalOrderById()->get(['id', 'employee_id', 'name']),
            'noticeLabels' => HrDisciplinaryNotice::NOTICE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $notice = HrDisciplinaryNotice::create($this->validated($request));

        return redirect()->route('hr-center.disciplinary-notices.index')
            ->with('success', 'Notice saved successfully.')
            ->with('printed_notice_id', $notice->id);
    }

    public function edit(int $id)
    {
        $notice = HrDisciplinaryNotice::findOrFail($id);

        return view('hr::disciplinary-notices.form', [
            'notice'    => $notice,
            'employees' => HrEmployee::naturalOrderById()->get(['id', 'employee_id', 'name']),
            'noticeLabels' => HrDisciplinaryNotice::NOTICE_LABELS,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $notice = HrDisciplinaryNotice::findOrFail($id);
        $notice->update($this->validated($request));

        return redirect()->route('hr-center.disciplinary-notices.index')
            ->with('success', 'Notice updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        HrDisciplinaryNotice::findOrFail($id)->delete();

        return redirect()->route('hr-center.disciplinary-notices.index')
            ->with('success', 'Notice deleted.');
    }

    public function print(int $id)
    {
        $notice = HrDisciplinaryNotice::with(['employee.department', 'employee.designation'])
            ->findOrFail($id);

        return view('hr::disciplinary-notices.print', [
            'notice' => $notice,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'employee_id'           => 'required|exists:hr_employees,id',
            'notice_no'             => 'required|integer|min:1|max:3',
            'notice_date'           => 'required|date',
            'incident_date'         => 'nullable|date',
            'incident_description'  => 'nullable|string|max:2000',
            'deduction_days'        => 'required|integer|min:1',
            'memo_no'               => 'nullable|string|max:100',
        ]);
    }
}
