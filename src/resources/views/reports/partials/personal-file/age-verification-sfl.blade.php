  @php
      $na = '';
      $employeeIdSfl = data_get($employee, 'employee_id', $na);
      $employeeNameSfl = data_get($employee, 'bn_name') ?? data_get($employee, 'name', $na);
      $fatherNameSfl = $employee->father_name_bn ?? $employee->father_name ?? $na;
      $motherNameSfl = $employee->mother_name_bn ?? $employee->mother_name ?? $na;

      $ageVerificationSfl = $employee->ageVerification;
      $certDateSourceSfl = optional($ageVerificationSfl)->verified_date ?? $employee->joining_date;
      $certDateSfl = blank($certDateSourceSfl) ? $na : bn_date($certDateSourceSfl, 'd/m/Y');
      $physicalAbilitySfl = optional($ageVerificationSfl)->physical_ability_bn ?? optional($ageVerificationSfl)->physical_ability ?? '';
      $identificationMarkSfl = optional($ageVerificationSfl)->identification_mark_bn ?? optional($ageVerificationSfl)->identification_mark ?? '';

      $sexIdSfl = optional($employee->basicInfo)->sex_id;
      $sexNameSfl = $sexIdSfl ? optional(\ME\Hr\Models\HrSex::find($sexIdSfl))->name : null;
      $isFemaleSfl = $sexNameSfl ? stripos($sexNameSfl, 'female') !== false : false;

      $presentAddressSfl = collect([
          data_get($employee, 'present_address_bn'),
          data_get($employee, 'present_village_bn'),
          data_get($employee, 'present_post_office_bn'),
          data_get($employee, 'present_upazila_bn'),
          data_get($employee, 'present_district_bn'),
      ])->filter(fn ($v) => filled($v))->implode(', ');
      $presentAddressSfl = $presentAddressSfl !== '' ? $presentAddressSfl : data_get($employee, 'address', $na);
      $permanentAddressSfl = collect([
          data_get($employee, 'permanent_address_bn'),
          data_get($employee, 'permanent_village_bn'),
          data_get($employee, 'permanent_post_office_bn'),
          data_get($employee, 'permanent_upazila_bn'),
          data_get($employee, 'permanent_district_bn'),
      ])->filter(fn ($v) => filled($v))->implode(', ');
      $permanentAddressSfl = $permanentAddressSfl !== '' ? $permanentAddressSfl : data_get($employee, 'address', $na);

      $dobSfl = $employee->dob;
      $dobTextSfl = blank($dobSfl) ? $na : bn_date($dobSfl, 'd/m/Y');
      $ageYearsRawSfl = optional($ageVerificationSfl)->age_years ?? (blank($dobSfl) ? null : \Carbon\Carbon::parse($dobSfl)->age);
      $ageYearsSfl = is_null($ageYearsRawSfl) ? $na : en2bnNumber((string) $ageYearsRawSfl);
  @endphp
  <main class="document-page-wrapper">
    <article class="certificate-letter-card">

      <!-- Top Law Text and Photo Box -->
      <section class="certificate-top-section">
        <div class="row align-items-center">

          <!-- Column 1: Center Text (No custom class on col) -->
          <div class="col-12 col-md-9">
            <div class="law-text-container">
              <p class="law-reference-text">
                {ধারা ৩৪,৩৬,৩৭ ও ২৭৭ এবং বিধি ৩৪ (১) ও ৩৩৬ (৪) দ্রষ্টব্য বয়স ও সক্ষমতার প্রত্যয়ন পত্র}
              </p>
              <h1 class="hospital-pad-title">“রেজিস্টার্ড চিকিৎসকের প্যাড -এ”</h1>
            </div>
          </div>

          <!-- Column 2: Photo Block (No custom class on col) -->
          <div class="col-12 col-md-3">
            <div class="photo-box-outer">
              <div class="photo-box-border">
                <img src="{{ asset($employee->image()) }}" alt="{{ $employeeNameSfl }}" class="photo-box-img">
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- Two-Panel Main Layout (Bootstrap row with no custom classes on columns) -->
      <section class="certificate-panels-section">
        <div class="row g-0 certificate-border-grid">

          <!-- Column 1: Left Panel (Counterfoil) -->
          <div class="col-12 col-md-6">
            <div class="panel-left-wrapper">

              <!-- Panel Header -->
              <div class="panel-header-title text-center">
                <h2>বয়স ও সক্ষমতার প্রত্যয়ন পত্র</h2>
              </div>

              <!-- Panel Fields -->
              <div class="panel-fields-container">

                <!-- Row 1: Serial No -->
                <div class="panel-field-row">
                  <span class="field-label-text">১। ক্রমিক নং</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $employeeIdSfl }}</span>
                </div>

                <!-- Row 2: Date -->
                <div class="panel-field-row">
                  <span class="field-label-text">তারিখ</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $certDateSfl }}</span>
                </div>

                <!-- Row 3: Name -->
                <div class="panel-field-row">
                  <span class="field-label-text">২। নাম</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $employeeNameSfl }}</span>
                </div>

                <!-- Row 4: Father's Name -->
                <div class="panel-field-row">
                  <span class="field-label-text">৩। পিতার নাম</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $fatherNameSfl }}</span>
                </div>

                <!-- Row 5: Mother's Name -->
                <div class="panel-field-row">
                  <span class="field-label-text">৪। মাতার নাম</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $motherNameSfl }}</span>
                </div>

                <!-- Row 6: Gender Options -->
                <div class="panel-field-row gender-field-row">
                  <span class="field-label-text">৫। লিঙ্গ</span>
                  <div class="gender-selection-container">
                    <span class="gender-box-item {{ $isFemaleSfl ? '' : 'selected' }}">পুরুষ</span>
                    <span class="gender-box-item {{ $isFemaleSfl ? 'selected' : '' }}">মহিলা</span>
                  </div>
                </div>

                <!-- Row 7: Permanent Address -->
                <div class="panel-field-row address-field-row">
                  <div class="address-label-wrapper">
                    <span class="field-label-text">৬। স্থায়ী ঠিকানাঃ</span>
                    <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $permanentAddressSfl }}</span>
                  </div>
                </div>

                <!-- Row 8: Temporary Address -->
                <div class="panel-field-row address-field-row">
                  <div class="address-label-wrapper">
                    <span class="field-label-text">৭। অস্থায়ী যোগাযোগের ঠিকানাঃ</span>
                    <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $presentAddressSfl }}</span>
                  </div>
                </div>

                <!-- Row 9: Date of Birth / Age -->
                <div class="panel-field-row">
                  <span class="field-label-text">৮। জন্ম সনদ/শিক্ষা সনদ অনুসারে বয়স/জন্ম তারিখঃ</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $dobTextSfl }}</span>
                </div>

                <!-- Row 10: Physical Fitness -->
                <div class="panel-field-row">
                  <span class="field-label-text">৯। দৈহিক সক্ষমতা</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $physicalAbilitySfl }}</span>
                </div>

                <!-- Row 11: Identification Mark -->
                <div class="panel-field-row">
                  <span class="field-label-text">১০। সনাক্তকরণ/চিহ্ন</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $identificationMarkSfl }}</span>
                </div>

              </div>

              <!-- Signatures Row (Bootstrap grid with clean columns) -->
              <div class="panel-signatures-wrapper">
                <div class="row g-0 text-center">
                  <div class="col-6">
                    <div class="signature-block-inner border-end-divider">
                      <p class="sign-role-title">সংশ্লিষ্ট ব্যক্তির</p>
                      <p class="sign-role-subtitle">স্বাক্ষর/টিপসহ</p>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="signature-block-inner">
                      <p class="sign-role-title">রেজিস্টার্ড চিকিৎসকের</p>
                      <p class="sign-role-subtitle">স্বাক্ষর</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Column 2: Right Panel (Certificate text) -->
          <div class="col-12 col-md-6">
            <div class="panel-right-wrapper">

              <!-- Panel Header -->
              <div class="panel-header-title text-center">
                <h2>বয়স ও সক্ষমতার প্রত্যয়ন পত্র</h2>
              </div>

              <!-- Panel Fields -->
              <div class="panel-fields-container">

                <!-- Row 1: Serial No -->
                <div class="panel-field-row">
                  <span class="field-label-text">১। ক্রমিক নং</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $employeeIdSfl }}</span>
                </div>

                <!-- Row 2: Date -->
                <div class="panel-field-row">
                  <span class="field-label-text">তারিখ</span>
                  <span class="dotted-fill-span with-value text-english line-flex-grow">{{ $certDateSfl }}</span>
                </div>

                <!-- Right Side Main Declaration Text -->
                <div class="certificate-declaration-block">

                  <p class="declaration-sentence">
                    আমি এই মর্মে প্রত্যয়ন করিতেছি যে নাম
                    <span class="dotted-fill-span with-value text-english inline-fill-width-200">{{ $employeeNameSfl }}</span>
                  </p>

                  <p class="declaration-sentence mt-3">
                    <span class="dotted-fill-span inline-fill-width-150"></span> পিতা <span class="dotted-fill-span with-value text-english inline-fill-width-250">{{ $fatherNameSfl }}</span>
                  </p>

                  <p class="declaration-sentence mt-3">
                    মাতা <span class="dotted-fill-span with-value text-english inline-fill-width-220">{{ $motherNameSfl }}</span> ঠিকানা <span class="dotted-fill-span inline-fill-width-120"></span>
                  </p>

                  <p class="declaration-sentence mt-3">
                    <span class="dotted-fill-span with-value text-english inline-fill-width-350">{{ $permanentAddressSfl }}</span> কে আমি
                  </p>

                  <p class="declaration-sentence mt-3">
                    পরীক্ষা করিয়াছি।
                  </p>

                  <p class="declaration-sentence mt-4 justify-text-sentence">
                    তিনি প্রতিষ্ঠানে নিযুক্ত হইতে ইচ্ছুক, এবং আমার পরীক্ষা হইতে এইরূপ পাওয়া গিয়াছে যে তাহার বয়স <span class="dotted-fill-span with-value text-english inline-fill-width-100">{{ $ageYearsSfl }}</span> বৎসর এবং তিনি প্রতিষ্ঠানে প্রাপ্ত বয়স্ক হিসাবে নিযুক্ত হইবার যোগ্য।
                  </p>

                  <p class="declaration-sentence mt-4">
                    তাহার সনাক্তকরণের চিহ্ন <span class="dotted-fill-span with-value text-english inline-fill-width-280">{{ $identificationMarkSfl }}</span>
                  </p>

                </div>

              </div>

              <!-- Signatures Row (Bootstrap grid with clean columns) -->
              <div class="panel-signatures-wrapper">
                <div class="row g-0 text-center">
                  <div class="col-6">
                    <div class="signature-block-inner border-end-divider">
                      <p class="sign-role-title">সংশ্লিষ্ট ব্যক্তির</p>
                      <p class="sign-role-subtitle">স্বাক্ষর/টিপসহ</p>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="signature-block-inner">
                      <p class="sign-role-title">রেজিস্টার্ড চিকিৎসকের</p>
                      <p class="sign-role-subtitle">স্বাক্ষর</p>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>
      </section>

    </article>
  </main>

  @push('css')
      <style>
/* Scoped to .certificate-letter-card. This print layout does not load
   Bootstrap, so the handful of grid/utility classes the markup already
   uses (.row, .col-*, .text-center, .mt-*) are given minimal definitions
   here rather than duplicating full Bootstrap. */
.document-page-wrapper { padding: 30px 15px; display: flex; justify-content: center; }
.certificate-letter-card { max-width: 850px; width: 100%; margin: auto; background: #fff; border: 2px solid #000; padding: 18px 22px; box-sizing: border-box; font-size: 13px; color: #000; }

.certificate-letter-card .row { display: flex; flex-wrap: wrap; }
.certificate-letter-card .col-12 { width: 100%; box-sizing: border-box; }
.certificate-letter-card .col-6 { flex: 0 0 50%; max-width: 50%; box-sizing: border-box; }
.certificate-letter-card .col-md-9 { flex: 0 0 75%; max-width: 75%; box-sizing: border-box; }
.certificate-letter-card .col-md-6 { flex: 0 0 50%; max-width: 50%; box-sizing: border-box; }
.certificate-letter-card .col-md-3 { flex: 0 0 25%; max-width: 25%; box-sizing: border-box; }
.certificate-letter-card .align-items-center { align-items: center; }
.certificate-letter-card .text-center { text-align: center; }
.certificate-letter-card .mt-2 { margin-top: 0.5rem; }
.certificate-letter-card .mt-3 { margin-top: 1rem; }
.certificate-letter-card .mt-4 { margin-top: 1.5rem; }

/* Top law text + photo box */
.certificate-top-section { margin-bottom: 10px; }
.law-reference-text { text-align: center; font-size: 12px; margin: 0 0 4px; }
.hospital-pad-title { text-align: center; font-size: 13px; font-weight: 700; margin: 0; }
.photo-box-outer { display: flex; justify-content: flex-end; }
.photo-box-border { width: 90px; height: 80px; border: 1px solid #000; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.photo-box-img { width: 100%; height: 100%; object-fit: cover; }

/* Two-panel bordered grid */
.certificate-border-grid { border: 1px solid #000; }
.panel-left-wrapper, .panel-right-wrapper { display: flex; flex-direction: column; height: 100%; }
.panel-left-wrapper { border-right: 1px solid #000; }
.panel-header-title { border-bottom: 1px solid #000; padding: 5px 0; }
.panel-header-title h2 { font-size: 14px; font-weight: 700; margin: 0; }
.panel-fields-container { flex: 1; }

.panel-field-row { display: flex; align-items: baseline; gap: 6px; border-bottom: 1px solid #000; padding: 4px 8px; font-size: 12.5px; }
.field-label-text { flex-shrink: 0; }
.dotted-fill-span { border-bottom: 1px dotted #000; }
.line-flex-grow { flex: 1; }
.dotted-fill-block { display: block; width: 100%; height: 18px; border-bottom: 1px dotted #000; }

.address-field-row { display: block; }
.address-label-wrapper { display: flex; gap: 6px; }

.gender-field-row { align-items: center; }
.gender-selection-container { display: flex; gap: 10px; }
.gender-box-item { border: 1px solid #000; border-radius: 4px; padding: 1px 16px; }
.gender-box-item.selected { font-weight: 700; }
.gender-box-item { 
  border: 1px solid #000; 
  border-radius: 4px; 
  padding: 1px 16px; 
  display: inline-flex;       /* টিক চিহ্ন ও লেখা পাশাপাশি রাখার জন্য */
  align-items: center;        /* লম্বালম্বিভাবে মাঝে রাখার জন্য */
  gap: 6px;                   /* টিক চিহ্ন ও লেখার মাঝের দূরত্ব */
  cursor: pointer;
}

.gender-box-item.selected { 
  font-weight: 700; 
  border-color: #000;         /* সিলেক্টেড বর্ডারের রঙ (প্রয়োজনে পরিবর্তন করতে পারেন) */
}

/* টিক চিহ্ন যুক্ত করার ম্যাজিক কোড */
.gender-box-item.selected::before {
  content: "✓";               /* টিক চিহ্নের ইউনিকোড */
  font-weight: bold;          /* টিক চিহ্নটি মোটা দেখানোর জন্য */
  color: rgb(0, 0, 0);               /* টিক চিহ্নের রঙ (যেমন: সবুজ), আপনার ইচ্ছা মতো পরিবর্তন করুন */
}


.panel-signatures-wrapper { border-top: 1px solid #000; margin-top: auto; }
.signature-block-inner { padding: 10px 6px; }
.border-end-divider { border-right: 1px solid #000; }
.sign-role-title, .sign-role-subtitle { margin: 0; font-size: 12px; }

/* Right panel declaration paragraph */
.certificate-declaration-block { padding: 8px; }
.declaration-sentence { margin: 0 0 6px; line-height: 1.7; font-size: 12.5px; }
.justify-text-sentence { text-align: justify; }
.inline-fill-width-100 { display: inline-block; min-width: 100px; }
.inline-fill-width-120 { display: inline-block; min-width: 120px; }
.inline-fill-width-150 { display: inline-block; min-width: 150px; }
.inline-fill-width-200 { display: inline-block; min-width: 200px; }
.inline-fill-width-220 { display: inline-block; min-width: 220px; }
.inline-fill-width-250 { display: inline-block; min-width: 250px; }
.inline-fill-width-280 { display: inline-block; min-width: 280px; }
.inline-fill-width-350 { display: inline-block; min-width: 350px; }

@media print {
  .document-page-wrapper { padding: 0; }
  .certificate-letter-card { max-width: 100%; }
}
      </style>
  @endpush
