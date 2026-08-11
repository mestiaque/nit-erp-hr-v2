<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_gate_pass', function (Blueprint $table) {
            $table->dateTime('in_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_gate_pass', function (Blueprint $table) {
            $table->dateTime('in_time')->nullable(false)->change();
        });
    }
};
