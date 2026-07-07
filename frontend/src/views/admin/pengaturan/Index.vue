<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ GENERAL SETTINGS ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">tune</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Pengaturan Umum</h3>
      </div>
      <div class="px-6 py-5 space-y-6">

        <!-- Nama Sistem -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Nama Sistem</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Nama yang ditampilkan di header dan TV</p>
          </div>
          <div class="setting-control">
            <input v-model="systemName" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
          </div>
        </div>

        <!-- Logo -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Logo Sistem</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Logo utama yang ditampilkan di semua halaman</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-4">
              <div class="w-16 h-16 rounded-xl flex items-center justify-center border-2 border-dashed border-accent/40" style="background: var(--bg-input)">
                <span class="material-symbols-outlined text-accent text-3xl">mosque</span>
              </div>
              <div class="flex flex-col gap-2">
                <button class="upload-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                  <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                  Upload Logo
                </button>
                <span class="text-[10px]" style="color: var(--text-muted)">PNG, SVG · Max 2MB · Min 128×128px</span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ═══ TV SETTINGS ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">tv</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Pengaturan TV</h3>
      </div>
      <div class="px-6 py-5 space-y-6">

        <!-- Timeout Idle TV -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Timeout Idle TV</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Waktu idle sebelum TV kembali ke halaman utama</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3">
              <input v-model="idleTimeout" type="range" min="30" max="600" step="30" class="range-input flex-1" />
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg min-w-[80px] justify-center" style="background: var(--bg-input); border: 1px solid var(--border)">
                <span class="text-sm font-bold font-mono" style="color: var(--text-heading)">{{ idleTimeout }}</span>
                <span class="text-xs" style="color: var(--text-muted)">detik</span>
              </div>
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-[10px]" style="color: var(--text-muted)">30 detik</span>
              <span class="text-[10px]" style="color: var(--text-muted)">10 menit</span>
            </div>
          </div>
        </div>

        <!-- Durasi Default Banner -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Durasi Default Banner</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Lama banner ditampilkan sebelum otomatis hilang</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3">
              <input v-model="bannerDuration" type="range" min="5" max="120" step="5" class="range-input flex-1" />
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg min-w-[80px] justify-center" style="background: var(--bg-input); border: 1px solid var(--border)">
                <span class="text-sm font-bold font-mono" style="color: var(--text-heading)">{{ bannerDuration }}</span>
                <span class="text-xs" style="color: var(--text-muted)">detik</span>
              </div>
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-[10px]" style="color: var(--text-muted)">5 detik</span>
              <span class="text-[10px]" style="color: var(--text-muted)">2 menit</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ═══ THEME SETTINGS ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">palette</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Warna Tema</h3>
      </div>
      <div class="px-6 py-5 space-y-6">

        <!-- Accent Color -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Warna Aksen</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Warna utama untuk tombol, badge, dan highlight</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3 flex-wrap">
              <button v-for="color in accentColors" :key="color.value"
                      @click="selectedAccent = color.value"
                      :class="['w-10 h-10 rounded-xl cursor-pointer transition-all border-2', selectedAccent === color.value ? 'border-white scale-110 shadow-lg' : 'border-transparent hover:scale-105']"
                      :style="{ background: color.value }"
                      :title="color.name">
              </button>
              <div class="flex items-center gap-2 ml-2">
                <input v-model="customAccent" type="color" class="w-10 h-10 rounded-xl cursor-pointer border-0 bg-transparent" />
                <span class="text-xs font-mono" style="color: var(--text-muted)">{{ customAccent }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Theme Mode -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Mode Tema</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Mode tampilan untuk panel admin</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3">
              <button v-for="mode in themeModes" :key="mode.value"
                      @click="selectedMode = mode.value"
                      :class="['theme-mode-btn flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all', selectedMode === mode.value ? 'active' : '']">
                <span class="material-symbols-outlined text-[18px]">{{ mode.icon }}</span>
                {{ mode.name }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ═══ REALTIME CONFIG ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">sync</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Konfigurasi Realtime</h3>
      </div>
      <div class="px-6 py-5 space-y-6">

        <!-- WebSocket -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">WebSocket Server</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">URL server WebSocket untuk komunikasi realtime</p>
          </div>
          <div class="setting-control">
            <input v-model="wsUrl" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-accent" />
          </div>
        </div>

        <!-- Auto Reconnect -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Auto Reconnect</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Otomatis reconnect saat koneksi terputus</p>
          </div>
          <div class="setting-control flex items-center gap-3">
            <label class="toggle-switch">
              <input type="checkbox" v-model="autoReconnect" />
              <span class="toggle-slider"></span>
            </label>
            <span class="text-sm font-medium" :class="autoReconnect ? 'text-green-400' : ''" :style="!autoReconnect ? 'color: var(--text-muted)' : ''">
              {{ autoReconnect ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>

        <!-- Heartbeat Interval -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Heartbeat Interval</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Interval pengecekan koneksi TV (ping/pong)</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3">
              <input v-model="heartbeat" type="range" min="5" max="60" step="5" class="range-input flex-1" />
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg min-w-[80px] justify-center" style="background: var(--bg-input); border: 1px solid var(--border)">
                <span class="text-sm font-bold font-mono" style="color: var(--text-heading)">{{ heartbeat }}</span>
                <span class="text-xs" style="color: var(--text-muted)">detik</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Broadcast Channel -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Broadcast Channel</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Nama channel untuk broadcast ke semua TV</p>
          </div>
          <div class="setting-control">
            <input v-model="broadcastChannel" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-accent" />
          </div>
        </div>

      </div>
    </div>

    <!-- ═══ SAVE BAR ═══ -->
    <div class="save-bar rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 sticky bottom-0 z-10">
      <p class="text-sm" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-accent text-[16px] align-text-bottom mr-1">info</span>
        Perubahan akan diterapkan ke semua TV setelah disimpan
      </p>
      <div class="flex items-center gap-3">
        <button class="reset-btn flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all">
          <span class="material-symbols-outlined text-[16px]">restart_alt</span>
          Reset
        </button>
        <button class="save-btn flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98]">
          <span class="material-symbols-outlined text-[16px]">save</span>
          Simpan Pengaturan
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'

const systemName = ref('Dalwa Vision')
const idleTimeout = ref(120)
const bannerDuration = ref(30)

const accentColors = [
  { name: 'Gold', value: '#fbbf24' },
  { name: 'Blue', value: '#3b82f6' },
  { name: 'Green', value: '#22c55e' },
  { name: 'Purple', value: '#a855f7' },
  { name: 'Rose', value: '#f43f5e' },
  { name: 'Cyan', value: '#06b6d4' }
]
const selectedAccent = ref('#fbbf24')
const customAccent = ref('#fbbf24')

const themeModes = [
  { name: 'Dark', value: 'dark', icon: 'dark_mode' },
  { name: 'Light', value: 'light', icon: 'light_mode' },
  { name: 'Auto', value: 'auto', icon: 'brightness_auto' }
]
const selectedMode = ref('dark')

const wsUrl = ref('wss://dalwavision.id/ws')
const autoReconnect = ref(true)
const heartbeat = ref(15)
const broadcastChannel = ref('dalwa-vision-broadcast')
</script>

<style scoped>
.settings-card { background: var(--bg-card); border: 1px solid var(--border); }
.card-header { border-bottom: 1px solid var(--border); }

.setting-row { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px) {
  .setting-row { flex-direction: row; align-items: flex-start; }
  .setting-label { width: 280px; flex-shrink: 0; padding-top: 0.5rem; }
  .setting-control { flex: 1; }
}

.setting-input {
  background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading);
}
.setting-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

.upload-btn {
  background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border);
}
.upload-btn:hover { border-color: var(--color-accent); color: var(--color-accent); }

/* Range slider */
.range-input {
  -webkit-appearance: none; appearance: none;
  height: 6px; border-radius: 999px; outline: none;
  background: linear-gradient(to right, var(--color-accent) 0%, var(--color-accent) var(--range-progress, 50%), var(--bg-input) var(--range-progress, 50%), var(--bg-input) 100%);
  border: 1px solid var(--border);
}
.range-input::-webkit-slider-thumb {
  -webkit-appearance: none; appearance: none;
  width: 20px; height: 20px; border-radius: 50%;
  background: var(--color-accent); cursor: pointer;
  box-shadow: 0 0 10px rgba(251, 191, 36, 0.4);
  border: 2px solid white;
}

/* Theme mode buttons */
.theme-mode-btn {
  background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border);
}
.theme-mode-btn:hover { border-color: rgba(251, 191, 36, 0.3); color: var(--text-heading); }
.theme-mode-btn.active {
  background: var(--color-accent); color: var(--text-btn);
  border-color: var(--color-accent); box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
}

/* Toggle switch */
.toggle-switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
  position: absolute; cursor: pointer; inset: 0;
  background: var(--bg-input); border: 1px solid var(--border);
  border-radius: 999px; transition: 0.3s;
}
.toggle-slider::before {
  content: ''; position: absolute; height: 20px; width: 20px;
  left: 2px; bottom: 2px; background: var(--text-muted);
  border-radius: 50%; transition: 0.3s;
}
.toggle-switch input:checked + .toggle-slider {
  background: rgba(34, 197, 94, 0.2); border-color: rgba(34, 197, 94, 0.4);
}
.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(22px); background: #22c55e;
  box-shadow: 0 0 8px rgba(34, 197, 94, 0.5);
}

/* Save bar */
.save-bar {
  background: var(--bg-card); border: 1px solid var(--border);
  backdrop-filter: blur(12px);
}
.save-btn {
  background: var(--color-accent); color: var(--text-btn);
  box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);
}
.save-btn:hover { box-shadow: 0 0 25px rgba(251, 191, 36, 0.5); }
.reset-btn {
  background: var(--bg-input); color: var(--text-heading);
  border: 1px solid var(--border);
}
.reset-btn:hover { border-color: #ef4444; color: #ef4444; }
</style>
