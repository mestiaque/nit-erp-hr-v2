<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->unsignedSmallInteger('ot_grace_minutes')->nullable()->after('allow_ot_hour');
        });
    }

    public function down(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->dropColumn('ot_grace_minutes');
        });
    }
};
