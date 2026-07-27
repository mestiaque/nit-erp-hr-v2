<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->string('weekend_allowance_count_comp', 50)->nullable()->after('weekend_allowance_count');
            $table->decimal('holiday_allowance_comp', 10, 2)->nullable()->after('holiday_allowance');
            $table->unsignedSmallInteger('ot_grace_minutes')->nullable()->after('ot_two_rate');
        });
    }

    public function down(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->dropColumn(['weekend_allowance_count_comp', 'holiday_allowance_comp', 'ot_grace_minutes']);
        });
    }
};
