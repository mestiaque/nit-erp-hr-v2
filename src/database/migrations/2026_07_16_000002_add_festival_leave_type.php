<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Some host apps (e.g. erp-software) never had a Festival Leave type at all —
        // the earlier General Leave migration assumed it already existed everywhere,
        // which was only true on erp-suhana. Insert-if-missing only, so a host app that
        // already has its own Festival Leave row (erp-suhana) is left untouched.
        if (!DB::table('hr_leave_infos')->where('code', 'FL')->exists()) {
            DB::table('hr_leave_infos')->insert([
                'name'       => 'Festival Leave',
                'bn_name'    => 'উৎসব ছুটি',
                'code'       => 'FL',
                'days'       => 2,
                'status'     => 'active',
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('hr_leave_infos')->where('code', 'FL')->delete();
    }
};
