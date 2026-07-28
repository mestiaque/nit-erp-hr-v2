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
        background: #f5f8ff;
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
        gap: 22px;
    }

    /* ── Document-stack animation: pages fall one by one into an HR folder ── */
    .hr-loader-stack {
        position: relative;
        width: 130px;
        height: 130px;
    }

    .hr-loader-page {
        position: absolute;
        left: 50%;
        top: 6px;
        width: 34px;
        height: 44px;
        margin-left: -17px;
        background: #fff;
        border: 2px solid #2c5cc5;
        border-radius: 4px;
        opacity: 0;
        animation: hr-loader-page-fall 1.8s ease-in infinite;
    }
    .hr-loader-page::before {
        content: '';
        position: absolute;
        left: 6px;
        right: 6px;
        top: 10px;
        height: 2px;
        background: #a2c0f5;
        box-shadow: 0 7px 0 #a2c0f5, 0 14px 0 #a2c0f5;
    }
    .hr-loader-page-1 { animation-delay: 0s; }
    .hr-loader-page-2 { animation-delay: .6s; }
    .hr-loader-page-3 { animation-delay: 1.2s; }

    @keyframes hr-loader-page-fall {
        0%   { opacity: 0; transform: translateY(-6px) scale(1); }
        12%  { opacity: 1; }
        62%  { opacity: 1; transform: translateY(58px) scale(1); }
        78%  { opacity: 0; transform: translateY(68px) scale(.4); }
        100% { opacity: 0; transform: translateY(-6px) scale(1); }
    }

    .hr-loader-folder {
        position: absolute;
        bottom: 4px;
        left: 50%;
        width: 96px;
        height: 60px;
        margin-left: -48px;
    }
    .hr-loader-folder-tab {
        position: absolute;
        top: -10px;
        left: 10px;
        width: 38px;
        height: 12px;
        background: #cddcf7;
        border-radius: 6px 6px 0 0;
    }
    .hr-loader-folder-back {
        position: absolute;
        inset: 0;
        background: #cddcf7;
        border-radius: 4px 10px 6px 6px;
    }
    .hr-loader-folder-front {
        position: absolute;
        left: -2px;
        right: -2px;
        bottom: -2px;
        height: 42px;
        background: linear-gradient(180deg, #5b8def, #2c5cc5);
        border-radius: 4px 4px 8px 8px;
        transform-origin: bottom center;
        animation: hr-loader-folder-bounce 1.8s ease-in-out infinite;
    }
    @keyframes hr-loader-folder-bounce {
        0%, 74%, 100% { transform: scaleY(1) translateY(0); }
        80%           { transform: scaleY(.92) translateY(2px); }
        88%           { transform: scaleY(1.03) translateY(-1px); }
    }

    .hr-loader-text {
        font-size: 14px;
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
        <div class="hr-loader-stack">
            <div class="hr-loader-page hr-loader-page-1"></div>
            <div class="hr-loader-page hr-loader-page-2"></div>
            <div class="hr-loader-page hr-loader-page-3"></div>
            <div class="hr-loader-folder">
                <div class="hr-loader-folder-tab"></div>
                <div class="hr-loader-folder-back"></div>
                <div class="hr-loader-folder-front"></div>
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
