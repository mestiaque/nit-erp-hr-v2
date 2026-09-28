<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields the company's own asset-register spreadsheet template carries that
 * the register didn't yet have: Brand/Model (identification), and the
 * Depreciation Method/Salvage Value inputs needed to compute Annual/
 * Accumulated Depreciation and Net Book Value (added as accessors on the
 * model, not stored — they're derived from these plus unit_cost/quantity/
 * useful_life_years/purchase_date, so storing them would just drift out of
 * sync whenever any of those change).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_company_assets', function (Blueprint $table) {
            $table->string('brand', 150)->nullable()->after('description');
            $table->string('model', 150)->nullable()->after('brand');
            $table->string('depreciation_method', 50)->nullable()->after('useful_life_years');
            $table->decimal('salvage_value', 12, 2)->nullable()->after('depreciation_method');
        });
    }

    public function down(): void
    {
        Schema::table('hr_company_assets', function (Blueprint $table) {
            $table->dropColumn(['brand', 'model', 'depreciation_method', 'salvage_value']);
        });
    }
};
