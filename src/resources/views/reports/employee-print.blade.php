@extends('printMaster2')

@section('title', 'Employee Report - ' . $reportTypeLabel)

@push('css')
<style>
    .report-head {
        text-align: center;
        margin-bottom: 14px;
    }
    .report-head h3 {
        margin: 0 0 4px;
    }
    .meta-line {
        margin-bottom: 10px;
        font-size: 12px;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #222;
        padding: 4px 5px;
        font-size: 11px;
        vertical-align: top;
    }
    .report-table thead th {
        text-align: center;
        background: #f2f2f2;
    }
    .database-table thead tr:first-child th {
        background: #c9ddb3;
    }
    .database-table thead tr:nth-child(2) th {
        background: #efe8b0;
    }
    .database-row-odd td {
        background: #d8edf3;
    }
    .database-row-even td {
        background: #f8fbfc;
    }
    .nowrap {
        white-space: nowrap;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .subtotal-row td {
        background: #d8f3c8;
        font-weight: 700;
    }
    .grandtotal-row td {
        background: #bfe7b8;
        font-weight: 700;
    }
</style>
@endpush

@section('contents')
@php
    // Bangla/English toggle helpers
    $isBangla = $language === 'bn';
    $t = fn ($bn, $en) => $isBangla ? $bn : $en;
    // Option maps — built from $options passed by controller
    $classificationMap = collect($options['classifications'] ?? [])->pluck('name', 'id');
    $departmentMap     = collect($options['departments'] ?? [])->pluck('name', 'id');
    $sectionMap        = collect($options['sections'] ?? [])->pluck('name', 'id');
    $subSectionMap     = collect($options['subSections'] ?? [])->keyBy('id');
    $designationMap    = collect($options['designations'] ?? [])->pluck('name', 'id');
    $workingPlaceMap   = collect($options['workingPlaces'] ?? [])->pluck('name', 'id');
    $shiftMap          = collect($options['shifts'] ?? [])->pluck('name', 'id');
    $lineMap           = collect($options['lines'] ?? [])->mapWithKeys(
        fn ($row) => [$row->id => trim(($row->name ?? '') . (filled($row->slug ?? null) ? ' - ' . $row->slug : ''))]
    );
    $gradeMap = collect($options['grades'] ?? [])->pluck('name', 'id');
    $fmtDate = function ($value) {
        if (blank($value)) return 'N/A';
        try { return \Carbon\Carbon::parse($value)->format('d-m-Y'); } catch (\Throwable $e) { return (string) $value; }
    };
    $fmtMoney = fn ($value) => number_format((float) $value, 2);
@endphp

<div class="report-head">
    <h3>{{ general()->title ?? 'Company Name' }}</h3>
    <div>{{ general()->address_one ?? data_get(general(), 'address') }}</div>
    <strong>{{ $t('কর্মচারী রিপোর্ট', 'Employee Report') }} - {{ $reportTypeLabel }}</strong>
</div>

<div class="meta-line">
    <strong>{{ $t('প্রিন্ট তারিখ', 'Print Date') }}:</strong> {{ now()->format('d-m-Y h:i A') }}
    <span style="margin-left: 18px;"><strong>{{ $t('মোট কর্মচারী', 'Total Employee') }}:</strong> {{ $employees->count() }}</span>
</div>

@if($reportType === 'manpower-summary')
    <table class="report-table database-table">
        <thead>
            <tr>
                <th>{{ $t('ক্রমিক', 'SL') }}</th>
                <th>{{ $t('বিভাগ', 'Department') }}</th>
                <th>{{ $t('সেকশন', 'Section') }}</th>
                <th>{{ $t('সাব-সেকশন', 'Sub Section') }}</th>
                <th>{{ $t('ডিজাইনেশন', 'Designation') }}</th>
                <th>{{ $t('অনুমোদিত জনবল', 'Approve Manpower') }}</th>
                <th>{{ $t('নিয়োগকৃত', 'Recruited') }}</th>
                <th>{{ $t('বিচ্যুতি', 'Deviation') }}</th>
                <th>{{ $t('মোট গ্রস স্যালারি', 'Total Gross Salary(TK)') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($manpowerRows as $row)
                <tr class="{{ $row['row_type'] === 'grand_total' ? 'grandtotal-row' : ($row['row_type'] === 'total' ? 'subtotal-row' : '') }}">
                    <td class="text-center">{{ $row['sl'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['section'] }}</td>
                    <td>{{ $row['sub_section'] }}</td>
                    <td>{{ $row['designation'] }}</td>
                    <td class="text-center">{{ $row['approve_manpower'] }}</td>
                    <td class="text-center">{{ $row['recruited'] }}</td>
                    <td class="text-center">{{ $row['deviation'] }}</td>
                    <td class="text-right">{{ $fmtMoney($row['total_gross_salary']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ $t('কোনো তথ্য পাওয়া যায়নি।', 'No data found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th colspan="5">Employee Profile</th>
                <th colspan="7">Salary Info.</th>
                <th colspan="11">Basic Info.</th>
                <th colspan="22">Reference</th>
                <th colspan="4">Permanent Address</th>
                <th colspan="4">Present Address</th>
                <th colspan="8">Nominee Info.</th>
            </tr>
            <tr>
                <th class="nowrap">S.L</th>
                <th class="nowrap">Working Place</th>
                <th class="nowrap">Name</th>
                <th class="nowrap">Emp.ID</th>
                <th class="nowrap">Join Date</th>
                <th class="nowrap">Gross Salary</th>
                <th class="nowrap">Pay Mode</th>
                <th class="nowrap">Bank/Mobile No.</th>
                <th class="nowrap">Car & Fuel</th>
                <th class="nowrap">Phone & Internet</th>
                <th class="nowrap">Extra Facility</th>
                <th class="nowrap">Tax</th>
                <th class="nowrap">Classification</th>
                <th class="nowrap">Department</th>
                <th class="nowrap">Section</th>
                <th class="nowrap">Sub Section</th>
                <th class="nowrap">Line/ Block</th>
                <th class="nowrap">Designation</th>
                <th class="nowrap">Grade</th>
                <th class="nowrap">Shift</th>
                <th class="nowrap">WeekEnd</th>
                <th class="nowrap">Personal Contact No.</th>
                <th class="nowrap">Emergency Contact No.</th>
                <th class="nowrap">Fathers Name</th>
                <th class="nowrap">Mothers Name</th>
                <th class="nowrap">Marital Status</th>
                <th class="nowrap">Spouse Name</th>
                <th class="nowrap">Sex</th>
                <th class="nowrap">Kids</th>
                <th class="nowrap">Religion</th>
                <th class="nowrap">DOB</th>
                <th class="nowrap">Blood Group</th>
                <th class="nowrap">Nationality</th>
                <th class="nowrap">NID No.</th>
                <th class="nowrap">Birth Reg. No.</th>
                <th class="nowrap">Passport No.</th>
                <th class="nowrap">Driving License No.</th>
                <th class="nowrap">Special Ident. Sign</th>
                <th class="nowrap">Edu. Exp.</th>
                <th class="nowrap">Job Exp.</th>
                <th class="nowrap">Previous Org.</th>
                <th class="nowrap">Reference Name</th>
                <th class="nowrap">Designation</th>
                <th class="nowrap">Card No.</th>
                <th class="nowrap">Mobile No.</th>
                <th class="nowrap">District</th>
                <th class="nowrap">Po. Station</th>
                <th class="nowrap">Post Office</th>
                <th class="nowrap">Village</th>
                <th class="nowrap">District</th>
                <th class="nowrap">Po. Station</th>
                <th class="nowrap">Post Office</th>
                <th class="nowrap">Village</th>
                <th class="nowrap">Nominee Name</th>
                <th class="nowrap">Po. Station</th>
                <th class="nowrap">Post Office</th>
                <th class="nowrap">Village</th>
                <th class="nowrap">NID No.</th>
                <th class="nowrap">Mobile No.</th>
                <th class="nowrap">Relation</th>
                <th class="nowrap">Age</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $other      = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information ?? '{}', true);
                    $profile    = data_get($other, 'profile', []);
                    $salaryInfo = data_get($other, 'salary_info', []);
                    $nominee    = data_get($other, 'nominee_info', []);
                    $subSection   = $subSectionMap->get($employee->sub_section_id ?? data_get($profile, 'sub_section_id'));
                    $workingPlace = $workingPlaceMap->get(data_get($profile, 'working_place_id') ?? $employee->working_place_id, 'N/A');
                    $line         = $lineMap->get($employee->line_number, 'N/A');
                    $weekend      = data_get($profile, 'weekend', $employee->weekend ?? 'N/A');
                @endphp
                <tr class="{{ $loop->odd ? 'database-row-odd' : 'database-row-even' }}">
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $workingPlace }}</td>
                    <td>{{ $employee->name ?? 'N/A' }}</td>
                    <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ $fmtDate($employee->joining_date) }}</td>
                    <td class="text-right">{{ $fmtMoney($employee->gross_salary) }}</td>
                    <td>{{ $employee->salary_type ?? 'N/A' }}</td>
                    <td>{{ data_get($salaryInfo, 'bank_or_phone', 'N/A') }}</td>
                    <td class="text-right">{{ $fmtMoney(data_get($salaryInfo, 'car_fuel', 0)) }}</td>
                    <td class="text-right">{{ $fmtMoney(data_get($salaryInfo, 'phone_internet', 0)) }}</td>
                    <td class="text-right">{{ $fmtMoney(data_get($salaryInfo, 'extra_facility', 0)) }}</td>
                    <td class="text-right">{{ $fmtMoney(data_get($salaryInfo, 'tax', 0)) }}</td>
                    <td>{{ $classificationMap->get($employee->employee_type, 'N/A') }}</td>
                    <td>{{ $departmentMap->get($employee->department_id, 'N/A') }}</td>
                    <td>{{ $sectionMap->get($employee->section_id, 'N/A') }}</td>
                    <td>{{ data_get($subSection, 'name', 'N/A') }}</td>
                    <td>{{ $line }}</td>
                    <td>{{ $designationMap->get($employee->designation_id, 'N/A') }}</td>
                    <td>{{ $gradeMap->get($employee->grade_lavel, 'N/A') }}</td>
                    <td>{{ $shiftMap->get($employee->shift_id, 'N/A') }}</td>
                    <td>{{ $weekend }}</td>
                    <td>{{ $employee->mobile ?? 'N/A' }}</td>
                    <td>{{ $employee->emergency_mobile ?? 'N/A' }}</td>
                    <td>{{ $employee->father_name ?? 'N/A' }}</td>
                    <td>{{ $employee->mother_name ?? 'N/A' }}</td>
                    <td>{{ $employee->marital_status ?? 'N/A' }}</td>
                    <td>{{ $employee->spouse_name ?? 'N/A' }}</td>
                    <td>{{ $employee->gender ?? 'N/A' }}</td>
                    <td>{{ $employee->boys ?? 'N/A' }}</td>
                    <td>{{ $employee->religion ?? 'N/A' }}</td>
                    <td>{{ $fmtDate($employee->dob) }}</td>
                    <td>{{ $employee->blood_group ?? 'N/A' }}</td>
                    <td>{{ $employee->nationality ?? 'N/A' }}</td>
                    <td>{{ $employee->nid_number ?? 'N/A' }}</td>
                    <td>{{ $employee->birth_registration ?? 'N/A' }}</td>
                    <td>{{ $employee->passport_no ?? 'N/A' }}</td>
                    <td>{{ $employee->driving_license ?? 'N/A' }}</td>
                    <td>{{ $employee->distinguished_mark ?? 'N/A' }}</td>
                    <td>{{ $employee->education ?? 'N/A' }}</td>
                    <td>{{ data_get($profile, 'job_experience', $employee->job_experience ?? 'N/A') }}</td>
                    <td>{{ data_get($profile, 'prev_organization', 'N/A') }}</td>
                    <td>{{ $employee->reference_1 ?? 'N/A' }}</td>
                    <td>{{ $employee->reference_2 ?? 'N/A' }}</td>
                    <td>{{ data_get($profile, 'reference_card_no', 'N/A') }}</td>
                    <td>{{ data_get($profile, 'reference_mobile', 'N/A') }}</td>
                    <td>{{ $employee->permanent_district ?? 'N/A' }}</td>
                    <td>{{ $employee->permanent_upazila ?? 'N/A' }}</td>
                    <td>{{ $employee->permanent_post_office ?? 'N/A' }}</td>
                    <td>{{ $employee->permanent_village ?? 'N/A' }}</td>
                    <td>{{ $employee->present_district ?? 'N/A' }}</td>
                    <td>{{ $employee->present_upazila ?? 'N/A' }}</td>
                    <td>{{ $employee->present_post_office ?? 'N/A' }}</td>
                    <td>{{ $employee->present_village ?? 'N/A' }}</td>
                    <td>{{ $employee->nominee ?? 'N/A' }}</td>
                    <td>{{ data_get($nominee, 'nominee_po_station', 'N/A') }}</td>
                    <td>{{ data_get($nominee, 'nominee_post_office', 'N/A') }}</td>
                    <td>{{ data_get($nominee, 'nominee_village', 'N/A') }}</td>
                    <td>{{ data_get($nominee, 'nominee_nid', 'N/A') }}</td>
                    <td>{{ data_get($nominee, 'nominee_mobile', 'N/A') }}</td>
                    <td>{{ $employee->nominee_relation ?? 'N/A' }}</td>
                    <td>{{ $employee->nominee_age ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="61" class="text-center">{{ $t('কোনো কর্মচারী পাওয়া যায়নি।', 'No employee found.') }}</td>

                </tr>
            @endforelse
        </tbody>
    </table>
@endif
@endsection






