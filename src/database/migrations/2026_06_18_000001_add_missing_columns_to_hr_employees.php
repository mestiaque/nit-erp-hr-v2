<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employees', 'mobile')) {
                $table->string('mobile', 20)->nullable()->after('emergency_contact');
            }
            if (!Schema::hasColumn('hr_employees', 'grade_lavel')) {
                $table->string('grade_lavel', 50)->nullable()->after('mobile');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_employees', function (Blueprint $table) {
            $table->dropColumn(array_filter(['mobile', 'grade_lavel'], fn($col) => Schema::hasColumn('hr_employees', $col)));
        });
    }
};
