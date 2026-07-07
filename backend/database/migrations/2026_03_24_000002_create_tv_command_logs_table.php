<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_command_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command');            // navigate, reload, home, banner_show, banner_dismiss
            $table->string('target');             // "all" or device name
            $table->json('payload')->nullable();  // command-specific data
            $table->string('status')->default('sent'); // sent, delivered, failed
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_command_logs');
    }
};
