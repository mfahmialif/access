<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_links', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon')->default('link');          // Material Symbols icon name
            $table->string('url');
            $table->string('color')->default('amber');        // amber, blue, emerald, violet, rose, cyan
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['Published', 'Draft'])->default('Published');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ── Default portal links ──
        $now = now();
        DB::table('app_links')->insert([
            [
                'title'      => 'Katalog Library UII Dalwa',
                'subtitle'   => 'OPAC Perpustakaan',
                'icon'       => 'menu_book',
                'url'        => 'https://opac.perpustakaandalwa.com/',
                'color'      => 'amber',
                'sort_order' => 1,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Visitor Library',
                'subtitle'   => 'Buku Tamu Kunjungan',
                'icon'       => 'assignment_ind',
                'url'        => 'https://opac.perpustakaandalwa.com/index.php?p=visitor',
                'color'      => 'blue',
                'sort_order' => 2,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Library UII Dalwa',
                'subtitle'   => 'Website Resmi Perpustakaan',
                'icon'       => 'language',
                'url'        => 'https://perpustakaandalwa.com/',
                'color'      => 'emerald',
                'sort_order' => 3,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Maktabah Shamela',
                'subtitle'   => 'Perpustakaan Digital',
                'icon'       => 'library_books',
                'url'        => 'https://shamela.ws/',
                'color'      => 'rose',
                'sort_order' => 4,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Kamus Almaany',
                'subtitle'   => 'Kamus Arab-Indonesia',
                'icon'       => 'translate',
                'url'        => 'https://www.almaany.com/id/dict/ar-id/',
                'color'      => 'violet',
                'sort_order' => 5,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'      => 'Turath.io',
                'subtitle'   => 'Pencarian Teks Turath',
                'icon'       => 'search',
                'url'        => 'https://app.turath.io/',
                'color'      => 'cyan',
                'sort_order' => 6,
                'status'     => 'Published',
                'created_by' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_links');
    }
};
