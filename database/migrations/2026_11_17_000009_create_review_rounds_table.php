<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('round_number');
            $table->enum('status', ['asignada', 'en_curso', 'finalizada', 'cancelada'])
                ->default('asignada');
            $table->enum('decision', ['aprobada', 'ajustes', 'rechazada'])->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_version_id', 'evaluator_id', 'round_number']);
            $table->index(['submission_version_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_rounds');
    }
};
