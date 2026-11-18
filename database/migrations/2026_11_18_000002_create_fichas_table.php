<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->string('numero', 80);
            $table->string('cohorte', 120);
            $table->timestamps();
            $table->unique(['especialidad_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas');
    }
};
