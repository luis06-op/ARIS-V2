<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title', 180);
            $table->longText('content')->nullable();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->char('checksum', 64);
            $table->enum('status', ['borrador', 'publicada'])->default('borrador');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'version_number']);
            $table->index(['activity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_versions');
    }
};
