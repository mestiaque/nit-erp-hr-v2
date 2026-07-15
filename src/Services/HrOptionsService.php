<?php

namespace ME\Hr\Services;

class HrOptionsService
{
    public static function getOptions(): array
    {
        // Use Eloquent models directly for now; can be optimized/cached later
        $classifications = \ME\Hr\Models\HrClassification::where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']);
        $departments     = \ME\Hr\Models\HrDepartment::where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']);
        $sections        = \ME\Hr\Models\HrSection::where('status', 'active')->orderBy('name')->get(['id', 'name', 'bn_name']);
        $subSections     = \ME\Hr\Models\HrSubSection::orderBy('name')->get(['id', 'name', 'department_id', 'section_id', 'salary_type', 'approve_man_power', 'bn_name', 'roster_shift_id']);
        $designations    = \ME\Hr\Models\HrDesignation::orderBy('name')->get(['id', 'name', 'bn_name', 'grade']);
        $shifts          = \ME\Hr\Models\HrShift::orderBy('name')->get(['id', 'name', 'bn_name']);
        $workingPlaces   = \ME\Hr\Models\HrWorkingPlace::orderBy('name')->get(['id', 'name', 'bn_name']);
        $lines           = \ME\Hr\Models\HrFloorLine::where('status', 'active')->orderBy('line_name')->get()->map(static function ($line) {
            return (object) [
                'id' => $line->id,
                'name' => $line->line_name,
                'bn_name' => $line->bn_line_name,
                'slug' => $line->line_name,
            ];
        });
        $holidays        = \ME\Hr\Models\HrHoliday::orderBy('from_date')->get();
        return [
            'classifications' => $classifications,
            'departments'     => $departments,
            'sections'        => $sections,
            'subSections'     => $subSections,
            'designations'    => $designations,
            'shifts'          => $shifts,
            'workingPlaces'   => $workingPlaces,
            'lines'           => $lines,
            'holidays'        => $holidays,
        ];
    }

    public static function getOptionsForEmployee(): callable
    {
        $options = self::getOptions();

        return function ($employee, $request = null, $factory = null, $salaryKey = null, $profile = null, $nominee = null) use ($options) {
            $language = data_get($request ?? null, 'language', 'en');

            $isBangla = $language === 'bn';
            $t = fn (string $bn, string $en) => $isBangla ? $bn : $en;
            $na = $t('--', '--');

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
            $gender = $isBangla ? \ME\Hr\Models\HrSex::where('name', $employee->gender)->first()?->bn_name : $employee->gender ?? $na;
            $designationModel = optional($employee->designation);
            $designationAttr = optional(\ME\Hr\Models\HrDesignation::find($employee->designation_id));
            $grade = $designationAttr->grade_id ?? $designationModel->grade_id ?? data_get($employee, 'designation_grade') ?? $na;
            $designation = $isBangla
                ? ($designationModel->bn_name ?? data_get($designationAttr, 'bn_name') ?? $designationModel->name ?? data_get($designationAttr, 'name') ?? data_get($employee, 'designation_bn_name') ?? data_get($employee, 'designation_name') ?? $na)
                : ($designationModel->name ?? data_get($designationAttr, 'name') ?? data_get($employee, 'designation_name') ?? $designationModel->bn_name ?? data_get($designationAttr, 'bn_name') ?? data_get($employee, 'designation_bn_name') ?? $na);

            $sectionAttr = optional(\ME\Hr\Models\HrSection::find($employee->section_id));
            $section = $isBangla
                ? (data_get($sectionAttr, 'bn_name') ?? data_get($sectionAttr, 'name') ?? data_get($employee, 'section_bn_name') ?? data_get($employee, 'section_name') ?? $na)
                : (data_get($sectionAttr, 'name') ?? data_get($employee, 'section_name') ?? data_get($sectionAttr, 'bn_name') ?? data_get($employee, 'section_bn_name') ?? $na);
            $subSections = $options['subSections'] ?? collect();
            $subSectionId = $employee->otherInfo()['profile']['sub_section_id'] ?? null;
            $subSection = $subSections->where('id', $subSectionId)->first();
            $subSection = $isBangla ? optional($subSection)->bn_name : optional($subSection)->name;
            $line = $isBangla
                ? optional($options['lines']->where('id', $employee->floor_line_id)->first())->bn_name
                : optional($options['lines']->where('id', $employee->floor_line_id)->first())->slug;
            $workingPlaces = $options['workingPlaces'] ?? collect();
            $workingPlace = $workingPlaces->where('id', $employee->otherInfo()['profile']['working_place_id'] ?? null)->first();
            $workingPlace = $isBangla
                ? optional($workingPlace)->bn_name
                : optional($workingPlace)->name;
            $masterData = $options;
            $employeeOthers = method_exists($employee, 'otherInfo') ? $employee->otherInfo() : [];
            $departments = $masterData['departments'] ?? collect();
            $department = $departments->where('id', $employee->department_id)->first();
            $department = $isBangla
                ? optional($department)->bn_name
                : optional($department)->name;
            $jobType = $isBangla
                ? optional($masterData['classifications']->where('id', $employee->classification_id)->first())->bn_name
                : optional($masterData['classifications']->where('id', $employee->classification_id)->first())->name;
            $employeeId = data_get($employee, 'employee_id', $na);
            $education = $isBangla
                ? $employeeOthers['profile']['education_bn'] ?? $na
                : $employee->education ?? $na;

            $religion = $isBangla
                ? \ME\Hr\Models\HrReligion::where('name', $employee->religion)->first()?->bn_name ?? $employee->religion ?? $na
                : $employee->religion ?? $na;
            $nid = data_get($employee, 'nid_number', 0);
            $girls = $employee->girls ?? 0;
            $boys = $employee->boys ?? 0;
            $maritalStatus = $isBangla
                ? \ME\Hr\Models\HrMaritalStatus::where('name', $employee->marital_status)->first()?->bn_name ?? $employee->marital_status ?? $na
                : $employee->marital_status ?? $na;

            $bloodGroup = $employee->blood_group ?? $na;
            $mobileNumber = $employee->mobile ?? $na;
            $emergencyMobile = $employee->emergency_mobile ?? $na;
            // dd($employee);

            $presentAddress = collect([
                data_get($employee, 'present_address'),
                data_get($employee, 'present_village'),
                data_get($employee, 'present_post_office'),
                data_get($employee, 'present_upazila'),
                data_get($employee, 'present_district'),
            ])->filter(fn ($v) => filled($v))->implode(', ');
            $permanentAddress = collect([
                data_get($employee, 'permanent_address'),
                data_get($employee, 'permanent_village'),
                data_get($employee, 'permanent_post_office'),
                data_get($employee, 'permanent_upazila'),
                data_get($employee, 'permanent_district'),
            ])->filter(fn ($v) => filled($v))->implode(', ');
            $presentAddressBn = collect([
                data_get($employee, 'present_address_bn'),
                data_get($employee, 'present_village_bn'),
                data_get($employee, 'present_post_office_bn'),
                data_get($employee, 'present_upazila_bn'),
                data_get($employee, 'present_district_bn'),
            ])->filter(fn ($v) => filled($v))->implode(', ');
            $permanentAddressBn = collect([
                data_get($employee, 'permanent_address_bn'),
                data_get($employee, 'permanent_village_bn'),
                data_get($employee, 'permanent_post_office_bn'),
                data_get($employee, 'permanent_upazila_bn'),
                data_get($employee, 'permanent_district_bn'),
            ])->filter(fn ($v) => filled($v))->implode(', ');
            $presentAddress = $presentAddress ?: data_get($employee, 'address', $na);
            $permanentAddress = $permanentAddress ?: data_get($employee, 'address', $na);
            // Present Address (EN)
            $presentAddressFull = collect([
                    'Address' => data_get($employee, 'present_address'),
                    'Village' => data_get($employee, 'present_village'),
                    'Post Office' => data_get($employee, 'present_post_office'),
                    'Upazila' => data_get($employee, 'present_upazila'),
                    'District' => data_get($employee, 'present_district'),
                ])
                ->filter(fn ($v) => filled($v))
                ->map(fn ($v, $key) => "{$key}: {$v}")
                ->implode(', ');

            // Permanent Address (EN)
            $permanentAddressFull = collect([
                    'Address' => data_get($employee, 'permanent_address'),
                    'Village' => data_get($employee, 'permanent_village'),
                    'Post Office' => data_get($employee, 'permanent_post_office'),
                    'Upazila' => data_get($employee, 'permanent_upazila'),
                    'District' => data_get($employee, 'permanent_district'),
                ])
                ->filter(fn ($v) => filled($v))
                ->map(fn ($v, $key) => "{$key}: {$v}")
                ->implode(', ');

            // Present Address (BN)
            $presentAddressBnFull = collect([
                    'ঠিকানা' => data_get($employee, 'present_address_bn'),
                    'গ্রাম' => data_get($employee, 'present_village_bn'),
                    'ডাকঘর' => data_get($employee, 'present_post_office_bn'),
                    'উপজেলা' => data_get($employee, 'present_upazila_bn'),
                    'জেলা' => data_get($employee, 'present_district_bn'),
                ])
                ->filter(fn ($v) => filled($v))
                ->map(fn ($v, $key) => "{$key}: {$v}")
                ->implode(', ');



            // Permanent Address (BN)
            $permanentAddressBnFull = collect([
                    'ঠিকানা' => data_get($employee, 'permanent_address_bn'),
                    'গ্রাম' => data_get($employee, 'permanent_village_bn'),
                    'ডাকঘর' => data_get($employee, 'permanent_post_office_bn'),
                    'উপজেলা' => data_get($employee, 'permanent_upazila_bn'),
                    'জেলা' => data_get($employee, 'permanent_district_bn'),
                ])
                ->filter(fn ($v) => filled($v))
                ->map(fn ($v, $key) => "{$key}: {$v}")
                ->implode(', ');

            // Salary breakdown — apply increment override so reports use effective salary
            $sal        = function_exists('hr_employee_salary') ? hr_employee_salary($employee, $factory ?? null, $salaryKey ?? null) : [];
            $sal        = \ME\Hr\Models\HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $employee->id);
            $gross      = $sal['gross'] ?? null;
            $basic      = $sal['basic'] ?? null;
            $house      = $sal['house'] ?? null;
            $medical    = $sal['medical'] ?? null;
            $transport  = $sal['transport'] ?? null;
            $food       = $sal['food'] ?? null;
            $otRate     = ($basic ?? 0) > 0 ? round(($basic / 208) * 2, 2) : 0;
            $deductFrom = $sal['deduct_from'] ?? null;

            $qualification = data_get($employee, 'qualification', data_get($profile, 'qualification', $na));
            $birthDate = data_get($employee, 'date_of_birth', data_get($employee, 'dob'));
            $employeeAge = isset($employeeOthers['age_verification']['verified_age'])
                ? $employeeOthers['age_verification']['verified_age']
                : \Illuminate\Support\Carbon::parse($birthDate)->age;
            $employeePhoto = method_exists($employee, 'image') ? $employee->image() : null;

            $shift = $employee->shift_id ? \ME\Hr\Models\HrShift::find($employee->shift_id) : null;


            // Nominee fields (EN & BN)
            $nomineeName = data_get($employee, 'nominee', data_get($nominee, 'nominee', $na));
            $nomineeNameBn = data_get($employee, 'nominee_bn_name', data_get($nominee, 'nominee_bn_name', $na));
            $nomineeRelation = data_get($employee, 'nominee_relation', data_get($nominee, 'nominee_relation', $na));
            $nomineeRelationBn = data_get($employee, 'nominee_relation_bn', data_get($nominee, 'nominee_relation_bn', $na));
            $nomineeAge = data_get($employee, 'nominee_age', data_get($nominee, 'nominee_age', ''));
            $nomineeVillage = data_get($nominee, 'nominee_village', $na);
            $nomineeVillageBn = data_get($nominee, 'nominee_village_bn', $na);
            $nomineePoStation = data_get($nominee, 'nominee_po_station', $na);
            $nomineePoStationBn = \ME\Hr\Models\HrGeoLocation::where('name', $nomineePoStation)->where('type', 'police_station')->first()?->bn_name ?? $na;
            $nomineePostOffice = data_get($nominee, 'nominee_post_office', $na);
            $nomineePostOfficeBn = data_get($nominee, 'nominee_post_office_bn', $na);
            $nomineeDistrict = data_get($nominee, 'nominee_district', $na);
            $nomineeDistrictBn = \ME\Hr\Models\HrGeoLocation::where('name', $nomineeDistrict)->where('type', 'district')->first()?->bn_name ?? $na;
            $nomineeNid = data_get($nominee, 'nominee_nid', $na);
            $nomineeMobile = data_get($nominee, 'nominee_mobile', $na);
            $nomineeImage = data_get($nominee, 'nominee_image', null);
            $nationality = data_get($nominee, 'nominee_nationality', data_get($employee, 'nationality', $t('বাংলাদেশী', 'Bangladeshi')));
            $permanentAddress = collect([
                data_get($employee, 'permanent_village'),
                data_get($employee, 'permanent_post_office'),
                data_get($employee, 'permanent_upazila'),
                data_get($employee, 'permanent_district'),
            ])->filter(fn ($value) => filled($value))->implode(', ');
            $presentAddress = collect([
                data_get($employee, 'present_village'),
                data_get($employee, 'present_post_office'),
                data_get($employee, 'present_upazila'),
                data_get($employee, 'present_district'),
            ])->filter(fn ($value) => filled($value))->implode(', ');
            $permanentAddress = $permanentAddress ?: data_get($employee, 'permanent_address', data_get($employee, 'address', $na));
            $presentAddress = $presentAddress ?: data_get($employee, 'present_address', data_get($employee, 'address', $na));

            // --- Salary/Earnings/Deductions/Leaves/Increments Logic ---
            // These three are only queried the first time their getter closure is actually
            // called below, instead of on every getOptionsForEmployee() invocation regardless
            // of whether the caller needs them.
            $earningsDeductions = null;
            $loadEarningsDeductions = function () use ($employee, &$earningsDeductions) {
                if ($earningsDeductions === null) {
                    $earningsDeductions = \ME\Hr\Models\HrEmployeeOtherTransaction::query()
                        ->where('employee_id', $employee->id)
                        ->orderBy('txn_date')
                        ->get()
                        ->map(static function ($row) {
                            return [
                                'date' => $row->txn_date,
                                'advance_iou' => (float) ($row->advance_iou ?? 0),
                                'ot' => (float) ($row->ot_adjust ?? 0),
                                'day' => (float) ($row->day_adjust ?? 0),
                                'earnings' => (float) ($row->earnings ?? 0),
                                'deductions' => (float) ($row->deductions ?? 0),
                                'remarks' => $row->remarks,
                            ];
                        })->all();
                }
                return $earningsDeductions;
            };

            $increments = null;
            $loadIncrements = function () use ($employee, &$increments) {
                if ($increments === null) {
                    $increments = \ME\Hr\Models\HrEmployeeSalaryIncrement::query()
                        ->where('employee_id', $employee->id)
                        ->orderByDesc('increment_date')
                        ->get()
                        ->map(static function ($row) {
                            return [
                                'increment_date' => $row->increment_date,
                                'previous_salary' => (float) ($row->previous_salary ?? 0),
                                'increment_amount' => (float) ($row->increment_amount ?? 0),
                                'new_salary' => (float) ($row->new_salary ?? 0),
                                'classification_id' => $row->classification_id,
                                'department_id' => $row->department_id,
                                'section_id' => $row->section_id,
                                'designation_id' => $row->designation_id,
                            ];
                        })->all();
                }
                return $increments;
            };

            $leaves = null;
            $loadLeaves = function () use ($employee, &$leaves) {
                if ($leaves === null) {
                    $leaves = \ME\Hr\Models\HrEmployeeLeave::query()
                        ->where('employee_id', $employee->id)
                        ->orderByDesc('leave_from')
                        ->get()
                        ->map(static function ($row) {
                            return [
                                'application_date' => $row->application_date,
                                'application_no' => $row->application_no,
                                'leave_type_id' => $row->leave_type_id,
                                'start_date' => $row->leave_from,
                                'end_date' => $row->leave_to,
                                'reason' => $row->reason,
                                'remarks' => $row->remarks,
                                'status' => $row->status,
                            ];
                        })->all();
                }
                return $leaves;
            };

            // Earnings/Deductions summary logic (for a date range)
            $getEarningsDeductionsSummary = function($from = null, $to = null) use ($loadEarningsDeductions) {
                $earningsDeductions = $loadEarningsDeductions();
                $earnings    = 0.0;
                $deductions  = 0.0;
                $advanceIou  = 0.0;
                $otPlusHours = 0.0;
                $otMinusHours = 0.0;
                $dayPlus     = 0.0;
                $dayMinus    = 0.0;
                foreach ($earningsDeductions as $entry) {
                    $date = data_get($entry, 'date');
                    if ($from && $to && ($date < $from || $date > $to)) continue;
                    $earnings   += (float) data_get($entry, 'earnings',    0);
                    $deductions += (float) data_get($entry, 'deductions',  0);
                    $advanceIou += (float) data_get($entry, 'advance_iou', 0);
                    $otHours = (float) data_get($entry, 'ot', 0);
                    if ($otHours >= 0) {
                        $otPlusHours += $otHours;
                    } else {
                        $otMinusHours += abs($otHours);
                    }
                    $days = (float) data_get($entry, 'day', 0);
                    if ($days >= 0) {
                        $dayPlus += $days;
                    } else {
                        $dayMinus += abs($days);
                    }
                }
                return compact('earnings', 'deductions', 'advanceIou', 'otPlusHours', 'otMinusHours', 'dayPlus', 'dayMinus');
            };

            // Increments logic
            $getIncrements = function() use ($loadIncrements) {
                return $loadIncrements();
            };

            // Leaves logic
            $getLeaves = function() use ($loadLeaves) {
                return $loadLeaves();
            };

            $getWeekendToRegularSummary = function($from = null, $to = null) use ($employee) {
                if (!$from || !$to) {
                    return [];
                }

                $attendancePack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                    $employee->id,
                    $from,
                    $to
                );

                return $attendancePack['weekend_to_regular'] ?? [];
            };

            // Salary report logic (aggregate salary, earnings, deductions, etc.)
            $getSalaryReport = function($from = null, $to = null) use ($employee, $getEarningsDeductionsSummary) {
                $sal        = function_exists('hr_employee_salary') ? hr_employee_salary($employee) : [];
                $sal        = \ME\Hr\Models\HrEmployeeSalaryIncrement::applyIncrementOverride($sal, $employee->id);
                $otRate     = (float) ($sal['ot_rate'] ?? 0);
                $gross      = (float) ($sal['gross'] ?? $employee->gross_salary ?? 0);
                $basic      = (float) ($sal['basic'] ?? $employee->basic_salary ?? 0);
                $deductFrom = (string) ($sal['deduct_from'] ?? 'gross');
                $dayBase    = $deductFrom === 'basic' ? $basic : $gross;
                $dayRate    = $dayBase > 0 ? ($dayBase / 30) : 0;
                $extras     = $getEarningsDeductionsSummary($from, $to);
                $otEarn     = $extras['otPlusHours']  * $otRate;
                $otDeduct   = $extras['otMinusHours'] * $otRate;
                $dayEarn    = $extras['dayPlus']   * $dayRate;
                $dayDeduct  = $extras['dayMinus']  * $dayRate;

                // Attendance bonus from designation / employee salary_info
                $factoryNo    = (int) (function_exists('hr_factory') ? (hr_factory('factory_no') ?? 0) : 0);
                $si           = $employee->salaryInfo;
                $designation  = $employee->designation
                    ?? ($employee->designation_id ? \ME\Hr\Models\HrDesignation::find($employee->designation_id) : null);
                $attBonusBase = ($factoryNo === 1 || $factoryNo === 2)
                    ? (float) ($sal['attendance_bonus_com'] ?? $si?->attendance_bonus_com ?? data_get($designation, 'attendance_bonus_com', 0))
                    : (float) ($sal['attendance_bonus']     ?? $si?->attendance_bonus     ?? data_get($designation, 'attendance_bonus',     0));

                // Eligibility: no absent, no leave in the period; also collect meal allowances
                $attBonus  = 0.0;
                $mealTotal = 0.0;
                if ($from && $to) {
                    $attendancePack = \ME\Hr\Services\EmployeeAttendanceService::getEmployeeAttendanceByDate(
                        $employee->id, $from, $to
                    );
                    $summ   = $attendancePack['summary'] ?? [];
                    $absent = (int) ($summ['totalAbsent'] ?? 0);
                    $leave  = (int) ($summ['totalLeave']  ?? 0);
                    if ($attBonusBase > 0 && $absent === 0 && $leave === 0) {
                        $attBonus = $attBonusBase;
                    }
                    $meal      = $attendancePack['meal'] ?? [];
                    $mealTotal = (float) ($meal['meal_total'] ?? 0);
                }

                // advance_iou is a deduction (an advance the employee already took), not an earning.
                $extraEarningAmount  = $extras['earnings'] + $otEarn + $dayEarn + $attBonus + ($mealTotal ?? 0.0);
                $extraDeductionAmount = $extras['deductions'] + $extras['advanceIou'] + $otDeduct + $dayDeduct;
                $totalEarn   = $extraEarningAmount;
                $totalDeduct = $extraDeductionAmount;
                $net         = $gross + $totalEarn - $totalDeduct;
                return [
                    'gross'        => $gross,
                    'basic'        => $basic,
                    'total_earn'   => $totalEarn,
                    'total_deduct' => $totalDeduct,
                    'net'          => $net,
                    'ot'           => $otEarn - $otDeduct,
                    'attendance_bonus' => $attBonus,
                    'meal_total'   => $mealTotal ?? 0.0,
                ];
            };

            return [
                'company_name'              => $companyName,
                'company_address'           => $companyAddress,
                'employee_name'             => $employeeName,
                'father_name'               => $fatherName,
                'mother_name'               => $motherName,
                'spouse_name'               => $spouseName,
                'education'                 => $education,
                'joining_date'              => $joiningDate,
                'designation'               => $designation,
                'designation_full'          => $designationAttr,
                'designation_weekend_allowance_count' => data_get($designationAttr, 'weekend_allowance_count'),
                'designation_holiday_allowance' => data_get($designationAttr, 'holiday_allowance'),
                'department'                => $department,
                'grade'                     => $grade,
                'section'                   => $section,
                'shift'                     => $shift,
                'line'                      => $line,
                'working_place'             => $workingPlace,
                'sub_section'               => $subSection,
                'job_type'                  => $jobType,
                'employee_id'               => $employeeId,
                'present_address'           => $presentAddress,
                'permanent_address'         => $permanentAddress,
                'present_address_bn'        => $presentAddressBn,
                'permanent_address_bn'      => $permanentAddressBn,
                'present_address_full'      => $presentAddressFull,
                'permanent_address_full'    => $permanentAddressFull,
                'present_address_bn_full'   => $presentAddressBnFull,
                'permanent_address_bn_full' => $permanentAddressBnFull,
                'qualification'             => $qualification,
                'birth_date'                => $birthDate,
                'employee_age'              => $employeeAge,
                'employee_photo'            => $employeePhoto,
                'nominee_name'              => $nomineeName,
                'nominee_name_bn'           => $nomineeNameBn,
                'nominee_relation'          => $nomineeRelation,
                'nominee_relation_bn'       => $nomineeRelationBn,
                'nominee_age'               => $nomineeAge,
                'nominee_village'           => $nomineeVillage,
                'nominee_village_bn'        => $nomineeVillageBn,
                'nominee_po_station'        => $nomineePoStation,
                'nominee_po_station_bn'     => $nomineePoStationBn,
                'nominee_post_office'       => $nomineePostOffice,
                'nominee_post_office_bn'    => $nomineePostOfficeBn,
                'nominee_district'          => $nomineeDistrict,
                'nominee_district_bn'       => $nomineeDistrictBn,
                'nominee_nid'               => $nomineeNid,
                'nominee_mobile'            => $nomineeMobile,
                'nominee_image'             => $nomineeImage,
                'nationality'               => $nationality,
                'salary'                    => [
                    'gross'       => $gross,
                    'basic'       => $basic,
                    'house'       => $house,
                    'medical'     => $medical,
                    'transport'   => $transport,
                    'food'        => $food,
                    'ot_rate'     => $otRate,
                    'deduct_from' => $deductFrom,
                ],
                'others' => $employeeOthers,
                  // Global helpers for reports and pages
                'getEarningsDeductionsSummary' => $getEarningsDeductionsSummary,
                'getIncrements'                => $getIncrements,
                'getLeaves'                    => $getLeaves,
                'getWeekendToRegularSummary'   => $getWeekendToRegularSummary,
                'getSalaryReport'              => $getSalaryReport,
                'gender'                       => $gender,
                'religion'                     => $religion,
                'nid'                          => $nid,
                'girls'                         => $girls,
                'boys'                          => $boys,
                'marital_status'               => $maritalStatus,
                'blood_group'                 => $bloodGroup,
                'mobile_number'               => $mobileNumber,
                'emergency_mobile'            => $emergencyMobile,
                  // Optionally add more fields as needed
            ];
        };
    }
}
