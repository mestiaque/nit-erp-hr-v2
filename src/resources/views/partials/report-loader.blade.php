{{-- Full-screen loading overlay shown while a report print view renders. Self-contained
     (no jQuery/FontAwesome dependency) so it can never break if those aren't loaded on the
     print page. Hidden automatically once the page finishes loading, with a hard failsafe
     timeout so it can never get stuck covering the report, and hidden in @media print so it
     never appears in the printed/PDF output. --}}
<div id="acReportLoader" class="ac-report-loader">
    <div class="ac-report-loader-spinner"></div>
    <div class="ac-report-loader-title">{{ config('acc-sfl.company.name') }}</div>
    <div class="ac-report-loader-subtitle">Loading Report&hellip;</div>
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
    .ac-report-loader-spinner {
        width: 48px;
        height: 48px;
        border: 5px solid #dbe4ee;
        border-top-color: #0047ab;
        border-radius: 50%;
        animation: ac-report-loader-spin 0.8s linear infinite;
    }
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
    @keyframes ac-report-loader-spin {
        to { transform: rotate(360deg); }
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
