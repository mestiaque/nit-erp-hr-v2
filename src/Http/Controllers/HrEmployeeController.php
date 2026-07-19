<?php

namespace ME\Hr\Http\Controllers;

use ME\Hr\Models\HrEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use ME\Hr\Models\HrClassification;
use ME\Hr\Models\HrDepartment;
use ME\Hr\Models\HrDesignation;
use ME\Hr\Models\HrEmployeeLeave;
use ME\Hr\Models\HrEmployeeAddress;
use ME\Hr\Models\HrEmployeeBasicInfo;
use ME\Hr\Models\HrEmployeeSalaryIncrement;
use ME\Hr\Models\HrEmployeeSalaryInfo;
use ME\Hr\Models\HrFloorLine;
use ME\Hr\Models\HrGeoLocation;
use ME\Hr\Models\HrAttendance;
use ME\Hr\Models\HrLeaveInfo;
use ME\Hr\Models\HrMaritalStatus;
use ME\Hr\Models\HrPaymentMethod;
use ME\Hr\Models\HrReligion;
use ME\Hr\Models\HrSection;
use ME\Hr\Models\HrSex;
use ME\Hr\Models\HrShift;
use ME\Hr\Models\HrSubSection;
use ME\Hr\Models\HrWorkingPlace;
use ME\Hr\Models\HrFactory;
use ME\Hr\Models\HrHoliday;
use ME\Hr\Models\HrRegularToWeekend;
use ME\Hr\Models\HrEmployeeNominee;
use ME\Hr\Models\HrEmployeeAgeVerification;
use ME\Hr\Models\HrEmployeeSeparation;
use ME\Hr\Models\HrEmployeeFinalSettlement;
use ME\Hr\Models\HrEmployeeOtherTransaction;
use ME\Hr\Models\HrEmployeeDocument;
use ME\Hr\Models\HrEmployeeGatePass;
use ME\Hr\Models\HrEmployeeAsset;
use Illuminate\Support\Facades\Storage;

class HrEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = HrEmployee::query();

        if ($request->filled('emp_id')) {
            $query->where('employee_id', 'like', '%' . trim((string) $request->emp_id) . '%');
        }

        if ($request->filled('name_filter')) {
            $query->where('name', 'like', '%' . trim((string) $request->name_filter) . '%');
        }

        if ($request->filled('joining_date')) {
            $query->whereDate('join_date', $request->joining_date);
        }

        if ($request->filled('joining_date_from')) {
            $query->whereDate('join_date', '>=', $request->joining_date_from);
        }

        if ($request->filled('joining_date_to')) {
            $query->whereDate('join_date', '<=', $request->joining_date_to);
        }

        if ($request->filled('contact')) {
            $query->where('personal_contact', 'like', '%' . trim((string) $request->contact) . '%');
        }

        if ($request->filled('classification_id')) {
            $this->applyIntegerFilter($query, 'classification_id', (int) $request->classification_id);
        }

        if ($request->filled('department_id')) {
            $this->applyIntegerFilter($query, 'department_id', (int) $request->department_id);
        }

        if ($request->filled('section_id')) {
            $this->applyIntegerFilter($query, 'section_id', (int) $request->section_id);
        }

        if ($request->filled('sub_section_id')) {
            $this->applyIntegerFilter($query, 'sub_section_id', (int) $request->sub_section_id, 'sub_section_id');
        }

        if ($request->filled('designation_id')) {
            $this->applyIntegerFilter($query, 'designation_id', (int) $request->designation_id);
        }

        if ($request->filled('shift_id')) {
            $this->applyIntegerFilter($query, 'shift_id', (int) $request->shift_id);
        }

        if ($request->filled('working_place_id')) {
            $this->applyIntegerFilter($query, 'working_place_id', (int) $request->working_place_id, 'working_place_id');
        }

        if ($request->filled('line_id')) {
            $this->applyIntegerFilter($query, 'floor_line_id', (int) $request->line_id);
        }

        if ($request->filled('weekend')) {
            $this->applyStringFilter($query, 'weekend', (string) $request->weekend, 'weekend');
        }

        if ($request->filled('status')) {
            $employmentStatus = (string) $request->status;
            $query->where(function ($builder) use ($employmentStatus) {
                if ($employmentStatus === 'regular') {
                    $builder->whereNull('employment_status')
                        ->orWhere('employment_status', '')
                        ->orWhere('employment_status', 'regular');

                    return;
                }

                $builder->where('employment_status', $employmentStatus);
                if ($employmentStatus === 'lefty') {
                    $builder->orWhere('employment_status', 'left');
                }
                if ($employmentStatus === 'resign') {
                    $builder->orWhere('employment_status', 'resigned');
                }
            });
        }

        if ($request->filled('is_active')) {
            $query->where('status', $this->normalizeUserStatus((string) $request->is_active));
        }

        $employees = $query->with('ageVerification')->naturalOrderById()->paginate(20)->appends($request->query());

        $options = $this->options();
        $basicInfoOptions = [
            'marital_status' => collect(data_get($options, 'maritalStatuses', []))->pluck('name')->filter()->values()->all(),
            'gender' => collect(data_get($options, 'sexes', []))->pluck('name')->filter()->values()->all(),
            'religion' => collect(data_get($options, 'religions', []))->pluck('name')->filter()->values()->all(),
        ];

        return view('hr::employees.index', [
            'employees' => $employees,
            'request' => $request,
            'options' => $options,
            'basicInfoOptions' => $basicInfoOptions,
            'newEmployee' => new HrEmployee(),
        ]);
    }
     
    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => 'required|string|max:191',
            'bn_name' => 'nullable|string|max:191',
            'employee_id' => 'required|string|max:100|unique:hr_employees,employee_id',
            'joining_date' => 'nullable|date',
            'employee_type' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'sub_section_id' => 'nullable|integer',
            'designation_id' => 'nullable|integer',
            'working_place_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'line_number' => 'nullable|integer',
            'weekend' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:30',
            'emergency_mobile' => 'nullable|string|max:30',
            'is_active_01' => 'nullable|in:0,1',
            'is_active_02' => 'nullable|in:0,1',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);
        $payload['status'] = $this->normalizeUserStatus((string) $payload['status']);
        $payload = $this->mapLegacyEmployeePayloadToHrColumns($payload);

        if (!empty($payload['designation_id'])) {
            $this->validateApprovedManpower((int) $payload['designation_id']);
        }

        $employee = new HrEmployee();
        $employee->fill($payload);
        $this->applyExtendedProfileFields($employee, $payload);
        $employee->created_by = Auth::id();
        $employee->setTypes('employee');
        $employee->save();
        $this->syncDesignationSalaryToEmployee($employee, $payload, true);

        return redirect()->route('hr-center.employees.index')->with('success', 'Employee created successfully.');
    }

    public function updateProfile(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'name' => 'required|string|max:191',
            'bn_name' => 'nullable|string|max:191',
            'employee_id' => 'required|string|max:100|unique:hr_employees,employee_id,' . $employee->id,
            'joining_date' => 'nullable|date',
            'employee_type' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'sub_section_id' => 'nullable|integer',
            'designation_id' => 'nullable|integer',
            'working_place_id' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'line_number' => 'nullable|integer',
            'weekend' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:30',
            'emergency_mobile' => 'nullable|string|max:30',
            'is_active_01' => 'nullable|in:0,1',
            'is_active_02' => 'nullable|in:0,1',
            'status' => 'required|in:active,inactive',
        ]);
        $payload['status'] = $this->normalizeUserStatus((string) $payload['status']);
        $payload = $this->mapLegacyEmployeePayloadToHrColumns($payload);

        $previousDesignationId = (int) ($employee->designation_id ?? 0);
        $employee->fill($payload);
        $this->applyExtendedProfileFields($employee, $payload);
        $designationChanged = $previousDesignationId !== (int) ($payload['designation_id'] ?? 0);
        $this->syncDesignationSalaryToEmployee($employee, $payload, $designationChanged);
        $employee->setTypes('employee');
        $employee->save();

        if ($request->hasFile('profile_image')) {
            uploadFile($request->file('profile_image'), $employee->id, 6, 1, Auth::id());
        }

        return redirect()->route('hr-center.employees.index')->with('success', 'Employee profile updated.');
    }

    public function updateSalary(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'gross_salary' => 'nullable|numeric|min:0',
            'gross_salary_comp_1' => 'nullable|numeric|min:0',
            'gross_salary_comp_2' => 'nullable|numeric|min:0',
            'salary_type' => 'nullable|string|max:50',
            'bank_or_phone' => 'nullable|string|max:191',
            'car_fuel' => 'nullable|numeric|min:0',
            'phone_internet' => 'nullable|numeric|min:0',
            'extra_facility' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'tax_calculate_by' => 'nullable|in:percent,amount',
            'salary_info_date' => 'nullable|date',
            'salary_info_status' => 'required|in:active,inactive',
        ]);
        $this->upsertSalaryInfo($employee, $payload);
        $employee->setTypes('employee');
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Salary info updated.');
    }

    public function updateAddress(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'permanent_district' => 'nullable|string|max:191',
            'permanent_upazila' => 'nullable|string|max:191',
            'permanent_post_office' => 'nullable|string|max:191',
            'permanent_village' => 'nullable|string|max:191',
            'present_district' => 'nullable|string|max:191',
            'present_upazila' => 'nullable|string|max:191',
            'present_post_office' => 'nullable|string|max:191',
            'present_village' => 'nullable|string|max:191',
            'permanent_post_office_bn' => 'nullable|string|max:191',
            'permanent_village_bn' => 'nullable|string|max:191',
            'present_post_office_bn' => 'nullable|string|max:191',
            'present_village_bn' => 'nullable|string|max:191',
        ]);
        $employee->setTypes('employee');
        $this->upsertAddressInfo($employee, $payload);
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Address info updated.');
    }

    public function updateBasicInfo(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'father_name'         => 'nullable|string|max:191',
            'father_name_bn'      => 'nullable|string|max:191',
            'mother_name'         => 'nullable|string|max:191',
            'mother_name_bn'      => 'nullable|string|max:191',
            'marital_status'      => 'nullable|string|max:50',
            'spouse_name'         => 'nullable|string|max:191',
            'spouse_name_bn'      => 'nullable|string|max:191',
            'gender'              => 'nullable|string|max:20',
            'boys'                => 'nullable|integer|min:0',
            'girls'               => 'nullable|integer|min:0',
            'religion'            => 'nullable|string|max:100',
            'dob'                 => 'nullable|date',
            'blood_group'         => 'nullable|string|max:10',
            'nationality'         => 'nullable|string|max:100',
            'nid_number'          => 'nullable|string|max:100',
            'birth_registration'  => 'nullable|string|max:100',
            'passport_no'         => 'nullable|string|max:100',
            'driving_license'     => 'nullable|string|max:100',
            'distinguished_mark'  => 'nullable|string|max:191',
            'distinguished_mark_bn' => 'nullable|string|max:191',
            'education'           => 'nullable|string|max:191',
            'education_bn'        => 'nullable|string|max:191',
            'reference_1'         => 'nullable|string|max:191',
            'reference_1_bn'      => 'nullable|string|max:191',
            'reference_2'         => 'nullable|string|max:191',
            'reference_2_bn'      => 'nullable|string|max:191',
            'salary_type'         => 'nullable|string|max:50',
            'job_experience'      => 'nullable|string|max:191',
            'job_experience_bn'   => 'nullable|string|max:191',
            'prev_organization'   => 'nullable|string|max:191',
            'prev_organization_bn' => 'nullable|string|max:191',
            'reference_card_no'   => 'nullable|string|max:100',
            'reference_card_no_bn' => 'nullable|string|max:100',
            'reference_mobile'    => 'nullable|string|max:30',
            'reference_mobile_bn' => 'nullable|string|max:30',
        ]);

        $other = $this->otherInfo($employee);
        $currentProfile = data_get($other, 'profile', []);

        $options = $this->options();
        $maritalStatusMap = array_replace([
            'single' => 'অবিবাহিত',
            'unmarried' => 'অবিবাহিত',
            'married' => 'বিবাহিত',
            'divorced' => 'তালাকপ্রাপ্ত',
            'widowed' => 'বিধবা/বিপত্নীক',
            'widow' => 'বিধবা/বিপত্নীক',
            'widower' => 'বিধবা/বিপত্নীক',
        ], $this->banglaMapFromOptions(data_get($options, 'maritalStatuses', []), ['name', 'code']));
        $genderMap = array_replace([
            'male' => 'পুরুষ',
            'female' => 'মহিলা',
            'other' => 'অন্যান্য',
        ], $this->banglaMapFromOptions(data_get($options, 'sexes', []), ['name', 'code']));
        $religionMap = array_replace([
            'islam' => 'ইসলাম',
            'hindu' => 'হিন্দু',
            'buddhist' => 'বৌদ্ধ',
            'christian' => 'খ্রিস্টান',
            'others' => 'অন্যান্য',
            'other' => 'অন্যান্য',
        ], $this->banglaMapFromOptions(data_get($options, 'religions', []), ['name', 'code']));
        $paymentModeMap = array_replace([
            'cash' => 'নগদ',
            'bank' => 'ব্যাংক',
            'mobile banking' => 'মোবাইল ব্যাংকিং',
            'cheque' => 'চেক',
        ], $this->banglaMapFromOptions(data_get($options, 'paymentMethods', []), ['name', 'code']));
        $nationalityMap = array_replace([
            'bangladeshi' => 'বাংলাদেশি',
            'bangladesh' => 'বাংলাদেশ',
            'indian' => 'ভারতীয়',
            'india' => 'ভারত',
            'pakistani' => 'পাকিস্তানি',
            'pakistan' => 'পাকিস্তান',
            'nepali' => 'নেপালি',
            'nepal' => 'নেপাল',
            'bhutanese' => 'ভুটানি',
            'bhutan' => 'ভুটান',
            'sri lankan' => 'শ্রীলঙ্কান',
            'sri lanka' => 'শ্রীলঙ্কা',
        ], $this->banglaMapFromOptions(data_get($options, 'countries', []), ['name', 'code']));

        $payload['marital_status_bn'] = $this->toBanglaLabel(
            $payload['marital_status'] ?? null,
            $maritalStatusMap,
            data_get($currentProfile, 'marital_status_bn')
        );

        $payload['gender_bn'] = $this->toBanglaLabel(
            $payload['gender'] ?? null,
            $genderMap,
            data_get($currentProfile, 'gender_bn')
        );

        $payload['religion_bn'] = $this->toBanglaLabel(
            $payload['religion'] ?? null,
            $religionMap,
            data_get($currentProfile, 'religion_bn')
        );

        $payload['nationality_bn'] = $this->toBanglaLabel(
            $payload['nationality'] ?? null,
            $nationalityMap,
            data_get($currentProfile, 'nationality_bn')
        );

        $payload['salary_type_bn'] = $this->toBanglaLabel(
            $payload['salary_type'] ?? null,
            $paymentModeMap,
            data_get($currentProfile, 'salary_type_bn')
        );

        $profileFields = [
            'job_experience',
            'job_experience_bn',
            'prev_organization',
            'prev_organization_bn',
            'reference_card_no',
            'reference_card_no_bn',
            'reference_mobile',
            'reference_mobile_bn',
            'marital_status_bn',
            'gender_bn',
            'religion_bn',
            'nationality_bn',
            'salary_type_bn',
            'distinguished_mark_bn',
            'education_bn',
            'reference_1_bn',
            'reference_2_bn',
        ];

        $this->upsertBasicInfo($employee, $payload);
        $employee->setTypes('employee');
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Basic info updated.');
    }

    public function updateNominee(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'nominee' => 'nullable|string|max:191',
            'nominee_age' => 'nullable|integer|min:0',
            'nominee_relation' => 'nullable|string|max:100',
            'nominee_district' => 'nullable|string|max:191',
            'nominee_po_station' => 'nullable|string|max:191',
            'nominee_post_office' => 'nullable|string|max:191',
            'nominee_post_office_bn' => 'nullable|string|max:191',
            'nominee_nationality' => 'nullable|string|max:191',
            'nominee_village' => 'nullable|string|max:191',
            'nominee_village_bn' => 'nullable|string|max:191',
            'nominee_nid' => 'nullable|string|max:100',
            'nominee_mobile' => 'nullable|string|max:30',
            'nominee_bn_name' => 'nullable|string|max:191',
            'nominee_relation_bn' => 'nullable|string|max:100',
            'distribution_net_payment' => 'nullable|numeric|min:0|max:100',
            'distribution_provident_fund' => 'nullable|numeric|min:0|max:100',
            'distribution_insurance' => 'nullable|numeric|min:0|max:100',
            'distribution_accident_fine' => 'nullable|numeric|min:0|max:100',
            'distribution_profit' => 'nullable|numeric|min:0|max:100',
            'distribution_others' => 'nullable|numeric|min:0|max:100',
            'nominee_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('nominee_image')) {
            $file = $request->file('nominee_image');
            $folder = public_path('medies/nominees');
            if (!is_dir($folder)) {
                @mkdir($folder, 0755, true);
            }
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($folder, $filename);
            $imagePath = 'medies/nominees/' . $filename;
        }
        $this->upsertNomineeInfo($employee, $payload, $imagePath);
        $employee->setTypes('employee');
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Nominee info updated.');
    }

    public function updateAgeVerification(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'physical_ability' => 'nullable|string|max:191',
            'physical_ability_bn' => 'nullable|string|max:191',
            'distinguished_mark' => 'nullable|string|max:191',
            'distinguished_mark_bn' => 'nullable|string|max:191',
            'verified_age' => 'nullable|integer|min:0',
            'age_verification_date' => 'nullable|date',
        ]);

        $this->upsertAgeVerification($employee, $payload);
        $employee->setTypes('employee');
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Age verification info updated.');
    }

    public function updateResign(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'employment_status' => 'nullable|in:regular,resign,lefty,transfer',
            'resign_remarks' => 'nullable|string|max:500',
            'resign_date' => 'nullable|date',
            'final_settlement_type' => 'nullable|string|max:191',
            'with_paid' => 'nullable|boolean',
        ]);

        $employee->employment_status = $payload['employment_status'] ?? null;
        $employee->exited_at = $payload['resign_date'] ?? null;
        $this->upsertSeparation($employee, $payload, $request->boolean('with_paid'));
        $employee->setTypes('employee');
        $employee->save();

        return redirect()->route('hr-center.employees.index')->with('success', 'Lefty/Resign info updated.');
    }

    public function updateFinalSettlement(Request $request, HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'absent_date' => 'nullable|date',
            'letter_1_date' => 'nullable|date',
            'letter_2_date' => 'nullable|date',
            'letter_3_date' => 'nullable|date',
            'final_settlement_option' => 'nullable|in:1st Letter,2nd Letter,3rd Letter',
            'last_basic_salary' => 'nullable|numeric|min:0',
            'last_gross_salary' => 'nullable|numeric|min:0',
            'service_years' => 'nullable|integer|min:0',
            'unpaid_salary_days' => 'nullable|integer|min:0',
            'unpaid_salary_amount' => 'nullable|numeric|min:0',
            'leave_encashment_days' => 'nullable|integer|min:0',
            'leave_encashment_amount' => 'nullable|numeric|min:0',
            'gratuity_amount' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_earnings' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'net_payable' => 'nullable|numeric',
            'calculation_notes' => 'nullable|string|max:2000',
            'settlement_status' => 'nullable|in:draft,approved,paid',
        ]);

        $this->upsertFinalSettlement($employee, $payload);
        $employee->setTypes('employee');
        $employee->save();

        if ($request->routeIs('hr-center.employees.final-settlement.print')) {
            $designationBn = null;
            $designationEn = null;

            if (!empty($employee->designation_id)) {
                if (Schema::hasTable((new HrDesignation())->getTable())) {
                    $designationRow = HrDesignation::query()->find($employee->designation_id);
                    $designationBn = data_get($designationRow, 'bn_name');
                    $designationEn = data_get($designationRow, 'name');
                }
            }

            return view('hr::employees.print.final-settlement', [
                'employee' => $employee,
                'settlement' => $payload,
                'designation_bn' => $designationBn,
                'designation_en' => $designationEn,
            ]);
        }

        return redirect()->route('hr-center.employees.index')->with('success', 'Final settlement info updated.');
    }

    /**
     * Persist the settlement form and render the payslip-style settlement statement,
     * separate from the disciplinary show-cause/warning/termination letters.
     */
    public function printFinalSettlementStatement(Request $request, HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'absent_date' => 'nullable|date',
            'letter_1_date' => 'nullable|date',
            'letter_2_date' => 'nullable|date',
            'letter_3_date' => 'nullable|date',
            'final_settlement_option' => 'nullable|in:1st Letter,2nd Letter,3rd Letter',
            'last_basic_salary' => 'nullable|numeric|min:0',
            'last_gross_salary' => 'nullable|numeric|min:0',
            'service_years' => 'nullable|integer|min:0',
            'unpaid_salary_days' => 'nullable|integer|min:0',
            'unpaid_salary_amount' => 'nullable|numeric|min:0',
            'leave_encashment_days' => 'nullable|integer|min:0',
            'leave_encashment_amount' => 'nullable|numeric|min:0',
            'gratuity_amount' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_earnings' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'net_payable' => 'nullable|numeric',
            'calculation_notes' => 'nullable|string|max:2000',
            'settlement_status' => 'nullable|in:draft,approved,paid',
        ]);

        $this->upsertFinalSettlement($employee, $payload);
        $employee->setTypes('employee');
        $employee->save();

        $designationBn = null;
        $designationEn = null;
        if (!empty($employee->designation_id) && Schema::hasTable((new HrDesignation())->getTable())) {
            $designationRow = HrDesignation::query()->find($employee->designation_id);
            $designationBn = data_get($designationRow, 'bn_name');
            $designationEn = data_get($designationRow, 'name');
        }

        return view('hr::employees.print.final-settlement-statement', [
            'employee' => $employee,
            'settlement' => $employee->finalSettlement,
            'designation_bn' => $designationBn,
            'designation_en' => $designationEn,
        ]);
    }

    public function incrementsPage(HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $rows = collect();
        $table = (new HrEmployeeSalaryIncrement())->getTable();
        if (Schema::hasTable($table)) {
            $query = HrEmployeeSalaryIncrement::query();
            if (Schema::hasColumn($table, 'user_id')) {
                $query->where('user_id', $employee->id);
            } elseif (Schema::hasColumn($table, 'employee_id')) {
                $query->where('employee_id', $employee->id);
            }
            $rows = $query->latest()->limit(50)->get()->map(function ($row) {
                return [
                    'source' => 'db',
                    'identifier' => (string) ($row->id ?? ''),
                    'previous_salary' => (float) $row->previous_salary,
                    'increment_amount' => (float) $row->increment_amount,
                    'new_salary' => (float) $row->new_salary,
                    'increment_date' => $row->increment_date,
                    'is_locked' => (bool) $row->is_locked,
                ];
            })->values();
        } else {
            $other = $this->otherInfo($employee);
            $rows = collect(data_get($other, 'increments', []))
                ->sortByDesc(function ($row) {
                    return data_get($row, 'increment_date') ?: data_get($row, 'created_at');
                })
                ->values()
                ->map(function ($row, $index) {
                    return [
                        'source' => 'other',
                        'identifier' => (string) $index,
                        'previous_salary' => (float) data_get($row, 'previous_salary', 0),
                        'increment_amount' => (float) data_get($row, 'increment_amount', 0),
                        'new_salary' => (float) data_get($row, 'new_salary', 0),
                        'increment_date' => data_get($row, 'increment_date'),
                    ];
                });
        }

        $options = $this->options();
        $employeeMeta = [
            'classification' => optional(collect($options['classifications'] ?? [])->firstWhere('id', $employee->classification_id))->name,
            'department' => optional(collect($options['departments'] ?? [])->firstWhere('id', $employee->department_id))->name,
            'section' => optional(collect($options['sections'] ?? [])->firstWhere('id', $employee->section_id))->name,
            'designation' => optional(collect($options['designations'] ?? [])->firstWhere('id', $employee->designation_id))->name,
        ];

        return view('hr::employees.pages.increments', compact('employee', 'rows', 'employeeMeta'));
    }

    public function incrementsStore(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);
        $payload = $request->validate([
            'increment_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        $oldIncrement = HrEmployeeSalaryIncrement::where('employee_id', $employee->id)->latest()->first();

        $previous_salary =  $oldIncrement ? $oldIncrement->new_salary : $employee->gross_salary;
        $increment_amount = $payload['amount'];
        $new_salary = $previous_salary + $increment_amount;
        $increment_percentage = $previous_salary > 0 ? ($increment_amount / $previous_salary) * 100 : null;

        // Read previous comp salaries from the last increment (not salary_info, which must not be mutated).
        $prev_comp_1 = (float) ($oldIncrement?->new_salary_comp_1 ?? $employee->salaryInfo?->gross_salary_comp1 ?? 0);
        $prev_comp_2 = (float) ($oldIncrement?->new_salary_comp_2 ?? $employee->salaryInfo?->gross_salary_comp2 ?? 0);
        $new_comp_1 = $prev_comp_1 + $increment_amount;
        $new_comp_2 = $prev_comp_2 + $increment_amount;

        HrEmployeeSalaryIncrement::create([
            'employee_id' => $employee->id,
            'increment_date' => $payload['increment_date'],
            'previous_salary' => $previous_salary,
            'increment_amount' => $increment_amount,
            'increment_percentage' => $increment_percentage,
            'new_salary' => $new_salary,
            'previous_salary_comp_1' => $prev_comp_1,
            'new_salary_comp_1' => $new_comp_1,
            'previous_salary_comp_2' => $prev_comp_2,
            'new_salary_comp_2' => $new_comp_2,
        ]);
        // Intentionally NOT updating salary_info — effective salary is derived from increment records.
        return redirect()->route('hr-center.employees.increments.page', $employee->id)->with('success', 'Increment added.');
    }

    public function incrementsUpdate(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'identifier' => 'required|integer|exists:hr_employee_salary_increments,id',
            'increment_date' => 'required|date',
            'amount' => 'required|numeric',
        ]);

        // ১. বিদ্যমান ইনক্রিমেন্ট রেকর্ডটি খুঁজে বের করা
        $increment = HrEmployeeSalaryIncrement::findOrFail($payload['identifier']);

        if ($increment->is_locked) {
            return redirect()->route('hr-center.employees.increments.page', $employee->id)
                ->with('error', 'This increment is locked — unlock it first before editing.');
        }

        // ২. স্যালারি রিভার্স করা (আগের ইনক্রিমেন্ট বাদ দিয়ে বেস স্যালারিতে ফিরে যাওয়া)
        // Use the stored previous_salary from the increment record — salary_info must not be read for this.
        $old_increment_amount = $increment->increment_amount;
        $base_salary  = (float) $increment->previous_salary;
        $prev_comp_1  = (float) ($increment->previous_salary_comp_1 ?? 0);
        $prev_comp_2  = (float) ($increment->previous_salary_comp_2 ?? 0);

        $new_increment_amount = $payload['amount'];
        $new_gross_salary = $base_salary + $new_increment_amount;
        $new_percentage   = $base_salary > 0 ? ($new_increment_amount / $base_salary) * 100 : 0;
        $new_comp_1       = $prev_comp_1 + $new_increment_amount;
        $new_comp_2       = $prev_comp_2 + $new_increment_amount;

        $increment->update([
            'increment_date'         => $payload['increment_date'],
            'increment_amount'       => $new_increment_amount,
            'increment_percentage'   => $new_percentage,
            'new_salary'             => $new_gross_salary,
            'previous_salary_comp_1' => $prev_comp_1,
            'new_salary_comp_1'      => $new_comp_1,
            'previous_salary_comp_2' => $prev_comp_2,
            'new_salary_comp_2'      => $new_comp_2,
        ]);
        // Intentionally NOT updating salary_info — effective salary is derived from increment records.

        return redirect()->route('hr-center.employees.increments.page', $employee->id)
                        ->with('success', 'Increment updated successfully.');
    }

    /**
     * A draft increment has no effect on any report/salary calculation
     * (see HrEmployeeSalaryIncrement::applyIncrementOverride()) until locked.
     */
    public function incrementsLock(HrEmployee $employee, HrEmployeeSalaryIncrement $increment): RedirectResponse
    {
        $this->ensureEmployee($employee);
        abort_unless($increment->employee_id === $employee->id, 404);

        $increment->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        return redirect()->route('hr-center.employees.increments.page', $employee->id)
            ->with('success', 'Increment locked — it is now effective in reports.');
    }

    public function incrementsUnlock(HrEmployee $employee, HrEmployeeSalaryIncrement $increment): RedirectResponse
    {
        $this->ensureEmployee($employee);
        abort_unless($increment->employee_id === $employee->id, 404);

        $increment->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return redirect()->route('hr-center.employees.increments.page', $employee->id)
            ->with('success', 'Increment unlocked.');
    }


    public function earningsDeductionsPage(HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $rows = HrEmployeeOtherTransaction::where('employee_id', $employee->id)
            ->latest('txn_date')
            ->get()
            ->map(function ($row) {
                $date = $row->txn_date;
                $year = '-';
                $month = '-';
                if ($date) {
                    try {
                        $parsed = \Illuminate\Support\Carbon::parse($date);
                        $year = $parsed->format('Y');
                        $month = $parsed->format('F');
                    } catch (\Throwable $e) {}
                }
                return [
                    'source'      => 'other',
                    'identifier'  => (string) $row->id,
                    'date'        => $date,
                    'year'        => $year,
                    'month'       => $month,
                    'advance_iou' => (float) ($row->advance_iou ?? 0),
                    'ot'          => (float) ($row->ot_adjust ?? 0),
                    'day'         => (float) ($row->day_adjust ?? 0),
                    'earnings'    => (float) ($row->earnings ?? 0),
                    'deductions'  => (float) ($row->deductions ?? 0),
                    'remarks'     => $row->remarks,
                ];
            });

        $options = $this->options();
        $employeeMeta = [
            'department' => optional(collect($options['departments'] ?? [])->firstWhere('id', $employee->department_id))->name,
            'designation' => optional(collect($options['designations'] ?? [])->firstWhere('id', $employee->designation_id))->name,
        ];

        return view('hr::employees.pages.earnings-deductions', compact('employee', 'rows', 'employeeMeta'));
    }

    public function earningsDeductionsStore(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'date' => 'required|date',
            'advance_iou' => 'nullable|numeric',
            'ot' => 'nullable|numeric',
            'day' => 'nullable|numeric',
            'earnings' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:500',
        ]);

        HrEmployeeOtherTransaction::create([
            'employee_id' => $employee->id,
            'txn_date'    => $payload['date'],
            'advance_iou' => $payload['advance_iou'] ?? null,
            'ot_adjust'   => $payload['ot'] ?? null,
            'day_adjust'  => $payload['day'] ?? null,
            'earnings'    => $payload['earnings'] ?? null,
            'deductions'  => $payload['deductions'] ?? null,
            'remarks'     => $payload['remarks'] ?? null,
            'status'      => 1,
            'created_by'  => Auth::id(),
        ]);

        return redirect()->route('hr-center.employees.earnings.page', $employee->id)->with('success', 'Earnings & deductions entry added.');
    }

    public function earningsDeductionsUpdate(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'identifier' => 'required|integer|min:0',
            'date' => 'required|date',
            'advance_iou' => 'nullable|numeric',
            'ot' => 'nullable|numeric',
            'day' => 'nullable|numeric',
            'earnings' => 'nullable|numeric',
            'deductions' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:500',
        ]);

        $txn = HrEmployeeOtherTransaction::where('employee_id', $employee->id)->find((int) $payload['identifier']);
        if (!$txn) {
            return redirect()->route('hr-center.employees.earnings.page', $employee->id)->with('error', 'Row not found.');
        }
        $txn->update([
            'txn_date'   => $payload['date'],
            'advance_iou'=> $payload['advance_iou'] ?? null,
            'ot_adjust'  => $payload['ot'] ?? null,
            'day_adjust' => $payload['day'] ?? null,
            'earnings'   => $payload['earnings'] ?? null,
            'deductions' => $payload['deductions'] ?? null,
            'remarks'    => $payload['remarks'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('hr-center.employees.earnings.page', $employee->id)->with('success', 'Earnings & deductions entry updated.');
    }

    public function earningsDeductionsDelete(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'identifier' => 'required|integer|min:0',
        ]);

        $txn = HrEmployeeOtherTransaction::where('employee_id', $employee->id)->find((int) $payload['identifier']);
        if (!$txn) {
            return redirect()->route('hr-center.employees.earnings.page', $employee->id)->with('error', 'Row not found.');
        }
        $txn->delete();

        return redirect()->route('hr-center.employees.earnings.page', $employee->id)->with('success', 'Earnings & deductions entry deleted.');
    }

    public function leavesPage(HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $rows = HrEmployeeLeave::query()
            ->where('employee_id', $employee->id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($row) {
                $leaveType = optional($row->leaveType);
                $leaveFrom = data_get($row, 'start_date');
                $leaveTo = data_get($row, 'end_date');
                return [
                    'source' => 'db',
                    'identifier' => (string) ($row->id ?? ''),
                    'application_date' => data_get($row, 'application_date'),
                    'application_no' => data_get($row, 'application_no'),
                    'leave_code' => $leaveType->code ?? null,
                    'leave_type' => $leaveType->name ?? null,
                    'leave_type_id' => $row->leave_type_id,
                    'leave_from' => $leaveFrom,
                    'leave_to' => $leaveTo,
                    'purpose' => data_get($row, 'reason'),
                    'remarks' => data_get($row, 'remarks'),
                    'status' => data_get($row, 'status'),
                    'total_days' => data_get($row, 'total_days') ?? $this->calculateTotalDays($leaveFrom, $leaveTo),
                ];
            });

        $leaveTypes = Schema::hasTable((new HrLeaveInfo())->getTable())
            ? HrLeaveInfo::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'code', 'days'])
            : collect();

        $takenByTypeId = $rows
            ->groupBy(fn ($row) => (int) $row['leave_type_id'])
            ->map(fn ($group) => (int) round($group->sum(fn ($row) => (float) data_get($row, 'total_days', 0))));

        $leaveSummary = $leaveTypes->map(function ($leaveType) use ($takenByTypeId) {
            $typeId = (int) $leaveType->id;
            $totalDays = (int) ($leaveType->days ?? 0);
            $takenDays = (int) ($takenByTypeId->get($typeId, 0));

            return [
                'code' => $leaveType->code,
                'name' => $leaveType->name,
                'remaining_days' => $totalDays,
                'taken_days' => $takenDays,
                'available_days' => max($totalDays - $takenDays, 0),
            ];
        });

        $options = $this->options();
        $employeeMeta = [
            'classification' => optional(collect($options['classifications'] ?? [])->firstWhere('id', $employee->classification_id))->name,
            'department' => optional(collect($options['departments'] ?? [])->firstWhere('id', $employee->department_id))->name,
            'section' => optional(collect($options['sections'] ?? [])->firstWhere('id', $employee->section_id))->name,
            'designation' => optional(collect($options['designations'] ?? [])->firstWhere('id', $employee->designation_id))->name,
        ];

        return view('hr::employees.pages.leaves', compact('employee', 'rows', 'leaveTypes', 'leaveSummary', 'employeeMeta'));
    }

    public function leavesPrint(HrEmployee $employee, HrEmployeeLeave $leave)
    {
        $this->ensureEmployee($employee);
        $options = $this->options();
        $deptRow   = collect($options['departments']  ?? [])->firstWhere('id', $employee->department_id);
        $sectRow   = collect($options['sections']     ?? [])->firstWhere('id', $employee->section_id);
        $desigRow  = collect($options['designations'] ?? [])->firstWhere('id', $employee->designation_id);
        $employeeMeta = [
            'department'     => optional($deptRow)->name,
            'department_bn'  => optional($deptRow)->bn_name ?: optional($deptRow)->name,
            'section'        => optional($sectRow)->name,
            'section_bn'     => optional($sectRow)->bn_name ?: optional($sectRow)->name,
            'designation'    => optional($desigRow)->name,
            'designation_bn' => optional($desigRow)->bn_name ?: optional($desigRow)->name,
        ];
        $leaveType = optional($leave->leaveType);

        // Leave summary for HR sidebar table
        $leaveTypes = Schema::hasTable((new HrLeaveInfo())->getTable())
            ? HrLeaveInfo::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name', 'code', 'days'])
            : collect();

        $allLeaves = HrEmployeeLeave::query()->where('employee_id', $employee->id)->get();
        $takenByTypeId = $allLeaves
            ->groupBy(fn ($r) => (int) $r->leave_type_id)
            ->map(fn ($g) => (int) round($g->sum(fn ($r) => (float) ($r->total_days ?? 0))));

        $earnLeaveDays = $this->calculateYearlyEarnLeave($employee);
        $earnCodes     = ['EL', 'AL', 'EARN'];

        $leaveSummary = $leaveTypes->map(function ($lt) use ($takenByTypeId, $earnCodes, $earnLeaveDays) {
            $code  = strtoupper(trim($lt->code ?? ''));
            $total = in_array($code, $earnCodes)
                ? $earnLeaveDays
                : (int) ($lt->days ?? 0);
            $taken = (int) ($takenByTypeId->get((int) $lt->id, 0));
            return ['code' => $lt->code, 'name' => $lt->name, 'bn_name' => $lt->bn_name, 'total' => $total, 'taken' => $taken, 'remaining' => max($total - $taken, 0)];
        })->values();

        // Most recent leave before this one (for "সর্বশেষ ছুটির তারিখ")
        $prevLeave = HrEmployeeLeave::query()
            ->where('employee_id', $employee->id)
            ->where('id', '!=', $leave->id)
            ->orderByDesc('leave_from')
            ->first();

        $factory = HrFactory::query()->where('status', 'active')->orderBy('id')->first();

        return view('hr::employees.pages.leave-print', compact('employee', 'leave', 'leaveType', 'employeeMeta', 'leaveSummary', 'prevLeave', 'factory'));
    }

    public function leavesStore(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);
        $payload = $request->validate([
            'leave_type_id' => 'required|exists:hr_leave_infos,id',
            'application_date' => 'required|date',
            'application_no' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'nullable|string|max:20',
        ]);

        $total_days = $this->calculateTotalDays($payload['start_date'], $payload['end_date']);

        HrEmployeeLeave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $payload['leave_type_id'],
            'application_date' => $payload['application_date'],
            'application_no' => $payload['application_no'] ?? null,
            'leave_from' => $payload['start_date'],
            'leave_to' => $payload['end_date'],
            'total_days' => $total_days,
            'reason' => $payload['reason'] ?? null,
            'remarks' => $payload['remarks'] ?? null,
            'status' => $payload['status'] ?? 'pending',
        ]);
        return redirect()->route('hr-center.employees.leaves.page', $employee->id)->with('success', 'Leave added.');
    }

    public function leavesUpdate(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'identifier' => 'required|string',
            'leave_type_id' => 'required|exists:hr_leave_infos,id',
            'application_date' => 'required|date',
            'application_no' => 'nullable|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'remarks' => 'nullable|string',
            'status' => 'nullable|string|max:20',
        ]);

        $row = HrEmployeeLeave::where('id', $payload['identifier'])->where('employee_id', $employee->id)->first();
        if (!$row) {
            return redirect()->route('hr-center.employees.leaves.page', $employee->id)->with('error', 'Leave row not found.');
        }
        $row->leave_type_id = $payload['leave_type_id'];
        $row->application_date = $payload['application_date'];
        $row->application_no = $payload['application_no'] ?? null;
        $row->leave_from = $payload['start_date'];
        $row->leave_to = $payload['end_date'];
        $row->total_days = $this->calculateTotalDays($payload['start_date'], $payload['end_date']);
        $row->reason = $payload['reason'] ?? null;
        $row->remarks = $payload['remarks'] ?? null;
        $row->status = $payload['status'] ?? 'pending';
        $row->save();
        return redirect()->route('hr-center.employees.leaves.page', $employee->id)->with('success', 'Leave updated.');
    }

    public function leavesDelete(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $payload = $request->validate([
            'source' => 'required|in:db,other',
            'identifier' => 'required|string',
        ]);

        $row = HrEmployeeLeave::where('id', $payload['identifier'])->where('employee_id', $employee->id)->first();
        if (!$row) {
            return redirect()->route('hr-center.employees.leaves.page', $employee->id)->with('error', 'Leave row not found.');
        }
        $row->delete();
        return redirect()->route('hr-center.employees.leaves.page', $employee->id)->with('success', 'Leave deleted.');
    }

    public function printSection(HrEmployee $employee, string $section)
    {
        $this->ensureEmployee($employee);

        dd("Print section: {$section} for employee ID: {$employee->id}");
    }

    private function calculateYearlyEarnLeave(HrEmployee $employee): int
    {
        $year      = now()->year;
        $yearStart = "{$year}-01-01";
        $yearEnd   = "{$year}-12-31";
        $today     = now()->format('Y-m-d');
        $scanEnd   = $today < $yearEnd ? $today : $yearEnd;

        $empWeekend = strtolower($employee->weekend ?? 'friday');

        $holidays = HrHoliday::query()
            ->where('status', 1)
            ->where('from_date', '<=', $yearEnd)
            ->where('to_date',   '>=', $yearStart)
            ->get(['from_date', 'to_date']);

        $rtwByDate = HrRegularToWeekend::query()
            ->where('section_id', $employee->section_id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->where('status', 1)
            ->get(['date', 'type'])
            ->keyBy(fn ($r) => (string) $r->date);

        $attendedDates = array_flip(
            HrAttendance::query()
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$yearStart, $scanEnd])
                ->whereNotNull('in_time')
                ->pluck('date')
                ->map(fn ($d) => (string) $d)
                ->toArray()
        );

        $attendCount = 0;
        $current     = \Carbon\Carbon::parse($yearStart);
        $end         = \Carbon\Carbon::parse($scanEnd);

        while ($current->lte($end)) {
            $dateStr   = $current->format('Y-m-d');
            $dayOfWeek = strtolower($current->format('l'));
            $swap      = $rtwByDate->get($dateStr);

            $isRegularToWeekend = $swap && $swap->type === 'weekend';
            $isWeekendToRegular = $swap && $swap->type === 'regular';

            $isWeekendDay = ($dayOfWeek === $empWeekend && !$isWeekendToRegular) || $isRegularToWeekend;
            $isHoliday    = $holidays->contains(fn ($h) => $dateStr >= $h->from_date && $dateStr <= $h->to_date);

            if (!$isWeekendDay && !$isHoliday && array_key_exists($dateStr, $attendedDates)) {
                $attendCount++;
            }

            $current->addDay();
        }

        return (int) floor(($attendCount / 18) * 30);
    }

    private function options(): array
    {
        $maritalStatuses = HrMaritalStatus::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name', 'code']);

        $religions = HrReligion::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name', 'code']);

        $sexes = HrSex::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'bn_name', 'code']);

        $paymentMethodColumns = ['id', 'name', 'code'];
        if (Schema::hasColumn((new HrPaymentMethod())->getTable(), 'bn_name')) {
            $paymentMethodColumns[] = 'bn_name';
        }

        $paymentMethods = HrPaymentMethod::query()
            ->orderBy('name')
            ->get($paymentMethodColumns);

        $countryColumns = ['id', 'name'];
        if (Schema::hasColumn((new HrGeoLocation())->getTable(), 'bn_name')) {
            $countryColumns[] = 'bn_name';
        }

        $countries = HrGeoLocation::query()->where('type', 'country')->orderBy('name')->get($countryColumns);
        $lines = HrFloorLine::query()->orderBy('line_name')->get()->map(static function ($line) {
            return (object) [
                'id' => $line->id,
                'name' => $line->line_name,
                'bn_name' => $line->bn_line_name,
                'slug' => null,
            ];
        });

        return [
            'classifications' => HrClassification::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'departments' => HrDepartment::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']),
            'sections' => HrSection::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']),
            'subSections' => HrSubSection::orderBy('name')->get(['id', 'name']),
            'lines' => $lines,
            'designations' => HrDesignation::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']),
            'workingPlaces' => HrWorkingPlace::orderBy('name')->get(['id', 'name']),
            'weeks' => collect([
                (object) ['id' => 1, 'name' => 'Sunday'],
                (object) ['id' => 2, 'name' => 'Monday'],
                (object) ['id' => 3, 'name' => 'Tuesday'],
                (object) ['id' => 4, 'name' => 'Wednesday'],
                (object) ['id' => 5, 'name' => 'Thursday'],
                (object) ['id' => 6, 'name' => 'Friday'],
                (object) ['id' => 7, 'name' => 'Saturday'],
            ]),
            'shifts' => HrShift::orderBy('name')->get(['id', 'name']),
            'maritalStatuses' => $maritalStatuses,
            'religions' => $religions,
            'sexes' => $sexes,
            'paymentMethods' => $paymentMethods,
            'countries' => $countries,
            'districts' => HrGeoLocation::where('type', 'district')->orderBy('name')->get(['id', 'name']),
            'thanas' => HrGeoLocation::where('type', 'police_station')->orderBy('name')->get(['id', 'parent_id', 'name']),
        ];
    }

    private function resolveLeaveType(string $leaveCode): ?LeaveInfo
    {
        $table = (new HrLeaveInfo())->getTable();
        if (!Schema::hasTable($table)) {
            return null;
        }

        return HrLeaveInfo::query()
            ->where('status', 'active')
            ->where('code', $leaveCode)
            ->first(['id', 'name', 'code', 'days']);
    }

    public function documentsPage(HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $documents = HrEmployeeDocument::where('employee_id', $employee->id)
            ->latest()
            ->get();

        $options = $this->options();
        $employeeMeta = [
            'department'  => optional(collect($options['departments'] ?? [])->firstWhere('id', $employee->department_id))->name,
            'designation' => optional(collect($options['designations'] ?? [])->firstWhere('id', $employee->designation_id))->name,
        ];

        return view('hr::employees.pages.documents', compact('employee', 'documents', 'employeeMeta'));
    }

    public function documentsStore(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $request->validate([
            'documents'           => 'required|array|min:1',
            'documents.*.title'   => 'required|string|max:150',
            'documents.*.files'   => 'required|array|min:1',
            'documents.*.files.*' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:10240',
        ]);

        $stored = 0;
        foreach ($request->documents as $row) {
            $title = trim($row['title']);
            foreach ($row['files'] as $file) {
                $ext      = strtolower($file->getClientOriginalExtension());
                $path     = $file->store("hr/documents/{$employee->id}", 'public');
                HrEmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'title'       => $title,
                    'file_path'   => $path,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_type'   => $ext,
                    'file_size'   => $file->getSize(),
                ]);
                $stored++;
            }
        }

        return redirect()->route('hr-center.employees.documents.page', $employee->id)
            ->with('success', "$stored টি ফাইল সফলভাবে আপলোড হয়েছে।");
    }

    public function documentsDelete(Request $request, HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $request->validate(['document_id' => 'required|integer']);

        $doc = HrEmployeeDocument::where('id', $request->document_id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return redirect()->route('hr-center.employees.documents.page', $employee->id)
            ->with('success', 'Document deleted.');
    }

    public function show(Request $request, HrEmployee $employee)
    {
        $this->ensureEmployee($employee);

        $employee->load([
            'basicInfo', 'salaryInfo', 'nomineeRecord', 'ageVerification',
            'separation', 'finalSettlement', 'permanentAddress', 'presentAddress',
            'classification', 'department', 'section', 'subSection',
            'designation', 'shift', 'workingPlace', 'floorLine',
        ]);

        // Leaves
        $leaves = HrEmployeeLeave::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest()
            ->get()
            ->map(function ($row) {
                $leaveType = optional($row->leaveType);
                return [
                    'leave_code'       => $leaveType->code,
                    'leave_type'       => $leaveType->name,
                    'leave_type_id'    => $row->leave_type_id,
                    'application_date' => $row->application_date,
                    'leave_from'       => $row->start_date,
                    'leave_to'         => $row->end_date,
                    'total_days'       => $row->total_days ?? $this->calculateTotalDays($row->start_date, $row->end_date),
                    'purpose'          => $row->reason,
                    'status'           => $row->status,
                ];
            });

        $leaveTypes = Schema::hasTable((new HrLeaveInfo())->getTable())
            ? HrLeaveInfo::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code', 'days'])
            : collect();

        $takenByTypeId = $leaves->groupBy('leave_type_id')
            ->map(fn ($g) => (int) round($g->sum(fn ($r) => (float) ($r['total_days'] ?? 0))));

        $leaveSummary = $leaveTypes->map(function ($lt) use ($takenByTypeId) {
            $total = (int) ($lt->days ?? 0);
            $taken = (int) ($takenByTypeId->get($lt->id, 0));
            return [
                'code'           => $lt->code,
                'name'           => $lt->name,
                'remaining_days' => $total,
                'taken_days'     => $taken,
                'available_days' => max($total - $taken, 0),
            ];
        });

        // Salary increments
        $increments = collect();
        $incTable = (new HrEmployeeSalaryIncrement())->getTable();
        if (Schema::hasTable($incTable)) {
            $q = HrEmployeeSalaryIncrement::query();
            if (Schema::hasColumn($incTable, 'employee_id')) {
                $q->where('employee_id', $employee->id);
            } elseif (Schema::hasColumn($incTable, 'user_id')) {
                $q->where('user_id', $employee->id);
            }
            $increments = $q->latest('increment_date')->get();
        }

        // Other transactions (advance/earnings/deductions)
        $transactions = HrEmployeeOtherTransaction::where('employee_id', $employee->id)
            ->latest('txn_date')
            ->get();

        // Documents
        $documents = HrEmployeeDocument::where('employee_id', $employee->id)->latest()->get();

        // Gate passes
        $gatePasses = HrEmployeeGatePass::where('employee_id', $employee->id)->latest('out_time')->get();

        // Assets
        $assets = HrEmployeeAsset::with('category')->where('employee_id', $employee->id)->latest('issued_date')->get();

        // Attendance — last 60 days
        $attendanceDateFrom = now()->subDays(59)->toDateString();
        $attendanceDateTo   = now()->toDateString();

        $monthFilter = $request->filled('att_month') ? $request->att_month : null;
        if ($monthFilter) {
            $attStart = \Carbon\Carbon::parse($monthFilter . '-01');
            $attendanceDateFrom = $attStart->toDateString();
            $attendanceDateTo   = $attStart->copy()->endOfMonth()->toDateString();
        }

        $attendances = HrAttendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$attendanceDateFrom, $attendanceDateTo])
            ->orderBy('date', 'desc')
            ->get();

        // Transaction totals
        $txnTotalAdvance    = (float) $transactions->sum('advance_iou');
        $txnTotalEarnings   = (float) $transactions->sum('earnings');
        $txnTotalDeductions = (float) $transactions->sum('deductions');
        $txnTotalOt         = (float) $transactions->sum('ot_adjust');

        // Attendance totals
        $attPresent = $attendances->where('status', 'Present')->count();
        $attAbsent  = $attendances->where('status', 'Absent')->count();
        $attLate    = $attendances->where('status', 'Late')->count();
        $attTotal   = $attendances->count();
        $totalOtMin = (int) $attendances->sum('total_ot_minute');
        $totalWkMin = (int) $attendances->sum('total_working_minute');

        $options = $this->options();

        return view('hr::employees.show', compact(
            'employee', 'leaves', 'leaveSummary', 'increments',
            'transactions', 'documents', 'attendances',
            'attendanceDateFrom', 'attendanceDateTo', 'options',
            'txnTotalAdvance', 'txnTotalEarnings', 'txnTotalDeductions', 'txnTotalOt',
            'attPresent', 'attAbsent', 'attLate', 'attTotal', 'totalOtMin', 'totalWkMin',
            'gatePasses', 'assets'
        ));
    }

    public function destroy(HrEmployee $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);
        $employee->delete();

        return redirect()->route('hr-center.employees.index')->with('success', 'Employee deleted successfully.');
    }

    private function ensureEmployee(HrEmployee $employee): void
    {
        // dd($employee, HrEmployee::query()->whereKey($employee->id)->exists());
        abort_unless(
            HrEmployee::query()->whereKey($employee->id)->exists(),
            404
        );
    }

    private function applyIntegerFilter($query, string $column, int $value, ?string $profileKey = null): void
    {
        if (Schema::hasColumn($this->employeeTable(), $column)) {
            $query->where($column, $value);
        }
    }

    private function applyStringFilter($query, string $column, string $value, ?string $profileKey = null): void
    {
        $text = trim($value);
        if ($text === '') {
            return;
        }

        if (Schema::hasColumn($this->employeeTable(), $column)) {
            $query->where($column, 'like', "%{$text}%");
        }
    }

    private function calculateTotalDays(?string $leaveFrom, ?string $leaveTo): int
    {
        if (!$leaveFrom || !$leaveTo) {
            return 0;
        }

        try {
            return \Illuminate\Support\Carbon::parse($leaveFrom)
                ->startOfDay()
                ->diffInDays(\Illuminate\Support\Carbon::parse($leaveTo)->startOfDay()) + 1;
        } catch (\Throwable $exception) {
            return 0;
        }
    }

    private function otherInfo(HrEmployee $employee): array
    {
        $si  = $employee->salaryInfo;
        $nom = $employee->nomineeRecord;
        $av  = $employee->ageVerification;
        $sep = $employee->separation;
        $fs  = $employee->finalSettlement;

        return [
            'profile' => [
                'sub_section_id'   => $employee->sub_section_id,
                'working_place_id' => $employee->working_place_id,
                'weekend'          => $employee->weekend,
                'is_active_01'     => $employee->comp_one,
                'is_active_02'     => $employee->comp_two,
            ],
            'salary_info' => [
                'gross_salary_comp_1'     => $si?->gross_salary_comp1,
                'gross_salary_comp_2'     => $si?->gross_salary_comp2,
                'car_fuel'                => $si?->car_fuel,
                'phone_internet'          => $si?->phone_internet,
                'extra_facility'          => $si?->extra_facility,
                'attendance_bonus'        => $si?->attendance_bonus,
                'attendance_bonus_com'    => $si?->attendance_bonus_com,
                'tiffin_allowance'        => $si?->tiffin_allowance,
                'night_allowance'         => $si?->night_allowance,
                'dinner_allowance'        => $si?->dinner_allowance,
                'minimum_tiffin_hour'     => $si?->min_tiffin_hour,
                'minimum_night_hour'      => $si?->min_night_hour,
                'minimum_dinner_hour'     => $si?->min_dinner_hour,
                'meal_payment_way'        => $si?->payment_way,
                'weekend_allowance_count' => $si?->weekend_allowance_count,
                'holiday_allowance'       => $si?->holiday_allowance,
            ],
            'nominee_info'        => $nom ? $nom->toArray() : [],
            'age_verification'    => $av  ? $av->toArray()  : [],
            'resign_info'         => $sep ? $sep->toArray()  : [],
            'final_settlement'    => $fs  ? $fs->toArray()   : [],
            'earnings_deductions' => [],
        ];
    }

    private function upsertNomineeInfo(HrEmployee $employee, array $payload, ?string $imagePath = null): void
    {
        $nominee = HrEmployeeNominee::firstOrNew(['employee_id' => $employee->id]);
        $nominee->employee_id = $employee->id;
        $nominee->name       = $payload['nominee'] ?? $nominee->name ?? '';
        $nominee->age        = $payload['nominee_age'] ?? $nominee->age ?? null;
        $nominee->relation   = $payload['nominee_relation'] ?? $nominee->relation ?? null;
        $nominee->bn_relation= $payload['nominee_relation_bn'] ?? $nominee->bn_relation ?? null;
        $nominee->bn_name    = $payload['nominee_bn_name'] ?? $nominee->bn_name ?? null;
        $nominee->nid_no     = $payload['nominee_nid'] ?? $nominee->nid_no ?? null;
        $nominee->mobile_no  = $payload['nominee_mobile'] ?? $nominee->mobile_no ?? null;
        $nominee->village    = $payload['nominee_village'] ?? $nominee->village ?? null;
        $nominee->bn_village = $payload['nominee_village_bn'] ?? $nominee->bn_village ?? null;
        $nominee->post_office    = $payload['nominee_post_office'] ?? $nominee->post_office ?? null;
        $nominee->bn_post_office = $payload['nominee_post_office_bn'] ?? $nominee->bn_post_office ?? null;
        $nominee->net_payment    = $payload['distribution_net_payment'] ?? $nominee->net_payment ?? null;
        $nominee->provident_fund = $payload['distribution_provident_fund'] ?? $nominee->provident_fund ?? null;
        $nominee->insurance      = $payload['distribution_insurance'] ?? $nominee->insurance ?? null;
        $nominee->accident_fine  = $payload['distribution_accident_fine'] ?? $nominee->accident_fine ?? null;
        $nominee->profit         = $payload['distribution_profit'] ?? $nominee->profit ?? null;
        $nominee->others         = $payload['distribution_others'] ?? $nominee->others ?? null;
        $nominee->district_id    = $this->resolveGeoLocationId($payload['nominee_district'] ?? null, 'district');
        $nominee->police_station_id = $this->resolveGeoLocationId($payload['nominee_po_station'] ?? null, 'police_station');
        if ($imagePath) {
            $nominee->photo = $imagePath;
        }
        $nominee->status = 1;
        $nominee->save();
    }

    private function upsertAgeVerification(HrEmployee $employee, array $payload): void
    {
        $av = HrEmployeeAgeVerification::firstOrNew(['employee_id' => $employee->id]);
        $av->employee_id = $employee->id;
        $av->physical_ability     = $payload['physical_ability'] ?? $av->physical_ability ?? null;
        $av->physical_ability_bn  = $payload['physical_ability_bn'] ?? $av->physical_ability_bn ?? null;
        $av->identification_mark  = $payload['distinguished_mark'] ?? $av->identification_mark ?? null;
        $av->identification_mark_bn = $payload['distinguished_mark_bn'] ?? $av->identification_mark_bn ?? null;
        $av->age_years            = $payload['verified_age'] ?? $av->age_years ?? null;
        $av->verified_date        = $payload['age_verification_date'] ?? $av->verified_date ?? null;
        $av->status = 1;
        $av->save();
    }

    private function upsertSeparation(HrEmployee $employee, array $payload, bool $withPaid = false): void
    {
        $sep = HrEmployeeSeparation::firstOrNew(['employee_id' => $employee->id]);
        $sep->employee_id = $employee->id;
        $sep->status           = $payload['employment_status'] ?? $sep->status ?? null;
        $sep->remarks          = $payload['resign_remarks'] ?? $sep->remarks ?? null;
        $sep->effective_date   = $payload['resign_date'] ?? $sep->effective_date ?? null;
        $sep->final_settlement = $payload['final_settlement_type'] ?? $sep->final_settlement ?? null;
        $sep->with_paid        = $withPaid;
        $sep->save();
    }

    private function upsertFinalSettlement(HrEmployee $employee, array $payload): void
    {
        $fs = HrEmployeeFinalSettlement::firstOrNew(['employee_id' => $employee->id]);
        $fs->employee_id = $employee->id;
        $fs->absent_date           = $payload['absent_date'] ?? $fs->absent_date ?? null;
        $fs->first_letter_date     = $payload['letter_1_date'] ?? $fs->first_letter_date ?? null;
        $fs->second_letter_date    = $payload['letter_2_date'] ?? $fs->second_letter_date ?? null;
        $fs->third_letter_date     = $payload['letter_3_date'] ?? $fs->third_letter_date ?? null;
        $fs->selected_letter_print = $payload['final_settlement_option'] ?? $fs->selected_letter_print ?? null;

        $fs->last_basic_salary        = $payload['last_basic_salary'] ?? $fs->last_basic_salary ?? null;
        $fs->last_gross_salary        = $payload['last_gross_salary'] ?? $fs->last_gross_salary ?? null;
        $fs->service_years            = $payload['service_years'] ?? $fs->service_years ?? null;
        $fs->unpaid_salary_days       = $payload['unpaid_salary_days'] ?? $fs->unpaid_salary_days ?? null;
        $fs->unpaid_salary_amount     = $payload['unpaid_salary_amount'] ?? $fs->unpaid_salary_amount ?? null;
        $fs->leave_encashment_days    = $payload['leave_encashment_days'] ?? $fs->leave_encashment_days ?? null;
        $fs->leave_encashment_amount  = $payload['leave_encashment_amount'] ?? $fs->leave_encashment_amount ?? null;
        $fs->gratuity_amount          = $payload['gratuity_amount'] ?? $fs->gratuity_amount ?? null;
        $fs->advance_deduction        = $payload['advance_deduction'] ?? $fs->advance_deduction ?? null;
        $fs->other_earnings           = $payload['other_earnings'] ?? $fs->other_earnings ?? null;
        $fs->other_deductions         = $payload['other_deductions'] ?? $fs->other_deductions ?? null;
        $fs->net_payable              = $payload['net_payable'] ?? $fs->net_payable ?? null;
        $fs->calculation_notes        = $payload['calculation_notes'] ?? $fs->calculation_notes ?? null;
        $fs->settlement_status        = $payload['settlement_status'] ?? $fs->settlement_status ?? 'draft';

        $fs->status = 1;
        $fs->save();
    }

    private function normalizeUserStatus(string $status): int|string
    {
        if (!$this->isUserStatusNumeric()) {
            return $status;
        }

        return $status === 'active' ? 1 : 0;
    }

    private function isUserStatusNumeric(): bool
    {
        if (!Schema::hasColumn($this->employeeTable(), 'status')) {
            return false;
        }

        $columnType = strtolower((string) Schema::getColumnType($this->employeeTable(), 'status'));

        return in_array($columnType, ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'boolean'], true);
    }

    private function toBanglaLabel(?string $value, array $map, ?string $fallback = null): ?string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $normalized = strtolower($text);
        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $fallbackText = trim((string) $fallback);
        if ($fallbackText !== '') {
            return $fallbackText;
        }

        return $text;
    }

    private function banglaMapFromOptions(iterable $items, array $sourceFields = ['name', 'code']): array
    {
        $map = [];

        foreach ($items as $item) {
            $bangla = trim((string) data_get($item, 'bn_name', data_get($item, 'name_bn', '')));
            if ($bangla === '') {
                continue;
            }

            foreach ($sourceFields as $field) {
                $source = trim((string) data_get($item, $field));
                if ($source === '') {
                    continue;
                }

                $map[strtolower($source)] = $bangla;
            }
        }

        return $map;
    }

    private function applyExtendedProfileFields(HrEmployee $employee, array $payload): void
    {
        if (Schema::hasColumn($this->employeeTable(), 'sub_section_id')) {
            $employee->sub_section_id = $payload['sub_section_id'] ?? null;
        }

        if (Schema::hasColumn($this->employeeTable(), 'working_place_id')) {
            $employee->working_place_id = $payload['working_place_id'] ?? null;
        }

        if (Schema::hasColumn($this->employeeTable(), 'weekend')) {
            $employee->weekend = $payload['weekend'] ?? null;
        }

        if (Schema::hasColumn($this->employeeTable(), 'comp_one')) {
            $employee->comp_one = isset($payload['is_active_01']) ? (int) $payload['is_active_01'] : 0;
        }

        if (Schema::hasColumn($this->employeeTable(), 'comp_two')) {
            $employee->comp_two = isset($payload['is_active_02']) ? (int) $payload['is_active_02'] : 0;
        }

        if (!Schema::hasColumn($this->employeeTable(), 'working_place_id') && Schema::hasColumn($this->employeeTable(), 'location') && !empty($payload['working_place_id'])) {
            $workingPlace = HrWorkingPlace::query()->find($payload['working_place_id']);
            $employee->location = $workingPlace?->name;
        }

        if (Schema::hasColumn($this->employeeTable(), 'grade') && !empty($payload['designation_id'])) {
            $desig = HrDesignation::query()->find($payload['designation_id']);
            if ($desig && !empty($desig->grade)) {
                $employee->grade = $desig->grade;
            }
        }
    }

    private function validateApprovedManpower(int $designationId): void
    {
        $designation = HrDesignation::query()->find($designationId);
        if (!$designation || empty($designation->approved_manpower)) {
            return;
        }

        $current = HrEmployee::query()
            ->where('designation_id', $designationId)
            ->where('status', 'active')
            ->count();

        if ($current >= (int) $designation->approved_manpower) {
            abort(422, 'Approved manpower limit of ' . $designation->approved_manpower . ' for "' . $designation->name . '" has been reached. Cannot create a new employee under this designation.');
        }
    }

    private function syncDesignationSalaryToEmployee(HrEmployee $employee, array $payload, bool $force = false): void
    {
        $designationId = (int) ($payload['designation_id'] ?? $employee->designation_id ?? 0);
        if ($designationId <= 0) {
            return;
        }

        $designation = HrDesignation::query()->find($designationId);
        if (!$designation) {
            return;
        }

        $si = $employee->salaryInfo;
        $salaryInfo = [
            'gross_salary_comp_1'     => $si?->gross_salary_comp1,
            'gross_salary_comp_2'     => $si?->gross_salary_comp2,
            'car_fuel'                => $si?->car_fuel,
            'phone_internet'          => $si?->phone_internet,
            'extra_facility'          => $si?->extra_facility,
            'attendance_bonus'        => $si?->attendance_bonus,
            'attendance_bonus_com'    => $si?->attendance_bonus_com,
            'tiffin_allowance'        => $si?->tiffin_allowance,
            'night_allowance'         => $si?->night_allowance,
            'dinner_allowance'        => $si?->dinner_allowance,
            'minimum_tiffin_hour'     => $si?->min_tiffin_hour,
            'minimum_night_hour'      => $si?->min_night_hour,
            'minimum_dinner_hour'     => $si?->min_dinner_hour,
            'meal_payment_way'        => $si?->payment_way,
            'weekend_allowance_count' => $si?->weekend_allowance_count,
            'holiday_allowance'       => $si?->holiday_allowance,
            'salary_type'             => $si?->payment_method_id,
            'tax'                     => $si?->tax,
            'tax_calculate_by'        => $si?->tax_calculate_by,
        ];

        $setIfEmpty = static function ($current, $incoming) use ($force) {
            if ($force) {
                return $incoming;
            }
            if ($incoming === null || $incoming === '') {
                return $current;
            }
            if ($current === null || $current === '' || (is_numeric($current) && (float) $current <= 0)) {
                return $incoming;
            }

            return $current;
        };

        $salaryInfo['gross_salary_comp_1'] = $setIfEmpty(
            data_get($salaryInfo, 'gross_salary_comp_1'),
            data_get($designation, 'gross_salary')
        );
        $salaryInfo['gross_salary_comp_2'] = $setIfEmpty(
            data_get($salaryInfo, 'gross_salary_comp_2'),
            data_get($designation, 'gross_salary')
        );

        $salaryInfo['car_fuel'] = $setIfEmpty(data_get($salaryInfo, 'car_fuel'), data_get($designation, 'car_fuel'));
        $salaryInfo['phone_internet'] = $setIfEmpty(data_get($salaryInfo, 'phone_internet'), data_get($designation, 'phone_internet'));
        $salaryInfo['extra_facility'] = $setIfEmpty(data_get($salaryInfo, 'extra_facility'), data_get($designation, 'extra_facility'));

        $salaryInfo['attendance_bonus'] = $setIfEmpty(data_get($salaryInfo, 'attendance_bonus'), data_get($designation, 'attendance_bonus'));
        $salaryInfo['attendance_bonus_com'] = $setIfEmpty(data_get($salaryInfo, 'attendance_bonus_com'), data_get($designation, 'attendance_bonus_com'));

        $salaryInfo['tiffin_allowance'] = $setIfEmpty(data_get($salaryInfo, 'tiffin_allowance'), data_get($designation, 'tiffin_allowance'));
        $salaryInfo['night_allowance'] = $setIfEmpty(data_get($salaryInfo, 'night_allowance'), data_get($designation, 'night_allowance'));
        $salaryInfo['dinner_allowance'] = $setIfEmpty(data_get($salaryInfo, 'dinner_allowance'), data_get($designation, 'dinner_allowance'));
        $salaryInfo['minimum_tiffin_hour'] = $setIfEmpty(data_get($salaryInfo, 'minimum_tiffin_hour'), data_get($designation, 'minimum_tiffin_hour'));
        $salaryInfo['minimum_night_hour'] = $setIfEmpty(data_get($salaryInfo, 'minimum_night_hour'), data_get($designation, 'minimum_night_hour'));
        $salaryInfo['minimum_dinner_hour'] = $setIfEmpty(data_get($salaryInfo, 'minimum_dinner_hour'), data_get($designation, 'minimum_dinner_hour'));

        $salaryInfo['meal_payment_way'] = $setIfEmpty(data_get($salaryInfo, 'meal_payment_way'), data_get($designation, 'meal_payment_way'));
        $salaryInfo['weekend_allowance_count'] = $setIfEmpty(data_get($salaryInfo, 'weekend_allowance_count'), data_get($designation, 'weekend_allowance_count'));
        $salaryInfo['holiday_allowance'] = $setIfEmpty(data_get($salaryInfo, 'holiday_allowance'), data_get($designation, 'holiday_allowance'));

        $this->upsertSalaryInfo($employee, [
            'gross_salary'            => $setIfEmpty(data_get($employee->salaryInfo, 'gross_salary'), data_get($designation, 'gross_salary')),
            'gross_salary_comp_1'     => data_get($salaryInfo, 'gross_salary_comp_1'),
            'gross_salary_comp_2'     => data_get($salaryInfo, 'gross_salary_comp_2'),
            'car_fuel'                => data_get($salaryInfo, 'car_fuel'),
            'phone_internet'          => data_get($salaryInfo, 'phone_internet'),
            'extra_facility'          => data_get($salaryInfo, 'extra_facility'),
            'salary_type'             => data_get($salaryInfo, 'salary_type'),
            'tax'                     => data_get($salaryInfo, 'tax'),
            'tax_calculate_by'        => data_get($salaryInfo, 'tax_calculate_by'),
            'attendance_bonus'        => data_get($salaryInfo, 'attendance_bonus'),
            'attendance_bonus_com'    => data_get($salaryInfo, 'attendance_bonus_com'),
            'tiffin_allowance'        => data_get($salaryInfo, 'tiffin_allowance'),
            'min_tiffin_hour'         => data_get($salaryInfo, 'minimum_tiffin_hour'),
            'night_allowance'         => data_get($salaryInfo, 'night_allowance'),
            'min_night_hour'          => data_get($salaryInfo, 'minimum_night_hour'),
            'dinner_allowance'        => data_get($salaryInfo, 'dinner_allowance'),
            'min_dinner_hour'         => data_get($salaryInfo, 'minimum_dinner_hour'),
            'payment_way'             => data_get($salaryInfo, 'meal_payment_way'),
            'weekend_allowance_count' => data_get($salaryInfo, 'weekend_allowance_count'),
            'holiday_allowance'       => data_get($salaryInfo, 'holiday_allowance'),
        ]);
    }

    private function upsertSalaryInfo(HrEmployee $employee, array $payload): void
    {
        $existing = $employee->salaryInfo ?: new HrEmployeeSalaryInfo();
        $existing->employee_id = $employee->id;
        $existing->gross_salary = $payload['gross_salary'] ?? $existing->gross_salary ?? null;
        $existing->gross_salary_comp1 = $payload['gross_salary_comp_1'] ?? $existing->gross_salary_comp1 ?? null;
        $existing->gross_salary_comp2 = $payload['gross_salary_comp_2'] ?? $existing->gross_salary_comp2 ?? null;
        $existing->bank_ac_or_phone = $payload['bank_or_phone'] ?? $payload['bank_ac_or_phone'] ?? $existing->bank_ac_or_phone ?? null;
        $existing->car_fuel = $payload['car_fuel'] ?? $existing->car_fuel ?? null;
        $existing->phone_internet = $payload['phone_internet'] ?? $existing->phone_internet ?? null;
        $existing->extra_facility = $payload['extra_facility'] ?? $existing->extra_facility ?? null;
        $existing->tax = $payload['tax'] ?? $existing->tax ?? null;
        $existing->tax_calculate_by = $payload['tax_calculate_by'] ?? $existing->tax_calculate_by ?? null;
        $existing->effective_date = $payload['salary_info_date'] ?? $payload['effective_date'] ?? $existing->effective_date ?? null;
        $existing->status = ($payload['salary_info_status'] ?? null) === 'inactive' ? 0 : 1;
        if (array_key_exists('salary_type', $payload)) {
            $existing->payment_method_id = $this->resolveLookupId(HrPaymentMethod::class, $payload['salary_type'] ?? null);
        }

        // Designation-effectiveness fields — only written when key is present in payload
        if (array_key_exists('attendance_bonus', $payload)) {
            $existing->attendance_bonus = $payload['attendance_bonus'];
        }
        if (array_key_exists('attendance_bonus_com', $payload)) {
            $existing->attendance_bonus_com = $payload['attendance_bonus_com'];
        }
        if (array_key_exists('tiffin_allowance', $payload)) {
            $existing->tiffin_allowance = $payload['tiffin_allowance'];
        }
        if (array_key_exists('min_tiffin_hour', $payload)) {
            $existing->min_tiffin_hour = $payload['min_tiffin_hour'];
        }
        if (array_key_exists('night_allowance', $payload)) {
            $existing->night_allowance = $payload['night_allowance'];
        }
        if (array_key_exists('min_night_hour', $payload)) {
            $existing->min_night_hour = $payload['min_night_hour'];
        }
        if (array_key_exists('dinner_allowance', $payload)) {
            $existing->dinner_allowance = $payload['dinner_allowance'];
        }
        if (array_key_exists('min_dinner_hour', $payload)) {
            $existing->min_dinner_hour = $payload['min_dinner_hour'];
        }
        if (array_key_exists('payment_way', $payload)) {
            $existing->payment_way = $payload['payment_way'];
        }
        if (array_key_exists('weekend_allowance_count', $payload)) {
            $existing->weekend_allowance_count = $payload['weekend_allowance_count'];
        }
        if (array_key_exists('holiday_allowance', $payload)) {
            $existing->holiday_allowance = $payload['holiday_allowance'];
        }

        $existing->created_by = $existing->created_by ?? Auth::id();
        $existing->updated_by = Auth::id();
        $existing->save();
    }

    private function upsertBasicInfo(HrEmployee $employee, array $payload): void
    {
        $basicInfo = $employee->basicInfo ?: new HrEmployeeBasicInfo();
        $basicInfo->employee_id = $employee->id;
        $basicInfo->father_name = $payload['father_name'] ?? $basicInfo->father_name ?? null;
        $basicInfo->bn_father_name = $payload['father_name_bn'] ?? $basicInfo->bn_father_name ?? null;
        $basicInfo->mother_name = $payload['mother_name'] ?? $basicInfo->mother_name ?? null;
        $basicInfo->bn_mother_name = $payload['mother_name_bn'] ?? $basicInfo->bn_mother_name ?? null;
        $basicInfo->marital_status_id = $this->resolveLookupId(HrMaritalStatus::class, $payload['marital_status'] ?? null);
        $basicInfo->spouse_name = $payload['spouse_name'] ?? $basicInfo->spouse_name ?? null;
        $basicInfo->bn_spouse_name = $payload['spouse_name_bn'] ?? $basicInfo->bn_spouse_name ?? null;
        $basicInfo->sex_id = $this->resolveLookupId(HrSex::class, $payload['gender'] ?? null);
        $basicInfo->children_boys = isset($payload['boys']) ? (int) $payload['boys'] : ($basicInfo->children_boys ?? 0);
        $basicInfo->children_girls = isset($payload['girls']) ? (int) $payload['girls'] : ($basicInfo->children_girls ?? 0);
        $basicInfo->payment_method_id = $this->resolveLookupId(HrPaymentMethod::class, $payload['salary_type'] ?? null);
        $basicInfo->religion_id = $this->resolveLookupId(HrReligion::class, $payload['religion'] ?? null);
        $basicInfo->birth_date = $payload['dob'] ?? $basicInfo->birth_date ?? null;
        $basicInfo->blood_group = $payload['blood_group'] ?? $basicInfo->blood_group ?? null;
        $basicInfo->nationality_country_id = $this->resolveLookupId(HrGeoLocation::class, $payload['nationality'] ?? null, ['name', 'bn_name']);
        $basicInfo->national_id_no = $payload['nid_number'] ?? $basicInfo->national_id_no ?? null;
        $basicInfo->birth_registration_no = $payload['birth_registration'] ?? $basicInfo->birth_registration_no ?? null;
        $basicInfo->passport_no = $payload['passport_no'] ?? $basicInfo->passport_no ?? null;
        $basicInfo->driving_license_no = $payload['driving_license'] ?? $basicInfo->driving_license_no ?? null;
        $basicInfo->special_id_sign = $payload['distinguished_mark'] ?? $basicInfo->special_id_sign ?? null;
        $basicInfo->bn_special_id_sign = $payload['distinguished_mark_bn'] ?? $basicInfo->bn_special_id_sign ?? null;
        $basicInfo->educational_experience = $payload['education'] ?? $basicInfo->educational_experience ?? null;
        $basicInfo->bn_educational_experience = $payload['education_bn'] ?? $basicInfo->bn_educational_experience ?? null;
        $basicInfo->job_experience = $payload['job_experience'] ?? $basicInfo->job_experience ?? null;
        $basicInfo->bn_job_experience = $payload['job_experience_bn'] ?? $basicInfo->bn_job_experience ?? null;
        $basicInfo->previous_organization = $payload['prev_organization'] ?? $basicInfo->previous_organization ?? null;
        $basicInfo->bn_previous_organization = $payload['prev_organization_bn'] ?? $basicInfo->bn_previous_organization ?? null;
        $basicInfo->reference_name = $payload['reference_1'] ?? $basicInfo->reference_name ?? null;
        $basicInfo->bn_reference_name = $payload['reference_1_bn'] ?? $basicInfo->bn_reference_name ?? null;
        $basicInfo->reference_designation = $payload['reference_2'] ?? $basicInfo->reference_designation ?? null;
        $basicInfo->bn_reference_designation = $payload['reference_2_bn'] ?? $basicInfo->bn_reference_designation ?? null;
        $basicInfo->reference_card_no = $payload['reference_card_no'] ?? $basicInfo->reference_card_no ?? null;
        $basicInfo->bn_reference_card_no = $payload['reference_card_no_bn'] ?? $basicInfo->bn_reference_card_no ?? null;
        $basicInfo->reference_mobile_no = $payload['reference_mobile'] ?? $basicInfo->reference_mobile_no ?? null;
        $basicInfo->bn_reference_mobile_no = $payload['reference_mobile_bn'] ?? $basicInfo->bn_reference_mobile_no ?? null;
        $basicInfo->status = 1;
        $basicInfo->created_by = $basicInfo->created_by ?? Auth::id();
        $basicInfo->updated_by = Auth::id();
        $basicInfo->save();
    }

    private function upsertAddressInfo(HrEmployee $employee, array $payload): void
    {
        $this->upsertSingleAddress($employee, 'permanent', [
            'district' => $payload['permanent_district'] ?? null,
            'upazila' => $payload['permanent_upazila'] ?? null,
            'post_office' => $payload['permanent_post_office'] ?? null,
            'post_office_bn' => $payload['permanent_post_office_bn'] ?? null,
            'village' => $payload['permanent_village'] ?? null,
            'village_bn' => $payload['permanent_village_bn'] ?? null,
        ]);

        $this->upsertSingleAddress($employee, 'present', [
            'district' => $payload['present_district'] ?? null,
            'upazila' => $payload['present_upazila'] ?? null,
            'post_office' => $payload['present_post_office'] ?? null,
            'post_office_bn' => $payload['present_post_office_bn'] ?? null,
            'village' => $payload['present_village'] ?? null,
            'village_bn' => $payload['present_village_bn'] ?? null,
        ]);
    }

    private function upsertSingleAddress(HrEmployee $employee, string $type, array $payload): void
    {
        $address = HrEmployeeAddress::firstOrNew([
            'employee_id' => $employee->id,
            'type' => $type,
        ]);

        $address->district_id = $this->resolveGeoLocationId($payload['district'] ?? null, 'district');
        $address->police_station_id = $this->resolveGeoLocationId($payload['upazila'] ?? null, 'police_station');
        $address->post_office_id = $this->resolveGeoLocationId($payload['post_office'] ?? null, 'post_office');
        $address->post_office = $payload['post_office'] ?? null;
        $address->bn_post_office = $payload['post_office_bn'] ?? null;
        $address->village = $payload['village'] ?? null;
        $address->bn_village = $payload['village_bn'] ?? null;
        $address->status = 1;
        $address->created_by = $address->created_by ?? Auth::id();
        $address->updated_by = Auth::id();
        $address->save();
    }

    private function resolveLookupId(string $modelClass, ?string $value, array $fields = ['name', 'code', 'bn_name']): ?int
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (is_numeric($text)) {
            return (int) $text;
        }

        $query = $modelClass::query();
        $query->where(function ($builder) use ($fields, $text) {
            foreach ($fields as $index => $field) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $builder->{$method}('lower(' . $field . ') = ?', [strtolower($text)]);
            }
        });

        return (int) ($query->value('id') ?: 0) ?: null;
    }

    private function resolveGeoLocationId(?string $value, string $type): ?int
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (is_numeric($text)) {
            return (int) $text;
        }

        return HrGeoLocation::query()
            ->where('type', $type)
            ->where(function ($builder) use ($text) {
                $builder->whereRaw('lower(name) = ?', [strtolower($text)])
                    ->orWhereRaw('lower(bn_name) = ?', [strtolower($text)]);
            })
            ->value('id') ?: null;
    }

    private function employeeTable(): string
    {
        return (new HrEmployee())->getTable();
    }

    private function mapLegacyEmployeePayloadToHrColumns(array $payload): array
    {
        if (array_key_exists('joining_date', $payload)) {
            $payload['join_date'] = $payload['joining_date'];
            unset($payload['joining_date']);
        }

        if (array_key_exists('employee_type', $payload)) {
            $payload['classification_id'] = $payload['employee_type'];
            unset($payload['employee_type']);
        }

        if (array_key_exists('line_number', $payload)) {
            $payload['floor_line_id'] = $payload['line_number'];
            unset($payload['line_number']);
        }

        if (array_key_exists('mobile', $payload)) {
            $payload['personal_contact'] = $payload['mobile'];
            unset($payload['mobile']);
        }

        if (array_key_exists('emergency_mobile', $payload)) {
            $payload['emergency_contact'] = $payload['emergency_mobile'];
            unset($payload['emergency_mobile']);
        }

        // These are handled via applyExtendedProfileFields() — not direct employee columns
        unset($payload['is_active_01'], $payload['is_active_02']);

        return $payload;
    }
}
