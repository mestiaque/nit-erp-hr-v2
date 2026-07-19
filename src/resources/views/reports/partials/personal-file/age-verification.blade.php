@php
    $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
    $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    $language = $language ?? data_get($request ?? null, 'language', 'en');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');
    $companyName = $employeeData['company_name'];
    $companyAddress = $employeeData['company_address'];
    $designation = $employeeData['designation'];
    $employeeName = $employeeData['employee_name'];
    $qualification = $employeeData['qualification'];
    $nomineeName = $isBangla ? $employeeData['nominee_name_bn'] : $employeeData['nominee_name'];
    $nomineeRelation = $isBangla ? $employeeData['nominee_relation_bn'] : $employeeData['nominee_relation'];
    $nomineeAge = $isBangla ? en2bnNumber($employeeData['nominee_age']) : $employeeData['nominee_age'];
    $nomineeVillage = $isBangla ? $employeeData['nominee_village_bn'] : $employeeData['nominee_village'];
    $nomineePoStation = $isBangla ? $employeeData['nominee_po_station_bn'] : $employeeData['nominee_po_station'];
    $nomineePostOffice = $isBangla ? $employeeData['nominee_post_office_bn'] : $employeeData['nominee_post_office'];
    $nomineeDistrict = $isBangla ? $employeeData['nominee_district_bn'] : $employeeData['nominee_district'];
    $nomineeNid = $isBangla ? en2bnNumber($employeeData['nominee_nid']) : $employeeData['nominee_nid'];
    $nomineeMobile = $isBangla ? en2bnNumber($employeeData['nominee_mobile']) : $employeeData['nominee_mobile'];
    $nationality = $isBangla ? 'বাংলাদেশী' : 'Bangladeshi';
    $permanentAddress = $isBangla ? $employeeData['permanent_address_bn'] : $employeeData['permanent_address'];
    $presentAddress = $isBangla ? $employeeData['present_address_bn'] : $employeeData['present_address'];
    $permanentAddressFull = $isBangla ? $employeeData['permanent_address_bn_full'] : $employeeData['permanent_address_full'];
    $presentAddressFull = $isBangla ? $employeeData['present_address_bn_full'] : $employeeData['present_address_full'];
    $birthDate = $isBangla ? bn_date($employeeData['birth_date']) : $employeeData['birth_date'];
    $employeeAge = $isBangla ? en2bnNumber($employeeData['employee_age']) : $employeeData['employee_age'];
    $employeePhoto = $employeeData['employee_photo'];
    $nomineeImage = $employeeData['nominee_image'];
    $fatherName = $employeeData['father_name'];
    $motherName = $employeeData['mother_name'];
    $joiningDate = $employeeData['joining_date'];
    $employeeId = $employeeData['employee_id'];
    $gender = $employeeData['gender'];
    $avInfo = $employee->otherInfo()['age_verification'] ?? [];
    $physicalAbility = $isBangla
        ? ($avInfo['physical_ability_bn'] ?? $avInfo['physical_ability'] ?? null)
        : ($avInfo['physical_ability'] ?? null);
    $identificationMark = $isBangla
        ? ($avInfo['identification_mark_bn'] ?? $avInfo['identification_mark'] ?? $employee->distinguished_mark)
        : ($avInfo['identification_mark'] ?? $employee->distinguished_mark);
@endphp

@if(ENV('FACTORY') == 'SFL')
    @include('hr::reports.partials.personal-file.age-verification-sfl', [
        'employee' => $employee,
        'language' => $language,
    ])
@else

    <div class="containerX">
        <div class="header">
            <h1>{{ $companyName }}</h1>
            <p>{{ $companyAddress }}</p>
        </div>

        <div class="form-title">
            <strong style="position: absolute; right: 2mm;">{{ $t('ফরম', 'Form') }} - {{ $isBangla ? en2bnNumber($employee->employee_id) : $employee->employee_id }}</strong>
            <h2>বয়স ও সক্ষমতার প্রত্যয়নপত্র</h2>
            <span>[ধারা ৩৪, ৩৬, ৩৭ ও ২৭৭ এবং বিধি- ৩৪(১) ও ৩৩৬(৪) দ্রষ্টব্য]</span>
        </div>

        <div class="main-content" style="min-height: 150mm">
            <!-- অফিস কপি / সংক্ষিপ্ত তথ্য -->
            <div class="left-panel">
                <div class="info-row"><label>ক্রমিক নং:</label><div class="fill-gap">{{ $isBangla ? en2bnNumber($employee->employee_id) : $employee->employee_id }}</div></div>
                <div class="info-row"><label>তারিখ:</label><div class="fill-gap">{{ $joiningDate }}</div></div>
                <div class="info-row"><label>নাম:</label><div class="fill-gap">{{ $employeeName }}</div></div>
                <div class="info-row"><label>পিতার নাম:</label><div class="fill-gap">{{ $fatherName }}</div></div>
                <div class="info-row"><label>মাতার নাম:</label><div class="fill-gap">{{ $motherName }}</div></div>
                <div class="info-row"><label>লিঙ্গ:</label><div class="fill-gap">{{ $gender }}</div></div>
                <div class="info-row"><label>স্থায়ী ঠিকানা:</label><div class="fill-gap">{{ $permanentAddressFull }}</div></div>
                <div class="info-row"><label>জন্ম তারিখ:</label><div class="fill-gap">{{ $birthDate }}</div></div>
                <div class="info-row"><label>শারীরিক সক্ষমতা:</label><div class="fill-gap">{{ $physicalAbility }}</div></div>
                <div class="info-row"><label>শনাক্তকরণ চিহ্ন:</label><div class="fill-gap">{{ $identificationMark }}</div></div>

                <div style="font-size: 9px; display: inline-flex; position: absolute; bottom: 0mm; ">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        সংশ্লিষ্ট ব্যক্তির স্বাক্ষর/টিপসই
                    </div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        রেজিস্টার্ড চিকিৎসকের স্বাক্ষর
                    </div>
                </div>
            </div>

            <!-- মূল সার্টিফিকেট অংশ -->
            <div class="right-panel">
                <div class="certify-text">
                    আমি এই মর্মে প্রত্যয়ন করিতেছি যে, <br>
                    <div class="info-row"><label>নাম:</label><div class="fill-gap">{{ $employeeName }}</div></div>
                    <div class="info-row"><label>পিতার নাম:</label><div class="fill-gap">{{ $fatherName }}</div></div>
                    <div class="info-row"><label>মাতার নাম:</label><div class="fill-gap">{{ $motherName }}</div></div>
                    <div class="info-row"><label>স্থায়ী ঠিকানা:</label><div class="fill-gap">{{ $permanentAddressFull }}</div></div>
                    কে আমি ব্যক্তিগতভাবে পরীক্ষা করিয়াছি। <br><br>
                    তিনি অত্র প্রতিষ্ঠানে নিযুক্ত হইতে ইচ্ছুক। আমার পরীক্ষা মতে তাহার বর্তমান বয়স <span class="dotted-line" style="min-width: 60px;">{{ $employeeAge }}</span> বছর এবং তিনি এই প্রতিষ্ঠানের <span class="dotted-line" style="min-width: 150px;">{{ $designation }}</span> কাজে প্রাপ্ত বয়স্ক হিসেবে নিযুক্ত হওয়ার যোগ্য। <br><br>
                    তাহার সনাক্তকরণের চিহ্ন: <span class="dotted-line" style="min-width: 180px;">{{ $identificationMark }}</span>
                </div>
                <div style="font-size: 9px; display: inline-flex; position: absolute; bottom: 0mm; ">
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        সংশ্লিষ্ট ব্যক্তির স্বাক্ষর/টিপসই
                    </div>
                    <div class="sig-box">
                        <div class="sig-line"></div>
                        রেজিস্টার্ড চিকিৎসকের স্বাক্ষর
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        .containerX {
            width: 210mm;
            min-height: 297mm;
            padding: 5mm 7mm !important;
            margin: auto;
            /* background: white; */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
        }
        body {
            font-family: 'Siyam Rupali', 'SolaimanLipi', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            /* background-color: #f4f4f4; */
        }
        .header {
            text-align: center;
            /* margin-bottom: 30px; */
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0; font-size: 14px; }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-title h2 { margin: 5px 0; font-size: 20px; }
        .form-title span { font-size: 13px; }

        .main-content {
            display: flex;
            gap: 0;
            position: relative;
        }
        .left-panel {
            flex: 0 0 50%;
            max-width: 50%;
            box-sizing: border-box;
            border-right: 1px dashed #999;
            padding-right: 20px;
        }
        .right-panel {
            flex: 0 0 50%;
            max-width: 50%;
            box-sizing: border-box;
            padding-left: 10px;
        }

        .info-row {
            margin-bottom: 12px;
            display: flex;
            align-items: baseline;
        }
        .info-row label {
            font-weight: bold;
            min-width: 120px;
            font-size: 14px;
        }
        .fill-gap {
            border-bottom: 1px dotted #000;
            flex-grow: 1;
            min-height: 18px;
        }

        .certify-text {
            line-height: 1.8;
            /* text-align: justify; */
            font-size: 13px;
        }
        .dotted-line {
            display: inline-block;
            border-bottom: 1px dotted #000;
            min-width: 100px;
            padding: 0 5px;
        }

        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            text-align: center;
            width: 200px;
        }


        @media print {
            body { background: none; padding: 0; }
            button { display: none; }
            .containerX { box-shadow: none; margin: 0; }

        }
    </style>

@endif

