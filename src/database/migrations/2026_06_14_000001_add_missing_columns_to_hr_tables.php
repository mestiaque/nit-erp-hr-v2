<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hr_employees: add employment lifecycle columns
        // All employee detail data (basic info, salary, address, nominee, etc.)
        // is stored in their own dedicated relational tables — no JSON blob.
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->string('employment_status', 50)->nullable()->after('weekend');
            $table->date('exited_at')->nullable()->after('employment_status');
        });
        // Make join_date nullable — use raw SQL to avoid doctrine/dbal dependency
        DB::statement("ALTER TABLE `hr_employees` MODIFY `join_date` DATE NULL");

        // hr_employee_salary_increments: add percentage and comp salary columns
        Schema::table('hr_employee_salary_increments', function (Blueprint $table) {
            $table->decimal('increment_percentage', 8, 4)->nullable()->after('increment_amount');
            $table->decimal('previous_salary_comp_1', 12, 2)->nullable()->after('increment_percentage');
            $table->decimal('new_salary_comp_1', 12, 2)->nullable()->after('previous_salary_comp_1');
            $table->decimal('previous_salary_comp_2', 12, 2)->nullable()->after('new_salary_comp_1');
            $table->decimal('new_salary_comp_2', 12, 2)->nullable()->after('previous_salary_comp_2');
            $table->string('remarks', 500)->nullable()->after('new_salary_comp_2');
            $table->unsignedBigInteger('approved_by')->nullable()->after('remarks');
        });

        // hr_leave_infos: add code column for leave type identification
        Schema::table('hr_leave_infos', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('name');
        });

        // hr_salary_keys: add approved persons and payment date
        Schema::table('hr_salary_keys', function (Blueprint $table) {
            $table->string('salary_approved_person_1', 191)->nullable()->after('transport');
            $table->string('salary_approved_person_2', 191)->nullable()->after('salary_approved_person_1');
            $table->string('salary_approved_person_3', 191)->nullable()->after('salary_approved_person_2');
            $table->string('salary_approved_person_4', 191)->nullable()->after('salary_approved_person_3');
            $table->string('salary_approved_person_5', 191)->nullable()->after('salary_approved_person_4');
            $table->date('payment_date')->nullable()->after('salary_approved_person_5');
        });

        // hr_working_places: add address and description
        Schema::table('hr_working_places', function (Blueprint $table) {
            $table->text('address')->nullable()->after('code');
            $table->text('description')->nullable()->after('address');
        });

        // hr_factories: add extended operational fields
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->string('factory_no', 50)->nullable()->after('id');
            $table->boolean('is_running')->default(false)->after('factory_no');
            $table->string('email', 191)->nullable()->after('contact_number');
            $table->string('website', 191)->nullable()->after('email');
            $table->string('weekend', 50)->nullable()->after('website');
            $table->string('roster_day', 50)->nullable()->after('weekend');
            $table->decimal('ot_rate', 8, 4)->nullable()->after('allow_ot_hour');
            $table->integer('attendance_bonus_late_days_more_than')->nullable()->after('ot_rate');
            $table->string('absent_deduct_from', 191)->nullable()->after('attendance_bonus_late_days_more_than');
            $table->string('absent_deduct_special', 191)->nullable()->after('absent_deduct_from');
            $table->decimal('production_subsidy', 10, 2)->nullable()->after('absent_deduct_special');
            $table->string('attendance_id_type', 191)->nullable()->after('production_subsidy');
            $table->string('attendance_type', 191)->nullable()->after('attendance_id_type');
            $table->date('last_earn_leave_count_date')->nullable()->after('attendance_type');
            $table->boolean('apply_special_office_time_in_main')->default(false)->after('last_earn_leave_count_date');
        });

        // hr_designations: add OT basis boolean flags and ot rates (already in migration but adding is_ot_basis_*)
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->boolean('is_ot_basis_wphp')->default(false)->after('ot_two_rate');
            $table->boolean('is_ot_basis_main')->default(false)->after('is_ot_basis_wphp');
            $table->boolean('is_ot_basis_others_1')->default(false)->after('is_ot_basis_main');
            $table->boolean('is_ot_basis_others_2')->default(false)->after('is_ot_basis_others_1');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'exited_at']);
            $table->date('join_date')->nullable(false)->change();
        });

        Schema::table('hr_employee_salary_increments', function (Blueprint $table) {
            $table->dropColumn(['increment_percentage', 'previous_salary_comp_1', 'new_salary_comp_1', 'previous_salary_comp_2', 'new_salary_comp_2', 'remarks', 'approved_by']);
        });

        Schema::table('hr_leave_infos', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('hr_salary_keys', function (Blueprint $table) {
            $table->dropColumn(['salary_approved_person_1', 'salary_approved_person_2', 'salary_approved_person_3', 'salary_approved_person_4', 'salary_approved_person_5', 'payment_date']);
        });

        Schema::table('hr_working_places', function (Blueprint $table) {
            $table->dropColumn(['address', 'description']);
        });

        Schema::table('hr_factories', function (Blueprint $table) {
            $table->dropColumn(['factory_no', 'is_running', 'email', 'website', 'weekend', 'roster_day', 'ot_rate', 'attendance_bonus_late_days_more_than', 'absent_deduct_from', 'absent_deduct_special', 'production_subsidy', 'attendance_id_type', 'attendance_type', 'last_earn_leave_count_date', 'apply_special_office_time_in_main']);
        });

        Schema::table('hr_designations', function (Blueprint $table) {
            $table->dropColumn(['is_ot_basis_wphp', 'is_ot_basis_main', 'is_ot_basis_others_1', 'is_ot_basis_others_2']);
        });
    }
};
