<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('classification', ['privado', 'institucional', 'publicado'])->default('privado');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['activo', 'archivado'])->default('activo');
            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['submission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
