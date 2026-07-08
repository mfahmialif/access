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
        Schema::table('news', function (Blueprint $table) {
            $table->dateTime('datetime')->nullable()->after('status');
        });
        Schema::table('announcements', function (Blueprint $table) {
            $table->dateTime('datetime')->nullable()->after('status');
        });
        Schema::table('galleries', function (Blueprint $table) {
            $table->dateTime('datetime')->nullable()->after('status');
        });

        // Sync old data: set datetime to created_at
        DB::table('news')->update(['datetime' => DB::raw('created_at')]);
        DB::table('announcements')->update(['datetime' => DB::raw('created_at')]);
        DB::table('galleries')->update(['datetime' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('datetime');
        });
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('datetime');
        });
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn('datetime');
        });
    }
};
