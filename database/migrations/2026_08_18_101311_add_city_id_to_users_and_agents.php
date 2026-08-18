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
        // 1. Add city_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
        });

        // 2. Add city_id to agents
        Schema::table('agents', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
        });

        // 3. Migrate existing text data
        $uniqueCities = collect()
            ->merge(DB::table('users')->whereNotNull('city')->pluck('city'))
            ->merge(DB::table('agents')->whereNotNull('city')->pluck('city'))
            ->filter()
            ->map(fn($c) => trim($c))
            ->unique();

        foreach ($uniqueCities as $cityName) {
            $cityId = DB::table('cities')->insertGetId([
                'name' => $cityName,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->where('city', $cityName)->update(['city_id' => $cityId]);
            DB::table('agents')->where('city', $cityName)->update(['city_id' => $cityId]);
        }

        // 4. Drop legacy columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('city');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore city columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('city', 100)->nullable();
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->string('city', 100)->nullable();
        });

        // Restore string data
        $users = DB::table('users')->whereNotNull('city_id')->get();
        foreach ($users as $user) {
            $cityName = DB::table('cities')->where('id', $user->city_id)->value('name');
            if ($cityName) {
                DB::table('users')->where('id', $user->id)->update(['city' => $cityName]);
            }
        }

        $agents = DB::table('agents')->whereNotNull('city_id')->get();
        foreach ($agents as $agent) {
            $cityName = DB::table('cities')->where('id', $agent->city_id)->value('name');
            if ($cityName) {
                DB::table('agents')->where('id', $agent->id)->update(['city' => $cityName]);
            }
        }

        // Drop foreign keys and columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
