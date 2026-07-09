<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Insert default units
        DB::table('units')->insert([
            ['name' => 'Maktabah',            'slug' => 'maktabah',            'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Taman',               'slug' => 'taman',               'created_at' => now(), 'updated_at' => now()],
        ]);

        // Insert role Superadmin
        DB::table('roles')->insertOrIgnore([
            'name' => 'Superadmin',
            'description' => 'Full access to all units and system features.',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
