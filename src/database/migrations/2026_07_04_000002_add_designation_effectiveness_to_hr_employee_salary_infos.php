<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_salary_infos', function (Blueprint $table) {
            $table->decimal('attendance_bonus', 10, 2)->nullable()->after('extra_facility');
            $table->decimal('attendance_bonus_com', 10, 2)->nullable()->after('attendance_bonus');
            $table->decimal('tiffin_allowance', 10, 2)->nullable()->after('attendance_bonus_com');
            $table->decimal('min_tiffin_hour', 5, 2)->nullable()->after('tiffin_allowance');
            $table->decimal('night_allowance', 10, 2)->nullable()->after('min_tiffin_hour');
            $table->decimal('min_night_hour', 5, 2)->nullable()->after('night_allowance');
            $table->decimal('dinner_allowance', 10, 2)->nullable()->after('min_night_hour');
            $table->decimal('min_dinner_hour', 5, 2)->nullable()->after('dinner_allowance');
            $table->string('payment_way', 50)->nullable()->after('min_dinner_hour');
            $table->string('weekend_allowance_count', 50)->nullable()->after('payment_way');
            $table->decimal('holiday_allowance', 10, 2)->nullable()->after('weekend_allowance_count');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_salary_infos', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_bonus',
                'attendance_bonus_com',
                'tiffin_allowance',
                'min_tiffin_hour',
                'night_allowance',
                'min_night_hour',
                'dinner_allowance',
                'min_dinner_hour',
                'payment_way',
                'weekend_allowance_count',
                'holiday_allowance',
            ]);
        });
    }
};
