<?php

namespace ME\Hr\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Routing\Controller;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeSeparation;
use ME\Hr\Models\HrRequisition;

class HrDashboardController extends Controller
{
    public function index()
    {
        $entities    = config('hr.entities', []);
        $legacyLinks = config('hr.legacy_links', []);
        $reports     = config('hr.reports', []);
        $stats       = $this->stats();

        return view('hr::dashboard', compact('entities', 'legacyLinks', 'reports', 'stats'));
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

            return compact(
                'totalEmployees', 'presentToday', 'lateToday', 'absentToday',
                'newThisMonth', 'recruitedThisYear', 'terminatedThisYear',
                'last30', 'monthlyTrend', 'departments',
                'recentJoiners', 'recentSeparations'
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
                'departments'       => collect(),
                'recentJoiners'     => collect(),
                'recentSeparations' => collect(),
            ];
        }
    }
}
