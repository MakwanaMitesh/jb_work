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
            // Fields required to fully populate the Opinion Report (OR)
            // affidavit without falling back to template demo data.
            $table->string('father_full_name', 150)->nullable()->after('mother_name');
            $table->string('relationship', 20)->nullable()->after('father_full_name'); // Son/Daughter/Wife
            $table->string('place_of_birth', 150)->nullable()->after('date_of_birth');
            $table->string('occupation', 150)->nullable()->after('place_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'father_full_name',
                'relationship',
                'place_of_birth',
                'occupation',
            ]);
        });
    }
};
