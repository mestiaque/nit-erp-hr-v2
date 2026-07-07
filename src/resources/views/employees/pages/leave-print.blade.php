@extends('printMaster2')

@section('title', 'ছুটির আবেদন পত্র - ' . $employee->name)

@push('css')
<style>
/* ── Override printMaster2 globals ── */
body {
    background: #f0f2f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #111;
    -webkit-font-smoothing: antialiased;
    font-size: 12px;
}
/* printMaster2 sets company-name blue/uppercase — reset for this page */
.company-name {
    font-size: 20px !important;
    font-weight: bold !important;
    letter-spacing: .5px !important;
    color: #111 !important;
    text-transform: none !important;
}
/* printMaster2 sets table margin-bottom:20px and th bg #cfcfcf — reset inside sidebar */
.sidebar table { margin-bottom: 0 !important; }
.sidebar table th { background: #f8f8f8 !important; padding: 5px 4px !important; }
.sidebar table td { padding: 5px 4px !important; }

/* ── Form container ── */
.form-container {
    max-width: 950px;
    margin: 0 auto 20px;
    background: #fff;
    padding: 20px 40px;
    border-radius: 4px;
}

/* ── Header ── */
.header {
    position: relative;
    text-align: center;
    margin-bottom: 8px;
}
.brand-logo {
    position: absolute;
    top: 0; left: 0;
    width: 54px; height: 54px;
    border-radius: 50%;
    overflow: hidden;
}
.company-address { font-size: 12px; margin-bottom: 4px; color: #111 !important; }
.form-title {
    display: inline-block;
    font-size: 16px;
    font-weight: bold;
    border-bottom: 2px solid #111;
    padding: 0 10px 2px;
    margin-top: 4px;
}

/* ── Date row ── */
.date-row {
    text-align: right;
    margin-bottom: 8px;
    padding-right: 4px;
}
.dotted { border-bottom: 1.5px dotted #555; display: inline-block; vertical-align: bottom; }

/* ── Two-column body ── */
.body-row {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.left-col  { flex: 1 1 0; min-width: 0; }
.right-col { width: 260px; flex-shrink: 0; }

/* ── Form rows ── */
.form-row {
    display: flex;
    align-items: flex-end;
    margin-bottom: 7px;
}
.sl    { width: 26px; flex-shrink: 0; font-weight: 500; }
.label { width: 130px; flex-shrink: 0; }
.colon { width: 16px; text-align: center; flex-shrink: 0; }
.line  { border-bottom: 1.5px dotted #555; flex: 1 1 auto; position: relative; top: -3px; }
.inline-word { white-space: nowrap; flex-shrink: 0; font-weight: 500; padding: 0 3px; }

/* ── Checkboxes ── */
.checkbox-group { display: flex; flex-wrap: wrap; gap: 6px 14px; margin-left: 4px; }
.cb { display: flex; align-items: center; gap: 5px; cursor: pointer; }
.cb-box {
    width: 14px; height: 14px;
    border: 1.5px solid #111;
    border-radius: 1px;
    flex-shrink: 0;
    display: inline-block;
}

/* ── Substitute section ── */
.sub-title { font-size: 12px; margin-left: 26px; }
.sig-line { border-bottom: 1px solid #111; }
.substitute-sig { width: 180px; margin-left: 160px; margin-top: 12px; text-align: center; }
.applicant-sig  { width: 180px; margin-top: 14px; text-align: center; }
.quote-text {
    font-style: italic; font-size: 12px; color: #333;
    text-align: center; margin: 10px 0 12px;
}
.note-text { font-size: 11px; margin-top: 8px; }

/* ── Right sidebar ── */
.sidebar {
    border: 1.5px solid #111;
}
.sidebar-header {
    text-align: center;
    font-weight: bold;
    padding: 5px;
    border-bottom: 1.5px solid #111;
    font-size: 13px;
}
.sidebar-subheader {
    text-align: center;
    background: #111;
    color: #fff;
    padding: 4px;
    font-size: 12px;
    border-bottom: 1.5px solid #111;
}
.sidebar table {
    width: 100%;
    border-collapse: collapse;
}
.sidebar table th,
.sidebar table td {
    border: 1px solid #111;
    font-size: 11px;
    text-align: center;
}
.sidebar table td:first-child { text-align: left; padding-left: 6px; }

.recommender-box {
    padding: 6px 8px;
    border-top: 1.5px solid #111;
    text-align: center;
}
.recommender-box .sig-label { font-size: 10px; color: #555; margin-top: 5px; margin-bottom: 2px; }
.recommender-box .sig-title { font-weight: bold; margin-bottom: 4px; font-size: 12px; }
.recommender-box .rec-note {
    font-size: 10px; text-align: left;
    line-height: 1.4; margin-bottom: 8px;
}
.rec-sig-line { border-bottom: 1px solid #111; width: 75%; margin: 0 auto 3px; }
.rec-sig-caption { font-size: 10px; }

.dept-head-box {
    padding: 6px 8px;
    border-top: 1.5px solid #111;
    text-align: center;
}
.dept-head-box .title { font-weight: bold; font-size: 12px; margin-bottom: 4px; }
.dept-sig-line { border-bottom: 1px solid #111; width: 75%; margin: 10px auto 3px; }
.dept-sig-caption { font-size: 10px; margin-bottom: 6px; }

/* ── Cut line ── */
.cut-line {
    position: relative;
    height: 20px;
    margin: 14px 0;
}
.cut-border {
    position: absolute;
    top: 50%;
    left: 0; right: 0;
    border-top: 2px dashed #999;
}
.scissors {
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    background: #fff;
    padding-right: 8px;
    line-height: 0;
    color: #888;
}

/* ── Leave Pass ── */
.pass-header { text-align: center; margin-bottom: 6px; }
.pass-header h3 { font-size: 14px; font-weight: bold; }
.pass-header p  { font-size: 11px; }
.pass-title     { font-size: 13px; font-weight: bold; }

.double-line { border-top: 4px double #111; margin: 6px 0 10px; }

.pass-body { display: flex; gap: 16px; align-items: flex-start; }
.pass-left  { flex: 1 1 0; min-width: 0; }
.pass-right { width: 240px; flex-shrink: 0; }

.note-box {
    border: 1.5px solid #111;
    border-radius: 2px;
    padding: 5px 8px;
    font-size: 11px;
    font-weight: 500;
    margin-top: 8px;
}

.pass-date-row {
    text-align: right;
    margin-bottom: 6px;
    font-size: 11px;
}
.hr-sig-area { margin-top: 10px; text-align: center; }
.hr-sig-line { border-bottom: 1px solid #111; width: 75%; margin: 20px auto 3px; }
.hr-sig-label { font-weight: bold; font-size: 11px; }

@media print {
    body { background: #fff; font-size: 11px; }
    .form-container {
        margin: 0;
        padding: 10px 16px;
        max-width: 100%;
    }
    .form-row { margin-bottom: 5px !important; }
    .cut-line { margin: 8px 0 !important; }
    .substitute-sig { margin-top: 8px !important; }
    .applicant-sig  { margin-top: 10px !important; }
    .quote-text     { margin: 6px 0 8px !important; }
    .dept-sig-line  { margin: 7px auto 2px !important; }
    .dept-sig-caption { margin-bottom: 4px !important; }
    .recommender-box .rec-note { margin-bottom: 5px !important; }
    .double-line { margin: 4px 0 6px !important; }
    .header { margin-bottom: 4px !important; }
    .date-row { margin-bottom: 4px !important; }
    .label { width: 100px; flex-shrink: 0; }
    /* -webkit-print-color-adjust: exact;
    print-color-adjust: exact; */
}
</style>
@endpush

@section('contents')
@php
    $leaveFrom = $leave->leave_from ?? $leave->start_date ?? '';
    $leaveTo   = $leave->leave_to   ?? $leave->end_date   ?? '';
    $appDate   = $leave->application_date ?? '';
    $appNo     = $leave->application_no   ?? '';
    $reason    = $leave->reason           ?? '';
    $totalDays = $leave->total_days       ?? '';
    // Bangla converters (fallback to English if helpers unavailable)
    $bnNum  = fn($n) => function_exists('en2bnNumber') ? en2bnNumber((string)$n) : (string)$n;
    $bnDate = function ($d) use (&$bnNum) {
        if (!$d) return '';
        $formatted = \Carbon\Carbon::parse($d)->format('d-M-Y');
        if (function_exists('en2bnNumber') && function_exists('en2bnMonth')) {
            return en2bnNumber(en2bnMonth($formatted));
        }
        return $formatted;
    };
    $joinDate = $employee->join_date ? bn_date($employee->join_date, 'd/m/Y') : '';
    $prevLeaveDate = $prevLeave
        ? bn_date($prevLeave->leave_from ?? $prevLeave->start_date, 'd/m/Y')
        : '';
    $sectionDept = implode(' / ', array_filter([
        $employeeMeta['section_bn']    ?? $employeeMeta['section']    ?? '',
        $employeeMeta['department_bn'] ?? $employeeMeta['department'] ?? '',
    ]));
    // Which leave type checkbox to tick
    $leaveCode  = strtoupper(trim($leaveType->code ?? ''));
    $isNaimitik = in_array($leaveCode, ['CL','NL']);
    $isOsustha  = in_array($leaveCode, ['SL']);
    $isArjit    = in_array($leaveCode, ['EL','AL']);
    $isMaatri   = in_array($leaveCode, ['ML','MAT']);
    $sidebarRows = $leaveSummary ?? [];
@endphp

<div class="form-container">

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <div class="brand-logo">
            <img src="{{ asset(general()->logo()) }}" alt="logo" style="width:100%;height:100%;object-fit:contain;border-radius:50%;">
        </div>
        <div class="company-name">{{ $factory?->bn_name ?: $factory?->name }}</div>
        <div class="company-address">{{ $factory?->bn_address ?: $factory?->address }}</div>
        <div class="form-title">ছুটির আবেদন পত্র</div>
    </div>

    <div class="date-row">
        তারিখ ঃ <span class="dotted" style="width:180px; padding: 0 6px;">{{ bn_date($appDate, 'd/m/Y') }}</span>
    </div>

    {{-- ===== MAIN TWO-COLUMN ===== --}}
    <div class="body-row">

        {{-- LEFT COLUMN --}}
        <div class="left-col">

            <div class="form-row">
                <span class="sl">১.</span>
                <span class="label">আবেদনকারীর নাম</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px; font-weight:600;">{{ $employee->bn_name ?: $employee->name }}</span>
            </div>

            <div class="form-row">
                <span class="sl">২.</span>
                <span class="label">আই.ডি. নং</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px; font-weight:600;">{{ $employee->employee_id }}</span>
            </div>

            <div class="form-row">
                <span class="sl">৩.</span>
                <span class="label">পদবী</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ $employeeMeta['designation_bn'] ?? $employeeMeta['designation'] ?? '' }}</span>
            </div>

            <div class="form-row">
                <span class="sl">৪.</span>
                <span class="label">সেকশন / বিভাগ</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ $sectionDept }}</span>
            </div>

            <div class="form-row">
                <span class="sl">৫.</span>
                <span class="label">যোগদানের তারিখ</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ $joinDate }}</span>
            </div>

            <div class="form-row" style="margin-bottom:10px;">
                <span class="sl">৬.</span>
                <span class="label">সর্বশেষ ছুটির তারিখ</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ $prevLeaveDate }}</span>
            </div>

            {{-- Leave type checkboxes --}}
            <div class="form-row" style="align-items:center; margin-bottom:6px;">
                <span class="sl">৭.</span>
                <span class="label">ছুটির ধরন</span>
                <span class="colon">:</span>
                <div class="checkbox-group">
                    <label class="cb"><span class="cb-box">@if($isNaimitik)✓@endif</span> নৈমিত্তিক</label>
                    <label class="cb"><span class="cb-box">@if($isOsustha)✓@endif</span> অসুস্থতা</label>
                    <label class="cb"><span class="cb-box">@if($isArjit)✓@endif</span> অর্জিত</label>
                    <label class="cb"><span class="cb-box">@if($isMaatri)✓@endif</span> মাতৃত্বকালীন</label>
                </div>
            </div>

            {{-- Leave dates --}}
            <div class="form-row" style="align-items:center; margin-bottom:10px;">
                <span class="sl">৮.</span>
                <span class="label">ছুটির তারিখ</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ bn_date($leaveFrom) }}</span>
                <span class="inline-word">থেকে</span>
                <span class="line" style="padding: 0 6px;">{{ bn_date($leaveTo) }}</span>
                <span class="inline-word">পর্যন্ত</span>
                <span class="dotted" style="width:55px; margin: 0 4px; padding: 0 4px; text-align:center;">{{ $bnNum($totalDays) }}</span>
                <span class="inline-word">দিন।</span>
            </div>

            <div class="form-row" style="margin-bottom:10px;">
                <span class="sl">৯.</span>
                <span class="label">ছুটির কারণ</span>
                <span class="colon">:</span>
                <span class="line" style="padding: 0 6px;">{{ $reason }}</span>
            </div>

            <div class="form-row" style="margin-bottom:18px;">
                <span class="sl">১০.</span>
                <span class="label">ছুটি কালীন ঠিকানা</span>
                <span class="colon">:</span>
                <span class="line">&nbsp;</span>
            </div>

            {{-- Substitute --}}
            <div style="margin-bottom:10px;">
                <div style="display:flex; align-items:flex-start;">
                    <span class="sl">১১.</span>
                    <span class="sub-title">বদলী ব্যক্তি (প্রযোজ্য ক্ষেত্রে) ঃ ছুটিতে থাকাকালীন সময়ে তার দায়িত্ব পালন করবেন।</span>
                </div>
            </div>

            <div class="form-row" style="padding-left:28px; margin-bottom:4px;">
                <span class="inline-word" style="padding-left:0;">নাম :</span>
                <span class="line">&nbsp;</span>
                <span class="inline-word">পদবী :</span>
                <span class="line">&nbsp;</span>
                <span class="inline-word">আইডি নং :</span>
                <span class="line">&nbsp;</span>
            </div>

            <div class="substitute-sig">
                <div class="sig-line">&nbsp;</div>
                <div style="font-size:12px; margin-top:3px;">বদলী ব্যক্তির স্বাক্ষর</div>
            </div>

            <div class="quote-text">
                "অনাকাঙ্ক্ষিত কোন ঘটনার উৎপত্তি না হলে মঞ্জুরীকৃত ছুটি বর্ধিত করার জন্য পুনরায় আবেদন করব না"
            </div>

            <div class="applicant-sig">
                <div class="sig-line">&nbsp;</div>
                <div style="font-size:12px; margin-top:3px;">আবেদনকারীর স্বাক্ষর</div>
            </div>

            <p class="note-text">দ্রষ্টব্যঃ অসুস্থতাজনিত ছুটির ক্ষেত্রে চিকিৎসা পত্রের ফটোকপি সংযুক্ত করতে হবে।</p>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="right-col">
            <div class="sidebar">
                <div class="sidebar-header">এ.এইচ.আর. ডিপার্টমেন্ট</div>
                <div class="sidebar-subheader">চলতি বছরের ছুটির হিসাব</div>
                <table>
                    <thead>
                        <tr>
                            <th>ছুটির ধরন</th>
                            <th>মোট</th>
                            <th>ভোগকৃত</th>
                            <th>অবশিষ্ট</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sidebarRows as $sr)
                        <tr>
                            <td>{{ $sr['bn_name'] ?: $sr['name'] }}</td>
                            <td>{{ $bnNum($sr['total']) }}</td>
                            <td>{{ $bnNum($sr['taken']) }}</td>
                            <td>{{ $bnNum($sr['remaining']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="recommender-box">
                    <div class="sig-label">স্বাক্ষর</div>
                    <div class="sig-title">সুপারিশকারী</div>
                    <div class="rec-note">
                        আমি এ বিষয়ে নিশ্চিত করছি যে, সংশ্লিষ্ট ব্যক্তি ছুটিতে থাকাকালীন স্বাভাবিক কাজ ব্যাহত হবে না।
                    </div>
                    <div class="rec-sig-line">&nbsp;</div>
                    <div class="rec-sig-caption">স্বাক্ষর (ইনচার্জ)</div>
                </div>

                <div class="dept-head-box">
                    <div class="title">বিভাগীয় প্রধান</div>
                    <div class="dept-sig-line">&nbsp;</div>
                    <div class="dept-sig-caption">এ.পি.এম / পি.এম / টি.ডি.এম / কিউ.এ</div>
                    <div class="dept-sig-line">&nbsp;</div>
                    <div class="dept-sig-caption">ম্যানেজার (এইচআর) / জিএম</div>
                    <div class="dept-sig-line">&nbsp;</div>
                    <div class="dept-sig-caption">পরিচালক</div>
                    <div class="dept-sig-line">&nbsp;</div>
                    <div class="dept-sig-caption">ব্যবস্থাপনা পরিচালক</div>
                </div>
            </div>
        </div>

    </div>{{-- end body-row --}}

    {{-- ===== CUT LINE ===== --}}
    <div class="cut-line">
        <div class="scissors">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
                <line x1="20" y1="4" x2="8.12" y2="15.88"/>
                <line x1="14.47" y1="14.48" x2="20" y2="20"/>
                <line x1="8.12" y1="8.12" x2="12" y2="12"/>
            </svg>
        </div>
        <div class="cut-border"></div>
    </div>

    {{-- ===== LEAVE PASS ===== --}}
    <div class="pass-header">
        <h3>{{ $factory?->bn_name ?: $factory?->name }}</h3>
        <p>{{ $factory?->bn_address ?: $factory?->address }}</p>
        <div class="pass-title" style="margin-top:6px; border-bottom:2px solid #111; display:inline-block; padding: 0 8px 2px;">ছুটির ছাড় পত্র</div>
    </div>

    <div class="double-line"></div>

    <div class="pass-body">

        {{-- PASS LEFT --}}
        <div class="pass-left">
            <div class="form-row" style="margin-bottom:12px;">
                <span class="inline-word" style="padding-left:0;">নাম</span>
                <span class="line" style="padding: 0 5px; font-weight:600;">{{ $employee->bn_name ?: $employee->name }}</span>
                <span class="inline-word">আইডি নং</span>
                <span class="line" style="padding: 0 5px; font-weight:600;">{{ $employee->employee_id }}</span>
                <span class="inline-word">পদবী</span>
                <span class="line" style="padding: 0 5px;">{{ $employeeMeta['designation_bn'] ?? $employeeMeta['designation'] ?? '' }}</span>
            </div>

            <div class="form-row" style="margin-bottom:14px; align-items:center;">
                <span class="line" style="padding: 0 5px;">{{ $bnDate($leaveFrom) }}</span>
                <span class="inline-word">থেকে</span>
                <span class="line" style="padding: 0 5px;">{{ $bnDate($leaveTo) }}</span>
                <span class="inline-word">তারিখ পর্যন্ত</span>
                <span class="dotted" style="width:65px; margin:0 4px; text-align:center;">{{ $bnNum($totalDays) }}</span>
                <span class="inline-word">দিন</span>
            </div>

            <div style="font-size:12px; font-weight:500; margin-bottom:12px;">
                নৈমিত্তিক / অসুস্থতা / অর্জিত / মাতৃত্বকালীন ছুটি প্রদান করা হয়েছে।
            </div>

            <div style="margin-bottom:8px; display:flex; align-items:center; gap:6px; font-size:12px;">
                <span class="cb-box"></span>
                ছুটির আবেদনটি কর্তৃপক্ষ দ্বারা অনুমোদিত হয়নি।
            </div>

            <div style="display:flex; align-items:flex-end; gap:6px; font-size:12px; margin-bottom:14px;">
                <span class="cb-box" style="flex-shrink:0;"></span>
                <span style="white-space:nowrap;">ছুটি অনুমোদন না হওয়ার কারণ :</span>
                <span class="line" style="flex:1;">&nbsp;</span>
            </div>

            <div class="note-box">
                দ্রষ্টব্য : 'ছুটির ছাড়পত্র' ব্যতিরেকে আবেদনকারীকে কার্যালয় ত্যাগ না করার জন্য জানানো যাচ্ছে।
            </div>
        </div>

        {{-- PASS RIGHT --}}
        <div class="pass-right">
            <div class="pass-date-row">
                তারিখ ঃ <span class="dotted" style="width:140px;">&nbsp;</span>
            </div>

            <div class="sidebar">
                <div class="sidebar-subheader">চলতি বছরের ছুটির হিসাব</div>
                <table>
                    <thead>
                        <tr>
                            <th>ছুটির ধরন</th>
                            <th>মোট</th>
                            <th>ভোগকৃত</th>
                            <th>অবশিষ্ট</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sidebarRows as $sr)
                        <tr>
                            <td>{{ $sr['bn_name'] ?: $sr['name'] }}</td>
                            <td>{{ $bnNum($sr['total']) }}</td>
                            <td>{{ $bnNum($sr['taken']) }}</td>
                            <td>{{ $bnNum($sr['remaining']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="hr-sig-area">
                <div class="hr-sig-line">&nbsp;</div>
                <div class="hr-sig-label">এ.এইচ.আর. ডিপার্টমেন্ট</div>
            </div>
        </div>

    </div>{{-- end pass-body --}}

</div>{{-- end form-container --}}
@endsection
