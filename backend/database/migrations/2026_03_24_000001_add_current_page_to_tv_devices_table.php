<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tv_devices', function (Blueprint $table) {
            $table->string('current_page')->nullable()->after('warning_message');
        });
    }

    public function down(): void
    {
        Schema::table('tv_devices', function (Blueprint $table) {
            $table->dropColumn('current_page');
        });
    }
};
