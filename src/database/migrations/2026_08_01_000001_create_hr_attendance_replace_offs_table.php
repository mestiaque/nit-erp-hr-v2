<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Factory-wide "Day Swap" (a.k.a. Replace Off): the office stays open on
        // worked_date (normally a weekend/holiday) and everyone takes their
        // compensatory day off on replace_date instead. Applying the swap physically
        // moves every employee's attendance for worked_date onto replace_date (see
        // AttendanceReplaceOffController::store()) and worked_date is then blocked
        // from ever accepting attendance again — see the guard added in
        // AttendanceMachineController::applyPunchToAttendance().
        Schema::create('hr_attendance_replace_offs', function (Blueprint $table) {
            $table->increments('id');
            $table->date('worked_date');
            $table->date('replace_date');
            $table->string('status', 20)->default('active'); // active | cancelled
            $table->unsignedInteger('moved_attendance_count')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // Not a DB-level unique constraint — a cancelled swap must be able to free
            // up its worked_date for a future one. "No other ACTIVE swap already uses
            // this worked_date" is enforced in AttendanceReplaceOffController::store().
            $table->index('worked_date', 'idx_hr_replace_off_worked_date');
            $table->index('replace_date', 'idx_hr_replace_off_replace_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_attendance_replace_offs');
    }
};
