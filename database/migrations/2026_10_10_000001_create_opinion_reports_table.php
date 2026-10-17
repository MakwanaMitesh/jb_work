<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The yellow (dynamic) values of the Opinion Report / net-worth affidavit.
     * Age is never stored: it is calculated from the date of birth.
     */
    public function up(): void
    {
        Schema::create('opinion_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();

            // Deponent
            $table->string('deponent_name', 255)->nullable();
            $table->string('relationship', 20)->nullable();
            $table->string('father_name', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 255)->nullable();
            $table->string('occupation', 255)->nullable();
            $table->text('residential_address')->nullable();
            $table->text('office_address')->nullable();

            // Bank / borrower / dates
            $table->string('branch', 255)->nullable();
            $table->string('borrower_name', 255)->nullable();
            $table->date('assets_date')->nullable();
            $table->date('deponent_date')->nullable();
            $table->string('notify_by', 255)->nullable();

            // Assets
            $table->json('properties')->nullable();
            $table->text('outside_india')->nullable();
            $table->json('liquid_assets')->nullable();
            $table->text('gov_securities')->nullable();
            $table->text('shares')->nullable();
            $table->text('life_insurance')->nullable();
            $table->string('life_policy_numbers', 255)->nullable();
            $table->string('life_surrender_value', 100)->nullable();
            $table->text('jewellery')->nullable();
            $table->text('associate_investment')->nullable();
            $table->text('intangible_assets')->nullable();
            $table->string('income_per_month', 255)->nullable();
            $table->text('other_asset')->nullable();

            // Liabilities
            $table->text('borrowings_banks')->nullable();
            $table->text('borrowings_market')->nullable();
            $table->text('borrowings_other')->nullable();
            $table->text('indirect_liability')->nullable();
            $table->text('other_liability')->nullable();

            $table->json('family_members')->nullable();

            $table->json('linked_overrides')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opinion_reports');
    }
};
