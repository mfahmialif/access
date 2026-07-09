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
        $tables = [
            'agendas', 'weeklies', 'monthlies', 'galleries', 'news', 'announcements',
            'app_links', 'tv_devices', 'screensavers', 'active_banners'
        ];

        // Ensure default unit exists for existing data
        $defaultUnit = DB::table('units')->where('slug', 'umum')->first();
        $defaultUnitId = $defaultUnit ? $defaultUnit->id : 1;

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('unit_id')->nullable()->after('id')->constrained('units')->nullOnDelete();
                });
                
                // Set default unit_id for existing records
                DB::table($tableName)->update(['unit_id' => $defaultUnitId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'agendas', 'weeklies', 'monthlies', 'galleries', 'news', 'announcements',
            'app_links', 'tv_devices', 'screensavers', 'active_banners'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['unit_id']);
                    $table->dropColumn('unit_id');
                });
            }
        }
    }
};
