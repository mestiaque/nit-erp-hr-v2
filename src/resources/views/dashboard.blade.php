@extends(adminTheme().'layouts.app')

@section('title')
<title>{{ websiteTitle('HR Dashboard') }}</title>
@endsection

@push('css')
<style>
/* ── Stat Cards ─────────────────────────────────────── */
.stat-card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    transition: transform .2s, box-shadow .2s;
    overflow: hidden;
    position: relative;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.12); }
.stat-card .stat-icon {
    width: 56px; height: 56px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; flex-shrink: 0;
}
.stat-card .stat-val { font-size: 1.9rem; font-weight: 700; line-height: 1; }
.stat-card .stat-label { font-size: .78rem; color: #888; margin-top: 2px; }
.stat-card .stat-sub { font-size: .75rem; margin-top: 4px; }

/* ── Chart cards ────────────────────────────────────── */
.chart-card {
    border-radius: 14px;
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
}
.chart-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    border-radius: 14px 14px 0 0 !important;
    padding: 14px 18px;
}
.chart-card .card-header h6 { font-size: .93rem; font-weight: 700; margin: 0; }

/* ── Table ──────────────────────────────────────────── */
.hr-table th { font-size: .78rem; color: #888; font-weight: 600; text-transform: uppercase; border-top: none; }
.hr-table td { font-size: .88rem; vertical-align: middle; }

/* ── Badge pills ────────────────────────────────────── */
.pill { display:inline-block; padding:2px 10px; border-radius:20px; font-size:.75rem; font-weight:600; }
.pill-green  { background:#e8f8f0; color:#27ae60; }
.pill-red    { background:#fdecea; color:#e53935; }
.pill-orange { background:#fff3e0; color:#ef6c00; }
.pill-blue   { background:#e3f2fd; color:#1976d2; }
</style>
@endpush

@section('contents')
@include(adminTheme().'alerts')

<div class="flex-grow-1" style="padding:20px 20px 30px;">

    {{-- ── Row 1: Stat Cards ─────────────────────────────── --}}
    <div class="row g-3 mb-4">

        {{-- Total Employees --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#1976d2,#42a5f5);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-users text-white"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['totalEmployees'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Total Employees</div>
                </div>
            </div>
        </div>

        {{-- Present Today --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#2e7d32,#66bb6a);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-user-check text-white" style="font-size:20px;"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['presentToday'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Present Today</div>
                </div>
            </div>
        </div>

        {{-- Late Today --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#e65100,#ffa726);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-clock-o text-white" style="font-size:20px;"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['lateToday'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Late Today</div>
                </div>
            </div>
        </div>

        {{-- Absent Today --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#b71c1c,#ef5350);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-user-times text-white" style="font-size:20px;"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['absentToday'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Absent Today</div>
                </div>
            </div>
        </div>

        {{-- Recruited This Year --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#00695c,#26a69a);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-user-plus text-white" style="font-size:20px;"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['recruitedThisYear'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Recruited {{ now()->year }}</div>
                </div>
            </div>
        </div>

        {{-- Terminated This Year --}}
        <div class="col-6 col-md-4 col-lg-2">
            <div class="stat-card card h-100" style="background:linear-gradient(135deg,#4a148c,#ab47bc);">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="stat-icon mb-2" style="background:rgba(255,255,255,.2);">
                        <i class="fa fa-user-minus text-white" style="font-size:20px;"></i>
                    </div>
                    <div class="stat-val text-white">{{ $stats['terminatedThisYear'] }}</div>
                    <div class="stat-label text-white" style="opacity:.85;">Terminated {{ now()->year }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Attendance Chart (30 days) ─────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="chart-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="fa fa-bar-chart mr-2 text-primary"></i> Last 30 Days Attendance</h6>
                    <div>
                        <span class="pill pill-green mr-1">Present</span>
                        <span class="pill pill-orange mr-1">Late</span>
                        <span class="pill pill-red">Absent</span>
                    </div>
                </div>
                <div class="card-body" style="padding:16px;">
                    <canvas id="attendanceChart" height="110"></canvas>
                </div>
            </div>
        </div>

        {{-- Today's Attendance Donut --}}
        <div class="col-lg-4">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-pie-chart mr-2 text-success"></i> Today's Snapshot</h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center" style="padding:16px;">
                    <canvas id="todayDonut" style="max-width:200px;max-height:200px;"></canvas>
                    <div class="d-flex gap-3 mt-3 flex-wrap justify-content-center">
                        <span class="pill pill-green">Present {{ $stats['presentToday'] - $stats['lateToday'] }}</span>
                        <span class="pill pill-orange">Late {{ $stats['lateToday'] }}</span>
                        <span class="pill pill-red">Absent {{ $stats['absentToday'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 3: Recruitment vs Termination + Department ── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-line-chart mr-2 text-info"></i> Recruitment vs Termination (Last 6 Months)</h6>
                </div>
                <div class="card-body" style="padding:16px;">
                    <canvas id="recruitChart" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-building mr-2 text-warning"></i> Employees by Department</h6>
                </div>
                <div class="card-body" style="padding:16px;">
                    <canvas id="deptChart" height="140"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 4: Recent Joiners + Recent Separations ──────── --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="fa fa-user-plus mr-2 text-success"></i> Recent Joiners</h6>
                    <a href="{{ route('hr-center.employees.index') }}" class="btn btn-xs btn-outline-success" style="font-size:.75rem;padding:2px 10px;">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table hr-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-3">Employee</th>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Join Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recentJoiners'] as $emp)
                            <tr>
                                <td class="pl-3">{{ $emp->name }}</td>
                                <td><code style="font-size:.8rem;">{{ $emp->employee_id }}</code></td>
                                <td>{{ $emp->department->name ?? '—' }}</td>
                                <td>
                                    @if($emp->join_date)
                                        {{ \Carbon\Carbon::parse($emp->join_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-sign-out mr-2 text-danger"></i> Recent Separations</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table hr-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-3">Employee</th>
                                <th>Status</th>
                                <th>Effective Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recentSeparations'] as $sep)
                            <tr>
                                <td class="pl-3">{{ $sep->employee->name ?? ('ID: ' . $sep->employee_id) }}</td>
                                <td>
                                    <span class="pill @if($sep->status == 'approved') pill-red @else pill-orange @endif">
                                        {{ ucfirst($sep->status ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    @if($sep->effective_date)
                                        {{ \Carbon\Carbon::parse($sep->effective_date)->format('d M Y') }}
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Row 5: Quick Actions + Setup + Reports ──────────── --}}
    <div class="row g-3">

        {{-- Quick Actions --}}
        <div class="col-lg-2">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-bolt mr-1 text-warning"></i> Quick Actions</h6>
                </div>
                <div class="card-body p-2 d-flex flex-column gap-2">
                    @php
                        $quickActions = [
                            ['route' => route('hr-center.employees.index'),              'icon' => 'fa-users',     'label' => 'Employees',    'color' => '#1976d2'],
                            ['route' => route('hr-center.attendances.index'),            'icon' => 'fa-clock-o',   'label' => 'Attendance',   'color' => '#2e7d32'],
                            ['route' => route('hr-center.machine-logs.index'),           'icon' => 'fa-list-alt',  'label' => 'Machine Log',  'color' => '#00695c'],
                            ['route' => route('hr-center.masters.index', 'requisitions'),'icon' => 'fa-file-text-o','label' => 'Requisitions','color' => '#6a1b9a'],
                        ];
                    @endphp
                    @foreach($quickActions as $qa)
                    <a href="{{ $qa['route'] }}"
                       class="d-flex align-items-center gap-2 text-decoration-none rounded-lg p-2"
                       style="background:#f8f9fa;border:1px solid #eee;transition:.2s;color:#333;"
                       onmouseover="this.style.background='{{ $qa['color'] }}';this.style.color='#fff';this.style.borderColor='{{ $qa['color'] }}';"
                       onmouseout="this.style.background='#f8f9fa';this.style.color='#333';this.style.borderColor='#eee';">
                        <span style="width:32px;height:32px;border-radius:8px;background:{{ $qa['color'] }}22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fa {{ $qa['icon'] }}" style="color:{{ $qa['color'] }};font-size:14px;"></i>
                        </span>
                        <span style="font-size:.85rem;font-weight:600;">{{ $qa['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Basic Setup --}}
        @if(!empty($entities))
        <div class="col-lg-5">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-cogs mr-1 text-secondary"></i> Basic Setup</h6>
                </div>
                <div class="card-body p-2">
                    @php
                        $setupIcons = [
                            'department'      => ['fa-building',      '#1976d2'],
                            'block_line'       => ['fa-th-large',     '#e65100'],
                            'classification'  => ['fa-tags',          '#7b1fa2'],
                            'country'         => ['fa-globe',         '#00796b'],
                            'division'        => ['fa-sitemap',       '#1565c0'],
                            'district'        => ['fa-map-marker',    '#c62828'],
                            'thana'           => ['fa-map-pin',       '#ad1457'],
                            'marital_status'  => ['fa-heart',         '#e91e63'],
                            'religion'        => ['fa-star',          '#f57f17'],
                            'sex'             => ['fa-venus-mars',    '#6a1b9a'],
                            'bonus_policy'    => ['fa-money',         '#2e7d32'],
                            'bonus_title'     => ['fa-gift',          '#00695c'],
                            'designation'     => ['fa-id-badge',      '#0277bd'],
                            'factory'         => ['fa-industry',      '#4e342e'],
                            'leave_info'      => ['fa-calendar-times-o','#bf360c'],
                            'production_bonus'=> ['fa-line-chart',    '#1b5e20'],
                            'salary_key'      => ['fa-key',           '#880e4f'],
                            'payment_method'  => ['fa-credit-card',   '#006064'],
                            'shift'           => ['fa-clock-o',       '#e65100'],
                            'section'         => ['fa-th',            '#283593'],
                            'sub_section'     => ['fa-th-list',       '#4527a0'],
                            'week'            => ['fa-calendar',      '#558b2f'],
                            'working_place'   => ['fa-map',           '#4e342e'],
                            'requisitions'    => ['fa-file-text',     '#37474f'],
                        ];
                    @endphp
                    <div class="row g-1">
                        @foreach($entities as $key => $entity)
                        @php
                            $icon  = $setupIcons[$key][0] ?? 'fa-circle';
                            $color = $setupIcons[$key][1] ?? '#607d8b';
                        @endphp
                        <div class="col-4 col-md-3">
                            <a href="{{ route('hr-center.masters.index', $key) }}"
                               class="d-flex flex-column align-items-center justify-content-center text-decoration-none p-2 rounded"
                               style="background:#fafafa;border:1px solid #eee;min-height:72px;transition:.2s;text-align:center;"
                               onmouseover="this.style.background='{{ $color }}11';this.style.borderColor='{{ $color }}55';"
                               onmouseout="this.style.background='#fafafa';this.style.borderColor='#eee';">
                                <div style="width:34px;height:34px;border-radius:10px;background:{{ $color }}18;display:flex;align-items:center;justify-content:center;margin-bottom:5px;">
                                    <i class="fa {{ $icon }}" style="color:{{ $color }};font-size:15px;"></i>
                                </div>
                                <span style="font-size:.72rem;font-weight:600;color:#444;line-height:1.2;">{{ $entity['title'] }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Reports --}}
        @if(!empty($reports))
        <div class="col-lg-5">
            <div class="chart-card card h-100">
                <div class="card-header">
                    <h6><i class="fa fa-file-text-o mr-1 text-info"></i> Reports</h6>
                </div>
                <div class="card-body p-2">
                    @php
                        $reportColors = [
                            '#1976d2','#2e7d32','#e65100','#7b1fa2','#00695c',
                            '#c62828','#ad1457','#f57f17','#0277bd','#4e342e',
                            '#bf360c','#1b5e20','#880e4f','#006064','#283593',
                            '#4527a0','#558b2f','#4e342e','#37474f','#1565c0',
                        ];
                        $ri = 0;
                    @endphp
                    <div class="row g-1">
                        @foreach($reports as $key => $label)
                        @php $rc = $reportColors[$ri++ % count($reportColors)]; @endphp
                        <div class="col-6">
                            <a href="{{ route('hr-center.reports.show', $key) }}"
                               class="d-flex align-items-center gap-2 text-decoration-none rounded p-2"
                               style="background:#fafafa;border:1px solid #eee;min-height:40px;transition:.2s;"
                               onmouseover="this.style.background='{{ $rc }}11';this.style.borderColor='{{ $rc }}66';"
                               onmouseout="this.style.background='#fafafa';this.style.borderColor='#eee';">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $rc }};flex-shrink:0;display:inline-block;"></span>
                                <span style="font-size:.78rem;font-weight:600;color:#333;line-height:1.3;">{{ $label }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- Chart.js data --}}
@php
    $last30     = $stats['last30'];
    $monthTrend = $stats['monthlyTrend'];
    $depts      = $stats['departments'];
@endphp
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.font.size   = 11;

/* ── 1. 30-day stacked bar ──────────────────────────── */
new Chart(document.getElementById('attendanceChart'), {
    type: 'bar',
    data: {
        labels : {!! json_encode($last30->pluck('label')) !!},
        datasets: [
            {
                label: 'Present',
                data : {!! json_encode($last30->pluck('present')) !!},
                backgroundColor: 'rgba(46,125,50,.75)',
                borderRadius: 3,
                stack: 's',
            },
            {
                label: 'Late',
                data : {!! json_encode($last30->pluck('late')) !!},
                backgroundColor: 'rgba(230,81,0,.75)',
                borderRadius: 3,
                stack: 's',
            },
            {
                label: 'Absent',
                data : {!! json_encode($last30->pluck('absent')) !!},
                backgroundColor: 'rgba(183,28,28,.55)',
                borderRadius: 3,
                stack: 's',
            },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 45 } },
            y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
        }
    }
});

/* ── 2. Today Donut ─────────────────────────────────── */
new Chart(document.getElementById('todayDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Late', 'Absent'],
        datasets: [{
            data: [
                {{ ($stats['presentToday'] - $stats['lateToday']) }},
                {{ $stats['lateToday'] }},
                {{ $stats['absentToday'] }}
            ],
            backgroundColor: ['#2e7d32','#e65100','#b71c1c'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
        }
    }
});

/* ── 3. Recruitment vs Termination (bar + line) ─────── */
new Chart(document.getElementById('recruitChart'), {
    type: 'bar',
    data: {
        labels : {!! json_encode($monthTrend->pluck('label')) !!},
        datasets: [
            {
                label: 'Recruited',
                type: 'bar',
                data : {!! json_encode($monthTrend->pluck('recruited')) !!},
                backgroundColor: 'rgba(0,105,92,.7)',
                borderRadius: 4,
                order: 2,
            },
            {
                label: 'Terminated',
                type: 'line',
                data : {!! json_encode($monthTrend->pluck('terminated')) !!},
                borderColor: '#ab47bc',
                backgroundColor: 'rgba(171,71,188,.15)',
                borderWidth: 2,
                tension: .4,
                pointBackgroundColor: '#ab47bc',
                pointRadius: 4,
                fill: true,
                order: 1,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
        }
    }
});

/* ── 4. Department horizontal bar ───────────────────── */
new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: {
        labels : {!! json_encode($depts->pluck('name')) !!},
        datasets: [{
            label: 'Employees',
            data : {!! json_encode($depts->pluck('employees_count')) !!},
            backgroundColor: [
                '#1976d2','#2e7d32','#e65100','#b71c1c',
                '#00695c','#4a148c','#f57f17','#0277bd'
            ],
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: '#f0f0f0' } },
            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
