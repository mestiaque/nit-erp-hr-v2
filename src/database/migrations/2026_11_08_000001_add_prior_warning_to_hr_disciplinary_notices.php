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
        // Databases created before the create migration gained notice_type / subject /
        // consequence (show-cause notices) never got those columns - add whatever is missing.
        $has = fn (string $column) => Schema::hasColumn('hr_disciplinary_notices', $column);
        $hadType = $has('notice_type');

        Schema::table('hr_disciplinary_notices', function (Blueprint $table) use ($has) {
            if (! $has('notice_type')) {
                $table->string('notice_type', 20)->default('fine')->after('employee_id');
            }
            if (! $has('subject')) {
                $table->string('subject')->nullable()->after('incident_description');
            }
            if (! $has('consequence')) {
                $table->text('consequence')->nullable()->after('incident_description');
            }
            if (! $has('prior_warning')) {
                $table->text('prior_warning')->nullable();
            }
        });

        DB::table('hr_disciplinary_notices')
            ->when($hadType, fn ($q) => $q->where('notice_type', 'fine'))
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
