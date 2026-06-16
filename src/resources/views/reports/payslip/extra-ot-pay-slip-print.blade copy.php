
@php
    $language = $language ?? data_get($request ?? null, 'language', 'bn');
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
@endphp
{{-- Extra OT Pay Slip (per employee) --}}
@foreach($employees as $employee)
    @php
        $employeeDataFn = \App\Services\HrOptionsService::getOptionsForEmployee();
        $employeeData = $employeeDataFn($employee, $request ?? null, $factory ?? null, $salaryKey ?? null, $profile ?? null, $nominee ?? null);
    @endphp

    <div class="containerX">
        <!-- Office Copy -->
        <div class="slip-half ">
            <div class="copy-tag">অফিস কপি</div>
            <div class="header">
                <h2>এনআর ফ্যাশন ওয়্যার লিমিটেড</h2>
                <p>ডেগেরচালা রোড, জাজিবর, হারিকেন, গাজীপুর সদর,</p>
                <p style="margin: 0; font-size: 11px;">অতিরিক্ত ওটি স্লিপ (মার্চ-২০২৬)</p>
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

            <table class="ot-table">
                <tr>
                    <td>এক্সট্রা ওটি ঘণ্টা</td>
                    <td style="text-align: right;">১২ ঘণ্টা</td>
                </tr>
                <tr>
                    <td>ওটি রেট (প্রতি ঘণ্টা)</td>
                    <td style="text-align: right;">৭৮.৮৩ টাকা</td>
                </tr>
            </table>

            <div class="total-box">
                মোট টাকা: ৯৪৬.০০
            </div>

            <div class="footer">
                {{-- <div class="signature">হিসাব রক্ষক</div> --}}
                <div class="signature">গ্রহীতা</div>
            </div>
        </div>

        <!-- Worker Copy -->
        <div class="slip-half">
            <div class="copy-tag">শ্রমিক কপি</div>
            <div class="header">
                <h2>এনআর ফ্যাশন ওয়্যার লিমিটেড</h2>
                <p>ডেগেরচালা রোড, জাজিবর, হারিকেন, গাজীপুর সদর,</p>
                <p style="margin: 0; font-size: 11px;">অতিরিক্ত ওটি স্লিপ (মার্চ-২০২৬)</p>
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

            <table class="ot-table">
                <tr>
                    <td>এক্সট্রা ওটি ঘণ্টা</td>
                    <td style="text-align: right;">১২ ঘণ্টা</td>
                </tr>
                <tr>
                    <td>ওটি রেট (প্রতি ঘণ্টা)</td>
                    <td style="text-align: right;">৭৮.৮৩ টাকা</td>
                </tr>
            </table>

            <div class="total-box">
                মোট টাকা: ৯৪৬.০০
            </div>

            <div class="footer">
                {{-- <div class="signature">হিসাব রক্ষক</div> --}}
                <div class="signature">গ্রহীতা</div>
            </div>
        </div>
    </div>
@endforeach
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            font-size: 10px
        }

        .containerX {
            margin: 0 auto;
            padding: 10px;
            border: 1px solid #ccc;
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        /* মাঝখানের ড্যাশ লাইন */
        .containerX::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            border-left: 1px dashed #000;
        }

        .slip-half {
            width: 45%;
            position: relative;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 5px;
            padding-bottom: 5px;
        }

        .header h2 { margin: 0; font-size: 16px; }
        .copy-tag { text-align: right; font-weight: bold; font-size: 12px; margin-bottom: 5px; position: absolute; right: 0; top: 2.7rem; }

        .section-info {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .ot-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .ot-table td {
            padding: 5px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .total-box {
            background-color: #f9f9f9;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 30px;
            /* display: flex; */
            justify-content: space-between;
        }

        .signature {
            border-top: 1px solid #000;
            width: 100px;
            text-align: center;
            font-size: 12px;
            float: right;
        }
    </style>
