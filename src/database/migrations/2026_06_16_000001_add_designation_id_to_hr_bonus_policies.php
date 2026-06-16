<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_bonus_policies', function (Blueprint $table) {
            $table->unsignedInteger('designation_id')->nullable()->after('sub_section_id');
            $table->foreign('designation_id')->references('id')->on('hr_designations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_bonus_policies', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->dropColumn('designation_id');
        });
    }
};
