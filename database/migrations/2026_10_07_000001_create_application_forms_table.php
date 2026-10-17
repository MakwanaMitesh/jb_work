<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One Application Form (SBI Mudra loan application) per Lead. Scalar
     * answers are typed columns; the repeating groups (associate concerns,
     * facility tables, machinery rows, performance, statutory obligations)
     * are validated JSON, since their shape is fixed by the Excel template.
     */
    public function up(): void
    {
        Schema::create('application_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();

            // A. Office use
            $table->string('application_serial_no', 100)->nullable();
            $table->string('branch_name', 255)->nullable();
            $table->string('category', 30)->nullable();

            // B. Business information
            $table->string('enterprise_name', 255)->nullable();
            $table->string('constitution', 30)->nullable();
            $table->string('constitution_other', 255)->nullable();
            $table->text('business_address')->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 6)->nullable();
            $table->string('business_premises', 30)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('mobile_country_code', 6)->nullable();
            $table->string('mobile_number', 25)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('activity_existing')->nullable();
            $table->text('activity_proposed')->nullable();
            $table->date('commencement_date')->nullable();
            $table->string('is_registered', 3)->nullable();
            $table->string('registration_no_act', 255)->nullable();
            $table->text('registered_office_address')->nullable();
            $table->string('social_category', 30)->nullable();
            $table->string('minority_community', 30)->nullable();

            // C. Proprietor / partner / director
            $table->string('applicant_name', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('sex', 10)->nullable();
            $table->text('residential_address')->nullable();
            $table->string('applicant_mobile', 25)->nullable();
            $table->string('qualification', 255)->nullable();
            $table->string('experience_years', 50)->nullable();
            $table->string('id_proof_type', 100)->nullable();
            $table->string('id_proof_number', 100)->nullable();
            $table->string('address_proof_type', 100)->nullable();
            $table->string('address_proof_number', 255)->nullable();
            $table->string('pan_din_number', 100)->nullable();
            $table->string('bank_relationship', 255)->nullable();

            // Table 2: banking, working capital, term loan
            $table->string('customer_id', 100)->nullable();
            $table->json('associate_concerns')->nullable();
            $table->json('existing_facilities')->nullable();
            $table->json('proposed_facilities')->nullable();
            $table->string('wc_fy_label_1', 30)->nullable();
            $table->string('wc_fy_label_2', 30)->nullable();
            $table->decimal('wc_actual_sales_1', 18, 2)->nullable();
            $table->decimal('wc_actual_sales_2', 18, 2)->nullable();
            $table->decimal('wc_projected_sales', 18, 2)->nullable();
            $table->string('wc_working_cycle_months', 30)->nullable();
            $table->decimal('wc_inventory', 18, 2)->nullable();
            $table->decimal('wc_debtors', 18, 2)->nullable();
            $table->decimal('wc_creditors', 18, 2)->nullable();
            $table->decimal('wc_promoter_contribution', 18, 2)->nullable();
            $table->decimal('wc_limits', 18, 2)->nullable();
            $table->json('machinery')->nullable();
            $table->string('repayment_period', 100)->nullable();
            $table->string('moratorium_period', 100)->nullable();

            // Table 3: performance, statutory obligations, declaration
            $table->json('performance')->nullable();
            $table->json('statutory')->nullable();
            $table->date('declaration_date')->nullable();
            $table->string('declaration_place', 255)->nullable();

            // Set on the first explicit save; until then the form is just Lead-derived defaults
            $table->timestamp('saved_at')->nullable();

            // Latest generated workbook (path on the private disk)
            $table->string('generated_path', 255)->nullable();
            $table->timestamp('generated_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_forms');
    }
};
