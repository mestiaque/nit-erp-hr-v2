{{-- Employee dashboard: rating, KPIs and trend charts (ME\Hr\Services\EmployeePerformance). --}}
@php
    [$labelText, $labelColor] = $perf['label'];
    $rating = $perf['rating'];
    $status = $employee->probationStatus();
    $monthly = collect($perf['monthly']);
    $onTime = max(0, $perf['present'] - $perf['late_or_early']);
    $chartData = [
        'rating' => $rating,
        'months' => $monthly->pluck('month'),
        'present' => $monthly->pluck('present'),
        'absent' => $monthly->pluck('absent'),
        'leave' => $monthly->pluck('leave'),
        'attPct' => $monthly->map(fn ($m) => ($m['present'] + $m['absent']) > 0 ? round($m['present'] / ($m['present'] + $m['absent']) * 100) : null),
        'ot' => $monthly->pluck('ot_hours'),
        'mix' => [
            'labels' => ['On time', 'Late / Early exit', 'Absent', 'Leave'],
            'values' => [$onTime, $perf['late_or_early'], $perf['absent'], (float) $perf['leave_days']],
        ],
    ];
    $tiles = [
        ['Service Time', $employee->serviceTimeText() ?? '—', 'fa-hourglass-half'],
        ['Job Status', $status ?? '—', 'fa-user-check', $status === 'Confirmed' ? 'text-success' : ($status ? 'text-warning' : '')],
        ['Attendance', $perf['attendance_pct'] !== null ? $perf['attendance_pct'].'%' : '—', 'fa-calendar-check'],
        ['Present', $perf['present'], 'fa-circle-check'],
        ['Absent', $perf['absent'], 'fa-circle-xmark', $perf['absent'] ? 'text-danger' : ''],
        ['Late / Early', $perf['late_or_early'], 'fa-clock', $perf['late_or_early'] ? 'text-warning' : ''],
        ['Punch Missing', $perf['punch_missing'], 'fa-fingerprint'],
        ['Leave', rtrim(rtrim(number_format($perf['leave_days'], 1), '0'), '.').' d', 'fa-umbrella-beach'],
        ['OT Hours', $perf['ot_hours'], 'fa-business-time'],
        ['Notices', $perf['notices'], 'fa-triangle-exclamation', $perf['notices'] ? 'text-danger' : 'text-success'],
    ];
@endphp
<style>
    .emp-dash { --ed-border: #eef0f4; --ed-muted: #6b7280; --ed-ink: #1f2937; margin-bottom: 22px; }
    .emp-dash .ed-card { background: #fff; border: 1px solid var(--ed-border); border-radius: 12px; padding: 12px 14px; height: 100%; }
    .emp-dash .ed-title { font-size: 12px; font-weight: 700; color: var(--ed-ink); text-transform: uppercase; letter-spacing: .3px; margin-bottom: 4px; }
    .emp-dash .ed-sub { font-size: 11px; color: var(--ed-muted); }
    .emp-dash .ed-tile { display: flex; align-items: center; gap: 8px; background: #fafbfd; border: 1px solid var(--ed-border); border-radius: 8px; padding: 7px 9px; height: 100%; }
    .emp-dash .ed-tile i { color: #94a3b8; width: 16px; text-align: center; }
    .emp-dash .ed-tile .k { font-size: 10px; color: var(--ed-muted); text-transform: uppercase; letter-spacing: .3px; line-height: 1.2; }
    .emp-dash .ed-tile .v { font-size: 14px; font-weight: 700; color: var(--ed-ink); line-height: 1.3; }
    .emp-dash .ed-bar { height: 6px; background: #eceff4; border-radius: 3px; overflow: hidden; }
    .emp-dash .ed-bar > span { display: block; height: 100%; border-radius: 3px; }
    .emp-dash .ed-stars { color: #f5a623; letter-spacing: 1px; }
    .emp-dash .ed-stars .off { color: #d7dbe2; }
    .emp-dash .ed-row { margin-left: -6px; margin-right: -6px; }
    .emp-dash .ed-row > [class*="col"] { padding-left: 6px; padding-right: 6px; margin-bottom: 12px; }
</style>

<div class="emp-dash">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
        <div class="section-title mb-0">Employee Dashboard</div>
        <small class="text-muted">{{ $perf['from']->format('d M Y') }} – {{ $perf['to']->format('d M Y') }} · last {{ \ME\Hr\Services\EmployeePerformance::MONTHS }} months (from joining / first punch if later)</small>
    </div>

    <div class="row ed-row">
        {{-- Rating --}}
        <div class="col-lg-3 col-md-5">
            <div class="ed-card text-center">
                <div class="ed-title text-left">Performance Rating</div>
                <div id="ed-gauge" style="min-height:150px;"></div>
                <div class="ed-stars mb-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid {{ $rating !== null && $rating >= $i - 0.25 ? 'fa-star' : ($rating !== null && $rating >= $i - 0.75 ? 'fa-star-half-stroke' : 'fa-star off') }}"></i>
                    @endfor
                </div>
                <span class="badge badge-{{ $labelColor }}">{{ $labelText }}</span>
                @if($perf['has_data'])
                <div class="text-left mt-3">
                    @foreach($perf['parts'] as $name => $part)
                        @php
                            $pct = $part['score'] !== null ? (int) round($part['score'] * 100) : 0;
                            $barColor = $pct >= 90 ? '#16a34a' : ($pct >= 70 ? '#2a78d6' : ($pct >= 50 ? '#eda100' : '#e34948'));
                        @endphp
                        <div class="mb-2" title="{{ $part['detail'] }}">
                            <div class="d-flex justify-content-between" style="font-size:11px;"><span>{{ $name }} <span class="text-muted">({{ $part['weight'] }}%)</span></span><span class="font-weight-bold">{{ $pct }}%</span></div>
                            <div class="ed-bar"><span style="width: {{ $pct }}%; background: {{ $barColor }};"></span></div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- KPIs + monthly attendance --}}
        <div class="col-lg-9 col-md-7">
            <div class="row ed-row">
                @foreach($tiles as $tile)
                    <div class="col-6 col-sm-4 col-xl" style="margin-bottom:8px;">
                        <div class="ed-tile">
                            <i class="fa-solid {{ $tile[2] }}"></i>
                            <div><div class="k">{{ $tile[0] }}</div><div class="v {{ $tile[3] ?? '' }}">{{ $tile[1] }}</div></div>
                        </div>
                    </div>
                    @if($loop->iteration === 5)<div class="w-100 d-none d-xl-block"></div>@endif
                @endforeach
            </div>
            <div class="ed-card" style="height:auto;">
                <div class="d-flex justify-content-between">
                    <div class="ed-title">Monthly Attendance</div>
                    <div class="ed-sub">days per month</div>
                </div>
                <div id="ed-monthly" style="min-height:220px;"></div>
            </div>
        </div>
    </div>

    <div class="row ed-row">
        <div class="col-lg-4 col-md-6">
            <div class="ed-card">
                <div class="ed-title">Day Breakdown</div>
                <div class="ed-sub">all days in the period</div>
                <div id="ed-mix" style="min-height:220px;"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="ed-card">
                <div class="ed-title">Attendance Rate</div>
                <div class="ed-sub">present ÷ (present + absent), %</div>
                <div id="ed-rate" style="min-height:220px;"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="ed-card">
                <div class="ed-title">Overtime</div>
                <div class="ed-sub">OT hours per month</div>
                <div id="ed-ot" style="min-height:220px;"></div>
            </div>
        </div>
    </div>

    <small class="text-muted d-block">
        Rating = Attendance 60% + Punctuality 15% + Discipline 15% + Punching 10%, from recorded attendance and disciplinary notices. Approved leave, weekends and holidays are not counted against the employee.
    </small>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>
<script>
(function () {
    if (typeof ApexCharts === 'undefined') return;
    var d = @json($chartData);
    // Reference categorical palette, fixed order: blue, orange, aqua, yellow.
    var C = { blue: '#2a78d6', orange: '#eb6834', aqua: '#1baf7a', yellow: '#eda100' };
    var ink = '#52514e', grid = '#eef0f4';
    var base = {
        chart: { fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: false } },
        grid: { borderColor: grid, strokeDashArray: 3, padding: { left: 4, right: 8 } },
        dataLabels: { enabled: false },
        legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px', labels: { colors: ink }, markers: { width: 8, height: 8, radius: 2 } },
        xaxis: { categories: d.months, labels: { style: { colors: ink, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: ink, fontSize: '11px' } } },
        tooltip: { theme: 'light' }
    };
    var merge = function (o) { return Object.assign({}, base, o); };
    var draw = function (id, opts) { var el = document.getElementById(id); if (el) new ApexCharts(el, opts).render(); };

    var r = d.rating === null ? 0 : d.rating;
    var rColor = r >= 4.5 ? '#16a34a' : (r >= 3.5 ? C.blue : (r >= 2.5 ? C.yellow : '#e34948'));
    draw('ed-gauge', {
        chart: { type: 'radialBar', height: 170, fontFamily: 'inherit', sparkline: { enabled: true }, animations: { enabled: false } },
        series: [Math.round(r / 5 * 100)],
        colors: [rColor],
        plotOptions: { radialBar: {
            startAngle: -110, endAngle: 110, hollow: { size: '62%' },
            track: { background: '#eceff4', strokeWidth: '100%' },
            dataLabels: { name: { show: true, offsetY: 22, fontSize: '11px', color: '#6b7280' },
                value: { offsetY: -12, fontSize: '28px', fontWeight: 700, color: '#0b0b0b',
                    formatter: function () { return d.rating === null ? '—' : d.rating.toFixed(1); } } }
        } },
        stroke: { lineCap: 'round' },
        labels: ['out of 5']
    });

    draw('ed-monthly', merge({
        chart: Object.assign({}, base.chart, { type: 'bar', height: 230, stacked: true }),
        series: [{ name: 'Present', data: d.present }, { name: 'Absent', data: d.absent }, { name: 'Leave', data: d.leave }],
        colors: [C.blue, C.orange, C.aqua],
        plotOptions: { bar: { columnWidth: '42%', borderRadius: 4, borderRadiusApplication: 'end', borderRadiusWhenStacked: 'last' } },
        stroke: { show: true, width: 2, colors: ['#fff'] },
        tooltip: { theme: 'light', shared: true, intersect: false, y: { formatter: function (v) { return v + ' days'; } } }
    }));

    draw('ed-mix', {
        chart: { type: 'donut', height: 240, fontFamily: 'inherit', animations: { enabled: false } },
        series: d.mix.values, labels: d.mix.labels,
        colors: [C.blue, C.yellow, C.orange, C.aqua],
        stroke: { width: 2, colors: ['#fff'] },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '11px', labels: { colors: ink }, markers: { width: 8, height: 8, radius: 2 } },
        plotOptions: { pie: { donut: { size: '68%', labels: { show: true,
            value: { fontSize: '20px', fontWeight: 700, color: '#0b0b0b' },
            total: { show: true, label: 'Days', fontSize: '11px', color: '#6b7280' } } } } },
        tooltip: { y: { formatter: function (v) { return v + ' days'; } } }
    });

    draw('ed-rate', merge({
        chart: Object.assign({}, base.chart, { type: 'area', height: 230 }),
        series: [{ name: 'Attendance', data: d.attPct }],
        colors: [C.blue],
        stroke: { curve: 'straight', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: .25, opacityTo: .02 } },
        markers: { size: 4, strokeColors: '#fff', strokeWidth: 2 },
        legend: { show: false },
        yaxis: { min: 0, max: 100, tickAmount: 4, labels: { style: { colors: ink, fontSize: '11px' }, formatter: function (v) { return Math.round(v) + '%'; } } },
        tooltip: { theme: 'light', y: { formatter: function (v) { return v === null ? '—' : v + '%'; } } }
    }));

    draw('ed-ot', merge({
        chart: Object.assign({}, base.chart, { type: 'bar', height: 230 }),
        series: [{ name: 'OT hours', data: d.ot }],
        colors: [C.blue],
        plotOptions: { bar: { columnWidth: '42%', borderRadius: 4, borderRadiusApplication: 'end' } },
        legend: { show: false },
        tooltip: { theme: 'light', y: { formatter: function (v) { return v + ' h'; } } }
    }));
})();
</script>
@endpush
