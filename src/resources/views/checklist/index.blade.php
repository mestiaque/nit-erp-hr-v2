<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HRM Checklist</title>
<style>
/* ─── Tokens ─── */
:root {
    --bg:       #f1f4f8;
    --surface:  #ffffff;
    --border:   #dde3ec;
    --text:     #1a2332;
    --muted:    #6b7a99;
    --shadow:   0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);

    --c-ready:  #16a34a;
    --c-ready-bg: #dcfce7;
    --c-dev:    #b45309;
    --c-dev-bg: #fef3c7;
    --c-none:   #64748b;
    --c-none-bg:#f1f5f9;

    --accent:   #2563eb;
    --radius:   10px;
    --font:     system-ui, -apple-system, 'Segoe UI', sans-serif;
}
@media (prefers-color-scheme: dark) {
    :root {
        --bg:       #0d1117;
        --surface:  #161b22;
        --border:   #21262d;
        --text:     #e6edf3;
        --muted:    #7d8590;
        --shadow:   0 1px 3px rgba(0,0,0,.3), 0 4px 12px rgba(0,0,0,.2);

        --c-ready:  #4ade80;
        --c-ready-bg: #052e16;
        --c-dev:    #fbbf24;
        --c-dev-bg: #1c1000;
        --c-none:   #94a3b8;
        --c-none-bg:#1e2330;
    }
}
:root[data-theme="dark"] {
    --bg:       #0d1117;
    --surface:  #161b22;
    --border:   #21262d;
    --text:     #e6edf3;
    --muted:    #7d8590;
    --shadow:   0 1px 3px rgba(0,0,0,.3), 0 4px 12px rgba(0,0,0,.2);

    --c-ready:  #4ade80;
    --c-ready-bg: #052e16;
    --c-dev:    #fbbf24;
    --c-dev-bg: #1c1000;
    --c-none:   #94a3b8;
    --c-none-bg:#1e2330;
}
:root[data-theme="light"] {
    --bg:       #f1f4f8;
    --surface:  #ffffff;
    --border:   #dde3ec;
    --text:     #1a2332;
    --muted:    #6b7a99;
    --shadow:   0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.04);

    --c-ready:  #16a34a;
    --c-ready-bg: #dcfce7;
    --c-dev:    #b45309;
    --c-dev-bg: #fef3c7;
    --c-none:   #64748b;
    --c-none-bg:#f1f5f9;
}

/* ─── Reset ─── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: var(--font);
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    font-size: 14px;
    line-height: 1.5;
    transition: background .2s, color .2s;
}

/* ─── Header ─── */
.header {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 0 var(--border);
}
.header-left h1 {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -.3px;
}
.header-left span {
    font-size: 12px;
    color: var(--muted);
    display: block;
    margin-top: 1px;
}
.theme-btn {
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12px;
    cursor: pointer;
    font-family: var(--font);
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background .15s;
}
.theme-btn:hover { background: var(--border); }

/* ─── Main layout ─── */
.main {
    max-width: 1400px;
    margin: 0 auto;
    padding: 28px 32px 60px;
}

/* ─── Summary strip ─── */
.summary {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 24px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px 28px;
    margin-bottom: 28px;
    box-shadow: var(--shadow);
    align-items: center;
}
@media (max-width: 700px) {
    .summary { grid-template-columns: 1fr; }
}

/* ─── Donut chart ─── */
.donut-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.donut-svg { overflow: visible; }
.donut-center-text {
    font-size: 22px;
    font-weight: 800;
    fill: var(--text);
    dominant-baseline: middle;
    text-anchor: middle;
    font-family: var(--font);
}
.donut-center-sub {
    font-size: 9px;
    fill: var(--muted);
    dominant-baseline: middle;
    text-anchor: middle;
    font-family: var(--font);
}

/* ─── Stat cards ─── */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 14px;
}
.stat-card {
    border-radius: 8px;
    padding: 14px 16px;
    border: 1px solid transparent;
}
.stat-card.ready  { background: var(--c-ready-bg); border-color: var(--c-ready); }
.stat-card.dev    { background: var(--c-dev-bg);   border-color: var(--c-dev);   }
.stat-card.none   { background: var(--c-none-bg);  border-color: var(--c-none);  }
.stat-card.total  { background: var(--bg);          border-color: var(--border);  }
.stat-num {
    font-size: 28px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    margin-bottom: 4px;
}
.stat-card.ready .stat-num  { color: var(--c-ready); }
.stat-card.dev .stat-num    { color: var(--c-dev);   }
.stat-card.none .stat-num   { color: var(--c-none);  }
.stat-card.total .stat-num  { color: var(--accent);  }
.stat-label { font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; }
.stat-pct { font-size: 12px; font-weight: 700; margin-top: 4px; }
.stat-card.ready .stat-pct  { color: var(--c-ready); }
.stat-card.dev .stat-pct    { color: var(--c-dev);   }
.stat-card.none .stat-pct   { color: var(--c-none);  }

/* ─── Legend ─── */
.legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
}
.legend-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}
.legend-dot.ready { background: var(--c-ready); }
.legend-dot.dev   { background: var(--c-dev);   }
.legend-dot.none  { background: var(--c-none);  }

/* ─── Category grid ─── */
.cat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}
.cat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px 20px;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.cat-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.cat-title {
    display: flex;
    align-items: center;
    gap: 8px;
}
.cat-icon { font-size: 16px; line-height: 1; flex-shrink: 0; }
.cat-name {
    font-size: 13px;
    font-weight: 700;
    line-height: 1.3;
}
.cat-bn {
    font-size: 10.5px;
    color: var(--muted);
    display: block;
    font-weight: 500;
}
.cat-count {
    font-size: 11px;
    font-weight: 700;
    color: var(--muted);
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 2px;
}

/* ─── Category progress bar ─── */
.cat-bar-wrap {
    height: 5px;
    border-radius: 99px;
    background: var(--c-none-bg);
    overflow: hidden;
    display: flex;
    gap: 1px;
}
.cat-bar-seg {
    height: 100%;
    border-radius: 99px;
    transition: width .4s;
}
.cat-bar-seg.ready { background: var(--c-ready); }
.cat-bar-seg.dev   { background: var(--c-dev);   }

/* ─── Item list ─── */
.item-list {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 5px 8px;
    border-radius: 6px;
    background: var(--bg);
    border: 1px solid transparent;
    transition: border-color .15s;
}
.item-row:hover { border-color: var(--border); }
.item-name {
    font-size: 12px;
    font-weight: 500;
    color: var(--text);
    flex: 1;
    min-width: 0;
}
.badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 99px;
    white-space: nowrap;
    flex-shrink: 0;
    letter-spacing: .2px;
}
.badge.ready { background: var(--c-ready-bg); color: var(--c-ready); }
.badge.dev   { background: var(--c-dev-bg);   color: var(--c-dev);   }
.badge.none  { background: var(--c-none-bg);  color: var(--c-none);  }

@media (max-width: 600px) {
    .main { padding: 16px; }
    .header { padding: 12px 16px; }
    .summary { padding: 16px; }
}
@media (prefers-reduced-motion: reduce) {
    .cat-bar-seg { transition: none; }
}
</style>
</head>
<body>

@php
$statusLabel = fn(int $s) => match($s) { 2 => 'Ready', 1 => 'In Dev', default => 'Not Started' };
$statusClass = fn(int $s) => match($s) { 2 => 'ready', 1 => 'dev', default => 'none' };
@endphp

<header class="header">
    <div class="header-left">
        <h1>HRM Module Checklist</h1>
        <span>Development progress tracker · {{ count($categories) }} modules · {{ $stats['total'] }} features</span>
    </div>
    <button class="theme-btn" onclick="toggleTheme()" id="themeBtn">🌙 Dark</button>
</header>

<main class="main">

    {{-- ─── Summary ─── --}}
    <section class="summary">
        {{-- Donut chart --}}
        <div class="donut-wrap">
            <svg class="donut-svg" width="140" height="140" viewBox="0 0 140 140">
                <g id="donut-segments"></g>
                <text class="donut-center-text" x="70" y="65">{{ $stats['pctReady'] }}%</text>
                <text class="donut-center-sub" x="70" y="80">Complete</text>
            </svg>
        </div>

        {{-- Stat cards --}}
        <div class="stat-grid">
            <div class="stat-card total">
                <div class="stat-num">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Features</div>
            </div>
            <div class="stat-card ready">
                <div class="stat-num">{{ $stats['ready'] }}</div>
                <div class="stat-label">Ready</div>
                <div class="stat-pct">{{ $stats['pctReady'] }}%</div>
            </div>
            <div class="stat-card dev">
                <div class="stat-num">{{ $stats['dev'] }}</div>
                <div class="stat-label">In Development</div>
                <div class="stat-pct">{{ $stats['pctDev'] }}%</div>
            </div>
            <div class="stat-card none">
                <div class="stat-num">{{ $stats['none'] }}</div>
                <div class="stat-label">Not Started</div>
                <div class="stat-pct">{{ $stats['pctNone'] }}%</div>
            </div>
        </div>
    </section>

    {{-- ─── Legend ─── --}}
    <div class="legend">
        <div class="legend-item"><span class="legend-dot ready"></span> Ready (2)</div>
        <div class="legend-item"><span class="legend-dot dev"></span> Under Development (1)</div>
        <div class="legend-item"><span class="legend-dot none"></span> Not Started (0)</div>
    </div>

    {{-- ─── Category cards ─── --}}
    <div class="cat-grid">
        @foreach($categories as $cat)
        @php
            $items  = $cat['items'];
            $total  = count($items);
            $ready  = count(array_filter($items, fn($i) => $i['status'] === 2));
            $dev    = count(array_filter($items, fn($i) => $i['status'] === 1));
            $pReady = $total > 0 ? round($ready / $total * 100) : 0;
            $pDev   = $total > 0 ? round($dev   / $total * 100) : 0;
        @endphp
        <div class="cat-card">
            <div class="cat-head">
                <div class="cat-title">
                    <span class="cat-icon">{{ $cat['icon'] }}</span>
                    <div>
                        <span class="cat-name">{{ $cat['category'] }}</span>
                        <span class="cat-bn">{{ $cat['bn'] }}</span>
                    </div>
                </div>
                <span class="cat-count">{{ $ready }}/{{ $total }}</span>
            </div>

            <div class="cat-bar-wrap">
                @if($pReady > 0)<div class="cat-bar-seg ready" style="width:{{ $pReady }}%"></div>@endif
                @if($pDev > 0)<div class="cat-bar-seg dev" style="width:{{ $pDev }}%"></div>@endif
            </div>

            <div class="item-list">
                @foreach($items as $item)
                <div class="item-row">
                    <span class="item-name">{{ $item['name'] }}</span>
                    <span class="badge {{ $statusClass($item['status']) }}">{{ $statusLabel($item['status']) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

</main>

<script>
// ─── Donut chart ───
(function () {
    const cx = 70, cy = 70, r = 52, stroke = 18;
    const data = [
        { pct: {{ $stats['pctReady'] }}, color: getComputedStyle(document.documentElement).getPropertyValue('--c-ready').trim() || '#16a34a' },
        { pct: {{ $stats['pctDev'] }},   color: getComputedStyle(document.documentElement).getPropertyValue('--c-dev').trim()   || '#b45309' },
        { pct: {{ $stats['pctNone'] }},  color: getComputedStyle(document.documentElement).getPropertyValue('--c-none').trim()  || '#64748b' },
    ];

    function polarToXY(deg, radius) {
        const rad = (deg - 90) * Math.PI / 180;
        return { x: cx + radius * Math.cos(rad), y: cy + radius * Math.sin(rad) };
    }

    function arcPath(startDeg, endDeg, r) {
        const large = (endDeg - startDeg) > 180 ? 1 : 0;
        const s = polarToXY(startDeg, r);
        const e = polarToXY(endDeg, r);
        return `M ${s.x} ${s.y} A ${r} ${r} 0 ${large} 1 ${e.x} ${e.y}`;
    }

    const g = document.getElementById('donut-segments');
    let start = 0;
    const gap = 2;

    data.forEach(seg => {
        if (seg.pct <= 0) return;
        const sweep = seg.pct / 100 * 360;
        const end = start + sweep - gap;
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', arcPath(start, end, r));
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', seg.color);
        path.setAttribute('stroke-width', stroke);
        path.setAttribute('stroke-linecap', 'round');
        g.appendChild(path);
        start += sweep;
    });

    // background track
    const track = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    track.setAttribute('cx', cx); track.setAttribute('cy', cy); track.setAttribute('r', r);
    track.setAttribute('fill', 'none');
    track.setAttribute('stroke', getComputedStyle(document.documentElement).getPropertyValue('--c-none-bg').trim() || '#f1f5f9');
    track.setAttribute('stroke-width', stroke);
    g.insertBefore(track, g.firstChild);
})();

// ─── Theme toggle ───
const btn = document.getElementById('themeBtn');
let darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
function applyTheme() {
    document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
    btn.textContent = darkMode ? '☀️ Light' : '🌙 Dark';
}
applyTheme();
function toggleTheme() { darkMode = !darkMode; applyTheme(); }
</script>

</body>
</html>
