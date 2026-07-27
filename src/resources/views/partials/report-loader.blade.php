{{--
    HR Report Loader — a self-contained, reusable loading overlay for report screens.

    Usage:
        @include('hr::partials.report-loader')

        <script>
            HrLoader.show('Generating Report...');   // show, with optional message
            HrLoader.hide();                          // hide
        </script>

    Everything (CSS, markup, JS) lives in this one file so any report blade can pull
    in the loader with a single @include and nothing else. @once guards the whole
    block so including it more than once on the same page (e.g. from a shared
    layout AND a specific view) never produces duplicate DOM nodes or re-registers
    the animation/JS — the loader design/logic itself is defined a single time.
--}}
@once
<style>
    .hr-loader-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.94);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        opacity: 0;
        visibility: hidden;
        transition: opacity .25s ease, visibility .25s ease;
    }
    .hr-loader-overlay.is-active {
        opacity: 1;
        visibility: visible;
    }

    .hr-loader-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 18px;
    }

    /* ── Org-chart orbit animation ── */
    .hr-loader-orbit {
        position: relative;
        width: 92px;
        height: 92px;
    }
    .hr-loader-hub {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        background: #2c5cc5;
        border-radius: 50%;
        animation: hr-loader-hub-pulse 1.6s ease-in-out infinite;
        z-index: 2;
    }
    .hr-loader-ring {
        position: absolute;
        inset: 0;
        animation: hr-loader-spin 3.2s linear infinite;
    }
    .hr-loader-node-wrap {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
    }
    .hr-loader-spoke {
        position: absolute;
        top: 0;
        left: 0;
        width: 1px;
        height: 44px;
        background: linear-gradient(to bottom, rgba(44, 92, 197, 0.05), rgba(44, 92, 197, .55));
        transform-origin: top center;
    }
    .hr-loader-node {
        position: absolute;
        top: 44px;
        left: -5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #5b8def;
        animation: hr-loader-node-pulse 1.6s ease-in-out infinite;
    }
    /* 5 satellite nodes spaced 72deg apart around the hub */
    .hr-loader-node-wrap:nth-child(1) { transform: rotate(0deg); }
    .hr-loader-node-wrap:nth-child(2) { transform: rotate(72deg); }
    .hr-loader-node-wrap:nth-child(3) { transform: rotate(144deg); }
    .hr-loader-node-wrap:nth-child(4) { transform: rotate(216deg); }
    .hr-loader-node-wrap:nth-child(5) { transform: rotate(288deg); }
    .hr-loader-node-wrap:nth-child(1) .hr-loader-node { animation-delay: 0s;    background: #2c5cc5; }
    .hr-loader-node-wrap:nth-child(2) .hr-loader-node { animation-delay: .16s; background: #3f6fd4; }
    .hr-loader-node-wrap:nth-child(3) .hr-loader-node { animation-delay: .32s; background: #5b8def; }
    .hr-loader-node-wrap:nth-child(4) .hr-loader-node { animation-delay: .48s; background: #7ea4ef; }
    .hr-loader-node-wrap:nth-child(5) .hr-loader-node { animation-delay: .64s; background: #a2c0f5; }

    @keyframes hr-loader-spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    @keyframes hr-loader-hub-pulse {
        0%, 100% { transform: scale(1);    box-shadow: 0 0 0 0 rgba(44, 92, 197, .35); }
        50%      { transform: scale(1.25); box-shadow: 0 0 0 6px rgba(44, 92, 197, 0); }
    }
    @keyframes hr-loader-node-pulse {
        0%, 100% { transform: scale(.8);  opacity: .55; }
        50%      { transform: scale(1.15); opacity: 1; }
    }

    .hr-loader-text {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .3px;
        color: #2c5cc5;
    }
    .hr-loader-text::after {
        content: '';
        display: inline-block;
        width: 1.2em;
        text-align: left;
        animation: hr-loader-ellipsis 1.4s steps(4, end) infinite;
        overflow: hidden;
        vertical-align: bottom;
    }
    @keyframes hr-loader-ellipsis {
        0%   { content: ''; }
        25%  { content: '.'; }
        50%  { content: '..'; }
        75%  { content: '...'; }
        100% { content: ''; }
    }

    @media print {
        .hr-loader-overlay { display: none !important; }
    }
</style>

<div id="hr-report-loader" class="hr-loader-overlay" role="status" aria-live="polite">
    <div class="hr-loader-box">
        <div class="hr-loader-orbit">
            <div class="hr-loader-hub"></div>
            <div class="hr-loader-ring">
                @for ($i = 0; $i < 5; $i++)
                    <div class="hr-loader-node-wrap">
                        <div class="hr-loader-spoke"></div>
                        <div class="hr-loader-node"></div>
                    </div>
                @endfor
            </div>
        </div>
        <div class="hr-loader-text" id="hr-report-loader-text">Generating Report</div>
    </div>
</div>

<script>
    // Defined once, no matter how many times this partial is pulled in — later
    // definitions are skipped so a stray double-include can never clobber an
    // in-flight show/hide call or reset the (harmless anyway) internal state.
    window.HrLoader = window.HrLoader || (function () {
        var overlay = null;
        var textEl = null;
        var hideTimer = null;

        function elements() {
            if (!overlay) overlay = document.getElementById('hr-report-loader');
            if (!textEl) textEl = document.getElementById('hr-report-loader-text');
            return overlay && textEl;
        }

        function show(message) {
            if (!elements()) return;
            if (hideTimer) { clearTimeout(hideTimer); hideTimer = null; }
            textEl.textContent = message || 'Generating Report';
            overlay.classList.add('is-active');
        }

        function hide() {
            if (!elements()) return;
            overlay.classList.remove('is-active');
        }

        // Safety valve: never let the overlay get stuck forever (e.g. if a print
        // tab fails to open and the calling code's own hide() never runs).
        function showWithTimeout(message, timeoutMs) {
            show(message);
            if (hideTimer) clearTimeout(hideTimer);
            hideTimer = setTimeout(hide, timeoutMs || 15000);
        }

        // Belt-and-suspenders: clear any stuck state on back/forward navigation,
        // bfcache restores, and tab-visibility changes — same rationale as the
        // app-wide XLoader this pairs with.
        ['pageshow', 'pagehide', 'popstate'].forEach(function (evt) {
            window.addEventListener(evt, hide);
        });
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') hide();
        });

        return { show: show, hide: hide, showWithTimeout: showWithTimeout };
    })();
</script>
@endonce
