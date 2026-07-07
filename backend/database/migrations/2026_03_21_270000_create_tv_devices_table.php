<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->enum('orientation', ['landscape', 'portrait'])->default('landscape');
            $table->string('token', 6)->unique();
            $table->enum('status', ['setup', 'online', 'offline'])->default('setup');
            $table->string('ip_address')->nullable();
            $table->timestamp('last_heartbeat')->nullable();
            $table->string('firmware_version')->nullable();
            $table->string('warning_message')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_devices');
    }
};
