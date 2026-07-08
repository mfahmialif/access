<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screensavers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_device_id')->nullable()->constrained('tv_devices')->cascadeOnDelete();
            $table->unsignedInteger('idle_timeout')->default(30)->comment('Idle duration in seconds');
            $table->unsignedInteger('interval')->default(8)->comment('Image interval in seconds');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screensavers');
    }
};
