<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->foreignId('activity_assignment_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('team_label', 80)->nullable();
            $table->string('title', 180);
            $table->enum('status', [
                'borrador', 'entregada', 'en_revision', 'ajustes_solicitados', 'revisada', 'retirada',
            ])->default('borrador');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('activity_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
