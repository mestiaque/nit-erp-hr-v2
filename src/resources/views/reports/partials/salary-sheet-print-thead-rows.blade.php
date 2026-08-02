<tr class="grp">
	<th rowspan="2">S.N</th>
	@if($withPicture)<th rowspan="2">Photo</th>@endif
	<th rowspan="2">Emp.<br>ID</th>
	<th rowspan="2">Name</th>
	<th rowspan="2">Designation</th>
	<th rowspan="2">Grade</th>
	<th rowspan="2">Joining Date</th>
	<th rowspan="2">Gross<br>Salary</th>
	<th colspan="6">Salary Components</th>
	<th rowspan="2">Month<br>Days</th>
	<th rowspan="2">Present</th>
	<th colspan="{{ $leaveCols }}">Leave</th>
	<th rowspan="2">Absent</th>
	<th rowspan="2">Earn<br>Days</th>
	<th rowspan="2">Att.<br>Bonus</th>
	<th colspan="4">Deduction</th>
	<th rowspan="2">Others<br>Deduct.</th>
	<th colspan="2">WP &amp; HP</th>
	<th rowspan="2">Others<br>Earning</th>
	<th rowspan="2">Payable<br>Salary</th>
	<th colspan="3">Over Time</th>
	<th rowspan="2">Extra<br>Facility</th>
	<th rowspan="2">Net Payable<br>Salary</th>
	<th rowspan="2">Signature<br>&amp; Stamp</th>
</tr>
<tr class="sub">
	<th>Basic</th>
	<th>House<br>Rent</th>
	<th>Medical</th>
	<th>Lunch</th>
	<th>Trans.</th>
	<th>Total</th>
	<th>WH</th>
	<th>FL</th>
	<th>GL</th>
	@foreach($leaveInfos as $li)
		<th>{{ $li->code }}</th>
	@endforeach
	<th>Absent<br>Amt</th>
	<th>Adv<br>Amt</th>
	<th>Tax</th>
	<th>Stamp</th>
	<th>Days</th>
	<th>Amt</th>
	<th>OT/H</th>
	<th>Rate</th>
	<th>Amt</th>
</tr>
