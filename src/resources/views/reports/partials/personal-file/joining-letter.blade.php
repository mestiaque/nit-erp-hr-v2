


@php
    $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
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
    $grade = $isBangla ? en2bnNumber($employeeData['grade']) ?? $na : $employeeData['grade'] ?? $na;
    $joiningDate = $employeeData['joining_date'] ?? $na;
    $department = $employeeData['department'] ?? $na;
    $subSection = $employeeData['sub_section'] ?? $na;
    $fatherName = $employeeData['father_name'] ?? $na;
    $motherName = $employeeData['mother_name'] ?? $na;
    $spouseName = $employeeData['spouse_name'] ?? $na;
@endphp

    <style>
        @import url('https://googleapis.com');

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .letter-container {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
            box-sizing: border-box;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .date-section { margin-top: 10px; }

        .photo-box {
            width: 100px;
            height: 120px;
            border: 1px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            text-align: center;
        }

        .card-no-box {
            border: 1px solid #000;
            padding: 5px 15px;
            float: right;
            margin-top: 0px;
        }

        .title {
            text-align: center;
            text-decoration: underline;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            clear: both;
        }

        .address-section { margin-bottom: 20px; line-height: 1.6; }

        .subject { font-weight: bold; margin-bottom: 20px; }

        .content { line-height: 2; text-align: justify; }

        .input-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            padding: 0 10px;
            font-weight: 600;
        }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-area {
            border-top: 1px solid #000;
            padding-top: 5px;
            width: 200px;
            text-align: center;
            margin-top: 80px;
        }

        .authority-sign {
            float: right;
            text-align: right;
            margin-top: 150px;
        }

        @media print {
            body { background: none; padding: 0; }
            .letter-container { box-shadow: none; margin: 0; }
        }
    </style>

<div style="font-family: 'Nikosh', 'Arial', sans-serif; max-width: 800px; margin: auto; color: #000; line-height: 1.5;">


<div class="letter-container">
    <div class="header-top">
        <div class="date-section">তারিখঃ <span class="input-line">০৮/০৪/২০২৪</span></div>
    </div>


    <div class="title">যোগদান পত্র (Joining Letter)</div>
    <div class="card-no-box">কার্ড নং: <span class="input-line">{{ $employeeId }}</span></div>

    <div class="address-section">
        বরাবর,<br>
        {{ $companyName }}<br>
        {{ $companyAddress }}।
    </div>

    <div class="subject">বিষয়ঃ চাকুরীতে যোগদান প্রসঙ্গে।</div>

    <div class="content">
        জনাব,<br>
        আমি <span class="input-line">{{ $employeeName }}</span>,
        পিতাঃ <span class="input-line">{{ $fatherName }}</span>,
        মাতাঃ <span class="input-line">{{ $motherName }}</span>, <br>
        স্বামী/স্ত্রীঃ <span class="input-line">{{ $spouseName }}</span>
        <span class="input-line">{{ $joiningDate }}</span> ইং তারিখের নিয়োগ পত্রের প্রদত্ত নিয়মাবলী মেনে অত্র প্রতিষ্ঠানে অদ্য
        <span class="input-line">{{ $joiningDate }}</span> ইং তারিখে সকাল
        <span class="input-line">০৮:০০</span> ঘটিকার সময়
        <span class="input-line">{{ $designation }}</span> পদে
        <span class="input-line">{{ $department }}</span> সেকশনে যোগদান করিলাম।
        <br><br>
        আমি এই মর্মে অঙ্গীকার করিতেছি যে, আমি কাহারো দ্বারা প্ররোচিত বা প্রলুব্ধ না হয়ে স্বেচ্ছায় ও স্বজ্ঞানে নিয়োগ পত্রের সকল শর্ত মেনে ও বুঝে উক্ত পদে যোগদান করিলাম এবং আমি অত্র প্রতিষ্ঠানের সকল নিয়ম কানুন মানিয়া চলিব।
        <br><br>
        অতএব, মহোদয় অনুগ্রহ পূর্বক উক্ত পদের কাজে যোগদানের অনুমতি প্রদান করতে আপনার একান্ত মর্জি হয়।
    </div>

    <div class="footer">
        <div>
            <div class="signature-area">
                স্বাক্ষর: <span style="font-weight: bold;">{{ $employeeName }}</span>
            </div>
            <p style="margin-top: 10px;">আপনাকে কাজে যোগদানের অনুমতি প্রদান করা হল।</p>
        </div>

        <div class="authority-sign">
            <p style="border-top: 1px solid #000; width: 150px; text-align: center;">কর্তৃপক্ষের স্বাক্ষর</p>
        </div>
    </div>
</div>

</div>
