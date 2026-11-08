<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fine notice text becomes editable per notice:
 * - prior_warning: the "এর আগেও আপনাকে মৌখিকভাবে..." paragraph (null = default text).
 * - incident_description now holds the whole incident sentence; the fixed lead-in the
 *   print view used to add is moved into existing fine rows so they print unchanged.
 */
return new class extends Migration
{
    private const LEAD_IN = 'আপনার দায়িত্বে অবহেলায় ও ভুল সিদ্ধান্ত গ্রহণ করার কারণে ';

    public function up(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->text('prior_warning')->nullable()->after('consequence');
        });

        DB::table('hr_disciplinary_notices')
            ->where('notice_type', 'fine')
            ->whereNotNull('incident_date')
            ->update(['incident_description' => DB::raw("CONCAT('" . self::LEAD_IN . "', COALESCE(incident_description, ''))")]);
    }

    public function down(): void
    {
        Schema::table('hr_disciplinary_notices', function (Blueprint $table) {
            $table->dropColumn('prior_warning');
        });
    }
};
