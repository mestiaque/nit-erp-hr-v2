<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            // Drop the FK and int column, replace with varchar for raw ZKTeco employee IDs
            $table->dropForeign(['employee_id']);
            $table->dropIndex('hr_attendance_machine_logs_employee_id_log_time_index');
        });

        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->string('employee_id', 50)->change();
            // Unique index to prevent duplicate punches from machine retries
            $table->unique(['employee_id', 'log_time'], 'hr_machine_logs_emp_time_unique');
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->dropUnique('hr_machine_logs_emp_time_unique');
            $table->unsignedInteger('employee_id')->change();
        });
    }
};
