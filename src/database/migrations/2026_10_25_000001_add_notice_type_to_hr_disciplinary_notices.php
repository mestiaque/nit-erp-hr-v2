<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two kinds of disciplinary notice on the same table: the existing Fine & Penalty
 * letter (salary deduction) and the Show Cause letter (কারণ দর্শানো নোটিশ), which
 * has its own subject and consequence line and no deduction days.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->string('notice_type', 20)->default('fine')->after('employee_id');
            $table->string('subject', 255)->nullable()->after('incident_description');
            $table->text('consequence')->nullable()->after('subject');
            $table->unsignedInteger('deduction_days')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->dropColumn(['notice_type', 'subject', 'consequence']);
        });
    }
};
