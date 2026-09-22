<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Show Cause Notice's signature block was finalized as three fixed slots
 * (Employee — taken from প্রাপক, Manager (HR & Admin), General Manager), not a
 * freely-typed "prepared by" name/designation — so these columns never end up
 * used by the printed letter and are dropped rather than left dead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->dropColumn(['prepared_by_name', 'prepared_by_designation']);
        });
    }

    public function down(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->string('prepared_by_name', 150)->nullable();
            $table->string('prepared_by_designation', 150)->nullable();
        });
    }
};
