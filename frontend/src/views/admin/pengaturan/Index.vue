<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ LOGO UNIT ═══ -->
    <div class="settings-card rounded-xl overflow-hidden mt-6">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">image</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Logo Unit</h3>
      </div>
      <div class="px-6 py-5 space-y-6">

        <!-- Unit Selector -->
        <div class="setting-row" v-if="showUnitSelector">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Pilih Unit</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Upload logo untuk unit tertentu</p>
          </div>
          <div class="setting-control">
            <VueMultiselect
              v-model="selectedUnit"
              :options="unitOptions"
              label="name"
              track-by="id"
              placeholder="Pilih unit..."
              :allow-empty="true"
              :searchable="false"
              :show-labels="false"
            />
          </div>
        </div>

        <!-- Auto-selected unit info (for single-unit admin) -->
        <div v-if="!showUnitSelector && selectedUnit" class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Unit</h4>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg" style="background: var(--bg-input); border: 1px solid var(--border)">
              <span class="material-symbols-outlined text-accent text-[18px]">domain</span>
              <span class="text-sm font-bold" style="color: var(--text-heading)">{{ selectedUnit.name }}</span>
            </div>
          </div>
        </div>

        <!-- Upload Area (visible when unit is selected) -->
        <template v-if="selectedUnit">
          <!-- Logo (small icon) -->
          <div class="setting-row">
            <div class="setting-label">
              <h4 class="text-sm font-bold" style="color: var(--text-heading)">Logo</h4>
              <p class="text-xs mt-0.5" style="color: var(--text-muted)">Ikon kecil, tampil di sidebar saat collapsed (disarankan rasio 1:1)</p>
            </div>
            <div class="setting-control">
              <div class="flex items-center gap-4">
                <div class="logo-preview-box flex items-center justify-center rounded-xl overflow-hidden" style="width: 80px; height: 80px">
                  <img :src="logoPreview" alt="Logo" class="w-full h-full object-contain" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="upload-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                    <span class="material-symbols-outlined text-[16px]">upload</span>
                    Upload
                    <input type="file" accept="image/*" class="hidden" @change="e => handleUpload(e, 'logo')" />
                  </label>
                  <button v-if="unitLogos.logo_path" @click="handleDelete('logo')"
                          class="delete-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                    Hapus
                  </button>
                </div>
              </div>
              <p v-if="uploadingLogo" class="text-xs mt-2 text-accent animate-pulse">Mengupload...</p>
            </div>
          </div>

          <!-- Logo Full -->
          <div class="setting-row">
            <div class="setting-label">
              <h4 class="text-sm font-bold" style="color: var(--text-heading)">Logo Full</h4>
              <p class="text-xs mt-0.5" style="color: var(--text-muted)">Logo lengkap, tampil di header TV & sidebar expanded</p>
            </div>
            <div class="setting-control">
              <div class="flex items-center gap-4">
                <div class="logo-preview-box flex items-center justify-center rounded-xl overflow-hidden" style="width: 200px; height: 80px">
                  <img :src="logoFullPreview" alt="Logo Full" class="w-full h-full object-contain" />
                </div>
                <div class="flex flex-col gap-2">
                  <label class="upload-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                    <span class="material-symbols-outlined text-[16px]">upload</span>
                    Upload
                    <input type="file" accept="image/*" class="hidden" @change="e => handleUpload(e, 'logo_full')" />
                  </label>
                  <button v-if="unitLogos.logo_full_path" @click="handleDelete('logo_full')"
                          class="delete-btn flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold cursor-pointer transition-all">
                    <span class="material-symbols-outlined text-[16px]">delete</span>
                    Hapus
                  </button>
                </div>
              </div>
              <p v-if="uploadingLogoFull" class="text-xs mt-2 text-accent animate-pulse">Mengupload...</p>
            </div>
          </div>
        </template>

        <!-- Placeholder when no unit selected -->
        <div v-else class="flex flex-col items-center justify-center py-8 gap-3">
          <span class="material-symbols-outlined text-[48px]" style="color: var(--text-muted)">perm_media</span>
          <p class="text-sm" style="color: var(--text-muted)">Pilih unit terlebih dahulu untuk mengelola logo</p>
        </div>
      </div>
    </div>

    <!-- ═══ TAMPILAN PORTAL ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">web</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Tampilan Portal</h3>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Ucapan Selamat Datang</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Teks berjalan (ticker) yang tampil di bagian bawah halaman Portal Apps</p>
          </div>
          <div class="setting-control">
            <input v-model="welcomeMessage" class="setting-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Contoh: Selamat Datang di Portal Aplikasi UII Dalwa..." />
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ WAKTU & TANGGAL ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[20px]">calendar_month</span>
        <h3 class="font-bold" style="color: var(--text-heading)">Waktu & Tanggal</h3>
      </div>
      <div class="px-6 py-5 space-y-6">
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Koreksi Tanggal Hijriah</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Penyesuaian hari (+/-) jika terjadi perbedaan awal bulan</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3">
              <input v-model="hijriAdjustment" type="range" min="-3" max="3" step="1" class="range-input flex-1" />
              <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg min-w-[80px] justify-center" style="background: var(--bg-input); border: 1px solid var(--border)">
                <span class="text-sm font-bold font-mono" style="color: var(--text-heading)">{{ hijriAdjustment > 0 ? '+' : '' }}{{ hijriAdjustment }}</span>
                <span class="text-xs" style="color: var(--text-muted)">hari</span>
              </div>
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-[10px]" style="color: var(--text-muted)">-3 hari</span>
              <span class="text-[10px]" style="color: var(--text-muted)">Normal (0)</span>
              <span class="text-[10px]" style="color: var(--text-muted)">+3 hari</span>
            </div>
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
        <button @click="saveSettings" class="save-btn flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98]">
          <span class="material-symbols-outlined text-[16px]">save</span>
          Simpan Pengaturan
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '../../../axios'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import { useAuthStore } from '../../../stores/auth'

const authStore = useAuthStore()

// ── Settings ──
const welcomeMessage = ref('Selamat Datang di Portal Aplikasi UII Dalwa. Silakan pilih menu aplikasi di layar.')
const hijriAdjustment = ref(0)

// ── Logo Unit ──
const unitOptions = ref([])
const selectedUnit = ref(null)
const unitLogos = ref({ logo_path: null, logo_full_path: null })
const uploadingLogo = ref(false)
const uploadingLogoFull = ref(false)

// Determine if unit selector should be shown
const showUnitSelector = computed(() => {
  // Superadmin always sees selector
  if (authStore.isSuperadmin) return true
  // Admin/Operator with multiple units sees selector
  const userUnits = authStore.user?.units || []
  return userUnits.length > 1
})

// Logo preview URLs (fallback to default)
const logoPreview = computed(() => {
  if (unitLogos.value.logo_path) {
    return `/storage/${unitLogos.value.logo_path}`
  }
  return '/img/logo.png'
})

const logoFullPreview = computed(() => {
  if (unitLogos.value.logo_full_path) {
    return `/storage/${unitLogos.value.logo_full_path}`
  }
  return '/img/logo-full.png'
})

// Load units for selector
async function loadUnits() {
  try {
    const { data } = await api.get('/my-units')
    unitOptions.value = data

    // Auto-select for single-unit admin
    const userUnits = authStore.user?.units || []
    if (!authStore.isSuperadmin && userUnits.length === 1) {
      selectedUnit.value = data.find(u => u.id === userUnits[0].id) || data[0]
    }
  } catch {
    // ignore
  }
}

// Load logos when unit changes
async function loadUnitLogos() {
  if (!selectedUnit.value) {
    unitLogos.value = { logo_path: null, logo_full_path: null }
    return
  }
  try {
    const { data } = await api.get(`/unit-logos/${selectedUnit.value.id}`)
    unitLogos.value = data
  } catch {
    unitLogos.value = { logo_path: null, logo_full_path: null }
  }
}

watch(selectedUnit, () => {
  loadUnitLogos()
})

// Upload handler
async function handleUpload(event, type) {
  const file = event.target.files[0]
  if (!file || !selectedUnit.value) return

  const loading = type === 'logo' ? uploadingLogo : uploadingLogoFull
  loading.value = true

  const fd = new FormData()
  fd.append('unit_id', selectedUnit.value.id)
  fd.append('type', type)
  fd.append('file', file)

  try {
    await api.post('/settings/upload-logo', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    await loadUnitLogos()
    // Also refresh auth user data so sidebar can update
    await authStore.fetchUser()
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal mengupload logo.'
    alert(msg)
  } finally {
    loading.value = false
    // Reset file input
    event.target.value = ''
  }
}

// Delete handler
async function handleDelete(type) {
  if (!selectedUnit.value) return
  if (!confirm(`Hapus ${type === 'logo' ? 'Logo' : 'Logo Full'} untuk unit ${selectedUnit.value.name}?`)) return

  try {
    await api.post('/settings/delete-logo', {
      unit_id: selectedUnit.value.id,
      type
    })
    await loadUnitLogos()
    await authStore.fetchUser()
  } catch {
    alert('Gagal menghapus logo.')
  }
}

// ── Settings load/save ──
function loadSettings() {
  api.get('/settings').then(res => {
    const data = res.data
    if (data.welcome_message) welcomeMessage.value = data.welcome_message
    if (data.hijri_adjustment !== undefined) hijriAdjustment.value = parseInt(data.hijri_adjustment)
  }).catch(() => {})
}

function saveSettings() {
  api.post('/settings', {
    welcome_message: welcomeMessage.value,
    hijri_adjustment: hijriAdjustment.value
  }).then(() => {
    alert('Pengaturan berhasil disimpan!')
  }).catch(() => {
    alert('Gagal menyimpan pengaturan.')
  })
}

onMounted(() => {
  loadSettings()
  loadUnits()
})
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

.delete-btn {
  background: transparent; color: var(--text-muted); border: 1px solid var(--border);
}
.delete-btn:hover { border-color: #ef4444; color: #ef4444; }

.logo-preview-box {
  background: var(--bg-input); border: 1px solid var(--border);
  padding: 8px;
}

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

/* ═══ VueMultiselect Dark Theme ═══ */
:deep(.multiselect) {
  min-height: 42px;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  background: var(--bg-input);
  color: var(--text-heading);
  font-size: 0.875rem;
}
:deep(.multiselect__tags) {
  background: var(--bg-input);
  border: none;
  border-radius: 0.5rem;
  min-height: 42px;
  padding: 8px 40px 0 12px;
}
:deep(.multiselect__single) {
  background: transparent;
  color: var(--text-heading);
  font-size: 0.875rem;
  margin-bottom: 0;
}
:deep(.multiselect__placeholder) {
  color: var(--text-muted);
  font-size: 0.875rem;
}
:deep(.multiselect__select) {
  height: 42px;
}
:deep(.multiselect__select::before) {
  border-color: var(--text-muted) transparent transparent;
}
:deep(.multiselect__content-wrapper) {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  margin-top: 4px;
}
:deep(.multiselect__option) {
  color: var(--text-body);
  font-size: 0.875rem;
  padding: 10px 12px;
}
:deep(.multiselect__option--highlight) {
  background: var(--color-accent);
  color: var(--text-btn);
}
:deep(.multiselect__option--selected) {
  background: rgba(251, 191, 36, 0.15);
  color: var(--color-accent);
  font-weight: 700;
}
:deep(.multiselect__option--selected.multiselect__option--highlight) {
  background: var(--color-accent);
  color: var(--text-btn);
}
:deep(.multiselect__input) {
  background: transparent;
  color: var(--text-heading);
  font-size: 0.875rem;
}
</style>
