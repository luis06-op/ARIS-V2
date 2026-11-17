<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained()->restrictOnDelete();
            $table->enum('event', ['creada', 'aprobada', 'rechazada', 'publicada', 'revocada', 'republicada']);
            $table->foreignId('actor_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('occurred_at')->useCurrent();
            $table->string('reason', 255)->nullable();
            $table->json('metadata')->nullable();

            $table->index(['publication_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_events');
    }
};
