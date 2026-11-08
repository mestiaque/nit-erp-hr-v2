                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-right">Gross</th>
                                <th class="text-center">Pay Days</th>
                                <th class="text-center">Present</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Leave</th>
                                <th class="text-right">Absent Deduct</th>
                                <th class="text-right">Att. Bonus</th>
                                <th class="text-right">OT</th>
                                <th class="text-right">Advance</th>
                                <th class="text-right">Stamp</th>
                                <th class="text-right">Net Pay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($monthlySalaries as $row)
                            <tr>
                                <td>{{ $row['month'] }}</td>
                                <td class="text-right">{{ number_format($row['gross'], 2) }}</td>
                                <td class="text-center">{{ $row['pay_days'] }}</td>
                                <td class="text-center">{{ $row['present'] }}</td>
                                <td class="text-center">{{ $row['absent'] }}</td>
                                <td class="text-center">{{ $row['leave'] }}</td>
                                <td class="text-right text-danger">{{ number_format($row['deduct_absent'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['att_bonus'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['ot'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['advance'], 2) }}</td>
                                <td class="text-right">{{ number_format($row['stamp'], 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($row['net'], 2) }}</td>
                                <td>
                                    @if($row['locked'])<span class="badge badge-success">Locked</span>
                                    @elseif($row['running'])<span class="badge badge-warning">Running</span>
                                    @else<span class="badge badge-light">Not locked</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="13" class="text-center text-muted">No attendance recorded yet.</td></tr>
                        @endforelse
                        </tbody>
                        @if($monthlySalaries->isNotEmpty())
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="6">Total</th>
                                <th class="text-right text-danger">{{ number_format($monthlySalaries->sum('deduct_absent'), 2) }}</th>
                                <th class="text-right">{{ number_format($monthlySalaries->sum('att_bonus'), 2) }}</th>
                                <th class="text-right">{{ number_format($monthlySalaries->sum('ot'), 2) }}</th>
                                <th class="text-right">{{ number_format($monthlySalaries->sum('advance'), 2) }}</th>
                                <th class="text-right">{{ number_format($monthlySalaries->sum('stamp'), 2) }}</th>
                                <th class="text-right">{{ number_format($monthlySalaries->sum('net'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
