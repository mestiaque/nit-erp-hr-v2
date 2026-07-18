<?php

namespace ME\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGeoLocations();
        $this->seedLookups();
        $this->seedOrganization();
        $this->seedOperational();
        $this->seedEmployees();
        $this->seedHolidays();
    }

    // ─────────────────────────────────────────────
    // GEO LOCATIONS
    // ─────────────────────────────────────────────
    private function seedGeoLocations(): void
    {
        $now = now();

        $this->upsertGeo(['name' => 'Bangladesh', 'bn_name' => 'বাংলাদেশ', 'type' => 'country', 'parent_id' => null, 'status' => 'active', 'created_at' => $now]);

        $bdId = DB::table('hr_geo_locations')->where('name', 'Bangladesh')->where('type', 'country')->value('id');

        foreach ([
            ['name' => 'Dhaka',      'bn_name' => 'ঢাকা',      'type' => 'division', 'parent_id' => $bdId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Chittagong', 'bn_name' => 'চট্টগ্রাম', 'type' => 'division', 'parent_id' => $bdId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Rajshahi',   'bn_name' => 'রাজশাহী',   'type' => 'division', 'parent_id' => $bdId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Sylhet',     'bn_name' => 'সিলেট',     'type' => 'division', 'parent_id' => $bdId, 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            $this->upsertGeo($row);
        }

        $dhakaId = DB::table('hr_geo_locations')->where('name', 'Dhaka')->where('type', 'division')->value('id');
        $ctgId   = DB::table('hr_geo_locations')->where('name', 'Chittagong')->where('type', 'division')->value('id');

        foreach ([
            ['name' => 'Dhaka',        'bn_name' => 'ঢাকা',        'type' => 'district', 'parent_id' => $dhakaId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Gazipur',      'bn_name' => 'গাজীপুর',     'type' => 'district', 'parent_id' => $dhakaId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Narayanganj',  'bn_name' => 'নারায়ণগঞ্জ', 'type' => 'district', 'parent_id' => $dhakaId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Manikganj',    'bn_name' => 'মানিকগঞ্জ',  'type' => 'district', 'parent_id' => $dhakaId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Chittagong',   'bn_name' => 'চট্টগ্রাম',  'type' => 'district', 'parent_id' => $ctgId,   'status' => 'active', 'created_at' => $now],
            ['name' => "Cox's Bazar",  'bn_name' => 'কক্সবাজার',  'type' => 'district', 'parent_id' => $ctgId,   'status' => 'active', 'created_at' => $now],
        ] as $row) {
            $this->upsertGeo($row);
        }

        $dhakaDistId = DB::table('hr_geo_locations')->where('name', 'Dhaka')->where('type', 'district')->value('id');
        $gazipurId   = DB::table('hr_geo_locations')->where('name', 'Gazipur')->where('type', 'district')->value('id');

        foreach ([
            ['name' => 'Mirpur',      'bn_name' => 'মিরপুর',      'type' => 'thana', 'parent_id' => $dhakaDistId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Uttara',      'bn_name' => 'উত্তরা',      'type' => 'thana', 'parent_id' => $dhakaDistId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Mohammadpur', 'bn_name' => 'মোহাম্মদপুর', 'type' => 'thana', 'parent_id' => $dhakaDistId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Savar',       'bn_name' => 'সাভার',       'type' => 'thana', 'parent_id' => $dhakaDistId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Tongi',       'bn_name' => 'টঙ্গী',       'type' => 'thana', 'parent_id' => $gazipurId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Kaliakair',   'bn_name' => 'কালিয়াকৈর',  'type' => 'thana', 'parent_id' => $gazipurId,   'status' => 'active', 'created_at' => $now],
        ] as $row) {
            $this->upsertGeo($row);
        }
    }

    private function upsertGeo(array $row): void
    {
        DB::table('hr_geo_locations')->updateOrInsert(
            ['name' => $row['name'], 'type' => $row['type'], 'parent_id' => $row['parent_id']],
            $row
        );
    }

    // ─────────────────────────────────────────────
    // LOOKUPS
    // ─────────────────────────────────────────────
    private function seedLookups(): void
    {
        $now = now();

        foreach ([
            ['name' => 'Islam',       'bn_name' => 'ইসলাম',    'code' => 'ISL', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Hinduism',    'bn_name' => 'হিন্দু',   'code' => 'HIN', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Christianity','bn_name' => 'খ্রিস্টান','code' => 'CHR', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Buddhism',    'bn_name' => 'বৌদ্ধ',   'code' => 'BUD', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_religions')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Single',   'bn_name' => 'অবিবাহিত',       'code' => 'SNG', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Married',  'bn_name' => 'বিবাহিত',         'code' => 'MRD', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Divorced', 'bn_name' => 'তালাকপ্রাপ্ত',   'code' => 'DIV', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Widowed',  'bn_name' => 'বিধবা/বিপত্নীক', 'code' => 'WID', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_marital_statuses')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Male',   'bn_name' => 'পুরুষ', 'code' => 'M', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Female', 'bn_name' => 'মহিলা', 'code' => 'F', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_sexes')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Cash',          'bn_name' => 'নগদ',               'code' => 'CASH', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Bank Transfer', 'bn_name' => 'ব্যাংক ট্রান্সফার', 'code' => 'BANK', 'status' => 'active', 'created_at' => $now],
            ['name' => 'bKash',         'bn_name' => 'বিকাশ',             'code' => 'BKSH', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Nagad',         'bn_name' => 'নগদ (MFS)',         'code' => 'NGAD', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_payment_methods')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Head Office', 'bn_name' => 'প্রধান কার্যালয়', 'code' => 'HO', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Factory A',   'bn_name' => 'কারখানা এ',        'code' => 'FA', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Factory B',   'bn_name' => 'কারখানা বি',       'code' => 'FB', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Warehouse',   'bn_name' => 'গুদাম',            'code' => 'WH', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_working_places')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Worker',    'bn_name' => 'শ্রমিক',    'description' => 'Production floor worker',  'status' => 'active', 'created_at' => $now],
            ['name' => 'Staff',     'bn_name' => 'স্টাফ',     'description' => 'Office and support staff', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Officer',   'bn_name' => 'কর্মকর্তা', 'description' => 'Mid-level officers',       'status' => 'active', 'created_at' => $now],
            ['name' => 'Executive', 'bn_name' => 'নির্বাহী',  'description' => 'Senior management',        'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_classifications')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Casual Leave',    'bn_name' => 'নৈমিত্তিক ছুটি',      'code' => 'CL',  'days' => 10,  'status' => 'active', 'created_at' => $now],
            ['name' => 'Sick Leave',      'bn_name' => 'অসুস্থতা ছুটি',        'code' => 'SL',  'days' => 14,  'status' => 'active', 'created_at' => $now],
            ['name' => 'Earned Leave',    'bn_name' => 'অর্জিত ছুটি',          'code' => 'EL',  'days' => 18,  'status' => 'active', 'created_at' => $now],
            ['name' => 'Festival Leave',  'bn_name' => 'উৎসব ছুটি',            'code' => 'FL',  'days' => 2,   'status' => 'active', 'created_at' => $now],
            ['name' => 'General Leave',   'bn_name' => 'সাধারণ ছুটি',          'code' => 'GL',  'days' => 0,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Maternity Leave', 'bn_name' => 'মাতৃত্বকালীন ছুটি',   'code' => 'ML',  'days' => 112, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Without Pay',     'bn_name' => 'বেতন বিহীন ছুটি',      'code' => 'WOP', 'days' => 0,   'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_leave_infos')->updateOrInsert(['name' => $row['name']], $row);
        }

        if (!DB::table('hr_salary_keys')->exists()) {
            DB::table('hr_salary_keys')->insert(['medical' => 600.00, 'lunch' => 900.00, 'transport' => 500.00, 'status' => 'active', 'created_at' => $now]);
        }

        foreach ([
            ['title' => 'Eid-ul-Fitr Bonus',  'bn_title' => 'ঈদুল ফিতর বোনাস',  'code' => 'EID1', 'description' => 'Festival bonus for Eid-ul-Fitr',    'status' => 'active', 'created_at' => $now],
            ['title' => 'Eid-ul-Adha Bonus',  'bn_title' => 'ঈদুল আযহা বোনাস',  'code' => 'EID2', 'description' => 'Festival bonus for Eid-ul-Adha',    'status' => 'active', 'created_at' => $now],
            ['title' => 'Annual Bonus',        'bn_title' => 'বার্ষিক বোনাস',     'code' => 'ANBL', 'description' => 'Annual performance bonus',           'status' => 'active', 'created_at' => $now],
            ['title' => 'Attendance Bonus',    'bn_title' => 'হাজিরা বোনাস',      'code' => 'ATBL', 'description' => 'Monthly attendance bonus',           'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_bonus_titles')->updateOrInsert(['code' => $row['code']], $row);
        }
    }

    // ─────────────────────────────────────────────
    // ORGANIZATION STRUCTURE
    // ─────────────────────────────────────────────
    private function seedOrganization(): void
    {
        $now = now();

        DB::table('hr_factories')->updateOrInsert(['name' => 'Apex Garments Ltd.'], [
            'name'           => 'Apex Garments Ltd.',
            'bn_name'        => 'এপেক্স গার্মেন্টস লিমিটেড',
            'address'        => 'Dhaka Export Processing Zone, Savar, Dhaka',
            'bn_address'     => 'ঢাকা রপ্তানি প্রক্রিয়াকরণ অঞ্চল, সাভার, ঢাকা',
            'contact_number' => '01700000001',
            'allow_ot_hour'  => 2.00,
            'stamp_amount'   => 10.00,
            'weekend'        => 'friday',
            'ot_rate'        => 1.5,
            'status'         => 'active',
            'created_at'     => $now,
        ]);

        foreach ([
            ['name' => 'Day Shift',   'bn_name' => 'দিন শিফট',  'start_time' => '08:00:00', 'end_time' => '17:00:00', 'start_allow_time' => '07:45:00', 'late_allow_time' => '08:10:00', 'out_time_start' => '16:45:00', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Night Shift', 'bn_name' => 'রাত শিফট', 'start_time' => '20:00:00', 'end_time' => '05:00:00', 'start_allow_time' => '19:45:00', 'late_allow_time' => '20:10:00', 'out_time_start' => '04:45:00', 'status' => 'active', 'created_at' => $now],
            ['name' => 'General',     'bn_name' => 'সাধারণ',    'start_time' => '09:00:00', 'end_time' => '18:00:00', 'start_allow_time' => '08:45:00', 'late_allow_time' => '09:15:00', 'out_time_start' => '17:45:00', 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_shifts')->updateOrInsert(['name' => $row['name']], $row);
        }

        foreach ([
            ['name' => 'Production',      'bn_name' => 'উৎপাদন',             'description' => 'Garment production floor', 'status' => 'active', 'created_at' => $now],
            ['name' => 'Quality Control', 'bn_name' => 'গুণমান নিয়ন্ত্রণ', 'description' => 'QC and compliance',       'status' => 'active', 'created_at' => $now],
            ['name' => 'Human Resources', 'bn_name' => 'মানবসম্পদ',          'description' => 'HR and administration',   'status' => 'active', 'created_at' => $now],
            ['name' => 'Finance',         'bn_name' => 'অর্থ বিভাগ',         'description' => 'Finance and payroll',     'status' => 'active', 'created_at' => $now],
            ['name' => 'Cutting',         'bn_name' => 'কাটিং',              'description' => 'Fabric cutting section',  'status' => 'active', 'created_at' => $now],
            ['name' => 'Finishing',       'bn_name' => 'ফিনিশিং',            'description' => 'Garment finishing',       'status' => 'active', 'created_at' => $now],
            ['name' => 'Washing',         'bn_name' => 'ওয়াশিং',            'description' => 'Garment washing',         'status' => 'active', 'created_at' => $now],
            ['name' => 'Store',           'bn_name' => 'স্টোর',              'description' => 'Raw material store',      'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_departments')->updateOrInsert(['name' => $row['name']], $row);
        }

        $prodId   = DB::table('hr_departments')->where('name', 'Production')->value('id');
        $qcId     = DB::table('hr_departments')->where('name', 'Quality Control')->value('id');
        $hrId     = DB::table('hr_departments')->where('name', 'Human Resources')->value('id');
        $finId    = DB::table('hr_departments')->where('name', 'Finance')->value('id');
        $cutId    = DB::table('hr_departments')->where('name', 'Cutting')->value('id');
        $finishId = DB::table('hr_departments')->where('name', 'Finishing')->value('id');

        foreach ([
            ['name' => 'Sewing A',       'bn_name' => 'সেলাই এ',       'department_id' => $prodId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Sewing B',       'bn_name' => 'সেলাই বি',      'department_id' => $prodId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Embroidery',     'bn_name' => 'এমব্রয়ডারি',   'department_id' => $prodId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'In-Line QC',     'bn_name' => 'ইন-লাইন কিউসি', 'department_id' => $qcId,    'status' => 'active', 'created_at' => $now],
            ['name' => 'Final QC',       'bn_name' => 'ফাইনাল কিউসি',  'department_id' => $qcId,    'status' => 'active', 'created_at' => $now],
            ['name' => 'Recruitment',    'bn_name' => 'নিয়োগ',         'department_id' => $hrId,    'status' => 'active', 'created_at' => $now],
            ['name' => 'Payroll',        'bn_name' => 'বেতন ভাতা',     'department_id' => $finId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Fabric Cutting', 'bn_name' => 'কাপড় কাটা',    'department_id' => $cutId,   'status' => 'active', 'created_at' => $now],
            ['name' => 'Packing',        'bn_name' => 'প্যাকিং',       'department_id' => $finishId,'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_sections')->updateOrInsert(['name' => $row['name'], 'department_id' => $row['department_id']], $row);
        }

        $sewingAId = DB::table('hr_sections')->where('name', 'Sewing A')->value('id');
        $sewingBId = DB::table('hr_sections')->where('name', 'Sewing B')->value('id');
        $packingId = DB::table('hr_sections')->where('name', 'Packing')->value('id');
        $dayShiftId = DB::table('hr_shifts')->where('name', 'Day Shift')->value('id');

        foreach ([
            ['name' => 'Line 1',        'bn_name' => 'লাইন ১',           'department_id' => $prodId,   'section_id' => $sewingAId, 'salary_type' => 'price_rate', 'approve_man_power' => 40, 'roster_shift_id' => $dayShiftId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Line 2',        'bn_name' => 'লাইন ২',           'department_id' => $prodId,   'section_id' => $sewingAId, 'salary_type' => 'price_rate', 'approve_man_power' => 40, 'roster_shift_id' => $dayShiftId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Line 3',        'bn_name' => 'লাইন ৩',           'department_id' => $prodId,   'section_id' => $sewingBId, 'salary_type' => 'price_rate', 'approve_man_power' => 35, 'roster_shift_id' => $dayShiftId, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Packing Unit 1','bn_name' => 'প্যাকিং ইউনিট ১', 'department_id' => $finishId, 'section_id' => $packingId, 'salary_type' => 'fixed_rate', 'approve_man_power' => 20, 'roster_shift_id' => $dayShiftId, 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_sub_sections')->updateOrInsert(['name' => $row['name'], 'section_id' => $row['section_id']], $row);
        }

        foreach ([
            ['floor_name' => 'Floor 1', 'bn_floor_name' => 'তলা ১', 'line_name' => 'Line 1', 'bn_line_name' => 'লাইন ১', 'line_capacity' => 40, 'status' => 'active', 'created_at' => $now],
            ['floor_name' => 'Floor 1', 'bn_floor_name' => 'তলা ১', 'line_name' => 'Line 2', 'bn_line_name' => 'লাইন ২', 'line_capacity' => 40, 'status' => 'active', 'created_at' => $now],
            ['floor_name' => 'Floor 2', 'bn_floor_name' => 'তলা ২', 'line_name' => 'Line 3', 'bn_line_name' => 'লাইন ৩', 'line_capacity' => 35, 'status' => 'active', 'created_at' => $now],
            ['floor_name' => 'Floor 2', 'bn_floor_name' => 'তলা ২', 'line_name' => 'Line 4', 'bn_line_name' => 'লাইন ৪', 'line_capacity' => 35, 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_floor_lines')->updateOrInsert(['floor_name' => $row['floor_name'], 'line_name' => $row['line_name']], $row);
        }

        $designations = [
            ['name' => 'General Worker',     'bn_name' => 'সাধারণ শ্রমিক',         'grade' => 'G7', 'department_id' => $prodId,  'approved_manpower' => 200, 'gross_salary' => 9000.00,  'tiffin_allowance' => 150.00, 'min_tiffin_hour' => 6.00,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => 500.00, 'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => null,   'payment_way' => 'monthly', 'ot_one_rate' => 1.5,  'ot_two_rate' => 2.0,  'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Skilled Worker',     'bn_name' => 'দক্ষ শ্রমিক',           'grade' => 'G6', 'department_id' => $prodId,  'approved_manpower' => 100, 'gross_salary' => 11000.00, 'tiffin_allowance' => 200.00, 'min_tiffin_hour' => 6.00,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => 600.00, 'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => null,   'payment_way' => 'monthly', 'ot_one_rate' => 1.5,  'ot_two_rate' => 2.0,  'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Line Supervisor',    'bn_name' => 'লাইন সুপারভাইজার',      'grade' => 'G5', 'department_id' => $prodId,  'approved_manpower' => 20,  'gross_salary' => 15000.00, 'tiffin_allowance' => 300.00, 'min_tiffin_hour' => 5.00,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => 800.00, 'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => null,   'payment_way' => 'monthly', 'ot_one_rate' => 1.5,  'ot_two_rate' => 2.0,  'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'QC Inspector',       'bn_name' => 'কিউসি পরিদর্শক',        'grade' => 'G5', 'department_id' => $qcId,   'approved_manpower' => 15,  'gross_salary' => 14000.00, 'tiffin_allowance' => 300.00, 'min_tiffin_hour' => 5.00,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => 700.00, 'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => null,   'payment_way' => 'monthly', 'ot_one_rate' => 1.5,  'ot_two_rate' => 2.0,  'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'HR Officer',         'bn_name' => 'মানবসম্পদ কর্মকর্তা',  'grade' => 'G4', 'department_id' => $hrId,   'approved_manpower' => 5,   'gross_salary' => 22000.00, 'tiffin_allowance' => null,   'min_tiffin_hour' => null,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => null,   'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => 500.00,  'payment_way' => 'monthly', 'ot_one_rate' => null, 'ot_two_rate' => null, 'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Accounts Officer',   'bn_name' => 'হিসাব কর্মকর্তা',       'grade' => 'G4', 'department_id' => $finId,  'approved_manpower' => 4,   'gross_salary' => 24000.00, 'tiffin_allowance' => null,   'min_tiffin_hour' => null,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => null,   'attendance_bonus_com' => null, 'car_fuel_allowance' => null,    'phone_internet_allowance' => 500.00,  'payment_way' => 'monthly', 'ot_one_rate' => null, 'ot_two_rate' => null, 'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Production Manager', 'bn_name' => 'উৎপাদন ব্যবস্থাপক',    'grade' => 'G2', 'department_id' => $prodId,  'approved_manpower' => 2,   'gross_salary' => 55000.00, 'tiffin_allowance' => null,   'min_tiffin_hour' => null,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => null,   'attendance_bonus_com' => null, 'car_fuel_allowance' => 3000.00, 'phone_internet_allowance' => 1000.00, 'payment_way' => 'monthly', 'ot_one_rate' => null, 'ot_two_rate' => null, 'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
            ['name' => 'General Manager',    'bn_name' => 'মহাব্যবস্থাপক',         'grade' => 'G1', 'department_id' => null,     'approved_manpower' => 1,   'gross_salary' => 90000.00, 'tiffin_allowance' => null,   'min_tiffin_hour' => null,  'night_allowance' => null, 'min_night_hour' => null, 'dinner_allowance' => null, 'min_dinner_hour' => null, 'attendance_bonus' => null,   'attendance_bonus_com' => null, 'car_fuel_allowance' => 8000.00, 'phone_internet_allowance' => 2000.00, 'payment_way' => 'monthly', 'ot_one_rate' => null, 'ot_two_rate' => null, 'weekend_allowance_count' => null, 'holiday_allowance' => null, 'status' => 'active', 'created_at' => $now],
        ];
        foreach ($designations as $row) {
            DB::table('hr_designations')->updateOrInsert(['name' => $row['name']], $row);
        }

        $eid1Id   = DB::table('hr_bonus_titles')->where('code', 'EID1')->value('id');
        $eid2Id   = DB::table('hr_bonus_titles')->where('code', 'EID2')->value('id');
        $annualId = DB::table('hr_bonus_titles')->where('code', 'ANBL')->value('id');

        foreach ([
            ['bonus_title_id' => $eid1Id,   'policy_name' => 'Eid Bonus - All Workers',          'bn_policy_name' => 'ঈদ বোনাস - সকল শ্রমিক',              'department_id' => $prodId, 'month_range_from' => 1,  'month_range_to' => 12, 'apply_on' => 'basic', 'type' => 'percent', 'amount' => 100.00, 'status' => 'active', 'created_at' => $now],
            ['bonus_title_id' => $eid2Id,   'policy_name' => 'Eid-ul-Adha Bonus - Officers',     'bn_policy_name' => 'ঈদুল আযহা বোনাস - কর্মকর্তা',        'department_id' => null,    'month_range_from' => 1,  'month_range_to' => 12, 'apply_on' => 'gross', 'type' => 'percent', 'amount' => 50.00,  'status' => 'active', 'created_at' => $now],
            ['bonus_title_id' => $annualId, 'policy_name' => 'Annual Performance Bonus - Fixed', 'bn_policy_name' => 'বার্ষিক পারফরম্যান্স বোনাস - নির্দিষ্ট', 'department_id' => null,    'month_range_from' => 6,  'month_range_to' => 12, 'apply_on' => 'gross', 'type' => 'fixed',   'amount' => 5000.00,'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_bonus_policies')->updateOrInsert(['policy_name' => $row['policy_name']], $row);
        }

        foreach ([
            ['name' => 'Saturday',  'day_number' => 6, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Sunday',    'day_number' => 0, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Monday',    'day_number' => 1, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Tuesday',   'day_number' => 2, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Wednesday', 'day_number' => 3, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Thursday',  'day_number' => 4, 'status' => 'active', 'created_at' => $now],
            ['name' => 'Friday',    'day_number' => 5, 'status' => 'active', 'created_at' => $now],
        ] as $row) {
            DB::table('hr_weekdays')->updateOrInsert(['day_number' => $row['day_number']], $row);
        }
    }

    // ─────────────────────────────────────────────
    // OPERATIONAL
    // ─────────────────────────────────────────────
    private function seedOperational(): void
    {
        $now = now();
        if (!DB::table('hr_salary_keys')->exists()) {
            DB::table('hr_salary_keys')->insert(['medical' => 600.00, 'lunch' => 900.00, 'transport' => 500.00, 'status' => 'active', 'created_at' => $now]);
        }
    }

    // ─────────────────────────────────────────────
    // EMPLOYEES
    // ─────────────────────────────────────────────
    private function seedEmployees(): void
    {
        $now = now();

        $workerDesigId  = DB::table('hr_designations')->where('name', 'General Worker')->value('id');
        $skilledDesigId = DB::table('hr_designations')->where('name', 'Skilled Worker')->value('id');
        $supDesigId     = DB::table('hr_designations')->where('name', 'Line Supervisor')->value('id');
        $hrDesigId      = DB::table('hr_designations')->where('name', 'HR Officer')->value('id');
        $accDesigId     = DB::table('hr_designations')->where('name', 'Accounts Officer')->value('id');
        $mgrDesigId     = DB::table('hr_designations')->where('name', 'Production Manager')->value('id');
        $gmDesigId      = DB::table('hr_designations')->where('name', 'General Manager')->value('id');

        $prodId   = DB::table('hr_departments')->where('name', 'Production')->value('id');
        $qcId     = DB::table('hr_departments')->where('name', 'Quality Control')->value('id');
        $hrDeptId = DB::table('hr_departments')->where('name', 'Human Resources')->value('id');
        $finId    = DB::table('hr_departments')->where('name', 'Finance')->value('id');

        $sewAId = DB::table('hr_sections')->where('name', 'Sewing A')->value('id');
        $sewBId = DB::table('hr_sections')->where('name', 'Sewing B')->value('id');
        $recId  = DB::table('hr_sections')->where('name', 'Recruitment')->value('id');
        $payId  = DB::table('hr_sections')->where('name', 'Payroll')->value('id');

        $dayShiftId = DB::table('hr_shifts')->where('name', 'Day Shift')->value('id');
        $genShiftId = DB::table('hr_shifts')->where('name', 'General')->value('id');

        $line1Id = DB::table('hr_floor_lines')->where('line_name', 'Line 1')->value('id');
        $line2Id = DB::table('hr_floor_lines')->where('line_name', 'Line 2')->value('id');

        $wpFactoryId = DB::table('hr_working_places')->where('code', 'FA')->value('id');
        $wpHOId      = DB::table('hr_working_places')->where('code', 'HO')->value('id');

        $workerClassId  = DB::table('hr_classifications')->where('name', 'Worker')->value('id');
        $staffClassId   = DB::table('hr_classifications')->where('name', 'Staff')->value('id');
        $officerClassId = DB::table('hr_classifications')->where('name', 'Officer')->value('id');
        $execClassId    = DB::table('hr_classifications')->where('name', 'Executive')->value('id');

        $islamId   = DB::table('hr_religions')->where('code', 'ISL')->value('id');
        $hinduId   = DB::table('hr_religions')->where('code', 'HIN')->value('id');
        $maleId    = DB::table('hr_sexes')->where('code', 'M')->value('id');
        $femaleId  = DB::table('hr_sexes')->where('code', 'F')->value('id');
        $marriedId = DB::table('hr_marital_statuses')->where('code', 'MRD')->value('id');
        $singleId  = DB::table('hr_marital_statuses')->where('code', 'SNG')->value('id');
        $cashId    = DB::table('hr_payment_methods')->where('code', 'CASH')->value('id');
        $bankId    = DB::table('hr_payment_methods')->where('code', 'BANK')->value('id');
        $bkashId   = DB::table('hr_payment_methods')->where('code', 'BKSH')->value('id');

        $dhakaDistId = DB::table('hr_geo_locations')->where('name', 'Dhaka')->where('type', 'district')->value('id');

        $employees = [
            ['employee' => ['name' => 'Fatema Akter',     'bn_name' => 'ফাতেমা আক্তার',      'employee_id' => 'EMP-0001', 'join_date' => '2021-03-15', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewAId, 'designation_id' => $workerDesigId,  'shift_id' => $dayShiftId, 'floor_line_id' => $line1Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1995-06-10', 'blood_group' => 'B+',  'payment_method_id' => $bkashId, 'national_id_no' => '1234567890001', 'father_name' => 'Abdul Karim',       'bn_father_name' => 'আব্দুল করিম',      'mother_name' => 'Rahela Begum',   'bn_mother_name' => 'রাহেলা বেগম'],   'salary' => ['gross_salary' => 9000.00,  'gross_salary_comp1' => 9000.00]],
            ['employee' => ['name' => 'Rina Begum',        'bn_name' => 'রিনা বেগম',          'employee_id' => 'EMP-0002', 'join_date' => '2020-07-01', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewAId, 'designation_id' => $workerDesigId,  'shift_id' => $dayShiftId, 'floor_line_id' => $line1Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $singleId,  'religion_id' => $islamId, 'birth_date' => '1998-11-20', 'blood_group' => 'O+',  'payment_method_id' => $bkashId, 'national_id_no' => '1234567890002', 'father_name' => 'Md. Hanif',         'bn_father_name' => 'মোঃ হানিফ',        'mother_name' => 'Sufia Begum',    'bn_mother_name' => 'সুফিয়া বেগম'],  'salary' => ['gross_salary' => 9000.00,  'gross_salary_comp1' => 9000.00]],
            ['employee' => ['name' => 'Sumaiya Khatun',    'bn_name' => 'সুমাইয়া খাতুন',    'employee_id' => 'EMP-0003', 'join_date' => '2022-01-10', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewAId, 'designation_id' => $skilledDesigId, 'shift_id' => $dayShiftId, 'floor_line_id' => $line1Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1993-03-05', 'blood_group' => 'A+',  'payment_method_id' => $bkashId, 'national_id_no' => '1234567890003', 'father_name' => 'Md. Anwar',         'bn_father_name' => 'মোঃ আনোয়ার',      'mother_name' => 'Razia Begum',    'bn_mother_name' => 'রাজিয়া বেগম'],  'salary' => ['gross_salary' => 11000.00, 'gross_salary_comp1' => 11000.00]],
            ['employee' => ['name' => 'Morium Akter',      'bn_name' => 'মরিয়ম আক্তার',      'employee_id' => 'EMP-0004', 'join_date' => '2021-09-15', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewBId, 'designation_id' => $workerDesigId,  'shift_id' => $dayShiftId, 'floor_line_id' => $line2Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $singleId,  'religion_id' => $hinduId, 'birth_date' => '1996-08-22', 'blood_group' => 'AB+', 'payment_method_id' => $cashId,  'national_id_no' => '1234567890004', 'father_name' => 'Ratan Chandra Das', 'bn_father_name' => 'রতন চন্দ্র দাস',  'mother_name' => 'Gita Rani Das',  'bn_mother_name' => 'গীতা রাণী দাস'], 'salary' => ['gross_salary' => 9000.00,  'gross_salary_comp1' => 9000.00]],
            ['employee' => ['name' => 'Amena Khatun',      'bn_name' => 'আমেনা খাতুন',        'employee_id' => 'EMP-0005', 'join_date' => '2019-06-01', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewBId, 'designation_id' => $skilledDesigId, 'shift_id' => $dayShiftId, 'floor_line_id' => $line2Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1990-12-30', 'blood_group' => 'O-',  'payment_method_id' => $bkashId, 'national_id_no' => '1234567890005', 'father_name' => 'Md. Sabbir',        'bn_father_name' => 'মোঃ সাব্বির',      'mother_name' => 'Jahanara Begum', 'bn_mother_name' => 'জাহানারা বেগম'], 'salary' => ['gross_salary' => 11000.00, 'gross_salary_comp1' => 11000.00]],
            ['employee' => ['name' => 'Md. Rakibul Islam', 'bn_name' => 'মোঃ রাকিবুল ইসলাম', 'employee_id' => 'EMP-0006', 'join_date' => '2020-03-20', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewAId, 'designation_id' => $workerDesigId,  'shift_id' => $dayShiftId, 'floor_line_id' => $line1Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $singleId,  'religion_id' => $islamId, 'birth_date' => '2000-01-15', 'blood_group' => 'B+',  'payment_method_id' => $bkashId, 'national_id_no' => '1234567890006', 'father_name' => 'Md. Jalal Uddin',   'bn_father_name' => 'মোঃ জালাল উদ্দিন', 'mother_name' => 'Hasina Begum',   'bn_mother_name' => 'হাসিনা বেগম'],  'salary' => ['gross_salary' => 9000.00,  'gross_salary_comp1' => 9000.00]],
            ['employee' => ['name' => 'Md. Kamrul Hasan',  'bn_name' => 'মোঃ কামরুল হাসান',  'employee_id' => 'EMP-0007', 'join_date' => '2018-11-01', 'classification_id' => $workerClassId,  'department_id' => $prodId,   'section_id' => $sewBId, 'designation_id' => $skilledDesigId, 'shift_id' => $dayShiftId, 'floor_line_id' => $line2Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1988-05-18', 'blood_group' => 'A+',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890007', 'father_name' => 'Md. Alauddin',      'bn_father_name' => 'মোঃ আলাউদ্দিন',   'mother_name' => 'Rohima Begum',   'bn_mother_name' => 'রহিমা বেগম'],   'salary' => ['gross_salary' => 11000.00, 'gross_salary_comp1' => 11000.00]],
            ['employee' => ['name' => 'Md. Shahin Alam',   'bn_name' => 'মোঃ শাহিন আলম',     'employee_id' => 'EMP-0008', 'join_date' => '2017-02-14', 'classification_id' => $staffClassId,   'department_id' => $prodId,   'section_id' => $sewAId, 'designation_id' => $supDesigId,     'shift_id' => $dayShiftId, 'floor_line_id' => $line1Id, 'working_place_id' => $wpFactoryId, 'weekend' => 'friday',          'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1985-09-10', 'blood_group' => 'O+',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890008', 'father_name' => 'Md. Selim',         'bn_father_name' => 'মোঃ সেলিম',        'mother_name' => 'Bilkis Begum',   'bn_mother_name' => 'বিলকিস বেগম'],  'salary' => ['gross_salary' => 15000.00, 'gross_salary_comp1' => 15000.00]],
            ['employee' => ['name' => 'Nusrat Jahan',      'bn_name' => 'নুসরাত জাহান',       'employee_id' => 'EMP-0009', 'join_date' => '2019-04-01', 'classification_id' => $officerClassId, 'department_id' => $hrDeptId, 'section_id' => $recId,  'designation_id' => $hrDesigId,      'shift_id' => $genShiftId, 'floor_line_id' => null,     'working_place_id' => $wpHOId,      'weekend' => 'friday,saturday', 'status' => 1], 'basic' => ['sex_id' => $femaleId, 'marital_status_id' => $singleId,  'religion_id' => $islamId, 'birth_date' => '1994-07-25', 'blood_group' => 'A-',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890009', 'father_name' => 'Md. Zahirul Islam', 'bn_father_name' => 'মোঃ জহিরুল ইসলাম', 'mother_name' => 'Nazma Begum',    'bn_mother_name' => 'নাজমা বেগম'],   'salary' => ['gross_salary' => 22000.00, 'gross_salary_comp1' => 22000.00]],
            ['employee' => ['name' => 'Md. Rafiqul Islam', 'bn_name' => 'মোঃ রফিকুল ইসলাম',  'employee_id' => 'EMP-0010', 'join_date' => '2018-08-01', 'classification_id' => $officerClassId, 'department_id' => $finId,    'section_id' => $payId,  'designation_id' => $accDesigId,     'shift_id' => $genShiftId, 'floor_line_id' => null,     'working_place_id' => $wpHOId,      'weekend' => 'friday,saturday', 'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1987-03-12', 'blood_group' => 'B+',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890010', 'father_name' => 'Md. Abul Kashem',   'bn_father_name' => 'মোঃ আবুল কাশেম',  'mother_name' => 'Nasima Begum',   'bn_mother_name' => 'নাসিমা বেগম'],  'salary' => ['gross_salary' => 24000.00, 'gross_salary_comp1' => 24000.00]],
            ['employee' => ['name' => 'Md. Kamal Hossain', 'bn_name' => 'মোঃ কামাল হোসেন',   'employee_id' => 'EMP-0011', 'join_date' => '2015-01-01', 'classification_id' => $execClassId,   'department_id' => $prodId,   'section_id' => null,    'designation_id' => $mgrDesigId,     'shift_id' => $genShiftId, 'floor_line_id' => null,     'working_place_id' => $wpFactoryId, 'weekend' => 'friday,saturday', 'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1980-06-22', 'blood_group' => 'O+',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890011', 'father_name' => 'Md. Abul Hossain', 'bn_father_name' => 'মোঃ আবুল হোসেন',  'mother_name' => 'Anjuara Begum',  'bn_mother_name' => 'আঞ্জুয়ারা বেগম'],'salary' => ['gross_salary' => 55000.00, 'gross_salary_comp1' => 55000.00]],
            ['employee' => ['name' => 'Mr. Rezaul Karim',  'bn_name' => 'জনাব রেজাউল করিম',  'employee_id' => 'EMP-0012', 'join_date' => '2012-06-01', 'classification_id' => $execClassId,   'department_id' => null,      'section_id' => null,    'designation_id' => $gmDesigId,      'shift_id' => $genShiftId, 'floor_line_id' => null,     'working_place_id' => $wpHOId,      'weekend' => 'friday,saturday', 'status' => 1], 'basic' => ['sex_id' => $maleId,   'marital_status_id' => $marriedId, 'religion_id' => $islamId, 'birth_date' => '1975-02-14', 'blood_group' => 'A+',  'payment_method_id' => $bankId,  'national_id_no' => '1234567890012', 'father_name' => 'Md. Abdur Rahman',  'bn_father_name' => 'মোঃ আব্দুর রহমান', 'mother_name' => 'Sufia Khatun',   'bn_mother_name' => 'সুফিয়া খাতুন'], 'salary' => ['gross_salary' => 90000.00, 'gross_salary_comp1' => 90000.00]],
        ];

        foreach ($employees as $data) {
            $empRow = $data['employee'];

            // Get existing employee or create new one
            $existing = DB::table('hr_employees')->where('employee_id', $empRow['employee_id'])->first();
            if ($existing) {
                $empId = $existing->id;
            } else {
                $empId = DB::table('hr_employees')->insertGetId(array_merge($empRow, ['created_at' => $now]));
            }

            // Always ensure sub-records exist
            if (!DB::table('hr_employee_basic_infos')->where('employee_id', $empId)->exists()) {
                DB::table('hr_employee_basic_infos')->insert(array_merge(
                    ['employee_id' => $empId, 'status' => 1, 'created_at' => $now],
                    $data['basic']
                ));
            }

            if (!DB::table('hr_employee_salary_infos')->where('employee_id', $empId)->exists()) {
                DB::table('hr_employee_salary_infos')->insert(array_merge(
                    ['employee_id' => $empId, 'payment_method_id' => $data['basic']['payment_method_id'] ?? null, 'status' => 1, 'effective_date' => $empRow['join_date'], 'created_at' => $now],
                    $data['salary']
                ));
            }

            DB::table('hr_employee_addresses')->updateOrInsert(
                ['employee_id' => $empId, 'type' => 'permanent'],
                ['employee_id' => $empId, 'type' => 'permanent', 'district_id' => $dhakaDistId, 'village' => 'Sample Village', 'bn_village' => 'নমুনা গ্রাম', 'status' => 1, 'created_at' => $now]
            );
        }
    }

    // ─────────────────────────────────────────────
    // HOLIDAYS & ATTENDANCE
    // ─────────────────────────────────────────────
    private function seedHolidays(): void
    {
        $now = now();

        foreach ([
            ['purpose' => 'International New Year\'s Day',     'bn_purpose' => 'আন্তর্জাতিক নববর্ষ',          'type' => 'general',  'from_date' => '2026-01-01', 'to_date' => '2026-01-01'],
            ['purpose' => 'International Mother Language Day', 'bn_purpose' => 'আন্তর্জাতিক মাতৃভাষা দিবস',  'type' => 'general',  'from_date' => '2026-02-21', 'to_date' => '2026-02-21'],
            ['purpose' => 'Independence Day',                  'bn_purpose' => 'স্বাধীনতা দিবস',              'type' => 'general',  'from_date' => '2026-03-26', 'to_date' => '2026-03-26'],
            ['purpose' => 'Eid-ul-Fitr',                       'bn_purpose' => 'ঈদুল ফিতর',                   'type' => 'festival', 'from_date' => '2026-03-31', 'to_date' => '2026-04-02'],
            ['purpose' => 'Bengali New Year (Pahela Boishakh)','bn_purpose' => 'পহেলা বৈশাখ',                  'type' => 'general',  'from_date' => '2026-04-14', 'to_date' => '2026-04-14'],
            ['purpose' => 'May Day',                           'bn_purpose' => 'মে দিবস',                     'type' => 'general',  'from_date' => '2026-05-01', 'to_date' => '2026-05-01'],
            ['purpose' => 'Buddha Purnima',                    'bn_purpose' => 'বুদ্ধ পূর্ণিমা',             'type' => 'festival', 'from_date' => '2026-05-11', 'to_date' => '2026-05-11'],
            ['purpose' => 'Eid-ul-Adha',                       'bn_purpose' => 'ঈদুল আযহা',                   'type' => 'festival', 'from_date' => '2026-06-07', 'to_date' => '2026-06-09'],
            ['purpose' => 'National Mourning Day',             'bn_purpose' => 'জাতীয় শোক দিবস',            'type' => 'general',  'from_date' => '2026-08-15', 'to_date' => '2026-08-15'],
            ['purpose' => 'Victory Day',                       'bn_purpose' => 'বিজয় দিবস',                  'type' => 'general',  'from_date' => '2026-12-16', 'to_date' => '2026-12-16'],
            ['purpose' => 'Christmas Day',                     'bn_purpose' => 'বড়দিন',                       'type' => 'festival', 'from_date' => '2026-12-25', 'to_date' => '2026-12-25'],
            ['purpose' => 'Factory Annual Maintenance',        'bn_purpose' => 'কারখানা বার্ষিক রক্ষণাবেক্ষণ','type' => 'general',  'from_date' => '2026-07-01', 'to_date' => '2026-07-03'],
        ] as $row) {
            DB::table('hr_holidays')->updateOrInsert(
                ['purpose' => $row['purpose'], 'from_date' => $row['from_date']],
                array_merge($row, ['status' => 1, 'created_at' => $now])
            );
        }

        $attendanceDays = [
            '2026-06-01' => ['in' => '07:58:00', 'out' => '17:05:00', 'status' => 'present'],
            '2026-06-02' => ['in' => '08:05:00', 'out' => '17:10:00', 'status' => 'present'],
            '2026-06-03' => ['in' => '08:02:00', 'out' => '19:30:00', 'status' => 'present'],
            '2026-06-04' => ['in' => '08:15:00', 'out' => '17:00:00', 'status' => 'late'],
            '2026-06-05' => ['in' => null,        'out' => null,        'status' => 'absent'],
            '2026-06-08' => ['in' => '07:55:00', 'out' => '17:02:00', 'status' => 'present'],
            '2026-06-09' => ['in' => '08:00:00', 'out' => '17:00:00', 'status' => 'present'],
            '2026-06-10' => ['in' => '08:10:00', 'out' => '19:45:00', 'status' => 'present'],
            '2026-06-11' => ['in' => '08:03:00', 'out' => '17:08:00', 'status' => 'present'],
            '2026-06-12' => ['in' => null,        'out' => null,        'status' => 'leave'],
        ];

        $empIds = DB::table('hr_employees')
            ->whereIn('employee_id', ['EMP-0001', 'EMP-0002', 'EMP-0008'])
            ->pluck('id');

        foreach ($empIds as $empId) {
            foreach ($attendanceDays as $date => $att) {
                $workMin = $otMin = null;
                if ($att['in'] && $att['out']) {
                    $in  = strtotime($att['in']);
                    $out = strtotime($att['out']);
                    if ($out < $in) $out += 86400;
                    $workMin = (int) round(($out - $in) / 60);
                    $otMin   = max(0, $workMin - 540);
                }
                DB::table('hr_attendances')->updateOrInsert(
                    ['employee_id' => $empId, 'date' => $date],
                    ['employee_id' => $empId, 'date' => $date, 'in_time' => $att['in'], 'out_time' => $att['out'], 'total_working_minute' => $workMin, 'total_ot_minute' => $otMin, 'status' => $att['status'], 'via' => 'manual', 'created_at' => $now]
                );
            }
        }
    }
}
