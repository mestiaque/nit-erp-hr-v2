@extends('admin.layouts.app')

@section('title')
<title>Employee Profile — {{ $employee->name }}</title>
@endsection

@push('css')
<style>
    .emp-avatar {
        width: 90px; height: 90px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #dee2e6;
    }
    .emp-avatar-placeholder {
        width: 90px; height: 90px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex; align-items: center; justify-content: center;
        border: 3px solid #dee2e6;
        font-size: 32px; color: #adb5bd;
    }
    .info-table th { width: 170px; font-weight: 600; color: #495057; white-space: nowrap; }
    .info-table td { color: #212529; }
    .tab-section { padding: 20px; }
    .nav-tabs .nav-link { font-size: 13px; padding: 7px 12px; }
    .status-badge { font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
    /* Tab pane visibility — direct child so Bootstrap selector matches */
    .tab-content > .tab-pane { display: none; }
    .tab-content > .tab-pane.active { display: block; }
    .doc-card { border: 1px solid #e3e6ea; border-radius: 8px; overflow: hidden; transition: box-shadow .15s; }
    .doc-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,.1); }
    .doc-thumb { height: 100px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; overflow: hidden; }
    .doc-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .att-present { background: #d4edda !important; }
    .att-absent  { background: #f8d7da !important; }
    .att-late    { background: #fff3cd !important; }
    .att-holiday { background: #cce5ff !important; }
    .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; border-bottom: 2px solid #dee2e6; padding-bottom: 6px; margin-bottom: 14px; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1 p-3">

    {{-- Alerts --}}
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>@endif

    {{-- Header Card --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex align-items-center" style="gap: 18px; flex-wrap: wrap;">
                @php
                    $imgSrc = method_exists($employee, 'image') ? $employee->image('md') : null;
                @endphp
                @if($imgSrc && $imgSrc !== 'medies/profile.png')
                    <img src="{{ asset($imgSrc) }}" alt="Photo" class="emp-avatar">
                @else
                    <div class="emp-avatar-placeholder"><i class="fa-solid fa-user"></i></div>
                @endif
                <div class="flex-grow-1">
                    <h4 class="mb-1 font-weight-bold">{{ $employee->name }}</h4>
                    <div class="d-flex flex-wrap" style="gap: 10px; font-size: 13px; color: #555;">
                        <span><i class="fa-solid fa-id-badge me-1"></i> {{ $employee->employee_id ?? '—' }}</span>
                        <span><i class="fa-solid fa-building me-1"></i> {{ optional($employee->department)->name ?? '—' }}</span>
                        <span><i class="fa-solid fa-briefcase me-1"></i> {{ optional($employee->designation)->name ?? '—' }}</span>
                        <span><i class="fa-solid fa-calendar-check me-1"></i> Join: {{ optional($employee->joining_date)->format('d M Y') ?? $employee->join_date ?? '—' }}</span>
                        @php
                            $statusLabel = $employee->status ?? 'regular';
                            $statusColor = match(strtolower($statusLabel)) {
                                'lefty','resign','transfer' => 'danger',
                                default => 'success',
                            };
                        @endphp
                        <span class="badge badge-{{ $statusColor }} status-badge">{{ ucfirst($statusLabel) }}</span>
                        @if((string)($employee->is_active ?? '') === '0' || $employee->is_active === false)
                            <span class="badge badge-secondary status-badge">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 8px;">
                    <a href="{{ route('hr-center.employees.index') }}" class="btn btn-light btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                    {{-- <a href="{{ route('hr-center.employees.increments.page', $employee->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit Salary History"><i class="fa-solid fa-pen-to-square"></i></a> --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="empTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-profile">Profile</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-basic-info">Personal Info</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-address">Address</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-nominee">Nominee</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-age">Age Verification</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-salary">Salary</a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-leaves">Leave <span class="badge badge-light ml-1">{{ count($leaves) }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-increments">Salary History <span class="badge badge-light ml-1">{{ count($increments) }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-transactions">Advance / Txn <span class="badge badge-light ml-1">{{ count($transactions) }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-documents">Documents <span class="badge badge-light ml-1">{{ count($documents) }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-attendance">Attendance <span class="badge badge-light ml-1">{{ count($attendances) }}</span></a></li>
    </ul>

    <div class="tab-content card border-top-0 rounded-0 rounded-bottom">

            {{-- ===== TAB 1: PROFILE ===== --}}
            <div class="tab-pane active tab-section" id="tab-profile">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Employee Profile</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#EditEmployeeModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Employee ID</th><td>: {{ $employee->employee_id ?? '—' }}</td></tr>
                            <tr><th>Name</th><td>: {{ $employee->name ?? '—' }}</td></tr>
                            <tr><th>Name (Bangla)</th><td>: {{ $employee->bn_name ?? '—' }}</td></tr>
                            <tr><th>Classification</th><td>: {{ optional($employee->classification)->name ?? '—' }}</td></tr>
                            <tr><th>Department</th><td>: {{ optional($employee->department)->name ?? '—' }}</td></tr>
                            <tr><th>Section</th><td>: {{ optional($employee->section)->name ?? '—' }}</td></tr>
                            <tr><th>Sub Section</th><td>: {{ optional($employee->subSection)->name ?? '—' }}</td></tr>
                            <tr><th>Designation</th><td>: {{ optional($employee->designation)->name ?? '—' }}</td></tr>
                            <tr><th>Shift</th><td>: {{ optional($employee->shift)->name ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Working Place</th><td>: {{ optional($employee->workingPlace)->name ?? '—' }}</td></tr>
                            <tr><th>Line / Block</th><td>: {{ optional($employee->floorLine)->line_name ?? '—' }}</td></tr>
                            <tr><th>Join Date</th><td>: {{ optional($employee->joining_date)->format('d M Y') ?? $employee->join_date ?? '—' }}</td></tr>
                            <tr><th>Weekend</th><td>: {{ $employee->weekend ?? '—' }}</td></tr>
                            <tr><th>Mobile</th><td>: {{ $employee->personal_contact ?? '—' }}</td></tr>
                            <tr><th>Emergency Contact</th><td>: {{ $employee->emergency_contact ?? '—' }}</td></tr>
                            <tr><th>Status</th><td>: <span class="badge badge-{{ $statusColor }}">{{ ucfirst($statusLabel) }}</span></td></tr>
                            <tr><th>Active</th><td>:
                                @if((string)($employee->is_active ?? '1') !== '0')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td></tr>
                            <tr><th>Comp 1</th><td>: {{ $employee->comp_one ? 'Yes' : 'No' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 2: PERSONAL INFO ===== --}}
            <div class="tab-pane tab-section" id="tab-basic-info">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Personal Information</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#BasicInfoModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                @php
                    $bi = $employee->basicInfo;
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Father's Name</th><td>: {{ $bi?->father_name ?? '—' }}</td></tr>
                            <tr><th>Father's Name (BN)</th><td>: {{ $bi?->bn_father_name ?? '—' }}</td></tr>
                            <tr><th>Mother's Name</th><td>: {{ $bi?->mother_name ?? '—' }}</td></tr>
                            <tr><th>Mother's Name (BN)</th><td>: {{ $bi?->bn_mother_name ?? '—' }}</td></tr>
                            <tr><th>Spouse Name</th><td>: {{ $bi?->spouse_name ?? '—' }}</td></tr>
                            <tr><th>Spouse Name (BN)</th><td>: {{ $bi?->bn_spouse_name ?? '—' }}</td></tr>
                            <tr><th>Gender</th><td>: {{ $employee->gender ?? '—' }}</td></tr>
                            <tr><th>Marital Status</th><td>: {{ $employee->marital_status ?? '—' }}</td></tr>
                            <tr><th>Religion</th><td>: {{ $employee->religion ?? '—' }}</td></tr>
                            <tr><th>Date of Birth</th><td>: {{ $bi?->birth_date ? \Carbon\Carbon::parse($bi->birth_date)->format('d M Y') : '—' }}</td></tr>
                            <tr><th>Blood Group</th><td>: {{ $bi?->blood_group ?? '—' }}</td></tr>
                            <tr><th>Boys / Girls</th><td>: {{ $bi?->children_boys ?? 0 }} / {{ $bi?->children_girls ?? 0 }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>NID No.</th><td>: {{ $bi?->national_id_no ?? '—' }}</td></tr>
                            <tr><th>Birth Reg. No.</th><td>: {{ $bi?->birth_registration_no ?? '—' }}</td></tr>
                            <tr><th>Passport No.</th><td>: {{ $bi?->passport_no ?? '—' }}</td></tr>
                            <tr><th>Driving License</th><td>: {{ $bi?->driving_license_no ?? '—' }}</td></tr>
                            <tr><th>Nationality</th><td>: {{ $employee->nationality ?? '—' }}</td></tr>
                            <tr><th>Education</th><td>: {{ $bi?->educational_experience ?? '—' }}</td></tr>
                            <tr><th>Education (BN)</th><td>: {{ $bi?->bn_educational_experience ?? '—' }}</td></tr>
                            <tr><th>Job Experience</th><td>: {{ $bi?->job_experience ?? '—' }}</td></tr>
                            <tr><th>Prev. Organization</th><td>: {{ $bi?->previous_organization ?? '—' }}</td></tr>
                            <tr><th>Reference Name</th><td>: {{ $bi?->reference_name ?? '—' }}</td></tr>
                            <tr><th>Reference Mobile</th><td>: {{ $bi?->reference_mobile_no ?? '—' }}</td></tr>
                            <tr><th>Special ID Sign</th><td>: {{ $bi?->special_id_sign ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 3: ADDRESS ===== --}}
            <div class="tab-pane tab-section" id="tab-address">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Address Information</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#AddressModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                @php
                    $perm = $employee->permanentAddress;
                    $pres = $employee->presentAddress;
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <div class="section-title">Permanent Address</div>
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>District</th><td>: {{ $employee->permanent_district ?? '—' }}</td></tr>
                            <tr><th>Upazila/Thana</th><td>: {{ $employee->permanent_upazila ?? '—' }}</td></tr>
                            <tr><th>Post Office</th><td>: {{ $perm?->post_office ?? '—' }}</td></tr>
                            <tr><th>Post Office (BN)</th><td>: {{ $perm?->bn_post_office ?? '—' }}</td></tr>
                            <tr><th>Village</th><td>: {{ $perm?->village ?? '—' }}</td></tr>
                            <tr><th>Village (BN)</th><td>: {{ $perm?->bn_village ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div class="section-title">Present Address</div>
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>District</th><td>: {{ $employee->present_district ?? '—' }}</td></tr>
                            <tr><th>Upazila/Thana</th><td>: {{ $employee->present_upazila ?? '—' }}</td></tr>
                            <tr><th>Post Office</th><td>: {{ $pres?->post_office ?? '—' }}</td></tr>
                            <tr><th>Post Office (BN)</th><td>: {{ $pres?->bn_post_office ?? '—' }}</td></tr>
                            <tr><th>Village</th><td>: {{ $pres?->village ?? '—' }}</td></tr>
                            <tr><th>Village (BN)</th><td>: {{ $pres?->bn_village ?? '—' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 4: NOMINEE ===== --}}
            <div class="tab-pane tab-section" id="tab-nominee">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Nominee Information</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#NomineeModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                @php
                    $nom = $employee->nomineeRecord;
                @endphp
                @if($nom)
                <div class="row">
                    <div class="col-md-2 mb-3">
                        @if($nom->photo)
                            <img src="{{ asset($nom->photo) }}" alt="Nominee Photo" style="width:100px;height:100px;object-fit:cover;border:1px solid #dee2e6;border-radius:6px;">
                        @else
                            <div style="width:100px;height:100px;background:#e9ecef;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:28px;color:#adb5bd;border:1px solid #dee2e6;"><i class="fa-solid fa-user"></i></div>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Name</th><td>: {{ $nom->name ?? '—' }}</td></tr>
                            <tr><th>Name (BN)</th><td>: {{ $nom->bn_name ?? '—' }}</td></tr>
                            <tr><th>Relation</th><td>: {{ $nom->relation ?? '—' }}</td></tr>
                            <tr><th>Relation (BN)</th><td>: {{ $nom->bn_relation ?? '—' }}</td></tr>
                            <tr><th>Age</th><td>: {{ $nom->age ?? '—' }}</td></tr>
                            <tr><th>Mobile No.</th><td>: {{ $nom->mobile_no ?? '—' }}</td></tr>
                            <tr><th>NID No.</th><td>: {{ $nom->nid_no ?? '—' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>District</th><td>: {{ optional($nom->district)->name ?? '—' }}</td></tr>
                            <tr><th>Po. Station</th><td>: {{ optional($nom->policeStation)->name ?? '—' }}</td></tr>
                            <tr><th>Post Office</th><td>: {{ $nom->post_office ?? '—' }}</td></tr>
                            <tr><th>Village</th><td>: {{ $nom->village ?? '—' }}</td></tr>
                            <tr><th>Net Payment</th><td>: {{ number_format((float)($nom->net_payment ?? 0), 2) }}</td></tr>
                            <tr><th>Provident Fund</th><td>: {{ number_format((float)($nom->provident_fund ?? 0), 2) }}</td></tr>
                            <tr><th>Insurance</th><td>: {{ number_format((float)($nom->insurance ?? 0), 2) }}</td></tr>
                        </table>
                    </div>
                </div>
                @else
                    <div class="text-muted text-center py-4"><i class="fa-solid fa-people-arrows fa-2x mb-2 d-block"></i>No nominee information found.</div>
                @endif
            </div>

            {{-- ===== TAB 5: AGE VERIFICATION ===== --}}
            <div class="tab-pane tab-section" id="tab-age">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Age Verification</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#AgeModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                @php
                    $av = $employee->ageVerification;
                @endphp
                @if($av)
                <table class="table table-sm table-borderless info-table" style="max-width:450px">
                    <tr><th>Age (Years)</th><td>: {{ $av->age_years ?? '—' }}</td></tr>
                    <tr><th>Verified Date</th><td>: {{ $av->verified_date ? \Carbon\Carbon::parse($av->verified_date)->format('d M Y') : '—' }}</td></tr>
                    <tr><th>Physical Ability</th><td>: {{ $av->physical_ability ?? '—' }}</td></tr>
                    <tr><th>Identification Mark</th><td>: {{ $av->identification_mark ?? '—' }}</td></tr>
                    <tr><th>Status</th><td>: {{ $av->status ? 'Active' : 'Inactive' }}</td></tr>
                </table>
                @else
                    <div class="text-muted text-center py-4"><i class="fa-solid fa-id-card fa-2x mb-2 d-block"></i>No age verification record found.</div>
                @endif
            </div>

            {{-- ===== TAB 6: SALARY ===== --}}
            <div class="tab-pane tab-section" id="tab-salary">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Salary Information</div>
                    {{-- <a href="javascript:void(0)" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#SalaryModal_{{ $employee->id }}"><i class="fa-solid fa-pen me-1"></i> Edit</a> --}}
                </div>
                @php
                    $si = $employee->salaryInfo;
                @endphp
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Gross Salary</th><td>: <strong>{{ number_format((float)($si?->gross_salary ?? 0), 2) }}</strong></td></tr>
                            <tr><th>Gross Salary (Comp-1)</th><td>: {{ number_format((float)($si?->gross_salary_comp1 ?? 0), 2) }}</td></tr>
                            <tr><th>Gross Salary (Comp-2)</th><td>: {{ number_format((float)($si?->gross_salary_comp2 ?? 0), 2) }}</td></tr>
                            <tr><th>Payment Mode</th><td>: {{ $employee->salary_type ?? '—' }}</td></tr>
                            <tr><th>Bank / Phone No.</th><td>: {{ $si?->bank_ac_or_phone ?? '—' }}</td></tr>
                            <tr><th>Car & Fuel</th><td>: {{ number_format((float)($si?->car_fuel ?? 0), 2) }}</td></tr>
                            <tr><th>Phone & Internet</th><td>: {{ number_format((float)($si?->phone_internet ?? 0), 2) }}</td></tr>
                            <tr><th>Extra Facility</th><td>: {{ number_format((float)($si?->extra_facility ?? 0), 2) }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless info-table">
                            <tr><th>Attendance Bonus</th><td>: {{ number_format((float)($si?->attendance_bonus ?? 0), 2) }}</td></tr>
                            <tr><th>Tiffin Allowance</th><td>: {{ number_format((float)($si?->tiffin_allowance ?? 0), 2) }}</td></tr>
                            <tr><th>Night Allowance</th><td>: {{ number_format((float)($si?->night_allowance ?? 0), 2) }}</td></tr>
                            <tr><th>Dinner Allowance</th><td>: {{ number_format((float)($si?->dinner_allowance ?? 0), 2) }}</td></tr>
                            <tr><th>Tax</th><td>: {{ number_format((float)($si?->tax ?? 0), 2) }} ({{ $si?->tax_calculate_by ?? '%' }})</td></tr>
                            <tr><th>Effective Date</th><td>: {{ $si?->effective_date ?? '—' }}</td></tr>
                            <tr><th>Status</th><td>: {{ ucfirst($si?->salary_info_status ?? 'active') }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===== TAB 7: LEAVES ===== --}}
            <div class="tab-pane tab-section" id="tab-leaves">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Leave Records</div>
                    <a href="{{ route('hr-center.employees.leaves.page', $employee->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen me-1"></i> Manage</a>
                </div>

                {{-- Leave Summary --}}
                @if($leaveSummary->isNotEmpty())
                <div class="mb-4">
                    <div class="section-title">Leave Balance Summary</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr><th>Code</th><th>Leave Type</th><th>Total Days</th><th>Taken Days</th><th>Available Days</th></tr>
                            </thead>
                            <tbody>
                            @foreach($leaveSummary as $ls)
                                <tr>
                                    <td>{{ $ls['code'] ?? '—' }}</td>
                                    <td>{{ $ls['name'] ?? '—' }}</td>
                                    <td>{{ $ls['remaining_days'] }}</td>
                                    <td>{{ $ls['taken_days'] }}</td>
                                    <td><strong>{{ $ls['available_days'] }}</strong></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Leave History --}}
                <div class="section-title">Leave History</div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Leave Type</th><th>Code</th><th>From</th><th>To</th><th>Days</th><th>Purpose</th><th>Applied On</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse($leaves as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['leave_type'] ?? '—' }}</td>
                                <td>{{ $row['leave_code'] ?? '—' }}</td>
                                <td>{{ $row['leave_from'] ?? '—' }}</td>
                                <td>{{ $row['leave_to'] ?? '—' }}</td>
                                <td>{{ $row['total_days'] ?? 0 }}</td>
                                <td>{{ $row['purpose'] ?? '—' }}</td>
                                <td>{{ $row['application_date'] ?? '—' }}</td>
                                <td>{{ $row['status'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No leave records found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== TAB 8: SALARY HISTORY / INCREMENTS ===== --}}
            <div class="tab-pane tab-section" id="tab-increments">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Salary Increment History</div>
                    <a href="{{ route('hr-center.employees.increments.page', $employee->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen me-1"></i> Manage</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Increment Date</th><th>Previous Salary</th><th>Increment Amount</th><th>New Salary</th></tr>
                        </thead>
                        <tbody>
                        @forelse($increments as $i => $inc)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $inc->increment_date ?? '—' }}</td>
                                <td>{{ number_format((float)($inc->previous_salary ?? 0), 2) }}</td>
                                <td class="text-success font-weight-bold">+{{ number_format((float)($inc->increment_amount ?? 0), 2) }}</td>
                                <td class="font-weight-bold">{{ number_format((float)($inc->new_salary ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No increment records found.</td></tr>
                        @endforelse
                        </tbody>
                        @if($increments->isNotEmpty())
                        <tfoot class="thead-light">
                            <tr>
                                <th colspan="3">Total Increment</th>
                                <th class="text-success">+{{ number_format($increments->sum('increment_amount'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- ===== TAB 9: ADVANCE / TRANSACTIONS ===== --}}
            <div class="tab-pane tab-section" id="tab-transactions">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Advance Salary & Transactions</div>
                    <a href="{{ route('hr-center.employees.earnings.page', $employee->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen me-1"></i> Manage</a>
                </div>

                {{-- Summary Cards --}}
                <div class="row mb-4">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-warning text-center py-3">
                            <div class="font-weight-bold text-warning">{{ number_format($txnTotalAdvance, 2) }}</div>
                            <small class="text-muted">Total Advance / IOU</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-success text-center py-3">
                            <div class="font-weight-bold text-success">{{ number_format($txnTotalEarnings, 2) }}</div>
                            <small class="text-muted">Total Earnings</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-danger text-center py-3">
                            <div class="font-weight-bold text-danger">{{ number_format($txnTotalDeductions, 2) }}</div>
                            <small class="text-muted">Total Deductions</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card border-info text-center py-3">
                            <div class="font-weight-bold text-info">{{ number_format($txnTotalOt, 2) }}</div>
                            <small class="text-muted">Total OT Adjust</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Date</th><th>Month</th><th>Advance / IOU</th><th>Earnings</th><th>Deductions</th><th>OT Adjust</th><th>Day Adjust</th><th>Remarks</th></tr>
                        </thead>
                        <tbody>
                        @forelse($transactions as $i => $txn)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $txn->txn_date ?? '—' }}</td>
                                <td>{{ $txn->txn_date ? \Carbon\Carbon::parse($txn->txn_date)->format('M Y') : '—' }}</td>
                                <td>{{ $txn->advance_iou ? number_format((float)$txn->advance_iou, 2) : '—' }}</td>
                                <td class="text-success">{{ $txn->earnings ? number_format((float)$txn->earnings, 2) : '—' }}</td>
                                <td class="text-danger">{{ $txn->deductions ? number_format((float)$txn->deductions, 2) : '—' }}</td>
                                <td>{{ $txn->ot_adjust ? number_format((float)$txn->ot_adjust, 2) : '—' }}</td>
                                <td>{{ $txn->day_adjust ? number_format((float)$txn->day_adjust, 2) : '—' }}</td>
                                <td>{{ $txn->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">No transaction records found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== TAB 10: DOCUMENTS ===== --}}
            <div class="tab-pane tab-section" id="tab-documents">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Documents</div>
                    <a href="{{ route('hr-center.employees.documents.page', $employee->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-upload me-1"></i> Upload / Manage</a>
                </div>
                @if($documents->isEmpty())
                    <div class="text-center text-muted py-4"><i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>No documents uploaded.</div>
                @else
                    @foreach($documents->groupBy('title') as $docTitle => $docGroup)
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-2" style="gap:8px">
                            <i class="fa-solid fa-folder text-warning"></i>
                            <strong>{{ $docTitle }}</strong>
                            <span class="badge badge-secondary">{{ $docGroup->count() }} file</span>
                        </div>
                        <div class="row g-2">
                            @foreach($docGroup as $doc)
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <div class="doc-card">
                                    <div class="doc-thumb">
                                        @if($doc->is_image ?? in_array(strtolower($doc->file_type ?? ''), ['jpg','jpeg','png','gif','webp']))
                                            <img src="{{ $doc->url ?? asset('storage/'.$doc->file_path) }}" alt="{{ $doc->file_name }}" loading="lazy">
                                        @else
                                            <i class="fa-solid fa-file-pdf" style="font-size:38px;color:#dc3545;"></i>
                                        @endif
                                    </div>
                                    <div class="p-2">
                                        <div class="small text-truncate" title="{{ $doc->file_name }}">{{ $doc->file_name }}</div>
                                        <a href="{{ $doc->url ?? asset('storage/'.$doc->file_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm mt-1 w-100" style="font-size:11px"><i class="fa-solid fa-eye"></i> View</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- ===== TAB 11: ATTENDANCE ===== --}}
            <div class="tab-pane tab-section" id="tab-attendance">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">Attendance Records</div>
                    <form method="get" action="{{ route('hr-center.employees.show', $employee->id) }}" class="d-flex align-items-center" style="gap:6px">
                        <input type="hidden" name="_anchor" value="tab-attendance">
                        <input type="month" name="att_month" value="{{ request('att_month', now()->format('Y-m')) }}" class="form-control form-control-sm" style="width:160px">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('hr-center.employees.show', $employee->id) }}" class="btn btn-sm btn-light">Reset</a>
                    </form>
                </div>

                {{-- Attendance Summary --}}
                <div class="row mb-3">
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-success text-center py-2">
                            <div class="font-weight-bold text-success" style="font-size:20px">{{ $attPresent }}</div>
                            <small class="text-muted">Present</small>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-danger text-center py-2">
                            <div class="font-weight-bold text-danger" style="font-size:20px">{{ $attAbsent }}</div>
                            <small class="text-muted">Absent</small>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-warning text-center py-2">
                            <div class="font-weight-bold text-warning" style="font-size:20px">{{ $attLate }}</div>
                            <small class="text-muted">Late</small>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-info text-center py-2">
                            <div class="font-weight-bold text-info" style="font-size:20px">{{ $attTotal }}</div>
                            <small class="text-muted">Total Days</small>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-secondary text-center py-2">
                            <div class="font-weight-bold" style="font-size:16px">{{ floor($totalWkMin/60) }}h {{ $totalWkMin%60 }}m</div>
                            <small class="text-muted">Working Time</small>
                        </div>
                    </div>
                    <div class="col-4 col-md-2 mb-2">
                        <div class="card border-secondary text-center py-2">
                            <div class="font-weight-bold" style="font-size:16px">{{ floor($totalOtMin/60) }}h {{ $totalOtMin%60 }}m</div>
                            <small class="text-muted">OT Time</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr><th>#</th><th>Date</th><th>Day</th><th>In Time</th><th>Out Time</th><th>Working</th><th>OT</th><th>Status</th><th>Via</th><th>Remarks</th></tr>
                        </thead>
                        <tbody>
                        @forelse($attendances as $i => $att)
                            @php
                                $attStatus = strtolower($att->status ?? '');
                                $rowClass = $attStatus === 'present' ? 'att-present'
                                    : ($attStatus === 'absent'  ? 'att-absent'
                                    : ($attStatus === 'late'    ? 'att-late'
                                    : (in_array($attStatus, ['holiday','weekend']) ? 'att-holiday' : '')));
                                $attBadge = $attStatus === 'present' ? 'success'
                                    : ($attStatus === 'absent' ? 'danger'
                                    : ($attStatus === 'late'   ? 'warning' : 'secondary'));
                                $wkMin = (int)($att->total_working_minute ?? 0);
                                $otMin = (int)($att->total_ot_minute ?? 0);
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $att->date }}</td>
                                <td>{{ \Carbon\Carbon::parse($att->date)->format('D') }}</td>
                                <td>{{ $att->in_time ?? '—' }}</td>
                                <td>{{ $att->out_time ?? '—' }}</td>
                                <td>{{ $wkMin ? floor($wkMin/60).'h '.($wkMin%60).'m' : '—' }}</td>
                                <td>{{ $otMin ? floor($otMin/60).'h '.($otMin%60).'m' : '—' }}</td>
                                <td>
                                    <span class="badge badge-{{ $attBadge }}">{{ $att->status ?? '—' }}</span>
                                </td>
                                <td>{{ $att->via ?? '—' }}</td>
                                <td>{{ $att->remarks ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No attendance records found for this period.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Showing attendance from <strong>{{ $attendanceDateFrom }}</strong> to <strong>{{ $attendanceDateTo }}</strong></small>
            </div>

    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    // Restore active tab from URL hash or anchor param
    var hash = window.location.hash;
    if (!hash) {
        var params = new URLSearchParams(window.location.search);
        var anchor = params.get('_anchor');
        if (anchor) hash = '#' + anchor;
    }
    if (hash) {
        var tab = document.querySelector('#empTabs a[href="' + hash + '"]');
        if (tab) {
            $(tab).tab('show');
        }
    }

    // Update hash on tab change
    $('#empTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        history.replaceState(null, null, e.target.getAttribute('href'));
    });
})();
</script>
@endpush
