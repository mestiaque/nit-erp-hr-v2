<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved "অগ্রিম বেতনের জন্য আবেদন" (salary advance application) letters - the paper an
 * employee signs BEFORE an advance is given. Record + print only: nothing reads this table
 * for salary, Earnings & Deductions or accounts. The advance itself, once paid, still comes in
 * through Earnings & Deductions (e.g. an approved acc-sfl Salary Advance expense).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_advance_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->date('application_date');
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255)->nullable();
            // First day of the month the advance is asked to be deducted from; null = "next month".
            $table->date('deduction_month')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_advance_applications');
    }
};
