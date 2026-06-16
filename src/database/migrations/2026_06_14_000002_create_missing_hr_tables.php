<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hr_weekdays: used for weekly day configuration
        if (!Schema::hasTable('hr_weekdays')) {
            Schema::create('hr_weekdays', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->tinyInteger('day_number')->nullable()->comment('0=Sunday, 1=Monday, ... 6=Saturday');
                $table->tinyInteger('status')->default(1);
                $table->timestamp('created_at')->useCurrent();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
            });
        }

        // hr_production_bonuses: production bonus rules per section
        if (!Schema::hasTable('hr_production_bonuses')) {
            Schema::create('hr_production_bonuses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 191);
                $table->unsignedInteger('section_id')->nullable();
                $table->unsignedInteger('sub_section_id')->nullable();
                $table->decimal('percentage', 8, 4)->default(0);
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamp('created_at')->useCurrent();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
                $table->foreign('sub_section_id')->references('id')->on('hr_sub_sections')->nullOnDelete();
            });
        }

        // hr_requisitions: manpower requisition requests
        if (!Schema::hasTable('hr_requisitions')) {
            Schema::create('hr_requisitions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('requisition_no', 50);
                $table->string('title', 191);
                $table->unsignedInteger('department_id')->nullable();
                $table->unsignedInteger('section_id')->nullable();
                $table->unsignedInteger('designation_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->date('requisition_date')->nullable();
                $table->unsignedBigInteger('requested_by')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 30)->default('draft');
                $table->timestamp('created_at')->useCurrent();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
                $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
                $table->foreign('designation_id')->references('id')->on('hr_designations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_requisitions');
        Schema::dropIfExists('hr_production_bonuses');
        Schema::dropIfExists('hr_weekdays');
    }
};
