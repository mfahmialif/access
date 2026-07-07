<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_device_id')->nullable()->constrained('tv_devices')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['emergency', 'announcement', 'info'])->default('info');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One banner per device (null = global)
            $table->unique('tv_device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_banners');
    }
};
