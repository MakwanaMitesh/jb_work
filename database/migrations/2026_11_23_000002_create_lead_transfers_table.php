<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permanent, append-only workflow history of every lead. Stage/role names are copied in at
        // write time so a row still reads correctly after the stage is renamed, re-roled or removed.
        Schema::create('lead_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            // created | transferred | reassigned | completed
            $table->string('action', 20)->index();

            $table->foreignId('from_stage_id')->nullable()->constrained('lead_workflow_stages')->nullOnDelete();
            $table->string('from_stage_name', 100)->nullable();
            $table->string('from_role_name', 125)->nullable();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('to_stage_id')->nullable()->constrained('lead_workflow_stages')->nullOnDelete();
            $table->string('to_stage_name', 100)->nullable();
            $table->string('to_role_name', 125)->nullable();
            $table->foreignId('to_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('transferred_at')->index();
            $table->timestamps();

            $table->index(['lead_id', 'transferred_at']);
        });

        // Audit trail of changes to the workflow configuration itself.
        Schema::create('workflow_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_change_logs');
        Schema::dropIfExists('lead_transfers');
    }
};
