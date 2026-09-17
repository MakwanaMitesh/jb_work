<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('status', 20)->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default banks
        $defaultBanks = [
            ['name' => 'State Bank of India', 'status' => 'active', 'sort_order' => 1],
            ['name' => 'HDFC Bank', 'status' => 'active', 'sort_order' => 2],
            ['name' => 'ICICI Bank', 'status' => 'active', 'sort_order' => 3],
            ['name' => 'Axis Bank', 'status' => 'active', 'sort_order' => 4],
            ['name' => 'Kotak Mahindra Bank', 'status' => 'active', 'sort_order' => 5],
            ['name' => 'Punjab National Bank', 'status' => 'active', 'sort_order' => 6],
            ['name' => 'Bank of Baroda', 'status' => 'active', 'sort_order' => 7],
            ['name' => 'IndusInd Bank', 'status' => 'active', 'sort_order' => 8],
            ['name' => 'Union Bank of India', 'status' => 'active', 'sort_order' => 9],
            ['name' => 'Canara Bank', 'status' => 'active', 'sort_order' => 10],
            ['name' => 'Bank of India', 'status' => 'active', 'sort_order' => 11],
            ['name' => 'Central Bank of India', 'status' => 'active', 'sort_order' => 12],
            ['name' => 'Indian Bank', 'status' => 'active', 'sort_order' => 13],
            ['name' => 'IDBI Bank', 'status' => 'active', 'sort_order' => 14],
            ['name' => 'Federal Bank', 'status' => 'active', 'sort_order' => 15],
            ['name' => 'YES Bank', 'status' => 'active', 'sort_order' => 16],
        ];

        $now = now();
        foreach ($defaultBanks as $bank) {
            DB::table('banks')->insert(array_merge($bank, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
