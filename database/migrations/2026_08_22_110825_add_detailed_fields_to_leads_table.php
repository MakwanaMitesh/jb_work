<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // KYC Details
            $table->date('date_of_birth')->nullable()->after('alternate_mobile_number');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('aadhar_card', 30)->nullable()->after('address');
            $table->string('pan_card', 30)->nullable()->after('aadhar_card');
            $table->string('udyam_registration', 50)->nullable()->after('pan_card');
            $table->string('education', 100)->nullable()->after('udyam_registration');
            $table->string('mother_name', 150)->nullable()->after('education');
            
            // ITR Details
            $table->string('itr_id', 100)->nullable()->after('mother_name');
            $table->string('itr_password', 100)->nullable()->after('itr_id');
            $table->string('itr_audited', 10)->nullable()->after('itr_password'); // Yes/No
            $table->boolean('itr_ay_2026_27')->default(false)->after('itr_audited');
            $table->boolean('itr_ay_2025_26')->default(false)->after('itr_ay_2026_27');
            $table->boolean('itr_ay_2024_25')->default(false)->after('itr_ay_2025_26');
            
            // Bank Details (JSON structure)
            $table->json('bank_details')->nullable()->after('itr_ay_2024_25');

            // Business Details
            $table->string('business_name', 150)->nullable()->after('bank_details');
            $table->string('constitution_of_business', 100)->nullable()->after('business_name');
            $table->text('introduction')->nullable()->after('constitution_of_business');
            $table->text('business_address')->nullable()->after('introduction');
            $table->string('gst_applicable', 10)->nullable()->after('business_address'); // Yes/No
            $table->string('gst_number', 50)->nullable()->after('gst_applicable');
            $table->string('gst_id', 100)->nullable()->after('gst_number');
            $table->string('gst_password', 100)->nullable()->after('gst_id');
            $table->string('firm_name', 150)->nullable()->after('gst_password');
            $table->string('business_activity', 50)->nullable()->after('firm_name'); // manufacturing/trading/services
            $table->string('business_experience', 100)->nullable()->after('business_activity');
            $table->string('no_of_manpower', 50)->nullable()->after('business_experience');
            $table->string('business_location', 150)->nullable()->after('no_of_manpower');
            $table->string('area_of_premises', 100)->nullable()->after('business_location');
            $table->string('connectivity', 150)->nullable()->after('area_of_premises');

            // Loan Details
            $table->string('required_loan_amount', 50)->nullable()->after('connectivity');
            $table->string('cc_amount', 50)->nullable()->after('required_loan_amount');
            $table->text('cc_details')->nullable()->after('cc_amount');
            $table->string('term_loan_amount', 50)->nullable()->after('cc_details');
            $table->text('term_loan_machinery_details')->nullable()->after('term_loan_amount');
            
            // Current Loan Details (JSON structure)
            $table->json('current_loans')->nullable()->after('term_loan_machinery_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'gender',
                'address',
                'aadhar_card',
                'pan_card',
                'udyam_registration',
                'education',
                'mother_name',
                'itr_id',
                'itr_password',
                'itr_audited',
                'itr_ay_2026_27',
                'itr_ay_2025_26',
                'itr_ay_2024_25',
                'bank_details',
                'business_name',
                'constitution_of_business',
                'introduction',
                'business_address',
                'gst_applicable',
                'gst_number',
                'gst_id',
                'gst_password',
                'firm_name',
                'business_activity',
                'business_experience',
                'no_of_manpower',
                'business_location',
                'area_of_premises',
                'connectivity',
                'required_loan_amount',
                'cc_amount',
                'cc_details',
                'term_loan_amount',
                'term_loan_machinery_details',
                'current_loans',
            ]);
        });
    }
};
