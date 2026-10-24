@php
    $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');
    $num = fn ($value) => $isBangla ? en2bnNumber(number_format((float) $value, 2)) : number_format((float) $value, 2);

    $companyName = $employeeData['company_name'];
    $companyAddress = $employeeData['company_address'];
    $employeeName = $employeeData['employee_name'];
    $employeeId = $employeeData['employee_id'] ?? $na;
    $designation = $employeeData['designation'] ?? $na;
    $department = $employeeData['department'] ?? null;
    $section = $employeeData['section'] ?? $na;

    $joinDateRaw = data_get($employee, 'joining_date') ?: data_get($employee, 'join_date');
    $joinDate = blank($joinDateRaw)
        ? $na
        : ($isBangla ? bn_date($joinDateRaw, 'd/m/Y') : \Illuminate\Support\Carbon::parse($joinDateRaw)->format('d/m/Y'));
    $issueDate = $isBangla ? bn_date(now()->toDateString(), 'd/m/Y') : now()->format('d/m/Y');

    $status = (string) ($employee->employment_status ?? 'regular');
    $isSeparated = in_array($status, ['lefty', 'left', 'resign', 'resigned'], true);

    // Effective salary (increment override already applied by HrOptionsService)
    $sal = $employeeData['salary'] ?? [];
    $components = collect([
        [$t('মূল বেতন', 'Basic Salary'), $sal['basic'] ?? 0, true],
        [$t('বাড়ি ভাড়া', 'House Rent'), $sal['house'] ?? 0, false],
        [$t('চিকিৎসা ভাতা', 'Medical Allowance'), $sal['medical'] ?? 0, false],
        [$t('যাতায়াত ভাতা', 'Conveyance Allowance'), $sal['transport'] ?? 0, false],
        [$t('খাদ্য ভাতা', 'Food Allowance'), $sal['food'] ?? 0, false],
    ])->filter(fn ($row) => $row[2] || (float) $row[1] > 0)->values();
    $gross = (float) ($sal['gross'] ?? 0) ?: (float) $components->sum(fn ($row) => (float) $row[1]);
    $grossInWords = $isBangla
        ? \ME\Hr\Services\SalaryReportService::numberToWordsBn((int) round($gross)) . ' টাকা মাত্র'
        : 'Taka ' . ucwords(\ME\Hr\Services\SalaryReportService::numberToWords((int) round($gross))) . ' Only';

    $cell = 'padding: 6px 10px; border: 1px solid #000;';
@endphp

<div style="font-family: 'Nikosh', 'Arial', sans-serif; max-width: 800px; margin: auto; color: #000; line-height: 1.6; padding: 20px;">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:25px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        @if(!blank(report_logo_url()))
            <img src="{{ report_logo_url() }}" alt="Logo" style="max-height:60px; margin-bottom:6px;">
        @endif
        <h2 style="margin:0; text-transform: uppercase;">{{ $companyName }}</h2>
        <div style="font-size: 14px;">{{ $companyAddress }}</div>
    </div>

    <!-- Date and Ref -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td><strong>{{ $t('সূত্র নং', 'Ref No') }}:</strong> {{ $employeeId }}/SC/{{ date('Y') }}</td>
            <td style="text-align: right;"><strong>{{ $t('তারিখ', 'Date') }}:</strong> {{ $issueDate }}</td>
        </tr>
    </table>

    <div style="text-align:center; margin-bottom: 25px;">
        <span style="font-size: 18px; font-weight: bold; text-decoration: underline; letter-spacing: 1px;">
            {{ $t('বেতন প্রত্যয়নপত্র', 'SALARY CERTIFICATE') }}
        </span>
    </div>

    <div style="margin-bottom: 15px; font-weight: bold;">
        {{ $t('সংশ্লিষ্ট সকলের অবগতির জন্য', 'To Whom It May Concern') }}
    </div>

    <div style="margin-bottom: 20px; text-align: justify;">
        @if($isBangla)
            এই মর্মে প্রত্যয়ন করা যাচ্ছে যে, <strong>{{ $employeeName }}</strong> (আইডি নং: {{ $employeeId }})
            {{ $joinDate }} ইং তারিখ থেকে অত্র প্রতিষ্ঠানে <strong>{{ $designation }}</strong> পদে
            @if(hr_hide_section_line()){{ $department ? $department . ' বিভাগে' : '' }}@else{{ $department ? $department . ' বিভাগ, ' : '' }}{{ $section }} সেকশনে@endif
            {{ $isSeparated ? 'কর্মরত ছিলেন।' : 'কর্মরত আছেন।' }}
            {{ $isSeparated ? 'তার সর্বশেষ মাসিক বেতনের বিবরণ নিম্নরূপ:' : 'তার বর্তমান মাসিক বেতনের বিবরণ নিম্নরূপ:' }}
        @else
            This is to certify that <strong>{{ $employeeName }}</strong> (ID No: {{ $employeeId }})
            {{ $isSeparated ? 'was employed' : 'has been employed' }} with this organization since {{ $joinDate }}
            as <strong>{{ $designation }}</strong>{{ $department ? ' in the ' . $department . ' department' : '' }}@unless(hr_hide_section_line()), {{ $section }} section@endunless.
            {{ $isSeparated ? 'Details of the last drawn monthly salary are as follows:' : 'Details of the current monthly salary are as follows:' }}
        @endif
    </div>

    <!-- Salary Breakdown -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="{{ $cell }} text-align: left; width: 10%;">{{ $t('ক্রমিক', 'SL') }}</th>
                <th style="{{ $cell }} text-align: left;">{{ $t('বিবরণ', 'Particulars') }}</th>
                <th style="{{ $cell }} text-align: right; width: 30%;">{{ $t('টাকা', 'Amount (BDT)') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($components as $index => [$componentLabel, $amount])
                <tr>
                    <td style="{{ $cell }}">{{ $isBangla ? en2bnNumber($index + 1) : $index + 1 }}</td>
                    <td style="{{ $cell }}">{{ $componentLabel }}</td>
                    <td style="{{ $cell }} text-align: right;">{{ $num($amount) }}</td>
                </tr>
            @endforeach
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td style="{{ $cell }}" colspan="2">{{ $t('মোট মাসিক বেতন (গ্রস)', 'Total Monthly Gross Salary') }}</td>
                <td style="{{ $cell }} text-align: right;">{{ $num($gross) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-bottom: 20px;">
        <strong>{{ $t('কথায়', 'In Words') }}:</strong> {{ $grossInWords }}
    </div>

    <div style="margin-bottom: 15px; text-align: justify;">
        {{ $t('আমাদের জানামতে তিনি একজন সৎ ও দায়িত্বশীল কর্মী। তার আবেদনের প্রেক্ষিতে এই প্রত্যয়নপত্র প্রদান করা হলো।',
              'To the best of our knowledge, he/she bears good moral character. This certificate is issued upon his/her request for whatever purpose it may serve.') }}
    </div>

    <div style="margin-bottom: 40px;">
        {{ $t('আমরা তার সর্বাঙ্গীণ মঙ্গল কামনা করি।', 'We wish him/her every success.') }}
    </div>

    <!-- Signatures -->
    <div style="margin-top: 70px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    --------------------------<br>
                    <strong>{{ $t('ব্যবস্থাপক (এইচআর ও এডমিন)', 'Manager (HR & Admin)') }}</strong><br>
                    {{ $companyName }}
                </td>
                <td style="width: 50%; text-align: right;">
                    --------------------------<br>
                    <strong>{{ $t('অনুমোদনকারী কর্তৃপক্ষ', 'Authorized Signature') }}</strong><br>
                    {{ $t('(সীলমোহর)', '(Seal)') }}
                </td>
            </tr>
        </table>
    </div>

</div>
