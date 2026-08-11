@extends('printMaster2')

@section('title', 'Gate Pass - ' . $gatePass->pass_no)

@push('css')
<style>
* { font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Nikosh', Arial, sans-serif; }

.gp-sheet { display: flex; flex-wrap: wrap; gap: 0; align-items: stretch; justify-content: center; }
.gp-wrap { flex: 1 1 0; max-width: 480px; min-width: 320px; border: 1px solid #000; padding: 10px 14px 14px; }
.gp-divider { flex: 0 0 auto; width: 0; border-left: 1px dashed #000; margin: 0 8px; }

.gp-head-row { display: flex; justify-content: center; align-items: flex-start; border-bottom: 1px solid #000; padding-bottom: 6px; margin-bottom: 6px; }
.gp-head-left { display: flex; gap: 8px; align-items: center; }
.gp-head-left img { max-height: 30px; }
.gp-company-name { font-size: 18px; font-weight: 700; margin: 0; text-align: center; }
.gp-company-address { font-size: 9.5px; color: #333; margin: 0; }

.gp-code-box { border: 1px solid #000; font-size: 8.5px; min-width: 110px; }
.gp-code-box table { border-collapse: collapse; width: 100%; }
.gp-code-box td { padding: 1px 4px; border-bottom: 1px solid #000; }
.gp-code-box tr:last-child td { border-bottom: none; }
.gp-unit-code { border-top: 1px solid #000; padding: 1px 4px; font-size: 8.5px; }
.gp-unit-code .box { display: inline-block; border: 1px solid #000; width: 40px; height: 12px; vertical-align: middle; margin-left: 4px; }

.gp-title-row { display: flex; justify-content: space-between; align-items: baseline; margin: 4px 0 10px; }
.gp-title { font-size: 15px; font-weight: 700; text-decoration: underline; width: 80%; text-align: center; padding-left: 35%; }
.gp-copy-tag { font-size: 10.5px; font-weight: 700; border: 1px dashed #0000007a;}
.gp-pass-no { font-size: 9px; color: #333; }

.gp-field { font-size: 11.5px; margin-bottom: 9px; display: flex; flex-wrap: wrap; gap: 4px 8px; }
.gp-field .lbl { white-space: nowrap; }
.gp-field .val { flex: 1 1 auto; min-width: 60px; border-bottom: 1px dotted #000; padding: 0 2px; }
.gp-field-inline { display: flex; flex-wrap: wrap; gap: 4px 18px; }
.gp-field-inline > div { display: flex; align-items: baseline; gap: 4px; flex: 1 1 45%; }
.gp-field-inline .val { flex: 1 1 auto; border-bottom: 1px dotted #000; padding: 0 2px; min-width: 40px; }

.gp-purpose-hint { font-size: 9px; color: #555; margin-top: 1px; }

.gp-note { font-size: 9px; color: #555; }

.gp-sign-row { display: flex; justify-content: space-between; margin-top: 34px; }
.gp-sign-box { text-align: center; width: 31%; font-size: 10px; }
.gp-sign-box .line { border-top: 1px solid #000; margin-bottom: 3px; }

.date { font-size: 11px; color: #333; position: absolute;   }

@media print {
    /* No forced orientation here — leave size/orientation to the browser's
       print dialog (Portrait/Landscape) so the user can pick either. Forcing
       `size: A4 landscape` overrides/hides that browser control. */
    @page { margin: 1mm; scale: 80; }
    body { margin: 0; zoom: 0.8; }
}
</style>
@endpush

@section('contents')
@php
    $company = hr_factory('name') ?? 'Company Name';
    $address = hr_factory('address') ?? '';

    // Reasons are stored in Bangla directly now; this only translates old
    // records created before that change (English values still in the DB).
    $reasonBn = [
        'Personal Work'        => 'ব্যক্তিগত কাজ',
        'Medical / Emergency'  => 'চিকিৎসা / জরুরী প্রয়োজন',
        'Official Work'        => 'অফিসিয়াল কাজ',
        'Bank Work'            => 'ব্যাংক সংক্রান্ত কাজ',
        'Family Emergency'     => 'পারিবারিক জরুরী প্রয়োজন',
        'Others'               => 'অন্যান্য',
    ][$gatePass->reason] ?? $gatePass->reason;

    $outTime = $gatePass->out_time;
    $inTime  = $gatePass->in_time;
    $ampmBn  = fn ($t) => $t ? ($t->format('A') === 'AM' ? 'এ.এম' : 'পি.এম') : '';
    $approxHours = $gatePass->duration_minutes ? round($gatePass->duration_minutes / 60, 1) : '';

    $employee   = $gatePass->employee;
    $empName    = $employee->bn_name ?? $employee->name ?? 'N/A';
    $empDesig   = optional($employee?->designation)->bn_name ?? optional($employee?->designation)->name ?? 'N/A';
    $empSection = optional($employee?->section)->bn_name ?? optional($employee?->section)->name ?? '';
    $empLine    = optional($employee?->floorLine)->bn_line_name ?? optional($employee?->floorLine)->line_name ?? '';
    $empDept    = optional($employee?->department)->bn_name ?? optional($employee?->department)->name ?? 'N/A';

    // Bengali digits for the numeric-only values (ID, hours) so the printed
    // slip reads fully in Bangla rather than mixing scripts mid-field.
    $bnDigits = fn ($v) => $v === null || $v === '' ? '' : strtr((string) $v, ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯']);

    $copies = ['অফিস কপি', 'গেইট কপি'];
@endphp

<div class="gp-sheet">
    @foreach($copies as $copyIndex => $copyLabel)
        @if($copyIndex > 0)
            <div class="gp-divider"></div>
        @endif
        <div class="gp-wrap">
            <div class="gp-head-row">
                <div class="gp-head-left">
                    @if(!blank(optional(general())->logo()))
                        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo">
                    @endif
                    <div>
                        <p class="gp-company-name">{{ $company }}</p>
                        <p class="gp-company-address">{{ $address }}</p>
                    </div>
                </div>
                {{-- <div class="gp-code-box">
                    <table>
                        <tr><td>Code</td><td>: HR/GP</td></tr>
                        <tr><td>Rev</td><td>: 00</td></tr>
                        <tr><td>Date</td><td>: {{ $bnDigits($gatePass->created_at?->format('d.m.Y')) }}</td></tr>
                    </table>
                    <div class="gp-unit-code">Unit Code<span class="box"></span></div>
                </div> --}}
            </div>
            <span class="date">তারিখ: {{ $bnDigits($gatePass->created_at?->format('d.m.Y')) }}</span>

            <div class="gp-title-row">
                <div class="gp-title">গেইট পাস</div>
                <div class="gp-copy-tag">{{ $copyLabel }}</div>
            </div>
            <div class="gp-pass-no">পাস নং: {{ $gatePass->pass_no }}</div>
            <br>

            <div class="gp-field">
                <span class="lbl">তারিখ:</span>
                <span class="val">{{ $bnDigits(optional($outTime)->format('d-m-Y')) }}</span>
            </div>

            <div class="gp-field-inline">
                <div style="">
                    <span class="lbl">জনাব:</span>
                    <span class="val">{{ $empName }}</span>
                </div>
                <div style="">
                    <span class="lbl">পদবী:</span>
                    <span class="val">{{ $empDesig }}</span>
                </div>
            </div>

            <div class="gp-field-inline">
                <div>
                    <span class="lbl">আইডি নং:</span>
                    <span class="val">{{ $bnDigits($employee->employee_id ?? '-') }}</span>
                </div>
                <div>
                    <span class="lbl">সেকশন:</span>
                    <span class="val">{{ $empSection }}</span>
                </div>
                <div>
                    <span class="lbl">লাইন নং:</span>
                    <span class="val">{{ $bnDigits($empLine) }}</span>
                </div>
                <div>
                    <span class="lbl">বিভাগ:</span>
                    <span class="val">{{ $empDept }}</span>
                </div>
            </div>

            <div class="gp-field">
                <span class="lbl">বাহিরে যাওয়ার উদ্দেশ্যে:</span>
                <span class="val">{{ $reasonBn }}{{ $gatePass->remarks ? ' — ' . $gatePass->remarks : '' }}</span>
            </div>

            <div class="gp-field">
                <span class="lbl">ফিরে আসার সময় (আনু:):</span>
                <span class="val">{{ $bnDigits($approxHours) }}</span>
                <span class="lbl">ঘন্টা</span>
            </div>

            <div class="gp-field">
                <span class="lbl">বর্হিগমনের সময়:</span>
                <span class="val">{{ $bnDigits(optional($outTime)->format('h:i')) }} &nbsp;&nbsp;&nbsp;{{ $ampmBn($outTime) }}</span>
                <span class="lbl"></span>
            </div>

            <div class="gp-field">
                <span class="lbl">ফিরে আসার সময়:</span>
                <span class="val">{{ $bnDigits(optional($inTime)->format('h:i')) }} &nbsp;&nbsp;&nbsp;{{ $ampmBn($inTime) }}</span>
                <span class="lbl"><span class="gp-note">(নিরাপত্তা সেকশন পূরণ করবেন)</span></span>
            </div>
            {{-- <div class="gp-note">(নিরাপত্তা সেকশন পূরণ করবেন)</div> --}}

            <div class="gp-sign-row">
                <div class="gp-sign-box"><div class="line"></div>আবেদনকারী</div>
                <div class="gp-sign-box"><div class="line"></div>সেকশন/বিভাগীয় প্রধান</div>
                <div class="gp-sign-box"><div class="line"></div>প্রশাসনিক প্রধান</div>
            </div>
        </div>
    @endforeach
</div>
@endsection
