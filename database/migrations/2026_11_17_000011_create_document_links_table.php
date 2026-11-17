<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('activity_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('submission_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('review_round_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_links');
    }
};
