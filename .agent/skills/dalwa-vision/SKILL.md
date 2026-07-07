---
name: dalwa-vision
description: Dalwa Vision TV Interaktif Pesantren — conventions, patterns, and project context for the full-stack application (Vue 3 frontend + Laravel 13 backend).
---

# Dalwa Vision — Project Skill

## Overview
**Dalwa Vision** is an interactive TV dashboard for Pondok Pesantren Darullughah Wadda'wah (Dalwa), Pasuruan. It displays announcements, agendas, news, and media on large screens within the pesantren campus.

**CRITICAL**: This app runs **fully offline** on a local network **without internet**. Never use external CDNs, Google Fonts links, or any `https://` external resources.

**CRITICAL**: Jangan lakukan testing otomatis (browser subagent, curl, dsb). Biarkan user yang test sendiri untuk menghemat kuota token.

## Tech Stack

### Frontend

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | Vue 3 (Composition API, `<script setup>`) | ^3.5 |
| Router | Vue Router | ^4.6 |
| State Management | Pinia | ^3.x |
| HTTP Client | Axios | ^1.x |
| Styling | Tailwind CSS v4 | ^4.2 |
| Build | Vite | ^7.3 |
| Fonts | `@fontsource-variable` (local npm packages) | — |
| Icons | Material Symbols Outlined (local woff2) | — |

### Backend

| Layer | Technology | Version |
|-------|-----------|---------|
| Framework | **Laravel 13** (bukan 12) | ^13.1 |
| Auth | Laravel Sanctum (token-based) | ^4.3 |
| PHP | PHP 8.3 | 8.3.26 |
| Database | MySQL | — |
| DB Name | `dalwavision` | — |

## Project Structure

```
dalwavision/
├── frontend/                      # Vue 3 SPA
│   ├── index.html
│   ├── vite.config.js             # Proxy /api → localhost:8000
│   ├── src/
│   │   ├── main.js                # App bootstrap (Vue + Pinia + Router)
│   │   ├── axios.js               # Axios instance + interceptors
│   │   ├── style.css              # Tailwind v4 theme + design tokens
│   │   ├── App.vue                # Root — page transition wrapper
│   │   ├── router/index.js        # Routes + navigation guard (requiresAuth)
│   │   ├── stores/                # Pinia stores
│   │   │   └── auth.js            # Auth store (login, logout, fetchUser)
│   │   ├── views/                 # Full-page views
│   │   │   ├── Login.vue          # /login — auth form
│   │   │   ├── LandingPage.vue    # /  — 6-card dashboard grid
│   │   │   ├── Admin*.vue         # /administrator/* — admin pages
│   │   │   └── ...                # Public display pages
│   │   ├── layouts/
│   │   │   └── AdminLayout.vue    # Admin sidebar layout wrapper
│   │   └── components/            # Reusable detail overlays
│   └── public/
│       ├── fonts/material-symbols/
│       └── img/
│
└── backend/                       # Laravel 13 API
    ├── app/
    │   ├── Http/Controllers/
    │   │   └── AuthController.php  # login, logout, user
    │   └── Models/
    │       └── User.php            # HasApiTokens, username fillable
    ├── routes/
    │   └── api.php                 # API routes (Sanctum protected)
    ├── database/
    │   ├── migrations/             # users + personal_access_tokens
    │   └── seeders/
    │       └── DatabaseSeeder.php  # Admin user seed
    ├── bootstrap/app.php           # statefulApi() middleware
    └── .env                        # MySQL dalwavision config
```

## Design System

### Color Palette
- **Background**: `#020617` (deepest), `#0f172a` (deep), `#0a192f` (navy)
- **Accent/Gold**: `#fbbf24` (primary accent for this project, used via `text-accent`, `bg-accent`)
- **Blue**: `#3b82f6` (secondary highlights)
- **Cards**: `rgba(15, 23, 42, 0.5)` glass panels with backdrop-blur

### Tailwind v4 Conventions
- Use `bg-linear-to-r` instead of `bg-gradient-to-r`
- Use `text-2xl!` instead of `!text-2xl` for important modifiers
- Theme tokens defined in `style.css` under `@theme { ... }`
- Key custom classes: `.glass-panel`, `.glass-panel-hover`, `.text-glow`

### Typography
- **Display font**: Space Grotesk Variable (`font-display`)
- **Serif font**: Playfair Display Variable (`font-serif`)
- **Icons**: `<span class="material-symbols-outlined">icon_name</span>`

### Fonts (All Local)
Fonts are bundled via npm packages (`@fontsource-variable/*`) and local woff2 files. **Never** link to Google Fonts CDN.

## Patterns & Conventions

### Page Structure
Every view follows this pattern:
```vue
<template>
  <div class="relative w-screen h-screen overflow-hidden font-display text-slate-100">
    <!-- Pattern/Background overlay -->
    <!-- Main wrapper with flex-col, padding, max-width -->
    <header><!-- Back button, logo, date/time pill --></header>
    <main><!-- Content --></main>
    <!-- Detail Overlays -->
    <Transition name="detail">
      <DetailNews v-if="..." :item="..." @close="..." />
    </Transition>
  </div>
</template>
```

### Header/Navbar
Every page header MUST display:
1. **Back button** → `router.push({ name: 'Landing' })`
2. **Logo icon** + "DALWA VISION" title + page subtitle
3. **Date/time pill** showing:
   - **Hijri date** (larger, e.g. "Jumat, 17 Ramadan 1447 H")
   - **Masehi date** (smaller, e.g. "Jumat, 6 Maret 2026")
   - **Time** + "WIB"

```js
// Hijri date pattern (use in every view)
const hijriDate = ref('')
try {
  hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
  }).format(now)
} catch { hijriDate.value = '' }
```

### Category System
Content items have one of three categories with consistent badge styling:
- **Artikel** → gold accent (`bg-accent/20 text-accent border-accent/40`)
- **Gambar** → blue (`bg-blue-500/20 text-blue-400 border-blue-500/40`)
- **Video** → red (`bg-red-500/20 text-red-400 border-red-500/40`)

Each category maps to a **detail overlay component**:
- `Artikel` → `DetailNews.vue`
- `Gambar` → `DetailImage.vue`
- `Video` → `DetailVideo.vue`

### Page Transitions
`App.vue` wraps `<router-view>` with `<Transition name="page" mode="out-in">`. This provides a scale+fade animation automatically on route change.

### Detail Overlays
Use `<Transition name="detail">` with scoped CSS:
```css
.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
```

### Router
- All routes are in `src/router/index.js`
- Each route has `meta: { title: 'Dalwa Vision — ...' }`
- Title is auto-set via `router.beforeEach`
- LandingPage uses a `routeMap` object to map card clicks → route names

### Adding a New Page
1. Create `src/views/NewPage.vue` following the page structure pattern
2. Add route in `src/router/index.js` with import + route entry
3. Add entry to `routeMap` in `LandingPage.vue` if needed
4. Include `hijriDate` + `currentDate` in the header
5. If content has categories, reuse existing `Detail*` components

## Dev Commands
```bash
# Frontend
cd frontend
npm run dev      # Start Vite dev server (localhost:5173)
npm run build    # Production build to dist/
npm run preview  # Preview production build

# Backend
cd backend
php artisan serve          # Start Laravel server (localhost:8000)
php artisan migrate        # Run migrations
php artisan migrate:fresh --seed  # Reset DB + seed
```

## Backend Conventions

### Authentication Flow
- Token-based via **Sanctum** (`HasApiTokens` trait pada `User` model)
- Login: `POST /api/login` → returns `{ user, token }`
- Logout: `POST /api/logout` (requires `Authorization: Bearer <token>`)
- Get user: `GET /api/user` (requires `Authorization: Bearer <token>`)
- Token disimpan di `localStorage` (`auth_token`, `auth_user`)

### API Routes Pattern
- Public routes: langsung di `routes/api.php`
- Protected routes: wrap dalam `Route::middleware('auth:sanctum')->group(...)`
- Semua controller baru di `app/Http/Controllers/`

### Frontend-Backend Integration
- Axios instance di `src/axios.js` — semua API call pakai `import api from '../axios'`
- Request interceptor otomatis attach `Bearer` token
- Response interceptor handle 401 → redirect ke login
- Vite proxy: `/api` → `http://localhost:8000` (dev mode)
- Pinia stores di `src/stores/` — auth state via `useAuthStore()`

### Seed Credentials
- **Username**: `admin`
- **Password**: `password`

## Offline Deployment
After `npm run build`, serve the `dist/` folder on any local HTTP server (nginx, Laragon, etc.) within the pesantren LAN. Backend served via `php artisan serve` or Apache/Nginx. No internet required.

## Important Rules
- **JANGAN** lakukan testing otomatis (browser subagent, curl, script test). Biarkan user yang test sendiri untuk **menghemat kuota token**.
- Laravel yang dipakai adalah **Laravel 13** (versi terbaru Maret 2026), **bukan** Laravel 12.
- Semua API call dari frontend harus melalui Axios instance (`src/axios.js`), jangan pakai `fetch` atau Axios langsung.
- Semua elemen **select/dropdown** di frontend harus pakai **VueMultiselect** (`vue-multiselect`), **JANGAN** pakai `<select>` native HTML. Import: `import VueMultiselect from 'vue-multiselect'` + `import 'vue-multiselect/dist/vue-multiselect.css'`.
- Hanya role **Admin** dan **Operator** yang boleh login ke admin dashboard.
- Untuk rich text editor / body content, gunakan **Quill** via `@vueup/vue-quill` (fully free). Import: `import { QuillEditor } from '@vueup/vue-quill'` + `import '@vueup/vue-quill/dist/vue-quill.snow.css'`. Dark theme via CSS overrides class `quill-dark`. Render output di frontend dengan `v-html`.

