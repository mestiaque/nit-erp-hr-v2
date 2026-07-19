<?php

namespace ME\Hr\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HrEmployee extends BaseHrModel
{
    protected $table = 'hr_employees';

    public function imageFile(): HasOne
    {
        $mediaClass = class_exists(\App\Models\Media::class) ? \App\Models\Media::class : self::class;
        return $this->hasOne($mediaClass, 'src_id')->where('src_type', 6)->where('use_Of_file', 1);
    }

    public function image($type = null): string
    {
        if (class_exists(\App\Models\Media::class) && $this->imageFile) {
            return match ($type) {
                'sm'    => $this->imageFile->file_url_sm ?? 'medies/profile.png',
                'md'    => $this->imageFile->file_url_md ?? 'medies/profile.png',
                'lg'    => $this->imageFile->file_url_lg ?? 'medies/profile.png',
                default => $this->imageFile->file_url   ?? 'medies/profile.png',
            };
        }
        return 'medies/profile.png';
    }

    public function scopeFilterByType(Builder $query, string $type): Builder
    {
        return $type === 'employee' ? $query : $query->whereRaw('1 = 0');
    }

    public function scopeNaturalOrderById(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CAST(REGEXP_REPLACE(employee_id, '[^0-9]', '') AS UNSIGNED) ASC")
            ->orderBy('employee_id', 'asc');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(HrClassification::class, 'classification_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(HrSection::class, 'section_id');
    }

    public function subSection(): BelongsTo
    {
        return $this->belongsTo(HrSubSection::class, 'sub_section_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(HrDesignation::class, 'designation_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrShift::class, 'shift_id');
    }

    public function shiftRule(): HasOne
    {
        return $this->hasOne(HrEmployeeShiftRule::class, 'employee_id');
    }

    /**
     * With an active Auto Roster rule, the rule's primary shift is worked every
     * day except its chosen day_of_week, which alternates weekly between the
     * primary and alt shift starting from anchor_date (first on/after anchor ->
     * alt, the occurrence a week later -> primary, then alt again, and so on).
     * Without a rule, always the employee's own default shift.
     */
    public function resolveShiftForDate($date): ?HrShift
    {
        $rule = $this->shiftRule;
        if (!$rule || !$rule->is_active) {
            return $this->shift;
        }

        $date = \Carbon\Carbon::parse($date)->startOfDay();

        if ((int) $rule->day_of_week !== $date->dayOfWeek) {
            return $rule->primaryShift ?: $this->shift;
        }

        $firstOccurrence = \Carbon\Carbon::parse($rule->anchor_date)->startOfDay();
        while ($firstOccurrence->dayOfWeek !== (int) $rule->day_of_week) {
            $firstOccurrence->addDay();
        }

        if ($date->lt($firstOccurrence)) {
            return $rule->primaryShift ?: $this->shift;
        }

        $weeksSince = (int) $firstOccurrence->diffInWeeks($date);

        return $weeksSince % 2 === 0
            ? ($rule->altShift ?: $this->shift)
            : ($rule->primaryShift ?: $this->shift);
    }

    public function workingPlace(): BelongsTo
    {
        return $this->belongsTo(HrWorkingPlace::class, 'working_place_id');
    }

    public function floorLine(): BelongsTo
    {
        return $this->belongsTo(HrFloorLine::class, 'floor_line_id');
    }

    public function basicInfo(): HasOne
    {
        return $this->hasOne(HrEmployeeBasicInfo::class, 'employee_id');
    }

    public function salaryInfo(): HasOne
    {
        return $this->hasOne(HrEmployeeSalaryInfo::class, 'employee_id');
    }

    public function nomineeRecord(): HasOne
    {
        return $this->hasOne(HrEmployeeNominee::class, 'employee_id');
    }

    public function ageVerification(): HasOne
    {
        return $this->hasOne(HrEmployeeAgeVerification::class, 'employee_id');
    }

    public function separation(): HasOne
    {
        return $this->hasOne(HrEmployeeSeparation::class, 'employee_id');
    }

    public function finalSettlement(): HasOne
    {
        return $this->hasOne(HrEmployeeFinalSettlement::class, 'employee_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(HrEmployeeAddress::class, 'employee_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(HrEmployeeLeave::class, 'employee_id');
    }

    public function otherTransactions(): HasMany
    {
        return $this->hasMany(HrEmployeeOtherTransaction::class, 'employee_id');
    }

    public function increments(): HasMany
    {
        return $this->hasMany(HrEmployeeSalaryIncrement::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrEmployeeDocument::class, 'employee_id');
    }

    public function gatePasses(): HasMany
    {
        return $this->hasMany(HrEmployeeGatePass::class, 'employee_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(HrEmployeeAsset::class, 'employee_id');
    }

    public function otherInfo(): array
    {
        return [
            'profile' => [
                'sub_section_id' => $this->sub_section_id,
                'working_place_id' => $this->working_place_id,
                'weekend' => $this->weekend,
                'education_bn' => $this->basicInfo?->bn_educational_experience,
            ],
            'salary_info' => [
                'gross_salary_comp_1' => $this->salaryInfo?->gross_salary_comp1,
                'gross_salary_comp_2' => $this->salaryInfo?->gross_salary_comp2,
                'car_fuel' => $this->salaryInfo?->car_fuel,
                'phone_internet' => $this->salaryInfo?->phone_internet,
                'extra_facility' => $this->salaryInfo?->extra_facility,
                'tax' => $this->salaryInfo?->tax,
                'tax_calculate_by' => $this->salaryInfo?->tax_calculate_by,
            ],
            'nominee_info' => $this->nomineeRecord ? $this->nomineeRecord->toArray() : [],
            'age_verification' => $this->ageVerification ? $this->ageVerification->toArray() : [],
            'resign_info' => $this->separation ? $this->separation->toArray() : [],
            'final_settlement' => $this->finalSettlement ? $this->finalSettlement->toArray() : [],
        ];
    }

    public function getOtherInformationAttribute(): array
    {
        $nom = $this->nomineeRecord;
        return [
            'nominee_info' => $nom ? [
                'nominee_image'          => $nom->photo,
                'nominee_district'       => $nom->district?->name,
                'nominee_po_station'     => $nom->policeStation?->name,
                'nominee_post_office'    => $nom->post_office,
                'nominee_post_office_bn' => $nom->bn_post_office,
                'nominee_village'        => $nom->village,
                'nominee_village_bn'     => $nom->bn_village,
                'nominee_nid'            => $nom->nid_no,
                'nominee_mobile'         => $nom->mobile_no,
                'nominee_relation'       => $nom->relation,
                'nominee_relation_bn'    => $nom->bn_relation,
                'nominee_bn_name'        => $nom->bn_name,
                'nominee_age'            => $nom->age,
                'distribution_net_payment'    => $nom->net_payment,
                'distribution_provident_fund' => $nom->provident_fund,
                'distribution_insurance'      => $nom->insurance,
                'distribution_accident_fine'  => $nom->accident_fine,
                'distribution_profit'         => $nom->profit,
                'distribution_others'         => $nom->others,
            ] : [],
        ];
    }

    public function getNomineeAttribute(): ?string
    {
        return $this->nomineeRecord?->name;
    }

    public function getNomineeRelationAttribute(): ?string
    {
        return $this->nomineeRecord?->relation;
    }

    public function getNomineeAgeAttribute(): ?int
    {
        return $this->nomineeRecord?->age;
    }

    public function setTypes(string $type): static
    {
        return $this;
    }

    public function getJoiningDateAttribute(): ?\Carbon\Carbon
    {
        return $this->join_date ? \Carbon\Carbon::parse($this->join_date) : null;
    }

    public function getEmployeeTypeAttribute(): ?int
    {
        return $this->classification_id;
    }

    public function getLineNumberAttribute(): ?int
    {
        return $this->floor_line_id;
    }

    public function getMobileAttribute(): ?string
    {
        return $this->personal_contact;
    }

    public function getEmergencyMobileAttribute(): ?string
    {
        return $this->emergency_contact;
    }

    public function getGrossSalaryAttribute()
    {
        return $this->salaryInfo?->gross_salary;
    }

    public function getSalaryTypeAttribute(): ?string
    {
        // Pay Mode can be set from either the Salary Info form (salary_infos.payment_method_id)
        // or the Basic Info form (basic_infos.payment_method_id) — read whichever was used.
        $id = $this->salaryInfo?->payment_method_id ?? $this->basicInfo?->payment_method_id;
        return $id ? HrPaymentMethod::find($id)?->name : null;
    }

    public function setJoiningDateAttribute($value): void
    {
        $this->attributes['join_date'] = $value;
    }

    public function setEmployeeTypeAttribute($value): void
    {
        $this->attributes['classification_id'] = $value;
    }

    public function setLineNumberAttribute($value): void
    {
        $this->attributes['floor_line_id'] = $value;
    }

    public function setMobileAttribute($value): void
    {
        $this->attributes['personal_contact'] = $value;
    }

    public function setEmergencyMobileAttribute($value): void
    {
        $this->attributes['emergency_contact'] = $value;
    }

    public function getFatherNameAttribute(): ?string
    {
        return $this->basicInfo?->father_name;
    }

    public function getFatherNameBnAttribute(): ?string
    {
        return $this->basicInfo?->bn_father_name;
    }

    public function getMotherNameAttribute(): ?string
    {
        return $this->basicInfo?->mother_name;
    }

    public function getMotherNameBnAttribute(): ?string
    {
        return $this->basicInfo?->bn_mother_name;
    }

    public function getSpouseNameAttribute(): ?string
    {
        return $this->basicInfo?->spouse_name;
    }

    public function getSpouseNameBnAttribute(): ?string
    {
        return $this->basicInfo?->bn_spouse_name;
    }

    public function getGenderAttribute(): ?string
    {
        $basicInfo = $this->basicInfo;

        return $basicInfo && $basicInfo->sex_id
            ? HrSex::query()->find($basicInfo->sex_id)?->name
            : null;
    }

    public function getReligionAttribute(): ?string
    {
        $basicInfo = $this->basicInfo;

        return $basicInfo && $basicInfo->religion_id
            ? HrReligion::query()->find($basicInfo->religion_id)?->name
            : null;
    }

    public function getMaritalStatusAttribute(): ?string
    {
        $basicInfo = $this->basicInfo;

        return $basicInfo && $basicInfo->marital_status_id
            ? HrMaritalStatus::query()->find($basicInfo->marital_status_id)?->name
            : null;
    }

    public function getDobAttribute(): mixed
    {
        return $this->basicInfo?->birth_date;
    }

    public function getBloodGroupAttribute(): ?string
    {
        return $this->basicInfo?->blood_group;
    }

    public function getNationalityAttribute(): ?string
    {
        $id = $this->basicInfo?->nationality_country_id;
        return $id ? HrGeoLocation::find($id)?->name : null;
    }

    public function getNidNumberAttribute(): ?string
    {
        return $this->basicInfo?->national_id_no;
    }

    public function getBirthRegistrationAttribute(): ?string
    {
        return $this->basicInfo?->birth_registration_no;
    }

    public function getPassportNoAttribute(): ?string
    {
        return $this->basicInfo?->passport_no;
    }

    public function getDrivingLicenseAttribute(): ?string
    {
        return $this->basicInfo?->driving_license_no;
    }

    public function getDistinguishedMarkAttribute(): ?string
    {
        return $this->basicInfo?->special_id_sign;
    }

    public function getEducationAttribute(): ?string
    {
        return $this->basicInfo?->educational_experience;
    }

    public function getReference1Attribute(): ?string
    {
        return $this->basicInfo?->reference_name;
    }

    public function getReference2Attribute(): ?string
    {
        return $this->basicInfo?->reference_designation;
    }

    public function getBoysAttribute(): ?int
    {
        return $this->basicInfo?->children_boys;
    }

    public function getGirlsAttribute(): ?int
    {
        return $this->basicInfo?->children_girls;
    }

    public function permanentAddress(): HasOne
    {
        return $this->hasOne(HrEmployeeAddress::class, 'employee_id')->where('type', 'permanent');
    }

    public function presentAddress(): HasOne
    {
        return $this->hasOne(HrEmployeeAddress::class, 'employee_id')->where('type', 'present');
    }

    public function getPermanentDistrictAttribute(): ?string
    {
        return $this->permanentAddress?->district?->name;
    }

    public function getPermanentDistrictBnAttribute(): ?string
    {
        $loc = $this->permanentAddress?->district;
        return $loc ? ($loc->bn_name ?? $loc->name) : null;
    }

    public function getPermanentUpazilaAttribute(): ?string
    {
        return $this->permanentAddress?->policeStation?->name;
    }

    public function getPermanentUpazilaBnAttribute(): ?string
    {
        $loc = $this->permanentAddress?->policeStation;
        return $loc ? ($loc->bn_name ?? $loc->name) : null;
    }

    public function getPermanentPostOfficeAttribute(): ?string
    {
        return $this->permanentAddress?->post_office;
    }

    public function getPermanentVillageAttribute(): ?string
    {
        return $this->permanentAddress?->village;
    }

    public function getPermanentPostOfficeBnAttribute(): ?string
    {
        return $this->permanentAddress?->bn_post_office;
    }

    public function getPermanentVillageBnAttribute(): ?string
    {
        return $this->permanentAddress?->bn_village;
    }

    public function getPresentDistrictAttribute(): ?string
    {
        return $this->presentAddress?->district?->name;
    }

    public function getPresentDistrictBnAttribute(): ?string
    {
        $loc = $this->presentAddress?->district;
        return $loc ? ($loc->bn_name ?? $loc->name) : null;
    }

    public function getPresentUpazilaAttribute(): ?string
    {
        return $this->presentAddress?->policeStation?->name;
    }

    public function getPresentUpazilaBnAttribute(): ?string
    {
        $loc = $this->presentAddress?->policeStation;
        return $loc ? ($loc->bn_name ?? $loc->name) : null;
    }

    public function getPresentPostOfficeAttribute(): ?string
    {
        return $this->presentAddress?->post_office;
    }

    public function getPresentVillageAttribute(): ?string
    {
        return $this->presentAddress?->village;
    }

    public function getPresentPostOfficeBnAttribute(): ?string
    {
        return $this->presentAddress?->bn_post_office;
    }

    public function getPresentVillageBnAttribute(): ?string
    {
        return $this->presentAddress?->bn_village;
    }
}
