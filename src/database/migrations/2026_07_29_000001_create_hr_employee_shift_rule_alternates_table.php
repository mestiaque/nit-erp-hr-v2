<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_shift_rule_alternates');
    }
};
