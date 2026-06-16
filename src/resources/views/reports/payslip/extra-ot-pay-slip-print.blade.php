
@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
@endphp
{{-- Extra OT Pay Slip (per employee) --}}
@foreach($employees as $employee)
    @php
        $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
        $attendancePack = \App\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
            $employee->id,
            $from,
            $to
        );
        $summary = $attendancePack['summary'];
        $salary = hr_employee_salary($employee, $factory ?? null, $salaryKey ?? null);
        $extraOtHour = $summary['totalExtraOt'] ?? 0;
        $otRate = $salary['basic'] > 0 ? round(($salary['basic'] / 208) * 2, 2) : 0;
        $extraOtAmount = round($extraOtHour * $otRate, 2);
    @endphp

    <div class="containerX">

        <table class="main-table">
            <td class="main-table-td">
                <div class="copy-tag">অফিস কপি</div>
                <div class="header">
                    <h2>{{ $employeeData['company_name'] ?? '' }}</h2>
                    <p>{{ $employeeData['company_address'] ?? '' }}</p>
                    <p style="margin: 0; font-size: 11px;">অতিরিক্ত ওটি স্লিপ ({{ $monthLabel ?? '' }})</p>
                </div>

                <div class="section-info">
                    <div>
                        <strong>সেকশন: {{ $employeeData['section'] ?? '-' }}</strong><br>
                        <strong>কার্ড নং: {{ $employee->employee_id }}</strong><br>
                        <strong>নাম: {{ $employeeData['employee_name'] ?? $employee->name }}</strong>
                    </div>
                    <div style="text-align: right;">
                        <strong>ব্লক নং - {{ $employeeData['line'] ?? '-' }}</strong><br>
                        হাজিরা বোনাস {{ $salary['attendance_bonus'] ?? 0 }}<br>
                        পদবী: {{ $employeeData['designation'] ?? '-' }}
                    </div>
                </div>

                <table class="ot-table">
                    <tr>
                        <td>এক্সট্রা ওটি ঘণ্টা</td>
                        <td style="text-align: right;">{{ en2bnNumber($extraOtHour) }} ঘণ্টা</td>
                    </tr>
                    <tr>
                        <td>ওটি রেট (প্রতি ঘণ্টা)</td>
                        <td style="text-align: right;">{{ en2bnNumber($otRate) }} টাকা</td>
                    </tr>
                    <tr>
                        <td>মোট:</td>
                        <td style="text-align: right;">{{ en2bnNumber($extraOtAmount) }} টাকা</td>
                    </tr>
                </table>

                <div class="footer">
                    <div class="signature">সাক্ষর</div>
                </div>
            </td>
            <td class="main-table-td">
                <div class="copy-tag">শ্রমিক কপি</div>
                <div class="header">
                    <h2>{{ $employeeData['company_name'] ?? '' }}</h2>
                    <p>{{ $employeeData['company_address'] ?? '' }}</p>
                    <p style="margin: 0; font-size: 11px;">অতিরিক্ত ওটি স্লিপ ({{ $monthLabel ?? '' }})</p>
                </div>

                <div class="section-info">
                    <div>
                        <strong>সেকশন: {{ $employeeData['section'] ?? '-' }}</strong><br>
                        <strong>কার্ড নং: {{ $employee->employee_id }}</strong><br>
                        <strong>নাম: {{ $employeeData['employee_name'] ?? $employee->name }}</strong>
                    </div>
                    <div style="text-align: right;">
                        <strong>ব্লক নং - {{ $employeeData['line'] ?? '-' }}</strong><br>
                        হাজিরা বোনাস {{ $salary['attendance_bonus'] ?? 0 }}<br>
                        পদবী: {{ $employeeData['designation'] ?? '-' }}
                    </div>
                </div>

                <table class="ot-table">
                    <tr>
                        <td>এক্সট্রা ওটি ঘণ্টা</td>
                        <td style="text-align: right;">{{ en2bnNumber($extraOtHour) }} ঘণ্টা</td>
                    </tr>
                    <tr>
                        <td>ওটি রেট (প্রতি ঘণ্টা)</td>
                        <td style="text-align: right;">{{ en2bnNumber($otRate) }} টাকা</td>
                    </tr>
                    <tr>
                        <td>মোট:</td>
                        <td style="text-align: right;">{{ en2bnNumber($extraOtAmount) }} টাকা</td>
                    </tr>
                </table>
                <div class="footer">
                    <div class="signature">সাক্ষর</div>
                </div>
            </td>
        </table>

    </div>
@endforeach
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            font-size: 10px
        }

        .containerX {
            width: 800px;
            margin: 0 auto;
            margin: 0 auto;
        }

        .slip-half {
            width: 45%;
            position: relative;
        }

        .header {
            text-align: center;
            /* border-bottom: 2px solid #000; */
            margin-bottom: 0px;
            padding-bottom: 0px;
        }
        .header p{
            margin: 0;
            font-size: 10px;
        }

        .header h2 { margin: 0; font-size: 14px; }
        .copy-tag { text-align: right; font-weight: bold; font-size: 12px; margin-bottom: 5px; position: absolute; right: 0.5rem; top: 0.5rem; }

        .section-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .ot-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .ot-table td {
            padding: 1px;
            border: 1px solid #ddd;
            font-size: 9px;
            color: #000 !important;
        }

        .total-box {
            background-color: #f9f9f9;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 30px;
            /* display: flex; */
            justify-content: space-between;
        }

        .signature {
            border-top: 1px solid #000;
            width: 100px;
            text-align: center;
            font-size: 12px;
            float: right;
        }
        .main-table{
            margin-bottom: 0px;
        }
        .main-table-td{
            border-bottom: none;
            border: 2px dashed rgb(0, 0, 0) !important;
            position: relative;
        }
    </style>
