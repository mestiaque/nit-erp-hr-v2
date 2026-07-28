{{-- Full-screen loading overlay shown while a report print view renders. Self-contained
     (no jQuery/FontAwesome dependency) so it can never break if those aren't loaded on the
     print page. Hidden automatically once the page finishes loading, with a hard failsafe
     timeout so it can never get stuck covering the report, and hidden in @media print so it
     never appears in the printed/PDF output. --}}
<div id="acReportLoader" class="ac-report-loader">
    <div class="ac-report-loader-bars">
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
    </div>
    <div class="ac-report-loader-title">{{ config('acc-sfl.company.name') }}</div>
    <div class="ac-report-loader-subtitle">Loading Report<span class="ac-report-loader-dots"></span></div>
</div>

<style>
    .ac-report-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .ac-report-loader-bars {
        display: flex;
        align-items: flex-end;
        gap: 6px;
        height: 48px;
    }
    .ac-report-loader-bar {
        width: 8px;
        background: #0047ab;
        border-radius: 2px;
        animation: ac-report-loader-bar-grow 1s ease-in-out infinite;
    }
    .ac-report-loader-bar:nth-child(1) { animation-delay: 0s; }
    .ac-report-loader-bar:nth-child(2) { animation-delay: .15s; }
    .ac-report-loader-bar:nth-child(3) { animation-delay: .3s; }
    .ac-report-loader-bar:nth-child(4) { animation-delay: .45s; }
    .ac-report-loader-title {
        font-family: 'Times New Roman', Times, serif;
        font-size: 20px;
        font-weight: bold;
        color: #0047ab;
        text-transform: uppercase;
        text-align: center;
    }
    .ac-report-loader-subtitle {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
        color: #777;
        text-align: center;
    }
    .ac-report-loader-dots::after {
        content: '.';
        display: inline-block;
        width: 1em;
        text-align: left;
        animation: ac-report-loader-dots 1.2s linear infinite;
    }
    @keyframes ac-report-loader-bar-grow {
        0%, 100% { height: 12px; }
        50%      { height: 48px; }
    }
    @keyframes ac-report-loader-dots {
        0%   { content: '.'; }
        33%  { content: '..'; }
        66%  { content: '...'; }
        100% { content: '.'; }
    }
    @media print {
        .ac-report-loader { display: none !important; }
    }
</style>

<script>
    (function () {
        function acHideReportLoader() {
            var loader = document.getElementById('acReportLoader');
            if (loader && loader.parentNode) {
                loader.style.display = 'none';
            }
        }

        if (document.readyState === 'complete') {
            acHideReportLoader();
        } else {
            window.addEventListener('load', acHideReportLoader);
        }

        // Failsafe: guarantees the loader is removed even if the load event never
        // fires, so it can never permanently block the report from view.
        window.setTimeout(acHideReportLoader, 4000);
    })();
</script>
