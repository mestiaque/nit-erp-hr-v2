


@php
    $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');
    $companyName = $employeeData['company_name'];
    $companyAddress = $employeeData['company_address'];
    $employeeName = $employeeData['employee_name'];
    $employeeId = $employeeData['employee_id'] ?? $na;
    $designation = $employeeData['designation'] ?? $na;
    $section = $employeeData['section'] ?? $na;
    $increments = $employeeData['getIncrements']();
    // dd($increment);
    $factoryNo = hr_factory('factory_no');
    $increment = $increment ?? '';
    if($factoryNo == 1){
        $newSalary = $increment->new_salary_comp_1 ?? 0;
        $previousSalary = $increment->previous_salary_comp_1 ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
        $incrementPercent = $previousSalary > 0 ? round(($incrementAmount / $previousSalary) * 100, 2) : 0;
    }
    elseif($factoryNo == 2){
        $newSalary = $increment->new_salary_comp_2 ?? 0;
        $previousSalary = $increment->previous_salary_comp_2 ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
        $incrementPercent = $previousSalary > 0 ? round(($incrementAmount / $previousSalary) * 100, 2) : 0;
    }
    else{
        $newSalary = $increment->new_salary ?? 0;
        $previousSalary = $increment->previous_salary ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
        $incrementPercent = $previousSalary > 0 ? round(($incrementAmount / $previousSalary) * 100, 2) : 0;
    }

@endphp

<div style="font-family: 'Nikosh', 'Arial', sans-serif; max-width: 800px; margin: auto; color: #000; line-height: 1.6; padding: 20px;">

    <!-- Header -->
    <div style="text-align:center; margin-bottom:30px;">
        <h2 style="margin:0; text-transform: uppercase;">{{ $companyName }}</h2>
        <div style="font-size: 14px;">{{ $companyAddress }}</div>
        <div style="margin-top: 10px; font-weight: bold; text-decoration: underline;">
            {{ $t('বেতন বৃদ্ধির পত্র', 'Salary Increment Letter') }}
        </div>
    </div>

    <!-- Date and Ref -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td><strong>{{ $t('সূত্র নং', 'Ref No') }}:</strong> {{ $employeeId }}/INC/{{ date('Y') }}</td>
            <td style="text-align: right;"><strong>{{ $t('তারিখ', 'Date') }}:</strong> {{ $isBangla ? bn_date($increment  Date) : $incrementDate }}</td>
        </tr>
    </table>

    <!-- Employee Info -->
    <div style="margin-bottom: 20px;">
        {{ $t('নাম', 'Name') }}: <strong>{{ $employeeName }}</strong><br>
        {{ $t('আইডি নম্বর', 'ID No') }}: {{ $employeeId }}<br>
        {{ $t('পদবী', 'Designation') }}: {{ $designation }}<br>
        {{ $t('সেকশন/বিভাগ', 'Section/Dept') }}: {{ $section }}
    </div>

    <div style="margin-bottom: 20px; text-align: justify;">
        {{ $t('প্রিয় সহকর্মী,', 'Dear Colleague,') }}<br>
        {{ $t('আপনার গত এক বছরের নিষ্ঠা ও কঠোর পরিশ্রমের মূল্যায়ন স্বরূপ কর্তৃপক্ষ অত্যন্ত আনন্দের সাথে আপনার বেতন বৃদ্ধির বিষয়টি নিশ্চিত করছে। আপনার বর্তমান কর্মদক্ষতা প্রতিষ্ঠানের লক্ষ্য অর্জনে গুরুত্বপূর্ণ ভূমিকা পালন করেছে।',
           'Based on your performance and contribution to the company over the past year, we are pleased to inform you that your salary has been revised.') }}
    </div>

    <!-- Increment Table -->
    <div style="margin-bottom: 20px;">
        <p style="font-weight: bold; margin-bottom: 5px;">{{ $t('বেতন বৃদ্ধির বিস্তারিত বিবরণঃ', 'Revised Salary Details:') }}</p>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr>
                <td style="padding: 8px; border: 1px solid #000; width: 50%;">{{ $t('পূর্ববর্তী মোট বেতন (গ্রস)', 'Previous Gross Salary') }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $isBangla ? en2bnNumber($previousSalary) : $previousSalary }} /-</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000;">{{ $t('বেতন বৃদ্ধির পরিমাণ', 'Increment Amount') }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $isBangla ? en2bnNumber($incrementAmount) : $incrementAmount }} /- ({{ $isBangla ? en2bnNumber($incrementPercent) : $incrementPercent }}%)</td>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td style="padding: 8px; border: 1px solid #000;">{{ $t('বর্তমান মোট বেতন (গ্রস)', 'New Gross Salary') }}</td>
                <td style="padding: 8px; border: 1px solid #000;">{{ $isBangla ? en2bnNumber($newSalary) : $newSalary }} /-</td>
            </tr>
        </table>
    </div>

    <div style="margin-bottom: 20px; text-align: justify;">
          {{ $t('এই বর্ধিত বেতন', 'This increment is effective from') }} <strong>{{ $isBangla ? bn_date($incrementDate) : $incrementDate }}</strong>
          {{ $t('ইং তারিখ থেকে কার্যকর হবে। আমরা আশা করি, ভবিষ্যতে আপনি আরও নিষ্ঠার সাথে প্রতিষ্ঠানের সাফল্যে অবদান রাখবেন।',
              ' onwards. We look forward to your continued dedication and commitment to the company.') }}
    </div>

    <div style="margin-bottom: 40px;">
        {{ $t('আপনার ভবিষ্যৎ উত্তরোত্তর সাফল্য কামনা করছি।', 'We wish you every success in your future endeavors.') }}
    </div>

    <!-- Signatures -->
    <div style="margin-top: 60px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <br><br>
                    --------------------------<br>
                    <strong>{{ $t('ব্যবস্থাপক (এইচআর ও এডমিন)', 'Manager (HR & Admin)') }}</strong>
                </td>
                <td style="width: 50%; text-align: right;">
                    <br><br>
                    --------------------------<br>
                    <strong>{{ $t('অনুমোদনকারী কর্তৃপক্ষ', 'Authorized Signature') }}</strong>
                </td>
            </tr>
        </table>
    </div>

</div>
