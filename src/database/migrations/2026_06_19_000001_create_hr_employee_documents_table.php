<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('title', 150);
            $table->string('file_path', 500);
            $table->string('file_name', 255);
            $table->string('file_type', 10);
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->index(['employee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_documents');
    }
};
