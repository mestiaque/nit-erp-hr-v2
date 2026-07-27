<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->unsignedSmallInteger('minimum_ot_minutes')->nullable()->after('ot_grace_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->dropColumn('minimum_ot_minutes');
        });
    }
};
