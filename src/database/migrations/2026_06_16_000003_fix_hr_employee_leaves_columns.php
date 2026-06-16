<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing tinyint status values to string before changing type
        DB::statement("UPDATE hr_employee_leaves SET status = 'active' WHERE status = 1");
        DB::statement("UPDATE hr_employee_leaves SET status = 'inactive' WHERE status = 0");

        Schema::table('hr_employee_leaves', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->string('application_no', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_leaves', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change();
            $table->string('application_no', 30)->nullable(false)->change();
        });
    }
};
