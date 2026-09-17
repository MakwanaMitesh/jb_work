<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assessment_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Insert initial assessment years
        DB::table('assessment_years')->insert([
            ['name' => 'A.Y. 2026-27', 'code' => '2026-27', 'sort_order' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'A.Y. 2025-26', 'code' => '2025-26', 'sort_order' => 2, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'A.Y. 2024-25', 'code' => '2024-25', 'sort_order' => 3, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'A.Y. 2023-24', 'code' => '2023-24', 'sort_order' => 4, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'A.Y. 2022-23', 'code' => '2022-23', 'sort_order' => 5, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_years');
    }
};
