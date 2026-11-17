<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('summary', 500);
            $table->enum('status', ['borrador', 'finalizada'])->default('borrador');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'version_number']);
            $table->index(['submission_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_versions');
    }
};
