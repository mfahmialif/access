<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\Role;
use App\Models\User;
use App\Models\TvDevice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Roles ──
        $admin = Role::create(['name' => 'Admin', 'description' => 'Full access to all system features and settings.', 'status' => 'Active']);
        $operator = Role::create(['name' => 'Operator', 'description' => 'Can manage and publish content across the platform.', 'status' => 'Active']);
        $user = Role::create(['name' => 'User', 'description' => 'Read-only access to specific modules.', 'status' => 'Active']);

        // ── Users ──
        $adminUser = User::create(['username' => 'admin', 'name' => 'Administrator', 'email' => 'admin@access.id', 'password' => bcrypt('password'), 'role_id' => $admin->id, 'status' => 'Active', 'last_active_at' => now()]);
        User::create(['username' => 'operator', 'name' => 'Operator Konten', 'email' => 'operator@access.id', 'password' => bcrypt('password'), 'role_id' => $operator->id, 'status' => 'Active', 'last_active_at' => now()->subHours(2)]);
        User::create(['username' => 'user', 'name' => 'User Biasa', 'email' => 'user@access.id', 'password' => bcrypt('password'), 'role_id' => $user->id, 'status' => 'Active', 'last_active_at' => now()->subDays(1)]);

        $icons = ['mosque', 'menu_book', 'school', 'groups', 'auto_stories', 'record_voice_over', 'campaign', 'sports_soccer', 'live_tv', 'cleaning_services', 'videocam', 'palette', 'computer', 'flag', 'work'];
        $cats = ['Artikel', 'Video', 'Gambar'];
        $locations = ['Masjid Jami Dalwa', 'Aula Utama', 'Gedung A', 'Lab Komputer', 'Lapangan Utama', 'Gedung Serbaguna', 'Asrama Putra', 'Perpustakaan', 'Hall Utama'];
        $teachers = ['Ustadz Ahmad', 'Ustadz Muhammad', 'Ustadz Abdullah', 'Ustadz Fauzan', 'Ustadz Hafidz', 'Imam Masjid', 'Pimpinan Pesantren', 'Pembina Ekskul', 'Pembina Media', 'Pembina Bahasa', 'Pembina Olahraga', 'Pengurus Asrama'];
        $times = ['04:00','05:00','06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','19:30','20:00','21:00'];
        $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

        // ── Agenda Harian (50 items) ──
        $harianTitles = [
            'Sholat Tahajud & Subuh','Kajian Pagi Kitab Al-Hikam','KBM Sesi 1','Ishoma & Sholat Dzuhur','KBM Sesi 2',
            'Ekstrakurikuler & Olahraga','Kajian Sore Kitab Riyadhus Shalihin','Sholat Maghrib & Isya','Belajar Mandiri Malam','Hafalan Quran Pagi',
            'Latihan Kaligrafi','Kelas Bahasa Inggris','Praktikum Komputer','Diskusi Kelompok Fiqih','Muroja\'ah Al-Quran',
            'Senam Pagi Bersama','Tahsin Al-Quran','Kelas Nahwu Shorof','Workshop Desain Grafis','Latihan Hadroh',
            'Kelas Balaghoh','Sholat Dhuha Berjamaah','Kelas Tafsir Al-Quran','Latihan Pidato Arab','Kelas Hadits',
            'Bimbingan Belajar Sains','Kelas Bahasa Arab Percakapan','Workshop Jurnalistik','Latihan Nasyid','Kelas Aqidah',
            'Pendampingan Santri Baru','Kelas Sirah Nabawiyah','Kelas Matematika','Pelatihan Leadership','Kelas Tarikh Islam',
            'Tahajud Club','Kelas IT & Coding','Latihan Debat','Kelas Fiqih Muamalah','Review Tugas Harian',
            'Kelas Public Speaking','Kajian Kitab Ihya Ulumuddin','Kelas Ushul Fiqih','Workshop Fotografi','Latihan Tilawah',
            'Kelas Bahasa Jepang','Workshop Entrepreneurship','Kelas Akhlaq Tasawuf','Tajwid Intensif','Evaluasi Harian Santri',
        ];
        foreach ($harianTitles as $i => $title) {
            Agenda::create([
                'title' => $title, 'time' => $times[$i % count($times)], 'location' => $locations[$i % count($locations)],
                'teacher' => $teachers[$i % count($teachers)], 'icon' => $icons[$i % count($icons)], 'category' => $cats[$i % 3],
                'body' => "Kegiatan {$title} dilaksanakan secara rutin setiap hari di pesantren.", 'status' => $i >= 48 ? 'Tidak Aktif' : 'Aktif',
                'created_by' => $adminUser->id,
            ]);
        }

        // ── Agenda Mingguan (50 items) ──
        $mingguanTitles = [
            'Rapat Koordinasi','Kajian Kitab Kuning','Tahfidz Quran','Workshop Media','Pengajian Fiqih',
            'Latihan Khitobah','Muhadloroh','Sholat Jumat','Yasin & Tahlil','Senam Pagi',
            'Bersih-Bersih Pesantren','Nonton Bareng Dokumenter','Pengajian Akbar','Latihan Futsal','Kelas Kaligrafi',
            'Diskusi Bahasa Arab','Tahsin Quran Bersama','Workshop Editing Video','Manaqib & Sholawat','Kelas Hadits Bulughul Maram',
            'Latihan Pramuka','Kelas Bahasa Inggris Conversation','Kajian Tasawuf','Turnamen Badminton','Kajian Kitab Aqidatul Awam',
            'Workshop Public Speaking','Pengajian Tafsir Jalalain','Latihan Teater','Kelas Entrepreneurship','Riyaadloh Bersama',
            'Kelas Tajwid Lanjutan','Debat Bahasa Arab','Workshop Kewirausahaan','Latihan Pencak Silat','Kajian Kitab Tanbihul Ghofilin',
            'Kelas Komputer Lanjutan','Musyawarah Asrama','Workshop Desain','Latihan Band Nasyid','Pengajian Kitab Bidayatul Hidayah',
            'Lomba Cerdas Cermat Islam','Kelas Menulis Kreatif','Workshop Podcast','Latihan Memanah','Kajian Malam Jumat',
            'Kelas Falak & Hisab','Bimbingan Konseling Santri','Workshop AI & Teknologi','Latihan Hiking','Evaluasi Mingguan',
        ];
        foreach ($mingguanTitles as $i => $title) {
            \App\Models\Weekly::create([
                'title' => $title, 'day' => $days[$i % 7], 'time' => $times[$i % count($times)], 'location' => $locations[$i % count($locations)],
                'teacher' => $teachers[$i % count($teachers)], 'icon' => $icons[$i % count($icons)], 'category' => $cats[$i % 3],
                'body' => "Kegiatan {$title} dilaksanakan setiap minggu.", 'status' => $i >= 48 ? 'Tidak Aktif' : 'Aktif',
                'created_by' => $adminUser->id,
            ]);
        }

        // ── Agenda Bulanan (50 items) ──
        $now = now(); $y = $now->year; $m = $now->format('m');
        $bulananTitles = [
            'Haul Akbar Pendiri Pesantren','Workshop Media Digital','Lomba Pidato Bahasa Arab','Perlombaan Olahraga','Kajian Kitab Kuning Bulanan',
            'Ujian Semester','Kunjungan Tamu VIP','Seminar Ekonomi Syariah','Pameran Karya Santri','Upacara & Apel Akhir Bulan',
            'Wisuda Hafidz Quran','Pelatihan Guru','Bakti Sosial Desa','Festival Seni Islam','Donor Darah Massal',
            'Rapat Wali Santri','Launching Program Baru','Seminar Pendidikan','Turnamen Antar Asrama','Kunjungan Industri',
            'Pengajian Akbar Bulanan','Workshop Penulisan','Perlombaan Tahfidz','Pelatihan P3K','Muhasabah Akhir Bulan',
            'Pemeriksaan Kesehatan','Pameran Buku Islam','Kelas Motivasi','Latihan Evakuasi','Pertemuan Alumni',
            'Workshop Keuangan Syariah','Lomba Kaligrafi','Pelatihan Digital Marketing','Tadarus Quran Bersama','Seminar Karir',
            'Festival Kuliner Nusantara','Workshop Hidroponik','Pelatihan Kepemimpinan','Lomba Kebersihan Asrama','Kunjungan Museum',
            'Bakti Lingkungan','Workshop Robotika','Pelatihan Jurnalistik','Lomba Menulis Esai','Seminar Kesehatan Mental',
            'Festival Film Pendek','Workshop Fotografi','Pelatihan SAR','Lomba Poster Islami','Evaluasi Bulanan Santri',
        ];
        foreach ($bulananTitles as $i => $title) {
            $day = str_pad(($i % 28) + 1, 2, '0', STR_PAD_LEFT);
            \App\Models\Monthly::create([
                'title' => $title, 'date' => "{$y}-{$m}-{$day}", 'time' => $times[$i % count($times)], 'location' => $locations[$i % count($locations)],
                'teacher' => $teachers[$i % count($teachers)], 'icon' => $icons[$i % count($icons)], 'category' => $cats[$i % 3],
                'body' => "Kegiatan {$title} dilaksanakan setiap bulan.", 'status' => $i >= 48 ? 'Tidak Aktif' : 'Aktif',
                'created_by' => $adminUser->id,
            ]);
        }

        // ── Gallery & Video (50 items) ──
        $galleryTitles = [
            'Suasana Maulid Nabi di Masjid Dalwa','Kajian Kitab Al-Hikam','Kegiatan Ekstrakurikuler Santri','Sejarah Pesantren Dalwa','Panorama Pesantren dari Udara',
            'Latihan Hadroh & Sholawat','Wisuda Santri Angkatan 2025','Kajian Fiqih Kontemporer','Lomba Kaligrafi Nusantara','Kisah Nabi Yusuf - Tausyiah',
            'Pembangunan Masjid Baru','Seminar Bahasa Arab Internasional','Olimpiade Sains Santri','Tabligh Akbar Ramadhan','Kegiatan Pramuka',
            'Workshop Jurnalistik','Reuni Alumni 2020','Ceramah Ustadz Abdul Somad','Pentas Seni Akhir Tahun','Tahsin Al-Quran',
            'Perpustakaan Digital Baru','Debat Bahasa Inggris','Malam Muhasabah','Public Speaking Training','Upacara Hari Santri',
            'Kuliah Umum Prof Al-Azhar','Dapur Umum Ramadhan','Sholat Tarawih Live','Kelas Wirausaha','Pengajian Kitab Kuning',
            'Festival Nasyid Dalwa','Bakti Sosial Komunitas','Lomba Tilawah Quran','Foto Aerial Pesantren','Workshop Coding',
            'Peringatan Isra Miraj','Donor Darah Santri','Kunjungan Menteri Agama','Pameran Sains dan Teknologi','Halal Bi Halal',
            'Training Kepemimpinan','Sunatan Massal','Workshop Desain Grafis','Kompetisi Cerdas Cermat','Festival Budaya Nusantara',
            'Peringatan Hari Guru','Tadarus 30 Juz','Bazar Amal Palestina','Seminar Moderasi Beragama','Dokumentasi Wisuda Akbar',
        ];
        foreach ($galleryTitles as $i => $title) {
            $isVideo = $i % 2 === 1;
            \App\Models\Gallery::create([
                'title' => $title, 'category' => $isVideo ? 'Video' : 'Gambar',
                'description' => "Dokumentasi {$title} di Pesantren Dalwa.",
                'duration' => $isVideo ? [1800,2700,3600,4200,5400,7200][$i % 6] : null,
                'status' => $i >= 48 ? 'Draft' : 'Published',
                'created_by' => $adminUser->id,
                'created_at' => now()->subHours($i * 2), 'updated_at' => now()->subHours($i * 2),
            ]);
        }

        // ── News / Info Terkini (50 items) ──
        $newsTitles = [
            'Kunjungan Duta Besar Arab Saudi','Haul Akbar: Ribuan Santri Padati Masjid','Lomba Pidato Bahasa Arab Nasional','Peresmian Gedung Asrama Baru','Seminar Ekonomi Syariah Internasional',
            'Bakti Sosial di Desa Sekitar','Wisuda Hafidz Quran ke-15','Talkshow: Pesantren di Era Digital','Festival Seni Islami Nusantara','Pelatihan Kewirausahaan Santri',
            'Ceramah Subuh: Keutamaan Ramadhan','Turnamen Futsal Antar Asrama','Akreditasi A dari Kemenag','Webinar Moderasi Beragama','Pameran Karya Ilmiah Santri',
            'Beasiswa Santri Berprestasi','Kajian Tasawuf: Mengenal Diri','Penanaman 1000 Pohon','MOU dengan Univ Islam Madinah','Maulid Nabi di Masjid Utama',
            'Donor Darah Massal Santri','Juara MTQ Nasional','Diskusi Panel: Islam & Sains','Renovasi Asrama Putri','Peluncuran App E-Learning',
            'Tadarus 30 Juz Non-Stop','Kunjungan Menteri Agama','Workshop Penulisan Kitab','Kompetisi Nasyid Suara Dalwa','Bazar Amal Palestina',
            'Peringatan Hari Santri Nasional','Santri Raih Emas Olimpiade','Program Tahfidz Intensif','Kerjasama dengan LIPIA','Pelatihan Digital Marketing',
            'Sunatan Massal Gratis','Workshop Robotika Santri','Festival Film Pendek Islami','Pelatihan Kepemimpinan Muda','Halal Bi Halal Bersama Alumni',
            'Seminar Kesehatan Mental Santri','Training SAR & P3K','Lomba Poster Islami','Festival Kuliner Nusantara','Donasi untuk Bencana Alam',
            'Kunjungan dari Pesantren Lain','Workshop AI & Machine Learning','Peluncuran Majalah Dalwa','Pelatihan Falak & Hisab','Evaluasi Tahunan Pesantren',
        ];
        $speakers = ['Ustaz Hasan Baharun','Dr. Muhammad Al-Fajri','KH. Abdul Rahim','Prof. Quraish Shihab','Habib Ali Zainal','KH. Maimun Zubair','Dr. Adi Hidayat','Ustadz Ahmad','Tim Qari Dalwa','Juri Tim Seni'];
        foreach ($newsTitles as $i => $title) {
            $cat = $cats[$i % 3];
            \App\Models\News::create([
                'title' => $title, 'category' => $cat,
                'body' => "Berita terkini: {$title}. Kegiatan ini berlangsung di Pesantren Dalwa dan diikuti oleh seluruh santri.",
                'speaker' => $cat === 'Video' ? $speakers[$i % count($speakers)] : null,
                'duration' => $cat === 'Video' ? [1800,2700,3600,4500,5400,7200,14400][$i % 7] : null,
                'status' => $i >= 48 ? 'Draft' : 'Published',
                'created_by' => $adminUser->id,
                'created_at' => now()->subHours($i * 2), 'updated_at' => now()->subHours($i * 2),
            ]);
        }

        // ── Announcements / Pengumuman (50 items) ──
        $annTitles = [
            'Perubahan Jadwal Sholat Jumat','Jadwal Ujian Akhir Semester','Pendaftaran Ekskul Semester Genap','Pemeliharaan Jaringan Internet','Rapat Wali Santri',
            'Pengumpulan Berkas Beasiswa','Libur Nasional Isra Miraj','Jadwal Piket Kebersihan Asrama','Pembagian Rapor Semester Ganjil','Peraturan Baru Jam Malam',
            'Vaksinasi Massal Santri','Pengumuman Hasil Seleksi OSES','Pendaftaran Lomba MTQ','Jadwal Latihan Pramuka','Perubahan Menu Makan',
            'Kunjungan Tamu Kemenag','Jadwal Tahfidz Intensif','Perbaikan Saluran Air Asrama','Batas Akhir Pembayaran SPP','Lomba Kebersihan Antar Kamar',
            'Jadwal Imunisasi Santri Baru','Pengumuman Libur Ramadhan','Workshop Kepemimpinan Santri','Peresmian Lab Komputer Baru','Jadwal Remedial UAS',
            'Donor Darah PMI di Pesantren','Festival Bahasa Arab','Peringatan Hari Guru Nasional','Jadwal Olahraga Mingguan','Pengumuman Beasiswa Prestasi',
            'Perubahan Jadwal KBM','Pelatihan P3K untuk Santri','Bazar Amal Akhir Tahun','Jadwal Kunjungan Orang Tua','Pengumuman Santri Teladan',
            'Perbaikan Gedung Asrama C','Jadwal Konsultasi Akademik','Pendaftaran Kelas Bahasa Inggris','Pengumuman Hasil Lomba Kaligrafi','Jadwal Muhasabah Bulanan',
            'Tes Kesehatan Rutin','Peraturan Penggunaan Gadget','Jadwal Pelatihan Jurnalistik','Pengumuman Penerima Beasiswa','Jadwal Kerja Bakti',
            'Sosialisasi Program Tahfidz','Pendaftaran Santri Baru','Perubahan Jadwal Kajian','Pengumuman Wisuda Santri','Jadwal Evaluasi Akhir Tahun',
        ];
        $priorities = ['Urgent','Normal','Info'];
        $audiences = ['Seluruh Santri','Santri Putra','Santri Putri','Kelas 7-9','Kelas 10-12','Wali Santri','Seluruh Warga','Santri Baru','Pengurus Asrama','Ustadz/Ustadzah'];
        $annLocations = ['Masjid Jami','Aula Utama','Kantor Admin','Gedung A','Lapangan','Asrama Putra','Asrama Putri','Lab Komputer','Perpustakaan','Ruang Rapat'];
        foreach ($annTitles as $i => $title) {
            \App\Models\Announcement::create([
                'title' => $title,
                'body' => "Detail pengumuman: {$title}. Seluruh pihak terkait dimohon untuk memperhatikan dan mengikuti arahan yang telah ditetapkan oleh pimpinan pesantren.",
                'excerpt' => "Pengumuman terkait {$title}. Mohon perhatian seluruh santri.",
                'priority' => $priorities[$i % 3],
                'audience' => $audiences[$i % count($audiences)],
                'location' => $annLocations[$i % count($annLocations)],
                'status' => $i >= 45 ? 'Expired' : 'Aktif',
                'created_by' => $adminUser->id,
                'created_at' => now()->subHours($i * 2), 'updated_at' => now()->subHours($i * 2),
            ]);
        }

        // ── TV Devices ──
        $tvNames = [
            'Lobby Utama', 'Ruang Tamu Gedung B', 'Kantin Putra',
            'Aula Besar', 'Masjid Jami', 'Asrama Putra Lt.2',
            'Perpustakaan', 'Gerbang Utama',
        ];
        $tvLocations = [
            'Gedung A, Lantai 1', 'Gedung B, Lantai 1', 'Kantin, Area Putra',
            'Gedung Utama, Lantai 2', 'Masjid Jami, Serambi', 'Asrama Putra, Lantai 2',
            'Gedung C, Lantai 1', 'Gate Utama',
        ];
        $tvStatuses = ['online', 'online', 'online', 'online', 'setup', 'offline', 'offline', 'online'];
        $tvOrientations = ['landscape', 'landscape', 'portrait', 'landscape', 'landscape', 'portrait', 'landscape', 'portrait'];

        foreach ($tvNames as $i => $name) {
            $device = TvDevice::create([
                'name'             => $name,
                'location'         => $tvLocations[$i],
                'orientation'      => $tvOrientations[$i],
                'token'            => str_pad(100001 + $i, 6, '0', STR_PAD_LEFT),
                'status'           => 'setup',
                'ip_address'       => null,
                'last_heartbeat'   => null,
                'firmware_version' => null,
                'warning_message'  => null,
                'registered_by'    => $adminUser->id,
                'created_at'       => now()->subDays($i), 'updated_at' => now()->subDays($i),
            ]);
        }
    }
}
