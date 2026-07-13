  @php
      $na = '';
      $companyName = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
      $companyAddress = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;

      $employeeName = data_get($employee, 'bn_name') ?? data_get($employee, 'name', $na);
      $employeeIdNo = data_get($employee, 'employee_id', $na);

      $designationModel = optional($employee->designation);
      $designationAttr = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
      $grade = $designationAttr->grade_bn ?? $designationModel->grade_bn ?? $designationAttr->grade ?? $designationModel->grade ?? data_get($employee, 'designation_grade') ?? $na;
      $designation = $designationModel->bn_name ?? data_get($designationAttr, 'bn_name') ?? $designationModel->name ?? data_get($designationAttr, 'name') ?? data_get($employee, 'designation_name') ?? $na;

      $departmentAttr = optional(\ME\Hr\Models\HrDepartment::find($employee->department_id));
      $department = data_get($departmentAttr, 'bn_name') ?? data_get($departmentAttr, 'name') ?? data_get($employee, 'department_bn_name') ?? data_get($employee, 'department_name') ?? $na;

      $joiningDate = blank($employee->joining_date) ? $na : bn_date($employee->joining_date, 'd/m/Y');


      $companyNameSfl = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
      $companyAddressSfl = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;

      $employeeNameSfl = data_get($employee, 'bn_name') ?? data_get($employee, 'name', $na);

      $designationModelSfl = optional($employee->designation);
      $designationAttrSfl = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
      $gradeSfl = $designationAttrSfl->grade_bn ?? $designationModelSfl->grade_bn ?? $designationAttrSfl->grade ?? $designationModelSfl->grade ?? data_get($employee, 'designation_grade') ?? $na;
      $designationSfl = $designationModelSfl->bn_name ?? data_get($designationAttrSfl, 'bn_name') ?? $designationModelSfl->name ?? data_get($designationAttrSfl, 'name') ?? data_get($employee, 'designation_name') ?? $na;

      $departmentAttrSfl = optional(\ME\Hr\Models\HrDepartment::find($employee->department_id));
      $departmentSfl = data_get($departmentAttrSfl, 'bn_name') ?? data_get($departmentAttrSfl, 'name') ?? data_get($employee, 'department_bn_name') ?? data_get($employee, 'department_name') ?? $na;

      $employeeIdSfl = data_get($employee, 'employee_id', $na);
      $joiningDateSfl = blank($employee->joining_date) ? $na : bn_date($employee->joining_date, 'd/m/Y');

      // Probation period: Classification.probation_period (months, 1-12) mapped to Bangla digit + word.
      $bnNumberWordsSfl = [
          1 => ['১', 'এক'], 2 => ['২', 'দুই'], 3 => ['৩', 'তিন'], 4 => ['৪', 'চার'],
          5 => ['৫', 'পাঁচ'], 6 => ['৬', 'ছয়'], 7 => ['৭', 'সাত'], 8 => ['৮', 'আট'],
          9 => ['৯', 'নয়'], 10 => ['১০', 'দশ'], 11 => ['১১', 'এগার'], 12 => ['১২', 'বার'],
      ];
      $probationMonthsSfl = (int) (optional($employee->classification)->probation_period ?: 3);
      $probationMonthsSfl = max(1, min(12, $probationMonthsSfl));
      $probationDigitSfl = $bnNumberWordsSfl[$probationMonthsSfl][0];
      $probationWordSfl = $bnNumberWordsSfl[$probationMonthsSfl][1];
      $probationTextSfl = $probationDigitSfl . ' (' . $probationWordSfl . ')';

      // Resolve salary breakdown via the same compliance-aware helper the non-SFL letter uses.
      $salSfl       = hr_employee_salary($employee, $factory ?? null, $salaryKey ?? null);
      $grossSfl     = en2bnNumber(number_format($salSfl['gross']));
      $basicSfl     = en2bnNumber(number_format($salSfl['basic']));
      $houseSfl     = en2bnNumber(number_format($salSfl['house']));
      $medicalSfl   = en2bnNumber(number_format($salSfl['medical']));
      $transportSfl = en2bnNumber(number_format($salSfl['transport']));
      $foodSfl      = en2bnNumber(number_format($salSfl['food']));
  @endphp
  <main class="document-page-wrapper">
    <article class="appointment-letter-card">
      
      <!-- Letter Header -->
      <header class="letter-header text-center" style="margin-bottom: 5mm; padding: 0mm 10mm;">
        <h1 class="company-name" style="color: black">{{ $companyNameSfl }}</h1>
        <p class="company-address">{{ $companyAddressSfl }}</p>
      </header>

      <div>
        <p style="margin-bottom:2mm">Ref No: SFL/HR/PP-EXT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ </p>
        <p>Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ </p>
      </div>
      <div class="document-title-wrapper" style="margin-top: 8px; margin-bottom:8px;text-align:center">
        <h2 class="document-title">শিক্ষানবীশকাল (Probation) মেয়াদ বৃদ্ধির পত্র</h2>
      </div>
      
      <!-- Employee Profile Details -->
      <section class="profile-details-section">
        <table class="profile-details-table" style="margin-bottom: 5px;">
          <tr class="profile-detail-item">
            <td class="detail-label" style="width:55mm">নাম -জনাব/জনাবা (Name)</td>
            <td class="detail-colon" style="width:5mm">:</td>
            <td class="detail-value text-english">{{ $employeeNameSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">শ্রমিকের শ্রেণি/ গ্রেড</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $gradeSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">আই ডি কার্ড নং (ID Card No)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $employeeIdSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">পদবী (Designation)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $designationSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">বিভাগ (Department)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $departmentSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">যোগদানের তারিখ (Joining date)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $joiningDateSfl }}</td>
          </tr>
        </table>
      </section>
      
      <!-- Opening Salutation -->
      <section class="salutation-section" style="margin-top: 5mm;">
        <p class="salutation-title">জনাব/ জনাবা</p>
        <p class="salutation-text" style="margin-top: 2mm;">
          আপনি <span class="bb-dot">{{ $joiningDate }}</span> তারিখে <span class="bb-dot">{{ $companyName }}</span>-এ <span class="bb-dot">{{ $designation }}</span> পদে যোগদান করেছেন এবং বর্তমানে আপনার শিক্ষানবীশকাল চলমান রয়েছে।
        </p>
        <p class="letter-body-paragraph" style="margin-top: 5mm;">
          আপনার কর্মদক্ষতা, দায়িত্ব পালন, উপস্থিতি, কাজের গুণগত মান, শৃঙ্খলা এবং সার্বিক কার্যক্রম মূল্যায়ন করে দেখা গেছে যে, স্থায়ী নিয়োগের বিষয়ে চূড়ান্ত সিদ্ধান্ত গ্রহণের পূর্বে আপনার কার্যক্রম আরও কিছু সময় পর্যবেক্ষণের প্রয়োজন রয়েছে। সেহেতু, প্রতিষ্ঠানের প্রচলিত নীতিমালা এবং প্রযোজ্য শ্রম আইন অনুসারে আপনার বর্তমান শিক্ষানবীশকাল মেয়াদ (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;) মাস বৃদ্ধি করা হলো। বর্ধিত শিক্ষানবীশকাল মেয়াদ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; তারিখ হতে &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; তারিখ পর্যন্ত কার্যকর থাকবে।
        </p>
        
        <p class="letter-body-paragraph" style="margin-top: 5mm;">
          এই সময়কালে আপনার কর্মদক্ষতা, দায়িত্বশীলতা, উপস্থিতি, শৃঙ্খলা ও আচরণ পুনরায় মূল্যায়ন করা হবে। মূল্যায়নের ফলাফল সন্তোষজনক হলে আপনাকে প্রতিষ্ঠানের নীতিমালা অনুযায়ী স্থায়ী নিয়োগের জন্য বিবেচনা করা হবে। শিক্ষানবীশকাল মেয়াদ বৃদ্ধিকালীন সময়ে আপনার বেতন ও অন্যান্য চাকরির শর্তাবলী পূর্বের ন্যায় বহাল থাকবে, যদি না প্রতিষ্ঠান লিখিতভাবে অন্য কোনো নির্দেশনা প্রদান করে। আমরা আশা করি আপনি এই অতিরিক্ত সময়কে কাজে লাগিয়ে আপনার দক্ষতা ও কর্মক্ষমতার আরও উন্নয়ন ঘটাবেন এবং প্রতিষ্ঠানের প্রত্যাশা পূরণে সচেষ্ট থাকবেন।
        </p>
      </section>
      
      
      
      <!-- Closing Remarks -->
      <section class="closing-section">
        <p class="closing-text font-weight-medium" style="margin-top: 5mm;">
          কর্তৃপক্ষ আশা করে, আপনার কর্মদক্ষতা, অভিজ্ঞতা এবং সেবার মাধ্যমে প্রতিষ্ঠান কর্তৃক আপনাকে দেওয়া পদবীতে যথাযথ সাফল্যের প্রতিফলন ঘটিবে।
        </p>
        
        <p class="closing-text declaration-text" style="margin-top: 5mm; margin-bottom: 10mm;">
          আমি নিয়োগপত্রের শর্তাদি পাঠ/শ্রবণ করিয়া, কারো দ্বারা প্ররোচিত বা প্রলুব্ধ না হইয়া এবং বর্ণিত শর্তাদি আমার নিকট গ্রহণ যোগ্য বিবেচিত হওয়ায় এবং মূল “নিয়োগ পত্র ” গ্রহণ করিয়া নিয়োগপত্রের “ডুপ্লিকেট কপি” তে স্বাক্ষর করিলাম।
        </p>
      </section>


        <div style="margin-top: 2px; width: 100%; display: flex; justify-content: space-between; padding:0mm 10mm;">
            <div>
                <div style="border:1px dashed #333; height: 11mm; width: 50mm; margin-bottom: 7px; margin-top:3mm"></div>
                <p class="signature-label" style="text-align: center;">শ্রমিক/ কর্মচারীর স্বাক্ষর</p>
            </div>
            <div>
                <p class="signature-label">{{ $employeeNameSfl }} এর পক্ষে</p>
                <div style="border-bottom:1px dashed #333; height: 15mm; margin-bottom: 2px;"></div>
                <p class="signature-name" style="text-align: center">কর্তৃপক্ষ</p>
            </div>
        </div>
      

      
      <!-- CC Distribution (Copies) -->
      <footer class="letter-footer">
        <h5 class="cc-header">অনুলিপি (CC):</h5>
        <ul class="cc-list">
          <li class="cc-list-item">১. কর্মচারীর ব্যক্তিগত নথি।</li>
          <li class="cc-list-item">২. এইচ আর (HR) বিভাগ।</li>
          <li class="cc-list-item">৩. সংশ্লিষ্ট বিভাগীয় প্রধান</li>
          <li class="cc-list-item">৪. হিসাব (Accounts) বিভাগ।</li>
        </ul>
      </footer>
      
    </article>
  </main>

@push('css')
<style>
    body{
        font-size: 13px;
    }
    .appointment-letter-card {
        /* background-color: rgb(230, 213, 247); */
        width: 210mm;
        /* height: 297mm; */
        box-shadow: var(--shadow-card);
        border-radius: 8px;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        margin: auto;
    }

    .appointment-letter-card .terms-section,
    .appointment-letter-card .contractual-terms-section {
        margin-bottom: 5px;
    }

    .appointment-letter-card .term-block-item,
    .appointment-letter-card .contract-block-item {
        display: flex;
        margin-bottom: 4px;
        font-size: 13px;
        /* line-height: 1.65; */
        text-align: justify;
    }

    .appointment-letter-card .term-bullet,
    .appointment-letter-card .contract-bullet {
        width: 28px;
        flex-shrink: 0;
        font-weight: 700;
    }

    .appointment-letter-card .term-body-content,
    .appointment-letter-card .contract-body-content {
        flex: 1;
    }

    .appointment-letter-card .term-highlight {
        font-weight: 700;
    }

    .appointment-letter-card .contractual-header-section {
        margin: 0px 0 5px;
    }

    .appointment-letter-card .contractual-header-title {
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid #333;
        display: inline-block;
        padding-bottom: 2px;
        margin: 0;
    }
    table td {
        padding: 1px 0px;
        font-size: 13px;
    }
    .wage-item-value, .wage-item-unit{
        text-align: right;
    }
</style>
@endpush
