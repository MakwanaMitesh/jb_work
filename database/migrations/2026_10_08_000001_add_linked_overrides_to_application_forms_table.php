<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_forms', function (Blueprint $table) {
            // Lead-linked fields the user has edited by hand; those stop following the Lead.
            $table->json('linked_overrides')->nullable()->after('saved_at');
        });
    }

    public function down(): void
    {
        Schema::table('application_forms', function (Blueprint $table) {
            $table->dropColumn('linked_overrides');
        });
    }
};
