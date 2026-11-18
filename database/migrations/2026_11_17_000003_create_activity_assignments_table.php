<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->enum('target_type', ['proyecto', 'grupo', 'usuario']);
            $table->string('target_ref', 64);
            $table->foreignId('target_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('due_at')->nullable();
            $table->enum('status', ['activa', 'revocada'])->default('activa');
            $table->timestamps();

            $table->unique(['activity_version_id', 'target_type', 'target_ref'], 'activity_assignment_target_unique');
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_assignments');
    }
};
