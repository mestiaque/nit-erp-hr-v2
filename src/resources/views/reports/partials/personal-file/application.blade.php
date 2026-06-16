
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
    $fatherName = $employeeData['father_name'] ?? $na;
    $motherName = $employeeData['mother_name'] ?? $na;
    $designation = $employeeData['designation'] ?? $na;
    $department = $employeeData['department'] ?? $na;
    $section = $employeeData['section'] ?? $na;
    $presentAddress = $employeeData['present_address'] ?? $na;
    $permanentAddress = $employeeData['permanent_address'] ?? $na;
    $applicationDate = $applicationDate ?? ($employeeData['joining_date'] ?? now()->format('d/m/Y'));
    $spouseName = $employeeData['spouse_name'] ?? $na;
    $education = $employeeData['education'] ?? $na;
    $gender = $employeeData['gender'] ?? $na;
    // Try to extract address parts if available
    $presentAddressFull = $isBangla ? ($employeeData['present_address_bn_full'] ?? $na) : ($employeeData['present_address_full'] ?? $na);
    $permanentAddressFull = $isBangla ? ($employeeData['permanent_address_bn_full'] ?? $na) : ($employeeData['permanent_address_full'] ?? $na);
    $birthDate = $employeeData['birth_date'] ?? $na;
    $employeeAge = $employeeData['employee_age'] ?? $na;
    $religion = $employeeData['religion'] ?? $na;
    $girls = $employeeData['girls'] ?? 0;
    $boys = $employeeData['boys'] ?? 0;
    $nid = $employeeData['nid'] ?? $na;
    $maritalStatus = $employeeData['marital_status'] ?? $na;
    $bloodGroup = $employeeData['blood_group'] ?? $na;
    $mobileNumber = $employeeData['mobile_number'] ?? $na;
    $emergencyMobile = $employeeData['emergency_mobile'] ?? $na;
    $employeePhoto = $employeeData['employee_photo'] ?? $na;
@endphp
    <style>
        @import url('https://googleapis.com');

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            font-size: 15px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm;
            margin: auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        .mailing-address {
            border: 1px solid #000;
            padding: 5px;
            width: fit-content;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .photo-area {
            float: right;
            width: 120px;
            height: 140px;
            border: 1px solid #000;
            margin-top: -40px;
        }

        .photo-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header { margin-top: 20px; line-height: 1.6; }

        .title {
            text-align: center;
            text-decoration: underline;
            font-size: 22px;
            font-weight: bold;
            margin: 25px 0;
        }

        .subject { font-weight: bold; margin-bottom: 15px; }

        .body-text { line-height: 1.8; text-align: justify; margin-bottom: 20px; }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px 0;
            vertical-align: top;
        }

        .label { width: 160px; font-weight: 600; }
        .colon { width: 20px; text-align: center; }
        .data { border-bottom: 1px dotted #888; }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .sig-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }

        @media print {
            body { background: none; padding: 0; }
            .page { box-shadow: none; margin: 0; }
        }
    </style>

<div style="font-family: 'Nikosh', 'Arial', sans-serif; max-width: 800px; margin: auto; color: #000; line-height: 1.6;">

<div class="page">
    <div class="photo-area">
        <!-- এখানে প্রার্থীর ছবি বসবে -->
        @if($employeePhoto !== $na)
            <img src="{{ asset($employeePhoto) }}" alt="Employee Photo">
        @else
            <div style="text-align: center; padding-top: 50px; color: #888;">ছবি নেই</div>
        @endif
    </div>

    <div class="header">
        বরাবর,<br>
        <strong>হেড অব এইচআর</strong><br>
        {{ $companyName }}<br>
        {{ $companyAddress }}
    </div>

    <div class="subject">বিষয়ঃ <span style="border-bottom: 1px solid #000;">{{$designation}}</span> পদে চাকুরীর জন্য আবেদন।</div>

    <div class="body-text">
        মহোদয়,<br>
        বিস্বস্ত সূত্রে জানিতে পারিলাম যে, আপনার সুনামধন্য শিল্প প্রতিষ্ঠানে <span style="font-weight: bold;">{{$designation}}</span> পদে লোক নিয়োগ করা হবে। আমি উক্ত পদের একজন প্রার্থী হিসেবে আবেদন করিতেছি। আমার সংক্ষিপ্ত জীবন বৃত্তান্ত আপনার সদয় বিবেচনার জন্য নিম্নে লিপিবদ্ধ করিলাম এবং প্রয়োজনীয় কাগজ পত্রাদি সংযুক্ত করিলামঃ
    </div>

    <table class="info-table">
        <tr>
            <td class="label">০১। নাম</td><td class="colon">ঃ</td><td class="data">{{ $employeeName }}</td>
        </tr>
        <tr>
            <td class="label">০২। পিতার নাম</td><td class="colon" >ঃ</td><td class="data">{{ $fatherName }}</td>
        </tr>
        <tr>
            <td class="label">০৩। মাতার নাম</td><td class="colon">ঃ</td><td class="data">{{ $motherName }}</td>
        </tr>
        <tr>
            <td class="label">০৪। স্বামী/স্ত্রীর নাম</td><td class="colon">ঃ</td><td class="data">{{ $spouseName }}</td>
        </tr>
        <tr>
            <td class="label">০৫। স্থায়ী ঠিকানা</td><td class="colon">ঃ</td>
            <td class="data">
               {{ $permanentAddressFull }}
            </td>
        </tr>
        <tr>
            <td class="label">০৬। বর্তমান ঠিকানা</td><td class="colon">ঃ</td>
            <td class="data">
                {{ $presentAddressFull }}
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 45%;">০৭। জন্ম তারিখ ঃ <span class="data">{{ $birthDate }}</span></div>
                    <div style="width: 45%;">০৮। বয়স ঃ <span class="data">{{ $t(en2bnNumber($employeeAge), $employeeAge ) }}</span></div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 45%;">০৯। শিক্ষাগত যোগ্যতা ঃ <span class="data">{{ $education }}</span></div>
                    <div style="width: 45%;">১০। লিঙ্গ ঃ <span class="data">{{ $gender }}</span></div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 45%;">১১। জাতীয়তা ঃ <span class="data">বাংলাদেশী (জন্ম সূত্রে)</span></div>
                    <div style="width: 45%;">১২। ধর্ম ঃ <span class="data">{{ $religion }}</span></div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">১৩। জাতীয় পরিচয়পত্রের নম্বর</td><td class="colon">ঃ</td><td class="data">{{ en2bnNumber($nid) }}</td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 45%;">১৪। বৈবাহিক অবস্থা ঃ <span class="data">{{ $maritalStatus }}</span></div>
                    <div style="width: 45%;">১৫। সন্তান সংখ্যা ঃ {{ en2bnNumber($girls + $boys) }} (ছেলে: {{ en2bnNumber($boys) }}, মেয়ে: {{ en2bnNumber($girls) }})</div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div style="display: flex; justify-content: space-between;">
                    <div style="width: 45%;">১৬। মোবাইল নম্বর ঃ <span class="data">{{ en2bnNumber($mobileNumber) }}</span></div>
                    <div style="width: 45%;">১৭। রক্তের গ্রুপ ঃ <span class="data">{{ $bloodGroup }}</span></div>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label">১৮। জরুরী মোবাইল নম্বর</td><td class="colon">ঃ</td><td class="data">{{ en2bnNumber($emergencyMobile) }}</td>
        </tr>
    </table>

    <div class="body-text">
        অতএব মহোদয়ের সমীপে বিনীত নিবেদন এই যে, আমার উপরোক্ত তথ্যাদী সম্পূর্ণ সত্য। উপরোক্ত তথ্যাদী বিবেচনা করিয়া আমাকে নিয়োগ দান করিলে আমি আপনার নিকট চির কৃতজ্ঞ থাকিব এবং আমার সর্বাত্মক চেষ্টা দ্বারা প্রতিষ্ঠানের সুনাম অক্ষুণ্ণ রাখিতে সচেষ্ট থাকিব।
    </div>

    <div class="footer">
        <div>তারিখঃ {{$applicationDate}} ইং</div>
        <div class="signature-box">
            <div class="sig-line">
                স্বাক্ষর ও প্রার্থীর নামঃ <br>
                <strong>{{ $employeeName }}</strong>
            </div>
        </div>
    </div>
</div>


</div>
