<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hr_leave_infos')->updateOrInsert(
            ['code' => 'GL'],
            [
                'name'       => 'General Leave',
                'bn_name'    => 'সাধারণ ছুটি',
                'code'       => 'GL',
                'days'       => 0,
                'status'     => 'active',
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('hr_leave_infos')->where('code', 'GL')->delete();
    }
};
