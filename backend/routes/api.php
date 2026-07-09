<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\WeeklyController;
use App\Http\Controllers\MonthlyController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\TvDeviceController;
use App\Http\Controllers\AppLinkController;
use App\Http\Controllers\TvCommandController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\ScreensaverController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ContentSearchController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

// ── Public routes ──
Route::post('/login', [AuthController::class, 'login']);

// Settings (public read)
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{key}', [SettingController::class, 'show']);
Route::get('/unit-logos/{unit}', [SettingController::class, 'getUnitLogos']);

// Proxy (public — for iframe embed of external sites)
Route::get('/proxy/check', [ProxyController::class, 'check']);
Route::get('/proxy', [ProxyController::class, 'fetch']);

// Agenda (public read — for TV display)
Route::get('/agendas', [AgendaController::class, 'index']);
Route::get('/agendas/{agenda}', [AgendaController::class, 'show']);

// Weekly (public read — for TV display)
Route::get('/weeklies', [WeeklyController::class, 'index']);
Route::get('/weeklies/{weekly}', [WeeklyController::class, 'show']);

// Monthly (public read — for TV display)
Route::get('/monthlies', [MonthlyController::class, 'index']);
Route::get('/monthlies/{monthly}', [MonthlyController::class, 'show']);

// Gallery (public read — for TV display)
Route::get('/galleries', [GalleryController::class, 'index']);
Route::get('/galleries/stats', [GalleryController::class, 'stats']);
Route::get('/galleries/{gallery}', [GalleryController::class, 'show']);

// News / Info Terkini (public read)
Route::get('/news/stats', [NewsController::class, 'stats']);
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/{news}', [NewsController::class, 'show']);

// Announcements / Pengumuman (public read)
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/stats', [AnnouncementController::class, 'stats']);
Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);

// App Links / Portal (public read)
Route::get('/app-links/stats', [AppLinkController::class, 'stats']);
Route::get('/app-links', [AppLinkController::class, 'index']);
Route::get('/app-links/{appLink}', [AppLinkController::class, 'show']);

// Unified content search (for push konten dropdown)
Route::get('/content-search', [ContentSearchController::class, 'search']);

// TV Device (public — connect + heartbeat + disconnect)
Route::post('/tv/connect', [TvDeviceController::class, 'connect']);
Route::post('/tv/heartbeat', [TvDeviceController::class, 'heartbeat']);
Route::post('/tv/disconnect', [TvDeviceController::class, 'disconnect']);

// Active banner (public — for TV display on load)
Route::get('/tv-commands/active-banner', [TvCommandController::class, 'activeBanner']);

// Screensaver (public — for TV display)
Route::get('/screensaver/tv', [ScreensaverController::class, 'forTv']);

// ── Protected routes (requires Sanctum token) ──
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/my-units', [UnitController::class, 'myUnits']);
});

Route::middleware(['auth:sanctum', \App\Http\Middleware\UnitScope::class])->group(function () {
    // Unit management
    Route::apiResource('units', UnitController::class);
    Route::post('/units/{unit}/assign-users', [UnitController::class, 'assignUsers']);

    // Settings (protected update)
    Route::post('/settings', [SettingController::class, 'update']);
    Route::post('/settings/upload-logo', [SettingController::class, 'uploadLogo']);
    Route::post('/settings/delete-logo', [SettingController::class, 'deleteLogo']);

    // Dashboard stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Manajemen Role
    Route::apiResource('roles', RoleController::class);

    // Manajemen User
    Route::apiResource('users', UserController::class);

    // Agenda (protected CRUD)
    Route::post('/agendas', [AgendaController::class, 'store']);
    Route::put('/agendas/{agenda}', [AgendaController::class, 'update']);
    Route::delete('/agendas/{agenda}', [AgendaController::class, 'destroy']);
    Route::post('/upload-editor', [AgendaController::class, 'uploadEditorFile']);
    Route::post('/delete-editor-file', [AgendaController::class, 'deleteEditorFile']);

    // Weekly (protected CRUD)
    Route::post('/weeklies', [WeeklyController::class, 'store']);
    Route::put('/weeklies/{weekly}', [WeeklyController::class, 'update']);
    Route::delete('/weeklies/{weekly}', [WeeklyController::class, 'destroy']);

    // Monthly (protected CRUD)
    Route::post('/monthlies', [MonthlyController::class, 'store']);
    Route::put('/monthlies/{monthly}', [MonthlyController::class, 'update']);
    Route::delete('/monthlies/{monthly}', [MonthlyController::class, 'destroy']);

    // Gallery (protected CRUD)
    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::put('/galleries/{gallery}', [GalleryController::class, 'update']);
    Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy']);

    // News / Info Terkini (protected CRUD)
    Route::post('/news', [NewsController::class, 'store']);
    Route::put('/news/{news}', [NewsController::class, 'update']);
    Route::delete('/news/{news}', [NewsController::class, 'destroy']);

    // Announcements / Pengumuman (protected CRUD)
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);

    // App Links / Portal (protected CRUD)
    Route::post('/app-links', [AppLinkController::class, 'store']);
    Route::put('/app-links/{appLink}', [AppLinkController::class, 'update']);
    Route::delete('/app-links/{appLink}', [AppLinkController::class, 'destroy']);

    // TV Devices (protected CRUD)
    Route::get('/tv-devices', [TvDeviceController::class, 'index']);
    Route::get('/tv-devices/stats', [TvDeviceController::class, 'stats']);
    Route::post('/tv-devices', [TvDeviceController::class, 'store']);
    Route::put('/tv-devices/{tvDevice}', [TvDeviceController::class, 'update']);
    Route::delete('/tv-devices/{tvDevice}', [TvDeviceController::class, 'destroy']);
    Route::post('/tv-devices/{tvDevice}/regenerate-token', [TvDeviceController::class, 'regenerateToken']);
    Route::post('/tv-devices/{tvDevice}/force-disconnect', [TvDeviceController::class, 'forceDisconnect']);
    Route::get('/tv-devices/{tvDevice}/current-page', [TvDeviceController::class, 'currentPage']);

    // TV Commands (Control Center)
    Route::post('/tv-commands/push', [TvCommandController::class, 'push']);
    Route::post('/tv-commands/broadcast', [TvCommandController::class, 'broadcast']);
    Route::post('/tv-commands/reload', [TvCommandController::class, 'reload']);
    Route::post('/tv-commands/home', [TvCommandController::class, 'home']);
    Route::post('/tv-commands/banner', [TvCommandController::class, 'banner']);
    Route::post('/tv-commands/targeted-banner', [TvCommandController::class, 'targetedBanner']);
    Route::get('/tv-commands/targeted-banners', [TvCommandController::class, 'targetedBanners']);
    Route::get('/tv-commands/all-active-banners', [TvCommandController::class, 'allActiveBanners']);
    Route::put('/tv-commands/active-banners/{id}', [TvCommandController::class, 'updateBanner']);
    Route::delete('/tv-commands/active-banners/{id}', [TvCommandController::class, 'deleteBanner']);
    Route::get('/tv-commands/logs', [TvCommandController::class, 'logs']);
    Route::post('/tv-commands/screensaver', [TvCommandController::class, 'screensaver']);

    // Screensavers (protected CRUD)
    Route::get('/screensavers/stats', [ScreensaverController::class, 'stats']);
    Route::get('/screensavers', [ScreensaverController::class, 'index']);
    Route::get('/screensavers/{screensaver}', [ScreensaverController::class, 'show']);
    Route::post('/screensavers', [ScreensaverController::class, 'store']);
    Route::post('/screensavers/{screensaver}', [ScreensaverController::class, 'update']);
    Route::delete('/screensavers/{screensaver}', [ScreensaverController::class, 'destroy']);
    Route::post('/screensavers/{screensaver}/images', [ScreensaverController::class, 'addImages']);
    Route::delete('/screensavers/{screensaver}/images/{image}', [ScreensaverController::class, 'removeImage']);
});
