@push('css')
<style>
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; }

.report-head { text-align:center; margin-bottom:4px; line-height:1.25; }
.report-head h3 { margin:0; font-size:17px; font-weight:700; }
.report-head p { margin:1px 0 0; font-size:11px; color:#333; }
.sub-title { font-size:13px; font-weight:700; margin:8px 0 10px; text-align:center; }
.rpt-meta { display:flex; justify-content:space-between; align-items:center; font-size:11px; margin-bottom:8px; color:#000; }

.t { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:10px; }
.t th, .t td { border:1px solid #000; padding:4px 6px; }
.t thead th { text-align:center; font-weight:700; }
.t .tc { text-align:center; }
.t .tr { text-align:right; }
.t .tl { text-align:left; }
.t tbody tr:nth-child(even) td { background:#f2f2f2; }

.grand-total td { font-weight:700; border-top:2px solid #000; }

.rpt-footer { margin-top:48px; }
.sig-row { display:flex; justify-content:space-between; }
.sig-box { width:30%; font-size:11px; line-height:1.5; }
.sig-box .sig-role { font-weight:700; }
.sig-box .sig-line { margin-top:32px; }

@media print {
	@page { size: A4 landscape; margin: 10mm; }
	body { margin: 0; }
}
</style>
@endpush

@php
	$company = hr_factory('name') ?? 'Company Name';
	$address = hr_factory('address') ?? '';
	$salaryKey = \ME\Hr\Models\HrSalaryKey::where('status', 'active')->latest('id')->first();
	$salaryDate = $salaryKey?->payment_date ? \Carbon\Carbon::parse($salaryKey->payment_date)->format('d M Y') : now()->format('d M Y');
	$fmt = fn ($v) => number_format((float) $v);
	$fmtHrs = fn ($v) => number_format((float) $v, 2);

	$periodStart = \Carbon\Carbon::parse($from);
	$periodEnd = \Carbon\Carbon::parse($to);
	$monthLabel = $periodStart->isSameMonth($periodEnd)
		? $periodStart->format('F Y')
		: $fromLabel . ' to ' . $toLabel;
@endphp

<div class="report-head">
	@if(!blank(optional(general())->logo()))
		<img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
	@endif
	<h3>{{ $company }}</h3>
	<p>{{ $address }}</p>
</div>
<div class="sub-title">Salary Summary</div>
<div class="rpt-meta">
	<span><strong>Month:</strong> {{ $monthLabel }}</span>
	<span><strong>Salary Date:</strong> {{ $salaryDate }}</span>
</div>

<table class="t">
	<thead>
		<tr>
			<th>Department</th>
			<th>Basic</th>
			<th>Gross Salary</th>
			<th>Total Salary</th>
			<th>OT. Hour</th>
			<th>OT-Amount</th>
			<th>Net Salary</th>
			<th>Advance Paid</th>
			<th>Revenue</th>
			<th>Payable</th>
		</tr>
	</thead>
	<tbody>
		@forelse($summaryRows as $row)
			<tr>
				<td class="tl">{{ $row['label'] }}</td>
				<td class="tr">{{ $fmt($row['basic']) }}</td>
				<td class="tr">{{ $fmt($row['gross']) }}</td>
				<td class="tr">{{ $fmt($row['total']) }}</td>
				<td class="tr">{{ $fmtHrs($row['ot_hours']) }}</td>
				<td class="tr">{{ $fmt($row['ot_amount']) }}</td>
				<td class="tr">{{ $fmt($row['net']) }}</td>
				<td class="tr">{{ $fmt($row['advance']) }}</td>
				<td class="tr">{{ $fmt($row['revenue']) }}</td>
				<td class="tr">{{ $fmt($row['payable']) }}</td>
			</tr>
		@empty
			<tr><td colspan="10" class="tc" style="padding:12px;color:#888;">No salary data found for the selected period.</td></tr>
		@endforelse
	</tbody>
	<tfoot>
		<tr class="grand-total">
			<td class="tl">Grand Total Amount :</td>
			<td class="tr">{{ $fmt($grandTotals['basic']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['gross']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['total']) }}</td>
			<td class="tr">{{ $fmtHrs($grandTotals['ot_hours']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['ot_amount']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['net']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['advance']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['revenue']) }}</td>
			<td class="tr">{{ $fmt($grandTotals['payable']) }}</td>
		</tr>
	</tfoot>
</table>

<div class="rpt-footer">
	<div class="sig-row">
		<div class="sig-box">
			<div class="sig-role">Prepared By</div>
			<div>IT Department</div>
			<div class="sig-line">Signature:_______________</div>
			<div>Date:_______________</div>
		</div>
		<div class="sig-box">
			<div class="sig-role">Checked By</div>
			<div>HR Admin Department</div>
			<div class="sig-line">Signature:_______________</div>
			<div>Date:_______________</div>
		</div>
		<div class="sig-box">
			<div class="sig-role">Approved By</div>
			<div>Managing Director</div>
			<div class="sig-line">Signature:_______________</div>
			<div>Date:_______________</div>
		</div>
	</div>
</div>
