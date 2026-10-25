{{-- Show Cause Notice (কারণ দর্শানো নোটিশ) letter body — the company header is drawn by print.blade.php. --}}
<div style="text-align:center; font-weight:700; font-size:20px; text-decoration: underline; margin: 18px 0 22px;">
    কারণ দর্শানো নোটিশ
</div>

<div style="margin-bottom:6px;"><strong>স্মারক নং:</strong> {{ $notice->memo_no }}</div>
<div style="margin-bottom:14px;"><strong>তারিখ:</strong> {{ $bd($notice->notice_date) }}</div>

<div style="margin-bottom:18px;">
    বরাবর<br>
    জনাব, {{ $employeeName }}<br>
    {{ $designationName }}, {{ $departmentName }}।
</div>

<div style="margin-bottom:18px;">
    <strong>বিষয়: {{ $notice->subject }}</strong>
</div>

<div style="text-align: justify;">
    <p style="margin-bottom:14px;">মহোদয়/মহোদয়া,</p>

    <p style="margin-bottom:14px;">
        উপরে উল্লিখিত বিষয়ের প্রেক্ষিতে আপনাকে জানানো যাচ্ছে যে,
        @if($notice->incident_date)
            {{ $bd($notice->incident_date) }} তারিখে
        @endif
        {!! nl2br(e($notice->incident_description)) !!}
        এর ফলে প্রতিষ্ঠানের আর্থিক এবং ব্যবসায়িক ক্ষতি সাধন হয়েছে।
    </p>

    @if($notice->consequence)
        <p style="margin-bottom:0;">{!! nl2br(e($notice->consequence)) !!}</p>
    @endif
</div>

<div style="margin-top:22px;">
    <strong>* শ্রমিকের / কর্মচারীর বিবরণ:</strong><br>
    নাম: &nbsp; {{ $employeeName }}<br>
    পদবী: &nbsp; {{ $designationName }}<br>
    বিভাগ: &nbsp; {{ $departmentName }}<br>
    আইডি নং: &nbsp; {{ en2bnNumber((string) ($employee->employee_id ?? '-')) }}
</div>

<div style="margin-top:22px;">
    <strong>* ঘোষণা ও স্বাক্ষর:</strong>
    <p style="text-align: justify; margin-top:6px;">
        আমি স্বীকার করছি যে, উপরোক্ত কাজের অবহেলার বিষয়টি সত্য এবং এর ফলে প্রতিষ্ঠানের ক্ষতি সাধন হয়েছে। আমি এই দায় মেনে নিচ্ছি এবং আমার বিরুদ্ধে প্রতিষ্ঠানের নিয়ম অনুযায়ী যেকোনো যথাযথ ব্যবস্থা গ্রহণে বাধ্য থাকব।
    </p>
</div>

<div style="margin-top:50px;">
    _______________<br>
    অভিযুক্ত শ্রমিকের / কর্মচারীর স্বাক্ষর ও তারিখ
</div>

<div style="margin-top:30px;">
    <strong>কর্তৃপক্ষের অনুমোদন:</strong>
</div>

<div style="margin-top:50px; display:flex; justify-content:space-between; text-align:center;">
    <div style="width:45%;">
        _______________<br>
        এইচ আর এন্ড এডমিন ম্যানেজার
    </div>
    <div style="width:45%;">
        _______________<br>
        জেনারেল ম্যানেজার
    </div>
</div>
