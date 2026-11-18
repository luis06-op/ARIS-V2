<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurisdicciones_evaluador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_membership_id')->constrained()->restrictOnDelete();
            $table->foreignId('ficha_id')->constrained('fichas')->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['institution_membership_id', 'ficha_id'], 'jurisdiccion_evaluador_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurisdicciones_evaluador');
    }
};
