<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_shift_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('primary_shift_id'); // baseline shift (also used on the alternating day's "off" weeks)
            $table->unsignedInteger('alt_shift_id');
            $table->tinyInteger('day_of_week'); // Carbon::dayOfWeek: 0=Sunday..6=Saturday
            $table->date('anchor_date'); // reference date: the day_of_week on/after this date is the first "alt" occurrence, alternating every week thereafter
            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->unique('employee_id', 'uq_hr_employee_shift_rule');
        });

        Schema::table('hr_employee_shift_rules', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('primary_shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
            $table->foreign('alt_shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_shift_rules');
    }
};
