@if(in_array($reportType, ['job-card', 'job-card-lock']))


@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
    $fmtTime = fn($t) => $t && $t!=='-' ? \Carbon\Carbon::parse($t)->format('h:i A') : '-';
    $fmtNumber = fn($n) => $isBangla ? en2bnNumber($n) : $n;
    $fmtDay = function($en, $bn = false) {
        $map = [
            'Saturday' => 'শনিবার', 'Sunday'=>'রবিবার', 'Monday'=>'সোমবার',
            'Tuesday'=>'মঙ্গলবার', 'Wednesday'=>'বুধবার', 'Thursday'=>'বৃহস্পতিবার', 'Friday'=>'শুক্রবার',
        ]; return $bn ? ($map[$en] ?? $en) : $en;
    };
@endphp

@foreach($employees as $employee)
    @php
        $factoryNo = hr_factory('factory_no');
        $attendancePack = \App\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
            $employee->id,
            $from,
            $to,
        );
        $attendance = $attendancePack['attendance'];
        $summary = $attendancePack['summary'];
        $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    @endphp

    <div class="report-head">
        <h3>{{ $employeeData['company_name'] }}</h3>
        <p>{{ $employeeData['company_address'] }}</p>
    </div>

    <div class="sub-title">
        {{ $t('জব কার্ড', 'Job Card') }}
        ({{ $t(bn_date($fromLabel), $fromLabel) }} {{ $t('থেকে', 'To') }} {{ $t(bn_date($toLabel), $toLabel) }})
    </div>

    <table class="info-grid">
        <tr>
            <td>{{ $t('কর্মী আইডি', 'Employee ID') }}</td><td>{{ $employee->employee_id }}</td>
            <td>{{ $t('বিভাগ', 'Department') }}</td><td>{{ $employeeData['department'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $t('নাম', 'Name') }}</td><td>{{ $employeeData['employee_name'] ?? '-' }}</td>
            <td>{{ $t('সেকশন', 'Section') }}</td><td>{{ $employeeData['section'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $t('শ্রেণীবিভাগ', 'Classification') }}</td><td>{{ $employeeData['job_type'] ?? '-' }}</td>
            <td>{{ $t('পদবী', 'Designation') }}</td><td>{{ $employeeData['designation'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>{{ $t('যোগদানের তারিখ', 'Join Date') }}</td>
            <td>
                {{ $isBangla ? bn_date($employee->joining_date) : ($employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d-M-y') : 'N/A') }}
            </td>
            <td></td><td></td>
        </tr>
    </table>

    <table class="t">
        <thead>
            <tr>
                <th>{{ $t('ক্রমিক', 'SL') }}</th>
                <th>{{ $t('তারিখ', 'Date') }}</th>
                <th>{{ $t('শিফট', 'Shift') }}</th>
                <th>{{ $t('বার', 'Day') }}</th>
                <th>{{ $t('প্রবেশের সময়', 'In Time') }}</th>
                <th>{{ $t('বাহিরের সময়', 'Out Time') }}</th>
                <th>{{ $t('ওটি ঘণ্টা', 'OT Hrs') }}</th>
                @if($factoryNo == 2)
                    <th>{{ $t('এক্সট্রা ওটি', 'Extra OT') }}</th>
                @endif
                <th>{{ $t('স্থিতি', 'Status') }}</th>
                <th>{{ $t('মন্তব্য', 'Remarks') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendance as $i => $row)
                <tr>
                    <td class="tc">{{ $fmtNumber($i+1) }}</td>
                    <td class="tc">{{ $isBangla ? bn_date($row['date']) : $row['date'] }}</td>
                    <td class="tc">{{ $row['shift'] ?? '-' }}</td>
                    <td class="tc">
                        {{ $isBangla ? $fmtDay($row['day'], true) : $row['day'] }}
                    </td>
                    <td class="tc">{{ $row['in_time'] && $row['in_time']!=='-' ? ($isBangla ? bn_time($row['in_time']) : $fmtTime($row['in_time'])) : '-' }}</td>
                    <td class="tc">{{ $row['out_time'] && $row['out_time']!=='-' ? ($isBangla ? bn_time($row['out_time']) : $fmtTime($row['out_time'])) : '-' }}</td>
                    <td class="tc">{{ $fmtNumber($row['compliance_ot']) }}</td>
                    @if($factoryNo == 2)
                        <td class="tc">{{ $fmtNumber($row['extra_ot']) }}</td>
                    @endif
                    <td class="tc">{{ att_status($row['status'], $isBangla ? 'bn' : 'en') }}</td>
                    <td class="tc">{{ $row['remarks'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><b>{{ $t('মোট ওটি ঘণ্টা', 'Total OT Hrs') }}: </b></td>
                <td class="text-center">
                    <b>
                        {{ $fmtNumber(array_sum(array_column($attendance, 'compliance_ot'))) }}
                    </b>
                </td>
                @if($factoryNo == 2)
                <td class="text-center">
                    <b>
                        {{ $fmtNumber(array_sum(array_column($attendance, 'extra_ot'))) }}
                    </b>
                </td>
                @endif
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Summary table here: -->
    <table class="info-grid">
        <tr>
            <td>{{ $t('মাসের মোট দিন', 'Total Days in Month') }}</td>
            <td>{{ $fmtNumber($summary['totalDays']) }}</td>
            <td>{{ $t('কার্যদিবস', 'Working Days') }}</td>
            <td>{{ $fmtNumber($summary['totalWorkingDays']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('সরকারি ছুটি', 'Govt. Holidays') }}</td>
            <td>{{ $fmtNumber($summary['totalGovHolidays']) }}</td>
            <td>{{ $t('সাপ্তাহিক ছুটি', 'Weekend Days') }}</td>
            <td>{{ $fmtNumber($summary['totalWeekendDays']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('অনুপস্থিত', 'Absent Days') }}</td>
            <td>{{ $fmtNumber($summary['totalAbsent']) }}</td>
            <td>{{ $t('ছুটি', 'Leave Days') }}</td>
            <td>{{ $fmtNumber($summary['totalLeave']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('উপস্থিত', 'Present Days') }}</td>
            <td>{{ $fmtNumber($summary['totalPresent']) }}</td>
            <td>{{ $t('মোট উপস্থিতি', 'Total Attendance') }}</td>
            <td>{{ $fmtNumber($summary['totalAttendance']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('বিলম্ব', 'Late') }}</td>
            <td>{{ $fmtNumber($summary['totalLate']) }}</td>
            <td>{{ $t('পাঞ্চ মিসিং', 'Punch Missing') }}</td>
            <td>{{ $fmtNumber($summary['totalPM']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('আগে বের হয়েছে', 'Early Out') }}</td>
            <td>{{ $fmtNumber($summary['totalEO']) }}</td>
            <td>{{ $t('বিলম্ব ও আগে বের', 'Late & Early Out') }}</td>
            <td>{{ $fmtNumber($summary['totalLEO']) }}</td>
        </tr>
        <tr>
            <td>{{ $t('বিলম্ব ও পাঞ্চ মিসিং', 'Late & Punch Missing') }}</td>
            <td>{{ $fmtNumber($summary['totalLPM']) }}</td>
            <td></td><td></td>
        </tr>
    </table>

    <div class="page-break"></div>
@endforeach

@endif
