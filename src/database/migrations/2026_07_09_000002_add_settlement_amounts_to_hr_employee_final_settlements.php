<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_final_settlements', function (Blueprint $table) {
            $table->decimal('last_basic_salary', 12, 2)->nullable()->after('third_letter_date');
            $table->decimal('last_gross_salary', 12, 2)->nullable()->after('last_basic_salary');
            $table->unsignedSmallInteger('service_years')->nullable()->after('last_gross_salary');

            $table->smallInteger('unpaid_salary_days')->nullable()->after('service_years');
            $table->decimal('unpaid_salary_amount', 12, 2)->nullable()->after('unpaid_salary_days');

            $table->smallInteger('leave_encashment_days')->nullable()->after('unpaid_salary_amount');
            $table->decimal('leave_encashment_amount', 12, 2)->nullable()->after('leave_encashment_days');

            $table->decimal('gratuity_amount', 12, 2)->nullable()->after('leave_encashment_amount');
            $table->decimal('advance_deduction', 12, 2)->nullable()->after('gratuity_amount');
            $table->decimal('other_earnings', 12, 2)->nullable()->after('advance_deduction');
            $table->decimal('other_deductions', 12, 2)->nullable()->after('other_earnings');
            $table->decimal('net_payable', 12, 2)->nullable()->after('other_deductions');

            $table->text('calculation_notes')->nullable()->after('net_payable');
            $table->string('settlement_status', 20)->default('draft')->after('calculation_notes');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_final_settlements', function (Blueprint $table) {
            $table->dropColumn([
                'last_basic_salary',
                'last_gross_salary',
                'service_years',
                'unpaid_salary_days',
                'unpaid_salary_amount',
                'leave_encashment_days',
                'leave_encashment_amount',
                'gratuity_amount',
                'advance_deduction',
                'other_earnings',
                'other_deductions',
                'net_payable',
                'calculation_notes',
                'settlement_status',
            ]);
        });
    }
};
