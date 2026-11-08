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
    // The running month is partial: flag it so a low month-to-date figure doesn't read as a drop.
    $mtd = ! $perf['to']->isSameDay($perf['to']->copy()->endOfMonth());
    $chartData['mtd'] = $mtd && $monthly->isNotEmpty();
    if ($chartData['mtd']) {
        $chartData['months'] = $chartData['months']->values()->all();
        $chartData['months'][count($chartData['months']) - 1] .= ' (MTD)';
    }
    $chartData['hasLeave'] = $monthly->sum('leave') > 0;
    $chartData['hasOt'] = $monthly->sum('ot_hours') > 0;

    // Trend: last complete month vs the one before it.
    $complete = $chartData['mtd'] ? $monthly->slice(0, -1)->values() : $monthly;
    $pctOf = fn ($m) => ($m['present'] + $m['absent']) > 0 ? round($m['present'] / ($m['present'] + $m['absent']) * 100) : null;
    $trend = function (string $key) use ($complete, $pctOf) {
        if ($complete->count() < 2) return null;
        [$a, $b] = [$complete[$complete->count() - 2], $complete->last()];
        return $key === 'pct' ? (($pctOf($a) === null || $pctOf($b) === null) ? null : $pctOf($b) - $pctOf($a)) : $b[$key] - $a[$key];
    };
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
    $tiles = [
        // [label, value, sub text, icon, icon colour, trend, trend is good when up?, unit]
        ['Attendance', $perf['attendance_pct'] !== null ? $perf['attendance_pct'].'%' : '—', 'of working days', 'fa-calendar-check', '#2a78d6', $trend('pct'), true, ' pts'],
        ['Present / Absent', $perf['present'].' / '.$perf['absent'], $fmt($perf['leave_days']).' day(s) leave', 'fa-user-clock', '#1baf7a', $trend('absent'), false, ' absent'],
        ['Late / Early', $perf['late_or_early'], $perf['punch_missing'].' punch missing', 'fa-clock', '#eda100', $trend('late_or_early'), false, ''],
        ['OT Hours', $fmt($perf['ot_hours']), 'overtime in period', 'fa-business-time', '#7c5cd6', $trend('ot_hours'), true, ' h'],
        ['Notices', $perf['notices'], $perf['notices'] ? 'disciplinary' : 'clean record', 'fa-shield-halved', $perf['notices'] ? '#e34948' : '#16a34a', null, false, ''],
    ];

    // One-line plain-language summary under the rating.
    $insight = null;
    if ($perf['has_data']) {
        $scored = collect($perf['parts'])->filter(fn ($p) => $p['score'] !== null);
        $best = $scored->sortByDesc('score')->keys()->first();
        $worst = $scored->sortBy('score')->keys()->first();
        $insight = $scored->min('score') >= 0.9
            ? 'Consistent on every measure.'
            : "Strongest in {$best}; {$worst} needs attention.";
        $absTrend = $trend('absent');
        if ($absTrend !== null && $absTrend > 0) $insight .= " Absences up by {$absTrend} last month.";
    }
@endphp
<style>
    .emp-dash { --ed-border: #e8ebf0; --ed-muted: #6b7280; --ed-ink: #111827; margin-bottom: 22px; }
    .emp-dash .ed-card { background: #fff; border: 1px solid var(--ed-border); border-radius: 12px; padding: 14px 16px; width: 100%; box-shadow: 0 1px 2px rgba(16,24,40,.04); }
    .emp-dash .ed-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 2px; }
    .emp-dash .ed-title { font-size: 12px; font-weight: 700; color: var(--ed-ink); text-transform: uppercase; letter-spacing: .4px; }
    .emp-dash .ed-sub { font-size: 11px; color: var(--ed-muted); }
    .emp-dash .ed-kpi { display: flex; gap: 12px; align-items: flex-start; }
    .emp-dash .ed-kpi .ic { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex: none; }
    .emp-dash .ed-kpi .k { font-size: 11px; color: var(--ed-muted); font-weight: 600; }
    .emp-dash .ed-kpi .v { font-size: 22px; font-weight: 700; color: var(--ed-ink); line-height: 1.15; }
    .emp-dash .ed-kpi .s { font-size: 11px; color: var(--ed-muted); }
    .emp-dash .ed-trend { font-size: 11px; font-weight: 600; margin-left: 4px; }
    .emp-dash .ed-bar { height: 6px; background: #eceff4; border-radius: 3px; overflow: hidden; }
    .emp-dash .ed-bar > span { display: block; height: 100%; border-radius: 3px; }
    .emp-dash .ed-stars { color: #f5a623; letter-spacing: 1px; }
    .emp-dash .ed-stars .off { color: #d7dbe2; }
    .emp-dash .ed-insight { font-size: 12px; color: #374151; background: #f6f8fb; border-radius: 8px; padding: 7px 10px; margin-top: 10px; text-align: left; }
    .emp-dash .ed-empty { height: 220px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; font-size: 12px; }
    .emp-dash .ed-empty i { font-size: 26px; margin-bottom: 8px; color: #d1d5db; }
    .emp-dash .ed-row { margin-left: -7px; margin-right: -7px; }
    .emp-dash .ed-row > [class*="col"] { padding-left: 7px; padding-right: 7px; margin-bottom: 14px; display: flex; }
</style>

<div class="emp-dash">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:8px;">
        <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
            <div class="section-title mb-0">Employee Dashboard</div>
        </div>
        <small class="text-muted"><i class="fa-regular fa-calendar mr-1"></i>{{ $perf['from']->format('d M Y') }} – {{ $perf['to']->format('d M Y') }}</small>
    </div>

    {{-- KPIs --}}
    <div class="row ed-row">
        @foreach($tiles as [$k, $v, $sub, $icon, $color, $tr, $upGood, $unit])
            <div class="col-6 col-md-4 col-xl">
                <div class="ed-card ed-kpi">
                    <div class="ic" style="background: {{ $color }}1a; color: {{ $color }};"><i class="fa-solid {{ $icon }}"></i></div>
                    <div>
                        <div class="k">{{ $k }}</div>
                        <div class="v">{{ $v }}</div>
                        <div class="s">{{ $sub }}
                            @if($tr !== null && $tr != 0)
                                @php $good = ($tr > 0) === $upGood; @endphp
                                <span class="ed-trend {{ $good ? 'text-success' : 'text-danger' }}" title="Last complete month vs the month before">{{ $tr > 0 ? '▲' : '▼' }} {{ $fmt(abs($tr)) }}{{ $unit }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row ed-row">
        {{-- Rating --}}
        <div class="col-lg-4 col-xl-3">
            <div class="ed-card text-center">
                <div class="ed-head">
                    <div class="ed-title">Performance Rating</div>
                    <i class="fa-solid fa-circle-info text-muted" style="cursor:help;" title="Rating = Attendance 60% + Punctuality 15% + Discipline 15% + Punching 10%, from recorded attendance and disciplinary notices over the last {{ \ME\Hr\Services\EmployeePerformance::MONTHS }} months (from joining / first punch if later). Approved leave, weekends and holidays are not counted against the employee."></i>
                </div>
                <div id="ed-gauge" style="min-height:140px;"></div>
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
                @if($insight)<div class="ed-insight"><i class="fa-solid fa-lightbulb mr-1" style="color:#eda100;"></i>{{ $insight }}</div>@endif
            </div>
        </div>

        <div class="col-lg-8 col-xl-9">
            <div class="ed-card">
                <div class="ed-head">
                    <div class="ed-title">Monthly Attendance</div>
                    <div class="ed-sub">days per month{{ $chartData['mtd'] ? ' · MTD = month to date' : '' }}</div>
                </div>
                <div id="ed-monthly" style="min-height:260px;"></div>
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
                <div class="ed-sub">present ÷ (present + absent){{ $chartData['mtd'] ? ' · dashed = month to date' : '' }}</div>
                <div id="ed-rate" style="min-height:220px;"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="ed-card">
                <div class="ed-title">Overtime</div>
                <div class="ed-sub">OT hours per month</div>
                @if($chartData['hasOt'])
                    <div id="ed-ot" style="min-height:220px;"></div>
                @else
                    <div class="ed-empty"><i class="fa-solid fa-business-time"></i>No overtime in this period</div>
                @endif
            </div>
        </div>
    </div>
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
        chart: { type: 'radialBar', height: 160, fontFamily: 'inherit', sparkline: { enabled: true }, animations: { enabled: false } },
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
        chart: Object.assign({}, base.chart, { type: 'bar', height: 340, stacked: true }),
        series: [{ name: 'Present', data: d.present }, { name: 'Absent', data: d.absent }].concat(d.hasLeave ? [{ name: 'Leave', data: d.leave }] : []),
        colors: [C.blue, C.orange, C.aqua],
        dataLabels: { enabled: true, enabledOnSeries: [0], style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] }, formatter: function (v) { return v ? v : ''; } },
        plotOptions: { bar: { columnWidth: '26%', borderRadius: 4, borderRadiusApplication: 'end', borderRadiusWhenStacked: 'last' } },
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
        forecastDataPoints: { count: d.mtd ? 1 : 0, dashArray: 4 },
        legend: { show: false },
        yaxis: { min: 0, max: 100, tickAmount: 4, labels: { style: { colors: ink, fontSize: '11px' }, formatter: function (v) { return Math.round(v) + '%'; } } },
        tooltip: { theme: 'light', y: { formatter: function (v) { return v === null ? '—' : v + '%'; } } }
    }));

    draw('ed-ot', merge({
        chart: Object.assign({}, base.chart, { type: 'bar', height: 230 }),
        series: [{ name: 'OT hours', data: d.ot }],
        colors: [C.blue],
        plotOptions: { bar: { columnWidth: '30%', borderRadius: 4, borderRadiusApplication: 'end' } },
        legend: { show: false },
        tooltip: { theme: 'light', y: { formatter: function (v) { return v + ' h'; } } }
    }));
})();
</script>
@endpush
