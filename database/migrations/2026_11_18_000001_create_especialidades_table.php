<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->restrictOnDelete();
            $table->string('nombre');
            $table->string('codigo', 80);
            $table->timestamps();
            $table->unique(['institution_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
