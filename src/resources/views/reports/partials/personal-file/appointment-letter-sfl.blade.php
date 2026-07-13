  @php
      $na = 'N/A';

      $companyNameSfl = hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na;
      $companyAddressSfl = hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na;

      $employeeNameSfl = data_get($employee, 'bn_name') ?? data_get($employee, 'name', $na);

      $designationModelSfl = optional($employee->designation);
      $designationAttrSfl = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
      $gradeSfl = $designationAttrSfl->grade_bn ?? $designationModelSfl->grade_bn ?? $designationAttrSfl->grade ?? $designationModelSfl->grade ?? data_get($employee, 'designation_grade') ?? $na;
      $designationSfl = $designationModelSfl->bn_name ?? data_get($designationAttrSfl, 'bn_name') ?? $designationModelSfl->name ?? data_get($designationAttrSfl, 'name') ?? data_get($employee, 'designation_name') ?? $na;

      $sectionAttrSfl = optional(\ME\Hr\Models\HrSection::find($employee->section_id));
      $sectionSfl = data_get($sectionAttrSfl, 'bn_name') ?? data_get($sectionAttrSfl, 'name') ?? data_get($employee, 'section_name') ?? $na;

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
      <header class="letter-header text-center">
        <h1 class="company-name" style="color: black">{{ $companyNameSfl }}</h1>
        <p class="company-address">{{ $companyAddressSfl }}</p>
        <div class="document-title-wrapper" style="margin-top: 8px; margin-bottom:8px;">
          <h2 class="document-title">নিয়োগ পত্র (Appointment Letter)</h2>
        </div>
      </header>
      
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
            <td class="detail-label">সেকশন (Section)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $sectionSfl }}</td>
          </tr>
          <tr class="profile-detail-item">
            <td class="detail-label">যোগদানের তারিখ (Joining date)</td>
            <td class="detail-colon">:</td>
            <td class="detail-value text-english">{{ $joiningDateSfl }}</td>
          </tr>
        </table>
      </section>
      
      <!-- Opening Salutation -->
      <section class="salutation-section">
        <p class="salutation-title">জনাব/ জনাবা</p>
        <p class="salutation-text">
          আপনার আবেদন ও কর্তৃপক্ষের সাথে সাক্ষাৎকারের ভিত্তিতে আপনাকে নিম্নলিখিত শর্তসাপেক্ষে নিয়োগ প্রদান করা হইল:
        </p>
      </section>
      
      <!-- Terms and Conditions Section -->
      <section class="terms-section">
        
        <!-- Term 1 -->
        <div class="term-block-item">
          <div class="term-bullet">১)</div>
          <div class="term-body-content">
            <span class="term-highlight">শিক্ষানবীশ কাল (Probationary period):</span>
            প্রাথমিকভাবে আপনার শিক্ষানবীশ সময় {{ $probationTextSfl }} মাস, কিন্তু শিক্ষানবীশকালে আপনার যোগ্যতা কর্তৃপক্ষের নিকট আশানুরূপ না হইলে আপনার শিক্ষানবীশ সময় পত্রের মাধ্যমে আরো ৩ মাস বৃদ্ধি করা হইবে। চাকুরীর দক্ষতা, বিচক্ষণতার নিরিখে চাকুরী স্থায়ীকরণ করা হইবে এবং চাকুরী স্থায়ীকরণ পত্র আলাদাভাবে প্রদান করা হইবে। যদি কর্তৃপক্ষ {{ $probationDigitSfl }} মাসের মধ্যে আপনাকে স্থায়ীকরণ পত্র দিতে ব্যর্থ হয়, সেক্ষেত্রে আপনি একজন স্থায়ী শ্রমিক বলে গণ্য হইবেন।
          </div>
        </div>
        
        <!-- Term 2 -->
        <div class="term-block-item">
          <div class="term-bullet">২)</div>
          <div class="term-body-content">
            <span class="term-highlight">বেতন ভাতাদি (Wages) আপনাকে নিম্নোক্ত বেতন ভাতাদি প্রদান করা হইবে :</span>
            
            <!-- Wages Table/Details -->
            <div class="wages-card-wrapper">
              <table class="wages-table" style="width:130mm; margin-left:10mm; margin-bottom:1mm;">
                <tr class="wage-detail-row">
                  <td class="wage-item-label" style="width: 85mm">ক) মূল বেতন (Basic Pay)</td>
                  <td class="wage-item-colon" style="width:5mm">:</td>
                  <td class="wage-item-value" style="width:25mm">{{ $basicSfl }}</td>
                  <td class="wage-item-unit" style="width:15mm">টাকা</td>
                </tr>
                <tr class="wage-detail-row">
                  <td class="wage-item-label">খ) বাড়ী ভাড়া (House Rent-50% of Basic pay)</td>
                  <td class="wage-item-colon">:</td>
                  <td class="wage-item-value">{{ $houseSfl }}</td>
                  <td class="wage-item-unit">টাকা</td>
                </tr>
                <tr class="wage-detail-row">
                  <td class="wage-item-label">গ) চিকিৎসা ভাতা (Medical Allowance)</td>
                  <td class="wage-item-colon">:</td>
                  <td class="wage-item-value">{{ $medicalSfl }}</td>
                  <td class="wage-item-unit">টাকা</td>
                </tr>
                <tr class="wage-detail-row">
                  <td class="wage-item-label">ঘ) যাতায়াত ভাতা (Conveyance Allowance)</td>
                  <td class="wage-item-colon">:</td>
                  <td class="wage-item-value">{{ $transportSfl }}</td>
                  <td class="wage-item-unit">টাকা</td>
                </tr>
                <tr class="wage-detail-row">
                  <td class="wage-item-label">ঙ) খাদ্য ভাতা (Food Allowance)</td>
                  <td class="wage-item-colon">:</td>
                  <td class="wage-item-value">{{ $foodSfl }}</td>
                  <td class="wage-item-unit">টাকা</td>
                </tr>
                <tr class="wage-detail-row wage-total-row" style="border-top: 1px dashed #333; font-weight: 700;">
                  <td class="wage-item-label">মোট বেতন / Total Salary</td>
                  <td class="wage-item-colon">:</td>
                  <td class="wage-item-value">{{ $grossSfl }}</td>
                  <td class="wage-item-unit">টাকা</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        
      </section>
      
      <!-- Contractual Agreement Header -->
      <section class="contractual-header-section">
        <h4 class="contractual-header-title">চাকুরীর শর্তাবলী (Contractual Agreement):</h4>
      </section>
      
      <!-- Contractual Terms List -->
      <section class="contractual-terms-section">
        
        <!-- Term 3 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৩)</div>
          <div class="contract-body-content">
            শুক্রবার সাপ্তাহিক ছুটি, নৈমিত্তিক ছুটি বছরে ১০ (দশ) দিন, অসুস্থতা ছুটি পূর্ণ বেতনে বছরে ১৪ দিন, উৎসব ছুটি কমপক্ষে বছরে ১৩ (তের) দিন, মাতৃত্বকল্যাণ ছুটি প্রসবের পূর্বে ৬০ (ষাট) দিন এবং প্রসবের পরে ৬০ (ষাট) দিন এবং ১৮ কর্মদিবসের জন্য ০১ দিন অর্জিত ছুটি যারা অবিচ্ছিন্নভাবে ০১ বৎসর চাকুরী পূর্ণ করেছেন।
          </div>
        </div>
        
        <!-- Term 4 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৪)</div>
          <div class="contract-body-content">
            যাহারা অবিচ্ছিন্নভাবে ০১ বৎসর চাকুরী পূর্ণ করেছেন তাহারা বাৎসরিক মূল মজুরী ৫% হারে বৃদ্ধি পাবেন।
          </div>
        </div>
        
        <!-- Term 5 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৫)</div>
          <div class="contract-body-content">
            আপনার চাকুরী বাংলাদেশ শ্রম আইন-২০০৬ মোতাবেক পরিচালিত হইবে।
          </div>
        </div>
        
        <!-- Term 6 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৬)</div>
          <div class="contract-body-content">
            অতিরিক্ত কাজের ভাতা প্রচলিত আইন অনুযায়ী প্রদান করা হইবে।
          </div>
        </div>
        
        <!-- Term 7 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৭)</div>
          <div class="contract-body-content">
            মাস শেষ হওয়ার ০৭ (সাত) কর্ম দিবসের মধ্যে বেতন ও ভাতা প্রদান করা হইবে।
          </div>
        </div>
        
        <!-- Term 8 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৮)</div>
          <div class="contract-body-content">
            কোন রুপ কারণ বা নোটিশ ব্যতীত কর্তৃপক্ষ শিক্ষানবীশ কালীন সময়ে আপনাকে অপসারণ করার অধিকার রাখে। এই ক্ষেত্রে আপনাকে কোন রকম ক্ষতিপূরণ প্রদান করা হইবে না।
          </div>
        </div>
        
        <!-- Term 9 -->
        <div class="contract-block-item">
          <div class="contract-bullet">৯)</div>
          <div class="contract-body-content">
            বাংলাদেশ শ্রম আইন-২০০৬ এর ২৬ ধারা মোতাবেক কোন কারণ ব্যাখ্যা করা ব্যতীত মাসিক মজুরীর ভিত্তিতে নিয়োজিত স্থায়ী শ্রমিকের ক্ষেত্রে ১২০ (একশত বিশ) দিনের এবং অন্য শ্রমিকের ক্ষেত্রে ৬০ (ষাট) দিনের লিখিত নোটিশ প্রদান করিয়া অথবা নোটিশের পরিবর্তে নোটিশ মেয়াদের জন্য মজুরী প্রদান করিয়া চাকুরীর অবসান করিতে পারিবেন।
          </div>
        </div>
        
        <!-- Term 10 -->
        <div class="contract-block-item">
          <div class="contract-bullet">১০)</div>
          <div class="contract-body-content">
            বাংলাদেশ শ্রম আইন-২০০৬ এর ২৭ ধারা মোতাবেক চাকুরী হইতে অব্যাহতি (Resign) দিতে হলে ৬০ (ষাট) দিনের লিখিত নোটিশ প্রদান করিতে হইবে নতুবা নোটিশের পরিবর্তে নোটিশ মেয়াদের জন্য মজুরীর সমপরিমান অর্থ কর্তৃপক্ষকে প্রদান করিয়া ইস্তফা দিতে পারিবেন। কর্তৃপক্ষ মহিলাদেরকে প্রসূতি কল্যাণ সুবিধা ও সন্তান প্রসবের সম্ভাব্য তারিখের অব্যবহিত পূর্ববর্তী ৬০ দিন এবং সন্তান প্রসবের অব্যবহিত পরবর্তী ৬০ দিনের জন্য ছুটি প্রদান করিবে।
          </div>
        </div>
        
        <!-- Term 11 -->
        <div class="contract-block-item">
          <div class="contract-bullet">১১)</div>
          <div class="contract-body-content">
            বাংলাদেশ শ্রম আইন-২০০৬ এর ২৩ (২) ধারা মোতাবেক যেমন: প্রতিষ্ঠানের প্রযোজ্য কোন আইন বা যুক্তি সংগত আদেশ মানার ক্ষেত্রে ইচ্ছাকৃতভাবে অবাধ্যতা, বিনা ছুটিতে অভ্যাসগত অনুপস্থিতি, কাজে কর্মে অভ্যাসগত গাফিলতি, কারখানার কোন সম্পত্তি চুরি এমন অসদাচরণের অপরাধে সাব্যস্ত হলে বরখাস্ত, নীচের পদে, গ্রেডে বা বেতন স্কেলে অনধিক এক বৎসর পর্যন্ত আনয়ন, অনধিক এক বছরের জন্য পদোন্নতি বন্ধ, অনধিক এক বছরের জন্য মজুরী বৃদ্ধি বন্ধ, জরিমানা, অনধিক সাত দিন পর্যন্ত বিনা মজুরীতে বা বিনা খোরাকীতে সাময়িক বরখাস্ত; এবং সতর্কীকরণ করিতে পারিবে।
          </div>
        </div>
        
      </section>
      
      <!-- Closing Remarks -->
      <section class="closing-section">
        <p class="closing-text font-weight-medium">
          কর্তৃপক্ষ আশা করে, আপনার কর্মদক্ষতা, অভিজ্ঞতা এবং সেবার মাধ্যমে প্রতিষ্ঠান কর্তৃক আপনাকে দেওয়া পদবীতে যথাযথ সাফল্যের প্রতিফলন ঘটিবে।
        </p>
        <p class="closing-text declaration-text">
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
        background-color: var(--color-white);
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