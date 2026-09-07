<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_company_assets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('asset_code', 40)->unique();
            $table->unsignedInteger('asset_category_id')->nullable();
            $table->string('description', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('department_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('supplier_vendor', 150)->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->unsignedInteger('useful_life_years')->nullable();
            $table->string('status', 20)->default('In Use');
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('asset_category_id')->references('id')->on('hr_asset_categories')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_company_assets');
    }
};
