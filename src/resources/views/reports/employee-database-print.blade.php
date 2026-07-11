    @extends('printMaster2')

@section('title', 'Employee Report - Database')

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
</style>
@endpush

@section('contents')
@php
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
    // Grade names from package options
    $gradeMap = collect($options['grades'] ?? [])->pluck('name', 'id');
    // Date formatter
    $fmtDate = function ($value) {
        if (blank($value)) return 'N/A';
        try { return \Carbon\Carbon::parse($value)->format('d-m-Y'); } catch (\Throwable $e) { return (string) $value; }
    };
    // Money formatter
    $fmtMoney = fn ($value) => number_format((float) $value, 2);
    // Return first non-empty value from a list of candidates
    $firstFilled = function (...$values) {
        foreach ($values as $value) {
            if (filled($value)) {
                return $value;
            }
        }

        return 'N/A';
    };
@endphp
<div class="report-head text-center">
    @if(!blank(general()->logo()))
        <img src="{{ asset(general()->logo()) }}" alt="Logo" style="max-height:40px;margin-bottom:4px;">
    @endif
    <h3>{{ hr_factory('name') ?? 'Company Name' }}</h3>
    <div>{{ hr_factory('address') ?? '' }}</div>
    <strong>Employee Report - Database</strong>
</div>
<div class="meta-line">
    <strong>Print Date:</strong> {{ now()->format('d-m-Y h:i A') }}
    <span style="margin-left: 18px;"><strong>Total Employee:</strong> {{ $employees->count() }}</span>
</div>
<table class="report-table database-table">
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
                // Parse other_information JSON (stored as text in DB)
                $other = is_array($employee->other_information)
                    ? $employee->other_information
                    : json_decode($employee->other_information ?? '{}', true);

                // profile: sub_section_id, working_place_id, weekend, job_experience, etc.
                $profile    = data_get($other, 'profile', []);

                // salary_info: bank_or_phone, car_fuel, phone_internet, extra_facility, tax
                $salaryInfo = data_get($other, 'salary_info', []);

                // nominee_info: nominee address, nid, mobile, etc.
                $nominee    = data_get($other, 'nominee_info', []);
                $profileNested = data_get($profile, 'profile', []);

                // Resolve IDs that are stored in profile (not direct columns)
                $subSection   = $subSectionMap->get($employee->sub_section_id ?? data_get($profile, 'sub_section_id'));
                $workingPlace = $workingPlaceMap->get(data_get($profile, 'working_place_id') ?? $employee->working_place_id, 'N/A');
                $line         = $lineMap->get($employee->line_number, 'N/A');
                $weekend      = data_get($profile, 'weekend', $employee->weekend ?? 'N/A');
                $designationName = $firstFilled(
                    $designationMap->get($employee->designation_id),
                    $designationMap->get(data_get($profile, 'designation_id')),
                    $designationMap->get(data_get($profileNested, 'designation_id')),
                    data_get($employee, 'designation.name'),
                    data_get($employee, 'designation_name'),
                    data_get($profile, 'designation_name'),
                    data_get($profileNested, 'designation_name')
                );
                $referenceDesignation = $firstFilled(
                    $employee->reference_2,
                    data_get($profile, 'reference_2'),
                    data_get($profileNested, 'reference_2')
                );
                $referenceCardNo = $firstFilled(
                    data_get($profile, 'reference_card_no'),
                    data_get($profileNested, 'reference_card_no'),
                    data_get($other, 'reference_card_no')
                );
                $referenceMobile = $firstFilled(
                    data_get($profile, 'reference_mobile'),
                    data_get($profileNested, 'reference_mobile'),
                    data_get($other, 'reference_mobile')
                );
                $nomineeMobile = $firstFilled(
                    data_get($nominee, 'nominee_mobile'),
                    data_get($nominee, 'mobile'),
                    data_get($nominee, 'nominee_info.nominee_mobile'),
                    data_get($other, 'nominee_mobile'),
                    data_get($other, 'nominee_info.nominee_mobile')
                );
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
                <td>{{ $designationName }}</td>
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
                <td>{{ $referenceDesignation }}</td>
                <td>{{ $referenceCardNo }}</td>
                <td>{{ $referenceMobile }}</td>
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
                <td>{{ $nomineeMobile }}</td>
                <td>{{ $employee->nominee_relation ?? 'N/A' }}</td>
                <td>{{ $employee->nominee_age ?? 'N/A' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="61" class="text-center">No employee found.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
