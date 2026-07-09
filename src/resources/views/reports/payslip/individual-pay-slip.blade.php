@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
@endphp

@foreach($employees as $employee)
    @php
        $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
        $attendancePack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
            $employee->id,
            $from,
            $to
        );
        $summary = $attendancePack['summary'];
        $salary = hr_employee_salary($employee, $factory ?? null, $salaryKey ?? null);
        $leave = $attendancePack['leave'] ?? [];
        $totalDays = $summary['totalDays'] ?? 0;
        $present = $summary['totalPresentAll'] ?? 0;
        $absent = $summary['totalAbsent'] ?? 0;
        $casual = $leave['casual'] ?? 0;
        $sick = $leave['sick'] ?? 0;
        $earned = $leave['earned'] ?? 0;
        $weekly = $leave['weekly'] ?? 0;
        $festival = $leave['festival'] ?? 0;
        $general = $leave['general'] ?? 0;
        $maternity = $leave['maternity'] ?? 0;
        $otRate = $employeeData['salary']['ot_rate'] ?? 0;
        if(hr_factory('factory_no') == 2 || hr_factory('factory_no') == 1) {
            $otHour = $summary['totalComplianceOt'] ?? 0;
        }else{
            $otHour = $summary['totalOt'] ?? 0;
        }
        $earnDeductSummary = $employeeData['getEarningsDeductionsSummary']($from, $to);
        $salaryReport = $employeeData['getSalaryReport']($from, $to);
        $totalEarnings = $salaryReport['total_earn'] ?? 0;
        // total_deduct already includes advance_iou (see HrOptionsService::getSalaryReport) — it's
        // shown as its own "Advance" line below purely for a readable breakdown, not subtracted twice.
        $totalDeductions = $salaryReport['total_deduct'] ?? 0;
        $otAmount = $otHour*$otRate ?? 0;
        $totalSalary = $salary['gross'] ?? 0;
        $leaveDays = $summary['totalLeave'] ?? 0;
        $hasNoAbsentOrLeave = (int) $absent === 0 && (int) $leaveDays === 0;
        $attendanceBonusBase = (hr_factory('factory_no') == 1 || hr_factory('factory_no') == 2)
            ? ($salary['attendance_bonus_com'] ?? 0)
            : ($salary['attendance_bonus'] ?? 0);
        $attendanceBonus = $hasNoAbsentOrLeave ? (float) $attendanceBonusBase : 0;
        $carFuel = (float) ($salary['car_fuel'] ?? 0);
        $phoneInternet = (float) ($salary['phone_internet'] ?? 0);
        $extraFacility = (float) ($salary['extra_facility'] ?? 0);
        $extraFacilityTotal = $carFuel + $phoneInternet + $extraFacility;
        $deductAbsent = $summary['deductAbsent'] ?? 0;
        $advance = $earnDeductSummary['advanceIou'] ?? 0;
        $payable = $totalSalary + $attendanceBonus + $otAmount + $extraFacilityTotal;
        $netPay = $summary['netPay'] ?? 0;
    @endphp

<div class="payslip-container">
    <!-- Office Copy -->
    <div class="payslip-half">
        <div class="copy-type">অফিস কপি</div>
        <div class="header">
            <h2>{{ $employeeData['company_name'] ?? '' }}</h2>
            <p>{{ $employeeData['company_address'] ?? '' }}</p>
            <p>Month: {{ $monthLabel ?? '' }}</p>
        </div>

        <div class="section-info">
            <div>
                <strong>সেকশন: {{ $employeeData['section'] ?? '-' }}</strong><br>
                <strong>কার্ড নং: {{ $employee->employee_id }}</strong><br>
                <strong>নাম: {{ $employeeData['employee_name'] ?? $employee->name }}</strong>
            </div>
            <div style="text-align: right;">
                <strong>ব্লক নং - {{ $employeeData['line'] ?? '-' }}</strong><br>
                পদবী: {{ $employeeData['designation'] ?? '-' }}
            </div>
        </div>

        <table>
            <tr>
                <td class="label">মূল বেতন:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['basic'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;হাজিরা বোনাস </td>
                <td class="value">{{ en2bnNumber(number_format($attendanceBonus, 0)) }}</td>
                <td class="label right-align">মোট দিন:</td>
                <td class="value right-align">{{ en2bnNumber($totalDays) }}</td>
            </tr>
            <tr>
                <td class="label">বাড়ি ভাড়া:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['house'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;চিকিৎসা ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['medical'] ?? 0, 0)) }}</td>
                <td class="label right-align">হাজিরা (দিন):</td>
                <td class="value right-align">{{ en2bnNumber($present) }}</td>
            </tr>
            <tr>
                <td class="label">যাতায়াত ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['transport'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;খাদ্য ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['food'] ?? 0, 0)) }}</td>
                <td class="label right-align">অনুপস্থিত:</td>
                <td class="value right-align">{{ en2bnNumber($absent) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">মোট বেতন:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($totalSalary, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;ওটি রেট:</td>
                <td class="value">{{ en2bnNumber($otRate) }}</td>
                <td class="label right-align">নৈমিত্তিক ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($casual) }}</td>
            </tr>
            <tr>
                <td class="label">মোট ওটি টাকা:</td>
                <td class="value">{{ en2bnNumber(bn2enNumber(number_format($otAmount, 0))) }}</td>
                <td class="label">&nbsp;&nbsp;ওটি ঘন্টা:</td>
                <td class="value">{{ en2bnNumber($otHour) }}</td>
                <td class="label right-align">অসুস্থতা ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($sick) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">প্রাপ্য বেতন:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($payable, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;অন্যান্য:</td>
                <td class="value">{{ en2bnNumber(number_format($phoneInternet + $extraFacility + $carFuel, 0)) }}</td>
                <td class="label right-align">অর্জিত ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($earned) }}</td>
            </tr>
            <tr>
                <td class="label">অনুপ: কর্তন টাকা:</td>
                <td class="value">{{ en2bnNumber($totalDeductions) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাপ্তাহিক ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($weekly) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">অগ্রিম প্রদেয় টাকা:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($advance, 0)) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">উৎসব ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($festival) }}</td>
            </tr>
            <tr>
                <td class="label">মোট প্রদেয় টাকা:</td>
                <td class="value">{{ en2bnNumber(number_format($payable - $totalDeductions, 0)) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাধারণ ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($general) }}</td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td class="label right-align">মাতৃত্বকালীন ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($maternity) }}</td>
            </tr>
        </table>

        <div class="footer">
            **আপনার যে কোন অভিযোগ এবং পরামর্শ মানব সম্পদ<br>
            ও কমপ্লায়েন্স বিভাগকে অবহিত করুন।
        </div>
        <div class="signature">স্বাক্ষর</div>
    </div>

    <!-- Dashed Divider -->
    <div class="dashed-line"></div>

    <!-- Worker Copy -->
    <div class="payslip-half">
        <div class="copy-type">শ্রমিক কপি</div>
        <div class="header">
            <h2>{{ $employeeData['company_name'] ?? '' }}</h2>
            <p>{{ $employeeData['company_address'] ?? '' }}</p>
            <p>Month: {{ $monthLabel ?? '' }}</p>
        </div>

        <div class="section-info">
            <div>
                <strong>সেকশন: {{ $employeeData['section'] ?? '-' }}</strong><br>
                <strong>কার্ড নং: {{ $employee->employee_id }}</strong><br>
                <strong>নাম: {{ $employeeData['employee_name'] ?? $employee->name }}</strong>
            </div>
            <div style="text-align: right;">
                <strong>ব্লক নং - {{ $employeeData['line'] ?? '-' }}</strong><br>

                পদবী: {{ $employeeData['designation'] ?? '-' }}
            </div>
        </div>

        <table>
            <tr>
                <td class="label">মূল বেতন:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['basic'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;হাজিরা বোনাস </td>
                <td class="value">{{ en2bnNumber(number_format($attendanceBonus, 0)) }}</td>
                <td class="label right-align">মোট দিন:</td>
                <td class="value right-align">{{ en2bnNumber($totalDays) }}</td>
            </tr>
            <tr>
                <td class="label">বাড়ি ভাড়া:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['house'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;চিকিৎসা ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['medical'] ?? 0, 0)) }}</td>
                <td class="label right-align">হাজিরা (দিন):</td>
                <td class="value right-align">{{ en2bnNumber($present) }}</td>
            </tr>
            <tr>
                <td class="label">যাতায়াত ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['transport'] ?? 0, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;খাদ্য ভাতা:</td>
                <td class="value">{{ en2bnNumber(number_format($salary['food'] ?? 0, 0)) }}</td>
                <td class="label right-align">অনুপস্থিত:</td>
                <td class="value right-align">{{ en2bnNumber($absent) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">মোট বেতন:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($totalSalary, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;ওটি রেট:</td>
                <td class="value">{{ en2bnNumber($otRate) }}</td>
                <td class="label right-align">নৈমিত্তিক ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($casual) }}</td>
            </tr>
            <tr>
                <td class="label">মোট ওটি টাকা:</td>
                <td class="value">{{ en2bnNumber(bn2enNumber(number_format($otAmount, 0))) }}</td>
                <td class="label">&nbsp;&nbsp;ওটি ঘন্টা:</td>
                <td class="value">{{ en2bnNumber($otHour) }}</td>
                <td class="label right-align">অসুস্থতা ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($sick) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">প্রাপ্য বেতন:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($payable, 0)) }}</td>
                <td class="label">&nbsp;&nbsp;অন্যান্য:</td>
                <td class="value">{{ en2bnNumber(number_format($phoneInternet + $extraFacility + $carFuel, 0)) }}</td>
                <td class="label right-align">অর্জিত ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($earned) }}</td>
            </tr>
            <tr>
                <td class="label">অনুপ: কর্তন টাকা:</td>
                <td class="value">{{ en2bnNumber($totalDeductions) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাপ্তাহিক ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($weekly) }}</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid gray !important;">অগ্রিম প্রদেয় টাকা:</td>
                <td class="value" style="border-bottom:1px solid gray !important;">{{ en2bnNumber(number_format($advance, 0)) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">উৎসব ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($festival) }}</td>
            </tr>
            <tr>
                <td class="label">মোট প্রদেয় টাকা:</td>
                <td class="value">{{ en2bnNumber(number_format($payable - $totalDeductions, 0)) }}</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাধারণ ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($general) }}</td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td class="label right-align">মাতৃত্বকালীন ছুটি:</td>
                <td class="value right-align">{{ en2bnNumber($maternity) }}</td>
            </tr>
        </table>

        <div class="footer">
            **আপনার যে কোন অভিযোগ এবং পরামর্শ মানব সম্পদ<br>
            ও কমপ্লায়েন্স বিভাগকে অবহিত করুন।
        </div>
        <div class="signature">স্বাক্ষর</div>
    </div>
</div>
@endforeach

    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            /* padding: 0px; */
            /* background-color: #5e0808; */
        }

        .payslip-container {
            width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 8px 5px;
            border: 1px dashed #000;
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .payslip-half {
            width: 48%;
            position: relative;
        }

        .header {
            text-align: center;
            margin-bottom: 1px;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
        }

        .header p {
            margin: 0px 0;
            font-size: 9px;
        }

        .copy-type {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
            position: absolute;
            top: 0.2rem;
            right: 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .section-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #5250506c;
            padding-bottom: 1px;
            margin-bottom: 1px;
            margin-top: -0.3rem;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }

        td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8px !important;
            border: none !important;
        }

        .label {
            font-weight: bold;
            width: 90px;
        }

        .value {
            text-align: left;
            color: #000;
            font-weight: bold;
        }

        .right-align {
            text-align: right;
        }

        .footer {
            margin-top: 1px;
            font-size: 9px;
            /* font-weight: bold; */
        }

        .signature {
            margin-top: 20px;
            text-align: right;
            border-top: 1px solid #000;
            width: 80px;
            float: right;
        }

        .dashed-line {
            border-left: 1px dashed #000;
            height: auto;
        }
    </style>
