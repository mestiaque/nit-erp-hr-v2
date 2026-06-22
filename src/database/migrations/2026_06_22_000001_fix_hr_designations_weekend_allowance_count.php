<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->string('weekend_allowance_count', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->integer('weekend_allowance_count')->nullable()->change();
        });
    }
};
