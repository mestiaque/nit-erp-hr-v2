<?php

use ME\Hr\Models\HrGeoLocation as Country;
use ME\Hr\Models\HrBonusPolicy as BonusPolicy;
use ME\Hr\Models\HrDepartment as Department;
use ME\Hr\Models\HrFloorLine as FloorLine;
use ME\Hr\Models\HrBonusTitle as BonusTitle;
use ME\Hr\Models\HrClassification as Classification;
use ME\Hr\Models\HrAssetCategory;
use ME\Hr\Models\HrSection as Section;
use ME\Hr\Models\HrDesignation as Designation;
use ME\Hr\Models\HrFactory as Factory;
use ME\Hr\Models\HrLeaveInfo as LeaveInfo;
use ME\Hr\Models\HrMaritalStatus;
use ME\Hr\Models\HrReligion;
use ME\Hr\Models\HrSex;
use ME\Hr\Models\HrPaymentMethod as PaymentMethod;
use ME\Hr\Models\HrProductionBonus as ProductionBonus;
use ME\Hr\Models\HrRequisition as Requisition;
use ME\Hr\Models\HrSalaryKey as SalaryKey;
use ME\Hr\Models\HrShift as Shift;
use ME\Hr\Models\HrSubSection as SubSection;
use ME\Hr\Models\HrWeekday as Weekday;
use ME\Hr\Models\HrWorkingPlace as WorkingPlace;
return [
    'route' => [
        'prefix' => 'admin/hr-center',
        'as' => 'hr-center.',
        'middleware' => ['web', 'auth', 'redirectUser'],
    ],

    /*
     | Token for ZKTeco / attendance machine API endpoints.
     | Set HR_MACHINE_API_TOKEN in your .env file.
     | Leave null to disable the machine API endpoints.
     */
    'machine_api_token' => env('HR_MACHINE_API_TOKEN'),
    'legacy_links' => [
        [
            'title' => 'Employee Type',
            'description' => 'HR Center classification setup',
            'url' => '/admin/hr-center/masters/classifications',
        ],
        [
            'title' => 'Block / Line',
            'description' => 'HR Center block/line setup',
            'url' => '/admin/hr-center/masters/floor-lines',
        ],
        [
            'title' => 'Department',
            'description' => 'HR Center department setup',
            'url' => '/admin/hr-center/masters/departments',
        ],
        [
            'title' => 'Division',
            'description' => 'Existing admin division setup',
            'url' => '/admin/hr/divisions',
        ],
        [
            'title' => 'Grade',
            'description' => 'Existing admin grade setup',
            'url' => '/admin/hr/grades',
        ],
        [
            'title' => 'Section',
            'description' => 'Existing admin section setup',
            'url' => '/admin/hr/sections',
        ],
    ],
    'entities' => [
        'departments' => [
            'title' => 'Department',
            'model' => Department::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'head_of_department' => ['label' => 'Head of Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'user', 'label' => 'name']],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'floor-lines' => [
            'title' => 'Block / Line',
            'model' => FloorLine::class,
            'search' => ['floor_name', 'bn_floor_name', 'line_name', 'bn_line_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['floor_name', 'bn_floor_name', 'line_name', 'bn_line_name', 'line_capacity', 'status'],
            'fields' => [
                'floor_name' => ['label' => 'Floor Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_floor_name' => ['label' => 'Floor Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'line_name' => ['label' => 'Line Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_line_name' => ['label' => 'Line Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'line_capacity' => ['label' => 'Line Capacity', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'classifications' => [
            'title' => 'Classification',
            'model' => Classification::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'description', 'probation_period', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string|max:1000', 'tinymce' => false],
                'probation_period' => ['label' => 'Probation Period', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'step' => 1],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'asset-categories' => [
            'title' => 'Asset Category',
            'model' => HrAssetCategory::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'countries' => [
            'title' => 'Country',
            'model' => Country::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['type' => 'country'],
            'index_fields' => ['name', 'bn_name'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:250'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ],
        ],
        'divisions' => [
            'title' => 'Division',
            'model' => Country::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['type' => 'division'],
            'index_fields' => ['name', 'bn_name'],
            'fields' => [
                'parent_id' => ['label' => 'Country', 'type' => 'select', 'rules' => 'required|integer', 'source' => ['driver' => 'model', 'model' => Country::class, 'conditions' => ['type' => 'country'], 'label' => 'name']],
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:250'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ],
        ],
        'districts' => [
            'title' => 'District',
            'model' => Country::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['type' => 'district'],
            'with' => ['parent'],
            'index_fields' => ['name', 'bn_name', 'parent_id'],
            'index_column_labels' => ['parent_id' => 'Division'],
            'fields' => [
                'parent_id' => ['label' => 'Division', 'type' => 'select', 'rules' => 'required|integer', 'source' => ['driver' => 'model', 'model' => Country::class, 'conditions' => ['type' => 'division'], 'label' => 'name']],
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:250'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ],
        ],
        'police-stations' => [
            'title' => 'Thana',
            'model' => Country::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['type' => 'police_station'],
            'with' => ['parent.parent'],
            'index_fields' => ['name', 'bn_name', 'parent_id', 'grandparent_name'],
            'index_column_labels' => ['parent_id' => 'District', 'grandparent_name' => 'Division'],
            'fields' => [
                'parent_id' => ['label' => 'District', 'type' => 'select', 'rules' => 'required|integer', 'source' => ['driver' => 'model', 'model' => Country::class, 'conditions' => ['type' => 'district'], 'label' => 'name']],
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:250'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ],
        ],
        'marital-statuses' => [
            'title' => 'Marital Status',
            'model' => HrMaritalStatus::class,
            'search' => ['name', 'bn_name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'code', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'religions' => [
            'title' => 'Religion',
            'model' => HrReligion::class,
            'search' => ['name', 'bn_name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'code', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'sexes' => [
            'title' => 'Sex',
            'model' => HrSex::class,
            'search' => ['name', 'bn_name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'code', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        // Note: hr_sexes.status uses tinyInteger — normalized to string 'active'/'inactive' by migration 2026_06_14_000003
        'bonus-policies' => [
            'title' => 'Bonus Policy',
            'model' => BonusPolicy::class,
            'search' => ['policy_name', 'bn_policy_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['section_id', 'designation_id', 'month_from', 'month_to', 'salary_basis', 'amount_type', 'amount', 'status'],
            'fields' => [
                'bonus_title_id' => ['label' => 'Bonus Title', 'type' => 'select', 'rules' => 'required|integer', 'source' => ['driver' => 'model', 'model' => BonusTitle::class, 'label' => 'title']],
                'policy_name' => ['label' => 'Policy Name', 'type' => 'text', 'rules' => 'required|string|max:200'],
                'bn_policy_name' => ['label' => 'Policy Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'department']],
                'section_id' => ['label' => 'Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'sections']],
                'sub_section_id' => ['label' => 'Sub Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'model', 'model' => SubSection::class, 'label' => 'name']],
                'designation_id' => ['label' => 'Designation', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'model', 'model' => Designation::class, 'label' => 'name']],
                'month_range_from' => ['label' => 'Month Range From', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'min' => '0'],
                'month_range_to' => ['label' => 'Month Range To', 'type' => 'number', 'rules' => 'nullable|integer|min:0', 'min' => '0'],
                'apply_on' => ['label' => 'Basic / Gross / Production', 'type' => 'select', 'rules' => 'required|in:basic,gross,production', 'options' => ['basic' => 'Basic', 'gross' => 'Gross', 'production' => 'Production']],
                'type' => ['label' => 'Percent / Fixed', 'type' => 'select', 'rules' => 'required|in:percent,fixed', 'options' => ['percent' => 'Percent', 'fixed' => 'Fixed']],
                'amount' => ['label' => 'Amount', 'type' => 'number', 'rules' => 'required|numeric|min:0', 'step' => '0.01'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'bonus-titles' => [
            'title' => 'Bonus Title',
            'model' => BonusTitle::class,
            'search' => ['title', 'bn_title', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['title', 'bn_title', 'code', 'status'],
            'fields' => [
                'title' => ['label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_title' => ['label' => 'Bangla Title', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'designations' => [
            'title' => 'Designation',
            'model' => Designation::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'with' => ['department'],
            'index_fields' => ['name', 'bn_name', 'department_name', 'grade', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'grade' => ['label' => 'Grade', 'type' => 'text', 'rules' => 'nullable|string|max:20'],
                'grade_bn' => ['label' => 'Grade (Bangla)', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'approved_manpower' => ['label' => 'Approved Manpower', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'department']],
                'section_id' => ['label' => 'Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'sections']],
                'attendance_bonus' => ['label' => 'Attendance Bonus', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'attendance_bonus_com' => ['label' => 'Attendance Bonus Com.', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'tiffin_allowance' => ['label' => 'Tiffin Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'min_tiffin_hour' => ['label' => 'Minimum Tiffin Hour', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'night_allowance' => ['label' => 'Night Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'min_night_hour' => ['label' => 'Minimum Night Hour', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'dinner_allowance' => ['label' => 'Dinner Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'min_dinner_hour' => ['label' => 'Minimum Dinner Hour', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'payment_way' => ['label' => 'Tiffin, Night & Dinner Payment Way', 'type' => 'select', 'rules' => 'nullable|in:daily,monthly', 'options' => ['daily' => 'Daily', 'monthly' => 'Monthly']],
                'weekend_allowance_count' => ['label' => 'Weekend Allowance Count', 'type' => 'select', 'rules' => 'nullable|in:gross_month_day,basic_working_day,basic_104_2_5,fixed_amount,ot_by_worked_hour', 'options' => ['gross_month_day' => 'gross/monthDay', 'basic_working_day' => 'basic/working day', 'basic_104_2_5' => 'basic/104*2.5', 'fixed_amount' => 'Fixed Amount (Holiday Allowance)', 'ot_by_worked_hour' => 'OT By Worked Hour']],
                'holiday_allowance' => ['label' => 'Holiday Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'gross_salary' => ['label' => 'Gross Salary', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'car_fuel_allowance' => ['label' => 'Car & Fuel Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'phone_internet_allowance' => ['label' => 'Phone & Internet Allowance', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'extra_facility' => ['label' => 'Extra Facility', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'ot_one_rate' => ['label' => 'OT Rate (Type 1)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.0001'],
                'ot_two_rate' => ['label' => 'OT Rate (Type 2)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.0001'],
                'is_ot_basis_wphp' => ['label' => 'Is OT Basis (WPHP)', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'is_ot_basis_main' => ['label' => 'Is OT Basis (Main)', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'is_ot_basis_others_1' => ['label' => 'Is OT Basis (Others-1)', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'is_ot_basis_others_2' => ['label' => 'Is OT Basis (Others-2)', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'responsibilities' => ['label' => 'Responsibilities', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => true],
                'report_to' => ['label' => 'Report To (Designation ID)', 'type' => 'number', 'rules' => 'nullable|integer'],
                'follow_up_team' => ['label' => 'Follow Up Team', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => true],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'factories' => [
            'title' => 'Factory',
            'model' => Factory::class,
            'search' => ['name', 'bn_name', 'address', 'bn_address'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['factory_no', 'name', 'bn_name', 'weekend', 'status'],
            'fields' => [
                'factory_no' => ['label' => 'No. of Factory', 'type' => 'select', 'rules' => 'nullable|in:0,1,2', 'options' => ['0' => 'Actual', '1' => 'Comp 1', '2' => 'Comp 2']],
                'is_running' => ['label' => 'Is A~Z Running', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:200'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:200'],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'bn_address' => ['label' => 'Bangla Address', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'contact_number' => ['label' => 'Contact Number', 'type' => 'text', 'rules' => 'nullable|string|max:30'],
                'email' => ['label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:191'],
                'website' => ['label' => 'Website', 'type' => 'url', 'rules' => 'nullable|url|max:191'],
                'weekend' => ['label' => 'Weekend', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'roster_day' => ['label' => 'Roster Day', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'allow_ot_hour' => ['label' => 'Allow OT Hour', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'ot_rate' => ['label' => 'OT Rate', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.0001'],
                'stamp_amount' => ['label' => 'Stamp Amount', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'attendance_bonus_late_days_more_than' => ['label' => 'Attendance Bonus Cut After Late Days', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'absent_deduct_from' => ['label' => 'Absent Deduct From', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'absent_deduct_special' => ['label' => 'Absent Deduct Special', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'production_subsidy' => ['label' => 'Pro. Subsidy', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'attendance_id_type' => ['label' => 'Attendance ID Type', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'attendance_type' => ['label' => 'Attendance Type', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'last_earn_leave_count_date' => ['label' => 'Last Earn Leave Count Date', 'type' => 'date', 'rules' => 'nullable|date'],
                'authority_sign' => ['label' => 'Authority Sign', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                'apply_special_office_time_in_main' => ['label' => 'Apply Special Office Time In Main', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'leave-infos' => [
            'title' => 'Leave Info',
            'model' => LeaveInfo::class,
            'search' => ['name', 'bn_name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'code', 'days', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'required|string|max:50'],
                'days' => ['label' => 'Days', 'type' => 'number', 'rules' => 'required|integer|min:0'],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'production-bonuses' => [
            'title' => 'Production Bonus',
            'model' => ProductionBonus::class,
            'search' => ['name'],
            'defaults' => ['status' => 'active'],
            'with' => ['section'],
            'index_fields' => ['name', 'section_name', 'percentage', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'section_id' => ['label' => 'Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'sections']],
                'sub_section_id' => ['label' => 'Sub Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'model', 'model' => SubSection::class, 'label' => 'name']],
                'percentage' => ['label' => 'Percentage', 'type' => 'number', 'rules' => 'required|numeric|min:0', 'step' => '0.01'],
                'effective_from' => ['label' => 'Effective From', 'type' => 'date', 'rules' => 'nullable|date'],
                'effective_to' => ['label' => 'Effective To', 'type' => 'date', 'rules' => 'nullable|date'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'salary-keys' => [
            'title' => 'Salary Key',
            'model' => SalaryKey::class,
            'search' => ['salary_approved_person_1'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['medical', 'lunch', 'transport', 'payment_date', 'status'],
            'index_column_labels' => ['lunch' => 'Food'],
            'fields' => [
                'medical' => ['label' => 'Medical', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'lunch' => ['label' => 'Food', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'transport' => ['label' => 'Transport', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'step' => '0.01'],
                'salary_approved_person_1' => ['label' => 'Salary Approved Person-1', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'salary_approved_person_2' => ['label' => 'Salary Approved Person-2', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'salary_approved_person_3' => ['label' => 'Salary Approved Person-3', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'salary_approved_person_4' => ['label' => 'Salary Approved Person-4', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'salary_approved_person_5' => ['label' => 'Salary Approved Person-5', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'payment_date' => ['label' => 'Payment Date', 'type' => 'date', 'rules' => 'nullable|date'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'payment-methods' => [
            'title' => 'Payment Method',
            'model' => PaymentMethod::class,
            'search' => ['name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'code', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:50'],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'shifts' => [
            'title' => 'Shift',
            'model' => Shift::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'start_time', 'end_time', 'late_allow_time', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:100'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
                'start_time' => ['label' => 'Start Time', 'type' => 'time', 'rules' => 'required'],
                'end_time' => ['label' => 'End Time', 'type' => 'time', 'rules' => 'required'],
                'start_allow_time' => ['label' => 'Start Allow Time (Card Accept From)', 'type' => 'time', 'rules' => 'nullable'],
                'late_allow_time' => ['label' => 'Late Allow Time (Red Marking On)', 'type' => 'time', 'rules' => 'nullable'],
                'out_time_start' => ['label' => 'Out Time Start (Card Accept To)', 'type' => 'time', 'rules' => 'nullable'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'sections' => [
            'title' => 'Section',
            'model' => Section::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'with' => ['department'],
            'index_fields' => ['name', 'bn_name', 'department_name', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'department']],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'sub-sections' => [
            'title' => 'Sub Section',
            'model' => SubSection::class,
            'search' => ['name', 'bn_name'],
            'defaults' => ['status' => 'active'],
            'with' => ['department', 'section'],
            'index_fields' => ['name', 'bn_name', 'department_name', 'section_name', 'salary_type_label', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'department']],
                'section_id' => ['label' => 'Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'sections']],
                'salary_type' => ['label' => 'Salary Type', 'type' => 'select', 'rules' => 'required|in:price_rate,fixed_rate', 'options' => ['price_rate' => 'Price Rate', 'fixed_rate' => 'Fixed Rate']],
                'approve_man_power' => ['label' => 'Approve Man Power', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
                'roster_shift_id' => ['label' => 'Roster Shift', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'model', 'model' => Shift::class, 'label' => 'name']],
                'is_individual_roster' => ['label' => 'Is Individual Roster', 'type' => 'checkbox', 'rules' => 'nullable|boolean'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'weeks' => [
            'title' => 'Week',
            'model' => Weekday::class,
            'search' => ['name'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'day_number', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:50'],
                'day_number' => ['label' => 'Day Number', 'type' => 'number', 'rules' => 'nullable|integer|min:0|max:6'],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'working-places' => [
            'title' => 'Working Place',
            'model' => WorkingPlace::class,
            'search' => ['name', 'bn_name', 'code'],
            'defaults' => ['status' => 'active'],
            'index_fields' => ['name', 'bn_name', 'code', 'status'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:150'],
                'bn_name' => ['label' => 'Bangla Name', 'type' => 'text', 'rules' => 'nullable|string|max:150'],
                'code' => ['label' => 'Code', 'type' => 'text', 'rules' => 'nullable|string|max:30'],
                'address' => ['label' => 'Address', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'description' => ['label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:active,inactive', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
            ],
        ],
        'requisitions' => [
            'title' => 'Requisition',
            'model' => Requisition::class,
            'search' => ['requisition_no', 'title'],
            'defaults' => ['status' => 'draft'],
            'with' => ['department'],
            'index_fields' => ['requisition_no', 'title', 'department_name', 'quantity', 'status'],
            'fields' => [
                'requisition_no' => ['label' => 'Requisition No', 'type' => 'text', 'rules' => 'required|string|max:50'],
                'title' => ['label' => 'Title', 'type' => 'text', 'rules' => 'required|string|max:191'],
                'department_id' => ['label' => 'Department', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'department']],
                'section_id' => ['label' => 'Section', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'attribute', 'filter' => 'sections']],
                'designation_id' => ['label' => 'Designation', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'model', 'model' => Designation::class, 'label' => 'name']],
                'quantity' => ['label' => 'Quantity', 'type' => 'number', 'rules' => 'required|integer|min:1'],
                'requisition_date' => ['label' => 'Requisition Date', 'type' => 'date', 'rules' => 'nullable|date'],
                'requested_by' => ['label' => 'Requested By', 'type' => 'select', 'rules' => 'nullable|integer', 'source' => ['driver' => 'user', 'label' => 'name']],
                'notes' => ['label' => 'Notes', 'type' => 'textarea', 'rules' => 'nullable|string', 'tinymce' => false],
                'status' => ['label' => 'Status', 'type' => 'select', 'rules' => 'required|in:draft,pending,approved,rejected', 'options' => ['draft' => 'Draft', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']],
            ],
        ],
    ],
    // ─────────────────────────────────────────────────────────────────────────
    // ATTENDANCE / LEAVE / HOLIDAY PRIORITY SYSTEM
    //
    // Controls how the system resolves a single calendar day when multiple
    // statuses overlap (e.g. an employee has both an approved leave and a
    // public holiday on the same date).
    //
    // priority_order   : Ordered list (highest → lowest). The first matching
    //                    status wins for that day's primary status label.
    //                    Allowed values: 'leave', 'attendance', 'holiday', 'absent'
    //
    // leave_vs_holiday : What happens when a leave and a holiday coincide:
    //   'leave_wins'   → Holiday is ignored; only the leave is counted.
    //                    The employee does NOT earn an extra holiday allowance.
    //   'both_count'   → Both are recorded; the employee earns the leave AND
    //                    the holiday allowance for that day.
    //
    // factory_overrides: Per-factory (by factory ID) rule override. If a
    //                    factory ID is listed here, its value replaces the
    //                    global leave_vs_holiday setting for that factory.
    //                    Example: 2 => 'both_count'
    // ─────────────────────────────────────────────────────────────────────────
    'attendance_priority' => [
        'priority_order'   => ['leave', 'attendance', 'holiday', 'absent'],
        'leave_vs_holiday' => 'leave_wins',
        'factory_overrides' => [
            // 1 => 'leave_wins',
            // 2 => 'both_count',
        ],
    ],

    'reports' => [
        'employee'                => 'Employee',
        'monthly'                 => 'Monthly',
        'machine-id'              => 'Machine Id',
        'job-card'                => 'Job Card',
        'personal-file'           => 'Personal File',
        'attendance'              => 'Attendance',
        'tiffin-night-dinner'     => 'Tiffin / Night / Dinner',
        'pro-job-card'            => 'Production Job Card A',
        'bonus-salary-fixed'      => 'Bonus Salary Fixed',
        'bonus-salary-production' => 'Bonus Salary Production',
        'salary-fixed'            => 'Salary Fixed',
        'salary-production'       => 'Salary Production',
        'salary-summary'          => 'Wages And Salary Summary',
        'job-card-report'         => 'Job Card Report',
        'attendance-report'       => 'Attendance Report',
        'attendance-with-ot'      => 'Attendance Report With OT',
        'monthly-late-report'     => 'Monthly Late Report',
        'daily-manpower-report'   => 'Daily Manpower Report',
        'meal-report'             => 'Tiffin / Diner / Night Report',
        'bonus-sheet'             => 'Bonus Sheet',
        'salary-report'           => 'Salary Report',
        'pay-slip'                => 'Pay Slip',
    ],
];
