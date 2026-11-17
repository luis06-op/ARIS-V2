<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('blob_id')->constrained('file_blobs')->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('original_name', 255);
            $table->string('mime', 127);
            $table->string('extension', 16);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->enum('status', ['borrador', 'final'])->default('borrador');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'version_number']);
            $table->index(['document_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
