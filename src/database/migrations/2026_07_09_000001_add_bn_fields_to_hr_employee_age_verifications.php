<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_age_verifications', function (Blueprint $table) {
            $table->text('physical_ability_bn')->nullable()->after('physical_ability');
            $table->text('identification_mark_bn')->nullable()->after('identification_mark');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_age_verifications', function (Blueprint $table) {
            $table->dropColumn(['physical_ability_bn', 'identification_mark_bn']);
        });
    }
};
