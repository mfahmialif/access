<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // News: index title for search
        Schema::table('news', function (Blueprint $table) {
            $table->index('title', 'news_title_index');
        });

        // Agendas: index title
        Schema::table('agendas', function (Blueprint $table) {
            $table->index('title', 'agendas_title_index');
        });

        // Weeklies: index title
        Schema::table('weeklies', function (Blueprint $table) {
            $table->index('title', 'weeklies_title_index');
        });

        // Monthlies: index title
        Schema::table('monthlies', function (Blueprint $table) {
            $table->index('title', 'monthlies_title_index');
        });

        // Galleries: index title
        Schema::table('galleries', function (Blueprint $table) {
            $table->index('title', 'galleries_title_index');
        });

        // Announcements: index title
        Schema::table('announcements', function (Blueprint $table) {
            $table->index('title', 'announcements_title_index');
        });

        // App links: index title
        Schema::table('app_links', function (Blueprint $table) {
            $table->index('title', 'app_links_title_index');
        });
    }

    public function down(): void
    {
        Schema::table('news', fn (Blueprint $t) => $t->dropIndex('news_title_index'));
        Schema::table('agendas', fn (Blueprint $t) => $t->dropIndex('agendas_title_index'));
        Schema::table('weeklies', fn (Blueprint $t) => $t->dropIndex('weeklies_title_index'));
        Schema::table('monthlies', fn (Blueprint $t) => $t->dropIndex('monthlies_title_index'));
        Schema::table('galleries', fn (Blueprint $t) => $t->dropIndex('galleries_title_index'));
        Schema::table('announcements', fn (Blueprint $t) => $t->dropIndex('announcements_title_index'));
        Schema::table('app_links', fn (Blueprint $t) => $t->dropIndex('app_links_title_index'));
    }
};
