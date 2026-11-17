<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_blobs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('disk', 32)->default('local');
            $table->string('storage_key', 255)->unique();
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64)->index();
            $table->string('mime', 127);
            $table->string('extension', 16);
            $table->enum('status', ['cuarentena', 'disponible', 'huerfano', 'eliminado'])
                ->default('cuarentena');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_blobs');
    }
};
