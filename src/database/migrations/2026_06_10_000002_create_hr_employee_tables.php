<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->string('employee_id', 30);
            $table->date('join_date')->nullable();
            $table->unsignedInteger('classification_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('sub_section_id')->nullable();
            $table->unsignedInteger('floor_line_id')->nullable();
            $table->unsignedInteger('designation_id')->nullable();
            $table->string('grade', 20)->nullable();
            $table->unsignedInteger('working_place_id')->nullable();
            $table->unsignedInteger('shift_id')->nullable();
            $table->string('weekend')->nullable();
            $table->string('employment_status', 50)->nullable();
            $table->date('exited_at')->nullable();
            $table->string('personal_contact', 20)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->string('grade_lavel', 50)->nullable();
            $table->boolean('comp_one')->default(false);
            $table->boolean('comp_two')->default(false);
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('salary_type', 50)->nullable();
            $table->unique('employee_id');

            $table->foreign('classification_id')->references('id')->on('hr_classifications')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
            $table->foreign('designation_id')->references('id')->on('hr_designations')->nullOnDelete();
            $table->foreign('floor_line_id')->references('id')->on('hr_floor_lines')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
            $table->foreign('shift_id')->references('id')->on('hr_shifts')->nullOnDelete();
            $table->foreign('sub_section_id')->references('id')->on('hr_sub_sections')->nullOnDelete();
            $table->foreign('working_place_id')->references('id')->on('hr_working_places')->nullOnDelete();
        });

        // hr_departments and hr_employees reference each other, so this key is added once both exist.
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->foreign('head_of_department')->references('id')->on('hr_employees')->nullOnDelete();
        });

        Schema::create('hr_employee_basic_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('father_name', 150)->nullable();
            $table->string('bn_father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('bn_mother_name', 150)->nullable();
            $table->unsignedInteger('marital_status_id')->nullable();
            $table->string('spouse_name', 150)->nullable();
            $table->string('bn_spouse_name', 150)->nullable();
            $table->unsignedInteger('sex_id')->nullable();
            $table->integer('children_boys')->default(0);
            $table->integer('children_girls')->default(0);
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->unsignedInteger('religion_id')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('blood_group')->nullable();
            $table->unsignedInteger('nationality_country_id')->nullable();
            $table->string('national_id_no', 30)->nullable();
            $table->string('birth_registration_no', 30)->nullable();
            $table->string('passport_no', 30)->nullable();
            $table->string('driving_license_no', 30)->nullable();
            $table->text('special_id_sign')->nullable();
            $table->text('bn_special_id_sign')->nullable();
            $table->text('educational_experience')->nullable();
            $table->text('bn_educational_experience')->nullable();
            $table->text('job_experience')->nullable();
            $table->text('bn_job_experience')->nullable();
            $table->string('previous_organization', 200)->nullable();
            $table->string('bn_previous_organization', 200)->nullable();
            $table->string('reference_name', 150)->nullable();
            $table->string('bn_reference_name', 150)->nullable();
            $table->string('reference_designation', 150)->nullable();
            $table->string('bn_reference_designation', 150)->nullable();
            $table->string('reference_card_no', 50)->nullable();
            $table->string('bn_reference_card_no', 50)->nullable();
            $table->string('reference_mobile_no', 20)->nullable();
            $table->string('bn_reference_mobile_no', 20)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unique('employee_id');

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('marital_status_id')->references('id')->on('hr_marital_statuses')->nullOnDelete();
            $table->foreign('nationality_country_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('hr_payment_methods')->nullOnDelete();
            $table->foreign('religion_id')->references('id')->on('hr_religions')->nullOnDelete();
            $table->foreign('sex_id')->references('id')->on('hr_sexes')->nullOnDelete();
        });

        Schema::create('hr_employee_salary_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->decimal('gross_salary', 12, 2)->nullable();
            $table->decimal('gross_salary_comp1', 12, 2)->nullable();
            $table->decimal('gross_salary_comp2', 12, 2)->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->string('bank_ac_or_phone', 50)->nullable();
            $table->decimal('car_fuel', 10, 2)->nullable();
            $table->decimal('phone_internet', 10, 2)->nullable();
            $table->text('extra_facility')->nullable();
            $table->decimal('attendance_bonus', 10, 2)->nullable();
            $table->decimal('attendance_bonus_com', 10, 2)->nullable();
            $table->decimal('tiffin_allowance', 10, 2)->nullable();
            $table->decimal('min_tiffin_hour', 5, 2)->nullable();
            $table->decimal('night_allowance', 10, 2)->nullable();
            $table->decimal('min_night_hour', 5, 2)->nullable();
            $table->decimal('dinner_allowance', 10, 2)->nullable();
            $table->decimal('min_dinner_hour', 5, 2)->nullable();
            $table->string('payment_way', 50)->nullable();
            $table->string('weekend_allowance_count', 50)->nullable();
            $table->decimal('holiday_allowance', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->string('tax_calculate_by', 20)->nullable();
            $table->date('effective_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('hr_payment_methods')->nullOnDelete();
        });

        Schema::create('hr_employee_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('type', 30);
            $table->unsignedInteger('district_id')->nullable();
            $table->unsignedInteger('police_station_id')->nullable();
            $table->unsignedInteger('post_office_id')->nullable();
            $table->string('post_office', 100)->nullable();
            $table->string('bn_post_office', 100)->nullable();
            $table->string('village', 150)->nullable();
            $table->string('bn_village', 150)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unique(['employee_id', 'type'], 'uq_emp_address_type');

            $table->foreign('district_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('police_station_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
            $table->foreign('post_office_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
        });

        Schema::create('hr_employee_nominees', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('photo')->nullable();
            $table->string('name', 150);
            $table->string('bn_name', 150)->nullable();
            $table->unsignedInteger('district_id')->nullable();
            $table->unsignedInteger('police_station_id')->nullable();
            $table->unsignedInteger('post_office_id')->nullable();
            $table->string('post_office', 100)->nullable();
            $table->string('bn_post_office', 100)->nullable();
            $table->string('village', 150)->nullable();
            $table->string('bn_village', 150)->nullable();
            $table->string('nid_no', 30)->nullable();
            $table->string('mobile_no', 20)->nullable();
            $table->string('relation', 100)->nullable();
            $table->string('bn_relation', 100)->nullable();
            $table->integer('age')->nullable();
            $table->decimal('net_payment', 5, 2)->nullable();
            $table->decimal('provident_fund', 5, 2)->nullable();
            $table->decimal('insurance', 5, 2)->nullable();
            $table->decimal('accident_fine', 5, 2)->nullable();
            $table->decimal('profit', 5, 2)->nullable();
            $table->decimal('others', 5, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('district_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('police_station_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
            $table->foreign('post_office_id')->references('id')->on('hr_geo_locations')->nullOnDelete();
        });

        Schema::create('hr_employee_age_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->text('physical_ability')->nullable();
            $table->text('physical_ability_bn')->nullable();
            $table->text('identification_mark')->nullable();
            $table->text('identification_mark_bn')->nullable();
            $table->integer('age_years')->nullable();
            $table->date('verified_date')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unique('employee_id');

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });

        Schema::create('hr_employee_separations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->string('status', 30);
            $table->text('remarks')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('final_settlement', 50)->nullable();
            $table->boolean('with_paid')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });

        Schema::create('hr_employee_final_settlements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->date('absent_date')->nullable();
            $table->date('first_letter_date')->nullable();
            $table->date('second_letter_date')->nullable();
            $table->date('third_letter_date')->nullable();
            $table->decimal('last_basic_salary', 12, 2)->nullable();
            $table->decimal('last_gross_salary', 12, 2)->nullable();
            $table->unsignedSmallInteger('service_years')->nullable();
            $table->smallInteger('unpaid_salary_days')->nullable();
            $table->decimal('unpaid_salary_amount', 12, 2)->nullable();
            $table->smallInteger('leave_encashment_days')->nullable();
            $table->decimal('leave_encashment_amount', 12, 2)->nullable();
            $table->decimal('gratuity_amount', 12, 2)->nullable();
            $table->decimal('advance_deduction', 12, 2)->nullable();
            $table->decimal('other_earnings', 12, 2)->nullable();
            $table->decimal('other_deductions', 12, 2)->nullable();
            $table->decimal('net_payable', 12, 2)->nullable();
            $table->text('calculation_notes')->nullable();
            $table->string('settlement_status', 20)->default('draft');
            $table->string('selected_letter_print', 10)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });

        Schema::create('hr_employee_salary_increments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('classification_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('section_id')->nullable();
            $table->unsignedInteger('designation_id')->nullable();
            $table->decimal('previous_salary', 12, 2);
            $table->decimal('increment_amount', 12, 2);
            $table->decimal('increment_percentage', 8, 4)->nullable();
            $table->decimal('previous_salary_comp_1', 12, 2)->nullable();
            $table->decimal('new_salary_comp_1', 12, 2)->nullable();
            $table->decimal('previous_salary_comp_2', 12, 2)->nullable();
            $table->decimal('new_salary_comp_2', 12, 2)->nullable();
            $table->string('remarks', 500)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->decimal('new_salary', 12, 2);
            $table->date('increment_date');
            $table->tinyInteger('status')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('classification_id')->references('id')->on('hr_classifications')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('hr_departments')->nullOnDelete();
            $table->foreign('designation_id')->references('id')->on('hr_designations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('hr_sections')->nullOnDelete();
        });

        Schema::create('hr_employee_other_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->date('txn_date');
            $table->decimal('advance_iou', 10, 2)->nullable();
            $table->decimal('ot_adjust', 10, 2)->nullable();
            $table->decimal('day_adjust', 5, 2)->nullable();
            $table->decimal('earnings', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
        });

        Schema::create('hr_employee_leaves', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id');
            $table->date('application_date');
            $table->string('application_no', 50)->nullable();
            $table->unsignedBigInteger('leave_type_id');
            $table->date('leave_from');
            $table->date('leave_to');
            $table->unsignedSmallInteger('total_days')->nullable();
            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 30)->default('pending');
            $table->timestamp('created_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unique('application_no');

            $table->foreign('employee_id')->references('id')->on('hr_employees')->cascadeOnDelete();
            $table->foreign('leave_type_id')->references('id')->on('hr_leave_infos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_departments', function (Blueprint $table) {
            $table->dropForeign('hr_departments_head_of_department_foreign');
        });
        Schema::dropIfExists('hr_employee_leaves');
        Schema::dropIfExists('hr_employee_other_transactions');
        Schema::dropIfExists('hr_employee_salary_increments');
        Schema::dropIfExists('hr_employee_final_settlements');
        Schema::dropIfExists('hr_employee_separations');
        Schema::dropIfExists('hr_employee_age_verifications');
        Schema::dropIfExists('hr_employee_nominees');
        Schema::dropIfExists('hr_employee_addresses');
        Schema::dropIfExists('hr_employee_salary_infos');
        Schema::dropIfExists('hr_employee_basic_infos');
        Schema::dropIfExists('hr_employees');
    }
};
