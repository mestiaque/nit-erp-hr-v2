  @php
      $employeeDataFn = \ME\Hr\Services\HrOptionsService::getOptionsForEmployee();
      $ed = $employeeDataFn($employee, ['language' => 'bn'], $factory ?? null, $salaryKey ?? null);
      $familyNameSfl = $ed['father_name'] !== '--' ? $ed['father_name'] : ($ed['spouse_name'] !== '--' ? $ed['spouse_name'] : $ed['mother_name']);

      $dobSfl = $employee->dob;
      $dobDaySfl = $dobSfl ? en2bnNumber(\Carbon\Carbon::parse($dobSfl)->format('d')) : '';
      $dobMonthSfl = $dobSfl ? en2bnNumber(\Carbon\Carbon::parse($dobSfl)->format('m')) : '';
      $dobYearSfl = $dobSfl ? en2bnNumber(\Carbon\Carbon::parse($dobSfl)->format('Y')) : '';
      $identificationMarkSfl = optional($employee->ageVerification)->identification_mark_bn ?? optional($employee->ageVerification)->identification_mark ?? '';
      $employeeId = $employee->employee_id ?? '';
      $permVillageSfl = $employee->permanent_village_bn ?? $employee->permanent_village ?? '';
      $permPostOfficeSfl = $employee->permanent_post_office_bn ?? $employee->permanent_post_office ?? '';
      $permThanaSfl = $employee->permanent_upazila_bn ?? $employee->permanent_upazila ?? '';
      $permDistrictSfl = $employee->permanent_district_bn ?? $employee->permanent_district ?? '';

      $nomSfl = $employee->nomineeRecord;
      $nomineeNameSfl = optional($nomSfl)->bn_name ?? optional($nomSfl)->name ?? '';
      $nomineeRelationSfl = optional($nomSfl)->bn_relation ?? optional($nomSfl)->relation ?? '';
      $nomineeAgeSfl = optional($nomSfl)->age ? en2bnNumber((string) $nomSfl->age) : '';
      $nomineeAddressSfl = collect([
          optional($nomSfl)->bn_village ?? optional($nomSfl)->village,
          optional($nomSfl)->bn_post_office ?? optional($nomSfl)->post_office,
          optional(optional($nomSfl)->district)->bn_name ?? optional(optional($nomSfl)->district)->name,
      ])->filter(fn ($v) => filled($v))->implode(', ');
      $nomineeNidSfl = optional($nomSfl)->nid_no ?? '';
      $nomineePhotoSfl = optional($nomSfl)->photo;
      $pctSfl = fn ($v) => is_null($v) ? '' : en2bnNumber(rtrim(rtrim(number_format((float) $v, 2), '0'), '.')) . '%';
  @endphp
  <main class="document-page-wrapper">
    <article class="nomination-letter-card">

      <header class="form-header">
        <h1 class="form-title-main">ফরম-{{ en2bnNumber((string) $employeeId) }}</h1>
        <p class="law-reference-description">
          বাংলাদেশ শ্রম আইন,২০০৬ এর [ ধারা ১৯, ১৩১ (১) (ক) , ১৫৫ ( ২), ২৩৪, ২৬৪, ২৬৫ ও ২৭৩ এবং বিধি ১১৮ (১), ১৩৬, ২৩২ (২), ২৬২ (১) , ২৮৯ (১) ও ৩২১ (১)] অনুযায়ী জমা ও বিভিন্নখাতে প্রাপ্য অর্থ পরিশোধের ঘোষণা ও মনোনয়নের ফরম
        </p>
      </header>

      <table class="form-fields-table">
        <tr><td class="num-col">১.</td><td>কারখানা / প্রতিষ্ঠানের নাম : <span class=""><strong>{{ $ed['company_name'] }}</strong></span></td></tr>
        <tr><td class="num-col">২.</td><td>কারখানা / প্রতিষ্ঠানের ঠিকানাঃ<span class=""> <strong>{{ $ed['company_address'] }}</strong></span></td></tr>
        <tr><td class="num-col">৩.</td><td>শ্রমিকের নাম :<span class="bb-dot">{{ $ed['employee_name'] }}</span></td></tr>
        <tr><td class="num-col">৪.</td><td>ঠিকানাঃ<span class="bb-dot">{{ $ed['present_address_bn'] }}</span></td></tr>
        <tr><td></td><td>লিঙ্গঃ<span class="bb-dot">{{ $ed['gender'] }}</span></td></tr>
        <tr><td class="num-col">৫.</td><td>পিতা / মাতা/ স্বামী / স্ত্রীর নামঃ<span class="bb-dot">{{ $familyNameSfl }}</span></td></tr>
        <tr>
          <td class="num-col">৬.</td>
          <td>জন্ম তারিখঃদিন <span class="bb-dot">{{ $dobDaySfl }}</span> মাস <span class="bb-dot">{{ $dobMonthSfl }}</span> বছর <span class="bb-dot">{{ $dobYearSfl }}</span></td>
        </tr>
        <tr><td class="num-col">৭.</td><td>সনাক্তকরণ চিহ্ন (যদি থাকে)ঃ<span class="bb-dot">{{ $identificationMarkSfl }}</span></td></tr>
        <tr>
          <td class="num-col">৮.</td>
          <td>স্থায়ী ঠিকানাঃ গ্রামঃ<span class="bb-dot">{{ $permVillageSfl }}</span> , ডাকঘরঃ <span class="bb-dot">{{ $permPostOfficeSfl }}</span></td>
        </tr>
        <tr>
          <td></td>
          <td>থানাঃ<span class="bb-dot">{{ $permThanaSfl }}</span> , জেলাঃ <span class="bb-dot">{{ $permDistrictSfl }}</span></td>
        </tr>
        <tr><td class="num-col">৯.</td><td>চাকরিতে নিযুক্তি তারিখঃ<span class="bb-dot">{{ $ed['joining_date'] }}</span></td></tr>
        <tr><td class="num-col">১০.</td><td>পদের নামঃ<span class="bb-dot">{{ $ed['designation'] }}</span></td></tr>
      </table>

      <p class="declaration-paragraph">
        আমি এতদ্বারা ঘোষণা করিতেছি যে, আমার মৃত্যু হইলে বা আমার অবর্তমানে, আমার অনুকূলে জমা ও বিভিন্নখাতে প্রাপ্য টাকা গ্রহণের জন্য আমি নিম্নবর্ণিত ব্যক্তিকে / ব্যক্তিগণকে মনোনয়ন দান করিতেছি এবং নির্দেশ দিচ্ছি যে, উক্ত টাকা নিম্নবর্ণিত পদ্ধতিতে মনোনীত ব্যক্তিদের মধ্যে বণ্টন করিতে হইবেঃ
      </p>

      <table class="nomination-table">
        <thead>
          <tr>
            <th rowspan="2">মনোনীত ব্যক্তি বা ব্যক্তিদের নাম, ঠিকানা ও ছবি (নমিনির ছবি ও স্বাক্ষর শ্রমিক কর্তৃক সত্যায়িত) এন্ড আই ডি নং</th>
            <th rowspan="2">সদস্যদের সহিত মনোনীত ব্যক্তিদের সম্পর্ক</th>
            <th rowspan="2">বয়স</th>
            <th colspan="2">প্রত্যেক মনোনীত ব্যক্তিকে দেয় অংশ</th>
          </tr>
          <tr><th>জমাখাত</th><th>অংশ</th></tr>
          <tr><td>(১)</td><td>(২)</td><td>(৩)</td><td colspan="2">(৪)</td></tr>
        </thead>
        <tbody>
          <tr>
            <td rowspan="7" class="text-english">
              {{ $nomineeNameSfl }}<br>{{ $nomineeAddressSfl }}
              <br>
              <div class="nominee-photo-box" style="text-align:center;">
                @if($nomineePhotoSfl)
                  <img src="{{ asset($nomineePhotoSfl) }}" class="nominee-photo-img" alt="{{ $nomineeNameSfl }}"  onerror="this.remove()">
                  @else
                  ছবি
                @endif
              </div>
              @if($nomineeNidSfl)<br>{{ $nomineeNidSfl }}@endif
            </td>
            <td rowspan="7" class="text-english">{{ $nomineeRelationSfl }}</td>
            <td rowspan="7" class="text-english">{{ $nomineeAgeSfl }}</td>
            <td class="font-weight-bold">জমাখাত</td>
            <td class="font-weight-bold">অংশ</td>
          </tr>
          <tr><td>বকেয়া মজুরি</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->net_payment) }}</td></tr>
          <tr><td>প্রভিডেন্ট ফান্ড</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->provident_fund) }}</td></tr>
          <tr><td>বীমা</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->insurance) }}</td></tr>
          <tr><td>দুর্ঘটনার ক্ষতিপূরণ</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->accident_fine) }}</td></tr>
          <tr><td>লভ্যাংশ</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->profit) }}</td></tr>
          <tr><td>অন্যান্য</td><td class="text-english">{{ $pctSfl(optional($nomSfl)->others) }}</td></tr>
        </tbody>
      </table>

      <p class="witness-paragraph">
        প্রত্যয়ন করিতেছি যে, আমার উপস্থিতিতে জনাব
        <span class="dotted-fill-span" style="min-width:320px"></span>
        লিপিবদ্ধ বিবরণ সমূহ পাঠ করিবার পর উক্ত ঘোষণা স্বাক্ষর করিয়াছেন।
      </p>

      <table class="signature-row-table">
        <tr>
          <td>
            মনোনয়ন প্রদানকারী শ্রমিকের / কর্মচারীর স্বাক্ষর, টিপসহ ও তারিখঃ
            <span class="dotted-fill-span" style="min-width:200px"></span>
          </td>
        </tr>
      </table>

      <table class="bottom-signatures-table">
        <tr>
          <td class="text-start">
            <p class="bottom-sig-title">তারিখ সহ মনোনীত ব্যক্তিগণের স্বাক্ষর অথবা টিপসহ</p>
            <p class="bottom-sig-subtitle">(শ্রমিক কর্তৃক সত্যায়িত ছবি)</p>
          </td>
          <td class="text-center">
            <div class="handwritten-sig-placeholder"><span class="script-sig-text"></span></div>
            <div class="employer-dotted-sig-line">...........................................................</div>
            <p class="bottom-sig-title">মালিকের বা প্রাধিকারপ্রাপ্ত কর্মকর্তার স্বাক্ষর ও তারিখ</p>
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
          .document-page-wrapper {  display: flex; justify-content: center; }
          .nomination-letter-card { width: 210mm; margin: auto; background: #fff; box-sizing: border-box; font-size: 14px; color: #000; }

          .form-header { text-align: center; margin-bottom: 16px; }
          .form-title-main { font-size: 18px; font-weight: 700; margin: 0 0 8px; }
          .law-reference-description { font-size: 12px; margin: 0; }

          .form-fields-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
          .form-fields-table td { padding: 3px 6px; font-size: 14px;  vertical-align: bottom; }
          .num-col { width: 22px; font-weight: 600; vertical-align: top; border-bottom: none !important; }

          .declaration-paragraph, .witness-paragraph { font-size: 13px; line-height: 1.7; text-align: justify; margin: 0 0 14px; }

          .nomination-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
          .nomination-table th, .nomination-table td { border: 1px solid #000 !important; padding: 6px; font-size: 12.5px; text-align: center; }
          .nomination-table thead tr:first-child th { width: 25%; }
          .nominee-photo-box { display: inline-block; height: 23mm; width: 20mm; border: 1px solid #000; overflow: hidden; vertical-align: top; margin-top: 4mm; margin-bottom: 4mm; }
          .nominee-photo-img { width: 100%; height: 100%; object-fit: cover; display: block; }

          .dotted-fill-span { display: inline-block; border-bottom: 1px dotted #000; }

          .signature-row-table, .bottom-signatures-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
          .bottom-signatures-table td { width: 50%; padding: 0 10px; vertical-align: bottom; }
          .text-start { text-align: left; }
          .text-center { text-align: center; }
          .bottom-sig-title { font-size: 11.5px; margin: 0; }
          .bottom-sig-subtitle { font-size: 11px; margin: 2px 0 0; }
          .script-sig-text { font-family: 'Brush Script MT', cursive; font-size: 26px; }
          .employer-dotted-sig-line { font-size: 12px; margin: 4px 0; }
          .font-weight-bold { font-weight: 700; }
          .text-english { font-family: inherit; }
          .bb-dot { border-bottom: 1px dotted #000; display: inline-block; min-width: 100px; }

      </style>
  @endpush
