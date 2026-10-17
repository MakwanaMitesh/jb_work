<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The customer/profile values printed on the CMA workbook's "profile"
     * sheet. The financial statements and ratios stay in the workbook.
     */
    public function up(): void
    {
        Schema::create('cma_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('activity', 255)->nullable();
            $table->string('firm_name', 255)->nullable();
            $table->string('proprietor_name', 255)->nullable();
            $table->string('pan', 50)->nullable();
            $table->string('udyam_no', 100)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('address')->nullable();

            $table->string('business_experience', 255)->nullable();
            $table->string('area_of_operation', 255)->nullable();
            $table->string('manpower', 100)->nullable();

            $table->string('space', 255)->nullable();
            $table->string('connectivity', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('electricity_water', 255)->nullable();
            $table->string('existing_facility', 255)->nullable();

            $table->json('linked_overrides')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cma_profiles');
    }
};
