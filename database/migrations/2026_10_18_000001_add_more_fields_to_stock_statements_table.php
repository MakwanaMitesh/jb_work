<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Part B's age-analysis table (rows 32-35), the second Major Debtors row
     * (row 44) and the "more than 6 months" columns of the first (row 43),
     * and Part C's Other Information table (rows 47-51) are blank, bordered
     * cells in the master template with no formula — genuine inputs the
     * original tab never collected. Added without touching any existing
     * column or stored value.
     */
    public function up(): void
    {
        Schema::table('stock_statements', function (Blueprint $table) {
            // Major Debtors: a name for the existing row (the master's "other debtors" demo label, which the
            // generator already clears), "more than 6 months" for that row, and the second row.
            $table->string('debtor_name', 150)->nullable()->after('debtor_amount');
            $table->unsignedInteger('debtor_more6_number')->nullable()->after('debtor_name');
            $table->decimal('debtor_more6_amount', 18, 2)->nullable()->after('debtor_more6_number');
            $table->string('debtor2_name', 150)->nullable()->after('debtor_more6_amount');
            $table->unsignedInteger('debtor2_up6_number')->nullable()->after('debtor2_name');
            $table->decimal('debtor2_up6_amount', 18, 2)->nullable()->after('debtor2_up6_number');
            $table->unsignedInteger('debtor2_more6_number')->nullable()->after('debtor2_up6_amount');
            $table->decimal('debtor2_more6_amount', 18, 2)->nullable()->after('debtor2_more6_number');

            // Part B.1: age analysis of book debts by category (fixed categories; row 35's Total is calculated).
            $table->json('age_analysis')->nullable()->after('debtor2_more6_amount');

            // Part C: Sales / Production / Consumption, by reporting period.
            $table->json('other_info')->nullable()->after('age_analysis');
            $table->string('sundry_creditors_year_end', 100)->nullable()->after('other_info');
        });
    }

    public function down(): void
    {
        Schema::table('stock_statements', function (Blueprint $table) {
            $table->dropColumn([
                'debtor_name', 'debtor_more6_number', 'debtor_more6_amount', 'debtor2_name', 'debtor2_up6_number', 'debtor2_up6_amount',
                'debtor2_more6_number', 'debtor2_more6_amount', 'age_analysis', 'other_info', 'sundry_creditors_year_end',
            ]);
        });
    }
};
