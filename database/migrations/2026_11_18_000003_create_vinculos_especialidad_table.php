<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vinculos_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_membership_id')->constrained()->restrictOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->restrictOnDelete();
            $table->boolean('can_create_projects')->default(false);
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['institution_membership_id', 'especialidad_id'], 'vinculo_especialidad_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculos_especialidad');
    }
};
