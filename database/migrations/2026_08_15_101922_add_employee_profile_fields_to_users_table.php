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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('mobile_number')->nullable()->after('email');
            $table->string('city')->nullable()->after('mobile_number');
            $table->text('address')->nullable()->after('city');
            $table->date('joining_date')->nullable()->after('address');
            $table->string('profile_photo_path')->nullable()->after('joining_date');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('profile_photo_path');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'middle_name',
                'last_name',
                'mobile_number',
                'city',
                'address',
                'joining_date',
                'profile_photo_path',
                'status',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
