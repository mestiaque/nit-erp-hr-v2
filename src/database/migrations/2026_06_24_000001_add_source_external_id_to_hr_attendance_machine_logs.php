<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_attendance_machine_logs', 'source')) {
                $table->string('source', 50)->nullable()->after('type_name');
            }
            if (!Schema::hasColumn('hr_attendance_machine_logs', 'external_id')) {
                $table->string('external_id', 100)->nullable()->after('source');
            }
            if (!Schema::hasColumn('hr_attendance_machine_logs', 'work_code')) {
                $table->string('work_code', 20)->nullable()->after('external_id');
            }
            if (!Schema::hasColumn('hr_attendance_machine_logs', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('work_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendance_machine_logs', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['source', 'external_id', 'work_code', 'received_at'],
                fn($col) => Schema::hasColumn('hr_attendance_machine_logs', $col)
            ));
        });
    }
};
