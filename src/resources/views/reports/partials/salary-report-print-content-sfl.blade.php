@push('css')
<style>
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; }

.rpt-header { text-align:center;  padding-bottom:4px; margin-bottom:5px; line-height:1.2; }
.rpt-header h2 { margin:0; font-size:14px; text-transform:uppercase; letter-spacing:.6px; color:#1a3a5c; }
.rpt-header p  { margin:1px 0 0; font-size:9.5px; color:#444; }
.rpt-title-bar { background:#ffffff00; color:#000000; text-align:center; padding:3px 6px; margin-bottom:-65px; line-height:1.15; }
.rpt-title-bar h4 { margin:0; font-size:11px; letter-spacing:.3px; }
.rpt-title-bar span { font-size:9px; opacity:.9; }
.rpt-meta { display:flex; justify-content:space-between; align-items:center; font-size:9px; margin-bottom:4px; color:#333; line-height:1.15; }
.rpt-meta span { display:inline-block; }

.t { width:100%; border-collapse:collapse; margin-bottom:12px; font-size:9.5px; }
.t th, .t td { border:1px solid #999; padding:3px 5px; }
.t thead tr.hdr1 th { background:#1a3a5c; color:#fff; text-align:center; font-size:9px; letter-spacing:.3px; }
.t thead tr.hdr2 th { background:#2e6da4; color:#fff; text-align:center; font-size:9px; }
.t tbody tr:nth-child(even) td { background:#f5f8fc; }
.t tbody tr:hover td { background:#eaf2fb; }
.tc { text-align:center; }
.tr { text-align:right; }
.tl { text-align:left; }

.dept-group-header td { background:#d0e4f7; color:#1a3a5c; font-weight:700; font-size:9.5px; border-top:2px solid #2e6da4; }
.dept-subtotal td { background:#cfe2f3; font-weight:700; font-size:9.5px; border-top:1.5px solid #2e6da4; }
.grand-total td { background:#1a3a5c; color:#fff; font-weight:700; font-size:10px; border:1px solid #1a3a5c; }

.stat-bar { display:flex; gap:8px; margin-bottom:10px; }
.stat-box { flex:1; border:1px solid #2e6da4; border-radius:3px; padding:5px 8px; text-align:center; background:#f0f6fc; }
.stat-box .val { font-size:14px; font-weight:700; color:#1a3a5c; display:block; }
.stat-box .lbl { font-size:8.5px; color:#555; text-transform:uppercase; letter-spacing:.4px; }

.dept-title { font-size:10.5px; font-weight:700; background:#1a3a5c; color:#fff; padding:4px 8px; margin:12px 0 2px; letter-spacing:.3px; }
.summary-row td { background:#dcedc8; font-weight:700; }
.photo-cell img { max-width:28px; max-height:34px; }

.rpt-footer { margin-top:18px; border-top:1.5px solid #1a3a5c; padding-top:8px; }
.sig-row { display:flex; justify-content:space-between; margin-top:24px; }
.sig-box { text-align:center; width:18%; }
.sig-box .sig-line { border-top:1px solid #333; margin-bottom:3px; }
.sig-box .sig-lbl { font-size:8.5px; color:#333; }
.rpt-footer-note { font-size:8px; color:#666; text-align:center; margin-top:10px; }

.sheet-top { display:flex; justify-content:space-between; gap:10px; margin:2px 0 5px; font-size:9.2px; line-height:1.2; }
.sheet-top .sheet-right { min-width:220px; }
.sheet-top .sheet-right .row { display:flex; justify-content:space-between; gap:10px; margin:1px 0; }

.sheet-table { width:100%; border-collapse:collapse; font-size:9px; margin-top:6px; }
.sheet-table th, .sheet-table td { border:1px solid #6b6b6b; padding:3px 4px; vertical-align:middle; }
.sheet-table thead th { text-align:center; font-weight:700; }
.sheet-table thead tr.grp th { background:#efefef; font-size:9.5px; }
.sheet-table thead tr.sub th { background:#f8f8f8; font-size:8px; font-weight:600; }
.sheet-table .tc { text-align:center; }
.sheet-table .tr { text-align:right; }
.sheet-table .tl { text-align:left; }

.sheet-sec-row td { background:#f4f4f4; color:#0b4f6c; font-weight:700; }
.sheet-sec-total td { background:#fafafa; font-weight:700; }
.sheet-grand td { background:#ececec; font-weight:700; font-size:10px; }
.stamp-box { width:25mm; height:20mm; padding:0; }
.sheet-inwords { margin-top:10px; font-size:10px; font-weight:700; }

@media print {
	@page {
		size: A4 landscape;
		margin: 7mm;
	}
	body {
		margin: 0;
	}
}
</style>
@endpush

@php
	$company = general()->title ?? 'Company Name';
	$address = general()->address_one ?? '';
	$fmt = fn($v) => number_format((float) $v, 2);
	$byDept = $employees->groupBy('department_id');
	$employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
	$isWagesSummary = ($salaryPrintMode ?? '') === 'wages';
	$isBonus        = ($salaryPrintMode ?? '') === 'bonus';

	$bonusPolicies  = collect();
	$bonusTitle     = null;
	$empBonus       = null;
	if ($isBonus) {
		$bonusTitleId = $request->input('bonus_title');
		if (filled($bonusTitleId)) {
			$bonusTitle   = \ME\Hr\Models\HrBonusTitle::find($bonusTitleId);
			$bonusPolicies = \ME\Hr\Models\HrBonusPolicy::query()
				->where('bonus_title_id', $bonusTitleId)
				->where('status', 'active')
				->get();
			if ($bonusPolicies->isEmpty()) {
				$bonusPolicies = \ME\Hr\Models\HrBonusPolicy::query()
					->where('bonus_title_id', $bonusTitleId)
					->get();
			}
		}

		// Reference date: use up_to_date if provided, otherwise use $to
		$bonusReferenceDate = \Carbon\Carbon::parse($request->input('up_to_date') ?: $to);

		$empBonus = function ($emp) use ($bonusPolicies, $bonusReferenceDate) {
			$sal            = hr_employee_salary($emp);
			$gross          = (float) ($sal['gross'] ?? $emp->gross_salary ?? 0);
			$basic          = (float) ($sal['basic'] ?? $emp->basic_salary ?? 0);
			$productionBase = (float) ($sal['production_salary'] ?? $sal['production'] ?? $sal['total_production'] ?? 0);

			$joiningDate   = $emp->joining_date ? \Carbon\Carbon::parse($emp->joining_date) : null;
			$serviceMonths = $joiningDate
				? max(0, (int) $joiningDate->diffInMonths($bonusReferenceDate, false))
				: null;

			$matchedPolicy = $bonusPolicies->filter(function ($policy) use ($emp, $serviceMonths) {
				$designationMatch = !$policy->designation_id
					|| (int) $policy->designation_id === (int) $emp->designation_id;
				$sectionMatch = !$policy->section_id
					|| (int) $policy->section_id === (int) $emp->section_id;

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
					+ (is_null($policy->month_from)      ? 0 : 1)
					+ (is_null($policy->month_to)        ? 0 : 1);
			})->first();

			$bonus       = 0.0;
			$policyLabel = '—';
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
					$bonus = (float) $matchedPolicy->amount;
				} else {
					$percent = (float) $matchedPolicy->amount;
					$bonus   = round($base * $percent / 100, 2);
				}
				$policyLabel = $matchedPolicy->name . ($percent !== null ? " ({$percent}%)" : '');
			}

			$jobAge = 'N/A';
			if ($joiningDate) {
				$diff   = $joiningDate->diff($bonusReferenceDate);
				$jobAge = sprintf('%dy %dm %dd', $diff->y, $diff->m, $diff->d);
			}

			return [
				'bonus'        => $bonus,
				'basic'        => $basic,
				'gross'        => $gross,
				'policy'       => $matchedPolicy,
				'policy_label' => $policyLabel,
				'job_age'      => $jobAge,
				'percent'      => $percent,
			];
		};
	}

	$hrOptions = \ME\Hr\Services\HrOptionsService::getOptions();
	$departmentMap = collect($hrOptions['departments'])->pluck('name', 'id');
	$sectionMap = collect($hrOptions['sections'])->pluck('name', 'id');
	$subSectionMap = collect($hrOptions['subSections'])->pluck('name', 'id');
	$designationMap = \ME\Hr\Models\HrDesignation::query()->get(['id', 'name', 'grade'])
		->mapWithKeys(fn ($d) => [$d->id => ['name' => $d->name, 'grade' => $d->grade_id]]);

	// Leave types from database (admin/hr-center/masters/leave-infos)
	$leaveInfos = \ME\Hr\Models\HrLeaveInfo::where('status', 'active')->orderBy('id')->get(['id', 'name', 'code']);
@endphp

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

@if($isWagesSummary)
	@php
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
					$sd = \ME\Hr\Services\SalaryReportService::getEmployeeSalaryData($emp, $from, $to, $request, $employeeDataFn);
					$row['basic'] += $sd['basic'];
					$row['house_rent'] += $sd['house_rent'];
					$row['medical'] += $sd['medical'];
					$row['transport'] += $sd['transport'];
					$row['ot'] += $sd['ot'];
					$row['gross'] += $sd['gross'];
					$row['earn'] += $sd['total_earn'];
					$row['deduct'] += $sd['total_deduct'];
					$row['net'] += $sd['net'];
					$row['present'] += $sd['present'];
					$row['absent'] += $sd['absent'];
				}
				$summaryData[] = $row;
				foreach (['emp','basic','house_rent','medical','transport','ot','gross','earn','deduct','net','present','absent'] as $k) {
					$grandTotals[$k] += $row[$k];
				}
			}
		}
		$byDeptSummary = collect($summaryData)->groupBy('dept_id');
	@endphp

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
				<tr class="dept-group-header">
					<td colspan="15" class="tl">&nbsp;&nbsp;&#9658; {{ $departmentMap->get($deptId, 'N/A') }}</td>
				</tr>
				@foreach($deptRows as $row)
					@php
						foreach (array_keys($deptTotals) as $k) {
							$deptTotals[$k] += $row[$k];
						}
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
@elseif($isBonus)
	@if(!filled($request->input('bonus_title')))
		<div style="padding:20px;text-align:center;color:#888;">Please select a Bonus Title to generate the bonus salary report.</div>
	@elseif($bonusPolicies->isEmpty())
		<div style="padding:20px;text-align:center;color:#888;">No bonus policies found for the selected Bonus Title.</div>
	@else
		@php
			$bonusGrandTotal = 0;
			$bonusGrandEmp   = 0;
			$hasPctPolicy    = false;
			$bonusByDept     = [];
			foreach ($byDept as $deptId => $deptEmps) {
				$rows = [];
				foreach ($deptEmps as $emp) {
					$bd = $empBonus($emp);
					if ($bd['policy'] === null) continue; // skip employees with no matching policy
					$rows[]           = ['emp' => $emp, 'bd' => $bd];
					$bonusGrandTotal += $bd['bonus'];
					$bonusGrandEmp++;
					if ($bd['percent'] !== null) $hasPctPolicy = true;
				}
				if (!empty($rows)) {
					$bonusByDept[$deptId] = $rows;
				}
			}
		@endphp

		<div class="stat-bar">
			<div class="stat-box">
				<span class="val">{{ $bonusGrandEmp }}</span>
				<span class="lbl">Total Employees</span>
			</div>
			<div class="stat-box">
				<span class="val">{{ $fmt($bonusGrandTotal) }}</span>
				<span class="lbl">Total Bonus Amount</span>
			</div>
			<div class="stat-box">
				<span class="val">{{ $bonusPolicies->count() }}</span>
				<span class="lbl">Active Policies</span>
			</div>
			@if($bonusTitle)
			<div class="stat-box">
				<span class="val" style="font-size:11px;">{{ $bonusTitle->title }}</span>
				<span class="lbl">Bonus Title</span>
			</div>
			@endif
		</div>

		@forelse($bonusByDept as $deptId => $deptRows)
			@php
				$deptBonusTotal = collect($deptRows)->sum(fn ($r) => $r['bd']['bonus']);
				$sl = 1;
			@endphp
			<div class="dept-title">&nbsp;Department: {{ $departmentMap->get($deptId, 'N/A') }}</div>
			<table class="t">
				<thead>
					<tr class="hdr1">
						<th>SL</th>
						@if($withPicture)<th>Photo</th>@endif
						<th>Emp. ID</th>
						<th>Name</th>
						<th>Designation</th>
						<th>Section</th>
						<th>Join Date</th>
						<th>Job Age</th>
						@if($hasPctPolicy)
							<th>Gross</th>
							<th>Basic</th>
						@endif
						<th>Matched Policy</th>
						<th>Bonus Amount</th>
						<th>Stamp</th>
						<th>Signature</th>
					</tr>
				</thead>
				<tbody>
					@foreach($deptRows as $row)
						@php
							$employee = $row['emp'];
							$bd       = $row['bd'];
						@endphp
						<tr>
							<td class="tc">{{ $sl++ }}</td>
							@if($withPicture)
								<td class="tc photo-cell">
									@if($employee->photo)
										<img src="{{ asset('storage/' . $employee->photo) }}" alt="">
									@else
										—
									@endif
								</td>
							@endif
							<td>{{ $employee->employee_id }}</td>
							<td>{{ $language === 'bn' && $employee->bn_name ? $employee->bn_name : $employee->name }}</td>
							<td>{{ $designationMap->get($employee->designation_id, 'N/A') }}</td>
							<td>{{ $sectionMap->get($employee->section_id, 'N/A') }}</td>
							<td class="tc">{{ optional($employee->joining_date)->format('d-M-y') ?? '-' }}</td>
							<td class="tc">{{ $bd['job_age'] }}</td>
							@if($hasPctPolicy)
								<td class="tr">{{ $fmt($bd['gross']) }}</td>
								<td class="tr">{{ $fmt($bd['basic']) }}</td>
							@endif
							<td class="tl">{{ $bd['policy_label'] }}</td>
							<td class="tr">{{ $fmt($bd['bonus']) }}</td>
							<td></td>
							<td></td>
						</tr>
					@endforeach
					@php
						// total cols = SL(1) + [photo] + EmpID + Name + Desig + Section + JoinDate + JobAge + [Gross+Basic] + Policy + BonusAmt + Stamp + Sig
						$totalCols    = ($withPicture ? 12 : 11) + ($hasPctPolicy ? 2 : 0);
						$totalColspan = $totalCols - 3; // leave Bonus Amount, Stamp, Signature
					@endphp
					<tr class="summary-row">
						<td colspan="{{ $totalColspan }}" class="tr">Dept. Bonus Total:</td>
						<td class="tr">{{ $fmt($deptBonusTotal) }}</td>
						<td></td>
						<td></td>
					</tr>
				</tbody>
			</table>
		@empty
			<p>No employees matched any bonus policy.</p>
		@endforelse

		@if(!empty($bonusByDept))
		<table class="t" style="margin-top:0;">
			<tfoot>
				<tr class="grand-total">
					<td colspan="{{ ($withPicture ? 12 : 11) + ($hasPctPolicy ? 2 : 0) - 1 }}" class="tr">
						GRAND TOTAL &mdash; {{ $bonusGrandEmp }} Employees
					</td>
					<td class="tr">{{ $fmt($bonusGrandTotal) }}</td>
				</tr>
			</tfoot>
		</table>
		@endif

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
	@endif
@else
	@php
		$periodStart = \Carbon\Carbon::parse($from);
		$periodEnd = \Carbon\Carbon::parse($to);
		$totalMonthDays = (int) $periodStart->daysInMonth;
		$totalPeriodDays = (int) $periodStart->diffInDays($periodEnd) + 1;

		$dayMap = [
			'sunday' => 0,
			'monday' => 1,
			'tuesday' => 2,
			'wednesday' => 3,
			'thursday' => 4,
			'friday' => 5,
			'saturday' => 6,
		];
		$weekendRaw = (string) (hr_factory('weekend') ?? 'Friday');
		$weekendNames = collect(preg_split('/\s*,\s*/', $weekendRaw))
			->filter(fn ($v) => filled($v))
			->map(fn ($v) => strtolower(trim((string) $v)))
			->values();
		if ($weekendNames->isEmpty()) {
			$weekendNames = collect(['friday']);
		}
		$weekendDayNumbers = $weekendNames
			->map(fn ($name) => $dayMap[$name] ?? null)
			->filter(fn ($n) => !is_null($n))
			->unique()
			->values()
			->all();
		if (empty($weekendDayNumbers)) {
			$weekendDayNumbers = [\Carbon\Carbon::FRIDAY];
		}

		$datePeriod = collect(\Carbon\CarbonPeriod::create(
			$periodStart->copy()->startOfDay(),
			'1 day',
			$periodEnd->copy()->startOfDay()
		));

		$weekendDateMap = $datePeriod
			->filter(fn ($d) => in_array($d->dayOfWeek, $weekendDayNumbers, true))
			->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => true])
			->all();

		try {
			$rtwRows = \ME\Hr\Models\HrRegularToWeekend::query()
				->whereDate('date', '>=', $periodStart->toDateString())
				->whereDate('date', '<=', $periodEnd->toDateString())
				->where('status', 1)
				->get(['date', 'type']);

			foreach ($rtwRows as $rtw) {
				$dateKey = \Carbon\Carbon::parse($rtw->date)->format('Y-m-d');
				if (strtolower((string) $rtw->type) === 'weekend') {
					$weekendDateMap[$dateKey] = true;
				} elseif (strtolower((string) $rtw->type) === 'regular') {
					unset($weekendDateMap[$dateKey]);
				}
			}
		} catch (\Throwable $e) {
			// Keep base weekend calculation when regular_to_weekends table/data is unavailable.
		}

		$holidayDateMap = [];
		try {
			$holidayRows = \ME\Hr\Models\HrHoliday::query()
				->where('status', 1)
				->where(function ($q) {
					$q->whereNull('type')
						->orWhere('type', 'not like', '%Weekly%');
				})
				->where(function ($q) use ($periodStart, $periodEnd) {
					$q->whereBetween('from_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
						->orWhereBetween('to_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
						->orWhere(function ($q2) use ($periodStart, $periodEnd) {
							$q2->where('from_date', '<=', $periodStart->toDateString())
								->where('to_date', '>=', $periodEnd->toDateString());
						});
				})
				->get(['from_date', 'to_date']);

			foreach ($holidayRows as $holiday) {
				$hStart = \Carbon\Carbon::parse($holiday->from_date)->startOfDay();
				$hEndRaw = filled($holiday->to_date) ? $holiday->to_date : $holiday->from_date;
				$hEnd = \Carbon\Carbon::parse($hEndRaw)->startOfDay();

				if ($hStart->lt($periodStart->copy()->startOfDay())) {
					$hStart = $periodStart->copy()->startOfDay();
				}
				if ($hEnd->gt($periodEnd->copy()->startOfDay())) {
					$hEnd = $periodEnd->copy()->startOfDay();
				}

				if ($hStart->lte($hEnd)) {
					foreach (\Carbon\CarbonPeriod::create($hStart, '1 day', $hEnd) as $hDate) {
						$holidayDateMap[$hDate->format('Y-m-d')] = true;
					}
				}
			}
		} catch (\Throwable $e) {
			// Keep holiday count 0 when holidays table/data is unavailable.
		}

		$weekendCount = count($weekendDateMap);
		$otherHolidayDateMap = array_diff_key($holidayDateMap, $weekendDateMap);
		$otherHolidayCount = count($otherHolidayDateMap);
		$totalWorkingDays = max(0, $totalPeriodDays - $weekendCount - $otherHolidayCount);
		$deductionMonthDays = 30;
		// Absent day-rate base follows factory compliance mode: Actual (0/null) -> Gross,
		// Comp-1/Comp-2 (1/2) -> Basic.
		$factoryNo = (int) (hr_factory('factory_no') ?? 0);

		// Base grand totals — leave keys added dynamically below
		$grandBase = [
			'emp' => 0, 'basic' => 0, 'house' => 0, 'medical' => 0,
			'transport' => 0, 'food' => 0, 'salary_total' => 0,
			'pr' => 0, 'wh' => 0, 'fh' => 0, 'ab' => 0, 'earn_days' => 0,
			'att_bonus' => 0, 'deduct_absent' => 0, 'loan' => 0, 'tax' => 0, 'stamp' => 0,
			'deduct_other' => 0, 'wph_days' => 0, 'wph_amount' => 0,
			'other_earn' => 0, 'gross' => 0, 'payable' => 0,
			'ot_hours' => 0, 'ot_rate' => 0, 'ot_total' => 0,
			'extra_facility' => 0, 'net' => 0, 'deduction_total' => 0,
		];
		foreach ($leaveInfos as $li) {
			$grandBase['leave_' . strtoupper($li->code)] = 0;
		}
		$grand = $grandBase;
		$sheetRows = [];

		foreach ($byDept as $deptId => $deptEmps) {
			$bySec = $deptEmps->groupBy('section_id');
			foreach ($bySec as $secId => $secEmps) {
				$rows = [];
				$secTotals = $grandBase; // same shape, reset to zero

				foreach ($secEmps as $emp) {
					$sd = \ME\Hr\Services\SalaryReportService::getEmployeeSalaryData($emp, $from, $to, $request, $employeeDataFn);

					$salaryTotal = $sd['basic'] + $sd['house_rent'] + $sd['medical'] + $sd['transport'] + $sd['food_allow'];
					$otRate = (float) ($sd['ot_rate'] ?? 0);
					$presentDays = (int) ($sd['present'] ?? 0);
					$absentDays = (int) ($sd['absent'] ?? 0);
					$attBonus = (float) ($sd['att_bonus'] ?? 0);
					$loan = (float) ($sd['loan'] ?? 0);
					$tax = (float) ($sd['tax'] ?? 0);
					$stamp = (float) ($sd['stamp'] ?? 0);
					$deductOther = (float) ($sd['deduct_other'] ?? 0);
					$otherEarn = (float) ($sd['allow_other'] ?? 0) + (float) ($sd['arrear'] ?? 0);
					$wphAmount = (float) ($sd['wph_amount'] ?? 0);
					$otAmount = (float) ($sd['ot'] ?? 0);
					$extraFacility = (float) ($sd['extra_facility'] ?? 0);

					$absentBase = ($factoryNo === 1 || $factoryNo === 2) ? $sd['basic'] : $sd['gross'];
					$deductAbsent = $absentDays > 0
						? round(($absentBase / $deductionMonthDays) * $absentDays, 2)
						: 0;
					$looksLikeNoPresentFullPay = $presentDays === 0
						&& $absentDays > 0
						&& (float) ($sd['net'] ?? 0) >= (float) ($sd['gross'] ?? 0);
					if ($looksLikeNoPresentFullPay && $deductAbsent <= 0 && $deductionMonthDays > 0) {
						$deductAbsent = round(($absentBase / $deductionMonthDays) * $absentDays, 2);
					}

					$payableSalary = max(0, ($salaryTotal + $attBonus + $wphAmount + $otherEarn) - $deductAbsent);
					$deductionTotal = (float) ($sd['total_deduct'] ?? 0);
					if ($looksLikeNoPresentFullPay) {
						$deductionTotal = max($deductionTotal, $deductAbsent + $loan + $tax + $stamp + $deductOther);
					}
					$netSalary = $looksLikeNoPresentFullPay
						? max(0, $payableSalary + $otAmount + $extraFacility - ($loan + $tax + $stamp + $deductOther))
						: (float) ($sd['net'] ?? 0);

					$row = [
						'emp'            => $emp,
						'basic'          => $sd['basic'],
						'house'          => $sd['house_rent'],
						'medical'        => $sd['medical'],
						'transport'      => $sd['transport'],
						'food'           => $sd['food_allow'],
						'salary_total'   => $salaryTotal,
						'pr'             => $presentDays,
						'wh'             => $sd['wh'],
						'fh'             => $sd['fh'],
						'ab'             => $absentDays,
						'earn_days'      => $totalMonthDays - $absentDays,
						'att_bonus'      => $attBonus,
						'deduct_absent'  => $deductAbsent,
						'loan'           => $loan,
						'tax'            => $tax,
						'stamp'          => $stamp,
						'deduct_other'   => $deductOther,
						'wph_days'       => $sd['wph_days'],
						'wph_amount'     => $wphAmount,
						'other_earn'     => $otherEarn,
						'gross'          => $sd['gross'],
						'payable'        => $payableSalary,
						'ot_hours'       => $sd['ot_hours'],
						'ot_rate'        => $otRate,
						'ot_total'       => $otAmount,
						'extra_facility' => $extraFacility,
						'net'            => $netSalary,
						'deduction_total'=> $deductionTotal,
					];
					foreach ($leaveInfos as $li) {
						$code = strtoupper($li->code);
						$row['leave_' . $code] = (int) ($sd['leaves_by_code'][$code] ?? 0);
					}
					$rows[] = $row;

					$secTotals['emp']++;
					$grand['emp']++;
					foreach (array_keys($grandBase) as $k) {
						if ($k === 'emp') continue;
						$secTotals[$k] = ($secTotals[$k] ?? 0) + ($row[$k] ?? 0);
						$grand[$k]     = ($grand[$k] ?? 0) + ($row[$k] ?? 0);
					}
				}

				if (!empty($rows)) {
					$sheetRows[] = [
						'dept_id' => $deptId,
						'sec_id'  => $secId,
						'rows'    => $rows,
						'totals'  => $secTotals,
					];
				}
			}
		}

		// Total columns for colspan calculations (single-line header)
		$totalCols = $withPicture ? 34 : 33;
		$labelCols = $withPicture ? 8 : 7; // Sl-NO..Grade, spanned as the total-row label

		$numberToWords = function ($number) use (&$numberToWords) {
			$number = (int) $number;
			if ($number === 0) {
				return 'zero';
			}

			$ones = [
				'', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
				'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
				'seventeen', 'eighteen', 'nineteen'
			];
			$tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

			$convertBelowThousand = function ($n) use ($ones, $tens) {
				$text = '';
				$hundreds = intdiv($n, 100);
				$rest = $n % 100;

				if ($hundreds > 0) {
					$text .= $ones[$hundreds] . ' hundred';
					if ($rest > 0) {
						$text .= ' ';
					}
				}

				if ($rest > 0) {
					if ($rest < 20) {
						$text .= $ones[$rest];
					} else {
						$text .= $tens[intdiv($rest, 10)];
						$u = $rest % 10;
						if ($u > 0) {
							$text .= '-' . $ones[$u];
						}
					}
				}

				return trim($text);
			};

			$scales = [
				1000000000 => 'billion',
				1000000 => 'million',
				1000 => 'thousand',
				1 => ''
			];

			$parts = [];
			foreach ($scales as $base => $label) {
				if ($number >= $base) {
					$chunk = intdiv($number, $base);
					$number %= $base;
					if ($chunk > 0) {
						$piece = $convertBelowThousand($chunk);
						if ($label !== '') {
							$piece .= ' ' . $label;
						}
						$parts[] = trim($piece);
					}
				}
			}

			return trim(implode(' ', $parts));
		};

		$inWords = $numberToWords((int) round($grand['net']));
	@endphp

	<div class="sheet-top">
		<div>
			<strong>Report:</strong> Salary Sheet<br>
			<strong>Period:</strong> {{ $fromLabel }} - {{ $toLabel }}
		</div>
		<div class="sheet-right">
			<div class="row"><span>Total Month Day's :</span><strong>{{ $totalMonthDays }}</strong></div>
			<div class="row"><span>Total Working Day's :</span><strong>{{ $totalWorkingDays }}</strong></div>
			<div class="row"><span>Weekly holiday's :</span><strong>{{ $weekendCount }}</strong></div>
			<div class="row"><span>Others Holiday's :</span><strong>{{ $otherHolidayCount }}</strong></div>
		</div>
	</div>

	@if(empty($sheetRows))
		<p style="text-align:center;color:#888;padding:10px 0;">No employees found.</p>
	@else
		<table class="sheet-table">
			<thead>
				<tr>
					<th>Sl-NO</th>
					<th>Card no</th>
					<th>Name</th>
					@if($withPicture)<th>Photo</th>@endif
					<th>Join Date</th>
					<th>Designation</th>
					<th>Section</th>
					<th>Grade</th>
					<th>Basic</th>
					<th>H.R</th>
					<th>M/A</th>
					<th>F/A</th>
					<th>T/A</th>
					<th>Gross Salary</th>
					<th>Month Day</th>
					<th>Work Day</th>
					<th>Holy Day</th>
					<th>E/L</th>
					<th>S/L</th>
					<th>C/L</th>
					<th>Pay Day</th>
					<th>Absent Day</th>
					<th>Absent TK</th>
					<th>Att: Bonus</th>
					<th>Other Alowance</th>
					<th>Total Salary</th>
					<th>OT. Hour</th>
					<th>Rate</th>
					<th>OT-Amount</th>
					<th>Net Salary</th>
					<th>Advance Paid</th>
					<th>Revenue</th>
					<th>Payable</th>
					<th>Stamp</th>
				</tr>
			</thead>
			<tbody>
				@php $sl = 1; @endphp
				@foreach($sheetRows as $group)
					<tr class="sheet-sec-row">
						<td colspan="{{ $totalCols }}" class="tl">
							{{ $departmentMap->get($group['dept_id'], 'N/A') }} &mdash; {{ $sectionMap->get($group['sec_id'], 'N/A') }}
						</td>
					</tr>
					@foreach($group['rows'] as $row)
						@php
							$employee   = $row['emp'];
							$desigEntry = $designationMap->get($employee->designation_id, []);
						@endphp
						<tr>
							<td class="tc">{{ $sl++ }}</td>
							<td class="tc">{{ $employee->employee_id }}</td>
							<td>{{ $language === 'bn' && $employee->bn_name ? $employee->bn_name : $employee->name }}</td>
							@if($withPicture)
								<td class="tc photo-cell">
									<img src="{{ asset($employee->image()) }}" alt="" style="width:32px;height:32px;object-fit:cover;">
								</td>
							@endif
							<td class="tc">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-Y') : '-' }}</td>
							<td>{{ $desigEntry['name'] ?? 'N/A' }}</td>
							<td>{{ $sectionMap->get($group['sec_id'], 'N/A') }}</td>
							<td class="tc">{{ $desigEntry['grade'] ?? '-' }}</td>

							<td class="tr">{{ number_format($row['basic']) }}</td>
							<td class="tr">{{ number_format($row['house']) }}</td>
							<td class="tr">{{ number_format($row['medical']) }}</td>
							<td class="tr">{{ number_format($row['food']) }}</td>
							<td class="tr">{{ number_format($row['transport']) }}</td>
							<td class="tr"><strong>{{ number_format($row['gross']) }}</strong></td>

							<td class="tc">{{ $totalMonthDays }}</td>
							<td class="tc">{{ $row['pr'] }}</td>
							<td class="tc">{{ $row['wh'] + $row['fh'] }}</td>

							<td class="tc">{{ $row['leave_EL'] ?? 0 }}</td>
							<td class="tc">{{ $row['leave_SL'] ?? 0 }}</td>
							<td class="tc">{{ $row['leave_CL'] ?? 0 }}</td>

							<td class="tc">{{ $row['earn_days'] }}</td>
							<td class="tc">{{ $row['ab'] }}</td>
							<td class="tr">{{ number_format($row['deduct_absent']) }}</td>
							<td class="tr">{{ number_format($row['att_bonus']) }}</td>
							<td class="tr">{{ number_format($row['extra_facility']) }}</td>
							<td class="tr"><strong>{{ number_format($row['salary_total']) }}</strong></td>

							<td class="tc">{{ number_format($row['ot_hours'], 2) }}</td>
							<td class="tc">{{ number_format($row['ot_rate'], 2) }}</td>
							<td class="tr">{{ number_format($row['ot_total']) }}</td>

							<td class="tr"><strong>{{ number_format($row['payable']) }}</strong></td>
							<td class="tr">{{ number_format($row['loan']) }}</td>
							<td class="tr">{{ number_format($row['stamp']) }}</td>
							<td class="tr"><strong>{{ number_format($row['net']) }}</strong></td>
							<td class="stamp-box"></td>
						</tr>
					@endforeach

					<tr class="sheet-sec-total">
						<td colspan="{{ $labelCols }}" class="tl">{{ $sectionMap->get($group['sec_id'], 'N/A') }} Total</td>
						<td class="tr">{{ number_format($group['totals']['basic']) }}</td>
						<td class="tr">{{ number_format($group['totals']['house']) }}</td>
						<td class="tr">{{ number_format($group['totals']['medical']) }}</td>
						<td class="tr">{{ number_format($group['totals']['food']) }}</td>
						<td class="tr">{{ number_format($group['totals']['transport']) }}</td>
						<td class="tr">{{ number_format($group['totals']['gross']) }}</td>
						<td class="tc">{{ $totalMonthDays }}</td>
						<td class="tc">{{ $group['totals']['pr'] }}</td>
						<td class="tc">{{ $group['totals']['wh'] + $group['totals']['fh'] }}</td>
						<td class="tc">{{ $group['totals']['leave_EL'] ?? 0 }}</td>
						<td class="tc">{{ $group['totals']['leave_SL'] ?? 0 }}</td>
						<td class="tc">{{ $group['totals']['leave_CL'] ?? 0 }}</td>
						<td class="tc">{{ $group['totals']['earn_days'] }}</td>
						<td class="tc">{{ $group['totals']['ab'] }}</td>
						<td class="tr">{{ number_format($group['totals']['deduct_absent']) }}</td>
						<td class="tr">{{ number_format($group['totals']['att_bonus']) }}</td>
						<td class="tr">{{ number_format($group['totals']['extra_facility']) }}</td>
						<td class="tr">{{ number_format($group['totals']['salary_total']) }}</td>
						<td class="tc">{{ number_format($group['totals']['ot_hours'], 2) }}</td>
						<td class="tc">{{ number_format($group['totals']['emp'] > 0 ? $group['totals']['ot_rate'] / $group['totals']['emp'] : 0, 2) }}</td>
						<td class="tr">{{ number_format($group['totals']['ot_total']) }}</td>
						<td class="tr">{{ number_format($group['totals']['payable']) }}</td>
						<td class="tr">{{ number_format($group['totals']['loan']) }}</td>
						<td class="tr">{{ number_format($group['totals']['stamp']) }}</td>
						<td class="tr">{{ number_format($group['totals']['net']) }}</td>
						<td class="tr"></td>
					</tr>
				@endforeach

				<tr class="sheet-grand">
					<td colspan="{{ $labelCols }}" class="tl">Grand Total Amount :</td>
					<td class="tr">{{ number_format($grand['basic']) }}</td>
					<td class="tr">{{ number_format($grand['house']) }}</td>
					<td class="tr">{{ number_format($grand['medical']) }}</td>
					<td class="tr">{{ number_format($grand['food']) }}</td>
					<td class="tr">{{ number_format($grand['transport']) }}</td>
					<td class="tr">{{ number_format($grand['gross']) }}</td>
					<td class="tc">{{ $totalMonthDays }}</td>
					<td class="tc">{{ $grand['pr'] }}</td>
					<td class="tc">{{ $grand['wh'] + $grand['fh'] }}</td>
					<td class="tc">{{ $grand['leave_EL'] ?? 0 }}</td>
					<td class="tc">{{ $grand['leave_SL'] ?? 0 }}</td>
					<td class="tc">{{ $grand['leave_CL'] ?? 0 }}</td>
					<td class="tc">{{ $grand['earn_days'] }}</td>
					<td class="tc">{{ $grand['ab'] }}</td>
					<td class="tr">{{ number_format($grand['deduct_absent']) }}</td>
					<td class="tr">{{ number_format($grand['att_bonus']) }}</td>
					<td class="tr">{{ number_format($grand['extra_facility']) }}</td>
					<td class="tr">{{ number_format($grand['salary_total']) }}</td>
					<td class="tc">{{ number_format($grand['ot_hours'], 2) }}</td>
					<td class="tc">{{ number_format($grand['emp'] > 0 ? $grand['ot_rate'] / $grand['emp'] : 0, 2) }}</td>
					<td class="tr">{{ number_format($grand['ot_total']) }}</td>
					<td class="tr">{{ number_format($grand['payable']) }}</td>
					<td class="tr">{{ number_format($grand['loan']) }}</td>
					<td class="tr">{{ number_format($grand['stamp']) }}</td>
					<td class="tr">{{ number_format($grand['net']) }}</td>
					<td class="tr"></td>
				</tr>
			</tbody>
		</table>

		<div class="sheet-inwords">
			In Words :
			@if($inWords)
				Taka {{ ucfirst($inWords) }} only
			@else
				Taka {{ number_format($grand['net'], 2) }} only
			@endif
		</div>
	@endif
@endif
