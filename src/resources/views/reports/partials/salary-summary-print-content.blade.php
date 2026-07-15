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
	<span><strong>Print Date:</strong> {{ now()->format('d M Y, h:i A') }}</span>
	<span><strong>Currency:</strong> BDT (Bangladeshi Taka)</span>
</div>

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
