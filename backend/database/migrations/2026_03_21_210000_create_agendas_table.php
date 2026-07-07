<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->time('time');
            $table->string('location')->nullable();
            $table->string('teacher')->nullable();
            $table->string('icon')->default('event');
            $table->enum('category', ['Artikel', 'Video', 'Gambar']);
            $table->longText('body')->nullable();
            $table->string('image_path')->nullable();    // untuk Gambar & banner Artikel
            $table->string('video_path')->nullable();    // untuk Video
            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
