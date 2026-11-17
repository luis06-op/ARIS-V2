<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_round_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->string('criterion_ref', 120)->nullable();
            $table->foreignId('document_version_id')->nullable()->constrained()->restrictOnDelete();
            $table->enum('visibility', ['autor', 'institucional'])->default('autor');
            $table->timestamp('created_at')->useCurrent();

            $table->index('review_round_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_comments');
    }
};
