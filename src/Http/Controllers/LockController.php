<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeSalaryIncrement;
use ME\Hr\Models\HrEmployeeSalarySnapshot;
use ME\Hr\Models\HrLock;
use ME\Hr\Services\SalaryReportService;

class LockController extends Controller
{
    private const MODULES = ['increment', 'attendance', 'salary'];

    public function index(Request $request)
    {
        $module = in_array($request->input('module'), self::MODULES, true) ? $request->input('module') : 'increment';
        $year   = (int) $request->input('year', now()->year);
        $month  = (int) $request->input('month', now()->month);

        $departments = HrDepartment::orderBy('name')->get(['id', 'name']);

        $rows = $departments->map(function ($dept) use ($module, $year, $month) {
            $lock = HrLock::where('module', $module)
                ->where('lock_year', $year)
                ->where('lock_month', $month)
                ->where('department_id', $dept->id)
                ->first();

            return [
                'department' => $dept,
                'is_locked'  => (bool) ($lock->is_locked ?? false),
                'locked_at'  => $lock->locked_at ?? null,
            ];
        });

        // Whole-factory (no department) lock, applies to employees with no department too.
        $wholeLock = HrLock::where('module', $module)
            ->where('lock_year', $year)
            ->where('lock_month', $month)
            ->whereNull('department_id')
            ->first();

        return view('hr::locks.index', [
            'module'      => $module,
            'year'        => $year,
            'month'       => $month,
            'rows'        => $rows,
            'wholeLocked' => (bool) ($wholeLock->is_locked ?? false),
            'wholeLockAt' => $wholeLock->locked_at ?? null,
        ]);
    }

    public function toggle(Request $request)
    {
        $payload = $request->validate([
            'module'        => 'required|in:' . implode(',', self::MODULES),
            'year'          => 'required|integer',
            'month'         => 'required|integer|between:1,12',
            'department_id' => 'nullable|integer',
            'action'        => 'required|in:lock,unlock',
        ]);

        $module       = $payload['module'];
        $year         = (int) $payload['year'];
        $month        = (int) $payload['month'];
        $departmentId = $payload['department_id'] ?? null;
        $locking      = $payload['action'] === 'lock';

        $employeeQuery = HrEmployee::query();
        if ($departmentId) {
            $employeeQuery->where('department_id', $departmentId);
        }
        $employeeIds = $employeeQuery->pluck('id');

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();

        if ($module === 'increment') {
            $query = HrEmployeeSalaryIncrement::whereIn('employee_id', $employeeIds)
                ->whereBetween('increment_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            $query->update($locking
                ? ['is_locked' => true, 'locked_at' => now(), 'locked_by' => Auth::id()]
                : ['is_locked' => false, 'locked_at' => null, 'locked_by' => null]);
        } elseif ($module === 'attendance') {
            $query = HrAttendance::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
            $query->update($locking
                ? ['is_locked' => true, 'locked_at' => now(), 'locked_by' => Auth::id()]
                : ['is_locked' => false, 'locked_at' => null, 'locked_by' => null]);
        } else { // salary
            if ($locking) {
                // Compute live salary for each employee (while still unlocked) and snapshot it,
                // then flip the lock — otherwise the read-side lock check in
                // SalaryReportService would try to read a snapshot that doesn't exist yet.
                $employees = $employeeQuery->get();
                foreach ($employees as $emp) {
                    $data = SalaryReportService::getEmployeeSalaryData(
                        $emp,
                        $periodStart->toDateString(),
                        $periodEnd->toDateString()
                    );

                    HrEmployeeSalarySnapshot::updateOrCreate(
                        [
                            'employee_id' => $emp->id,
                            'lock_year'   => $year,
                            'lock_month'  => $month,
                        ],
                        [
                            'department_id'  => $emp->department_id,
                            'gross'          => $data['gross'] ?? 0,
                            'basic'          => $data['basic'] ?? 0,
                            'house_rent'     => $data['house_rent'] ?? 0,
                            'medical'        => $data['medical'] ?? 0,
                            'transport'      => $data['transport'] ?? 0,
                            'food_allow'     => $data['food_allow'] ?? 0,
                            'total_earn'     => $data['total_earn'] ?? 0,
                            'total_deduct'   => $data['total_deduct'] ?? 0,
                            'net'            => $data['net'] ?? 0,
                            'ot'             => $data['ot'] ?? 0,
                            'ot_hours'       => $data['ot_hours'] ?? 0,
                            'ot_rate'        => $data['ot_rate'] ?? 0,
                            'present'        => $data['present'] ?? 0,
                            'absent'         => $data['absent'] ?? 0,
                            'att_bonus'      => $data['att_bonus'] ?? 0,
                            'deduct_absent'  => $data['deduct_absent'] ?? 0,
                            'deduct_other'   => $data['deduct_other'] ?? 0,
                            'loan'           => $data['loan'] ?? 0,
                            'tax'            => $data['tax'] ?? 0,
                            'stamp'          => $data['stamp'] ?? 0,
                            'extra_facility' => $data['extra_facility'] ?? 0,
                            'raw_data'       => $data,
                            'locked_at'      => now(),
                            'locked_by'      => Auth::id(),
                        ]
                    );
                }
            }
            // Snapshot rows are kept on unlock too (audit trail) — only the hr_locks
            // flag below controls whether SalaryReportService reads them or recomputes live.
        }

        HrLock::updateOrCreate(
            [
                'module'        => $module,
                'lock_year'     => $year,
                'lock_month'    => $month,
                'department_id' => $departmentId,
            ],
            $locking
                ? ['is_locked' => true, 'locked_at' => now(), 'locked_by' => Auth::id()]
                : ['is_locked' => false, 'unlocked_at' => now(), 'unlocked_by' => Auth::id()]
        );

        return redirect()
            ->route('hr-center.locks.index', ['module' => $module, 'year' => $year, 'month' => $month])
            ->with('success', $locking ? 'Period locked.' : 'Period unlocked.');
    }
}
