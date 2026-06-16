@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
@endphp

@foreach($employees as $employee)
    @php
        $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    @endphp

<div class="payslip-container">
    <!-- Office Copy -->
    <div class="payslip-half">
        <div class="copy-type">অফিস কপি:</div>
        <div class="header">
            <h2>এনআর ফ্যাশন ওয়্যার লিমিটেড</h2>
            <p>ডেগেরচালা রোড, জাজিবর, হারিকেন, গাজীপুর সদর,</p>
            <p>Month: March-2026</p>
        </div>

        <div class="section-info">
            <div>
                <strong>সেকশন: Cleaner/Security</strong><br>
                <strong>কার্ড নং: ১১০০৫</strong><br>
                <strong>নাম: আব্দুর রহিম</strong>
            </div>
            <div style="text-align: right;">
                <strong>ব্লক নং - </strong><br>
                হাাজিরা বোনাস ৭২৫<br>
                পদবী: ক্লিনার
            </div>
        </div>

        <table>
            <tr>
                <td class="label">মূল বেতন:</td>
                <td class="value">৮১৮৮</td>
                <td></td><td></td>
                <td class="label right-align">মোট দিন:</td>
                <td class="value right-align">৩১</td>
            </tr>
            <tr>
                <td class="label">বাড়ি ভাড়া:</td>
                <td class="value">৪২০০</td>
                <td class="label">চিকিৎসা ভাতা:</td>
                <td class="value">৭৫০</td>
                <td class="label right-align">হাজিরা (দিন):</td>
                <td class="value right-align">১৯</td>
            </tr>
            <tr>
                <td class="label">যাতায়াত ভাতা:</td>
                <td class="value">৪৫০</td>
                <td class="label">খাদ্য ভাতা:</td>
                <td class="value">১২৫০</td>
                <td class="label right-align">অনুপস্থিত:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">মোট বেতন:</td>
                <td class="value" style="border-bottom:1px solid red !important;">১৪৭৮৭</td>
                <td class="label">ওটি রেট:</td>
                <td class="value">৭৮.৮৩</td>
                <td class="label right-align">নৈমিত্তিক ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label">মোট ওটি টাকা:</td>
                <td class="value">৬৩১</td>
                <td class="label">ওটি ঘন্টা:</td>
                <td class="value">৮</td>
                <td class="label right-align">অসুস্থতা ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">প্রাপ্য বেতন:</td>
                <td class="value" style="border-bottom:1px solid red !important;">১৫৩৭৮</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">অর্জিত ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label">অনুপ: কর্তন টাকা:</td>
                <td class="value">০</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাপ্তাহিক ছুটি:</td>
                <td class="value right-align">৬</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">অগ্রিম প্রদেয় টাকা:</td>
                <td class="value" style="border-bottom:1px solid red !important;">0</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">উৎসব ছুটি:</td>
                <td class="value right-align">৬</td>
            </tr>
            <tr>
                <td class="label">মোট প্রদেয় টাকা:</td>
                <td class="value">১৬০৯৩</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাধারণ ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td class="label right-align">মাতৃত্বকালীন ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
        </table>

        <div class="footer">
            **আপনার যে কোন অভিযোগ এবং পরামর্শ মানব সম্পদ<br>
            ও কমপ্লায়েন্স বিভাগকে অবহিত করুন।
        </div>
        <div class="signature">স্বাক্ষর</div>
    </div>

    <!-- Dashed Divider -->
    <div class="dashed-line"></div>

    <!-- Worker Copy -->
    <div class="payslip-half">
        <div class="copy-type">শ্রমিক কপি:</div>
        <div class="header">
            <h2>এনআর ফ্যাশন ওয়্যার লিমিটেড</h2>
            <p>ডেগেরচালা রোড, জাজিবর, হারিকেন, গাজীপুর সদর,</p>
            <p>Month: March-2026</p>
        </div>

        <div class="section-info">
            <div>
                <strong>সেকশন: Cleaner/Security</strong><br>
                <strong>কার্ড নং: ১১০০৫</strong><br>
                <strong>নাম: আব্দুর রহিম</strong>
            </div>
            <div style="text-align: right;">
                <strong>ব্লক নং - </strong><br>
                হাাজিরা বোনাস ৭২৫<br>
                পদবী: ক্লিনার
            </div>
        </div>

        <table>
            <!-- টেবিলের তথ্যগুলো উপরের মতো একই থাকবে -->
            <tr>
                <td class="label">মূল বেতন:</td>
                <td class="value">৮১৮৮</td>
                <td></td><td></td>
                <td class="label right-align">মোট দিন:</td>
                <td class="value right-align">৩১</td>
            </tr>
            <tr>
                <td class="label">বাড়ি ভাড়া:</td>
                <td class="value">৪২০০</td>
                <td class="label">চিকিৎসা ভাতা:</td>
                <td class="value">৭৫০</td>
                <td class="label right-align">হাজিরা (দিন):</td>
                <td class="value right-align">১৯</td>
            </tr>
            <tr>
                <td class="label">যাতায়াত ভাতা:</td>
                <td class="value">৪৫০</td>
                <td class="label">খাদ্য ভাতা:</td>
                <td class="value">১২৫০</td>
                <td class="label right-align">অনুপস্থিত:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">মোট বেতন:</td>
                <td class="value" style="border-bottom:1px solid red !important;">১৪৭৮৭</td>
                <td class="label">ওটি রেট:</td>
                <td class="value">৭৮.৮৩</td>
                <td class="label right-align">নৈমিত্তিক ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label">মোট ওটি টাকা:</td>
                <td class="value">৬৩১</td>
                <td class="label">ওটি ঘন্টা:</td>
                <td class="value">৮</td>
                <td class="label right-align">অসুস্থতা ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">প্রাপ্য বেতন:</td>
                <td class="value" style="border-bottom:1px solid red !important;">১৫৩৭৮</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">অর্জিত ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td class="label">অনুপ: কর্তন টাকা:</td>
                <td class="value">০</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাপ্তাহিক ছুটি:</td>
                <td class="value right-align">৬</td>
            </tr>
            <tr>
                <td class="label" style="border-bottom:1px solid red !important;">অগ্রিম প্রদেয় টাকা:</td>
                <td class="value" style="border-bottom:1px solid red !important;"></td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">উৎসব ছুটি:</td>
                <td class="value right-align">৬</td>
            </tr>
            <tr>
                <td class="label">মোট প্রদেয় টাকা:</td>
                <td class="value">১৬০৯৩</td>
                <td class="label"></td>
                <td class="value"></td>
                <td class="label right-align">সাধারণ ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
            <tr>
                <td colspan="4"></td>
                <td class="label right-align">মাতৃত্বকালীন ছুটি:</td>
                <td class="value right-align">০</td>
            </tr>
        </table>

        <div class="footer">
            **আপনার যে কোন অভিযোগ এবং পরামর্শ মানব সম্পদ<br>
            ও কমপ্লায়েন্স বিভাগকে অবহিত করুন।
        </div>
        <div class="signature">স্বাক্ষর</div>
    </div>
</div>
@endforeach

    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .payslip-container {
            width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border: 1px dashed #000;
            display: flex;
            justify-content: space-between;
        }

        .payslip-half {
            width: 48%;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
        }

        .copy-type {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .section-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
            font-size: 10px !important;
            border: none !important;
        }

        .label {
            font-weight: bold;
            width: 90px;
        }

        .value {
            text-align: left;
        }

        .right-align {
            text-align: right;
        }

        .footer {
            margin-top: 15px;
            font-size: 10px;
            font-weight: bold;
        }

        .signature {
            margin-top: 20px;
            text-align: right;
            border-top: 1px solid #000;
            width: 80px;
            float: right;
        }

        .dashed-line {
            border-left: 1px dashed #000;
            height: auto;
        }
    </style>
