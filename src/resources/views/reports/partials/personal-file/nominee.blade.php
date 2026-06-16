@if(!empty($nominee) || filled($employee->nominee) || filled($employee->nominee_relation) || filled($employee->nominee_age))
@php
    $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
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
    $birthDate = $isBangla ? bn_date($employeeData['birth_date']) : $employeeData['birth_date'];
    $employeeAge = $isBangla ? en2bnNumber($employeeData['employee_age']) : $employeeData['employee_age'];
    $employeePhoto = $employeeData['employee_photo'];
    $nomineeImage = $employeeData['nominee_image'];
    $fatherName = $employeeData['father_name'];
    $motherName = $employeeData['mother_name'];
    $joiningDate = $employeeData['joining_date'];
@endphp

<style>
  .nominee-sheet {
    font-family: SolaimanLipi, 'Noto Sans Bengali', Arial, sans-serif;
    color: #111;
    font-size: 14px;
    line-height: 1.35;
  }
  .nominee-sheet * {
    box-sizing: border-box;
  }
  .nominee-head {
    text-align: center;
    margin-bottom: 10px;
  }
  .nominee-company {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 3px;
  }
  .nominee-address {
    font-size: 13px;
    margin-bottom: 6px;
  }
  .nominee-form-no {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 2px;
  }
  .nominee-title {
    font-size: 16px;
    font-weight: 700;
    text-decoration: underline;
  }
  .nominee-top {
    display: table;
    width: 100%;
    margin-top: 14px;
  }
  .nominee-top-main,
  .nominee-top-photo {
    display: table-cell;
    vertical-align: top;
  }
  .nominee-top-main {
    width: calc(100% - 138px);
    padding-right: 12px;
  }
  .nominee-top-photo {
    width: 138px;
  }
  .photo-box {
    width: 122px;
    height: 144px;
    border: 1px solid #555;
    margin-left: auto;
    background: #f7f7f7;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .details-table,
  .nominee-table,
  .dist-table {
    width: 100%;
    border-collapse: collapse;
  }
  .details-table td {
    padding: 1px 2px;
    vertical-align: top;
    font-size: 13px;
    border: none !important;
  }
  /* .details-table .sl { width: 22px; } */
  .details-table .label { width: 150px; white-space: nowrap; }
  .details-table .colon { width: 10px; text-align: center; }
  .declaration {
    margin-top: 10px;
    text-align: justify;
    font-size: 13px;
  }
  .nominee-table {
    margin-top: 12px;
  }
  .nominee-table th,
  .nominee-table td {
    border: 1px solid #6d8c95 !important;
    padding: 4px 6px;
    vertical-align: top;
    font-size: 13px;
  }
  .nominee-table th { text-align: center; font-weight: 700; }
  .nominee-table .name-col { width: 42%; }
  .nominee-table .relation-col,
  .nominee-table .age-col { width: 12%; text-align: center; vertical-align: middle; }
  .nominee-table .share-col { width: 34%; padding: 0; }
  .dist-table th,
  .dist-table td {
    border: 1px solid #6d8c95 !important;
    padding: 3px 5px;
    font-size: 12px;
  }
  .dist-table .percent { width: 52px; text-align: center; }
  .note-text {
    margin-top: 12px;
    font-size: 13px;
  }
  .signatures {
    width: 100%;
    margin-top: 28px;
    border-collapse: collapse;
  }
  .signatures td {
    width: 50%;
    vertical-align: top;
    border: none !important;
    padding: 0;
    font-size: 13px;
  }
</style>

<div class="nominee-sheet">
  <div class="nominee-head">
    <div class="nominee-company">{{ $companyName }}</div>
    <div class="nominee-address">{{ $companyAddress }}</div>
    <div class="nominee-form-no">{{ $t('ফরম', 'Form') }}-{{ $isBangla ? en2bnNumber($employee->id) : $employee->id }}</div>
    <div class="nominee-title">{{ $t('মনোনয়ন ফরম', 'Nominee Declaration Form') }}</div>
  </div>

  <div class="nominee-top">
    <div class="nominee-top-main">
      <table class="details-table">
        <tr>
          <td class="sl"></td><td class="label">{{ $t('প্রতিষ্ঠানের নাম', 'Company Name') }}</td>
          <td class="colon">:</td><td colspan="4">{{ $companyName }}</td>
        </tr>
        <tr>
          <td class="sl"></td><td class="label">{{ $t('প্রতিষ্ঠানের ঠিকানা', 'Company Address') }}</td>
          <td class="colon">:</td><td colspan="4">{{ $companyAddress }}</td>
        </tr>
        <tr>
          <td class="sl"></td><td class="label">{{ $t('কর্মচারীর নাম', 'Employee Name') }}</td>
          <td class="colon">:</td><td>{{ $employeeName }}</td>
          <td class="label">{{ $t('আইডি', 'ID') }}</td>
          <td class="colon">:</td><td>{{ data_get($employee, 'employee_id', $na) }}</td>
        </tr>
        <tr>
          <td class="sl"></td><td class="label">{{ $t('পিতার নাম', 'Father Name') }}</td>
          <td class="colon">:</td><td>{{ $fatherName }}</td>
          <td class="label">{{ $t('শিক্ষাগত যোগ্যতা', 'Qualification') }}</td><td class="colon">:</td>
          <td>{{ $qualification }}</td>
        </tr>
        <tr>
          <td class="sl"></td>
          <td class="label">{{ $t('মাতার নাম', 'Mother Name') }}</td>
          <td class="colon">:</td><td>{{ $motherName }}</td>
          <td class="label">{{ $t('পদের নাম', 'Designation') }}</td>
          <td class="colon">:</td><td>{{ $designation }}</td>
        </tr>
        <tr>
            <td class="sl"></td>
            <td class="label">{{ $t('স্থায়ী ঠিকানা', 'Permanent Address') }}</td>
            <td class="colon">:</td><td>{{ $permanentAddress }}</td>
            <td class="label">{{ $t('জাতীয়তা', 'Nationality') }}</td>
            <td class="colon">:</td><td>{{ $nationality }}</td>
        </tr>
        <tr>
          <td class="sl"></td>
          <td class="label">{{ $t('বর্তমান ঠিকানা', 'Present Address') }}</td>
          <td class="colon">:</td><td>{{ $presentAddress }}</td>
          <td class="label">{{ $t('জন্ম তারিখ', 'Date of Birth') }}</td>
          <td class="colon">:</td><td>{{ $fmtDate($birthDate) }}</td>
        </tr>
        <tr>
          <td class="sl"></td>
          <td class="label">{{ $t('যোগদানের তারিখ', 'Joining Date') }}</td>
          <td class="colon">:</td><td>{{ $joiningDate }}</td>
          <td class="label">{{ $t('বয়স', 'Age') }}</td>
          <td class="colon">:</td><td>{{ $employeeAge ?: $na }}</td>
        </tr>
      </table>
    </div>

    <div class="nominee-top-photo">
      <div class="photo-box">
        @if(filled($nomineeImage))
          <img src="{{ asset($nomineeImage) }}" alt="{{ $t('মনোনীত ব্যক্তির ছবি', 'Nominee Image') }}">
        @endif
      </div>
    </div>
  </div>

  <div class="declaration">
    {{ $t('আমি ঘোষণা করছি যে, আমার মৃত্যু/অক্ষমতার ক্ষেত্রে আমার প্রাপ্য বেতন, প্রভিডেন্ট ফান্ড, বীমা, ক্ষতিপূরণ ও অন্যান্য পাওনা নিচে বর্ণিত মনোনীত ব্যক্তিকে প্রদান করা হবে।', 'I declare that in the event of my death or incapacity, my due wages, provident fund, insurance, compensation, and other payable amounts shall be paid to the nominee listed below.') }}
  </div>

  <table class="nominee-table">
    <tr>
      <th class="name-col">{{ $t('মনোনীত ব্যক্তির নাম, ঠিকানা ও এনআইডি', 'Nominee Name, Address and NID') }}</th>
      <th class="relation-col">{{ $t('সম্পর্ক', 'Relation') }}</th>
      <th class="age-col">{{ $t('বয়স', 'Age') }}</th>
      <th class="share-col">{{ $t('প্রাপ্য অংশ', 'Payable Share') }}</th>
    </tr>
    <tr>
      <td class="name-col">
        {{ $t('নাম', 'Name') }}: {{ $nomineeName }}<br>
        {{ $t('গ্রাম', 'Village') }}: {{ $nomineeVillage }}<br>
        {{ $t('ডাকঘর', 'Post Office') }}: {{ $nomineePostOffice }}<br>
        {{ $t('থানা', 'Police/PO Station') }}: {{ $nomineePoStation }}<br>
        {{ $t('জেলা', 'District') }}: {{ $nomineeDistrict }}<br>
        {{ $t('এনআইডি', 'NID') }}: {{ $nomineeNid }}<br>
        {{ $t('মোবাইল', 'Mobile') }}: {{ $nomineeMobile }}
      </td>
      <td class="relation-col">{{ $nomineeRelation }}</td>
      <td class="age-col">{{ $nomineeAge }} {{ filled($nomineeAge) ? $t('বছর', 'years') : '' }}</td>
      <td class="share-col">
        <table class="dist-table">
          <tr><th>{{ $t('খাত', 'Category') }}</th><th class="percent">{{ $t('অংশ', 'Share') }}</th></tr>
          <tr><td>{{ $t('অনাদায় মজুরি', 'Due Wages') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_net_payment', '0')) : data_get($nominee, 'distribution_net_payment', '0') }}%</td></tr>
          <tr><td>{{ $t('প্রভিডেন্ট ফান্ড', 'Provident Fund') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_provident_fund', '0')) : data_get($nominee, 'distribution_provident_fund', '0') }}%</td></tr>
          <tr><td>{{ $t('বীমা', 'Insurance') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_insurance', '0')) : data_get($nominee, 'distribution_insurance', '0') }}%</td></tr>
          <tr><td>{{ $t('দুর্ঘটনা ক্ষতিপূরণ', 'Accident Compensation') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_accident_fine', '0')) : data_get($nominee, 'distribution_accident_fine', '0') }}%</td></tr>
          <tr><td>{{ $t('লভ্যাংশ', 'Profit Share') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_profit', '0')) : data_get($nominee, 'distribution_profit', '0') }}%</td></tr>
          <tr><td>{{ $t('অন্যান্য', 'Others') }}</td><td class="percent">{{ $isBangla ? en2bnNumber(data_get($nominee, 'distribution_others', '0')) : data_get($nominee, 'distribution_others', '0') }}%</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="note-text">
    {{ $t('প্রত্যয়ন করা হলো যে, উপরোক্ত বিবরণ আমার জ্ঞানে সঠিক।', 'Certified that the above details are true to the best of my knowledge.') }}
  </div>

  <table class="signatures">
    <tr>
      <td><br>
        {{ $t('মনোনীত ব্যক্তি/অভিভাবকের স্বাক্ষর ও তারিখ', 'Nominee/Guardian Signature with Date') }}
      </td>
      <td style="text-align:right;">
        {{ $t('শ্রমিকের স্বাক্ষর/টিপসই ও তারিখ', 'Employee Signature/Thumb Impression with Date') }}<br><br><br>
        {{ $t('ম্যানেজার/অনুমোদিত কর্মকর্তার স্বাক্ষর ও তারিখ', 'Manager/Authorized Officer Signature with Date') }}
      </td>
    </tr>
  </table>
</div>
@endif
