<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_sn', 100)->nullable();
            $table->string('employee_id', 50);
            $table->dateTime('log_time');
            $table->string('type_code', 20)->nullable();
            $table->string('type_name', 50)->nullable();
            $table->string('source', 50)->nullable();
            $table->string('external_id', 100)->nullable();
            $table->string('work_code', 20)->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unique(['employee_id', 'log_time'], 'hr_machine_logs_emp_time_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_attendance_machine_logs');
    }
};
