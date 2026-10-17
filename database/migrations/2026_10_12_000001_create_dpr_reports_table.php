<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The per-lead values of the Detailed Project Report. Totals (project cost,
     * means of finance, required loan, quotation) are calculated, not stored.
     */
    public function up(): void
    {
        Schema::create('dpr_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();

            // Basic information
            $table->string('firm_name', 255)->nullable();
            $table->string('constitution', 100)->nullable();
            $table->string('applicant_name', 255)->nullable();
            $table->text('business_address')->nullable();
            $table->string('pan', 50)->nullable();
            $table->string('aadhaar', 50)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('education', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('experience', 255)->nullable();
            $table->text('proprietor_history')->nullable();

            // Business profile
            $table->text('activity')->nullable();
            $table->text('business_profile')->nullable();
            $table->text('land_and_factory')->nullable();
            $table->string('udyam', 100)->nullable();
            $table->string('gst', 100)->nullable();
            $table->string('fssai', 100)->nullable();
            $table->string('manpower', 100)->nullable();
            $table->string('connectivity', 255)->nullable();
            $table->string('itr_filing', 255)->nullable();
            $table->string('present_bank', 255)->nullable();
            $table->string('account_type', 100)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('ifsc', 50)->nullable();
            $table->text('location')->nullable();

            $table->json('current_loans')->nullable();

            // Required loan
            $table->string('term_required', 50)->nullable();
            $table->string('term_contribution', 50)->nullable();
            $table->string('cc_required', 50)->nullable();
            $table->string('cc_contribution', 50)->nullable();

            // Project cost / means of finance
            $table->string('cost_land', 50)->nullable();
            $table->string('cost_building', 50)->nullable();
            $table->string('cost_machinery', 50)->nullable();
            $table->string('cost_working_capital', 50)->nullable();
            $table->string('fin_promoter_capital', 50)->nullable();
            $table->string('fin_term_loan', 50)->nullable();
            $table->string('fin_cash_credit', 50)->nullable();

            $table->json('machinery')->nullable();

            // Manufacturing process
            $table->text('process_steps')->nullable();
            $table->text('process_output')->nullable();
            $table->text('process_description')->nullable();

            $table->json('collateral')->nullable();

            // Working capital / market
            $table->string('wc_amount', 50)->nullable();
            $table->text('wc_purpose')->nullable();
            $table->string('wc_raw_material', 100)->nullable();
            $table->string('wc_power_fuel', 100)->nullable();
            $table->string('wc_labour', 100)->nullable();
            $table->string('wc_receivables', 100)->nullable();
            $table->text('market_potential')->nullable();

            $table->json('financials')->nullable();

            $table->json('linked_overrides')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpr_reports');
    }
};
