<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeLeave;
use ME\Hr\Models\HrEmployeeSalaryInfo;
use ME\Hr\Models\HrEmployeeSeparation;
use ME\Hr\Models\HrHoliday;
use ME\Hr\Models\HrRequisition;

class HrDashboardController extends Controller
{
    public function index()
    {
        return view('hr::dashboard');
    }

    public static function stats(): array
    {
        try {
            $today = now()->toDateString();

            $activeScope = fn ($q) => $q
                ->whereNull('exited_at')
                ->where(fn ($q2) => $q2->whereNull('employment_status')
                    ->orWhereIn('employment_status', ['', 'regular', 'active']));

            // ── Headline numbers ──────────────────────────────────────────────
            $totalEmployees = HrEmployee::query()->tap($activeScope)->count();

            $presentToday = HrAttendance::whereDate('date', $today)
                ->whereIn('status', ['Present', 'Late'])
                ->distinct('employee_id')->count('employee_id');

            $lateToday = HrAttendance::whereDate('date', $today)
                ->where('status', 'Late')
                ->distinct('employee_id')->count('employee_id');

            $absentToday = max(0, $totalEmployees - $presentToday);

            $newThisMonth = HrEmployee::whereYear('join_date', now()->year)
                ->whereMonth('join_date', now()->month)->count();

            // Recruited this year
            $recruitedThisYear = HrEmployee::whereYear('join_date', now()->year)->count();

            // Terminated this year (separations with effective_date this year)
            $terminatedThisYear = HrEmployeeSeparation::whereYear('effective_date', now()->year)->count();

            // ── Last 30 days daily Present / Late / Absent ────────────────────
            $last30 = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date  = now()->subDays($i)->toDateString();
                $label = now()->subDays($i)->format('d M');

                $rows = HrAttendance::whereDate('date', $date)
                    ->selectRaw("status, COUNT(DISTINCT employee_id) as cnt")
                    ->groupBy('status')
                    ->pluck('cnt', 'status');

                $present = ($rows['Present'] ?? 0);
                $late    = ($rows['Late'] ?? 0);
                $absent  = max(0, $totalEmployees - $present - $late);

                $last30->push(compact('date', 'label', 'present', 'late', 'absent'));
            }

            // ── Monthly recruitment vs termination (last 6 months) ────────────
            $monthlyTrend = collect();
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthlyTrend->push([
                    'label'      => $month->format('M Y'),
                    'recruited'  => HrEmployee::whereYear('join_date', $month->year)
                        ->whereMonth('join_date', $month->month)->count(),
                    'terminated' => HrEmployeeSeparation::whereYear('effective_date', $month->year)
                        ->whereMonth('effective_date', $month->month)->count(),
                ]);
            }

            // ── Department breakdown ─────────────────────────────────────────
            $departments = HrDepartment::where('status', 'active')
                ->withCount(['employees' => fn ($q) => $q
                    ->whereNull('exited_at')
                    ->where(fn ($q2) => $q2->whereNull('employment_status')
                        ->orWhereIn('employment_status', ['', 'regular', 'active']))
                ])
                ->having('employees_count', '>', 0)
                ->orderByDesc('employees_count')
                ->limit(8)
                ->get(['id', 'name']);

            // ── Recent joiners ────────────────────────────────────────────────
            $recentJoiners = HrEmployee::orderByDesc('join_date')
                ->limit(6)
                ->get(['id', 'name', 'employee_id', 'join_date', 'department_id', 'designation_id']);

            // ── Recent separations ────────────────────────────────────────────
            $recentSeparations = HrEmployeeSeparation::with('employee')
                ->orderByDesc('effective_date')
                ->limit(5)
                ->get();

            $joinTrend = $monthlyTrend->map(fn ($m) => [
                'label' => $m['label'],
                'count' => $m['recruited'],
            ]);

            // ── Employees on leave today ─────────────────────────────────────────
            $onLeaveToday = HrEmployeeLeave::with(['employee', 'leaveType'])
                ->where('status', 'approved')
                ->whereDate('leave_from', '<=', $today)
                ->whereDate('leave_to', '>=', $today)
                ->limit(6)
                ->get();

            // ── Upcoming birthdays (next 30 days) ───────────────────────────────
            $now = now()->startOfDay();
            $upcomingBirthdays = HrEmployee::query()
                ->join('hr_employee_basic_infos as bi', 'bi.employee_id', '=', 'hr_employees.id')
                ->whereNotNull('bi.birth_date')
                ->tap($activeScope)
                ->get(['hr_employees.id', 'hr_employees.name', 'hr_employees.employee_id', 'bi.birth_date'])
                ->map(function ($emp) use ($now) {
                    $next = Carbon::parse($emp->birth_date)->year($now->year);
                    if ($next->lt($now)) {
                        $next->addYear();
                    }
                    $emp->next_birthday = $next;
                    $emp->days_left     = $now->diffInDays($next);
                    return $emp;
                })
                ->filter(fn ($emp) => $emp->days_left >= 0 && $emp->days_left <= 30)
                ->sortBy('days_left')
                ->take(6)
                ->values();

            // ── Upcoming holidays ────────────────────────────────────────────────
            $upcomingHolidays = HrHoliday::where('status', 1)
                ->where('to_date', '>=', $today)
                ->orderBy('from_date')
                ->limit(5)
                ->get(['id', 'purpose', 'type', 'from_date', 'to_date']);

            // ── Payroll summary (active employees) ──────────────────────────────
            $activeEmployeeIds = HrEmployee::query()->tap($activeScope)->pluck('id');
            $payrollTotal = HrEmployeeSalaryInfo::where('status', 1)
                ->whereIn('employee_id', $activeEmployeeIds)
                ->sum('gross_salary');
            $payrollAvg = $totalEmployees > 0 ? round($payrollTotal / $totalEmployees) : 0;

            // ── Leave summary ────────────────────────────────────────────────────
            $leaveSummary = [
                'pending'     => HrEmployeeLeave::where('status', 'pending')->count(),
                'approved'    => HrEmployeeLeave::where('status', 'approved')
                    ->whereMonth('leave_from', now()->month)
                    ->whereYear('leave_from', now()->year)->count(),
                'onLeaveToday'=> HrEmployeeLeave::where('status', 'approved')
                    ->whereDate('leave_from', '<=', $today)
                    ->whereDate('leave_to', '>=', $today)
                    ->count(),
            ];

            return compact(
                'totalEmployees', 'presentToday', 'lateToday', 'absentToday',
                'newThisMonth', 'recruitedThisYear', 'terminatedThisYear',
                'last30', 'monthlyTrend', 'joinTrend', 'departments',
                'recentJoiners', 'recentSeparations', 'onLeaveToday',
                'upcomingBirthdays', 'upcomingHolidays', 'payrollTotal',
                'payrollAvg', 'leaveSummary'
            );

        } catch (\Throwable $e) {
            return [
                'totalEmployees'    => 0,
                'presentToday'      => 0,
                'lateToday'         => 0,
                'absentToday'       => 0,
                'newThisMonth'      => 0,
                'recruitedThisYear' => 0,
                'terminatedThisYear'=> 0,
                'last30'            => collect(),
                'monthlyTrend'      => collect(),
                'joinTrend'         => collect(),
                'departments'       => collect(),
                'recentJoiners'     => collect(),
                'recentSeparations' => collect(),
                'onLeaveToday'      => collect(),
                'upcomingBirthdays' => collect(),
                'upcomingHolidays'  => collect(),
                'payrollTotal'      => 0,
                'payrollAvg'        => 0,
                'leaveSummary'      => ['pending' => 0, 'approved' => 0, 'onLeaveToday' => 0],
            ];
        }
    }
}
