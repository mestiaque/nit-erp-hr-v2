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

.photo-cell img { max-height:20mm; }

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
	@page { size: A4 landscape; margin: 7mm; }
	body { margin: 0; }
}
</style>
@endpush

@php
	$company = hr_factory('name') ?? 'Company Name';
	$address = hr_factory('address') ?? '';
	$totalCols = $withPicture ? 34 : 33;
	$labelCols = $withPicture ? 8 : 7;
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
								<img src="{{ asset($employee->image()) }}" alt="" style="object-fit:cover;">
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
					<td colspan="{{ $labelCols }}" class="tl">{{ $departmentMap->get($group['dept_id'], 'N/A') }} &mdash; {{ $sectionMap->get($group['sec_id'], 'N/A') }} Total</td>
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
