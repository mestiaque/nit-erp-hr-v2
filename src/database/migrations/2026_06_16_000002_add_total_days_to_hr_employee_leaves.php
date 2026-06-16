<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_leaves', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_days')->nullable()->after('leave_to');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_leaves', function (Blueprint $table) {
            $table->dropColumn('total_days');
        });
    }
};
