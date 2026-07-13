

@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
    $na = $t('প্রযোজ্য নয়', 'N/A');

    $companyName = $isBangla
        ? (hr_factory('bn_name') ?? hr_factory('name') ?? general()->name ?? $na)
        : (hr_factory('name') ?? general()->name ?? hr_factory('bn_name') ?? $na);
    $companyAddress = $isBangla
        ? (hr_factory('bn_address') ?? hr_factory('address') ?? general()->address ?? $na)
        : (hr_factory('address') ?? general()->address ?? hr_factory('bn_address') ?? $na);
    $employeeName = $isBangla
        ? (data_get($employee, 'bn_name') ?? data_get($employee, 'name') ?? $na)
        : (data_get($employee, 'name') ?? data_get($employee, 'bn_name') ?? $na);
    $fatherName = $isBangla ? data_get($employee, 'father_name_bn', $na) : data_get($employee, 'father_name', $na);
    $motherName = $isBangla ? data_get($employee, 'mother_name_bn', $na) : data_get($employee, 'mother_name', $na);
    $spouseName = $isBangla ? data_get($employee, 'spouse_name_bn', $na) : data_get($employee, 'spouse_name', $na);
    $joiningDate = blank($employee->joining_date) ? $na : bn_date($employee->joining_date, 'd/m/Y');

    $designationModel = optional($employee->designation);
    $designationAttr = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
    $grade = $designationAttr->grade ?? $designationModel->grade ?? data_get($employee, 'designation_grade') ?? $na;
    $designation = $isBangla
        ? ($designationModel->bn_name ?? data_get($designationAttr, 'bn_name') ?? $designationModel->name ?? data_get($designationAttr, 'name') ?? data_get($employee, 'designation_bn_name') ?? data_get($employee, 'designation_name') ?? $na)
        : ($designationModel->name ?? data_get($designationAttr, 'name') ?? data_get($employee, 'designation_name') ?? $designationModel->bn_name ?? data_get($designationAttr, 'bn_name') ?? data_get($employee, 'designation_bn_name') ?? $na);
    $departmentAttr = optional(\ME\Hr\Models\HrDepartment::find($employee->department_id));
    $department = $isBangla
        ? (data_get($departmentAttr, 'bn_name') ?? data_get($departmentAttr, 'name') ?? data_get($employee, 'department_bn_name') ?? data_get($employee, 'department_name') ?? $na)
        : (data_get($departmentAttr, 'name') ?? data_get($employee, 'department_name') ?? data_get($departmentAttr, 'bn_name') ?? data_get($employee, 'department_bn_name') ?? $na);
    $sectionAttr = optional(\ME\Hr\Models\HrSection::find($employee->section_id));
    $section = $isBangla
        ? (data_get($sectionAttr, 'bn_name') ?? data_get($sectionAttr, 'name') ?? data_get($employee, 'section_bn_name') ?? data_get($employee, 'section_name') ?? $na)
        : (data_get($sectionAttr, 'name') ?? data_get($employee, 'section_name') ?? data_get($sectionAttr, 'bn_name') ?? data_get($employee, 'section_bn_name') ?? $na);

    $masterData = \ME\Hr\Services\HrOptionsService::getOptions();
    $employeeOthers = $employee->otherInfo();

    $jobType = $isBangla
        ? $masterData['classifications']->where('id', $employee->employee_type)->first()?->bn_name
        : $masterData['classifications']->where('id', $employee->employee_type)->first()?->name;
    $employeeId = data_get($employee, 'employee_id', $na);

    $present_district = $employee->present_district;
    $present_upazila = $employee->present_upazila;


    $presentAddress = collect([
        data_get($employee, 'present_address_bn'),
        data_get($employee, 'present_village_bn'),
        data_get($employee, 'present_post_office_bn'),
        data_get($employee, 'present_upazila_bn'),
        data_get($employee, 'present_district_bn'),
    ])->filter(fn ($v) => filled($v))->implode(', ');
    $permanentAddress = collect([
        data_get($employee, 'permanent_address_bn'),
        data_get($employee, 'permanent_village_bn'),
        data_get($employee, 'permanent_post_office_bn'),
        data_get($employee, 'permanent_upazila_bn'),
        data_get($employee, 'permanent_district_bn'),
    ])->filter(fn ($v) => filled($v))->implode(', ');
    $presentAddress = $presentAddress ?: data_get($employee, 'address', $na);
    $permanentAddress = $permanentAddress ?: data_get($employee, 'address', $na);
	// Resolve salary breakdown via compliance-aware helper (factory_no determines gross source & deduction base)
	$sal        = hr_employee_salary($employee, $factory ?? null, $salaryKey ?? null);
	$gross      = $sal['gross'];
	$basic      = $sal['basic'];
	$house      = $sal['house'];
	$medical    = $sal['medical'];
	$transport  = $sal['transport'];
	$food       = $sal['food'];
	$otRate     = $basic > 0 ? round(($basic / 208) * 2, 2) : 0;
	$deductFrom = $sal['deduct_from'];
@endphp

@if(ENV('FACTORY') == 'SFL')
    @include('hr::reports.partials.personal-file.appointment-letter-sfl', [
        'employee' => $employee,
        'language' => $language,
        'salaryKey' => $salaryKey ?? null,
    ])
@else
    <div class="letter-box" style="border:none; padding:0; margin-top:0;">
        <div class="company-head" style="margin-bottom:10px;">
            <h3 style="margin:0; font-size:22px;">{{ $companyName ?? '' }}</h3>
            <div style="font-size:13px;">{{ $companyAddress ?? '' }}</div>
            <div style="margin-top:4px; font-weight:700; font-size:16px;">{{ $t('নিয়োগ পত্র', 'Appointment Letter') }}</div>
        </div>

        <table class="letter-grid" style="margin-bottom:12px;">
            <tr>
                <td class="k">শ্রমিকের নাম</td><td class="s">:</td><td class="v">{{ $employeeName }}</td>
                <td class="k">পিতার নাম</td><td class="s">:</td><td class="v">{{ $fatherName }}</td>
            </tr>
            <tr>
                <td class="k">স্বামী/স্ত্রীর নাম</td><td class="s">:</td><td class="v">{{ $spouseName  }}</td>
                <td class="k">মাতার নাম</td><td class="s">:</td><td class="v">{{ $motherName  }}</td>
            </tr>
            <tr>
                <td class="k">সেকশন</td><td class="s">:</td><td class="v">{{ $section ?: 'N/A' }}</td>
                <td class="k">পদবীঃ</td><td class="s">:</td><td class="v">{{ $designation ?: 'N/A' }}</td>
            </tr>
            <tr>
                <td class="k">কার্ড নং</td><td class="s">:</td><td class="v">{{ $employeeId }}</td>
                <td class="k">যোগদানের তারিখ</td><td class="s">:</td><td class="v">{{ $joiningDate }}</td>
            </tr>
            <tr>
                <td class="k">কাজের ধরন</td><td class="s">:</td><td class="v">{{ $jobType }}</td>
                <td class="k"></td><td class="s"></td><td class="v"></td>
            </tr>
            <tr>
                <td class="k">বর্তমান ঠিকানা</td><td class="s">:</td><td colspan="4">{{ $presentAddress }}</td>
            </tr>
            <tr>
                <td class="k">স্থায়ী ঠিকানা</td><td class="s">:</td><td colspan="4">{{ $permanentAddress }}</td>
            </tr>
        </table>

        <p style="font-size:12px; line-height:1.7; text-align:justify; margin-bottom:10px;">
            আপনার আবেদন, সাক্ষাৎকার এবং যোগ্যতা যাচাইয়ের পরিপ্রেক্ষিতে আপনাকে {{ $joiningDate }} ইং তারিখ হইতে {{ $designation  }} পদে {{ $grade }} নং গ্রেডে নম্নলিখিত শর্তসাপেক্ষে নিয়োগ প্রদান করিতেছি। নিয়োগ প্রাপ্তির তিন মাস প্রাথমিক পর্যায় হিসাবে গণ্য হবে। যদি দেখা যায় যে নিয়োগ প্রাপ্তির তিন মাস প্রাথমিক পর্যায়ে আপনি সম্পূর্ণ যোগ্যতা অর্জন করিতে পারেন নাই, তখন ইহা আর ও তিন মাস পর্যন্ত বর্ধিত করা হবে। এই সময়ের পর আপনাকে স্থায়ী শ্রমিক হিসাবে গণ্য করা হবে এবং ইহার জন্য কোন প্রকার চিঠি দেওয়া হবে না।
        </p>

        <div style="font-size:12px; line-height:1.7;">
            <p style="margin:0 0 6px;"><strong>১। বেতন ও ভাতাঃ</strong></p>
            <table class="salary-table" style="margin-bottom:10px;">
                <tbody>
                    <tr><td>ক। মাসিক মূল মঞ্জুরী (Monthly Basic Wages)</td><td style="width:70px;">টাকা</td><td style="width:140px;" class="text-right">{{ en2bnNumber(number_format($basic, 2)) }} /=</td></tr>
                    <tr><td>খ। মাসিক বাড়ী ভাড়া ভাতা (Monthly House Rent )</td><td>টাকা</td><td class="text-right">{{ en2bnNumber(number_format($house, 2)) }} /=</td></tr>
                    <tr><td>গ। মাসিক চিকিৎসা ভাতা (Monthly Medical Allowance)</td><td>টাকা</td><td class="text-right">{{ en2bnNumber(number_format($medical, 2)) }} /=</td></tr>
                    <tr><td>ঘ। মাসিক যাতায়াত ভাতা (Monthly Transport Allowance)</td><td>টাকা</td><td class="text-right">{{ en2bnNumber(number_format($transport, 2)) }} /=</td></tr>
                    <tr style="border-bottom: 1px solid rgba(0, 0, 0, 0.589)"><td>ঙ। মাসিক খাদ্য ভাতা (Monthly Fooding Allowance)</td><td>টাকা</td><td class="text-right">{{ en2bnNumber(number_format($food, 2)) }} /=</td></tr>
                    <tr><td><strong>মাসিক সর্বমোট টাকা (Monthly Gross Amount)</strong></td><td>টাকা</td><td class="text-right"><strong>{{ en2bnNumber(number_format($gross, 2)) }} /=</strong></td></tr>
                    <tr><td>প্রতি ঘন্টায় ও.টির হার(Over Time)</td><td>টাকা</td><td class="text-right">{{ en2bnNumber(number_format($otRate, 2)) }} /=</td></tr>
                </tbody>
            </table>

            <p style="margin:0 0 6px;"><strong>০২। সাধারন কর্মঘন্টা এবং অতিরিক্ত কর্মঘন্টা (ও, টি):</strong> ক। ও. টি হিসাব মূল বেতন হিসাবে। ঘন্টা প্রতি মূল মজুরীর দ্বিগুন হারে হিসাব করা হয়। হিসাবটি নিম্নরূপঃ মূল মজুরী/২০৮ × ২ x মোট ওভার টাইম ঘন্টা।</p>
            <p style="margin:0 0 4px;">খ। দৈনিক কর্মঘন্টা: ০৮ ঘন্টা প্রতিদিন ০১ ঘন্টা খাবার বিরতি।</p>
            <p style="margin:0 0 4px;">গ। দৈনিক ও. টি ঘন্টা: প্রতিদিন ০৮ ঘন্টার অতিরিক্ত (খাবার বিরতি ব্যতিত) ও সপ্তাহে ৪৮ ঘন্টার অতিরিক্ত সময়ের কাজকে ওভার টাইম হিসাবে গন্য করা হইবে।</p>
            <p style="margin:0 0 8px;">ঘ। বেতন/মজুরী প্রদানের সময়: প্রত্যেক মাসের বেতন / মজুরী, ওভার টাইম একত্রে প্রদান করা হয় (যাহা পরবর্তী মাসের ০৭ কর্মদিবসের মধ্যে যে কোন দিন প্রদান করা হয়)।</p>

            <p style="margin:0 0 6px;">{{ $general->title ?? '' }} এর শ্রমিক/কর্মচারীদের চাকুরীর সাধারন নিয়মাবলী ও সুবিধাদি নিম্নে উল্লেখ করা হলঃ</p>

            <p style="margin:0 0 4px;"><strong>০৩। চাকুরীতে যোগদানঃ</strong> চাকুরীতে যোগদানের দিন বা গ্রহণযোগ্য সময়ের মধ্যে নিম্নোক্ত কাগজপত্র সহ নির্ধারিত ফরম পুরন করে কারখানা অফিসে জমা দিতে হবে।</p>
            <p style="margin:0 0 2px;">ক। ০৫ (পাঁচ) কপি ছবি। পাসপোর্ট সাইজ ৪ কপি, স্ট্যাম্প সাইজ-১ কপি।</p>
            <p style="margin:0 0 2px;">খ। চেয়ারম্যান/ওয়ার্ড কমিশনার কর্তৃক প্রদত্ত নাগরিকত্ব সনদপত্রের ফটোকপি (যদি থাকে)।</p>
            <p style="margin:0 0 2px;">গ। শিক্ষাগত যোগ্যতার সনদপত্র বা স্কুল পাশের সার্টিফিকেটের ফটোকপি (যদি থাকে)।</p>
            <p style="margin:0 0 8px;">ঘ। জন্ম নিবন্ধন/জাতীয়পরিচয় পত্রের (ভোটার আইডিকার্ড) ফটোকপি।</p>

            <p style="margin:0 0 4px;"><strong>০৪। মালিক কর্তৃক শ্রমিকের চাকুরীর অবসানঃ</strong></p>
            <p style="margin:0 0 2px;">ক। বাংলাদেশের শ্রম আইন ২০০৬ অনুসারে ধারা ২৬ (১) মোতাবেক স্থায়ী শ্রমিক এর চাকুরীর অবসান করার জন্য ১২০ দিনের নোটিশ প্রদান করিতে হইবে এবং অস্থায়ী শ্রমিকের ক্ষেত্রে ৩০ দিনের লিখিত নোটিশ প্রদান করিতে হইবে।</p>
            <p style="margin:0 0 8px;">খ। নোটিশের পরিবর্তে নোটিশ মেয়াদের জন্য মজুরী প্রদান করিয়া ইহা করিতে পারিবেন।</p>

            <p style="margin:0 0 4px;"><strong>০৫। শ্রমিক কর্তৃক চাকুরীর অবসানঃ</strong></p>
            <p style="margin:0 0 2px;">ক। চাকুরী ছাড়তে হইলে বাংলাদেশ শ্রম আইন ২০০৬ অনুসারে ২৭(১) ধারা মোতাবেক কোন স্থায়ী শ্রমিক চাকুরী ছাড়ার ২ মাস (৬০ দিন) এবং অস্থায়ী শ্রমিককে ১ মাস (৩০ দিন) এবং অন্যান্য শ্রমিককে ১৪ দিন পূর্বে কর্তপক্ষ কে লিখিত নোটিশ প্রদান করিতে হইবে। অন্যথায় প্রদেয় নোটিশের পরিবর্তে নোটিশ মেয়াদের জন্য মজুরীর সমপরিমান অর্থ মালিককে প্রদান করিয়া ইহা করিতে পারিবেন।</p>
            <p style="margin:0 0 8px;">খ। চাকুরী ছাড়ার পর কোম্পানীর প্রদত্ত মালামাল অর্থাৎ আইডি কার্ডসহ কোম্পানীর প্রদত্ত জিনিসপত্র দায়িত্ব প্রাপ্ত লোকের কাছে জমা দিয়ে কারখানা হইতে ছাড়পত্র/ক্লিয়ারেন্স নিতে হইবে।</p>

            <p style="margin:0 0 4px;"><strong>০৬। ছুটিঃ</strong></p>
            <p style="margin:0 0 2px;">ক। সপ্তাহে ০১ (এক) দিন সাপ্তাহিক ছুটি: (সাধারণত শুক্রবার) যাহা সরকারী কোন আদেশ বা কোম্পানীর প্রয়োজনী কারনে অনুমোদন সাপেদে পরিবর্তন হইতে পারে।</p>
            <p style="margin:0 0 2px;">খ। উৎসব জনিত ছুটি: বৎসরে ১৩ (তের) দিন পূর্ণ মঞ্জুরী সহ এবং যাহা পরবর্তী বৎসরের সাথে যোগ হয় না।</p>
            <p style="margin:0 0 2px;">গ। নৈমিত্তিক ছুটি: বৎসরে ১০ (দশ) দিন পূর্ন মজুরীতে যাহা পরবর্তী বৎসরের সাথে যোগ হয় না।</p>
            <p style="margin:0 0 2px;">ঘ। বার্ষিক বা অর্জিত ছুটি: প্রতি আঠার কর্মদিবসের জন্য ১ (এক) দিন। এই ছুটি কেবল তাদের জন্য প্রযোজ্য যারা কমপক্ষে এক বৎসর চাকুরী পূর্ণ করিয়াছেন।</p>
            <p style="margin:0 0 2px;">ঙ। চিকিৎসা/পীড়া/অসুস্থতা ছুটি: বৎসরে ১৪ (চৌদ্দ) দিন পূর্ন মজুরীতে যাহা পরবর্তী বৎসরের সাথে যোগ হয় না।</p>
            <p style="margin:0 0 8px;">চ। মাতৃত্ব কালীন ছুটি: ১৬ সপ্তাহ (১১২ দিন) (আইনানুগ ও নগদে) এই ছুটি কেবল সেই সব মহিলার জন্য প্রযেজ্য যাহারা অত্র কোম্পানীতে কমপক্ষে ছয় (০৬) মাস চাকুরী পূর্ণ করিয়াছেন। যাহার দুই এর অধিক সন্তান জীবিত নাই তিনিই এই সুবিধা ভোগ করিতে পারিবেন।</p>

            <p style="margin:0 0 4px;"><strong>০৭। ছুটির নিয়মাবলীঃ</strong></p>
            <p style="margin:0 0 2px;">ক। কোন শ্রমিক/কর্মচারী ছুটি নিতে আগ্রহী হইলে অবশ্যই ছুটির আবেদনের নির্ধারিত ফরম পূরন করে অফিসে জমা দিতে হইবে।</p>
            <p style="margin:0 0 8px;">খ। ছুটির আবেদন মঞ্জুর হইলে কেবলমাত্র কোন শ্রমিক/কর্মচারী ছুটি ভোগ করিতে পারিবেন।</p>

            <p style="margin:0 0 4px;"><strong>০৮। সুবিধাঃ</strong></p>
            <p style="margin:0 0 2px;">ক। বিনা খরচে কারখানার ডাক্তার এবং নার্সের মাধ্যমে চিকিৎসা সুবিধা প্রদান করা হয়।</p>
            <p style="margin:0 0 8px;">খ। শ্রমিক/কর্মচারীর জন্য গ্রুপ ইনসুরেন্স এর ব্যবস্থা আছে।</p>

            <p style="margin:0 0 4px;"><strong>০৯। "বাংলাদেশ শ্রম আইন ২০০৬ অনুসারে ধারা ২৩ (৪) মোতাবেক নিম্নোক্তকাজ করা অসদাচরণ" বলিয়া গণ্য হইবে যথাঃ</strong></p>
            <p style="margin:0 0 2px;">ক। উপরস্থের কোন আইন সংগত বা যুক্তি সংগত আদেশ মানার ক্ষেত্রে এককভাবে বা অন্যের সঙ্গে সংঘবদ্ধ হইয়া ইচ্ছাকৃতভাবে অবাধ্যতা।</p>
            <p style="margin:0 0 2px;">খ। মালিকের ব্যবসা বা সম্পত্তি চুরি, প্রতারনা বা অসাধুতা।</p>
            <p style="margin:0 0 2px;">গ। মালিকের অধীনে তাঁহার বা অন্য কোন শ্রমিকের চাকুরী সংক্রান্ত ব্যাপারে ঘুষ গ্রহণ বা প্রদান।</p>
            <p style="margin:0 0 2px;">ঘ। বিনা ছুটিতে অভ্যাগত অনুপস্থিতি অথবা ছুটি না নিয়া এক সঙ্গে দশ দিনের অধিক সময় অনুপস্থিত।</p>
            <p style="margin:0 0 2px;">ঙ। অভ্যাগত বিলম্বে উপস্থিত।</p>
            <p style="margin:0 0 2px;">চ। প্রতিষ্ঠানের প্রযোজ্য কোন আইন, বিধি বা প্রবিধানের অভ্যাসগত লংঘন।</p>
            <p style="margin:0 0 2px;">ছ। প্রতিষ্ঠানে উচ্ছৃঙ্খল বা দাংগা হাঙ্গামামূলক আচরন, অথবা শৃঙ্খলা হানিকর কোন কর্ম।</p>
            <p style="margin:0 0 2px;">জ। কাজ কর্মে অভ্যাসগত গাফিলতি।</p>
            <p style="margin:0 0 2px;">ঝা। প্রধান পরিদর্শক কর্তৃক অনুমোদিত চাকুরী সংক্রান্ত, শৃঙ্খলা বা আচরনসহ যে কোব বিধির অভ্যাসগত লংঘন।</p>
            <p style="margin:0 0 8px;">ঞ। মালিকের অফিসিয়াল রেকর্ডের রদবদল, জালকরন, অন্যায় পরিবর্তন, উহার ক্ষতিকরন বা উহা হারাইয়া ফেলা।</p>

            <p style="margin:0 0 4px;"><strong>১০। ঠিকানা প্রদানঃ</strong> আপনি/ আপনার ঠিকানা পরিবর্তন করিলে সাথে সাথে কর্তৃপক্ষ কে লিখিতভাবে জানাইতে হইবে।</p>
            <p style="margin:0 0 4px;"><strong>১১। আইডি কার্ডঃ</strong> প্রত্যেক শ্রমিক কর্মচারীকে আইডি কার্ড প্রদান করা হয় এবং উক্ত কার্ডটি কারখানা প্রবেশ ও বাহিরের সময় প্রধান ফটকে নিরাপত্তা বিভাগে প্রদর্শন করতে হবে। এবং কর্মকালীন সময়ে গলায় অথবা শরীরের দৃশ্যমান হয় এমন স্থানে ঝুলন্ত অবস্থায় রাখতে হবে।</p>
            <p style="margin:0 0 4px;"><strong>১২। হাজিরা কার্ডঃ</strong> প্রত্যেক শ্রমিক/কর্মচারী কে তাহাদের দৈনন্দিন হাজিরা নিশ্চিত করিবার জন্য হাজিরা কার্ড প্রদান করা হয়। উক্ত কার্ডটি কারখানায় উপস্থিত হয়ে প্রবেশ করার সময় সিকিউরিটি গেটে জমা দিতে হবে এবং ছুটির সময় কার্ডটিতে আপনার হাজিরা নিশ্চিত করে নিজ কর্মস্থলে ত্যাগ করতে হবে।</p>
            <p style="margin:0 0 4px;"><strong>১৩। মজুরী বৃদ্ধি/পদোন্নতিঃ</strong> প্রতি বছর আপনার কর্মদক্ষদা মূল্যায়ন করে পদোন্নতি প্রদান এবং আপনার বেতন বৃদ্ধি করা হবে। তবে কমপক্ষে ৯% হার অবশ্যই বৃদ্ধি করা হবে।</p>
            <p style="margin:0 0 8px;"><strong>১৪। সার্ভিস বইঃ</strong> বাংলাদেশ শ্রম আইন ২০০৬ অনুযায়ী প্রত্যেক শ্রমিকের জন্য কোম্পনীর খরচে সার্ভিস বই চালুর ব্যবস্থা আছে।</p>

            <p style="font-size:12px; line-height:1.7; text-align:justify; margin:10px 0 18px;">
                আমি {{ $employeeName ?? '................................' }} অত্র নিয়োগপত্র পাঠ করেছি/ আমাকে পাঠ করে শোনানো হয়েছে। এতে বর্ণিত শর্তাদি আমি সম্পূর্ণক্রমে অবগত হয়ে, কারো দ্বারা প্ররোচিত না হয়ে কারো কোনরূপ জোর জবরদস্তি ছাড়াই স্বেচ্ছায় ও স্বজ্ঞানে উপরোক্ত শর্ত মেনে নিয়ে, এই নিয়োগপত্রে স্বাক্ষর করে নিয়োগপত্র এবং একটি শ্রমিক সহায়িকা গ্রহন করছি এবং কাজে যোগদান করছি।
            </p>
        </div>

        <table style="width:100%; margin-top:24px; font-size:12px; border-collapse:collapse;">
            <tr>
                <td style="width:33%; text-align:left; padding-top:30px;">তারিখ</td>
                <td style="width:33%; text-align:center; padding-top:30px;">কর্মচারীর স্বাক্ষর</td>
                <td style="width:34%; text-align:right; padding-top:30px;">প্রশাসন, মানব সম্পদ বিভাগ</td>
            </tr>
        </table>
    </div>
@endif

@push('css')
    <style>

    </style>
@endpush


