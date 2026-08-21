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
        Schema::table('agents', function (Blueprint $table) {
            // Qualification & Contact Info
            $table->string('qualification')->nullable()->after('profile_photo_path');
            $table->string('resume_path')->nullable()->after('qualification');
            $table->string('alternate_mobile_number')->nullable()->after('resume_path');

            // KYC Documents
            $table->string('aadhaar_card_number')->nullable()->after('alternate_mobile_number');
            $table->string('aadhaar_photo_path')->nullable()->after('aadhaar_card_number');
            $table->string('pan_card_number')->nullable()->after('aadhaar_photo_path');
            $table->string('pan_photo_path')->nullable()->after('pan_card_number');

            // Bank Details
            $table->string('bank_account_number')->nullable()->after('pan_photo_path');
            $table->string('bank_ifsc_code')->nullable()->after('bank_account_number');
            $table->string('bank_name')->nullable()->after('bank_ifsc_code');
            $table->string('bank_account_holder_name')->nullable()->after('bank_name');
            $table->string('bank_cheque_photo_path')->nullable()->after('bank_account_holder_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'qualification',
                'resume_path',
                'alternate_mobile_number',
                'aadhaar_card_number',
                'aadhaar_photo_path',
                'pan_card_number',
                'pan_photo_path',
                'bank_account_number',
                'bank_ifsc_code',
                'bank_name',
                'bank_account_holder_name',
                'bank_cheque_photo_path',
            ]);
        });
    }
};
