<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rough_leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('mobile_number', 20);
            $table->string('alternate_mobile_number', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('loan_requirement', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('source', 100)->nullable();
            $table->foreignId('assigned_employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('new');
            $table->foreignId('converted_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rough_leads');
    }
};
