<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_sn', 100)->nullable();
            $table->unsignedInteger('employee_id');
            $table->dateTime('log_time');
            $table->string('type_code', 20)->nullable();
            $table->string('type_name', 50)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->index(['employee_id', 'log_time']);
        });

        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_attendance_machine_logs');
    }
};
