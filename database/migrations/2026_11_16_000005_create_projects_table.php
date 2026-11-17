<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->string('slug', 120);
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['borrador', 'activo', 'archivado'])->default('borrador');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(['institution_id', 'code']);
            $table->unique(['institution_id', 'slug']);
            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
