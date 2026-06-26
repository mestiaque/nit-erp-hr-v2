<?php

namespace ME\Hr\Http\Controllers;

use ME\Hr\Models\HrEmployee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrBonusPolicy;
use ME\Hr\Models\HrBonusTitle;
use ME\Hr\Models\HrClassification;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrDesignation;
use ME\Hr\Models\HrEmployeeSalaryIncrement;
use ME\Hr\Models\HrFloorLine;
use ME\Hr\Models\HrHoliday;
use ME\Hr\Models\HrProductionBonus;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrSubSection;
use ME\Hr\Models\HrSex;
use ME\Hr\Models\HrWorkingPlace;




class HrReportController extends Controller
{

    public function proJobCard(Request $request)
    {
        // dd('This report is under development. Please check back later.');
        $reportKey = 'pro-job-card';
        $reportTitle = 'Pro. Job Card';
        $options = $this->employeeReportOptions();

        $columns = $rows = [];
        $showTable = false;
        // Show table if any filter is applied or print requested
        if ($request->hasAny([
            'employee_ids', 'from', 'to', 'classification', 'department', 'section', 'sub_section',
            'shift', 'working_place', 'line_number', 'salary_type', 'employee_status', 'language', 'report_type', 'print'])
        ) {
            [$columns, $rows] = $this->productionJobCardReport();
            $showTable = true;
        }

        if ($request->boolean('print')) {
            return view('hr::reports.pro-job-card-print', compact('reportKey', 'reportTitle', 'options', 'request', 'columns', 'rows'));
        }

        return view('hr::reports.pro-job-card', compact('reportKey', 'reportTitle', 'options', 'request', 'columns', 'rows', 'showTable'));
    }

    public function lockMonthlyIncrement(Request $request)
    {
        $payload = $request->validate([
            'effective_date' => 'required|date',
            'increment_percent' => 'required|numeric|min:0|max:100',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'classification' => 'nullable|integer',
            'department' => 'nullable|integer',
            'section' => 'nullable|integer',
            'sub_section' => 'nullable|integer',
            'working_place' => 'nullable|integer',
            'salary_type' => 'nullable|string|max:50',
            'designation' => 'nullable|integer',
            'line_number' => 'nullable|integer',
        ]);

        $filterRequest = new Request($payload);
        $employees = $this->employeeReportQuery($filterRequest)
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get();

        $options = $this->employeeReportOptions();
        $incrementMap = $this->latestIncrements($employees);
        $data = $this->monthlyIncrementData(
            $employees,
            $options,
            $filterRequest,
            $incrementMap,
            (float) $payload['increment_percent'],
            (string) $payload['effective_date'],
            false
        );

        $rows = collect($data['rows'] ?? []);
        if ($rows->isEmpty()) {
            return back()->with('error', 'No employee found for increment lock.');
        }

        DB::transaction(function () use ($rows, $payload) {
            foreach ($rows as $row) {
                $employee = HrEmployee::query()
                    
                    ->find(data_get($row, 'employee_row_id'));
                if (!$employee) {
                    continue;
                }

                $this->upsertIncrementRecord(
                    $employee,
                    (string) $payload['effective_date'],
                    (float) data_get($row, 'gross_salary', 0),
                    (float) data_get($row, 'inc_value', 0),
                    (float) data_get($row, 'inc_percent', 0),
                    (float) data_get($row, 'final_gross', 0)
                );
            }
        });

        return back()->with('success', 'Increment locked successfully for selected employees.');
    }

    public function index()
    {
        $reports = config('hr.reports', []);

        return view('hr::reports.index', compact('reports'));
    }

    public function attendanceWithOt(Request $request)
    {
        return $this->attendanceWithOtReportScreen($request, 'attendance-with-ot');
    }

    public function monthlyLateReport(Request $request)
    {
        return $this->monthlyLateReportScreen($request, 'monthly-late-report');
    }

    public function show(string $report, Request $request)
    {
        abort_unless(array_key_exists($report, config('hr.reports', [])), 404);

        if ($report === 'employee') {
            return $this->employeeReportScreen($request, $report);
        }

        if ($report === 'monthly') {
            return $this->monthlyReportScreen($request, $report);
        }

        if ($report === 'personal-file') {
            return $this->personalFileReportScreen($request, $report);
        }

        if ($report === 'job-card-report') {
            return $this->jobCardReportScreen($request, $report);
        }

        if ($report === 'attendance-report') {
            return $this->attendanceReportScreen($request, $report);
        }

        if ($report === 'monthly-late-report') {
            return $this->monthlyLateReportScreen($request, $report);
        }

        if ($report === 'daily-manpower-report') {
            return $this->dailyManpowerReportScreen($request, $report);
        }

        if ($report === 'meal-report') {
            return $this->mealReportScreen($request, $report);
        }

        if ($report === 'bonus-sheet') {
            return $this->bonusSheetScreen($request, $report);
        }

        if ($report === 'salary-report') {
            return $this->salaryReportScreen($request, $report);
        }

        if( $report === 'pay-slip') {
            return $this->paySlipReportScreen($request, $report);
        }

        [$columns, $rows] = match ($report) {
            'employee' => $this->employeeReport(),
            'monthly' => $this->monthlyReport(),
            'machine-id' => $this->machineIdReport(),
            'job-card' => $this->jobCardReport(),
            'personal-file' => $this->personalFileReport(),
            'attendance' => $this->attendanceReport(),
            'tiffin-night-dinner' => $this->mealAllowanceReport(),
            'pro-job-card' => $this->productionJobCardReport(),
            'bonus-salary-fixed' => $this->bonusSalaryFixedReport(),
            'bonus-salary-production' => $this->bonusSalaryProductionReport(),
            'salary-fixed' => $this->salaryFixedReport(),
            'salary-production' => $this->salaryProductionReport(),
            'salary-summary' => $this->salarySummaryReport(),
            default => [[], collect()],
        };

        return view('hr::reports.show', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'columns' => $columns,
            'rows' => $rows,
            'request' => $request,
        ]);
    }

    private function monthlyReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $reportTypes = [
            'recruitment' => 'Recruitment',
            'migration' => 'Migration',
            'long-absent' => 'Long Absent',
            'monthly-late-report' => 'Monthly Late Report',
            'increment' => 'Increment',
            'increment-summary' => 'Increment Report',
        ];

        $reportType = (string) $request->input('report_type', 'recruitment');
        if (!array_key_exists($reportType, $reportTypes)) {
            $reportType = 'recruitment';
        }

        $incrementPercent = (float) $request->input('increment_percent', 0);
        $effectiveDate = $request->input('effective_date');

        $employees = $this->employeeReportQuery($request)
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get();

        $incrementMap = $this->latestIncrements($employees);

        $data = match ($reportType) {
            'recruitment' => $this->monthlyRecruitmentData($employees, $options, $request),
            'migration' => $this->monthlyMigrationData($employees, $options, $request),
            'long-absent' => $this->monthlyLongAbsentData($employees, $options, $request),
            'increment' => $this->monthlyIncrementData($employees, $options, $request, $incrementMap, $incrementPercent, $effectiveDate, false),
            'increment-summary' => $this->monthlyIncrementData($employees, $options, $request, $incrementMap, $incrementPercent, $effectiveDate, true),
            default => ['rows' => collect()],
        };

        if ($request->boolean('print')) {
            if ($reportType === 'monthly-late-report') {
                return $this->monthlyLateReportScreen($request, 'monthly-late-report');
            }

            return view('hr::reports.monthly-print', [
                'reportKey' => $report,
                'reportTitle' => config('hr.reports.' . $report),
                'reportType' => $reportType,
                'reportTypeLabel' => $reportTypes[$reportType],
                'request' => $request,
                'options' => $options,
                'data' => $data,
                'incrementPercent' => $incrementPercent,
                'effectiveDate' => $effectiveDate,
            ]);
        }

        return view('hr::reports.monthly', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'request' => $request,
            'options' => $options,
            'reportTypes' => $reportTypes,
            'reportType' => $reportType,
            'incrementPercent' => $incrementPercent,
            'effectiveDate' => $effectiveDate,
        ]);
    }

    private function monthlyRecruitmentData($employees, array $options, Request $request): array
    {
        $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');
        $gradeMap = collect();

        $rows = $employees
            ->filter(function (HrEmployee $employee) use ($request) {
                if (blank($employee->join_date)) {
                    return false;
                }

                $joinDate = $employee->join_date instanceof \Carbon\Carbon
                    ? $employee->join_date
                    : \Carbon\Carbon::parse($employee->join_date);

                if ($request->filled('from') && $joinDate->lt(\Carbon\Carbon::parse($request->from)->startOfDay())) {
                    return false;
                }
                if ($request->filled('to') && $joinDate->gt(\Carbon\Carbon::parse($request->to)->endOfDay())) {
                    return false;
                }

                return true;
            })
            ->values();

        $detailRows = $rows->map(function (HrEmployee $employee) use ($classificationMap, $departmentMap, $sectionMap, $designationMap, $gradeMap) {
            return [
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'department' => $departmentMap->get($employee->department_id, 'N/A'),
                'section' => $sectionMap->get($employee->section_id, 'N/A'),
                'join_date' => optional($employee->join_date)->format('d-M-Y'),
                'contact' => $employee->mobile,
                'classification' => $classificationMap->get($employee->classification_id, 'N/A'),
                'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                'grade' => $gradeMap->get($employee->grade_lavel, 'N/A'),
                'gross_salary' => (float) ($employee->gross_salary ?? 0),
            ];
        })->values();

        $summaryRows = $rows
            ->groupBy(function (HrEmployee $employee) {
                return implode('|', [
                    $employee->department_id,
                    $employee->section_id,
                    $employee->classification_id,
                    $employee->designation_id,
                ]);
            })
            ->map(function ($group) use ($departmentMap, $sectionMap, $classificationMap, $designationMap) {
                /** @var HrEmployee $first */
                $first = $group->first();

                return [
                    'department' => $departmentMap->get($first->department_id, 'N/A'),
                    'section' => $sectionMap->get($first->section_id, 'N/A'),
                    'classification' => $classificationMap->get($first->classification_id, 'N/A'),
                    'designation' => $designationMap->get($first->designation_id, 'N/A'),
                    'total_employees' => $group->count(),
                    'total_gross_salary' => $group->sum(fn (HrEmployee $employee) => (float) ($employee->gross_salary ?? 0)),
                ];
            })
            ->values();

        return [
            'rows' => $detailRows,
            'summary_rows' => $summaryRows,
        ];
    }

    private function monthlyMigrationData($employees, array $options, Request $request): array
    {
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');

        $rows = $employees
            ->filter(function (HrEmployee $employee) use ($request) {
                $status = strtolower((string) ($employee->employment_status ?? ''));
                if (!in_array($status, ['transfer', 'lefty', 'left', 'resign', 'resigned'], true)) {
                    return false;
                }

                $migrationDate = $employee->exited_at;
                if (blank($migrationDate)) {
                    return !$request->filled('from') && !$request->filled('to');
                }

                $date = \Carbon\Carbon::parse($migrationDate);
                if ($request->filled('from') && $date->lt(\Carbon\Carbon::parse($request->from)->startOfDay())) {
                    return false;
                }
                if ($request->filled('to') && $date->gt(\Carbon\Carbon::parse($request->to)->endOfDay())) {
                    return false;
                }

                return true;
            })
            ->map(function (HrEmployee $employee) use ($departmentMap, $sectionMap, $designationMap) {
                $other = is_array($employee->other_information) ? $employee->other_information : [];
                $status = (string) ($employee->employment_status ?? 'N/A');

                return [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'department' => $departmentMap->get($employee->department_id, 'N/A'),
                    'section' => $sectionMap->get($employee->section_id, 'N/A'),
                    'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                    'migration_type' => ucfirst($status),
                    'migration_date' => !blank($employee->exited_at) ? \Carbon\Carbon::parse($employee->exited_at)->format('d-M-Y') : 'N/A',
                    'remarks' => data_get($other, 'resign_info.remarks', ''),
                ];
            })
            ->values();

        return ['rows' => $rows];
    }

    private function monthlyLongAbsentData($employees, array $options, Request $request): array
    {
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        // Build holiday date set for the period (for O(1) lookup)
        $holidayDateSet = [];
        try {
            $holidays = HrHoliday::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('from_date', [$from->toDateString(), $to->toDateString()])
                      ->orWhereBetween('to_date', [$from->toDateString(), $to->toDateString()])
                      ->orWhere(function ($q2) use ($from, $to) {
                          $q2->where('from_date', '<=', $from->toDateString())
                             ->where('to_date', '>=', $to->toDateString());
                      });
                })
                ->get(['from_date', 'to_date']);

            $allHolidayDates = $holidays->flatMap(function ($holiday) {
                $start = \Carbon\Carbon::parse($holiday->from_date);
                $end = blank($holiday->to_date) ? $start->copy() : \Carbon\Carbon::parse($holiday->to_date);
                return collect(\Carbon\CarbonPeriod::create($start->startOfDay(), '1 day', $end->startOfDay()))
                    ->map(fn ($d) => $d->format('Y-m-d'))
                    ->all();
            })->unique()->toArray();

            $holidayDateSet = array_flip($allHolidayDates);
        } catch (\Throwable $e) {
            // Holiday table may not exist; proceed without holiday exclusion
        }

        // Build working day period: exclude Fridays and holidays
        $periodDates = collect(\Carbon\CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay()))
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->filter(fn ($date) => \Carbon\Carbon::parse($date)->dayOfWeek !== \Carbon\Carbon::FRIDAY)
            ->filter(fn ($date) => !isset($holidayDateSet[$date]))
            ->values();

        $employeeIds = $employees->pluck('id')->values();
        $attendanceByUser = collect();
        if ($employeeIds->isNotEmpty()) {
            $attendanceByUser = HrAttendance::query()
                ->whereIn('employee_id', $employeeIds->all())
                ->whereDate('date', '>=', $from->toDateString())
                ->whereDate('date', '<=', $to->toDateString())
                ->selectRaw('employee_id, DATE(`date`) as att_date')
                ->get()
                ->groupBy('employee_id')
                ->map(fn ($rows) => array_flip($rows->pluck('att_date')->unique()->toArray()));
        }

        // Minimum consecutive absent working-days to qualify as "long absent"
        $minConsecutive = max(1, (int) $request->input('min_absent_days', 3));

        $rows = $employees
            ->map(function (HrEmployee $employee) use ($attendanceByUser, $periodDates, $departmentMap, $sectionMap, $designationMap, $minConsecutive) {
                $presentSet = $attendanceByUser->get($employee->id, []);

                // Find the longest consecutive absent streak among working days
                $longestStreak = [];
                $currentStreak = [];

                foreach ($periodDates as $date) {
                    if (!isset($presentSet[$date])) {
                        $currentStreak[] = $date;
                    } else {
                        if (count($currentStreak) > count($longestStreak)) {
                            $longestStreak = $currentStreak;
                        }
                        $currentStreak = [];
                    }
                }
                if (count($currentStreak) > count($longestStreak)) {
                    $longestStreak = $currentStreak;
                }

                if (count($longestStreak) < $minConsecutive) {
                    return null;
                }

                $firstDate = \Carbon\Carbon::parse($longestStreak[0])->format('d-M-Y');
                $lastDate  = \Carbon\Carbon::parse(end($longestStreak))->format('d-M-Y');
                $absentDateRange = $firstDate === $lastDate ? $firstDate : $firstDate . ' to ' . $lastDate;

                $other = is_array($employee->other_information) ? $employee->other_information : [];

                return [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'doj' => optional($employee->join_date)->format('d-M-Y'),
                    'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                    'department' => $departmentMap->get($employee->department_id, 'N/A'),
                    'section' => $sectionMap->get($employee->section_id, 'N/A'),
                    'absent_days' => count($longestStreak),
                    'absent_date' => $absentDateRange,
                    'remarks' => data_get($other, 'resign_info.remarks', ''),
                ];
            })
            ->filter()
            ->values();

        return ['rows' => $rows];
    }

    private function monthlyIncrementData($employees, array $options, Request $request, array $incrementMap, float $incrementPercent, ?string $effectiveDate, bool $withRemarks): array
    {
        $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');
        $lineMap = collect($options['lines'] ?? [])->mapWithKeys(fn ($row) => [
            $row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')),
        ]);
        $gradeMap = collect();

        $rows = $employees
            ->map(function (HrEmployee $employee) use ($incrementMap, $request, $withRemarks) {
                $increment = $incrementMap[$employee->id] ?? null;

                // increment-summary: only show employees with a saved increment record
                if ($withRemarks && !$increment) {
                    return null;
                }

                $lastIncDate = data_get($increment, 'increment_date', data_get($increment, 'date'));

                if ($request->filled('from') || $request->filled('to')) {
                    if (blank($lastIncDate)) {
                        return null;
                    }

                    $date = \Carbon\Carbon::parse($lastIncDate);
                    if ($request->filled('from') && $date->lt(\Carbon\Carbon::parse($request->from)->startOfDay())) {
                        return null;
                    }
                    if ($request->filled('to') && $date->gt(\Carbon\Carbon::parse($request->to)->endOfDay())) {
                        return null;
                    }
                }

                return [
                    'employee' => $employee,
                    'increment' => $increment,
                ];
            })
            ->filter()
            ->values()
            ->map(function ($item) use ($classificationMap, $departmentMap, $sectionMap, $subSectionMap, $designationMap, $lineMap, $gradeMap, $incrementPercent, $effectiveDate, $withRemarks) {
                /** @var HrEmployee $employee */
                $employee = $item['employee'];
                $increment = $item['increment'];

                $other = is_array($employee->other_information)
                    ? $employee->other_information
                    : json_decode($employee->other_information ?? '{}', true);
                $profile = data_get($other, 'profile', []);
                $profileNested = data_get($profile, 'profile', []);
                $subSectionId = $employee->sub_section_id
                    ?? data_get($profile, 'sub_section_id')
                    ?? data_get($profileNested, 'sub_section_id');
                $grossSalary = (float) ($employee->gross_salary ?? 0);

                $lastIncValue = (float) data_get($increment, 'increment_amount', data_get($increment, 'gross_increment_amount', data_get($increment, 'amount', 0)));
                $lastIncDate = data_get($increment, 'increment_date', data_get($increment, 'date'));

                // For increment-summary (withRemarks), use the locked increment record values
                if ($withRemarks && $increment) {
                    $incValue = (float) data_get(
                        $increment,
                        'increment_amount',
                        data_get($increment, 'gross_increment_amount', data_get($increment, 'amount', 0))
                    );

                    $finalGross = (float) data_get(
                        $increment,
                        'new_salary',
                        data_get($increment, 'new_salary_comp_1', data_get($increment, 'new_salary_comp_2', $grossSalary + $incValue))
                    );

                    $incPercent = (float) data_get(
                        $increment,
                        'increment_percentage',
                        ($grossSalary > 0 ? (($incValue / $grossSalary) * 100) : $incrementPercent)
                    );

                    $effectiveDateResolved = data_get($increment, 'increment_date', data_get($increment, 'date', $effectiveDate));
                } else {
                    // increment preview: if employee already has a saved/locked increment record,
                    // show those values; otherwise calculate from the requested increment_percent
                    if ($increment) {
                        $incValue = (float) data_get(
                            $increment,
                            'increment_amount',
                            data_get($increment, 'gross_increment_amount', data_get($increment, 'amount', 0))
                        );
                        $finalGross = (float) data_get(
                            $increment,
                            'new_salary',
                            data_get($increment, 'new_salary_comp_1', data_get($increment, 'new_salary_comp_2', $grossSalary + $incValue))
                        );
                        $incPercent = (float) data_get(
                            $increment,
                            'increment_percentage',
                            ($grossSalary > 0 ? (($incValue / $grossSalary) * 100) : $incrementPercent)
                        );
                        $effectiveDateResolved = data_get($increment, 'increment_date', data_get($increment, 'date', $effectiveDate));
                    } else {
                        $incValue   = ($grossSalary * max(0, $incrementPercent)) / 100;
                        $incPercent = max(0, $incrementPercent);
                        $finalGross = $grossSalary + $incValue;
                        $effectiveDateResolved = $effectiveDate;
                    }
                }

                $serviceLength = 'N/A';
                if (!blank($employee->join_date)) {
                    $join = $employee->join_date instanceof \Carbon\Carbon
                        ? $employee->join_date
                        : \Carbon\Carbon::parse($employee->join_date);
                    $ref = !blank($effectiveDateResolved) ? \Carbon\Carbon::parse($effectiveDateResolved) : now();
                    $diff = $join->diff($ref);
                    $serviceLength = sprintf('%dy %dm %dd', $diff->y, $diff->m, $diff->d);
                }

                $row = [
                    'employee_row_id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'service_length' => $serviceLength,
                    'department' => $departmentMap->get($employee->department_id, 'N/A'),
                    'section' => $sectionMap->get($employee->section_id, 'N/A'),
                    'sub_section' => $subSectionMap->get($subSectionId, 'N/A'),
                    'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                    'grade' => $gradeMap->get($employee->grade_lavel, 'N/A'),
                    'classification' => $classificationMap->get($employee->classification_id, 'N/A'),
                    'line_block' => $lineMap->get($employee->floor_line_id, 'N/A'),
                    'join_date' => optional($employee->join_date)->format('d-M-Y'),
                    'last_inc_date' => $lastIncDate ? \Carbon\Carbon::parse($lastIncDate)->format('d-M-Y') : 'N/A',
                    'last_inc_value' => $lastIncValue,
                    'gross_salary' => $grossSalary,
                    'inc_percent' => $incPercent,
                    'inc_value' => $incValue,
                    'final_gross' => $finalGross,
                    'effective_date' => !blank($effectiveDateResolved) ? \Carbon\Carbon::parse($effectiveDateResolved)->format('d-M-Y') : 'N/A',
                ];

                if ($withRemarks) {
                    $row['remarks'] = data_get($increment, 'remarks', '');
                }

                return $row;
            })
            ->values();

        $summary = [
            'employee_count' => $rows->count(),
            'total_increment_value' => $rows->sum('inc_value'),
            'total_final_gross' => $rows->sum('final_gross'),
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    private function upsertIncrementRecord(HrEmployee $employee, string $effectiveDate, float $previousSalary, float $incrementValue, float $incrementPercent, float $newSalary): void
    {
        $table = (new HrEmployeeSalaryIncrement())->getTable();

        if (Schema::hasTable($table)) {
            $query = HrEmployeeSalaryIncrement::query();
            if (Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', $employee->id);
            } elseif (Schema::hasColumn($table, 'employee_id')) {
                $query->where('employee_id', $employee->id);
            }

            if (Schema::hasColumn($table, 'increment_date')) {
                $query->whereDate('increment_date', $effectiveDate);
            } elseif (Schema::hasColumn($table, 'date')) {
                $query->whereDate('date', $effectiveDate);
            }

            $row = $query->first() ?? new HrEmployeeSalaryIncrement();

            if (Schema::hasColumn($table, 'user_id')) {
                $row->user_id = $employee->id;
            }
            if (Schema::hasColumn($table, 'employee_id')) {
                $row->employee_id = $employee->id;
            }
            if (Schema::hasColumn($table, 'increment_date')) {
                $row->increment_date = $effectiveDate;
            } elseif (Schema::hasColumn($table, 'date')) {
                $row->date = $effectiveDate;
            }
            if (Schema::hasColumn($table, 'previous_salary')) {
                $row->previous_salary = $previousSalary;
            }
            if (Schema::hasColumn($table, 'increment_amount')) {
                $row->increment_amount = $incrementValue;
            }
            if (Schema::hasColumn($table, 'gross_increment_amount')) {
                $row->gross_increment_amount = $incrementValue;
            }
            if (Schema::hasColumn($table, 'amount')) {
                $row->amount = $incrementValue;
            }
            if (Schema::hasColumn($table, 'increment_percentage')) {
                $row->increment_percentage = $incrementPercent;
            }
            if (Schema::hasColumn($table, 'new_salary')) {
                $row->new_salary = $newSalary;
            }
            if (Schema::hasColumn($table, 'remarks')) {
                $row->remarks = 'Locked from monthly increment report';
            }
            if (Schema::hasColumn($table, 'approved_by')) {
                $row->approved_by = Auth::id();
            }

            $row->save();

            return;
        }

        $other = $employee->other_information;
        $other = is_array($other) ? $other : [];
        $rows = collect(data_get($other, 'increments', []));

        $existingIndex = $rows->search(function ($row) use ($effectiveDate) {
            $date = data_get($row, 'increment_date', data_get($row, 'date'));
            return (string) $date === (string) $effectiveDate;
        });

        $newRow = [
            'amount' => $incrementValue,
            'increment_date' => $effectiveDate,
            'increment_percentage' => $incrementPercent,
            'previous_salary' => $previousSalary,
            'new_salary' => $newSalary,
            'remarks' => 'Locked from monthly increment report',
            'created_at' => now()->toDateTimeString(),
        ];

        if ($existingIndex !== false) {
            $rows[$existingIndex] = array_merge((array) $rows[$existingIndex], $newRow);
        } else {
            $rows->push($newRow);
        }

        $other['increments'] = $rows->values()->all();
        $employee->other_information = json_encode($other);
        $employee->save();
    }

    private function employeeReportScreen(Request $request, string $report)
    {
        $employees = $this->employeeReportQuery($request)
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get();

        $options = $this->employeeReportOptions();
        $reportTypes = [
            'database' => 'Database',
            'manpower-summary' => 'Manpower Summary',
            'details' => 'Details',
        ];
        $language = $request->input('language', 'en');

        if ($request->boolean('print')) {
            $reportType = (string) $request->input('report_type', 'database');
            if (! array_key_exists($reportType, $reportTypes)) {
                $reportType = 'database';
            }

            if ($reportType === 'database') {
                return view('hr::reports.employee-database-print', [
                    'employees' => $employees,
                    'request' => $request,
                    'options' => $options,
                ]);
            } elseif ($reportType === 'details') {
                $detailsRows = $this->employeeDetailsRows($employees, $options);
                return view('hr::reports.employee-details-print', [
                    'detailsRows' => $detailsRows,
                    'request' => $request,
                    'options' => $options,
                ]);
            } elseif ($reportType === 'manpower-summary') {
                $manpowerRows = $this->employeeManpowerSummaryRows($employees, $options);
                return view('hr::reports.employee-manpower-print', [
                    'employees' => $employees,
                    'manpowerRows' => $manpowerRows,
                    'request' => $request,
                    'options' => $options,
                ]);
            }
            // fallback
            return view('hr::reports.employee-print', [
                'employees' => $employees,
                'request' => $request,
                'options' => $options,
                'reportType' => $reportType,
                'reportTypeLabel' => $reportTypes[$reportType],
                'language' => $language,
                'manpowerRows' => $this->employeeManpowerSummaryRows($employees, $options),
                'detailsRows' => null,
            ]);
        }

        return view('hr::reports.employee', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'employees' => $employees,
            'options' => $options,
            'reportTypes' => $reportTypes,
            'request' => $request,
            'language' => $language,
        ]);
    }

       /**
     * Generate rows for the 'details' employee report type.
     * Table header:
     * S.L | Working Place | Emp. ID | Name | Join Date | Job Age | DOB | Age | Sex | Department | Section | Sub Section | Designation | Contact No. | Grade | Classification | Line/Block | Shift | WeekEnd | Gross Salary
     */
    private function employeeDetailsRows($employees, array $options)
    {
        $workingPlaceMap = collect($options['workingPlaces'] ?? [])->pluck('name', 'id');
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'] ?? [])->pluck('name', 'id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');
        $gradeMap = collect();
        $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
        $lineMap = collect($options['lines'] ?? [])->mapWithKeys(fn ($row) => [
            $row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')),
        ]);
        $shiftMap = HrShift::query()->pluck('name', 'id');

        $rows = collect();
        $serial = 1;
        foreach ($employees as $employee) {
            // Parse other_information JSON
            $other = is_array($employee->other_information)
                ? $employee->other_information
                : json_decode($employee->other_information ?? '{}', true);

            // profile: working_place_id, sub_section_id, weekend, etc.
            $profile = data_get($other, 'profile', []);

            // salary_info: bank_or_phone, car_fuel, phone_internet, extra_facility, tax
            $salaryInfo = data_get($other, 'salary_info', []);

            $dobRaw = data_get($employee, 'dob') ?: data_get($employee, 'date_of_birth');
            $dob = 'N/A';
            $age = 'N/A';
            if (filled($dobRaw)) {
                try {
                    $dobDate = \Carbon\Carbon::parse($dobRaw);
                    $dob = $dobDate->format('d-M-Y');
                    $age = (string) $dobDate->age;
                } catch (\Throwable $e) {
                    $dob = (string) $dobRaw;
                }
            }

            $jobAge = 'N/A';
            if (filled($employee->join_date)) {
                try {
                    $joinDate = \Carbon\Carbon::parse($employee->join_date);
                    $diff = $joinDate->diff(now());
                    $jobAge = sprintf('%dy %dm %dd', $diff->y, $diff->m, $diff->d);
                } catch (\Throwable $e) {
                    $jobAge = 'N/A';
                }
            }

            $rows->push([
                'sl'               => $serial++,
                'working_place'    => $workingPlaceMap->get(data_get($profile, 'working_place_id') ?? $employee->working_place_id, 'N/A'),
                'name'             => $employee->name,
                'employee_id'      => $employee->employee_id,
                'join_date'        => $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d-M-Y') : 'N/A',
                'job_age'          => $jobAge,
                'dob'              => $dob,
                'age'              => $age,
                'gross_salary'     => (float) ($employee->gross_salary ?? 0),
                'pay_mode'         => $employee->salary_type ?? 'N/A',
                'bank_mobile_no'   => data_get($salaryInfo, 'bank_or_phone', $employee->mobile ?? 'N/A'),
                'car_fuel'         => (float) data_get($salaryInfo, 'car_fuel', 0),
                'phone_internet'   => (float) data_get($salaryInfo, 'phone_internet', 0),
                'extra_facility'   => (float) data_get($salaryInfo, 'extra_facility', 0),
                'tax'              => (float) data_get($salaryInfo, 'tax', 0),
                    'classification'   => $classificationMap->get($employee->classification_id, 'N/A'),
                'department'       => $departmentMap->get($employee->department_id, 'N/A'),
                'section'          => $sectionMap->get($employee->section_id, 'N/A'),
                'sub_section'      => $subSectionMap->get($employee->sub_section_id ?? data_get($profile, 'sub_section_id'), 'N/A'),
                'line_block'       => $lineMap->get($employee->floor_line_id, 'N/A'),
                'designation'      => $designationMap->get($employee->designation_id, 'N/A'),
                'grade'            => $gradeMap->get($employee->grade_lavel, 'N/A'),
                'shift'            => $shiftMap->get($employee->shift_id, 'N/A'),
                'weekend'          => data_get($profile, 'weekend', $employee->weekend ?? 'N/A'),
                'contact_no'       => $employee->mobile ?? 'N/A',
                'sex'              => $employee->sex ?? 'N/A',
            ]);
        }
        return $rows;
    }

    private function employeeReportQuery(Request $request)
    {
        $query = HrEmployee::query();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', 'like', '%' . trim((string) $request->employee_id) . '%');
        }

        if ($request->filled('employee_ids')) {
            $ids = collect(explode(',', (string) $request->employee_ids))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->values();
            if ($ids->isNotEmpty()) {
                $query->whereIn('employee_id', $ids->all());
            }
        }

        if ($request->filled('classification')) {
            $query->where('classification_id', (int) $request->classification);
        }

        if ($request->filled('department')) {
            $query->where('department_id', (int) $request->department);
        }

        if ($request->filled('section')) {
            $query->where('section_id', (int) $request->section);
        }

        if ($request->filled('sub_section')) {
            $subSectionCol = Schema::hasColumn((new HrEmployee())->getTable(), 'sub_section_id') ? 'sub_section_id'
                : (Schema::hasColumn((new HrEmployee())->getTable(), 'hr_sub_section_id') ? 'hr_sub_section_id' : null);
            if ($subSectionCol) {
                $query->where($subSectionCol, (int) $request->sub_section);
            }
        }

        if ($request->filled('working_place')) {
            $wpCol = Schema::hasColumn((new HrEmployee())->getTable(), 'working_place_id') ? 'working_place_id'
                : (Schema::hasColumn((new HrEmployee())->getTable(), 'hr_working_place_id') ? 'hr_working_place_id' : null);
            if ($wpCol) {
                $query->where($wpCol, (int) $request->working_place);
            }
        }

        if ($request->filled('shift')) {
            $query->where('shift_id', (int) $request->shift);
        }

        if ($request->filled('line_number')) {
            $query->where('floor_line_id', (int) $request->line_number);
        }

        if ($request->filled('salary_type')) {
            $query->where('salary_type', (string) $request->salary_type);
        }

        if ($request->filled('designation')) {
            $query->where('designation_id', (int) $request->designation);
        }

        if ($request->filled('gender')) {
            $query->whereHas('basicInfo', function ($q) use ($request) {
                $sexId = HrSex::where('name', (string) $request->gender)->value('id');
                $q->where('sex_id', $sexId);
            });
        }

        if ($request->filled('employee_status')) {
            $status = (string) $request->employee_status;
            $query->where(function ($builder) use ($status) {
                if ($status === 'regular') {
                    $builder->whereNull('employment_status')
                        ->orWhere('employment_status', '')
                        ->orWhere('employment_status', 'regular');

                    return;
                }

                $builder->where('employment_status', $status);
                if ($status === 'lefty') {
                    $builder->orWhere('employment_status', 'left');
                }
                if ($status === 'resign') {
                    $builder->orWhere('employment_status', 'resigned');
                }
            });
        }

        return $query;
    }

    private function employeeReportOptions(): array
    {
        $genderOptions = HrSex::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name');

        return [
            'classifications' => HrClassification::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'departments' => HrDepartment::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'sections' => HrSection::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'subSections' => HrSubSection::orderBy('name')->get(['id', 'name', 'department_id', 'section_id', 'salary_type', 'approve_man_power']),
            'lines' => HrFloorLine::query()->where('status', 'active')->orderBy('line_name')->get()->map(static function ($line) {
                return (object) [
                    'id' => $line->id,
                    'name' => $line->line_name,
                    'slug' => $line->line_name,
                ];
            }),
            'designations' => Schema::hasTable((new HrDesignation())->getTable())
                ? HrDesignation::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'workingPlaces' => HrWorkingPlace::orderBy('name')->get(['id', 'name']),
            'shifts' => HrShift::orderBy('name')->get(['id', 'name']),
            'gender' => $genderOptions,
            'employeeStatuses' => collect([
                ['id' => 'regular', 'name' => 'Regular'],
                ['id' => 'lefty', 'name' => 'Lefty'],
                ['id' => 'resign', 'name' => 'Resign'],
                ['id' => 'transfer', 'name' => 'Transfer'],
            ]),
            'salaryTypes' => collect([
                ['id' => 'price_rate', 'name' => 'Price Rate'],
                ['id' => 'fixed_rate', 'name' => 'Fixed Rate'],
                ['id' => 'Cash', 'name' => 'Cash'],
                ['id' => 'Bank', 'name' => 'Bank'],
                ['id' => 'Mobile Banking', 'name' => 'Mobile Banking'],
                ['id' => 'Cheque', 'name' => 'Cheque'],
            ]),
        ];
    }

    private function employeeManpowerSummaryRows($employees, array $options)
    {
        $departmentMap = collect($options['departments'] ?? [])->pluck('name', 'id');
        $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'] ?? [])->keyBy('id');
        $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');

        $rows = collect();
        $serial = 1;
        $grandApprove = 0;
        $grandRecruited = 0;
        $grandGrossSalary = 0;

        $employees
            ->groupBy(function (HrEmployee $employee) {
                // sub_section_id is stored in other_information['profile'], not a direct column
                $other = is_array($employee->other_information)
                    ? $employee->other_information
                    : json_decode($employee->other_information ?? '{}', true);
                $subSectionId = data_get($other, 'profile.sub_section_id') ?? $employee->sub_section_id;

                return implode('|', [
                    $employee->department_id,
                    $employee->section_id,
                    $subSectionId,
                ]);
            })
            ->each(function ($subSectionGroup) use (&$rows, &$serial, &$grandApprove, &$grandRecruited, &$grandGrossSalary, $departmentMap, $sectionMap, $subSectionMap, $designationMap) {
                /** @var HrEmployee $subSectionFirst */
                $subSectionFirst = $subSectionGroup->first();

                // Resolve sub_section_id from profile (not a direct column)
                $subSectionFirstOther = is_array($subSectionFirst->other_information)
                    ? $subSectionFirst->other_information
                    : json_decode($subSectionFirst->other_information ?? '{}', true);
                $subSectionId = data_get($subSectionFirstOther, 'profile.sub_section_id') ?? $subSectionFirst->sub_section_id;

                $subSection = $subSectionMap->get($subSectionId);
                $subSectionApprove = (int) ($subSection->approve_man_power ?? 0);
                $subSectionRecruited = 0;
                $subSectionGrossSalary = 0;

                $subSectionGroup
                    ->groupBy('designation_id')
                    ->each(function ($designationGroup) use (&$rows, &$serial, &$subSectionRecruited, &$subSectionGrossSalary, $departmentMap, $sectionMap, $subSection, $designationMap, $subSectionFirst, $subSectionApprove) {
                        /** @var HrEmployee $first */
                        $first = $designationGroup->first();
                        $recruited = $designationGroup->count();
                        $totalGrossSalary = $designationGroup->sum(function (HrEmployee $employee) {
                            return (float) ($employee->gross_salary ?? 0);
                        });

                        $subSectionRecruited += $recruited;
                        $subSectionGrossSalary += $totalGrossSalary;

                        $rows->push([
                            'row_type' => 'detail',
                            'sl' => $serial++,
                            'department' => $departmentMap->get($subSectionFirst->department_id, 'N/A'),
                            'section' => $sectionMap->get($subSectionFirst->section_id, 'N/A'),
                            'sub_section' => data_get($subSection, 'name', 'N/A'),
                            'designation' => $designationMap->get($first->designation_id, 'N/A'),
                            'approve_manpower' => $subSectionApprove,
                            'recruited' => $recruited,
                            'deviation' => $recruited - $subSectionApprove,
                            'total_gross_salary' => $totalGrossSalary,
                        ]);
                    });

                $subSectionDeviation = $subSectionRecruited - $subSectionApprove;
                $grandApprove += $subSectionApprove;
                $grandRecruited += $subSectionRecruited;
                $grandGrossSalary += $subSectionGrossSalary;

                $rows->push([
                    'row_type' => 'total',
                    'sl' => 'Total',
                    'department' => '',
                    'section' => '',
                    'sub_section' => '',
                    'designation' => '',
                    'approve_manpower' => $subSectionApprove,
                    'recruited' => $subSectionRecruited,
                    'deviation' => $subSectionDeviation,
                    'total_gross_salary' => $subSectionGrossSalary,
                ]);
            });

        $rows->push([
            'row_type' => 'grand_total',
            'sl' => 'Grand Total',
            'department' => '',
            'section' => '',
            'sub_section' => '',
            'designation' => '',
            'approve_manpower' => $grandApprove,
            'recruited' => $grandRecruited,
            'deviation' => $grandRecruited - $grandApprove,
            'total_gross_salary' => $grandGrossSalary,
        ]);

        return $rows;
    }

    private function personalFileReportScreen(Request $request, string $report)
    {
        $query = HrEmployee::query();

        // ID card should skip placeholder IDs but must not hide valid employees
        // just because designation/department is missing.
        if ((string) $request->input('report_type') === 'id-card') {
            // $query->where('employee_id', '<>', '00000');
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', trim((string) $request->employee_id));
        }

        if ($request->filled('employee_ids')) {
            $ids = collect(explode(',', (string) $request->employee_ids))
                ->map(fn ($id) => trim($id))
                ->filter()
                ->values();
            if ($ids->isNotEmpty()) {
                $query->whereIn('employee_id', $ids->all());
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('join_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('join_date', '<=', $request->to);
        }

        if ($request->filled('classification')) {
            $query->where('classification_id', $request->classification);
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('section')) {
            $query->where('section_id', $request->section);
        }

        if ($request->filled('subsection') && Schema::hasColumn((new HrEmployee())->getTable(), 'sub_section_id')) {
            $query->where('sub_section_id', $request->subsection);
        }

        if ($request->filled('shift')) {
            $query->where('shift_id', $request->shift);
        }

        if ($request->filled('working_place')) {
            $workingPlace = trim((string) $request->working_place);
            $query->where(function ($builder) use ($workingPlace) {
                if (Schema::hasColumn((new HrEmployee())->getTable(), 'working_place_id')) {
                    $builder->orWhere('working_place_id', $workingPlace);
                }
                if (Schema::hasColumn((new HrEmployee())->getTable(), 'location')) {
                    $builder->orWhere('location', 'like', '%' . $workingPlace . '%');
                }
            });
        }

        if ($request->filled('employee_status')) {
            $status = (string) $request->employee_status;
            $query->where(function ($builder) use ($status) {
                if ($status === 'regular') {
                    $builder->whereNull('employment_status')
                        ->orWhere('employment_status', '')
                        ->orWhere('employment_status', 'regular');

                    return;
                }

                $builder->where('employment_status', $status);
                if ($status === 'lefty') {
                    $builder->orWhere('employment_status', 'left');
                }
                if ($status === 'resign') {
                    $builder->orWhere('employment_status', 'resigned');
                }
            });
        }

        $employees = $query->with(['designation', 'department'])->naturalOrderById()->get();

        $options = [
            'classifications' => HrClassification::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'departments' => HrDepartment::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'sections' => HrSection::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'subsections' => HrSubSection::orderBy('name')->get(['id', 'name']),
            'shifts' => HrShift::orderBy('name')->get(['id', 'name']),
            'workingPlaces' => HrWorkingPlace::orderBy('name')->get(['id', 'name']),
        ];

        $reportTypes = [
            'id-card' => 'ID Card',
            'application' => 'Application',
            'appointment-letter' => 'Appoinment Letter',
            // 'employment-letter' => 'Employment Letter',
            'nominee' => 'Nominee',
            'age-verification' => 'Age Verification',
            'job-responsibility' => 'Job Responsibility',
            'appraisal-letter' => 'Apprasial Letter',
            'joining-letter' => 'Joining Letter',
            'increment-letter' => 'Increment Letter',
        ];

        if ($request->boolean('print')) {
            $validated = $request->validate([
                'report_type' => 'required|string',
            ]);

            $reportType = (string) $validated['report_type'];
            abort_unless(array_key_exists($reportType, $reportTypes), 422);

            return view('hr::reports.personal-file-print', [
                'employees' => $employees,
                'request' => $request,
                'reportType' => $reportType,
                'reportTypeLabel' => $reportTypes[$reportType],
                'language' => $request->input('language', 'en'),
                'increments' => $this->latestIncrements($employees),
            ]);
        }

        if ($request->filled('report_type')) {
            $reportType = (string) $request->report_type;
            abort_unless(array_key_exists($reportType, $reportTypes), 422);
        }

        return view('hr::reports.personal-file', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'employees' => $employees,
            'options' => $options,
            'reportTypes' => $reportTypes,
            'request' => $request,
        ]);
    }

    private function latestIncrements($employees): array
    {
        $map = [];
        $table = (new HrEmployeeSalaryIncrement())->getTable();
        if (!Schema::hasTable($table)) {
            return $map;
        }

        $userIds = $employees->pluck('id')->filter()->values();
        $employeeCodes = $employees->pluck('employee_id')->filter()->values();
        if ($userIds->isEmpty() && $employeeCodes->isEmpty()) {
            return $map;
        }

        $sortCol = Schema::hasColumn($table, 'increment_date') ? 'increment_date' : 'created_at';
        $hasUserId = Schema::hasColumn($table, 'user_id');
        $hasEmployeeId = Schema::hasColumn($table, 'employee_id');
        if (!$hasUserId && !$hasEmployeeId) {
            return $map;
        }

        $rows = HrEmployeeSalaryIncrement::query()
            ->where(function ($query) use ($hasUserId, $hasEmployeeId, $userIds, $employeeCodes) {
                if ($hasUserId && $userIds->isNotEmpty()) {
                    $query->orWhereIn('user_id', $userIds->all());
                }

                if ($hasEmployeeId && $userIds->isNotEmpty()) {
                    $query->orWhereIn('employee_id', $userIds->all());
                }

                if ($hasEmployeeId && $employeeCodes->isNotEmpty()) {
                    $query->orWhereIn('employee_id', $employeeCodes->all());
                }
            })
            ->orderBy($sortCol, 'desc')
            ->get();

        foreach ($employees as $employee) {
            $map[$employee->id] = $rows->first(function ($row) use ($employee) {
                $rowUserId = data_get($row, 'user_id');
                $rowEmployeeId = data_get($row, 'employee_id');

                return (string) $rowUserId === (string) $employee->id
                    || (string) $rowEmployeeId === (string) $employee->id
                    || (string) $rowEmployeeId === (string) $employee->employee_id;
            });
        }

        return $map;
    }

    private function employeeReport(): array
    {
        $rows = HrEmployee::query()
            
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get()
            ->map(function (HrEmployee $user) {
                return [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'designation' => optional($user->designation)->name,
                    'department' => optional($user->department)->name,
                    'joining_date' => optional($user->join_date)?->format('Y-m-d'),
                    'status' => $user->status,
                ];
            });

        return [['employee_id', 'name', 'designation', 'department', 'joining_date', 'status'], $rows];
    }

    private function monthlyReport(): array
    {
        $rows = HrEmployee::query()
            
            ->selectRaw("DATE_FORMAT(join_date, '%Y-%m') as month")
            ->selectRaw('count(*) as total_employee')
            ->whereNotNull('join_date')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get();

        return [['month', 'total_employee'], $rows->map(fn ($row) => ['month' => $row->month, 'total_employee' => $row->total_employee])];
    }

    private function machineIdReport(): array
    {
        $rows = HrEmployee::query()
            
            ->naturalOrderById()
            ->get(['employee_id', 'name', 'mobile', 'status'])
            ->map(fn (HrEmployee $user) => ['employee_id' => $user->employee_id, 'name' => $user->name, 'mobile' => $user->mobile, 'status' => $user->status]);

        return [['employee_id', 'name', 'mobile', 'status'], $rows];
    }

    private function jobCardReport(): array
    {
        $rows = HrEmployee::query()
            
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get()
            ->map(function (HrEmployee $user) {
                return [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'department' => optional($user->department)->name,
                    'designation' => optional($user->designation)->name,
                    'line_number' => $user->floor_line_id,
                    'shift_id' => $user->shift_id,
                ];
            });

        return [['employee_id', 'name', 'department', 'designation', 'line_number', 'shift_id'], $rows];
    }

    private function personalFileReport(): array
    {
        $rows = HrEmployee::query()
            
            ->naturalOrderById()
            ->get()
            ->map(function (HrEmployee $user) {
                return [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'father_name' => $user->father_name,
                    'mother_name' => $user->mother_name,
                    'dob' => optional($user->dob)?->format('Y-m-d'),
                    'age_verify_ref' => $user->birth_registration ?: $user->nid_number,
                    'nominee' => $user->nominee,
                    'nominee_age' => $user->nominee_age,
                    'nominee_relation' => $user->nominee_relation,
                    'mobile' => $user->mobile,
                    'nid_number' => $user->nid_number,
                ];
            });

        return [[
            'employee_id',
            'name',
            'father_name',
            'mother_name',
            'dob',
            'age_verify_ref',
            'nominee',
            'nominee_age',
            'nominee_relation',
            'mobile',
            'nid_number',
        ], $rows];
    }

    private function attendanceReport(): array
    {
        $rows = HrEmployee::query()
            
            ->with(['designation', 'department'])
            ->naturalOrderById()
            ->get()
            ->map(function (HrEmployee $user) {
                return [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'login_status' => $user->login_status,
                    'department' => optional($user->department)->name,
                    'designation' => optional($user->designation)->name,
                    'shift_id' => $user->shift_id,
                ];
            });

        return [['employee_id', 'name', 'login_status', 'department', 'designation', 'shift_id'], $rows];
    }

    private function mealAllowanceReport(): array
    {
        $rows = HrDesignation::query()
            ->orderBy('name')
            ->get()
            ->map(function (HrDesignation $designation) {
                return [
                    'designation' => $designation->name,
                    'tiffin_allowance' => $designation->tiffin_allowance,
                    'night_allowance' => $designation->night_allowance,
                    'dinner_allowance' => $designation->dinner_allowance,
                    'payment_way' => $designation->payment_way,
                ];
            });

        return [['designation', 'tiffin_allowance', 'night_allowance', 'dinner_allowance', 'payment_way'], $rows];
    }

    private function productionJobCardReport(): array
    {
        $rows = HrEmployee::query()
            
            ->whereNotNull('floor_line_id')
            ->orderBy('floor_line_id')
            ->naturalOrderById()
            ->get()
            ->map(fn (HrEmployee $user) => ['line_number' => $user->floor_line_id, 'employee_id' => $user->employee_id, 'name' => $user->name, 'salary_type' => $user->salary_type]);

        return [['line_number', 'employee_id', 'name', 'salary_type'], $rows];
    }

    private function bonusSalaryFixedReport(): array
    {
        $rows = HrBonusPolicy::query()
            ->where('type', 'fixed')
            ->orderBy('policy_name')
            ->get()
            ->map(fn (BonusPolicy $policy) => ['policy' => $policy->name, 'basis' => $policy->salary_basis, 'amount' => $policy->amount, 'status' => $policy->status]);

        return [['policy', 'basis', 'amount', 'status'], $rows];
    }

    private function bonusSalaryProductionReport(): array
    {
        $rows = HrProductionBonus::query()
            ->orderBy('name')
            ->get()
            ->map(fn (ProductionBonus $bonus) => ['name' => $bonus->name, 'percentage' => $bonus->percentage, 'effective_from' => $bonus->effective_from, 'effective_to' => $bonus->effective_to]);

        return [['name', 'percentage', 'effective_from', 'effective_to'], $rows];
    }

    private function salaryFixedReport(): array
    {
        $rows = HrEmployee::query()
            
            ->where(function ($builder) {
                $builder->where('salary_type', 'fixed_rate')
                    ->orWhereNull('salary_type');
            })
            ->naturalOrderById()
            ->get()
            ->map(fn (HrEmployee $user) => ['employee_id' => $user->employee_id, 'name' => $user->name, 'gross_salary' => $user->gross_salary, 'basic_salary' => $user->basic_salary]);

        return [['employee_id', 'name', 'gross_salary', 'basic_salary'], $rows];
    }

    private function salaryProductionReport(): array
    {
        $rows = HrEmployee::query()
            
            ->where('salary_type', 'price_rate')
            ->naturalOrderById()
            ->get()
            ->map(fn (HrEmployee $user) => ['employee_id' => $user->employee_id, 'name' => $user->name, 'gross_salary' => $user->gross_salary, 'basic_salary' => $user->basic_salary]);

        return [['employee_id', 'name', 'gross_salary', 'basic_salary'], $rows];
    }

    private function salarySummaryReport(): array
    {
        $employeeTable = (new HrEmployee())->getTable();

        $rows = HrEmployee::query()
            
            ->leftJoin('hr_departments as departments', 'departments.id', '=', $employeeTable . '.department_id')
            ->select('departments.name as department')
            ->selectRaw('count(' . $employeeTable . '.id) as total_employee')
            ->selectRaw('sum(coalesce(' . $employeeTable . '.gross_salary, 0)) as gross_salary')
            ->selectRaw('sum(coalesce(' . $employeeTable . '.basic_salary, 0)) as basic_salary')
            ->groupBy('departments.name')
            ->orderBy('departments.name')
            ->get()
            ->map(fn ($row) => ['department' => $row->department ?: 'Undefined', 'total_employee' => $row->total_employee, 'gross_salary' => $row->gross_salary, 'basic_salary' => $row->basic_salary]);

        return [['department', 'total_employee', 'gross_salary', 'basic_salary'], $rows];
    }

    // ──────────────────────────────────────────────────────────────────
    // JOB CARD REPORT
    // ──────────────────────────────────────────────────────────────────

    private function jobCardReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $reportTypes = [
            'job-card'              => 'Job Card',
            'job-card-summary'      => 'Job Card Summary',
            'job-card-lock'         => 'Job Card (Lock)',
            'job-card-summary-lock' => 'Job Card Summary (Lock)',
            'attendance-summary'    => 'Attendance Summary',
            'ot-details'            => 'OT Details',
            'ot-summary'            => 'OT Summary',
        ];

        if ($request->boolean('print')) {
            $from = $request->input('from') ?: now()->toDateString();
            $to   = $request->input('to') ?: $from;
            $reportType = $request->input('report_type', 'job-card');
            if (!array_key_exists($reportType, $reportTypes)) {
                $reportType = 'job-card';
            }

            // If no filter is selected, show all employees
            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->naturalOrderById()
                ->get();

            // Build date range collection
            $dates = collect();
            $cur = \Carbon\Carbon::parse($from);
            $end = \Carbon\Carbon::parse($to);
            while ($cur->lte($end)) {
                $dates->push($cur->copy());
                $cur->addDay();
            }

            // Attendance keyed by "employee_id_date"
            $attendanceMap = HrAttendance::query()
                ->whereIn('employee_id', $employees->pluck('id'))
                ->whereBetween('date', [$from, $to])
                ->get()
                ->groupBy(fn ($a) => $a->employee_id . '_' . $a->date);

            $departmentMap   = collect($options['departments'])->pluck('name', 'id');
            $sectionMap      = collect($options['sections'])->pluck('name', 'id');
            $subSectionMap   = collect($options['subSections'])->pluck('name', 'id');
            $designationMap  = collect($options['designations'])->pluck('name', 'id');
            $classificationMap = collect($options['classifications'])->pluck('name', 'id');
            $lineMap = collect($options['lines'])->mapWithKeys(fn ($r) => [
                $r->id => trim(($r->name ?? '') . (filled($r->slug ?? null) ? ' - ' . $r->slug : '')),
            ]);
            $shiftMap = HrShift::query()->pluck('name', 'id');

            return view('hr::reports.job-card-report-print', compact(
                'request', 'employees', 'attendanceMap', 'dates',
                'from', 'to', 'reportType', 'reportTypes',
                'departmentMap', 'sectionMap', 'subSectionMap',
                'designationMap', 'classificationMap', 'lineMap', 'shiftMap'
            ) + [
                'fromLabel' => \Carbon\Carbon::parse($from)->format('d-M-Y'),
                'toLabel'   => \Carbon\Carbon::parse($to)->format('d-M-Y'),
                'reportTypeLabel' => $reportTypes[$reportType],
            ]);
        }

        return view('hr::reports.job-card-report', [
            'reportKey'   => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options'     => $options,
            'reportTypes' => $reportTypes,
            'request'     => $request,
        ]);
    }

    public function applyJobCardLock(Request $request)
    {
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to') ?: $from;

        $employees = $this->employeeReportQuery($request)->get(['id', 'other_information']);

        DB::transaction(function () use ($employees, $from, $to) {
            foreach ($employees as $employee) {
                $other = $employee->other_information;
                $other = is_array($other) ? $other : [];
                $lockKey = 'job_card_lock';
                if (!isset($other[$lockKey])) {
                    $other[$lockKey] = [];
                }
                $key = $from . '_' . $to;
                $other[$lockKey][$key] = [
                    'locked_at' => now()->toDateTimeString(),
                    'locked_by' => Auth::id(),
                ];
                $employee->other_information = json_encode($other);
                $employee->save();
            }
        });

        return back()->with('success', 'Job card locked for selected period.');
    }

    // ──────────────────────────────────────────────────────────────────
    // ATTENDANCE REPORT
    // ──────────────────────────────────────────────────────────────────

    private function attendanceReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $attendanceTypes = [
            'P'  => 'Present',
            'A'  => 'Absent',
            'L'  => 'Leave',
            'H'  => 'Holiday',
            'W'  => 'Weekend',
            'OT' => 'OT Only',
            'PM' => 'Attendance Missing',
            'AS' => 'Attendance Status',
        ];

        if ($request->boolean('print')) {
            $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
            $to   = $request->input('to')   ?: now()->toDateString();
            $type = strtoupper((string) $request->input('att_type', ''));

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->naturalOrderById()
                ->get();

            // Build per-employee summary over the date range
            $attendanceByEmployee = $employees->mapWithKeys(function ($employee) use ($from, $to) {
                $pack    = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $from, $to);
                $summary = $pack['summary'] ?? [];

                $presentStatuses = ['present', 'late', 'early_exit', 'punch_missing', 'late_and_early_exit', 'late_and_punch_missing'];
                $presentCount = ($summary['totalPresent'] ?? 0)
                    + ($summary['totalLate'] ?? 0)
                    + ($summary['totalEO'] ?? 0)
                    + ($summary['totalPM'] ?? 0)
                    + ($summary['totalLEO'] ?? 0)
                    + ($summary['totalLPM'] ?? 0);

                return [$employee->id => [
                    'present'  => $presentCount,
                    'absent'   => $summary['totalAbsent'] ?? 0,
                    'late'     => ($summary['totalLate'] ?? 0)
                                + ($summary['totalLEO'] ?? 0)
                                + ($summary['totalLPM'] ?? 0),
                    'leave'    => $summary['totalLeave'] ?? 0,
                    'weekend'  => $summary['totalWeekendDays'] ?? 0,
                    'holiday'  => $summary['totalGovHolidays'] ?? 0,
                    'ot_hours' => round($summary['totalComplianceOt'] ?? 0, 2),
                ]];
            });

            if ($request->filled('att_type')) {
                $employees = $employees->filter(function ($employee) use ($attendanceByEmployee, $type) {
                    $row     = $attendanceByEmployee->get($employee->id, []);
                    $present = $row['present'] ?? 0;
                    $absent  = $row['absent'] ?? 0;
                    $leave   = $row['leave'] ?? 0;
                    $holiday = $row['holiday'] ?? 0;
                    $weekend = $row['weekend'] ?? 0;
                    $ot      = $row['ot_hours'] ?? 0;

                    return match ($type) {
                        'P'  => $present > 0,
                        'A'  => $absent > 0,
                        'L'  => $leave > 0,
                        'H'  => $holiday > 0,
                        'W'  => $weekend > 0,
                        'OT' => $ot > 0,
                        default => true,
                    };
                })->values();
            }

            $sectionMap     = collect($options['sections'])->pluck('name', 'id');
            $subSectionMap  = collect($options['subSections'])->pluck('name', 'id');
            // designation_id on users references hr_designations table
            $designationMap = HrDesignation::query()->pluck('name', 'id');
            $shiftMap       = HrShift::query()->pluck('name', 'id');
            $lineMap = collect($options['lines'] ?? [])->mapWithKeys(fn ($row) => [
                $row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')),
            ]);
            $workingPlaceMap = collect($options['workingPlaces'] ?? [])->pluck('name', 'id');

            $dateLabel = \Carbon\Carbon::parse($from)->format('d-M-Y') . ' to ' . \Carbon\Carbon::parse($to)->format('d-M-Y');

            if ($type === 'A') {
                $fromDate = \Carbon\Carbon::parse($from)->toDateString();
                $toDate = \Carbon\Carbon::parse($to)->toDateString();
                $isSingleDay = $fromDate === $toDate;

                if ($isSingleDay) {
                    $dailySummary = $employees->mapWithKeys(function ($employee) use ($toDate) {
                        $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $toDate, $toDate);
                        $summary = $pack['summary'] ?? [];

                        return [$employee->id => [
                            'absent' => (int) ($summary['totalAbsent'] ?? 0),
                        ]];
                    });

                    $employees = $employees
                        ->filter(fn ($employee) => (($dailySummary->get($employee->id, [])['absent'] ?? 0) > 0))
                        ->values();

                    $attendanceMap = HrAttendance::query()
                        ->whereIn('employee_id', $employees->pluck('id'))
                        ->whereDate('date', $toDate)
                        ->get()
                        ->keyBy('employee_id');

                    $rows = $employees->map(function ($employee) use ($attendanceMap, $designationMap, $lineMap, $workingPlaceMap, $toDate) {
                        $attendance = $attendanceMap->get($employee->id);

                        $other = is_array($employee->other_information)
                            ? $employee->other_information
                            : json_decode($employee->other_information ?? '{}', true);
                        $profile = data_get($other, 'profile', []);
                        $workingPlaceId = data_get($profile, 'working_place_id') ?? $employee->working_place_id;

                        return [
                            'section_id' => $employee->section_id,
                            'employee_id' => $employee->employee_id,
                            'name' => $employee->name,
                            'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                            'floor' => $workingPlaceMap->get($workingPlaceId, 'N/A'),
                            'line' => $lineMap->get($employee->floor_line_id, 'N/A'),
                            'date' => $toDate,
                            'in_time' => $attendance->in_time ?? '0',
                            'out_time' => $attendance->out_time ?? '0',
                        ];
                    })->values();
                } else {
                    $rows = collect();

                    foreach ($employees as $employee) {
                        $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $fromDate, $toDate);
                        $attendanceRows = collect($pack['attendance'] ?? []);

                        $other = is_array($employee->other_information)
                            ? $employee->other_information
                            : json_decode($employee->other_information ?? '{}', true);
                        $profile = data_get($other, 'profile', []);
                        $workingPlaceId = data_get($profile, 'working_place_id') ?? $employee->working_place_id;

                        $absentRows = $attendanceRows
                            ->filter(fn ($row) => strtolower((string) data_get($row, 'status_key', '')) === 'absent')
                            ->map(function ($row) use ($employee, $designationMap, $lineMap, $workingPlaceMap, $workingPlaceId) {
                                $rawDate = (string) data_get($row, 'date', '');
                                try {
                                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $rawDate)->toDateString();
                                } catch (\Throwable $e) {
                                    $date = null;
                                }

                                return [
                                    'section_id' => $employee->section_id,
                                    'employee_id' => $employee->employee_id,
                                    'name' => $employee->name,
                                    'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                                    'floor' => $workingPlaceMap->get($workingPlaceId, 'N/A'),
                                    'line' => $lineMap->get($employee->floor_line_id, 'N/A'),
                                    'date' => $date,
                                    'in_time' => data_get($row, 'in_time', '0'),
                                    'out_time' => data_get($row, 'out_time', '0'),
                                ];
                            })
                            ->filter(fn ($row) => !empty($row['date']));

                        $rows = $rows->merge($absentRows);
                    }

                    $rows = $rows
                        ->sortBy(function ($row) {
                            return sprintf(
                                '%s|%s|%s',
                                (string) data_get($row, 'section_id', ''),
                                (string) data_get($row, 'date', ''),
                                (string) data_get($row, 'name', '')
                            );
                        })
                        ->values();
                }

                return view('hr::reports.absent-report-print', compact(
                    'request', 'rows', 'sectionMap', 'fromDate', 'toDate', 'isSingleDay'
                ));
            }

            if ($type === 'PM') {
                $attendanceRecords = HrAttendance::query()
                    ->whereIn('employee_id', $employees->pluck('id'))
                    ->whereBetween('date', [$from, $to])
                    ->where(function($query) {
                        $query->whereNull('in_time')
                              ->orWhereNull('out_time');
                    })
                    ->orderBy('date', 'asc')
                    ->get();

                if ($attendanceRecords->isEmpty()) {
                    $attendanceRecords = collect();
                }

                $employeeMap = $employees->keyBy('id');
                $attendanceBySection = $attendanceRecords->groupBy(function ($record) use ($employeeMap) {
                    $emp = $employeeMap->get($record->employee_id);
                    return $emp ? $emp->section_id : 'unknown';
                });

                return view('hr::reports.attendance-missing-report-print', compact(
                    'request', 'employees', 'attendanceRecords', 'attendanceBySection', 'sectionMap',
                    'designationMap', 'lineMap', 'workingPlaceMap', 'employeeMap', 'from', 'to'
                ));
            }

            if ($type === 'AS') {
                $days = collect();
                $cursor = \Carbon\Carbon::parse($from)->startOfDay();
                $end = \Carbon\Carbon::parse($to)->startOfDay();
                while ($cursor->lte($end)) {
                    $days->push($cursor->copy()->toDateString());
                    $cursor->addDay();
                }

                $attendanceStatusByEmployee = $employees->mapWithKeys(function ($employee) use ($from, $to) {
                    $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $from, $to);
                    $rows = collect($pack['attendance'] ?? []);

                    $statusByDate = [];
                    $totalP = 0;
                    $totalHD = 0;
                    $totalL = 0;

                    foreach ($rows as $row) {
                        try {
                            $dateKey = \Carbon\Carbon::createFromFormat('d-m-Y', (string) data_get($row, 'date'))->format('Y-m-d');
                        } catch (\Throwable $e) {
                            continue;
                        }

                        $statusKey = strtolower((string) data_get($row, 'status_key', ''));
                        $code = 'A';

                        if ($statusKey === 'leave') {
                            $code = 'L';
                            $totalL++;
                        } elseif ($statusKey === 'holiday' || $statusKey === 'weekend') {
                            $code = 'HD';
                            $totalHD++;
                        } elseif ($statusKey === 'absent') {
                            $code = 'A';
                        } else {
                            // Treat present-like statuses (present/late/pm/eo/etc.) as present for this matrix.
                            $code = 'P';
                            $totalP++;
                        }

                        $statusByDate[$dateKey] = $code;
                    }

                    return [
                        $employee->id => [
                            'status_by_date' => $statusByDate,
                            'p' => $totalP,
                            'hd' => $totalHD,
                            'l' => $totalL,
                        ],
                    ];
                });

                return view('hr::reports.attendance-status-report-print', compact(
                    'request', 'employees', 'from', 'to', 'sectionMap', 'days', 'attendanceStatusByEmployee'
                ));
            }

            return view('hr::reports.attendance-report-print', compact(
                'request', 'employees', 'from', 'to',
                'sectionMap', 'subSectionMap', 'designationMap', 'shiftMap', 'attendanceByEmployee', 'dateLabel'
            ));
        }

        return view('hr::reports.attendance-report', [
            'reportKey'        => $report,
            'reportTitle'      => config('hr.reports.' . $report),
            'options'          => $options,
            'attendanceTypes'  => $attendanceTypes,
            'request'          => $request,
        ]);
    }

    private function attendanceWithOtReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();

        if ($request->boolean('print')) {
            $reportDate = (string) ($request->input('date') ?: now()->toDateString());

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
            $designationMap = collect($options['designations'] ?? [])->pluck('name', 'id');

            $attendanceByUser = HrAttendance::query()
                ->whereIn('employee_id', $employees->pluck('id'))
                ->whereDate('date', $reportDate)
                ->get()
                ->keyBy('employee_id');

            $englishRequest = clone $request;
            $englishRequest->merge([
                'language' => 'en',
            ]);
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee(null, $englishRequest, null, null, null, null); 

            $rows = $employees->map(function ($employee) use ($request, $attendanceByUser, $employeeDataFn, $sectionMap, $designationMap, $reportDate) {
                $att = $attendanceByUser->get($employee->id);

                $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                    $employee->id,
                    $reportDate,
                    $reportDate
                );
                $summary = $pack['summary'] ?? [];
                $attendanceRow = collect($pack['attendance'] ?? [])->first();

                $employeeData = $employeeDataFn($employee, $request ?? null, null, null, null, null);

                $late = (int) (($summary['totalLate'] ?? 0)
                    + ($summary['totalLEO'] ?? 0)
                    + ($summary['totalLPM'] ?? 0));

                $status = (string) ($att->status ?? data_get($attendanceRow, 'status', ''));
                if ($status === '') {
                    $status = $att && $att->in_time ? 'P' : 'A';
                }

                $otHours = (float) ($att->compliance_ot ?? data_get($attendanceRow, 'compliance_ot', 0));
                return [
                    'section_id' => $employee->section_id,
                    'section' => $employeeData['section'] ?? $sectionMap->get($employee->section_id, 'N/A'),
                    'card_no' => $employee->employee_id,
                    'name' => $employeeData['employee_name'] ?? $employee->name,
                    'designation' => $employeeData['designation'] ?? $designationMap->get($employee->designation_id, 'N/A'),
                    'in_time' => $att->in_time ?? data_get($attendanceRow, 'in_time'),
                    'out_time' => $att->out_time ?? data_get($attendanceRow, 'out_time'),
                    'late' => $late,
                    'ot_hours' => $otHours,
                    'status' => strtoupper((string) $status),
                ];
            });

            return view('hr::reports.attendance-with-ot-print', [
                'request' => $request,
                'rows' => $rows,
                'reportDate' => $reportDate,
            ]);
        }

        return view('hr::reports.attendance-with-ot', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options' => $options,
            'request' => $request,
        ]);
    }

    private function monthlyLateReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();

        if ($request->boolean('print')) {
            $from = (string) ($request->input('from') ?: now()->startOfMonth()->toDateString());
            $to = (string) ($request->input('to') ?: now()->endOfMonth()->toDateString());

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
            $shiftMap = HrShift::query()->get(['id', 'name', 'start_time', 'late_allow_time'])->keyBy('id');

            $lateByEmployee = collect();

            foreach ($employees as $employee) {
                $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $from, $to);
                $attendanceRows = collect($pack['attendance'] ?? []);

                $employeeData = $employeeDataFn($employee, $request ?? null, null, null, null, null);
                $shift = $shiftMap->get($employee->shift_id);

                $lateRows = $attendanceRows
                    ->filter(function ($row) {
                        return in_array((string) data_get($row, 'status_key', ''), [
                            'late',
                            'late_and_early_exit',
                            'late_and_punch_missing',
                        ], true);
                    })
                    ->map(function ($row) use ($shift, $shiftMap) {
                        $inTimeRaw = (string) data_get($row, 'in_time', '');
                        $outTimeRaw = (string) data_get($row, 'out_time', '');
                        $dateRaw = (string) data_get($row, 'date', '');
                        $rowShiftId = data_get($row, 'shift_id');
                        $rowShift = $rowShiftId ? $shiftMap->get($rowShiftId) : null;
                        $effectiveShift = $rowShift ?: $shift;

                        $dateObj = null;
                        try {
                            $dateObj = Carbon::createFromFormat('d-m-Y', $dateRaw);
                        } catch (\Throwable $e) {
                            return null;
                        }

                        $lateMinute = 0;
                        if ($inTimeRaw !== '' && $inTimeRaw !== '-') {
                            try {
                                $inClock = Carbon::parse($inTimeRaw)->format('H:i:s');
                                $inTime = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $inClock);

                                $lateStart = null;
                                if ($effectiveShift && filled($effectiveShift->late_allow_time)) {
                                    $lateCutoffClock = Carbon::parse((string) $effectiveShift->late_allow_time)->format('H:i:s');
                                    $lateStart = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $lateCutoffClock);
                                } elseif ($effectiveShift && filled($effectiveShift->start_time)) {
                                    $shiftStartClock = Carbon::parse((string) $effectiveShift->start_time)->format('H:i:s');
                                    $lateStart = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $shiftStartClock);
                                }

                                if ($lateStart) {
                                    if ($inTime->greaterThan($lateStart)) {
                                        $lateMinute = $lateStart->diffInMinutes($inTime);
                                    }
                                }
                            } catch (\Throwable $e) {
                                $lateMinute = 0;
                            }
                        }

                        return [
                            'date' => $dateObj->format('d/m/Y'),
                            'shift' => $effectiveShift->name ?? '-',
                            'in_time' => ($inTimeRaw !== '' && $inTimeRaw !== '-') ? Carbon::parse($inTimeRaw)->format('h:i:sA') : '-',
                            'out_time' => ($outTimeRaw !== '' && $outTimeRaw !== '-') ? Carbon::parse($outTimeRaw)->format('h:i:sA') : '-',
                            'late_minute' => $lateMinute,
                        ];
                    })
                    ->filter()
                    ->values();

                if ($lateRows->isEmpty()) {
                    continue;
                }

                $lateByEmployee->push([
                    'section_id' => $employee->section_id,
                    'section' => $employeeData['section'] ?? 'N/A',
                    'card_no' => $employee->employee_id,
                    'name' => $employeeData['employee_name'] ?? $employee->name,
                    'designation' => $employeeData['designation'] ?? 'N/A',
                    'doj' => $employee->join_date,
                    'late_rows' => $lateRows,
                    'total_late_days' => $lateRows->count(),
                ]);
            }

            return view('hr::reports.monthly-late-report-print', [
                'request' => $request,
                'from' => $from,
                'to' => $to,
                'lateBySection' => $lateByEmployee->groupBy('section_id'),
            ]);
        }

        return view('hr::reports.monthly-late-report', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options' => $options,
            'request' => $request,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // DAILY MANPOWER REPORT (NAME WISE)
    // ──────────────────────────────────────────────────────────────────

    private function dailyManpowerReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();

        if ($request->boolean('print')) {
            $reportDate = (string) ($request->input('date') ?: now()->toDateString());

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            $sectionMap = collect($options['sections'] ?? [])->pluck('name', 'id');
            $rows = collect();

            $grand = [
                'female' => 0,
                'male' => 0,
                'manpower_total' => 0,
                'leave' => 0,
                'present' => 0,
                'present_total' => 0,
                'absent' => 0,
                'others_total' => 0,
            ];

            $employees->groupBy('section_id')->each(function ($sectionEmployees, $sectionId) use (&$rows, &$grand, $sectionMap, $reportDate) {
                $female = 0;
                $male = 0;
                $leave = 0;
                $present = 0;
                $absent = 0;

                foreach ($sectionEmployees as $employee) {
                    $gender = strtolower(trim((string) ($employee->sex ?? '')));
                    if (in_array($gender, ['female', 'f'], true)) {
                        $female++;
                    } else {
                        $male++;
                    }

                    $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                        $employee->id,
                        $reportDate,
                        $reportDate
                    );
                    $summary = $pack['summary'] ?? [];

                    $present += (int) (($summary['totalPresent'] ?? 0)
                        + ($summary['totalLate'] ?? 0)
                        + ($summary['totalEO'] ?? 0)
                        + ($summary['totalPM'] ?? 0)
                        + ($summary['totalLEO'] ?? 0)
                        + ($summary['totalLPM'] ?? 0));
                    $leave += (int) ($summary['totalLeave'] ?? 0);
                    $absent += (int) ($summary['totalAbsent'] ?? 0);
                }

                $manpowerTotal = $female + $male;
                $presentTotal = $leave + $present;
                $othersTotal = $absent;

                $rows->push([
                    'section' => $sectionMap->get($sectionId, 'N/A'),
                    'female' => $female,
                    'male' => $male,
                    'manpower_total' => $manpowerTotal,
                    'leave' => $leave,
                    'present' => $present,
                    'present_total' => $presentTotal,
                    'absent' => $absent,
                    'others_total' => $othersTotal,
                ]);

                $grand['female'] += $female;
                $grand['male'] += $male;
                $grand['manpower_total'] += $manpowerTotal;
                $grand['leave'] += $leave;
                $grand['present'] += $present;
                $grand['present_total'] += $presentTotal;
                $grand['absent'] += $absent;
                $grand['others_total'] += $othersTotal;
            });

            return view('hr::reports.daily-manpower-report-print', [
                'request' => $request,
                'rows' => $rows,
                'reportDate' => $reportDate,
                'grand' => $grand,
            ]);
        }

        return view('hr::reports.daily-manpower-report', [
            'reportKey'   => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options'     => $options,
            'request'     => $request,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // MEAL (TIFFIN / DINER / NIGHT) REPORT
    // ──────────────────────────────────────────────────────────────────

    private function mealReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $mealTypes = [
            'tiffin' => 'Tiffin',
            'dinner' => 'Dinner / Diner',
            'night'  => 'Night',
        ];
        $reportTypes = [
            'details' => 'Details',
            'summary' => 'Summary',
        ];

        if ($request->boolean('print')) {
            $date     = $request->input('date') ?: now()->toDateString();
            $mealType = $request->input('meal_type', 'tiffin');
            if (!array_key_exists($mealType, $mealTypes)) {
                $mealType = 'tiffin';
            }
            $reportType = $request->input('report_type', 'details');

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            $attendanceMap = HrAttendance::query()
                ->whereIn('employee_id', $employees->pluck('id'))
                ->whereDate('date', $date)
                ->get()
                ->keyBy('employee_id');

            $sectionMap     = collect($options['sections'])->pluck('name', 'id');
            $subSectionMap  = collect($options['subSections'])->pluck('name', 'id');
            $designationMap = collect($options['designations'])->pluck('name', 'id');
            $shiftMap       = HrShift::query()->pluck('name', 'id');
            $designationInfoMap = HrDesignation::query()->get()->keyBy('id');

            // Meal eligibility: check shift meal options
            $shifts = HrShift::query()->get()->keyBy('id');

            return view('hr::reports.meal-report-print', compact(
                'request', 'employees', 'attendanceMap', 'date',
                'mealType', 'reportType', 'mealTypes', 'reportTypes',
                'sectionMap', 'subSectionMap', 'designationMap', 'shiftMap', 'shifts', 'designationInfoMap'
            ) + [
                'dateLabel'     => \Carbon\Carbon::parse($date)->format('d-M-Y'),
                'mealTypeLabel' => $mealTypes[$mealType],
            ]);
        }

        return view('hr::reports.meal-report', [
            'reportKey'   => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options'     => $options,
            'mealTypes'   => $mealTypes,
            'reportTypes' => $reportTypes,
            'request'     => $request,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // BONUS SHEET
    // ──────────────────────────────────────────────────────────────────

    public function bonusSheetScreen(Request $request, string $report)
    {
        // 1. Options and Static Data
        $options = $this->employeeReportOptions();
        $bonusTitles = HrBonusTitle::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'bn_title']);

        // Only two bonus types
        $bonusCategories = [
            'fixed'      => 'Fixed',
            'production' => 'Production',
        ];

        // Only two report types
        $reportTypes = [
            'details' => 'Details',
            'summary' => 'Summary',
        ];

        // 2. Handle Bonus Print Request
        if ($request->boolean('print')) {
            return $this->printBonusSheetByCategory($request, $options, $bonusCategories);
        }

        // 3. Default: show bonus sheet setup screen
        return view('hr::reports.bonus-sheet', [
            'reportKey'       => $report,
            'reportTitle'     => config('hr.reports.' . $report),
            'options'         => $options,
            'bonusTitles'     => $bonusTitles,
            'bonusCategories' => $bonusCategories,
            'reportTypes'     => $reportTypes,
            'request'         => $request,
        ]);
    }

    private function printBonusSheetByCategory(Request $request, array $options, array $bonusCategories)
    {
        $category = (string) $request->input('bonus_category', 'fixed');
        abort_unless(array_key_exists($category, $bonusCategories), 400, 'Unsupported bonus category.');

        $upToDate = $request->input('up_to_date') ?: now()->toDateString();
        $fromDate = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $toDate   = $request->input('to') ?: ($category === 'fixed' ? $upToDate : now()->toDateString());
        $reportType = $request->input('report_type', 'details');
        $language   = $request->input('language', 'en');

        $bonusTitleId = $request->input('bonus_title');
        $bonusTitle   = null;
        $policies     = collect();
        if (filled($bonusTitleId)) {
            $bonusTitle = HrBonusTitle::find($bonusTitleId);
            abort_unless($bonusTitle, 404, 'Bonus title not found');

            $policies = HrBonusPolicy::query()
                ->where('bonus_title_id', $bonusTitle->id)
                ->where('status', 'active')
                ->get();

            if ($policies->isEmpty()) {
                $policies = HrBonusPolicy::query()
                    ->where('bonus_title_id', $bonusTitle->id)
                    ->get();
            }
        }

        $employees = $this->employeeReportQuery($request)
            ->orderBy('department_id')
            ->orderBy('section_id')
            ->orderBy('name')
            ->get();

        [$bonusData, $hasPctPolicy] = $this->calculateBonusData($employees, $policies, $upToDate);

        // Only show employees with a matched policy
        $employees = $employees->filter(fn ($emp) => $bonusData[$emp->id]['policy'] !== null)->values();

        $departmentMap  = collect($options['departments'])->pluck('name', 'id');
        $sectionMap     = collect($options['sections'])->pluck('name', 'id');
        $subSectionMap  = collect($options['subSections'])->pluck('name', 'id');
        $designationMap = HrDesignation::query()->pluck('name', 'id');
        $lineMap = collect($options['lines'])->mapWithKeys(fn ($row) => [
            $row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : '')),
        ]);

        $view = $category === 'production'
            ? 'hr::reports.bonus-sheet-production-print'
            : 'hr::reports.bonus-sheet-fixed-print';

        return view($view, [
            'request'        => $request,
            'employees'      => $employees,
            'category'       => $category,
            'upToDate'       => $upToDate,
            'fromDate'       => $fromDate,
            'toDate'         => $toDate,
            'reportType'     => $reportType,
            'bonusTitle'     => $bonusTitle,
            'bonusData'      => $bonusData,
            'hasPctPolicy'   => $hasPctPolicy,
            'departmentMap'  => $departmentMap,
            'sectionMap'     => $sectionMap,
            'subSectionMap'  => $subSectionMap,
            'designationMap' => $designationMap,
            'lineMap'        => $lineMap,
            'withPicture'    => $request->boolean('with_picture'),
            'language'       => $language,
            'upToDateLabel'  => \Carbon\Carbon::parse($upToDate)->format('d-M-Y'),
            'fromLabel'      => \Carbon\Carbon::parse($fromDate)->format('d-M-Y'),
            'toLabel'        => \Carbon\Carbon::parse($toDate)->format('d-M-Y'),
            'categoryLabel'  => $bonusCategories[$category] ?? 'Fixed',
        ]);
    }

    private function calculateBonusData($employees, $policies, string $upToDate): array
    {
        $referenceDate = \Carbon\Carbon::parse($upToDate);
        $bonusData     = [];
        $hasPctPolicy  = false;

        foreach ($employees as $employee) {
            $employeeSalary = hr_employee_salary($employee);
            $gross = (float) ($employeeSalary['gross'] ?? $employee->gross_salary ?? 0);
            $basic = (float) ($employeeSalary['basic'] ?? $employee->basic_salary ?? 0);
            $productionBase = (float) (
                data_get($employeeSalary, 'production_salary')
                ?? data_get($employeeSalary, 'production')
                ?? data_get($employeeSalary, 'total_production')
                ?? 0
            );

            $joiningDate   = $employee->join_date ? \Carbon\Carbon::parse($employee->join_date) : null;
            $serviceMonths = $joiningDate
                ? max(0, (int) $joiningDate->diffInMonths($referenceDate, false))
                : null;

            $matchedPolicy = $policies->filter(function ($policy) use ($employee, $serviceMonths) {
                $designationMatch = !$policy->designation_id
                    || (int) $policy->designation_id === (int) $employee->designation_id;
                $sectionMatch = !$policy->section_id
                    || (int) $policy->section_id === (int) $employee->section_id;

                $monthFrom = is_null($policy->month_from) ? null : (int) $policy->month_from;
                $monthTo   = is_null($policy->month_to)   ? null : (int) $policy->month_to;

                $monthMatch = true;
                if (!is_null($serviceMonths)) {
                    if (!is_null($monthFrom)) {
                        $monthMatch = $monthMatch && ($serviceMonths >= $monthFrom);
                    }
                    if (!is_null($monthTo)) {
                        $monthMatch = $monthMatch && ($serviceMonths <= $monthTo);
                    }
                } elseif (!is_null($monthFrom) || !is_null($monthTo)) {
                    $monthMatch = false;
                }

                return $designationMatch && $sectionMatch && $monthMatch;
            })->sortByDesc(function ($policy) {
                return (is_null($policy->designation_id) ? 0 : 4)
                    + (is_null($policy->section_id)      ? 0 : 2)
                    + (is_null($policy->month_from)       ? 0 : 1)
                    + (is_null($policy->month_to)         ? 0 : 1);
            })->first();

            $bonus       = 0.0;
            $amountType  = '';
            $salaryBasis = '';
            $percent     = null;

            if ($matchedPolicy) {
                $amountType  = strtolower($matchedPolicy->amount_type  ?? 'percent');
                $salaryBasis = strtolower($matchedPolicy->salary_basis ?? 'gross');

                $base = match ($salaryBasis) {
                    'basic'      => $basic,
                    'production' => $productionBase,
                    default      => $gross,
                };

                if ($amountType === 'fixed') {
                    $bonus   = (float) $matchedPolicy->amount;
                    $percent = null;
                } else {
                    $percent      = (float) $matchedPolicy->amount; // policy percentage
                    $bonus        = round($base * $percent / 100, 2);
                    $hasPctPolicy = true;
                }
            }

            $jobAge = 'N/A';
            if ($joiningDate) {
                $diff   = $joiningDate->diff($referenceDate);
                $jobAge = sprintf('%dy %dm %dd', $diff->y, $diff->m, $diff->d);
            }

            $bonusData[$employee->id] = [
                'policy'       => $matchedPolicy,
                'amount_type'  => $amountType,
                'salary_basis' => $salaryBasis,
                'gross'        => $gross,
                'basic'        => $basic,
                'percent'      => $percent,
                'bonus'        => $bonus,
                'job_age'      => $jobAge,
            ];
        }

        return [$bonusData, $hasPctPolicy];
    }

    // ──────────────────────────────────────────────────────────────────
    // SALARY REPORT
    // ──────────────────────────────────────────────────────────────────

    private function salaryReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $bonusTitles = HrBonusTitle::where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $reportTypes = [
            'fixed'                  => 'Fixed Salary',
            'production'             => 'Production Salary',
            'bonus'                  => 'Bonus Salary',
            'wages-salary-summary'   => 'Wages & Salary Summary',
        ];
        $paymentModes = HrEmployee::query()
            
            ->whereNotNull('salary_type')
            ->distinct()
            ->pluck('salary_type')
            ->filter()
            ->values();

        if ($request->boolean('print')) {
            $printPayload = $this->prepareSalaryReportPrintPayload($request, $options, $reportTypes);
            $printView = $this->resolveSalaryReportPrintView($printPayload['reportType']);

            return view($printView, $printPayload);
        }

        return view('hr::reports.salary-report', [
            'reportKey'    => $report,
            'reportTitle'  => config('hr.reports.' . $report),
            'options'      => $options,
            'bonusTitles'  => $bonusTitles,
            'reportTypes'  => $reportTypes,
            'paymentModes' => $paymentModes,
            'request'      => $request,
        ]);
    }

    private function resolveSalaryReportPrintView(string $reportType): string
    {
        return match ($reportType) {
            'wages-salary-summary' => 'hr::reports.salary-report-print-wages',
            'bonus' => 'hr::reports.salary-report-print-bonus',
            'production' => 'hr::reports.salary-report-print-production',
            default => 'hr::reports.salary-report-print-fixed',
        };
    }

    private function prepareSalaryReportPrintPayload(Request $request, array $options, array $reportTypes): array
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?: now()->toDateString();
        $reportType = $request->input('report_type', 'fixed');
        if (!array_key_exists($reportType, $reportTypes)) {
            $reportType = 'fixed';
        }

        $employees = $this->employeeReportQuery($request)
            ->orderBy('department_id')
            ->orderBy('section_id')
            ->orderBy('name')
            ->get();

        $departmentMap = collect($options['departments'])->pluck('name', 'id');
        $sectionMap = collect($options['sections'])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'])->pluck('name', 'id');
        $designationMap = HrDesignation::query()->pluck('name', 'id');
        $lineMap = collect($options['lines'])->mapWithKeys(fn ($r) => [
            $r->id => trim(($r->name ?? '') . (filled($r->slug ?? null) ? ' - ' . $r->slug : '')),
        ]);

        $fromMonth = Carbon::parse($from)->month;
        $fromYear = Carbon::parse($from)->year;
        $toMonth = Carbon::parse($to)->month;
        $toYear = Carbon::parse($to)->year;

        $salarySheets = collect();

        return [
            'request' => $request,
            'employees' => $employees,
            'salarySheets' => $salarySheets,
            'from' => $from,
            'to' => $to,
            'reportType' => $reportType,
            'reportTypes' => $reportTypes,
            'departmentMap' => $departmentMap,
            'sectionMap' => $sectionMap,
            'subSectionMap' => $subSectionMap,
            'designationMap' => $designationMap,
            'lineMap' => $lineMap,
            'withPicture' => $request->boolean('with_picture'),
            'language' => $request->input('language', 'en'),
            'fromLabel' => Carbon::parse($from)->format('d-M-Y'),
            'toLabel' => Carbon::parse($to)->format('d-M-Y'),
            'reportTypeLabel' => $reportTypes[$reportType],
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // PAY SLIP REPORT
    // ──────────────────────────────────────────────────────────────────

    private function paySlipReportScreen(Request $request, $report)
    {
        $options = $this->employeeReportOptions();
        $reportTypes = [
            'salary'   => 'Salary',
            'extra_ot' => 'Extra OT',
        ];
        $languages = [
            'bn' => 'Bangla',
            'en' => 'English',
        ];
        $currentYear = now()->year;
        $years = range($currentYear - 5, $currentYear + 1);
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];


        if ($request->boolean('print')) {
            $month = (int) $request->input('month', now()->month);
            $year = (int) $request->input('year', $currentYear);
            $from = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $to = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            // Salary/OT logic will go here (to be implemented)

            $monthLabel = $months[$month] ?? '';

            return view('hr::reports.payslip.pay-slip-print', compact(
                'request', 'employees', 'from', 'to', 'month', 'year', 'monthLabel', 'reportTypes', 'languages', 'options', 'months', 'years'
            ));
        }

        return view('hr::reports.payslip.pay-slip', [
            'options'     => $options,
            'reportTypes' => $reportTypes,
            'languages'   => $languages,
            'months'      => $months,
            'years'       => $years,
            'request'     => $request,
        ]);
    }

        /**
     * Individual Pay Slip Report (like Job Card)
     */
    public function individualPaySlipReport(Request $request)
    {
        $options = $this->employeeReportOptions();
        $reportTypes = [
            'salary'   => 'Salary',
            'extra_ot' => 'Extra OT',
        ];
        $languages = [
            'bn' => 'Bangla',
            'en' => 'English',
        ];
        $currentYear = now()->year;
        $years = range($currentYear - 5, $currentYear + 1);
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        if ($request->boolean('print')) {
            $month = (int) $request->input('month', now()->month);
            $year = (int) $request->input('year', $currentYear);
            $from = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $to = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

            $employees = $this->employeeReportQuery($request)
                ->orderBy('section_id')
                ->orderBy('name')
                ->get();

            // Salary/OT logic will go here (to be implemented)

            return view('hr::reports.payslip.pay-slip-print', compact(
                'request', 'employees', 'from', 'to', 'month', 'year', 'reportTypes', 'languages', 'options', 'months', 'years'
            ));
        }

        return view('hr::reports.payslip.pay-slip', [
            'options'     => $options,
            'reportTypes' => $reportTypes,
            'languages'   => $languages,
            'months'      => $months,
            'years'       => $years,
            'request'     => $request,
        ]);
    }
}





