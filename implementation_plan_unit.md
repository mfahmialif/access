# Fitur Unit — Multi-Unit Content Isolation (Final Plan)

Menambahkan konsep **Unit** (satuan organisasi) sebagai entitas inti. Semua konten dan data diisolasi berdasarkan unit.

## Keputusan Final

| Keputusan | Jawaban |
|-----------|---------|
| Relasi User ↔ Unit | **Many-to-many** (pivot `unit_user`), 1 user bisa di banyak unit |
| Role baru | **Superadmin** — akses semua unit tanpa batasan |
| Unit defaults | Pesantren Putra, Pesantren Putri, MTs, MA, Maktabah, Taman, Umum (default) |
| Seed method | **Migration** (bukan seeder), tinggal `php artisan migrate` di production |
| TV Device per unit | **Ya**, TV punya `unit_id` |
| Public page filter | **Hanya konten unit TV itu** (tidak ada opsi semua unit) |
| Navbar unit switcher | Superadmin: dropdown + "Semua Unit"; Multi-unit: dropdown; Single-unit: tanpa dropdown |

---

## Proposed Changes

### Phase 1: Backend — Database

#### [NEW] `2026_07_09_100000_create_units_table.php`

Migration yang membuat tabel `units` **dan langsung insert data default**:

```php
Schema::create('units', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('logo_path')->nullable();
    $table->enum('status', ['Active', 'Inactive'])->default('Active');
    $table->timestamps();
});

// Insert default units langsung di migration
DB::table('units')->insert([
    ['name' => 'Umum',              'slug' => 'umum',              ...],
    ['name' => 'Pesantren Putra',   'slug' => 'pesantren-putra',   ...],
    ['name' => 'Pesantren Putri',   'slug' => 'pesantren-putri',   ...],
    ['name' => 'Madrasah Tsanawiyah','slug' => 'madrasah-tsanawiyah',...],
    ['name' => 'Madrasah Aliyah',   'slug' => 'madrasah-aliyah',   ...],
    ['name' => 'Maktabah',          'slug' => 'maktabah',          ...],
    ['name' => 'Taman',             'slug' => 'taman',             ...],
]);

// Insert role Superadmin juga di migration ini
DB::table('roles')->insertOrIgnore([
    'name' => 'Superadmin',
    'description' => 'Full access to all units and system features.',
    'status' => 'Active',
    'created_at' => now(), 'updated_at' => now(),
]);
```

#### [NEW] `2026_07_09_100001_create_unit_user_table.php`

```php
Schema::create('unit_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['unit_id', 'user_id']);
});
```

#### [NEW] `2026_07_09_100002_add_unit_id_to_content_tables.php`

Tambah kolom `unit_id` (nullable, FK → units) ke **10 tabel**:
- `agendas`, `weeklies`, `monthlies`, `galleries`, `news`, `announcements`
- `app_links`, `tv_devices`, `screensavers`, `active_banners`

---

### Phase 2: Backend — Models

#### [NEW] `Unit.php`
```
- fillable: name, slug, description, logo_path, status
- Relasi: users() → belongsToMany(User)
- Relasi: agendas(), weeklies(), monthlies(), galleries(), news(), announcements(), appLinks(), tvDevices()
```

#### [MODIFY] `User.php`
```
- Tambah relasi: units() → belongsToMany(Unit)
- Tambah method: isSuperadmin() → $this->role?->name === 'Superadmin'
- Tambah method: hasUnitAccess($unitId) → superadmin selalu true, lainnya cek pivot
```

#### [MODIFY] 8 content models
Semua tambah `unit_id` ke fillable + relasi `unit()` → `belongsTo(Unit)`:
- `Agenda`, `Weekly`, `Monthly`, `Gallery`, `News`, `Announcement`, `AppLink`, `TvDevice`

---

### Phase 3: Backend — Middleware & Routes

#### [NEW] `UnitScope.php` middleware

```
1. Baca header X-Unit-Id dari request
2. Jika user Superadmin + unit_id = "all" → tidak filter (semua unit)
3. Jika user punya akses ke unit → set request unit_id
4. Jika tidak ada header → ambil unit pertama dari user
5. Jika user tidak punya unit → 403
```

#### [MODIFY] `api.php`

Tambah routes:
```php
// Unit management (protected)
Route::apiResource('units', UnitController::class);
Route::get('/my-units', [UnitController::class, 'myUnits']);
Route::post('/units/{unit}/assign-users', [UnitController::class, 'assignUsers']);
```

Public routes: tambah support `?unit_id=` query parameter untuk filter konten per unit (digunakan TV display).

---

### Phase 4: Backend — Controllers

#### [NEW] `UnitController.php`

| Method | Akses | Deskripsi |
|--------|-------|-----------|
| `index()` | All auth | Superadmin: semua unit. Lainnya: unit terdaftar |
| `store()` | Superadmin | Buat unit baru |
| `show()` | All auth | Detail unit (cek akses) |
| `update()` | Superadmin | Edit unit |
| `destroy()` | Superadmin | Hapus unit |
| `myUnits()` | All auth | Unit yang bisa diakses user login |
| `assignUsers()` | Superadmin | Assign/sync users ke unit |

#### [MODIFY] `AuthController.php`
- Login: load `units` di response, tambah role Superadmin ke whitelist login
- `user()`: load `units` relationship

#### [MODIFY] `UserController.php`
- Store/update: terima `unit_ids[]` array, sync ke pivot
- Index/show: eager load `units:id,name`

#### [MODIFY] 8 content controllers — Unit filtering pattern:

```php
// index() — filter berdasarkan unit
$unitId = $request->header('X-Unit-Id');
if ($unitId && $unitId !== 'all') {
    $query->where('unit_id', $unitId);
}

// store() — otomatis set unit_id
$data['unit_id'] = $request->header('X-Unit-Id');
```

Controllers yang dimodifikasi:
1. `AgendaController` — index, store
2. `WeeklyController` — index, store
3. `MonthlyController` — index, store
4. `GalleryController` — index, store, stats
5. `NewsController` — index, store, stats
6. `AnnouncementController` — index, store, stats
7. `AppLinkController` — index, store, stats
8. `TvDeviceController` — index, store, stats

#### [MODIFY] `DashboardController.php`
- Stats dihitung per unit aktif (baca dari `X-Unit-Id` header)

#### [MODIFY] `ContentSearchController.php`
- Search difilter per unit

#### [MODIFY] `DatabaseSeeder.php`
- User admin → role Superadmin + assign ke semua unit
- User operator → assign ke unit Pesantren Putra
- Semua seeded content → assign ke unit Umum (id=1)

---

### Phase 5: Frontend — Core

#### [NEW] `stores/unit.js`
```js
// State
units         // Array unit yang bisa diakses
activeUnitId  // ID unit aktif (dari localStorage)

// Getters
activeUnit    // Object unit aktif
isSuperadmin  // dari auth store

// Actions
fetchMyUnits()   // GET /my-units
switchUnit(id)   // Ganti unit aktif, simpan localStorage
```

#### [MODIFY] `stores/auth.js`
- Tambah computed `isSuperadmin` — cek `user.role.name === 'Superadmin'`
- Setelah login: otomatis fetch units

#### [MODIFY] `axios.js`
- Request interceptor: otomatis attach header `X-Unit-Id` dari localStorage

#### [MODIFY] 6 content stores
Semua sudah otomatis via axios interceptor (X-Unit-Id header). Tidak perlu modifikasi signifikan kecuali reload data saat unit diganti.

---

### Phase 6: Frontend — Admin UI

#### [MODIFY] `AdminNavbar.vue` — Unit Switcher

Logika tampilan di navbar:

```
if (isSuperadmin):
    → Dropdown: "Semua Unit" + list semua unit
elif (units.length > 1):
    → Dropdown: list unit terdaftar
elif (units.length === 1):
    → Badge statis: nama unit (tanpa dropdown)
```

Styling: badge/dropdown gold accent, sesuai design system existing.

#### [MODIFY] `AdminSidebar.vue`
- Tambah menu **"Manajemen Unit"** di section admin (hanya tampil jika role Superadmin)
- Icon: `domain`

#### [NEW] `views/admin/unit/Index.vue`
- Halaman daftar unit (Superadmin only)
- Tabel: nama, slug, jumlah user, jumlah konten, status
- Tombol: tambah, edit, hapus

#### [NEW] `views/admin/unit/Form.vue`
- Form create/edit unit
- Fields: nama, slug (auto-generate), deskripsi, logo upload, status
- Tab "Users": multi-select assign users ke unit

#### [MODIFY] `views/admin/user/User.vue`
- Form create/edit: tambah field multi-select "Unit" (VueMultiselect)
- Tabel: tampilkan kolom unit (badges)

---

### Phase 7: Frontend — Router

#### [MODIFY] `router/index.js`

Tambah:
```js
// Admin Unit Management
{ path: 'manajemen-unit', name: 'AdminManajemenUnit', ... }
{ path: 'manajemen-unit/create', name: 'AdminManajemenUnitCreate', ... }
{ path: 'manajemen-unit/:id/edit', name: 'AdminManajemenUnitEdit', ... }
```

Navigation guard: tambah `'Superadmin'` ke whitelist role yang boleh akses admin.

---

### Phase 8: Frontend — Public Pages (TV Display)

#### [MODIFY] Public views — Filter konten berdasarkan unit TV

Saat TV connect, response dari API sudah include `unit_id` dari TV device. Data ini disimpan di localStorage (`tv_unit_id`). Semua API call dari public pages mengirim `?unit_id=xxx`.

Files yang dimodifikasi:
- `LandingPage.vue`, `InfoTerkini.vue`, `Pengumuman.vue`, `GalleryVideo.vue`
- `agenda/harian.vue`, `agenda/mingguan.vue`, `agenda/bulanan.vue`
- `DetailPage.vue`, `Apps.vue`

---

## Execution Summary

| Phase | Files Baru | Files Dimodifikasi |
|-------|-----------|-------------------|
| 1. Database | 3 migrations | — |
| 2. Models | 1 (Unit) | 9 (User + 8 content) |
| 3. Middleware & Routes | 1 (UnitScope) | 1 (api.php) |
| 4. Controllers | 1 (UnitController) | 12 |
| 5. Frontend Core | 1 (unit store) | 8 (auth + axios + 6 stores) |
| 6. Frontend Admin UI | 2 (Unit Index/Form) | 3 (Navbar, Sidebar, User) |
| 7. Frontend Router | — | 1 (router/index.js) |
| 8. Frontend Public | — | 9 (TV display pages) |
| **Total** | **9** | **~43** |

---

## Verification Plan

### Manual Verification (User test sendiri)
1. `php artisan migrate` — pastikan migration + insert data default berjalan
2. `php artisan migrate:fresh --seed` — pastikan seeder compatible
3. Login Superadmin → dropdown semua unit, CRUD unit, switch unit
4. Login Admin (multi-unit) → dropdown unit terdaftar
5. Login Admin (single-unit) → badge statis tanpa dropdown
6. Buat konten → otomatis ter-assign ke unit aktif
7. TV display → hanya tampil konten dari unit TV tersebut
