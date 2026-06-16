<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts all remaining tinyInteger status columns to varchar(20) strings
 * using 'active' / 'inactive' values, consistent with the pattern already
 * established for hr_departments and hr_floor_lines.
 *
 * Uses raw ALTER TABLE instead of ->change() to avoid doctrine/dbal dependency.
 */
return new class extends Migration
{
    private array $tables = [
        'hr_geo_locations',
        'hr_religions',
        'hr_marital_statuses',
        'hr_sexes',
        'hr_payment_methods',
        'hr_working_places',
        'hr_classifications',
        'hr_leave_infos',
        'hr_salary_keys',
        'hr_factories',
        'hr_shifts',
        'hr_sections',
        'hr_sub_sections',
        'hr_designations',
        'hr_bonus_titles',
        'hr_bonus_policies',
        'hr_weekdays',
        'hr_production_bonuses',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // ALTER TABLE changes column type; MySQL auto-converts 0→'0', 1→'1'
            DB::statement("ALTER TABLE `{$table}` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'active'");

            // Remap numeric strings to named values
            DB::table($table)->where('status', '1')->update(['status' => 'active']);
            DB::table($table)->where('status', '0')->update(['status' => 'inactive']);
            DB::table($table)->whereNotIn('status', ['active', 'inactive'])->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->where('status', 'active')->update(['status' => '1']);
            DB::table($table)->where('status', 'inactive')->update(['status' => '0']);

            DB::statement("ALTER TABLE `{$table}` MODIFY `status` TINYINT NOT NULL DEFAULT 1");
        }
    }
};
