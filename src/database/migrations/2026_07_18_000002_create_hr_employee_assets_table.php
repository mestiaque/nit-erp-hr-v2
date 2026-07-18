<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('asset_no', 30)->unique();
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('asset_category_id')->nullable();
            $table->string('reporting_manager', 150)->nullable();

            // Asset Information
            $table->string('asset_description', 255)->nullable();
            $table->string('brand', 150)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('serial_no', 150)->nullable();
            $table->string('engine_no', 150)->nullable();
            $table->string('registration_no', 150)->nullable();
            $table->string('asset_code', 100)->nullable();
            $table->decimal('purchase_value', 12, 2)->nullable();

            // Accessories / Purpose (checkbox lists)
            $table->json('accessories')->nullable();
            $table->string('accessories_others', 255)->nullable();
            $table->json('purpose_of_issue')->nullable();
            $table->string('purpose_others', 255)->nullable();

            // Handover
            $table->date('issued_date');
            $table->date('expected_return_date')->nullable();
            $table->string('condition_at_handover', 30)->nullable();
            $table->text('handover_remarks')->nullable();

            // Return
            $table->date('return_date')->nullable();
            $table->string('received_by', 150)->nullable();
            $table->string('condition_on_return', 30)->nullable();
            $table->decimal('damage_cost', 12, 2)->nullable();

            $table->string('status', 20)->default('Active');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('asset_category_id')->references('id')->on('hr_asset_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_assets');
    }
};
