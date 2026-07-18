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
.t tbody tr:nth-child(even) td { background:#f5f8fc; }
.tc { text-align:center; }
.tr { text-align:right; }
.tl { text-align:left; }

.grand-total td { background:#1a3a5c; color:#fff; font-weight:700; font-size:10px; border:1px solid #1a3a5c; }

.stat-bar { display:flex; gap:8px; margin-bottom:10px; }
.stat-box { flex:1; border:1px solid #2e6da4; border-radius:3px; padding:5px 8px; text-align:center; background:#f0f6fc; }
.stat-box .val { font-size:14px; font-weight:700; color:#1a3a5c; display:block; }
.stat-box .lbl { font-size:8.5px; color:#555; text-transform:uppercase; letter-spacing:.4px; }

.dept-title { font-size:10.5px; font-weight:700; background:#1a3a5c; color:#fff; padding:4px 8px; margin:12px 0 2px; letter-spacing:.3px; }
.summary-row td { background:#dcedc8; font-weight:700; }
.photo-cell img { max-height:20mm; }

.rpt-footer { margin-top:18px; border-top:1.5px solid #1a3a5c; padding-top:8px; }
.sig-row { display:flex; justify-content:space-between; margin-top:24px; }
.sig-box { text-align:center; width:18%; }
.sig-box .sig-line { border-top:1px solid #333; margin-bottom:3px; }
.sig-box .sig-lbl { font-size:8.5px; color:#333; }
.rpt-footer-note { font-size:8px; color:#666; text-align:center; margin-top:10px; }

@media print {
	@page { size: A4 landscape; margin: 7mm; }
	body { margin: 0; }
}
</style>
@endpush

@php
	$company = hr_factory('name') ?? 'Company Name';
	$address = hr_factory('address') ?? '';
	$salaryKey = \ME\Hr\Models\HrSalaryKey::where('status', 'active')->latest('id')->first();
	$salaryDate = $salaryKey?->payment_date ? \Carbon\Carbon::parse($salaryKey->payment_date)->format('d M Y') : now()->format('d M Y');
	$fmt = fn($v) => number_format((float) $v, 2);
@endphp

<div class="rpt-header">
	@if(!blank(optional(general())->logo()))
		<img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
	@endif
	<h2>{{ $company }}</h2>
	<p>{{ $address }}</p>
</div>
<div class="rpt-title-bar">
	<h4>{{ $reportTypeLabel }}</h4>
	<span>Period: {{ $fromLabel }} &mdash; {{ $toLabel }}</span>
</div>
<div class="rpt-meta">
	<span><strong>Salary Date:</strong> {{ $salaryDate }}</span>
	<span><strong>Currency:</strong> BDT (Bangladeshi Taka)</span>
</div>

@if(!filled($request->input('bonus_title')))
	<div style="padding:20px;text-align:center;color:#888;">Please select a Bonus Title to generate the bonus salary report.</div>
@elseif($bonusPolicies->isEmpty())
	<div style="padding:20px;text-align:center;color:#888;">No bonus policies found for the selected Bonus Title.</div>
@else
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
		<div class="dept-title">&nbsp;{{ isset($groupLabel) ? $groupLabel((string) $deptId) : ('Department: ' . $departmentMap->get($deptId, 'N/A')) }}</div>
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
						<td>{{ $designationMap->get($employee->designation_id)['name'] ?? 'N/A' }}</td>
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
					$totalCols    = ($withPicture ? 12 : 11) + ($hasPctPolicy ? 2 : 0);
					$totalColspan = $totalCols - 3;
				@endphp
				<tr class="summary-row">
					<td colspan="{{ $totalColspan }}" class="tr">{{ (($groupBy ?? 'department') === 'department' ? 'Dept.' : 'Group') }} Bonus Total:</td>
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
