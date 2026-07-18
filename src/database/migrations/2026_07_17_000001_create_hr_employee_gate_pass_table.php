<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_gate_pass', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pass_no', 30)->unique();
            $table->unsignedInteger('employee_id');
            $table->dateTime('out_time');
            $table->dateTime('in_time');
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->string('reason', 100);
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_gate_pass');
    }
};
