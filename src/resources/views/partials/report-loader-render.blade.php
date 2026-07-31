{{-- Instant "loading" page returned on the first hit of a heavy report-print URL —
     see HrReportController::otSheetReportPrint(). All the actual report queries run
     server-side BEFORE any HTML can be sent, so a loader embedded in the real report
     page would never be visible; this page renders immediately instead, shows the
     spinner, then fetches the real report (with _render=1) in the background and
     swaps this whole document for it once it arrives. --}}
@extends('printMaster2')

@section('title', 'Report Loading...')

@section('contents')
<div id="acReportLoader" class="ac-report-loader">
    <div class="ac-report-loader-bars">
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
        <div class="ac-report-loader-bar"></div>
    </div>
    <div class="ac-report-loader-title">{{ hr_factory('name') ?? config('acc-sfl.company.name') }}</div>
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
    var params = new URLSearchParams(window.location.search);
    params.set('_render', '1');

    fetch(window.location.pathname + '?' + params.toString(), { credentials: 'same-origin' })
        .then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.text();
        })
        .then(function (html) {
            // Replaces this entire document (spinner included) with the fully-rendered
            // report — nothing needs to manually hide the loader, it's simply gone
            // along with the rest of this page's DOM.
            document.open();
            document.write(html);
            document.close();
        })
        .catch(function () {
            var loader = document.getElementById('acReportLoader');
            if (loader) {
                loader.innerHTML = '<div style="color:#c00;font-family:Arial,Helvetica,sans-serif;font-size:13px;text-align:center;">Failed to load the report. Please try again.</div>';
            }
        });
})();
</script>
@endsection
