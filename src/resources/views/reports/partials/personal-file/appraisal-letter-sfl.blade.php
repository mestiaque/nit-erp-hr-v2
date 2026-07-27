@php
    // Shared scope from the parent appraisal-letter.blade.php include: $employeeData,
    // $isBangla, $t, $employee are already available here.
    $skillEmployeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? $employeeData['employee_name'])
        : $employeeData['employee_name'];
    $skillFatherName = data_get($employee->basicInfo, 'father_name') ?: $employeeData['father_name'];
    if ($isBangla) {
        $skillFatherName = data_get($employee->basicInfo, 'bn_father_name') ?: $skillFatherName;
    }
    $skillEducation = $isBangla
        ? (data_get($employee->basicInfo, 'bn_educational_experience') ?: $employeeData['education'])
        : $employeeData['education'];
    $skillWorkDescription = $isBangla
        ? (data_get($employee->designation, 'responsibilities') ?: '')
        : (data_get($employee->designation, 'responsibilities') ?: '');
    $skillGross = (float) data_get($employeeData, 'salary.gross', 0);
    $skillGrade = (string) preg_replace('/\D/', '', (string) ($employeeData['grade'] ?? ''));

    // Experience (years/months/days) — tenure at this company, from Join Date up to
    // today (or the exit date, for a resigned employee), not a prior-employer figure.
    $skillExpYears = $skillExpMonths = $skillExpDays = null;
    if ($employee->join_date) {
        $expEnd = $employee->exited_at ? \Carbon\Carbon::parse($employee->exited_at) : now();
        $expDiff = \Carbon\Carbon::parse($employee->join_date)->diff($expEnd);
        $skillExpYears = $expDiff->y;
        $skillExpMonths = $expDiff->m;
        $skillExpDays = $expDiff->d;
    }
    $skillFmtExp = fn ($n) => is_null($n) ? '' : ($isBangla ? en2bnNumber((string) $n) : (string) $n);
@endphp

<div class="page">

  <div class="skill-head">
    @if(!blank(optional(general())->logo()))
        <img src="{{ asset(optional(general())->logo()) }}" alt="Logo" class="skill-logo">
    @endif
    <div class="skill-company">{{ $employeeData['company_name'] }}</div>
    <div class="skill-address">{{ $employeeData['company_address'] }}</div>
  </div>

  <div class="title">কর্মদক্ষতা মূল্যায়ন প্রতিবেদন (SKILL TEST REPORT)</div>

  <div class="field"><span class="label">তারিখ (Date of Examination)</span><span>:</span><span class="dots"></span></div>
  <div class="field"><span class="label">আবেদনকৃত পদের নাম (Name of Designation)</span><span>:</span><span class="dots value">{{ $employeeData['designation'] }}</span></div>
  <div class="field"><span class="label">প্রার্থীর নাম (Name of Examinee)</span><span>:</span><span class="dots value">{{ $skillEmployeeName }}</span></div>
  @php $skillAge = (int) ($employeeData['employee_age'] ?? 0); @endphp
  <div class="field"><span class="label">বয়স (Age)</span><span>:</span><span class="dots value">{{ $skillAge > 0 ? ($isBangla ? en2bnNumber((string) $skillAge) : $skillAge) : '' }}</span></div>
  <div class="field"><span class="label">পিতা/স্বামীর নাম (Father/Husband Name)</span><span>:</span><span class="dots value">{{ $skillFatherName }}</span></div>
  <div class="field"><span class="label">ব্লক/লাইন (Block/Line)</span><span>:</span><span class="dots value">{{ $employeeData['line'] }}</span></div>
  <div class="field"><span class="label">শিক্ষাগত যোগ্যতা (Educational Background)</span><span>:</span><span class="dots value">{{ $skillEducation }}</span></div>
  <div class="field"><span class="label">অভিজ্ঞতা (Experience)</span><span>: বছরঃ</span><span class="dots value">{{ $skillFmtExp($skillExpYears) }}</span><span>মাসঃ</span><span class="dots value">{{ $skillFmtExp($skillExpMonths) }}</span><span>দিনঃ</span><span class="dots value">{{ $skillFmtExp($skillExpDays) }}</span></div>
  <div class="field"><span class="label">বিশেষ দক্ষতা (Special Operational Skill)</span><span>:</span><span class="dots"></span></div>
  <div class="field"><span class="label">মনোভাব (Attitude)</span><span>:</span><span class="dots"></span></div>
  <div class="field"><span class="label">কাজের বিবরণ (Work Description)</span><span>:</span><span class="dots value">{{ $skillWorkDescription }}</span></div>

  <table>
    <tr>
      <th>ক্রমিক নং<br>SL. No</th>
      <th>প্রসেসের নাম<br>(Process Name)</th>
      <th>টার্গেট প্রতি ঘন্টায়<br>(Target Per Hour)</th>
      <th>মোট প্রোডাকশন<br>(Total Production)</th>
      <th>মেশিনের নাম<br>(Machine Name)</th>
      <th>মন্তব্য<br>Remarks</th>
    </tr>
    <tr><td>০১.</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>০২.</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>০৩.</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>০৪.</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>০৫.</td><td></td><td></td><td></td><td></td><td></td></tr>
  </table>

  <div class="title" style="font-size:16px; margin-bottom:8px;">গ্রেড (Grade)</div>
  <table>
    <tr><td>০১.</td><td class="grade-name left">সিঃ অপারেটর (Senior Operator)</td><td>১ (1)</td><td>{{ $skillGrade === '1' ? '✓' : '' }}</td></tr>
    <tr><td>০২.</td><td class="grade-name left">অপারেটর (Operator)</td><td>২ (2)</td><td>{{ $skillGrade === '2' ? '✓' : '' }}</td></tr>
    <tr><td>০৩.</td><td class="grade-name left">জুনিঃ অপারেটর (Junior Operator)</td><td>৩ (3)</td><td>{{ $skillGrade === '3' ? '✓' : '' }}</td></tr>
    <tr><td>০৪.</td><td class="grade-name left">সহকারী অপারেটর (Asst. Operator)</td><td>৪ (4)</td><td>{{ $skillGrade === '4' ? '✓' : '' }}</td></tr>
    <tr><td></td><td class="grade-name left"></td><td></td><td></td></tr>
  </table>

  <div class="bottomline">
    <div>প্রস্তাবিত বেতন (Proposed Salary) : {{ $skillGross > 0 ? ($isBangla ? en2bnNumber(number_format($skillGross, 2)) : number_format($skillGross, 2)) : '.................................' }} টাকা (Taka)</div>
    <div style="text-align:left;">
      Joining Date &nbsp;: {{ $employeeData['joining_date'] }}<br>
      Mobile Number &nbsp;: {{ $isBangla ? en2bnNumber((string) $employeeData['mobile_number']) : $employeeData['mobile_number'] }}<br>
      Nominee Number : {{ $isBangla ? en2bnNumber((string) $employeeData['nominee_mobile']) : $employeeData['nominee_mobile'] }}
    </div>
  </div>

  <div class="sign">
    <div>কর্মীর স্বাক্ষর<br><i>Employee Sign</i></div>
    <div>পরীক্ষকের নাম ও স্বাক্ষর<br>(Name &amp; Sign of Examiner Operator)</div>
    <div>ফ্যাক্টরী ব্যবস্থাপক<br>( Factory Manager)</div>
  </div>

</div>


@push('css')
<style>
  body { margin:0; font-family: 'Noto Sans Bengali','Segoe UI',Arial,sans-serif; }
  /* This partial always renders nested inside personal-file-print.blade.php's
     .pf-page wrapper (already 210mm wide with its own padding) — a second fixed
     210mm width + padding here would overflow that frame and force the browser
     to shrink the whole page to fit, making everything look smaller/narrower
     than intended. Just fill the parent's content width instead. */
  .page { width:100%; box-sizing:border-box; }
  .skill-head { text-align:center; margin-bottom:10px; }
  .skill-logo { max-height:34px; margin-bottom:2px; }
  .skill-company { font-size:16px; font-weight:bold; }
  .skill-address { font-size:11px; color:#333; }
  .title { text-align:center; font-size:20px; font-weight:bold; text-decoration:underline; margin-bottom:14px; }
  .field { display:flex; margin-bottom:8px; font-size:14px; }
  .label { white-space:nowrap; margin-right:6px; }
  .dots { flex:1; border-bottom:1px dotted #000; margin-left:4px; }
  .dots.value { border-bottom-style:solid; padding-bottom:1px; font-weight:600; }
  table { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:13px; }
  /* !important is required here — personal-file-print.blade.php's shared CSS has a
     page-wide `td{border:none !important;}` reset (for the letter-style documents
     that use <table> purely for layout), which would otherwise strip these borders
     since it also carries !important and beats normal specificity. */
  td, th { border:1px solid #000 !important; padding:6px 8px; text-align:center; vertical-align:middle; }
  .left { text-align:left; }
  .grade-name { text-align:left; width:35%; }
  .sign { display:flex; justify-content:space-between; margin-top:40px; font-size:13px; text-align:center; }
  .bottomline { display:flex; justify-content:space-between; margin-top:20px; font-size:14px; }
</style>
@endpush
