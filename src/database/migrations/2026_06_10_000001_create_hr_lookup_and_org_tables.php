<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_geo_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('type', 50);
            $table->integer('type_no', 5)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::table('hr_geo_locations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
        });

        Schema::create('hr_religions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('bn_name', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_marital_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('bn_name', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_sexes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->string('bn_name', 50)->nullable();
            $table->string('code', 10)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('bn_name', 100)->nullable();
            $table->string('code', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_working_places', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->string('code', 30)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_leave_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->integer('days');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_salary_keys', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('medical', 10, 2)->nullable();
            $table->decimal('lunch', 10, 2)->nullable();
            $table->decimal('transport', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_factories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('bn_name', 200)->nullable();
            $table->text('address')->nullable();
            $table->text('bn_address')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('authority_sign', 255)->nullable();
            $table->decimal('allow_ot_hour', 5, 2)->nullable();
            $table->decimal('stamp_amount', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('bn_name', 100)->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->time('start_allow_time')->nullable();
            $table->time('late_allow_time')->nullable();
            $table->time('out_time_start')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('head_of_department')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->unsignedInteger('department_id');
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::table('hr_sections', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('hr_departments')->cascadeOnDelete();
        });

        Schema::create('hr_sub_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->unsignedInteger('department_id');
            $table->unsignedInteger('section_id');
            $table->string('salary_type', 50)->nullable();
            $table->integer('approve_man_power')->nullable();
            $table->unsignedInteger('roster_shift_id')->nullable();
            $table->tinyInteger('is_individual_roster')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::table('hr_sub_sections', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('hr_departments')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->cascadeOnDelete();
            $table->foreign('roster_shift_id')->references('id')->on('hr_shifts')->nullOnDelete();
        });

        Schema::create('hr_floor_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('floor_name', 150);
            $table->string('bn_floor_name', 150)->nullable();
            $table->string('line_name', 150);
            $table->string('bn_line_name', 150)->nullable();
            $table->integer('line_capacity')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_designations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->string('grade', 20)->nullable();
            $table->integer('approved_manpower')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->decimal('attendance_bonus', 10, 2)->nullable();
            $table->decimal('attendance_bonus_com', 10, 2)->nullable();
            $table->decimal('tiffin_allowance', 10, 2)->nullable();
            $table->decimal('min_tiffin_hour', 5, 2)->nullable();
            $table->decimal('night_allowance', 10, 2)->nullable();
            $table->decimal('min_night_hour', 5, 2)->nullable();
            $table->decimal('dinner_allowance', 10, 2)->nullable();
            $table->decimal('min_dinner_hour', 5, 2)->nullable();
            $table->string('payment_way', 50)->nullable();
            $table->integer('weekend_allowance_count')->nullable();
            $table->decimal('holiday_allowance', 10, 2)->nullable();
            $table->decimal('gross_salary', 12, 2)->nullable();
            $table->decimal('car_fuel_allowance', 10, 2)->nullable();
            $table->decimal('phone_internet_allowance', 10, 2)->nullable();
            $table->text('extra_facility')->nullable();
            $table->decimal('ot_one_rate', 6, 4)->nullable();
            $table->decimal('ot_two_rate', 6, 4)->nullable();
            $table->unsignedInteger('report_to')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('follow_up_team')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::table('hr_designations', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
            $table->foreign('report_to')->references('id')->on('hr_designations')->nullOnDelete();
        });

        Schema::create('hr_bonus_titles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->string('bn_title', 150)->nullable();
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('hr_bonus_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bonus_title_id');
            $table->string('policy_name', 200);
            $table->string('bn_policy_name', 200)->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('sub_section_id')->nullable();
            $table->integer('month_range_from')->nullable();
            $table->integer('month_range_to')->nullable();
            $table->string('apply_on', 50)->nullable();
            $table->string('type', 20);
            $table->decimal('amount', 12, 2);
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::table('hr_bonus_policies', function (Blueprint $table) {
            $table->foreign('bonus_title_id')->references('id')->on('hr_bonus_titles')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
            $table->foreign('sub_section_id')->references('id')->on('hr_sub_sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_bonus_policies');
        Schema::dropIfExists('hr_bonus_titles');
        Schema::dropIfExists('hr_designations');
        Schema::dropIfExists('hr_floor_lines');
        Schema::dropIfExists('hr_sub_sections');
        Schema::dropIfExists('hr_sections');
        Schema::dropIfExists('hr_departments');
        Schema::dropIfExists('hr_shifts');
        Schema::dropIfExists('hr_factories');
        Schema::dropIfExists('hr_salary_keys');
        Schema::dropIfExists('hr_leave_infos');
        Schema::dropIfExists('hr_classifications');
        Schema::dropIfExists('hr_working_places');
        Schema::dropIfExists('hr_payment_methods');
        Schema::dropIfExists('hr_sexes');
        Schema::dropIfExists('hr_marital_statuses');
        Schema::dropIfExists('hr_religions');
        Schema::dropIfExists('hr_geo_locations');
    }
};
