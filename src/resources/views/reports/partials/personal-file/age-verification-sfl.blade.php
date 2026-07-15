  @php
      $na = 'N/A';
      $employeeIdSfl = $employee->employee_id ?? $na;
      $employeeNameSfl = $employee->bn_name ?? $employee->name ?? $na;
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
          $employee->present_address_bn,
          $employee->present_village_bn,
          $employee->present_post_office_bn,
          $employee->present_upazila_bn,
          $employee->present_district_bn,
      ])->filter(fn ($v) => filled($v))->implode(', ');
      $presentAddressSfl = $presentAddressSfl !== '' ? $presentAddressSfl : $employee->address ?? $na;
      $permanentAddressSfl = collect([
          $employee->permanent_address_bn,
          $employee->permanent_village_bn,
          $employee->permanent_post_office_bn,
          $employee->permanent_upazila_bn,
          $employee->permanent_district_bn,
      ])->filter(fn ($v) => filled($v))->implode(', ');
      $permanentAddressSfl = $permanentAddressSfl !== '' ? $permanentAddressSfl : $employee->address ?? $na;

      $dobSfl = $employee->dob;
      $dobTextSfl = blank($dobSfl) ? $na : bn_date($dobSfl, 'd/m/Y');
      $ageYearsRawSfl = optional($ageVerificationSfl)->age_years ?? (blank($dobSfl) ? null : \Carbon\Carbon::parse($dobSfl)->age);
      $ageYearsSfl = is_null($ageYearsRawSfl) ? $na : en2bnNumber((string) $ageYearsRawSfl);
  @endphp
  <main class="document-page-wrapper">
    <article class="certificate-letter-card" style="padding: 5mm 5mm 5mm 5mm;">
      <div class="certificate-title" style="text-align: center; margin-bottom: 10px; position: relative;">
          <p class="law-reference-text" style=" font-size: 12px; font-weight: 400">
            [ধারা ৩৪,৩৬,৩৭ ও ২৭৭ এবং বিধি ৩৪ (১) ও ৩৩৬ (৪) দ্রষ্টব্য বয়স ও সক্ষমতার প্রত্যয়ন পত্র]
          </p>
          <h2 class="hospital-pad-title" style="margin: 5mm 0mm 8mm 0mm">“রেজিস্টার্ড চিকিৎসকের প্যাড -এ”</h2>
          <div class="photo-cell" style="position: absolute; top: 1px; right: 1px;">
            <img src="{{ asset($employee->image()) }}" alt="{{ $employeeNameSfl }}" class="photo-box-img">
          </div>
      </div>

      <!-- Two-Panel Main Layout -->
      <table class="certificate-main-table">
          <tr>
              <td colspan="2" style="text-align: center; font-weight: 700; font-size: 16px;">
                  বয়স ও সক্ষমতার প্রত্যয়ন পত্র
              </td>
              <td colspan="2" style="text-align: center; font-weight: 700; font-size: 16px;">
                  বয়স ও সক্ষমতার প্রত্যয়ন পত্র
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ১। ক্রমিক নং <span class="bb-dot">{{ $employeeIdSfl }}</span>
              </td>
              <td colspan="2">
                  ১। ক্রমিক নং <span class="bb-dot">{{ $employeeIdSfl }}</span>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  তারিখ <span class="bb-dot">{{ $certDateSfl }}</span>
              </td>
              <td colspan="2">
                  তারিখ <span class="bb-dot">{{ $certDateSfl }}</span>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ২। নাম <span class="bb-dot">{{ $employeeNameSfl }}</span>
              </td>
              <td colspan="2" rowspan="2">
                <div class="tw">
                    আমি এই মর্মে প্রত্যয়ন করিতেছি যে নাম
                    <span class="bb-dot" style="min-width:200px">{{ $employeeNameSfl }}</span>
                    <span class="dotted-fill-span" style="min-width:150px"></span> পিতা <span class="bb-dot" style="min-width:250px">{{ $fatherNameSfl }}</span>
                    মাতা <span class="bb-dot" style="min-width:220px">{{ $motherNameSfl ?? '........................' }}</span> ঠিকানা <span class="dotted-fill-span" style="min-width:120px"></span>
                    <span class="bb-dot" style="min-width:350px">{{ $permanentAddressSfl }}</span> কে আমি
                    পরীক্ষা করিয়াছি।
                </div>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ৩। পিতার নাম <span class="bb-dot">{{ $fatherNameSfl }}</span>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ৪। মাতার নাম <span class="bb-dot">{{ $motherNameSfl }}</span>
              </td>
              <td colspan="2">
                  তিনি প্রতিষ্ঠানে নিযুক্ত হইতে ইচ্ছুক, এবং আমার পরীক্ষা হইতে এইরূপ পাওয়া গিয়াছে যে তাহার বয়স <span class="bb-dot" style="min-width:100px">{{ $ageYearsSfl }}</span> বৎসর এবং তিনি প্রতিষ্ঠানে প্রাপ্ত বয়স্ক হিসাবে নিযুক্ত হইবার যোগ্য।
              </td>
          </tr>
          <tr>
              <td colspan="2">
                <div style="display: flex; align-items: center; gap: 15px;">
                  ৫। লিঙ্গ             
                    <div class="gender-selection-container">
                      <span class="gender-box-item {{ $isFemaleSfl ? '' : 'selected' }}">পুরুষ</span>
                      <span class="gender-box-item {{ $isFemaleSfl ? 'selected' : '' }}">মহিলা</span>
                    </div>
                </div>
              </td>
              <td colspan="2">
                  তাহার সনাক্তকরণের চিহ্ন <span class="bb-dot" style="min-width:280px">{{ $identificationMarkSfl }}</span>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ৬। স্থায়ী ঠিকানা <span class="bb-dot">{{ $permanentAddressSfl }}</span>
              </td>
              <td colspan="2" style="height: 20mm;">
                  
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ৭। অস্থায়ী যোগাযোগের ঠিকানা <span class="bb-dot">{{ $presentAddressSfl }}</span>
              </td>
              <td colspan="2" style="height: 20mm;">
                  <p></p>
              </td>
          </tr>
          <tr>
              <td colspan="2" >
                  ৮। জন্ম সনদ/শিক্ষা সনদ অনুসারে বয়স/জন্ম তারিখ <span class="bb-dot">{{ $ageYearsSfl }}</span>
              </td>
              <td colspan="2" style="height: 20mm;">
                  <p></p>
              </td>
          </tr>
          <tr>
              <td colspan="2">
                  ৯। দৈহিক সক্ষমতা <span class="bb-dot">{{ $ageYearsSfl }}</span>
              </td>
              <td colspan="2" style="height: 20mm;">
                  <p></p>
              </td>
          </tr>
          <tr>
              <td colspan="2" >
                 ১০। সনাক্তকরণ/চিহ্ন <span class="bb-dot">{{ $ageYearsSfl }}</span>
              </td>
              <td colspan="2" style="height: 20mm;">
                  <p></p>
              </td>
          </tr>
          <tr>
              <td style="border-right: none !important; padding-top: 15mm;">
                <div>
                  <p class="sign-role-title">সংশ্লিষ্ট ব্যক্তির</p>
                  <p class="sign-role-subtitle">স্বাক্ষর/টিপসহ</p>
                </div>
              </td>
              <td style="border-left: none !important;padding-top: 15mm;">
                <div style="text-align: center">
                  <p class="sign-role-title">রেজিস্টার্ড চিকিৎসকের</p>
                  <p class="sign-role-subtitle">স্বাক্ষর</p>
                </div>
              </td>
              <td style="border-right: none !important;padding-top: 15mm;">
                <div>
                  <p class="sign-role-title">সংশ্লিষ্ট ব্যক্তির</p>
                  <p class="sign-role-subtitle">স্বাক্ষর/টিপসহ</p>
                </div>
              </td>
              <td style="border-left: none !important;padding-top: 15mm;">
                <div style="text-align: center">
                  <p class="sign-role-title">রেজিস্টার্ড চিকিৎসকের</p>
                  <p class="sign-role-subtitle">স্বাক্ষর</p>
                </div>
              </td>
          </tr>
      </table>

    </article>
  </main>

  @push('css')
      <style>
            body{
        font-size: 13px;
    }
        .document-page-wrapper{
          width: 210mm;
          height: 297mm;
          margin: auto;
        }
        .certificate-main-table td{
          border: 1px solid black !important;
          width: 25% !important;
        }
        .photo-cell {
          width: 20mm;
          height: 23mm;
          text-align: center;
          border: 1px solid rgba(0, 0, 0, 0.671);
        }
        .photo-box-img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          border: 1px solid black;
        }

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
        .bb-dot {
          border-bottom: 1px dotted rgb(37, 37, 37) !important;
          padding: 0 2mm;
        }

      </style>
  @endpush
