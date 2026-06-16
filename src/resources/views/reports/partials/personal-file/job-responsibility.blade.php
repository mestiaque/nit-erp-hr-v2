@php
    $language = $language ?? data_get($request ?? null, 'language', 'en');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');

    $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    $companyName = $employeeData['company_name'];
    $companyAddress = $employeeData['company_address'];
    $designation = $employeeData['designation'];
    $employeeName = $employeeData['employee_name'];
    $joiningDate = $employeeData['joining_date'];
    $employeeId = $employeeData['employee_id'];
    $section = $employeeData['section'];
    $department = $employeeData['department'];
    $designationAttibute = $employeeData['designation_full'];

@endphp

<style>
.job-resp-header {
    text-align: center;
    margin-bottom: 8px;
}
.job-resp-title {
    font-weight: bold;
    text-decoration: underline;
    font-size: 17px;
}
.job-resp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    margin-top: 10px;
}
.job-resp-table th, .job-resp-table td {
    border: 1px solid #888 !important;
    padding: 6px 10px;
    vertical-align: top;
}
.job-resp-table th {
    background: #f7f7f7 !important;
    font-weight: 600;
    width: 180px;
}
.respons ol, .respons ul {
    padding-left: 15px;
}
</style>

<div class="job-resp-header">
    <h3 style="margin:0;">{{ $companyName }}</h3>
    <div>{{ $companyAddress }}</div>
    <div class="job-resp-title" style="margin-top:8px;">
        {{ $designation }} {{ $t('এর দায়িত্ব ও কর্তব্য', 'Job Responsibilities and Duties') }}
    </div>
    <div style="text-align:right; font-weight:600; margin-top:4px;">{{ $t('তারিখ', 'Date') }}: {{ $joiningDate }}</div>
</div>

<table class="job-resp-table">
    <tr>
        <th>{{ $t('নাম', 'Name') }}</th>
        <td>{{ $employeeName }}</td>
        <th>{{ $t('পদবী', 'Designation') }}</th>
        <td>{{ $designation }}</td>
        <th>{{ $t('আই.ডি নম্বর', 'ID No.') }}</th>
        <td>{{ $employeeId }}</td>
    </tr>
    <tr>
        <th>{{ $t('সেকশন', 'Section') }}</th>
        <td>{{ $section }}</td>
        <th colspan="2">{{ $t('যার অধীনে নিয়োজিত থাকবেন', 'Reporting Supervisor') }}</th>
        <td colspan="2">{{ $designationAttibute->report_to }}</td>
    </tr>
    <tr>
        <th colspan="1">{{ $t('দায়িত্বসমূহ', 'Responsibility') }}</th>
        <td colspan="5" class="respons">
            {!! $designationAttibute->responsibilities ?? $t('তথ্য নেই', 'No information available') !!}
        </td>
    </tr>
</table>
