<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Routing\Controller;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrDepartment;

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

            $totalEmployees = HrEmployee::query()->tap($activeScope)->count();

            $presentToday = HrAttendance::query()
                ->whereDate('date', $today)
                ->distinct('employee_id')
                ->count('employee_id');

            $absentToday = max(0, $totalEmployees - $presentToday);

            $newThisMonth = HrEmployee::query()
                ->whereYear('join_date', now()->year)
                ->whereMonth('join_date', now()->month)
                ->count();

            // Last 30 days daily attendance
            $last30 = collect();
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $last30->push([
                    'date'    => $date,
                    'label'   => now()->subDays($i)->format('d M'),
                    'present' => HrAttendance::query()->whereDate('date', $date)->distinct('employee_id')->count('employee_id'),
                ]);
            }

            // Department breakdown
            $departments = HrDepartment::query()
                ->where('status', 'active')
                ->withCount(['employees' => fn ($q) => $q
                    ->whereNull('exited_at')
                    ->where(fn ($q2) => $q2->whereNull('employment_status')->orWhereIn('employment_status', ['', 'regular', 'active']))
                ])
                ->having('employees_count', '>', 0)
                ->orderByDesc('employees_count')
                ->limit(8)
                ->get(['id', 'name']);

            // Employment status breakdown
            $statusBreakdown = HrEmployee::query()
                ->selectRaw("IFNULL(NULLIF(employment_status, ''), 'regular') as status, COUNT(*) as total")
                ->whereNull('exited_at')
                ->groupBy('employment_status')
                ->pluck('total', 'status');

            // Recent joiners
            $recentJoiners = HrEmployee::query()
                ->orderByDesc('join_date')
                ->limit(5)
                ->get(['id', 'name', 'employee_id', 'join_date', 'department_id', 'designation_id']);

            // Monthly join trend (last 6 months)
            $joinTrend = collect();
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $joinTrend->push([
                    'label' => $month->format('M Y'),
                    'count' => HrEmployee::query()
                        ->whereYear('join_date', $month->year)
                        ->whereMonth('join_date', $month->month)
                        ->count(),
                ]);
            }

            return compact(
                'totalEmployees', 'presentToday', 'absentToday', 'newThisMonth',
                'last30', 'departments', 'statusBreakdown', 'recentJoiners', 'joinTrend'
            );
        } catch (\Throwable) {
            return [
                'totalEmployees'  => 0,
                'presentToday'    => 0,
                'absentToday'     => 0,
                'newThisMonth'    => 0,
                'last30'          => collect(),
                'departments'     => collect(),
                'statusBreakdown' => collect(),
                'recentJoiners'   => collect(),
                'joinTrend'       => collect(),
            ];
        }
    }
}
