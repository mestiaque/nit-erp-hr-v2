<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hr_floor_lines: tinyInteger -> string
        DB::statement("ALTER TABLE `hr_floor_lines` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE `hr_floor_lines` SET `status` = CASE WHEN `status` = '1' THEN 'active' ELSE 'inactive' END");

        // hr_departments: tinyInteger -> string
        DB::statement("ALTER TABLE `hr_departments` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'active'");
        DB::statement("UPDATE `hr_departments` SET `status` = CASE WHEN `status` = '1' THEN 'active' ELSE 'inactive' END");
    }

    public function down(): void
    {
        DB::statement("UPDATE `hr_floor_lines` SET `status` = CASE WHEN `status` = 'active' THEN '1' ELSE '0' END");
        DB::statement("ALTER TABLE `hr_floor_lines` MODIFY `status` TINYINT NOT NULL DEFAULT 1");

        DB::statement("UPDATE `hr_departments` SET `status` = CASE WHEN `status` = 'active' THEN '1' ELSE '0' END");
        DB::statement("ALTER TABLE `hr_departments` MODIFY `status` TINYINT NOT NULL DEFAULT 1");
    }
};
