@extends('printMaster2')

@section('title', 'Daily Attendance Report - ' . $dateLabel)

@push('css')
<style>
.report-head { text-align:center; margin-bottom:10px; }
.report-head h3 { margin:0 0 2px; font-size:15px; }
.report-head p  { margin:0; font-size:11px; }
.sub-title  { font-size:12px; font-weight:700; margin:8px 0 4px; }
.section-title { font-size:11px; font-weight:700; background:#dde6f0; padding:3px 6px; margin:10px 0 2px; }
.emp-block-title { font-size:10.5px; font-weight:700; background:#f4f4f4; padding:2px 6px; margin:6px 0 1px; }
.t { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10px; }
.t th, .t td { border:1px solid #555; padding:3px 5px; }
.t th { background:#eef1d4; text-align:center; }
.tc { text-align:center; }
.status-present { color:green; font-weight:700; }
.status-absent  { color:red; font-weight:700; }
.status-late    { color:#b8860b; font-weight:700; }
.summary-title { font-size:11px; font-weight:700; margin:6px 0 4px; }
.stat-bar { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
.stat-box { flex:1; min-width:80px; border:1px solid #2e6da4; border-radius:3px; padding:5px 8px; text-align:center; background:#f0f6fc; }
.stat-box .val { font-size:14px; font-weight:700; color:#1a3a5c; display:block; }
.stat-box .lbl { font-size:8.5px; color:#555; text-transform:uppercase; letter-spacing:.4px; }
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';
@endphp

<div class="report-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ $company }}</h3>
    <p>{{ $address }}</p>
</div>

<div class="sub-title">Daily Attendance Report &mdash; {{ $dateLabel }}</div>

<div class="summary-title">Summary</div>
<div class="stat-bar">
    <div class="stat-box">
        <span class="val">{{ $summary['total_employees'] }}</span>
        <span class="lbl">Total Employees</span>
    </div>
    @foreach($summary['status_counts'] as $statusLabel => $count)
        <div class="stat-box">
            <span class="val">{{ $count }}</span>
            <span class="lbl">{{ $statusLabel }}</span>
        </div>
    @endforeach
    <div class="stat-box">
        <span class="val">{{ number_format($summary['total_ot_hours'], 2) }}</span>
        <span class="lbl">Total OT Hrs</span>
    </div>
</div>

@forelse($groups as $groupKey => $groupRows)
    @if($groupBy !== 'none')
        <div class="section-title">{{ $groupLabel((string) $groupKey) }}</div>
    @endif

    @if(!$isRange)
        {{-- Single day: one flat row per employee --}}
        <table class="t">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Emp. ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Status</th>
                    <th>OT Hrs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupRows as $i => $row)
                    <tr>
                        <td class="tc">{{ $i + 1 }}</td>
                        <td class="tc">{{ $row['employee_id'] }}</td>
                        <td>{{ $row['name'] }}</td>
                        <td>{{ $row['designation'] }}</td>
                        <td class="tc">{{ $row['in_time'] }}</td>
                        <td class="tc">{{ $row['out_time'] }}</td>
                        <td class="tc status-{{ \Illuminate\Support\Str::slug($row['status']) }}">{{ $row['status'] }}</td>
                        <td class="tc">{{ number_format($row['ot_hours'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        {{-- Date range: grouped by employee, one mini-table per employee across the range --}}
        @foreach($groupRows as $row)
            <div class="emp-block-title">{{ $row['employee_id'] }} &mdash; {{ $row['name'] }} ({{ $row['designation'] }})</div>
            <table class="t">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Status</th>
                        <th>OT Hrs</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($row['days'] as $day)
                        <tr>
                            <td class="tc">{{ $day['date'] }}</td>
                            <td class="tc">{{ $day['in_time'] }}</td>
                            <td class="tc">{{ $day['out_time'] }}</td>
                            <td class="tc status-{{ \Illuminate\Support\Str::slug($day['status']) }}">{{ $day['status'] }}</td>
                            <td class="tc">{{ number_format($day['ot_hours'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="tc" style="padding:6px;color:#888;">No attendance data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endif
@empty
    <p style="text-align:center;color:#888;padding:12px 0;">No employees found.</p>
@endforelse
@endsection
