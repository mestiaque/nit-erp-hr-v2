<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Location / Dept" on the Company Asset register (asset register, not HR
 * headcount) always meant a physical location within the factory — a room
 * like "Generator Room" or "MD Sir Room" — not an HR Department, so it needs
 * its own master data (hr_asset_locations) instead of reusing hr_departments.
 * The table this touches has no rows yet, so this is a straight column swap
 * rather than a data migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_company_assets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            $table->unsignedInteger('location_id')->nullable()->after('asset_category_id');
            $table->foreign('location_id')->references('id')->on('hr_asset_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_company_assets', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
            $table->unsignedInteger('department_id')->nullable()->after('asset_category_id');
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
        });
    }
};
