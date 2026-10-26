<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Saved "Show Cause Notice" (কারণ দর্শানো নোটিশ) records — one row per notice
 * actually issued, so a history of every salary-deduction notice sent to an
 * employee is kept (unlike the stateless Personal File letters, which are
 * generated fresh on every print with nothing saved).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_disciplinary_notices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('notice_type', 20)->default('fine');
            $table->unsignedTinyInteger('notice_no')->default(1);
            $table->date('notice_date');
            $table->date('incident_date')->nullable();
            $table->text('incident_description')->nullable();
            $table->string('subject')->nullable();
            $table->text('consequence')->nullable();
            $table->unsignedInteger('deduction_days')->nullable();
            $table->string('memo_no', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_disciplinary_notices');
    }
};
