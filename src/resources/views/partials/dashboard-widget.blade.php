

@php
    try {
        $hrStats = \ME\Hr\Http\Controllers\HrDashboardController::stats();
    } catch (\Throwable $e) {
        $hrStats = null;
    }
@endphp

@if($hrStats)
@php
    $s          = $hrStats;
    $deptNames  = $s['departments']->pluck('name')->toJson();
    $deptCounts = $s['departments']->pluck('employees_count')->toJson();
    $attLabels  = $s['last30']->pluck('label')->toJson();
    $attData    = $s['last30']->pluck('present')->toJson();
    $joinLabels = $s['joinTrend']->pluck('label')->toJson();
    $joinData   = $s['joinTrend']->pluck('count')->toJson();
    $widgetId   = 'hr_widget_' . uniqid();
@endphp

<style>
.hr-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px 18px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    border: none;
    transition: transform .2s, box-shadow .2s;
    height: 100%;
}
.hr-stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.11); }
.hr-stat-icon {
    width: 54px; height: 54px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.hr-stat-val { font-size: 26px; font-weight: 700; line-height: 1; margin-bottom: 3px; }
.hr-stat-lbl { font-size: 12px; color: #888; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }
.hr-section-title {
    font-size: 13px; font-weight: 700; color: #444;
    text-transform: uppercase; letter-spacing: 1px;
    border-left: 3px solid #6366f1; padding-left: 10px;
    margin-bottom: 16px;
}
.hr-chart-card {
    background: #fff; border-radius: 12px;
    padding: 18px 20px; box-shadow: 0 2px 12px rgba(0,0,0,.07); height: 100%;
}
.hr-quick-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-radius: 10px;
    background: #f8f9ff; border: 1px solid #e8eaf0;
    color: #444; font-size: 13px; font-weight: 500;
    text-decoration: none; transition: all .2s;
}
.hr-quick-btn:hover { background: #6366f1; color: #fff; border-color: #6366f1; }
.hr-quick-btn i { width: 20px; text-align: center; }
.hr-recent-table td { font-size: 13px; vertical-align: middle; padding: 8px 10px; }
.hr-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
</style>

{{-- ── Section Header ── --}}
<div class="d-flex align-items-center justify-content-between mb-3 mt-1">
    <h4 class="mb-0" style="font-size:17px;font-weight:700;">
        <i class="fa fa-id-badge me-2" style="color:#6366f1;"></i> HR &amp; Compliance
    </h4>
    @if(Route::has('hr-center.index'))
    <a href="{{ route('hr-center.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:12px;">
        <i class="fa fa-tachometer-alt me-1"></i> HR Dashboard
    </a>
    @endif
</div>

{{-- ── Stat Cards ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg">
        <div class="hr-stat-card">
            <div class="hr-stat-icon" style="background:#eef2ff;">
                <i class="fa fa-users" style="color:#6366f1;"></i>
            </div>
            <div>
                <div class="hr-stat-val" style="color:#6366f1;">{{ number_format($s['totalEmployees']) }}</div>
                <div class="hr-stat-lbl">Total Employees</div>
                <div style="margin-top:6px;background:#eef2ff;border-radius:4px;height:4px;">
                    <div style="width:100%;height:4px;border-radius:4px;background:#6366f1;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="hr-stat-card">
            <div class="hr-stat-icon" style="background:#ecfdf5;">
                <i class="fa fa-user-check" style="color:#10b981;"></i>
            </div>
            <div>
                <div class="hr-stat-val" style="color:#10b981;">{{ number_format($s['presentToday']) }}</div>
                <div class="hr-stat-lbl">Present Today</div>
                @php $pct = $s['totalEmployees'] > 0 ? round($s['presentToday']/$s['totalEmployees']*100) : 0; @endphp
                <div style="margin-top:6px;background:#ecfdf5;border-radius:4px;height:4px;">
                    <div style="width:{{ $pct }}%;height:4px;border-radius:4px;background:#10b981;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="hr-stat-card">
            <div class="hr-stat-icon" style="background:#fff1f2;">
                <i class="fa fa-user-times" style="color:#f43f5e;"></i>
            </div>
            <div>
                <div class="hr-stat-val" style="color:#f43f5e;">{{ number_format($s['absentToday']) }}</div>
                <div class="hr-stat-lbl">Absent Today</div>
                @php $aPct = $s['totalEmployees'] > 0 ? round($s['absentToday']/$s['totalEmployees']*100) : 0; @endphp
                <div style="margin-top:6px;background:#fff1f2;border-radius:4px;height:4px;">
                    <div style="width:{{ $aPct }}%;height:4px;border-radius:4px;background:#f43f5e;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="hr-stat-card">
            <div class="hr-stat-icon" style="background:#faf5ff;">
                <i class="fa fa-user-clock" style="color:#a855f7;"></i>
            </div>
            <div>
                <div class="hr-stat-val" style="color:#a855f7;">{{ number_format($s['lateToday']) }}</div>
                <div class="hr-stat-lbl">Late Today</div>
                @php $lPct = $s['totalEmployees'] > 0 ? round($s['lateToday']/$s['totalEmployees']*100) : 0; @endphp
                <div style="margin-top:6px;background:#faf5ff;border-radius:4px;height:4px;">
                    <div style="width:{{ $lPct }}%;height:4px;border-radius:4px;background:#a855f7;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="hr-stat-card">
            <div class="hr-stat-icon" style="background:#fffbeb;">
                <i class="fa fa-user-plus" style="color:#f59e0b;"></i>
            </div>
            <div>
                <div class="hr-stat-val" style="color:#f59e0b;">{{ number_format($s['newThisMonth']) }}</div>
                <div class="hr-stat-lbl">New This Month</div>
                <div style="margin-top:6px;background:#fffbeb;border-radius:4px;height:4px;">
                    <div style="width:60%;height:4px;border-radius:4px;background:#f59e0b;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row ── --}}
<div class="row g-3 mb-4">

    {{-- Attendance 30-day bar chart --}}
    <div class="col-lg-8">
        <div class="hr-chart-card">
            <div class="hr-section-title">Attendance – Last 30 Days</div>
            <div id="{{ $widgetId }}_att" style="height:220px;"></div>
        </div>
    </div>

    {{-- Today donut --}}
    <div class="col-lg-4">
        <div class="hr-chart-card text-center">
            <div class="hr-section-title">Today's Status</div>
            <div id="{{ $widgetId }}_donut" style="height:220px;"></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">

    {{-- Department bar chart --}}
    <div class="col-lg-5">
        <div class="hr-chart-card">
            <div class="hr-section-title">Employees by Department</div>
            <div id="{{ $widgetId }}_dept" style="height:230px;"></div>
        </div>
    </div>

    {{-- Monthly join trend --}}
    <div class="col-lg-4">
        <div class="hr-chart-card">
            <div class="hr-section-title">Monthly Joining Trend</div>
            <div id="{{ $widgetId }}_join" style="height:230px;"></div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="col-lg-3">
        <div class="hr-chart-card">
            <div class="hr-section-title">Quick Links</div>
            <div class="d-flex flex-column gap-2">
                @if(Route::has('hr-center.employees.index'))
                <a href="{{ route('hr-center.employees.index') }}" class="hr-quick-btn">
                    <i class="fa fa-users"></i> Employees
                </a>
                @endif
                @if(Route::has('hr-center.attendances.index'))
                <a href="{{ route('hr-center.attendances.index') }}" class="hr-quick-btn">
                    <i class="fa fa-clock"></i> Attendance
                </a>
                @endif
                @if(Route::has('hr-center.masters.index'))
                <a href="{{ route('hr-center.masters.index', 'requisitions') }}" class="hr-quick-btn">
                    <i class="fa fa-file-alt"></i> Requisitions
                </a>
                <a href="{{ route('hr-center.masters.index', 'shifts') }}" class="hr-quick-btn">
                    <i class="fa fa-calendar-alt"></i> Shifts
                </a>
                @endif
                @if(Route::has('hr-center.reports.show'))
                <a href="{{ route('hr-center.reports.show', 'employee') }}" class="hr-quick-btn">
                    <i class="fa fa-chart-bar"></i> Reports
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── HR Insights Row ── --}}
<div class="row g-3 mb-4">

    {{-- Employees on Leave Today --}}
    <div class="col-lg-4">
        <div class="hr-chart-card h-100">
            <div class="hr-section-title">On Leave Today ({{ $s['leaveSummary']['onLeaveToday'] }})</div>
            @if($s['onLeaveToday']->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($s['onLeaveToday'] as $leave)
                    <div class="d-flex align-items-center justify-content-between" style="font-size:13px;">
                        <span>{{ $leave->employee->name ?? ('ID: ' . $leave->employee_id) }}</span>
                        <span class="hr-badge" style="background:#eef2ff;color:#6366f1;">{{ $leave->leaveType->name ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted text-center py-4" style="font-size:13px;">No one on leave today</div>
            @endif
        </div>
    </div>

    {{-- Upcoming Birthdays --}}
    <div class="col-lg-4">
        <div class="hr-chart-card h-100">
            <div class="hr-section-title">Upcoming Birthdays (30 Days)</div>
            @if($s['upcomingBirthdays']->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($s['upcomingBirthdays'] as $emp)
                    <div class="d-flex align-items-center justify-content-between" style="font-size:13px;">
                        <span>{{ $emp->name }} <span class="text-muted">({{ $emp->employee_id }})</span></span>
                        <span class="hr-badge" style="background:#fdf2f8;color:#db2777;">{{ $emp->next_birthday->format('d M') }}</span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted text-center py-4" style="font-size:13px;">No upcoming birthdays</div>
            @endif
        </div>
    </div>

    {{-- Upcoming Holidays --}}
    <div class="col-lg-4">
        <div class="hr-chart-card h-100">
            <div class="hr-section-title">Upcoming Holidays</div>
            @if($s['upcomingHolidays']->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($s['upcomingHolidays'] as $holiday)
                    <div class="d-flex align-items-center justify-content-between" style="font-size:13px;">
                        <span>{{ $holiday->purpose }}</span>
                        <span class="hr-badge" style="background:#eef2ff;color:#6366f1;">
                            {{ \Carbon\Carbon::parse($holiday->from_date)->format('d M') }}
                            @if($holiday->from_date != $holiday->to_date)
                                – {{ \Carbon\Carbon::parse($holiday->to_date)->format('d M') }}
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted text-center py-4" style="font-size:13px;">No upcoming holidays</div>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-4">

    {{-- Payroll Summary --}}
    <div class="col-lg-6">
        <div class="hr-chart-card h-100">
            <div class="hr-section-title">Payroll Summary</div>
            <div class="row g-2 text-center">
                <div class="col-6">
                    <div class="hr-stat-val" style="color:#10b981;font-size:20px;">{{ number_format($s['payrollTotal']) }}</div>
                    <div class="hr-stat-lbl">Total Gross Salary</div>
                </div>
                <div class="col-6">
                    <div class="hr-stat-val" style="color:#6366f1;font-size:20px;">{{ number_format($s['payrollAvg']) }}</div>
                    <div class="hr-stat-lbl">Avg. Salary / Employee</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Leave Summary --}}
    <div class="col-lg-6">
        <div class="hr-chart-card h-100">
            <div class="hr-section-title">Leave Summary</div>
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="hr-stat-val" style="color:#f59e0b;font-size:20px;">{{ $s['leaveSummary']['pending'] }}</div>
                    <div class="hr-stat-lbl">Pending</div>
                </div>
                <div class="col-4">
                    <div class="hr-stat-val" style="color:#10b981;font-size:20px;">{{ $s['leaveSummary']['approved'] }}</div>
                    <div class="hr-stat-lbl">Approved (Month)</div>
                </div>
                <div class="col-4">
                    <div class="hr-stat-val" style="color:#f43f5e;font-size:20px;">{{ $s['leaveSummary']['onLeaveToday'] }}</div>
                    <div class="hr-stat-lbl">On Leave Today</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Joiners ── --}}
@if($s['recentJoiners']->isNotEmpty())
<div class="hr-chart-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="hr-section-title mb-0">Recent Joiners</div>
        @if(Route::has('hr-center.employees.index'))
        <a href="{{ route('hr-center.employees.index') }}" style="font-size:12px;color:#6366f1;text-decoration:none;">View All →</a>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#f8f9ff;">
                <tr>
                    <th style="font-size:12px;color:#888;font-weight:600;border:none;padding:8px 10px;">Emp ID</th>
                    <th style="font-size:12px;color:#888;font-weight:600;border:none;padding:8px 10px;">Name</th>
                    <th style="font-size:12px;color:#888;font-weight:600;border:none;padding:8px 10px;">Join Date</th>
                    <th style="font-size:12px;color:#888;font-weight:600;border:none;padding:8px 10px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($s['recentJoiners'] as $emp)
                <tr>
                    <td class="hr-recent-table" style="font-weight:600;color:#6366f1;">{{ $emp->employee_id }}</td>
                    <td class="hr-recent-table">{{ $emp->name }}</td>
                    <td class="hr-recent-table">
                        {{ $emp->join_date ? \Carbon\Carbon::parse($emp->join_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="hr-recent-table">
                        <span class="hr-badge" style="background:#ecfdf5;color:#10b981;">Active</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── ApexCharts ── --}}
<script>
(function() {
    var attLabels  = {!! $attLabels !!};
    var attData    = {!! $attData !!};
    var deptNames  = {!! $deptNames !!};
    var deptCounts = {!! $deptCounts !!};
    var joinLabels = {!! $joinLabels !!};
    var joinData   = {!! $joinData !!};
    var late       = {{ $s['lateToday'] }};
    var present    = {{ $s['presentToday'] }} - late;
    var absent     = {{ $s['absentToday'] }};

    function initCharts() {
        // ── 30-day Attendance Bar ──
        new ApexCharts(document.getElementById('{{ $widgetId }}_att'), {
            series: [{ name: 'Present', data: attData }],
            chart: { type: 'bar', height: 220, toolbar: { show: false }, sparkline: { enabled: false } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
            colors: ['#6366f1'],
            xaxis: { categories: attLabels, labels: { rotate: -45, style: { fontSize: '10px' } }, tickAmount: 10 },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
            tooltip: { y: { formatter: v => v + ' employees' } },
        }).render();

        // ── Today Donut ──
        new ApexCharts(document.getElementById('{{ $widgetId }}_donut'), {
            series: [present, late, absent],
            chart: { type: 'donut', height: 220 },
            labels: ['Present', 'Late', 'Absent'],
            colors: ['#10b981', '#a855f7', '#f43f5e'],
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: true, formatter: (val) => Math.round(val) + '%' },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: () => present + late + absent } } } } },
            stroke: { width: 0 },
        }).render();

        // ── Department Horizontal Bar ──
        new ApexCharts(document.getElementById('{{ $widgetId }}_dept'), {
            series: [{ name: 'Employees', data: deptCounts }],
            chart: { type: 'bar', height: 230, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
            colors: ['#6366f1'],
            xaxis: { categories: deptNames, labels: { style: { fontSize: '11px' } } },
            dataLabels: { enabled: true, style: { fontSize: '11px' } },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        }).render();

        // ── Monthly Join Trend Line ──
        new ApexCharts(document.getElementById('{{ $widgetId }}_join'), {
            series: [{ name: 'New Joiners', data: joinData }],
            chart: { type: 'area', height: 230, toolbar: { show: false } },
            colors: ['#f59e0b'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .4, opacityTo: .05 } },
            xaxis: { categories: joinLabels, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } }, min: 0, forceNiceScale: true },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#f59e0b'] } },
            markers: { size: 4, colors: ['#f59e0b'], strokeWidth: 0 },
            grid: { borderColor: '#f0f0f0', strokeDashArray: 4 },
        }).render();
    }

    if (typeof ApexCharts !== 'undefined') {
        initCharts();
    } else {
        var s = document.createElement('script');
        s.src = '{{ asset("admin/assets/js/apexcharts/apexcharts.min.js") }}';
        s.onload = initCharts;
        document.head.appendChild(s);
    }
})();
</script>
@endif
