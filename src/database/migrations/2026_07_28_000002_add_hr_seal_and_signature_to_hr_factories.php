<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->string('hr_seal')->nullable()->after('authority_sign');
            $table->string('hr_signature')->nullable()->after('hr_seal');
        });
    }

    public function down(): void
    {
        Schema::table('hr_factories', function (Blueprint $table) {
            $table->dropColumn(['hr_seal', 'hr_signature']);
        });
    }
};
