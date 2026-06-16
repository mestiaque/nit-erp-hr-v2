

<style>
.appraisal-header {
    text-align: center;
    margin-bottom: 8px;
}
.appraisal-title {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 2px;
}
.appraisal-table, .appraisal-table th, .appraisal-table td {
    border: 1px solid #888 !important;
    border-collapse: collapse;
}
.appraisal-table {
    width: 100%;
    font-size: 15px;
    margin-bottom: 10px;
}
.appraisal-table th, .appraisal-table td {
    padding: 6px 8px;
    text-align: left;
}
.appraisal-table th {
    background: #f7f7f7;
    font-weight: 600;
    text-align: center;
}
.appraisal-footer-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    margin-top: 18px;
}
.appraisal-footer-table th, .appraisal-footer-table td {
    border: 1px solid #888 !important;
    padding: 6px 8px;
    text-align: left;
}
</style>

@php
    $factoryNo = hr_factory('factory_no');
    $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');
    $companyName = $employeeData['company_name'];
    $companyAddress = $employeeData['company_address'];
    $employeeName = $employeeData['employee_name'];
    $section = $employeeData['section'] ?? $na;
    $cardNo = $employeeData['employee_id'] ?? $na;
    $joiningDate = $employeeData['joining_date'] ?? $na;
    $designation = $employeeData['designation'] ?? $na;
    $increment = $increment ?? '';
    if($factoryNo == 1){
        $newSalary = $increment->new_salary_comp_1 ?? 0;
        $previousSalary = $increment->previous_salary_comp_1 ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
    }
    elseif($factoryNo == 2){
        $newSalary = $increment->new_salary_comp_2 ?? 0;
        $previousSalary = $increment->previous_salary_comp_2 ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
    }
    else{
        $newSalary = $increment->new_salary ?? 0;
        $previousSalary = $increment->previous_salary ?? 0;
        $incrementAmount = $increment->increment_amount ?? 0;
        $effectiveDate = $increment->increment_date ?? 0;
    }
@endphp

<div class="appraisal-header">
    <h3 style="margin:0;">{{ $companyName }}</h3>
    <div>{{ $companyAddress }}</div>
    <div class="appraisal-title">{{ $t('পদোন্নতি ও বেতনবৃদ্ধি মূল্যায়ন ফরম', 'Promotion and Salary Increment Appraisal Form') }}</div>
</div>

<table style="width:100%; border:none; margin-bottom:2px;">
    <tr>
        <td style="border:none; font-weight:600;">{{ $t('নাম', 'Name') }}: {{ $employeeName }}</td>
        <td style="border:none; font-weight:600;">{{ $t('সেকশন', 'Section') }}: {{ $section }}</td>
        <td style="border:none; text-align:right; font-weight:600;">{{ $t('কার্ড নম্বর', 'Card No.') }}: {{ $cardNo }}</td>
        <td style="border:none; text-align:right; font-weight:600;">{{ $t('যোগদানের তারিখ', 'Joining Date') }}: {{ $joiningDate }}</td>
    </tr>
    <tr>
        <td style="border:none; font-weight:600;">{{ $t('পদবী', 'Designation') }}: {{ $designation }}</td>
        <td style="border:none;"></td>
        <td style="border:none;"></td>
        <td style="border:none;"></td>
    </tr>
</table>

<table class="appraisal-table">
    <tr>
        <th style="width:30%;">{{ $t('মূল্যায়নের বিষয়সমূহ', 'Evaluation Criteria') }}</th>
        <th style="width:14%;">{{ $t('উত্তম (১০)', 'Excellent (10)') }}</th>
        <th style="width:14%;">{{ $t('ভাল (৮)', 'Good (8)') }}</th>
        <th style="width:14%;">{{ $t('সন্তোষজনক (৬)', 'Satisfactory (6)') }}</th>
        <th style="width:14%;">{{ $t('মাঝারি (৪)', 'Average (4)') }}</th>
        <th style="width:14%;">{{ $t('খারাপ (২)', 'Poor (2)') }}</th>
    </tr>
    <tr><td>{{ $t('উপস্থিতি ও নিয়মিততা', 'Attendance and Regularity') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>{{ $t('কাজের জ্ঞান ও দক্ষতা', 'Job Knowledge and Skills') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>{{ $t('প্রতিষ্ঠানের নিয়ম-কানুন মেনে চলা', 'Compliance with Company Rules') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>{{ $t('দায়িত্ববোধ', 'Sense of Responsibility') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>{{ $t('উর্ধ্বতন ও সহকর্মীদের সাথে আচরণ', 'Behavior with Supervisors and Coworkers') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr><td>{{ $t('সততা ও নির্ভরযোগ্যতা', 'Integrity and Reliability') }}</td><td></td><td></td><td></td><td></td><td></td></tr>
    <tr>
        <td colspan="4" style="text-align:right;"><b>{{ $t('মোট নম্বর', 'Total Score') }}:</b></td>
        <td colspan="2"></td>
    </tr>
</table>

<div style="margin: 10px 0 10px 0; text-align:right; font-size:15px;">
    {{ $t('উপরোক্ত মূল্যায়নের ভিত্তিতে: □ পদোন্নতি □ বেতনবৃদ্ধি □ উভয়ই প্রযোজ্য', 'Based on this appraisal: □ Promotion □ Salary Increment □ Both are applicable') }}
    <hr style="margin:8px 0; border:0; border-top:1px dashed #888;">
</div>

<table class="appraisal-footer-table">
    <tr>
        <th style="width:20%;">{{ $t('এইচআর কর্মকর্তা', 'HR Officer') }}</th>
        <th style="width:20%;">{{ $t('পদবী', 'Designation') }}</th>
        <th style="width:20%;">{{ $t('পূর্বের বেতন', 'Previous Salary') }}</th>
        <th style="width:20%;">{{ $t('পরিবর্তিত বেতন', 'Revised Salary') }}</th>
        <th style="width:10%;">{{ $t('বৃদ্ধির পরিমাণ', 'Increment') }}</th>
        <th style="width:10%;">{{ $t('কার্যকর তারিখ', 'Effective Date') }}</th>
    </tr>
    <tr>
        <td>{{ $t('এইচ.আর এন্ড কমপ্লায়েন্স', 'HR and Compliance') }}</td>
        <td>{{ $designation }}</td>
        <td>{{ $isBangla ? en2bnNumber($previousSalary) : $previousSalary }}</td>
        <td>{{ $isBangla ? en2bnNumber($newSalary) : $newSalary }}</td>
        <td>{{ $isBangla ? en2bnNumber($incrementAmount) : $incrementAmount }}</td>
        <td>{{ $isBangla ? bn_date($effectiveDate) : $effectiveDate }}</td>
    </tr>
</table>

<div style="margin-top: 30px;">
    <div style="float:left; width:33%; text-align:center;">------------------------------<br>{{ $t('প্রস্তুতকারক (এইচআর)', 'Prepared By (HR)') }}</div>
    <div style="float:left; width:33%; text-align:center;">------------------------------<br>{{ $t('যাচাইকারী (প্রশাসন)', 'Verified By (Admin)') }}</div>
    <div style="float:left; width:33%; text-align:center;">------------------------------<br>{{ $t('অনুমোদনকারী', 'Approving Authority') }}</div>
    <div style="clear:both;"></div>
</div>
