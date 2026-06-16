<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('employee_id');
            $table->date('date');
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            $table->integer('total_working_minute')->nullable();
            $table->integer('total_ot_minute')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_long', 10, 7)->nullable();
            $table->string('status', 20)->nullable();
            $table->string('via', 20)->nullable();
            $table->string('verify_type', 20)->nullable();
            $table->string('device_sn')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['employee_id', 'date'], 'uq_hr_emp_date');
            $table->index('date', 'idx_hr_att_date');
            $table->index('employee_id', 'idx_hr_att_emp');
        });

        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });

        Schema::create('hr_locks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('module', 50);
            $table->smallInteger('lock_year')->nullable();
            $table->tinyInteger('lock_month')->nullable();
            $table->date('lock_date')->nullable();
            $table->unsignedInteger('factory_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedInteger('locked_by')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->unsignedInteger('unlocked_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['module', 'lock_year', 'lock_month', 'factory_id', 'department_id'], 'uq_hr_lock_period');
            $table->index('module', 'idx_hr_lock_module');
            $table->index('is_locked', 'idx_hr_lock_status');
        });

        Schema::table('hr_locks', function (Blueprint $table) {
            $table->foreign('factory_id')->references('id')->on('hr_factories')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
        });

        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('purpose');
            $table->string('bn_purpose', 200)->nullable();
            $table->string('type', 30);
            $table->date('from_date');
            $table->date('to_date');
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index(['from_date', 'to_date'], 'idx_hr_holiday_range');
        });

        Schema::create('hr_regular_to_weekends', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('section_id');
            $table->date('date');
            $table->string('type', 20);
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['section_id', 'date'], 'uq_hr_section_weekend_date');
        });

        Schema::table('hr_regular_to_weekends', function (Blueprint $table) {
            $table->foreign('section_id')->references('id')->on('hr_sections')->cascadeOnDelete();
        });

        Schema::create('hr_shift_rosters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('roster_type', 20);
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('sub_section_id')->nullable();
            $table->unsignedInteger('shift_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index(['sub_section_id', 'from_date', 'to_date'], 'idx_hr_roster_period');
        });

        Schema::table('hr_shift_rosters', function (Blueprint $table) {
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
            $table->foreign('sub_section_id')->references('id')->on('hr_sub_sections')->nullOnDelete();
            $table->foreign('shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
        });

        Schema::create('hr_shift_roster_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('shift_id');
            $table->date('roster_date');
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique(['employee_id', 'roster_date'], 'uq_hr_emp_roster_date');
        });

        Schema::table('hr_shift_roster_employees', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_shift_roster_employees');
        Schema::dropIfExists('hr_shift_rosters');
        Schema::dropIfExists('hr_regular_to_weekends');
        Schema::dropIfExists('hr_holidays');
        Schema::dropIfExists('hr_locks');
        Schema::dropIfExists('hr_attendances');
    }
};
