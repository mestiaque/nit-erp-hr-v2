<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use ME\Hr\Models\HrAdvanceApplication;
use ME\Hr\Models\HrEmployee;

/**
 * Salary advance application letters, managed from the employee's Earnings & Deductions page.
 * Saving only records the letter for printing - it affects no salary or Earnings & Deductions figure.
 */
class HrAdvanceApplicationController extends Controller
{
    public function store(Request $request, HrEmployee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'application_date' => 'required|date',
            'amount'           => 'required|numeric|min:1',
            'reason'           => 'nullable|string|max:255',
            'deduction_month'  => 'nullable|date_format:Y-m',
        ]);

        $application = HrAdvanceApplication::create([
            'employee_id'      => $employee->id,
            'application_date' => $validated['application_date'],
            'amount'           => $validated['amount'],
            'reason'           => $validated['reason'] ?: HrAdvanceApplication::DEFAULT_REASON,
            'deduction_month'  => filled($validated['deduction_month'] ?? null)
                ? Carbon::createFromFormat('Y-m', $validated['deduction_month'])->startOfMonth()
                : null,
        ]);

        return redirect()->route('hr-center.employees.earnings.page', $employee->id)
            ->with('success', 'Advance application saved.')
            ->with('printed_advance_application_id', $application->id);
    }

    public function print(HrEmployee $employee, int $application)
    {
        $application = HrAdvanceApplication::with(['employee.department', 'employee.designation'])
            ->where('employee_id', $employee->id)
            ->findOrFail($application);

        return view('hr::employees.print.advance-application', compact('application'));
    }

    public function destroy(HrEmployee $employee, int $application): RedirectResponse
    {
        HrAdvanceApplication::where('employee_id', $employee->id)->findOrFail($application)->delete();

        return redirect()->route('hr-center.employees.earnings.page', $employee->id)
            ->with('success', 'Advance application deleted.');
    }
}
