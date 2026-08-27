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
        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->json('stages')->nullable(); // configuration for stages later
            $table->timestamps();
        });

        Schema::create('customer_constitutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('loan_product_constitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_product_id')->constrained('loan_products')->onDelete('cascade');
            $table->foreignId('constitution_id')->constrained('customer_constitutions')->onDelete('cascade');
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();

            $table->unique(['loan_product_id', 'constitution_id'], 'prod_const_unique');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('loan_product_id')->nullable()->after('agent_id')->constrained('loan_products')->nullOnDelete();
            $table->foreignId('constitution_id')->nullable()->after('loan_product_id')->constrained('customer_constitutions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('constitution_id');
            $table->dropConstrainedForeignId('loan_product_id');
        });

        Schema::dropIfExists('loan_product_constitutions');
        Schema::dropIfExists('customer_constitutions');
        Schema::dropIfExists('loan_products');
    }
};
