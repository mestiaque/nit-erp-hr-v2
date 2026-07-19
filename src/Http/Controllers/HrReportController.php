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
use ME\Hr\Models\HrLeaveInfo;
use ME\Hr\Models\HrLock;
use ME\Hr\Models\HrProductionBonus;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrSubSection;
use ME\Hr\Models\HrSex;
use ME\Hr\Models\HrWorkingPlace;
use ME\Hr\Services\SalaryReportService;




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
            'shift', 'working_place', 'line_number', 'salary_type', 'employee_status', 'designation', 'language', 'report_type', 'print'])
        ) {
            [$columns, $rows] = $this->productionJobCardReport($request);
            $showTable = true;
        }

        if ($request->boolean('print')) {
            $groupBy = $this->resolveGroupBy($request);
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows(collect($rows), $groupBy, $optionMaps, fn (array $row) => $row['employee']);
            return view('hr::reports.pro-job-card-print', compact('reportKey', 'reportTitle', 'options', 'request', 'columns', 'rows', 'groups', 'groupLabel', 'groupBy'));
        }

        $groupByOptions = self::GROUP_BY_OPTIONS;
        return view('hr::reports.pro-job-card', compact('reportKey', 'reportTitle', 'options', 'request', 'columns', 'rows', 'showTable', 'groupByOptions'));
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
        // Increment date filtering is done inside monthlyIncrementData (join date OR increment date).
        // Stripping from/to here so employeeReportQuery does not pre-filter by join_date only.
        $queryRequest = new Request(array_diff_key($payload, array_flip(['from', 'to'])));
        $employees = $this->employeeReportQuery($queryRequest)
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

            // Audit trail for the Lock Management page — the per-row is_locked flag on
            // hr_employee_salary_increments (set in upsertIncrementRecord) is what reports
            // actually filter on; this row just records that a bulk lock action happened.
            $effective = Carbon::parse($payload['effective_date']);
            HrLock::updateOrCreate(
                [
                    'module' => 'increment',
                    'lock_year' => $effective->year,
                    'lock_month' => $effective->month,
                    'department_id' => $payload['department'] ?? null,
                ],
                [
                    'is_locked' => true,
                    'locked_at' => now(),
                    'locked_by' => Auth::id(),
                ]
            );
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
            'pro-job-card' => $this->productionJobCardReport(new Request),
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
            'attendance-with-ot' => 'Attendance With OT',
            'increment' => 'Increment',
            'increment-summary' => 'Increment Report',
        ];

        $reportType = (string) $request->input('report_type', 'recruitment');
        if (!array_key_exists($reportType, $reportTypes)) {
            $reportType = 'recruitment';
        }

        $incrementPercent = (float) $request->input('increment_percent', 0);
        $effectiveDate = $request->input('effective_date');

        // For increment report types, date filtering happens inside monthlyIncrementData
        // (join date OR increment date in range). Don't pre-filter by join_date here.
        $isIncrementReport = in_array($reportType, ['increment', 'increment-summary']);
        $queryRequest = $isIncrementReport
            ? new Request($request->except(['from', 'to']))
            : $request;

        $employees = $this->employeeReportQuery($queryRequest)
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
            if ($reportType === 'attendance-with-ot') {
                return $this->attendanceWithOtReportScreen($request, 'attendance-with-ot');
            }

            // Every report_type here produces per-employee rows carrying an 'employee' key
            // (recruitment's summary_rows table and increment's grand-total row are the two
            // exceptions — both are pre-aggregated and stay untouched by grouping).
            $groupBy = $this->resolveGroupBy($request);
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows(collect($data['rows'] ?? []), $groupBy, $optionMaps, fn (array $row) => $row['employee']);

            return view('hr::reports.monthly-print', [
                'reportKey' => $report,
                'reportTitle' => config('hr.reports.' . $report),
                'reportType' => $reportType,
                'reportTypeLabel' => $reportTypes[$reportType],
                'request' => $request,
                'options' => $options,
                'data' => $data,
                'groups' => $groups,
                'groupLabel' => $groupLabel,
                'groupBy' => $groupBy,
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
            'groupByOptions' => self::GROUP_BY_OPTIONS,
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
                'employee' => $employee,
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
                    'employee' => $employee,
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
                    'employee' => $employee,
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

        // Date filter: exact range the user provides.
        // Show employees whose join date OR last increment date falls within [from, to].
        // If only from is given: join date >= from. If neither: no date filter.
        $filterFrom = $request->filled('from') ? \Carbon\Carbon::parse($request->from)->startOfDay() : null;
        $filterTo   = $request->filled('to')   ? \Carbon\Carbon::parse($request->to)->endOfDay()     : null;

        $inRange = function ($date) use ($filterFrom, $filterTo): bool {
            if (blank($date)) return false;
            $c = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
            if ($filterFrom && $c->lt($filterFrom)) return false;
            if ($filterTo   && $c->gt($filterTo))   return false;
            return true;
        };

        $rows = $employees
            ->map(function (HrEmployee $employee) use ($incrementMap, $withRemarks, $filterFrom, $filterTo, $inRange) {
                $increment = $incrementMap[$employee->id] ?? null;

                // increment-summary: only show employees with a saved increment record
                if ($withRemarks && !$increment) {
                    return null;
                }

                // Apply date filter only when at least one bound is given
                if ($filterFrom || $filterTo) {
                    $joinDate       = $employee->join_date;
                    $lastIncDateVal = data_get($increment, 'increment_date', data_get($increment, 'date'));

                    if (!$inRange($joinDate) && !$inRange($lastIncDateVal)) {
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

                // Use effective salary (base salary + any existing increments) as the basis
                $sal         = function_exists('hr_employee_salary') ? hr_employee_salary($employee) : [];
                $sal         = \ME\Hr\Models\HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $employee->id);
                $grossSalary = (float) ($sal['gross'] ?? $employee->gross_salary ?? 0);

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
                    'employee' => $employee,
                    'employee_row_id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'service_length' => $serviceLength,
                    'department' => $departmentMap->get($employee->department_id, 'N/A'),
                    'section' => $sectionMap->get($employee->section_id, 'N/A'),
                    'sub_section' => $subSectionMap->get($subSectionId, 'N/A'),
                    'designation' => $designationMap->get($employee->designation_id, 'N/A'),
                    'grade' => $employee->grade ?? optional($employee->designation)->grade ?? 'N/A',
                    'classification' => $classificationMap->get($employee->classification_id, 'N/A'),
                    'line_block' => $lineMap->get($employee->floor_line_id, 'N/A'),
                    'join_date' => $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d-M-Y') : 'N/A',
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
            if (Schema::hasColumn($table, 'is_locked')) {
                $row->is_locked = true;
                $row->locked_at = now();
                $row->locked_by = Auth::id();
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
            $groupBy = $this->resolveGroupBy($request);
            $optionMaps = $this->groupByOptionMaps($options);

            if ($reportType === 'database') {
                [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);
                return view('hr::reports.employee-database-print', [
                    'employees' => $employees,
                    'groups' => $groups,
                    'groupLabel' => $groupLabel,
                    'groupBy' => $groupBy,
                    'request' => $request,
                    'options' => $options,
                ]);
            } elseif ($reportType === 'details') {
                $detailsRows = $this->employeeDetailsRows($employees, $options);
                [$groups, $groupLabel] = $this->groupEmployeeRows($detailsRows, $groupBy, $optionMaps, fn ($row) => $row['employee']);
                return view('hr::reports.employee-details-print', [
                    'detailsRows' => $detailsRows,
                    'groups' => $groups,
                    'groupLabel' => $groupLabel,
                    'groupBy' => $groupBy,
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
            'groupByOptions' => self::GROUP_BY_OPTIONS,
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
                'employee'         => $employee,
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
                'grade'            => $employee->grade ?? optional($employee->designation)->grade ?? 'N/A',
                'shift'            => $shiftMap->get($employee->shift_id, 'N/A'),
                'weekend'          => data_get($profile, 'weekend', $employee->weekend ?? 'N/A'),
                'contact_no'       => $employee->mobile ?? 'N/A',
                'sex'              => $employee->gender ?? 'N/A',
            ]);
        }
        return $rows;
    }

    private function employeeReportQuery(Request $request)
    {
        $query = HrEmployee::query();
        // dd($request->all());

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
            $values = array_filter(array_map('intval', (array) $request->classification));
            if (!empty($values)) {
                $query->whereIn('classification_id', $values);
            }
        }

        if ($request->filled('department')) {
            $values = array_filter(array_map('intval', (array) $request->department));
            if (!empty($values)) {
                $query->whereIn('department_id', $values);
            }
        }

        if ($request->filled('section')) {
            $values = array_filter(array_map('intval', (array) $request->section));
            if (!empty($values)) {
                $query->whereIn('section_id', $values);
            }
        }

        if ($request->filled('sub_section')) {
            $subSectionCol = Schema::hasColumn((new HrEmployee())->getTable(), 'sub_section_id') ? 'sub_section_id'
                : (Schema::hasColumn((new HrEmployee())->getTable(), 'hr_sub_section_id') ? 'hr_sub_section_id' : null);
            if ($subSectionCol) {
                $values = array_filter(array_map('intval', (array) $request->sub_section));
                if (!empty($values)) {
                    $query->whereIn($subSectionCol, $values);
                }
            }
        }

        if ($request->filled('working_place')) {
            $wpCol = Schema::hasColumn((new HrEmployee())->getTable(), 'working_place_id') ? 'working_place_id'
                : (Schema::hasColumn((new HrEmployee())->getTable(), 'hr_working_place_id') ? 'hr_working_place_id' : null);
            if ($wpCol) {
                $values = array_filter(array_map('intval', (array) $request->working_place));
                if (!empty($values)) {
                    $query->whereIn($wpCol, $values);
                }
            }
        }

        if ($request->filled('shift')) {
            $values = array_filter(array_map('intval', (array) $request->shift));
            if (!empty($values)) {
                $query->whereIn('shift_id', $values);
            }
        }

        if ($request->filled('today_shifts')) {
            $shiftIds = array_filter(array_map('intval', (array) $request->today_shifts));
            if (!empty($shiftIds)) {
                $query->whereIn('shift_id', $shiftIds);
            }
        }

        if ($request->filled('lastday_shifts')) {
            $shiftIds = array_filter(array_map('intval', (array) $request->lastday_shifts));
            if (!empty($shiftIds)) {
                $query->whereIn('shift_id', $shiftIds);
            }
        }

        if ($request->filled('line_number')) {
            $values = array_filter(array_map('intval', (array) $request->line_number));
            if (!empty($values)) {
                $query->whereIn('floor_line_id', $values);
            }
        }

        if ($request->filled('salary_type')) {
            $values = array_filter((array) $request->salary_type);
            if (!empty($values)) {
                $query->whereIn('salary_type', $values);
            }
        }

        if ($request->filled('designation')) {
            $values = array_filter(array_map('intval', (array) $request->designation));
            if (!empty($values)) {
                $query->whereIn('designation_id', $values);
            }
        }

        if ($request->filled('gender')) {
            $genderValues = (array) $request->gender;
            $sexIds = HrSex::query()
                ->whereIn('name', $genderValues)
                ->pluck('id')
                ->filter()
                ->values();
            if ($sexIds->isNotEmpty()) {
                $query->whereHas('basicInfo', function ($q) use ($sexIds) {
                    $q->whereIn('sex_id', $sexIds);
                });
            }
        }

        if ($request->filled('employee_status')) {
            $statuses = (array) $request->employee_status;
            $query->where(function ($builder) use ($statuses) {
                foreach ($statuses as $status) {
                    if ($status === 'regular') {
                        $builder->orWhere(function ($sq) {
                            $sq->whereNull('employment_status')
                                ->orWhere('employment_status', '')
                                ->orWhere('employment_status', 'regular');
                        });
                    } else {
                        $builder->orWhere(function ($sq) use ($status) {
                            $sq->where('employment_status', $status);
                            if ($status === 'lefty') {
                                $sq->orWhere('employment_status', 'left');
                            }
                            if ($status === 'resign') {
                                $sq->orWhere('employment_status', 'resigned');
                            }
                        });
                    }
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

    /**
     * Shared "Group By" option list used across report print screens — org-unit axes
     * an employee row can be grouped by. 'none' is always the flat/ungrouped case.
     */
    private const GROUP_BY_OPTIONS = [
        'none'                    => 'None (Flat List)',
        'classification'          => 'Classification',
        'department'              => 'Department',
        'section'                 => 'Section',
        'sub_section'             => 'Sub-Section',
        'designation'             => 'Designation',
        'shift'                   => 'Shift',
        'department_section'      => 'Department + Section',
        'department_designation'  => 'Department + Designation',
    ];

    /**
     * Resolves the requested group_by value against the known option list, falling
     * back to $default (per-report — 'none' for reports with no prior grouping,
     * or an already-hardcoded axis like 'section' for reports being made configurable
     * without changing their default appearance).
     */
    private function resolveGroupBy(Request $request, string $default = 'none'): string
    {
        $groupBy = (string) $request->input('group_by');
        return array_key_exists($groupBy, self::GROUP_BY_OPTIONS) ? $groupBy : $default;
    }

    private function groupByOptionMaps(array $options): array
    {
        return [
            'classification' => collect($options['classifications'] ?? [])->pluck('name', 'id')->all(),
            'department'      => collect($options['departments'] ?? [])->pluck('name', 'id')->all(),
            'section'         => collect($options['sections'] ?? [])->pluck('name', 'id')->all(),
            'sub_section'     => collect($options['subSections'] ?? [])->pluck('name', 'id')->all(),
            'designation'     => collect($options['designations'] ?? [])->pluck('name', 'id')->all(),
            'shift'           => collect($options['shifts'] ?? [])->pluck('name', 'id')->all(),
        ];
    }

    /**
     * Groups a collection of rows by the selected org-unit axis and returns
     * [groups, groupLabel closure]. $employeeResolver pulls the employee-like object
     * (with department_id/section_id/etc.) out of each row — rows may be the employee
     * model itself, an array with an 'employee' key, or an object with an ->employee
     * relation, depending on the report. 'none' collapses everything into a single
     * key 'all', so the caller's existing flat/ungrouped output is unchanged when no
     * grouping is selected — this is what keeps the rollout purely additive.
     */
    private function groupEmployeeRows($rows, string $groupBy, array $optionMaps, callable $employeeResolver): array
    {
        $keyOf = function ($row) use ($groupBy, $employeeResolver) {
            if ($groupBy === 'none') {
                return 'all';
            }
            $emp = $employeeResolver($row);
            $dept = (string) ($emp?->department_id ?? '');
            return match ($groupBy) {
                'classification' => (string) ($emp?->classification_id ?? ''),
                'department' => $dept,
                'section' => (string) ($emp?->section_id ?? ''),
                'sub_section' => (string) ($emp?->sub_section_id ?? ''),
                'designation' => (string) ($emp?->designation_id ?? ''),
                'shift' => (string) ($emp?->shift_id ?? ''),
                'department_section' => $dept . '|' . ($emp?->section_id ?? ''),
                'department_designation' => $dept . '|' . ($emp?->designation_id ?? ''),
                default => 'all',
            };
        };

        $groups = $rows->groupBy($keyOf);

        return [$groups, $this->groupLabelResolver($groupBy, $optionMaps)];
    }

    /**
     * The label half of groupEmployeeRows(), split out so callers that already have
     * their own group keys (e.g. SalaryReportService's own department/section loop)
     * can resolve display labels without needing groupEmployeeRows()'s row-grouping.
     */
    private function groupLabelResolver(string $groupBy, array $optionMaps): \Closure
    {
        return function (string $key) use ($groupBy, $optionMaps) {
            if (str_contains($key, '|')) {
                [$a, $b] = explode('|', $key, 2);
                return match ($groupBy) {
                    'department_section' => ($optionMaps['department'][$a] ?? 'N/A') . ' — ' . ($optionMaps['section'][$b] ?? 'N/A'),
                    'department_designation' => ($optionMaps['department'][$a] ?? 'N/A') . ' — ' . ($optionMaps['designation'][$b] ?? 'N/A'),
                    default => 'N/A',
                };
            }
            return $optionMaps[$groupBy][$key] ?? 'N/A';
        };
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
            $values = array_filter(array_map('intval', (array) $request->classification));
            if (!empty($values)) {
                $query->whereIn('classification_id', $values);
            }
        }

        if ($request->filled('department')) {
            $values = array_filter(array_map('intval', (array) $request->department));
            if (!empty($values)) {
                $query->whereIn('department_id', $values);
            }
        }

        if ($request->filled('section')) {
            $values = array_filter(array_map('intval', (array) $request->section));
            if (!empty($values)) {
                $query->whereIn('section_id', $values);
            }
        }

        if ($request->filled('subsection') && Schema::hasColumn((new HrEmployee())->getTable(), 'sub_section_id')) {
            $values = array_filter(array_map('intval', (array) $request->subsection));
            if (!empty($values)) {
                $query->whereIn('sub_section_id', $values);
            }
        }

        if ($request->filled('shift')) {
            $values = array_filter(array_map('intval', (array) $request->shift));
            if (!empty($values)) {
                $query->whereIn('shift_id', $values);
            }
        }

        if ($request->filled('working_place')) {
            $wpValues = array_filter((array) $request->working_place);
            if (!empty($wpValues)) {
                $query->where(function ($builder) use ($wpValues) {
                    if (Schema::hasColumn((new HrEmployee())->getTable(), 'working_place_id')) {
                        $builder->whereIn('working_place_id', array_map('intval', $wpValues));
                    }
                    if (Schema::hasColumn((new HrEmployee())->getTable(), 'location')) {
                        $builder->orWhere(function ($q2) use ($wpValues) {
                            foreach ($wpValues as $val) {
                                $q2->orWhere('location', 'like', '%' . trim((string) $val) . '%');
                            }
                        });
                    }
                });
            }
        }

        if ($request->filled('employee_status')) {
            $statuses = (array) $request->employee_status;
            $query->where(function ($builder) use ($statuses) {
                foreach ($statuses as $status) {
                    if ($status === 'regular') {
                        $builder->orWhere(function ($sq) {
                            $sq->whereNull('employment_status')
                                ->orWhere('employment_status', '')
                                ->orWhere('employment_status', 'regular');
                        });
                    } else {
                        $builder->orWhere(function ($sq) use ($status) {
                            $sq->where('employment_status', $status);
                            if ($status === 'lefty') {
                                $sq->orWhere('employment_status', 'left');
                            }
                            if ($status === 'resign') {
                                $sq->orWhere('employment_status', 'resigned');
                            }
                        });
                    }
                }
            });
        }

        $employees = $query->with(['designation', 'department', 'section', 'shift'])->naturalOrderById()->get();

        $options = [
            'classifications' => HrClassification::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'departments' => HrDepartment::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'sections' => HrSection::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'subsections' => HrSubSection::orderBy('name')->get(['id', 'name']),
            'shifts' => HrShift::orderBy('name')->get(['id', 'name']),
            'workingPlaces' => HrWorkingPlace::orderBy('name')->get(['id', 'name']),
            'designations' => HrDesignation::query()->orderBy('name')->get(['id', 'name']),
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
            'probaitionary-exten' => 'Probationary Extension',
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
            $reportType = (string) $request->input('report_type');
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

        // employee_id in hr_employee_salary_increments is the integer FK to hr_employees.id.
        // Never compare against card numbers (employee_id on hr_employees) — those are strings.
        $query = HrEmployeeSalaryIncrement::query()->orderBy($sortCol, 'desc');
        if ($hasUserId && $userIds->isNotEmpty()) {
            $query->whereIn('user_id', $userIds->all());
        } elseif ($hasEmployeeId && $userIds->isNotEmpty()) {
            $query->whereIn('employee_id', $userIds->all());
        } else {
            return $map;
        }
        $rows = $query->get();

        foreach ($employees as $employee) {
            $map[$employee->id] = $rows->first(function ($row) use ($employee) {
                return (int) ($row->user_id ?? $row->employee_id) === (int) $employee->id;
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

    private function productionJobCardReport(Request $request): array
    {
        $employees = $this->employeeReportQuery($request)
            ->whereNotNull('floor_line_id')
            ->with(['department', 'section', 'subSection', 'designation', 'workingPlace', 'shift', 'classification', 'floorLine'])
            ->orderBy('floor_line_id')
            ->naturalOrderById()
            ->get();

        $rows = $employees->map(function (HrEmployee $employee) {
            $other = is_array($employee->other_information)
                ? $employee->other_information
                : json_decode($employee->other_information ?? '{}', true);

            return [
                'employee'           => $employee,
                'line_number'        => $employee->floor_line_id,
                'employee_id'        => $employee->employee_id,
                'name'               => $employee->name,
                'designation_name'   => $employee->designation->name ?? null,
                'section_name'       => $employee->section->name ?? null,
                'department_name'    => $employee->department->name ?? null,
                'sub_section_name'   => $employee->subSection->name ?? data_get($other, 'profile.sub_section_name'),
                'working_place_name' => $employee->workingPlace->name ?? null,
                'shift_name'         => $employee->shift->name ?? null,
                'classification'     => $employee->classification->name ?? null,
                'join_date'          => $employee->join_date ?? null,
                'block_line'         => $employee->floorLine->line_name ?? null,
                'salary_type'        => $employee->salary_type,
                'employee_status'    => $employee->employment_status,
            ];
        });

        return [
            [
                'line_number', 'employee_id', 'name', 'designation_name',
                'section_name', 'department_name', 'sub_section_name',
                'working_place_name', 'shift_name', 'classification',
                'join_date', 'block_line', 'salary_type', 'employee_status'
            ],
            $rows
        ];
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
            $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
            $to   = $request->input('to')   ?: now()->endOfMonth()->toDateString();
            $reportType = (string) $request->input('report_type', 'job-card');
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

            // 'job-card'/'job-card-lock' had no grouping at all before (plain per-employee
            // page-per-employee list) — default 'none' there. The other four report_types
            // were always hardcoded Section-grouped (ot-summary additionally nests
            // Designation inside that, kept as-is) — default 'section' for those, so their
            // appearance is unchanged unless the user picks a different axis.
            $groupByDefault = in_array($reportType, ['job-card', 'job-card-lock'], true) ? 'none' : 'section';
            $groupBy = $this->resolveGroupBy($request, $groupByDefault);
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

            return view('hr::reports.job-card-report-print', compact(
                'request', 'employees', 'attendanceMap', 'dates',
                'from', 'to', 'reportType', 'reportTypes',
                'departmentMap', 'sectionMap', 'subSectionMap',
                'designationMap', 'classificationMap', 'lineMap', 'shiftMap',
                'groups', 'groupLabel', 'groupBy'
            ) + [
                'fromLabel' => \Carbon\Carbon::parse($from)->format('d-M-Y'),
                'toLabel'   => \Carbon\Carbon::parse($to)->format('d-M-Y'),
                'reportTypeLabel' => $reportTypes[$reportType],
                'groupByAxisLabel' => self::GROUP_BY_OPTIONS[$groupBy] === 'None (Flat List)' ? 'Group' : self::GROUP_BY_OPTIONS[$groupBy],
            ]);
        }

        return view('hr::reports.job-card-report', [
            'reportKey'   => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options'     => $options,
            'reportTypes' => $reportTypes,
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'request'     => $request,
        ]);
    }

    public function applyJobCardLock(Request $request)
    {
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to') ?: $from;

        $employees = $this->employeeReportQuery($request)->get(['id']);

        // Real enforcement: flip the same hr_attendances.is_locked flag that
        // AttendanceController/AttendanceMachineController check before any write —
        // a locked day becomes immutable, not just cosmetically labeled. Only existing
        // rows are locked here (a day with no row yet — a plain absence — has nothing
        // to lock; creating an empty placeholder row would wrongly flip its derived
        // status elsewhere from "Absent" to "Punch Missing", so that's deliberately
        // not done here).
        HrAttendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$from, $to])
            ->update(['is_locked' => true, 'locked_at' => now(), 'locked_by' => Auth::id()]);

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
            'LR' => 'Late Report (Detailed)',
        ];

        if ($request->boolean('print')) {
            $type = strtoupper((string) $request->input('att_type', ''));

            if ($type === 'LR') {
                return $this->monthlyLateReportScreen($request, 'monthly-late-report');
            }

            $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
            $to   = $request->input('to')   ?: now()->endOfMonth()->toDateString();

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

            // This report was always hardcoded Section-grouped — default 'section' here
            // (rather than 'none') keeps that exact appearance unless the user picks a
            // different axis, per the "existing grouping becomes the default" rule.
            $groupBy = $this->resolveGroupBy($request, 'section');
            $optionMaps = $this->groupByOptionMaps($options);

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
                            'employee' => $employee,
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
                                    'employee' => $employee,
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

                [$groups, $groupLabel] = $this->groupEmployeeRows($rows, $groupBy, $optionMaps, fn (array $row) => $row['employee']);

                return view('hr::reports.absent-report-print', compact(
                    'request', 'rows', 'sectionMap', 'fromDate', 'toDate', 'isSingleDay',
                    'groups', 'groupLabel', 'groupBy'
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
                [$groups, $groupLabel] = $this->groupEmployeeRows($attendanceRecords, $groupBy, $optionMaps, fn ($record) => $employeeMap->get($record->employee_id));

                return view('hr::reports.attendance-missing-report-print', compact(
                    'request', 'employees', 'attendanceRecords', 'attendanceBySection', 'sectionMap',
                    'designationMap', 'lineMap', 'workingPlaceMap', 'employeeMap', 'from', 'to',
                    'groups', 'groupLabel', 'groupBy'
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

                [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

                return view('hr::reports.attendance-status-report-print', compact(
                    'request', 'employees', 'from', 'to', 'sectionMap', 'days', 'attendanceStatusByEmployee',
                    'groups', 'groupLabel', 'groupBy'
                ));
            }

            [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

            return view('hr::reports.attendance-report-print', compact(
                'request', 'employees', 'from', 'to',
                'sectionMap', 'subSectionMap', 'designationMap', 'shiftMap', 'attendanceByEmployee', 'dateLabel',
                'groups', 'groupLabel', 'groupBy'
            ));
        }

        return view('hr::reports.attendance-report', [
            'reportKey'        => $report,
            'reportTitle'      => config('hr.reports.' . $report),
            'options'          => $options,
            'attendanceTypes'  => $attendanceTypes,
            'groupByOptions'   => self::GROUP_BY_OPTIONS,
            'request'          => $request,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // DAILY ATTENDANCE REPORT
    // ──────────────────────────────────────────────────────────────────

    /**
     * Route: GET /reports/daily-attendance-report
     */
    public function dailyAttendanceReportScreen(Request $request)
    {
        return view('hr::reports.daily-attendance-report', [
            'options' => $this->employeeReportOptions(),
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'request' => $request,
        ]);
    }

    /**
     * Route: GET /reports/daily-attendance-report-print
     */
    public function dailyAttendanceReportPrint(Request $request)
    {
        $from = $request->input('from') ?: now()->toDateString();
        $to = $request->input('to') ?: $from;
        $isRange = $from !== $to;
        $groupBy = $this->resolveGroupBy($request);

        $options = $this->employeeReportOptions();
        $optionMaps = $this->groupByOptionMaps($options);

        $employees = $this->employeeReportQuery($request)
            ->with(['designation:id,name,bn_name'])
            ->orderBy('section_id')
            ->naturalOrderById()
            ->get();

        // This report only ever wants to show Present, Late, Absent, Weekend, Holiday or
        // Leave — "Punch Missing" (and "Late and Punch Missing") are a data-entry quality
        // flag from other screens, not a real attendance state here, so fold them back into
        // plain Present/Late.
        $normalizeStatus = function ($status) {
            $status = (string) ($status ?: 'N/A');
            if (stripos($status, 'punch missing') === false) {
                return $status;
            }
            return stripos($status, 'late') !== false ? 'Late' : 'Present';
        };

        $employeeRows = $employees->map(function (HrEmployee $employee) use ($from, $to, $normalizeStatus) {
            $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate($employee->id, $from, $to);
            $days = collect($pack['attendance'] ?? [])
                // 'not_employed' days (past the employee's exit date) aren't real
                // attendance to report on — drop them so a resigned employee doesn't
                // show up as a row for dates after they left.
                ->filter(fn ($d) => ($d['status_key'] ?? null) !== 'not_employed')
                ->map(fn ($d) => [
                    'date' => $d['date'] ?? '-',
                    'in_time' => $d['in_time'] ?? '-',
                    'out_time' => $d['out_time'] ?? '-',
                    'status' => $normalizeStatus($d['status'] ?? 'N/A'),
                    'ot_hours' => $d['compliance_ot'] ?? 0,
                ])
                ->values();
            $firstDay = $days->first() ?? [];

            return [
                'employee' => $employee,
                'employee_id' => $employee->employee_id,
                'name' => $employee->bn_name ?? $employee->name,
                'designation' => $employee->designation?->name ?? 'N/A',
                'days' => $days,
                'in_time' => $firstDay['in_time'] ?? '-',
                'out_time' => $firstDay['out_time'] ?? '-',
                'status' => $firstDay['status'] ?? 'N/A',
                'ot_hours' => $firstDay['ot_hours'] ?? 0,
            ];
        })->filter(fn (array $row) => $row['days']->isNotEmpty())->values();

        // Report-wide summary — a simple total across every employee-day shown below,
        // in a fixed, predictable status order (rather than sorted by count, which would
        // reorder itself unpredictably day to day).
        $statusOrder = ['Present', 'Late', 'Absent', 'Leave', 'Holiday', 'Weekend'];
        $statusCounts = array_fill_keys($statusOrder, 0);
        $totalOtHours = 0.0;
        foreach ($employeeRows as $row) {
            foreach ($row['days'] as $day) {
                $status = $day['status'];
                // 'N/A' marks a day past the employee's resignation date (not_employed) —
                // not a real attendance state, so it's excluded from the summary entirely
                // rather than showing up as its own stray column.
                if ($status === 'N/A' || !array_key_exists($status, $statusCounts)) {
                    continue;
                }
                $statusCounts[$status]++;
                $totalOtHours += (float) ($day['ot_hours'] ?? 0);
            }
        }
        $summary = [
            'total_employees' => $employeeRows->count(),
            'status_counts'   => $statusCounts,
            'total_ot_hours'  => round($totalOtHours, 2),
        ];

        [$groups, $groupLabel] = $this->groupEmployeeRows($employeeRows, $groupBy, $optionMaps, fn (array $row) => $row['employee']);

        return view('hr::reports.daily-attendance-report-print', [
            'groups' => $groups,
            'groupLabel' => $groupLabel,
            'groupBy' => $groupBy,
            'isRange' => $isRange,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'dateLabel' => $isRange
                ? Carbon::parse($from)->format('d-M-Y') . ' to ' . Carbon::parse($to)->format('d-M-Y')
                : Carbon::parse($from)->format('d-M-Y'),
        ]);
    }

    public function otSummaryReportScreen(Request $request)
    {
        return view('hr::reports.ot-summary-report', [
            'options' => $this->employeeReportOptions(),
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'request' => $request,
        ]);
    }

    public function otSummaryReportPrint(Request $request)
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->input('to')   ?: now()->endOfMonth()->toDateString();

        $employees = $this->employeeReportQuery($request)
            ->orderBy('section_id')
            ->naturalOrderById()
            ->get();

        $dates = collect();
        $cur = Carbon::parse($from);
        $end = Carbon::parse($to);
        while ($cur->lte($end)) {
            $dates->push($cur->copy());
            $cur->addDay();
        }

        // Always hardcoded Section -> Designation grouped before — default 'section'
        // preserves the outer level's appearance; Designation stays the fixed inner level.
        $options = $this->employeeReportOptions();
        $groupBy = $this->resolveGroupBy($request, 'section');
        $optionMaps = $this->groupByOptionMaps($options);
        [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

        return view('hr::reports.ot-summary-report-print', [
            'employees' => $employees,
            'from' => $from,
            'to' => $to,
            'dates' => $dates,
            'request' => $request,
            'language' => $request->input('language', 'bn'),
            'fromLabel' => Carbon::parse($from)->format('d-M-Y'),
            'toLabel'   => Carbon::parse($to)->format('d-M-Y'),
            'groups' => $groups,
            'groupLabel' => $groupLabel,
            'groupBy' => $groupBy,
            'groupByAxisLabel' => self::GROUP_BY_OPTIONS[$groupBy] === 'None (Flat List)' ? 'Group' : self::GROUP_BY_OPTIONS[$groupBy],
        ]);
    }

    public function gatePassReportScreen(Request $request)
    {
        return view('hr::reports.gate-pass-report', [
            'options' => $this->employeeReportOptions(),
            'request' => $request,
        ]);
    }

    public function gatePassReportPrint(Request $request)
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->input('to')   ?: now()->endOfMonth()->toDateString();

        $employeeIds = $this->employeeReportQuery($request)->pluck('id');

        $gatePasses = \ME\Hr\Models\HrEmployeeGatePass::query()
            ->with(['employee.department', 'employee.section', 'employee.designation'])
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('out_time', '>=', $from)
            ->whereDate('out_time', '<=', $to)
            ->orderBy('out_time')
            ->get();

        $isRange = $from !== $to;

        // Grouped by employee (each group ending in a Total Duration row) once the
        // report spans more than a single day — for a one-day report every employee
        // already has just their one or two rows directly in a flat, chronological list.
        $groupedGatePasses = $isRange
            ? $gatePasses->groupBy('employee_id')
                ->sortBy(fn ($rows) => $rows->first()->employee->employee_id ?? '')
            : collect();

        return view('hr::reports.gate-pass-report-print', [
            'gatePasses' => $gatePasses,
            'groupedGatePasses' => $groupedGatePasses,
            'isRange' => $isRange,
            'from' => $from,
            'to' => $to,
            'fromLabel' => Carbon::parse($from)->format('d-M-Y'),
            'toLabel'   => Carbon::parse($to)->format('d-M-Y'),
        ]);
    }

    public function assetReportScreen(Request $request)
    {
        return view('hr::reports.asset-report', [
            'options' => $this->employeeReportOptions(),
            'categories' => \ME\Hr\Models\HrAssetCategory::query()->orderBy('name')->get(['id', 'name']),
            'request' => $request,
        ]);
    }

    public function assetReportPrint(Request $request)
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to   = $request->input('to')   ?: now()->endOfMonth()->toDateString();

        $employeeIds = $this->employeeReportQuery($request)->pluck('id');

        $assets = \ME\Hr\Models\HrEmployeeAsset::query()
            ->with(['employee.department', 'employee.section', 'employee.designation', 'category'])
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('issued_date', '>=', $from)
            ->whereDate('issued_date', '<=', $to)
            ->when($request->filled('asset_category'), function ($q) use ($request) {
                $values = array_filter(array_map('intval', (array) $request->asset_category));
                if (!empty($values)) {
                    $q->whereIn('asset_category_id', $values);
                }
            })
            ->when($request->filled('asset_status'), function ($q) use ($request) {
                $values = array_filter((array) $request->asset_status);
                if (!empty($values)) {
                    $q->whereIn('status', $values);
                }
            })
            ->orderBy('issued_date')
            ->get();

        $isRange = $from !== $to;

        // Grouped by employee (each group ending in a Total Assets row) once the
        // report spans more than a single day — mirrors the Gate Pass Report pattern.
        $groupedAssets = $isRange
            ? $assets->groupBy('employee_id')
                ->sortBy(fn ($rows) => $rows->first()->employee->employee_id ?? '')
            : collect();

        return view('hr::reports.asset-report-print', [
            'assets' => $assets,
            'groupedAssets' => $groupedAssets,
            'isRange' => $isRange,
            'from' => $from,
            'to' => $to,
            'fromLabel' => Carbon::parse($from)->format('d-M-Y'),
            'toLabel'   => Carbon::parse($to)->format('d-M-Y'),
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

            $englishRequest = clone $request;
            $englishRequest->merge([
                'language' => 'en',
            ]);
            $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee(null, $englishRequest, null, null, null, null);

            // Same Punch-Missing fold used by Daily Attendance Report — it's a data-entry
            // quality flag from other screens, not a real attendance state here.
            $normalizeStatus = function ($status) {
                $status = (string) ($status ?: 'N/A');
                if (stripos($status, 'punch missing') === false) {
                    return $status;
                }
                return stripos($status, 'late') !== false ? 'Late' : 'Present';
            };

            $rows = $employees->map(function ($employee) use ($request, $employeeDataFn, $sectionMap, $designationMap, $reportDate, $normalizeStatus) {
                // The compliance-mode/weekend/holiday-aware processed row is the single
                // source of truth here — previously this preferred the raw HrAttendance
                // record instead, which showed unprocessed in/out/status (ignoring
                // weekend-hiding, compliance capping, etc.), inconsistent with every other
                // report in the system.
                $pack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                    $employee->id,
                    $reportDate,
                    $reportDate
                );
                $attendanceRow = collect($pack['attendance'] ?? [])->first() ?? [];
                $statusKey = (string) ($attendanceRow['status_key'] ?? '');

                $employeeData = $employeeDataFn($employee, $request ?? null, null, null, null, null);

                return [
                    'employee' => $employee,
                    'section_id' => $employee->section_id,
                    'section' => $employeeData['section'] ?? $sectionMap->get($employee->section_id, 'N/A'),
                    'card_no' => $employee->employee_id,
                    'name' => $employeeData['employee_name'] ?? $employee->name,
                    'designation' => $employeeData['designation'] ?? $designationMap->get($employee->designation_id, 'N/A'),
                    'in_time' => $attendanceRow['in_time'] ?? '-',
                    'out_time' => $attendanceRow['out_time'] ?? '-',
                    'late' => in_array($statusKey, ['late', 'late_and_early_exit', 'late_and_punch_missing'], true) ? 1 : 0,
                    'ot_hours' => (float) ($attendanceRow['compliance_ot'] ?? 0),
                    'status' => strtoupper($normalizeStatus($attendanceRow['status'] ?? 'N/A')),
                ];
            });

            // Always hardcoded Section-grouped before — default 'section' preserves that.
            $groupBy = $this->resolveGroupBy($request, 'section');
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows($rows, $groupBy, $optionMaps, fn (array $row) => $row['employee']);

            return view('hr::reports.attendance-with-ot-print', [
                'request' => $request,
                'rows' => $rows,
                'reportDate' => $reportDate,
                'groups' => $groups,
                'groupLabel' => $groupLabel,
                'groupBy' => $groupBy,
            ]);
        }

        return view('hr::reports.attendance-with-ot', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options' => $options,
            'groupByOptions' => self::GROUP_BY_OPTIONS,
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
                    'employee' => $employee,
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

            // Always hardcoded Section-grouped before — default 'section' preserves that.
            $groupBy = $this->resolveGroupBy($request, 'section');
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows($lateByEmployee, $groupBy, $optionMaps, fn (array $row) => $row['employee']);

            return view('hr::reports.monthly-late-report-print', [
                'request' => $request,
                'from' => $from,
                'to' => $to,
                'lateBySection' => $groups,
                'groupLabel' => $groupLabel,
                'groupBy' => $groupBy,
            ]);
        }

        return view('hr::reports.monthly-late-report', [
            'reportKey' => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options' => $options,
            'groupByOptions' => self::GROUP_BY_OPTIONS,
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
            $reportType = (string) $request->input('report_type', 'details');
            if (!array_key_exists($reportType, $reportTypes)) {
                $reportType = 'details';
            }

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

            // Always hardcoded Section-grouped before — default 'section' preserves that.
            $groupBy = $this->resolveGroupBy($request, 'section');
            $optionMaps = $this->groupByOptionMaps($options);
            [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

            return view('hr::reports.meal-report-print', compact(
                'request', 'employees', 'attendanceMap', 'date',
                'mealType', 'reportType', 'mealTypes', 'reportTypes',
                'sectionMap', 'subSectionMap', 'designationMap', 'shiftMap', 'shifts', 'designationInfoMap',
                'groups', 'groupLabel', 'groupBy'
            ) + [
                'dateLabel'     => \Carbon\Carbon::parse($date)->format('d-M-Y'),
                'mealTypeLabel' => $mealTypes[$mealType],
                'groupByAxisLabel' => self::GROUP_BY_OPTIONS[$groupBy] === 'None (Flat List)' ? 'Group' : self::GROUP_BY_OPTIONS[$groupBy],
            ]);
        }

        return view('hr::reports.meal-report', [
            'reportKey'   => $report,
            'reportTitle' => config('hr.reports.' . $report),
            'options'     => $options,
            'mealTypes'   => $mealTypes,
            'reportTypes' => $reportTypes,
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'request'     => $request,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // BONUS SHEET
    // ──────────────────────────────────────────────────────────────────

    /**
     * Route: GET /reports/bonus-sheet/{category} — path-based category (fixed/production)
     * instead of the old ?bonus_category= query string, e.g. /reports/bonus-sheet/fixed.
     */
    public function bonusSheetByCategory(Request $request, string $category)
    {
        $request->merge(['bonus_category' => $category]);
        return $this->bonusSheetScreen($request, 'bonus-sheet');
    }

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
            'groupByOptions'  => self::GROUP_BY_OPTIONS,
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

        // Always hardcoded Department-grouped before — default 'department' preserves that.
        $groupBy = $this->resolveGroupBy($request, 'department');
        $optionMaps = $this->groupByOptionMaps($options);
        [$groups, $groupLabel] = $this->groupEmployeeRows($employees, $groupBy, $optionMaps, fn ($emp) => $emp);

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
            'groups'         => $groups,
            'groupLabel'     => $groupLabel,
            'groupBy'        => $groupBy,
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

    private const SALARY_REPORT_TYPES = [
        'fixed'                => 'Fixed Salary',
        'production'           => 'Production Salary',
        'bonus'                => 'Bonus Salary',
        'wages-salary-summary' => 'Wages & Salary Summary',
    ];

    private function salaryReportScreen(Request $request, string $report)
    {
        $options = $this->employeeReportOptions();
        $bonusTitles = HrBonusTitle::where('status', 'active')->orderBy('title')->get(['id', 'title']);
        $paymentModes = HrEmployee::query()
            ->whereNotNull('salary_type')
            ->distinct()
            ->pluck('salary_type')
            ->filter()
            ->values();

        return view('hr::reports.salary-report', [
            'reportKey'    => $report,
            'reportTitle'  => config('hr.reports.' . $report),
            'options'      => $options,
            'bonusTitles'  => $bonusTitles,
            'reportTypes'  => self::SALARY_REPORT_TYPES,
            'paymentModes' => $paymentModes,
            'groupByOptions' => self::GROUP_BY_OPTIONS,
            'request'      => $request,
        ]);
    }

    /**
     * Route: GET /reports/fixed-salary (filter form)
     */
    public function fixedSalaryReportScreen(Request $request)
    {
        return $this->salaryReportScreenFor($request, 'fixed');
    }

    /**
     * Route: GET /reports/production-salary (filter form)
     */
    public function productionSalaryReportScreen(Request $request)
    {
        return $this->salaryReportScreenFor($request, 'production');
    }

    /**
     * Route: GET /reports/bonus-salary (filter form)
     */
    public function bonusSalaryReportScreen(Request $request)
    {
        return $this->salaryReportScreenFor($request, 'bonus');
    }

    /**
     * Route: GET /reports/wages-salary-summary (filter form)
     */
    public function wagesSalarySummaryReportScreen(Request $request)
    {
        return $this->salaryReportScreenFor($request, 'wages-salary-summary');
    }

    private function salaryReportScreenFor(Request $request, string $reportType)
    {
        if (!$request->filled('report_type')) {
            $request->merge(['report_type' => $reportType]);
        }

        return $this->salaryReportScreen($request, 'salary-report');
    }

    /**
     * Route: GET /reports/fixed-salary-print
     */
    public function fixedSalaryReportPrint(Request $request)
    {
        return $this->renderSalarySheetReport($request, 'fixed', 'hr::reports.salary-report-print-fixed');
    }

    /**
     * Route: GET /reports/production-salary-print
     */
    public function productionSalaryReportPrint(Request $request)
    {
        return $this->renderSalarySheetReport($request, 'production', 'hr::reports.salary-report-print-production');
    }

    /**
     * Route: GET /reports/bonus-salary-print
     */
    public function bonusSalaryReportPrint(Request $request)
    {
        $payload = $this->salaryReportBasePayload($request, 'bonus');

        // Always hardcoded Department-grouped before — default 'department' preserves that.
        $options = $this->employeeReportOptions();
        $groupBy = $this->resolveGroupBy($request, 'department');
        $optionMaps = $this->groupByOptionMaps($options);

        $payload = array_merge(
            $payload,
            SalaryReportService::buildBonusReportData($payload['employees'], $request, $payload['to'], $groupBy),
            ['groupLabel' => $this->groupLabelResolver($groupBy, $optionMaps), 'groupBy' => $groupBy]
        );

        return view('hr::reports.salary-report-print-bonus', $payload);
    }

    /**
     * Route: GET /reports/wages-salary-summary-print
     */
    public function wagesSalarySummaryReportPrint(Request $request)
    {
        $payload = $this->salaryReportBasePayload($request, 'wages-salary-summary');

        // Only Department/Section are coherent rollup axes here — each summary row is
        // already a pre-aggregated Department+Section bucket, not a single employee.
        $groupBy = $this->resolveGroupBy($request, 'department');

        $payload = array_merge(
            $payload,
            SalaryReportService::buildWagesSummaryData($payload['employees'], $payload['from'], $payload['to'], $request, $groupBy)
        );

        return view('hr::reports.salary-report-print-wages', $payload);
    }

    private function renderSalarySheetReport(Request $request, string $reportType, string $view)
    {
        $payload = $this->salaryReportBasePayload($request, $reportType);
        // FL/GL are excluded here — those codes are now the dedicated Festival/General
        // factory-holiday columns (see EmployeeAttendanceService's holiday_festival/
        // holiday_general), not per-employee leave-type columns, so they must not also
        // appear via the generic per-leave-type loop below.
        $leaveInfos = HrLeaveInfo::where('status', 'active')
            ->whereNotIn('code', ['FL', 'GL'])
            ->orderBy('id')->get(['id', 'name', 'code']);

        // Always hardcoded Department+Section grouped before — default 'department_section'
        // preserves that exact bucketing/subtotal appearance.
        $options = $this->employeeReportOptions();
        $groupBy = $this->resolveGroupBy($request, 'department_section');
        $optionMaps = $this->groupByOptionMaps($options);

        $payload = array_merge(
            $payload,
            SalaryReportService::buildSalarySheetData($payload['employees'], $payload['from'], $payload['to'], $request, $leaveInfos, $groupBy),
            [
                'leaveInfos' => $leaveInfos,
                'groupLabel' => $this->groupLabelResolver($groupBy, $optionMaps),
            ]
        );

        return view($view, $payload);
    }

    /**
     * Common request/employee/lookup-map data every salary report print view needs.
     */
    private function salaryReportBasePayload(Request $request, string $reportType): array
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?: now()->endOfMonth()->toDateString();
        $options = $this->employeeReportOptions();

        $employees = $this->employeeReportQuery($request)
            ->with(['designation:id,name,bn_name,grade', 'department:id,name,bn_name', 'section:id,name,bn_name'])
            ->orderBy('department_id')
            ->orderBy('section_id')
            ->orderBy('name')
            ->get();

        $departmentMap = collect($options['departments'])->pluck('name', 'id');
        $sectionMap = collect($options['sections'])->pluck('name', 'id');
        $subSectionMap = collect($options['subSections'])->pluck('name', 'id');
        $designationMap = HrDesignation::query()->get(['id', 'name', 'grade'])
            ->mapWithKeys(fn (HrDesignation $d) => [$d->id => ['name' => $d->name, 'grade' => $d->grade]]);
        $lineMap = collect($options['lines'])->mapWithKeys(fn ($r) => [
            $r->id => trim(($r->name ?? '') . (filled($r->slug ?? null) ? ' - ' . $r->slug : '')),
        ]);

        return [
            'request' => $request,
            'employees' => $employees,
            'salarySheets' => collect(),
            'from' => $from,
            'to' => $to,
            'reportType' => $reportType,
            'reportTypes' => self::SALARY_REPORT_TYPES,
            'departmentMap' => $departmentMap,
            'sectionMap' => $sectionMap,
            'subSectionMap' => $subSectionMap,
            'designationMap' => $designationMap,
            'lineMap' => $lineMap,
            'withPicture' => $request->boolean('with_picture'),
            'language' => $request->input('language', 'en'),
            'fromLabel' => Carbon::parse($from)->format('d-M-Y'),
            'toLabel' => Carbon::parse($to)->format('d-M-Y'),
            'reportTypeLabel' => self::SALARY_REPORT_TYPES[$reportType],
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
            'reportKey'   => $report,
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





