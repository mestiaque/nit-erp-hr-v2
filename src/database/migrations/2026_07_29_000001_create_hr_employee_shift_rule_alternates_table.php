<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_shift_rule_alternates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shift_rule_id');
            $table->unsignedInteger('shift_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('shift_rule_id')->references('id')->on('hr_employee_shift_rules')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
        });

        // Auto Roster previously supported exactly one alternate shift (alt_shift_id).
        // Carry any existing rows forward as the first (sort_order 0) alternate before
        // the column is dropped, so upgrading never silently loses a configured rule.
        DB::table('hr_employee_shift_rules')
            ->whereNotNull('alt_shift_id')
            ->get(['id', 'alt_shift_id'])
            ->each(function ($rule) {
                DB::table('hr_employee_shift_rule_alternates')->insert([
                    'shift_rule_id' => $rule->id,
                    'shift_id' => $rule->alt_shift_id,
                    'sort_order' => 0,
                    'created_at' => now(),
                ]);
            });

        Schema::table('hr_employee_shift_rules', function (Blueprint $table) {
            $table->dropForeign(['alt_shift_id']);
            $table->dropColumn('alt_shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_shift_rules', function (Blueprint $table) {
            $table->unsignedInteger('alt_shift_id')->nullable()->after('primary_shift_id');
        });

        DB::table('hr_employee_shift_rule_alternates')
            ->where('sort_order', 0)
            ->get(['shift_rule_id', 'shift_id'])
            ->each(function ($alt) {
                DB::table('hr_employee_shift_rules')
                    ->where('id', $alt->shift_rule_id)
                    ->update(['alt_shift_id' => $alt->shift_id]);
            });

        Schema::table('hr_employee_shift_rules', function (Blueprint $table) {
            $table->foreign('alt_shift_id')->references('id')->on('hr_shifts')->cascadeOnDelete();
        });

        Schema::dropIfExists('hr_employee_shift_rule_alternates');
    }
};
