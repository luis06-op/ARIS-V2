<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('function', ['lider', 'instructor', 'evaluador', 'aprendiz', 'observador']);
            $table->date('assigned_at');
            $table->date('ended_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id', 'function']);
            $table->index(['project_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_memberships');
    }
};
