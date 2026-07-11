<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_salary_increments', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('status');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
        });

        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('remarks');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
        });

        Schema::create('hr_employee_salary_snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->smallInteger('lock_year');
            $table->tinyInteger('lock_month');
            $table->unsignedInteger('department_id')->nullable();

            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('basic', 12, 2)->default(0);
            $table->decimal('house_rent', 12, 2)->default(0);
            $table->decimal('medical', 12, 2)->default(0);
            $table->decimal('transport', 12, 2)->default(0);
            $table->decimal('food_allow', 12, 2)->default(0);
            $table->decimal('total_earn', 12, 2)->default(0);
            $table->decimal('total_deduct', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->decimal('ot', 12, 2)->default(0);
            $table->decimal('ot_hours', 8, 2)->default(0);
            $table->decimal('ot_rate', 10, 2)->default(0);
            $table->integer('present')->default(0);
            $table->integer('absent')->default(0);
            $table->decimal('att_bonus', 12, 2)->default(0);
            $table->decimal('deduct_absent', 12, 2)->default(0);
            $table->decimal('deduct_other', 12, 2)->default(0);
            $table->decimal('loan', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('stamp', 12, 2)->default(0);
            $table->decimal('extra_facility', 12, 2)->default(0);
            $table->json('raw_data')->nullable();

            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['employee_id', 'lock_year', 'lock_month'], 'uq_hr_salary_snapshot_period');
        });

        Schema::table('hr_employee_salary_snapshots', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_salary_snapshots');

        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });

        Schema::table('hr_employee_salary_increments', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });
    }
};
