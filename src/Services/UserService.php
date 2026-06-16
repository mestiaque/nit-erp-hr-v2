<?php

namespace App\Services;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Validation rules for creating a user
     */
    public function getCreateValidationRules(): array
    {
        return [
            'name' => 'required|max:100',
            'email' => 'nullable|email|max:100|unique:users,email',
            'mobile' => 'nullable|max:20|unique:users,mobile',
            'employee_id' => 'nullable|max:50|unique:users,employee_id',
            'password' => 'required|min:6|max:100',
        ];
    }

    /**
     * Validation rules for updating a user
     */
    public function getUpdateValidationRules($userId = null): array
    {
        $rules = [
            'name' => 'required|max:100',
            'bn_name' => 'nullable|max:100',
            'email' => 'nullable|email|max:100|unique:users,email,' . $userId,
            'mobile' => 'nullable|max:20|unique:users,mobile,' . $userId,
            'employee_id' => 'nullable|max:50|unique:users,employee_id,' . $userId,
            'father_name' => 'nullable|max:100',
            'father_name_bn' => 'nullable|max:100',
            'mother_name' => 'nullable|max:100',
            'mother_name_bn' => 'nullable|max:100',
            'spouse_name' => 'nullable|max:100',
            'spouse_name_bn' => 'nullable|max:100',
            'boys' => 'nullable|integer|min:0|max:20',
            'girls' => 'nullable|integer|min:0|max:20',
            'blood_group' => 'nullable|max:10',
            'religion' => 'nullable|max:50',
            'gender' => 'nullable|max:20',
            'marital_status' => 'nullable|max:20',
            'nid_number' => 'nullable|max:20',
            'birth_registration' => 'nullable|max:20',
            'passport_no' => 'nullable|max:20',
            'driving_license' => 'nullable|max:20',
            'etin' => 'nullable|max:20',
            'distinguished_mark' => 'nullable|max:255',
            'distinguished_mark_bn' => 'nullable|max:255',
            'height' => 'nullable|integer|min:30|max:300',
            'weight' => 'nullable|integer|min:10|max:500',
            'home_district' => 'nullable|max:100',
            'nationality' => 'nullable|max:50',
            'location' => 'nullable|max:100',
            'report_to' => 'nullable|max:100',
            'grade_lavel' => 'nullable|integer',
            'gross_salary' => 'nullable|numeric|min:0',
            'basic_salary' => 'nullable|numeric|min:0',
            'house_rent' => 'nullable|numeric|min:0',
            'medical_allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'food_allowance' => 'nullable|numeric|min:0',
            'conveyance_allowance' => 'nullable|numeric|min:0',
            'provident_fund' => 'nullable|numeric|min:0',
            'emergency_mobile' => 'nullable|max:20',
            'emergency_relation' => 'nullable|max:50',
            'present_village' => 'nullable|max:150',
            'present_village_bn' => 'nullable|max:150',
            'present_post_office' => 'nullable|max:150',
            'present_post_office_bn' => 'nullable|max:150',
            'present_upazila' => 'nullable|max:150',
            'present_upazila_bn' => 'nullable|max:150',
            'present_district' => 'nullable|max:150',
            'present_district_bn' => 'nullable|max:150',
            'permanent_village' => 'nullable|max:150',
            'permanent_village_bn' => 'nullable|max:150',
            'permanent_post_office' => 'nullable|max:150',
            'permanent_post_office_bn' => 'nullable|max:150',
            'permanent_upazila' => 'nullable|max:150',
            'permanent_upazila_bn' => 'nullable|max:150',
            'permanent_district' => 'nullable|max:150',
            'permanent_district_bn' => 'nullable|max:150',
            'division_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'designation_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'line_number' => 'nullable|integer',
            'shift_id' => 'nullable|integer',
            'employee_type' => 'nullable|integer',
            'city' => 'nullable|integer',
            'district' => 'nullable|integer',
            'postal_code' => 'nullable|max:20',
            'password' => 'nullable|min:6|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        return $rules;
    }

    /**
     * Create a new user
     */
    public function create(Request $request, string $type = 'customer'): User
    {
        $password = $request->password ?? Str::random(8);

        $user = new User();
        $user->name = $request->name;
        $user->bn_name = $request->bn_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->employee_id = $request->employee_id;
        $user->password_show = $password;
        $user->password = Hash::make($password);

        // Set user type
        $user->setTypes($type);

        $user->save();

        return $user;
    }

    /**
     * Update an existing user
     */
    public function update(Request $request, User $user): User
    {
        // Basic Information
        $user->employee_id = $request->employee_id;
        $user->name = $request->name;
        $user->bn_name = $request->bn_name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->gender = $request->gender;
        $user->marital_status = $request->marital_status;
        $user->dob = $request->date_of_birth;

        // Family Information
        $user->father_name = $request->father_name;
        $user->father_name_bn = $request->father_name_bn;
        $user->mother_name = $request->mother_name;
        $user->mother_name_bn = $request->mother_name_bn;
        $user->spouse_name = $request->spouse_name;
        $user->spouse_name_bn = $request->spouse_name_bn;
        $user->boys = $request->boys;
        $user->girls = $request->girls;

        // Personal Information
        $user->blood_group = $request->blood_group;
        $user->religion = $request->religion;
        $user->education = $request->education;
        $user->work_type = $request->work_type;

        // ID/Documents
        $user->nid_number = $request->nid_number;
        $user->birth_registration = $request->birth_registration;
        $user->passport_no = $request->passport_no;
        $user->driving_license = $request->driving_license;
        $user->etin = $request->etin;
        $user->distinguished_mark = $request->distinguished_mark;
        $user->distinguished_mark_bn = $request->distinguished_mark_bn;
        $user->height = $request->height;
        $user->weight = $request->weight;

        // Contact Information
        $user->home_district = $request->home_district;
        $user->nationality = $request->nationality;
        $user->location = $request->location;
        $user->report_to = $request->report_to;
        $user->emergency_mobile = $request->emergency_mobile;
        $user->emergency_relation = $request->emergency_relation;

        // Address
        $user->present_address = $request->present_address;
        $user->present_address_bn = $request->present_address_bn;
        $user->present_village = $request->present_village;
        $user->present_village_bn = $request->present_village_bn;
        $user->present_post_office = $request->present_post_office;
        $user->present_post_office_bn = $request->present_post_office_bn;
        $user->present_upazila = $request->present_upazila;
        $user->present_upazila_bn = $request->present_upazila_bn;
        $user->present_district = $request->present_district;
        $user->present_district_bn = $request->present_district_bn;
        $user->permanent_address = $request->permanent_address;
        $user->permanent_address_bn = $request->permanent_address_bn;
        $user->permanent_village = $request->permanent_village;
        $user->permanent_village_bn = $request->permanent_village_bn;
        $user->permanent_post_office = $request->permanent_post_office;
        $user->permanent_post_office_bn = $request->permanent_post_office_bn;
        $user->permanent_upazila = $request->permanent_upazila;
        $user->permanent_upazila_bn = $request->permanent_upazila_bn;
        $user->permanent_district = $request->permanent_district;
        $user->permanent_district_bn = $request->permanent_district_bn;
        $user->city = $request->city;
        $user->district = $request->district;
        $user->postal_code = $request->postal_code;

        // Employment Details
        $user->division = $request->division_id;
        $user->department_id = $request->department_id;
        $user->designation_id = $request->designation_id;
        $user->section_id = $request->section_id;
        $user->line_number = $request->line_number;
        $user->shift_id = $request->shift_id;
        $user->employee_type = $request->employee_type;
        $user->grade_lavel = $request->grade_lavel;

        // Salary
        $user->gross_salary = $request->gross_salary ?? 0;
        $user->basic_salary = $request->basic_salary ?? 0;
        $user->house_rent = $request->house_rent ?? 0;
        $user->medical_allowance = $request->medical_allowance ?? 0;
        $user->transport_allowance = $request->transport_allowance ?? 0;
        $user->food_allowance = $request->food_allowance ?? 0;
        $user->conveyance_allowance = $request->conveyance_allowance ?? 0;
        $user->provident_fund = $request->provident_fund ?? 0;

        // Employment Dates
        $user->joining_date = $request->joining_date;
        $user->confirmation_date = $request->confirmation_date;
        $user->retirement_date = $request->retirement_date;
        $user->employee_status = $request->employee_status ?? 'active';

        // References
        $user->other_information = $request->other_information;
        $user->reference_1 = $request->reference_1;
        $user->reference_2 = $request->reference_2;
        $user->nominee = $request->nominee;
        $user->nominee_bn = $request->nominee_bn;
        $user->nominee_relation = $request->nominee_relation;
        $user->nominee_age = $request->nominee_age;

        // Profile & Status
        $user->profile = $request->profile;
        $user->login_status = $request->login_status ? 1 : 0;
        $user->status = 1;

        // Password
        if ($request->password) {
            $user->password_show = $request->password;
            $user->password = Hash::make($request->password);
        }

        // Created Date
        if ($request->created_at) {
            $user->created_at = Carbon::parse($request->created_at . ' ' . now()->format('H:i:s'));
        }

        $this->syncDesignationSalaryDefaults($user, (int) ($request->designation_id ?? 0));

        $user->exited_at = $request->exited_at;

        // Permission (only for admin editing others)
        if ($user->id != Auth::id() && Auth::user()->permission_id == 1) {
            if ($request->role) {
                $user->permission_id = $request->role;
                $user->addedby_at = now();
                $user->addedby_id = Auth::id();
            } else {
                $user->permission_id = null;
                $user->addedby_id = null;
                $user->addedby_at = null;
            }
        }

        $user->save();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            uploadFile($request->image, $user->id, 6, 1, Auth::id());
        }

        // Handle Photo Upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('employees/photos', 'public');
            $user->photo = 'storage/' . $photoPath;
            $user->save();
        }

        // Handle Signature Upload
        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('employees/signatures', 'public');
            $user->signature = 'storage/' . $signaturePath;
            $user->save();
        }

        return $user;
    }

    private function syncDesignationSalaryDefaults(User $user, int $designationId): void
    {
        if ($designationId <= 0 || !class_exists(\ME\Hr\Models\HrDesignation::class)) {
            return;
        }

        $designation = \ME\Hr\Models\HrDesignation::query()->find($designationId);
        if (!$designation) {
            return;
        }

        if (empty($user->gross_salary) || (float) $user->gross_salary <= 0) {
            $user->gross_salary = data_get($designation, 'gross_salary', $user->gross_salary);
        }

        $other = is_array($user->other_information)
            ? $user->other_information
            : json_decode($user->other_information ?? '{}', true);
        if (!is_array($other)) {
            $other = [];
        }

        $salaryInfo = data_get($other, 'salary_info', []);
        if (!is_array($salaryInfo)) {
            $salaryInfo = [];
        }

        $defaults = [
            'gross_salary_comp_1' => data_get($designation, 'gross_salary'),
            'gross_salary_comp_2' => data_get($designation, 'gross_salary'),
            'car_fuel' => data_get($designation, 'car_fuel'),
            'phone_internet' => data_get($designation, 'phone_internet'),
            'extra_facility' => data_get($designation, 'extra_facility'),
            'attendance_bonus' => data_get($designation, 'attendance_bonus'),
            'attendance_bonus_com' => data_get($designation, 'attendance_bonus_com'),
            'holiday_allowance' => data_get($designation, 'holiday_allowance'),
            'weekend_allowance_count' => data_get($designation, 'weekend_allowance_count'),
            'tiffin_allowance' => data_get($designation, 'tiffin_allowance'),
            'night_allowance' => data_get($designation, 'night_allowance'),
            'dinner_allowance' => data_get($designation, 'dinner_allowance'),
            'minimum_tiffin_hour' => data_get($designation, 'minimum_tiffin_hour'),
            'minimum_night_hour' => data_get($designation, 'minimum_night_hour'),
            'minimum_dinner_hour' => data_get($designation, 'minimum_dinner_hour'),
            'meal_payment_way' => data_get($designation, 'meal_payment_way'),
        ];

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $salaryInfo) || $salaryInfo[$key] === null || $salaryInfo[$key] === '' || (is_numeric($salaryInfo[$key]) && (float) $salaryInfo[$key] <= 0)) {
                $salaryInfo[$key] = $value;
            }
        }

        $other['salary_info'] = $salaryInfo;
        $user->other_information = json_encode($other);
    }

    /**
     * Check if user exists with given mobile
     */
    public function existsByMobile(string $mobile): ?User
    {
        return User::where('mobile', $mobile)->first();
    }

    /**
     * Check if user exists with given email
     */
    public function existsByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Check if soft deleted user exists with given mobile
     */
    public function existsTrashedByMobile(string $mobile): ?User
    {
        return User::withTrashed()->where('mobile', $mobile)->first();
    }

    /**
     * Delete user media files
     */
    public function deleteMediaFiles(User $user): void
    {
        $mediaFiles = Media::where('src_type', 6)->where('src_id', $user->id)->get();
        foreach ($mediaFiles as $media) {
            if (File::exists($media->file_url)) {
                File::delete($media->file_url);
            }
            $media->delete();
        }
    }

    /**
     * Force delete user with all data
     */
    public function forceDelete(User $user): void
    {
        $this->deleteMediaFiles($user);
        $user->forceDelete();
    }

    /**
     * Soft delete user
     */
    public function softDelete(User $user): void
    {
        $user->delete();
    }

    /**
     * Restore soft deleted user
     */
    public function restore(int $userId): ?User
    {
        $user = User::onlyTrashed()->find($userId);
        if ($user) {
            $user->restore();
        }
        return $user;
    }
}
