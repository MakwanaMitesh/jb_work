<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The typed-in (blue) values of the SBI Stock Statement. Everything
     * calculated stays an Excel formula and is not stored.
     */
    public function up(): void
    {
        Schema::create('stock_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->unique()->constrained()->cascadeOnDelete();

            $table->date('statement_date')->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('cc_limit', 100)->nullable();
            $table->string('place', 255)->nullable();
            $table->string('summary_amount', 100)->nullable();

            // Part A inventory rows (the template has two)
            $table->json('items')->nullable();

            // Part B book debts: "other debtors", up to 6 months
            $table->unsignedInteger('debtor_number')->nullable();
            $table->decimal('debtor_amount', 18, 2)->nullable();

            // Summary table
            $table->decimal('margin_stock', 7, 2)->nullable();
            $table->string('margin_book_debts', 20)->nullable();
            $table->decimal('sub_limit_stock', 18, 2)->nullable();
            $table->decimal('sub_limit_book_debts', 18, 2)->nullable();

            // Lead-linked fields the user has edited by hand
            $table->json('linked_overrides')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_statements');
    }
};
