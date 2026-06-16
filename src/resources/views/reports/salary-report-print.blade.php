@extends('printMaster2')

@section('title', $reportTypeLabel . ' - ' . $fromLabel . ' To ' . $toLabel)

@push('css')
<style>
/* ── GLOBAL ── */
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; }

/* ── REPORT HEADER ── */
.rpt-header { text-align:center; border-bottom:2.5px solid #1a3a5c; padding-bottom:8px; margin-bottom:10px; }
.rpt-header h2 { margin:0 0 1px; font-size:16px; text-transform:uppercase; letter-spacing:1px; color:#1a3a5c; }
.rpt-header p  { margin:0; font-size:10.5px; color:#444; }
.rpt-title-bar { background:#1a3a5c; color:#fff; text-align:center; padding:5px 8px; margin-bottom:8px; }
.rpt-title-bar h4 { margin:0; font-size:12px; letter-spacing:.5px; }
.rpt-title-bar span { font-size:10px; opacity:.88; }
.rpt-meta { display:flex; justify-content:space-between; font-size:9.5px; margin-bottom:8px; color:#333; }
.rpt-meta span { display:inline-block; }

/* ── GENERIC TABLE ── */
.t { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:9.5px; }
.t th, .t td { border:1px solid #999; padding:3px 5px; }
.t thead tr.hdr1 th { background:#1a3a5c; color:#fff; text-align:center; font-size:9px; letter-spacing:.3px; }
.t thead tr.hdr2 th { background:#2e6da4; color:#fff; text-align:center; font-size:9px; }
.t tbody tr:nth-child(even) td { background:#f5f8fc; }
.t tbody tr:hover td { background:#eaf2fb; }
.tc { text-align:center; }
.tr { text-align:right; }
.tl { text-align:left; }

/* ── DEPT GROUP ── */
.dept-group-header td { background:#d0e4f7; color:#1a3a5c; font-weight:700; font-size:9.5px; border-top:2px solid #2e6da4; }
.dept-subtotal td    { background:#cfe2f3; font-weight:700; font-size:9.5px; border-top:1.5px solid #2e6da4; }

/* ── GRAND TOTAL ── */
.grand-total td { background:#1a3a5c; color:#fff; font-weight:700; font-size:10px; border:1px solid #1a3a5c; }

/* ── SUMMARY BADGES ── */
.stat-bar { display:flex; gap:8px; margin-bottom:10px; }
.stat-box { flex:1; border:1px solid #2e6da4; border-radius:3px; padding:5px 8px; text-align:center; background:#f0f6fc; }
.stat-box .val { font-size:14px; font-weight:700; color:#1a3a5c; display:block; }
.stat-box .lbl { font-size:8.5px; color:#555; text-transform:uppercase; letter-spacing:.4px; }

/* ── DETAIL TABLE ── */
.dept-title { font-size:10.5px; font-weight:700; background:#1a3a5c; color:#fff; padding:4px 8px; margin:12px 0 2px; letter-spacing:.3px; }
.summary-row td { background:#dcedc8; font-weight:700; }
.photo-cell img { max-width:28px; max-height:34px; }

/* ── FOOTER ── */
.rpt-footer { margin-top:18px; border-top:1.5px solid #1a3a5c; padding-top:8px; }
.sig-row { display:flex; justify-content:space-between; margin-top:24px; }
.sig-box { text-align:center; width:18%; }
.sig-box .sig-line { border-top:1px solid #333; margin-bottom:3px; }
.sig-box .sig-lbl  { font-size:8.5px; color:#333; }
.rpt-footer-note { font-size:8px; color:#666; text-align:center; margin-top:10px; }
</style>
@endpush
@section('contents')
@php
    $company = general()->title ?? 'Company Name';
    $address = general()->address_one ?? '';
    $fmt     = fn($v) => number_format((float)$v, 2);
    $byDept  = $employees->groupBy('department_id');
    $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();

    // Use central HR options service for all lookups
    $hrOptions = \App\Services\HrOptionsService::getOptions();
    $departmentMap = collect($hrOptions['departments'])->pluck('name', 'id');
    $sectionMap = collect($hrOptions['sections'])->pluck('name', 'id');
    $subSectionMap = collect($hrOptions['subSections'])->pluck('name', 'id');
    $designationMap = \ME\Hr\Models\HrDesignation::query()->pluck('name', 'id');

    // Helper: use same earnings/deductions + OT adjustment logic as payslip
    $empSalary = function($userId, $emp = null) use ($salarySheets, $employeeDataFn, $request, $from, $to) {
        $sheets = $salarySheets->get($userId, collect());
        if (!$emp) {
            return [
                'gross' => 0,
                'basic' => 0,
                'house_rent' => 0,
                'medical' => 0,
                'transport' => 0,
                'total_earn' => 0,
                'total_deduct' => 0,
                'net' => 0,
                'ot' => 0,
                'ot_hours' => 0,
                'present' => 0,
                'absent' => 0,
            ];
        }

        $factoryNo = (int) (hr_factory('factory_no') ?? 0);
        $employeeData = $employeeDataFn($emp, $request ?? null, null, null, null, null);
        $salaryReport = $employeeData['getSalaryReport']($from, $to);
        $sal          = hr_employee_salary($emp);
        $otRate       = (float) ($employeeData['salary']['ot_rate'] ?? $sal['ot_rate'] ?? 0);

        $attendancePack = \App\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
            $emp->id,
            $from,
            $to
        );
        $summary = $attendancePack['summary'] ?? [];
        $otHours = ($factoryNo === 1 || $factoryNo === 2)
            ? (float) ($summary['totalComplianceOt'] ?? 0)
            : (float) ($summary['totalOt'] ?? 0);
        $otAmount = round($otHours * $otRate, 2);
        $salaryOt = (float) ($salaryReport['ot'] ?? 0);
        $otAdjustment = $otAmount - $salaryOt;
        $totalEarn = (float) ($salaryReport['total_earn'] ?? 0) + $otAdjustment;
        $netPay = (float) ($salaryReport['net'] ?? 0) + $otAdjustment;

        // Attendance summary is period-aware; use it as source of truth for present/absent.
        $present = isset($summary['totalPresentAll'])
            ? (int) $summary['totalPresentAll']
            : ($sheets->isNotEmpty() ? (int) $sheets->sum('present_days') : 0);
        $absent = isset($summary['totalAbsent'])
            ? (int) $summary['totalAbsent']
            : ($sheets->isNotEmpty() ? (int) $sheets->sum('absent_days') : 0);

        return [
            'gross'        => (float) ($salaryReport['gross'] ?? $sal['gross'] ?? 0),
            'basic'        => (float) ($salaryReport['basic'] ?? $sal['basic'] ?? 0),
            'house_rent'   => (float) ($sal['house'] ?? 0),
            'medical'      => (float) ($sal['medical'] ?? 0),
            'transport'    => (float) ($sal['transport'] ?? 0),
            'total_earn'   => $totalEarn,
            'total_deduct' => (float) ($salaryReport['total_deduct'] ?? 0),
            'net'          => $netPay,
            // OT hours/amount follow payslip & job-card factory logic
            'ot'           => $otAmount,
            'ot_hours'     => $otHours,
            'present'      => $present,
            'absent'       => $absent,
        ];
    };
@endphp


{{-- ── CORPORATE REPORT HEADER ── --}}
<div class="rpt-header">
    <h2>{{ $company }}</h2>
    <p>{{ $address }}</p>
</div>
<div class="rpt-title-bar">
    <h4>{{ $reportTypeLabel }}</h4>
    <span>Period: {{ $fromLabel }} &mdash; {{ $toLabel }}</span>
</div>
<div class="rpt-meta">
    <span><strong>Print Date:</strong> {{ now()->format('d M Y, h:i A') }}</span>
    <span><strong>Currency:</strong> BDT (Bangladeshi Taka)</span>
</div>

@if($reportType === 'wages-salary-summary')
    {{-- ── WAGES & SALARY SUMMARY ── --}}
    @php
        // Pre-compute all salary data grouped by dept → section
        $summaryData = [];
        $grandTotals = [
            'emp' => 0, 'basic' => 0, 'house_rent' => 0, 'medical' => 0,
            'transport' => 0, 'ot' => 0, 'gross' => 0,
            'earn' => 0, 'deduct' => 0, 'net' => 0,
            'present' => 0, 'absent' => 0,
        ];
        foreach ($byDept as $deptId => $deptEmps) {
            $bySec = $deptEmps->groupBy('section_id');
            foreach ($bySec as $secId => $secEmps) {
                $row = [
                    'dept_id' => $deptId, 'sec_id' => $secId,
                    'emp' => $secEmps->count(),
                    'basic' => 0, 'house_rent' => 0, 'medical' => 0,
                    'transport' => 0, 'ot' => 0, 'gross' => 0,
                    'earn' => 0, 'deduct' => 0, 'net' => 0,
                    'present' => 0, 'absent' => 0,
                ];
                foreach ($secEmps as $emp) {
                    $sd = $empSalary($emp->id, $emp);
                    $row['basic']      += $sd['basic'];
                    $row['house_rent'] += $sd['house_rent'];
                    $row['medical']    += $sd['medical'];
                    $row['transport']  += $sd['transport'];
                    $row['ot']         += $sd['ot'];
                    $row['gross']      += $sd['gross'];
                    $row['earn']       += $sd['total_earn'];
                    $row['deduct']     += $sd['total_deduct'];
                    $row['net']        += $sd['net'];
                    $row['present']    += $sd['present'];
                    $row['absent']     += $sd['absent'];
                }
                $summaryData[] = $row;
                foreach (['emp','basic','house_rent','medical','transport','ot','gross','earn','deduct','net','present','absent'] as $k) {
                    $grandTotals[$k] += $row[$k];
                }
            }
        }
        // Group summaryData by dept for subtotals
        $byDeptSummary = collect($summaryData)->groupBy('dept_id');
    @endphp

    {{-- KPI STAT BOXES --}}
    <div class="stat-bar">
        <div class="stat-box">
            <span class="val">{{ $grandTotals['emp'] }}</span>
            <span class="lbl">Total Employees</span>
        </div>
        <div class="stat-box">
            <span class="val">{{ $fmt($grandTotals['gross']) }}</span>
            <span class="lbl">Total Gross Salary</span>
        </div>
        <div class="stat-box">
            <span class="val">{{ $fmt($grandTotals['ot']) }}</span>
            <span class="lbl">Total OT Amount</span>
        </div>
        <div class="stat-box">
            <span class="val">{{ $fmt($grandTotals['earn']) }}</span>
            <span class="lbl">Total Earning</span>
        </div>
        <div class="stat-box">
            <span class="val">{{ $fmt($grandTotals['deduct']) }}</span>
            <span class="lbl">Total Deduction</span>
        </div>
        <div class="stat-box">
            <span class="val">{{ $fmt($grandTotals['net']) }}</span>
            <span class="lbl">Net Payable</span>
        </div>
    </div>

    <table class="t">
        <thead>
            <tr class="hdr1">
                <th rowspan="2">SL</th>
                <th rowspan="2">Department</th>
                <th rowspan="2">Section</th>
                <th rowspan="2">Emp.</th>
                <th colspan="5">Salary Components (BDT)</th>
                <th rowspan="2">Gross<br>Salary</th>
                <th rowspan="2">Total<br>Earning</th>
                <th rowspan="2">Total<br>Deduction</th>
                <th rowspan="2">Net<br>Payable</th>
                <th rowspan="2">Present</th>
                <th rowspan="2">Absent</th>
            </tr>
            <tr class="hdr2">
                <th>Basic</th>
                <th>House Rent</th>
                <th>Medical</th>
                <th>Transport</th>
                <th>OT Amt</th>
            </tr>
        </thead>
        <tbody>
            @php $sl = 1; @endphp
            @forelse($byDeptSummary as $deptId => $deptRows)
                @php
                    $deptTotals = [
                        'emp'=>0,'basic'=>0,'house_rent'=>0,'medical'=>0,'transport'=>0,
                        'ot'=>0,'gross'=>0,'earn'=>0,'deduct'=>0,'net'=>0,'present'=>0,'absent'=>0
                    ];
                @endphp
                {{-- Dept header span --}}
                <tr class="dept-group-header">
                    <td colspan="15" class="tl">&nbsp;&nbsp;&#9658; {{ $departmentMap->get($deptId, 'N/A') }}</td>
                </tr>
                @foreach($deptRows as $row)
                    @php
                        foreach (array_keys($deptTotals) as $k) $deptTotals[$k] += $row[$k];
                    @endphp
                    <tr>
                        <td class="tc">{{ $sl++ }}</td>
                        <td class="tl">{{ $departmentMap->get($row['dept_id'], 'N/A') }}</td>
                        <td class="tl">{{ $sectionMap->get($row['sec_id'], 'N/A') }}</td>
                        <td class="tc">{{ $row['emp'] }}</td>
                        <td class="tr">{{ $fmt($row['basic']) }}</td>
                        <td class="tr">{{ $fmt($row['house_rent']) }}</td>
                        <td class="tr">{{ $fmt($row['medical']) }}</td>
                        <td class="tr">{{ $fmt($row['transport']) }}</td>
                        <td class="tr">{{ $fmt($row['ot']) }}</td>
                        <td class="tr">{{ $fmt($row['gross']) }}</td>
                        <td class="tr">{{ $fmt($row['earn']) }}</td>
                        <td class="tr">{{ $fmt($row['deduct']) }}</td>
                        <td class="tr">{{ $fmt($row['net']) }}</td>
                        <td class="tc">{{ $row['present'] }}</td>
                        <td class="tc">{{ $row['absent'] }}</td>
                    </tr>
                @endforeach
                {{-- Department subtotal --}}
                <tr class="dept-subtotal">
                    <td colspan="3" class="tr">Sub-Total ({{ $departmentMap->get($deptId, '') }}):</td>
                    <td class="tc">{{ $deptTotals['emp'] }}</td>
                    <td class="tr">{{ $fmt($deptTotals['basic']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['house_rent']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['medical']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['transport']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['ot']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['gross']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['earn']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['deduct']) }}</td>
                    <td class="tr">{{ $fmt($deptTotals['net']) }}</td>
                    <td class="tc">{{ $deptTotals['present'] }}</td>
                    <td class="tc">{{ $deptTotals['absent'] }}</td>
                </tr>
            @empty
                <tr><td colspan="15" class="tc" style="padding:12px;color:#888;">No salary data found for the selected period.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="3" class="tr">GRAND TOTAL</td>
                <td class="tc">{{ $grandTotals['emp'] }}</td>
                <td class="tr">{{ $fmt($grandTotals['basic']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['house_rent']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['medical']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['transport']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['ot']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['gross']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['earn']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['deduct']) }}</td>
                <td class="tr">{{ $fmt($grandTotals['net']) }}</td>
                <td class="tc">{{ $grandTotals['present'] }}</td>
                <td class="tc">{{ $grandTotals['absent'] }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- AUTHORISATION FOOTER --}}
    <div class="rpt-footer">
        <div class="sig-row">
            <div class="sig-box"><div class="sig-line"></div><div class="sig-lbl">Prepared By</div></div>
            <div class="sig-box"><div class="sig-line"></div><div class="sig-lbl">Checked By</div></div>
            <div class="sig-box"><div class="sig-line"></div><div class="sig-lbl">HR Manager</div></div>
            <div class="sig-box"><div class="sig-line"></div><div class="sig-lbl">Accounts Manager</div></div>
            <div class="sig-box"><div class="sig-line"></div><div class="sig-lbl">Managing Director</div></div>
        </div>
        <div class="rpt-footer-note">This is a system-generated report. &mdash; {{ $company }} &mdash; Confidential</div>
    </div>

@else
    {{-- ── FIXED / PRODUCTION / BONUS SALARY DETAILS ── --}}
    @forelse($byDept as $deptId => $deptEmps)
        <div class="dept-title">&nbsp;Department: {{ $departmentMap->get($deptId, 'N/A') }}</div>

        <table class="t">
            <thead>
                <tr>
                    <th>SL</th>
                    @if($withPicture)<th>Photo</th>@endif
                    <th>Emp. ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Section</th>
                    <th>Sub-Section</th>
                    <th>Block/Line</th>
                    <th>Join Date</th>
                    <th>Gross</th>
                    <th>Basic</th>
                    <th>OT Hrs</th>
                    <th>OT Amt</th>
                    <th>Total Earn</th>
                    <th>Deduction</th>
                    <th>Net Pay</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @php $sl = 1; $totalNet = 0; @endphp
                @foreach($deptEmps as $employee)
                    @php
                        $sd = $empSalary($employee->id, $employee);
                        $totalNet += $sd['net'];
                    @endphp
                    <tr>
                        <td class="tc">{{ $sl++ }}</td>
                        @if($withPicture)
                            <td class="tc photo-cell">
                                @if($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="">
                                @else —
                                @endif
                            </td>
                        @endif
                        <td>{{ $employee->employee_id }}</td>
                        <td>{{ $language === 'bn' && $employee->bn_name ? $employee->bn_name : $employee->name }}</td>
                        <td>{{ $designationMap->get($employee->designation_id, 'N/A') }}</td>
                        <td>{{ $sectionMap->get($employee->section_id, 'N/A') }}</td>
                        <td>{{ $subSectionMap->get($employee->otherInfo()['profile']['sub_section_id']) }}</td>
                        <td>{{ $lineMap->get($employee->line_number, 'N/A') }}</td>
                        <td class="tc">{{ optional($employee->joining_date)->format('d-M-y') ?? '-' }}</td>
                        <td class="tr">{{ $fmt($sd['gross']) }}</td>
                        <td class="tr">{{ $fmt($sd['basic']) }}</td>
                        <td class="tc">{{ number_format($sd['ot_hours'], 2) }}</td>
                        <td class="tr">{{ $fmt($sd['ot']) }}</td>
                        <td class="tr">{{ $fmt($sd['total_earn']) }}</td>
                        <td class="tr">{{ $fmt($sd['total_deduct']) }}</td>
                        <td class="tr">{{ $fmt($sd['net']) }}</td>
                        <td class="tc">{{ $sd['present'] }}</td>
                        <td class="tc">{{ $sd['absent'] }}</td>
                        <td></td>
                    </tr>
                @endforeach
                <tr class="summary-row">
                    <td colspan="{{ $withPicture ? 15 : 14 }}" class="tr">Total Net Pay:</td>
                    <td class="tr">{{ $fmt($totalNet) }}</td>
                    <td></td><td></td><td></td>
                </tr>
            </tbody>
        </table>
    @empty
        <p>No employees found.</p>
    @endforelse
@endif

@endsection
