<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('excerpt')->nullable();
            $table->enum('priority', ['Urgent', 'Normal', 'Info'])->default('Normal');
            $table->string('audience')->nullable()->comment('Target audience');
            $table->string('location')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('status', ['Aktif', 'Expired'])->default('Aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
