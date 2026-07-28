<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->boolean('is_fixed_punch')->default(false)->after('is_ot_basis_others_2');
        });
    }

    public function down(): void
    {
        Schema::table('hr_designations', function (Blueprint $table) {
            $table->dropColumn('is_fixed_punch');
        });
    }
};
