<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ STATS ROW ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
      <div v-for="stat in statsCards" :key="stat.label" class="stat-card rounded-xl p-4 flex items-center gap-4 border border-transparent">
        <div class="p-3 rounded-lg" :class="stat.iconBg">
          <span class="material-symbols-outlined text-[24px]" :class="stat.iconColor">{{ stat.icon }}</span>
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ stat.label }}</p>
          <p class="text-2xl font-bold" style="color: var(--text-heading)">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- ═══ TOOLBAR ═══ -->
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div class="flex items-center gap-3 flex-wrap">
        <!-- Search -->
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[18px]" style="color: var(--text-muted)">search</span>
          <input v-model="searchQuery" @input="onSearch" type="text" placeholder="Cari perangkat..."
                 class="pl-10 pr-4 py-2 rounded-lg text-sm outline-none" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)" />
        </div>
        <!-- Status Filter -->
        <div class="flex gap-1 p-1 rounded-lg" style="background: var(--bg-input); border: 1px solid var(--border)">
          <button v-for="tab in statusTabs" :key="tab.value" @click="filterStatus = tab.value; loadDevices()"
                  :class="filterStatus === tab.value ? 'bg-accent text-[#0a192f]' : ''"
                  class="px-3 py-1.5 rounded-md text-xs font-bold uppercase tracking-wider cursor-pointer transition-all" :style="filterStatus !== tab.value ? 'color: var(--text-muted)' : ''">
            {{ tab.label }}
          </button>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <!-- Refresh Button -->
        <button @click="loadDevices" :disabled="loading"
                class="flex items-center gap-2 font-bold text-sm px-4 py-2.5 rounded-lg transition-all cursor-pointer"
                style="background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border)"
                :class="{ 'opacity-50': loading }">
          <span class="material-symbols-outlined text-[20px]" :class="{ 'animate-spin': loading }">refresh</span>
          Refresh
        </button>
        <!-- Add Button -->
        <button @click="openCreateDialog" class="flex items-center gap-2 bg-accent text-[#0a192f] font-bold text-sm px-5 py-2.5 rounded-lg hover:brightness-110 transition-all cursor-pointer">
          <span class="material-symbols-outlined text-[20px]">add</span>
          Tambah Perangkat
        </button>
      </div>
    </div>

    <!-- ═══ DEVICE GRID ═══ -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <span class="material-symbols-outlined text-5xl text-accent animate-spin">progress_activity</span>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
      <!-- Device Cards -->
      <div v-for="device in store.devices" :key="device.id"
           class="device-card rounded-xl p-6 flex flex-col justify-between min-h-[280px] relative group overflow-hidden"
           :class="device.status === 'setup' ? 'setup-card' : ''">

        <div>
          <!-- Status badge + actions -->
          <div class="flex items-center gap-2 mb-4">
            <span class="flex h-2.5 w-2.5 rounded-full" :class="statusDot(device.status)"></span>
            <span class="text-xs font-bold uppercase tracking-wider" :class="statusTextClass(device.status)">{{ statusLabel(device.status) }}</span>
            <span v-if="device.orientation === 'portrait'" class="text-xs px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/30 font-bold">Portrait</span>
            <span v-else class="text-xs px-2 py-0.5 rounded-full bg-accent/10 text-accent border border-accent/30 font-bold">Landscape</span>
            <div class="ml-auto flex items-center gap-1">
              <button @click.stop="openEditDialog(device)" class="action-btn p-1.5 rounded-lg transition-colors" title="Edit">
                <span class="material-symbols-outlined text-[18px]">edit</span>
              </button>
              <button @click.stop="confirmDelete(device)" class="action-btn p-1.5 rounded-lg transition-colors hover:text-red-400" title="Hapus">
                <span class="material-symbols-outlined text-[18px]">delete</span>
              </button>
            </div>
          </div>

          <!-- Name & Location -->
          <h3 class="text-xl font-bold mb-1" style="color: var(--text-heading)">{{ device.name }}</h3>
          <div class="flex items-center gap-2 text-sm mb-5" style="color: var(--text-muted)">
            <span class="material-symbols-outlined text-[18px]">location_on</span>
            {{ device.location || '-' }}
          </div>

          <!-- Token display for setup mode -->
          <div v-if="device.status === 'setup'" class="token-box rounded-lg p-4 mb-4 text-center">
            <p class="text-xs mb-2" style="color: var(--text-muted)">Masukkan token ini di layar TV</p>
            <div class="flex items-center justify-center gap-3">
              <span class="text-3xl font-mono font-bold text-accent tracking-widest">{{ device.token }}</span>
              <button @click="copyToken(device.token)" class="action-btn p-1.5 rounded transition-colors" title="Copy">
                <span class="material-symbols-outlined text-[20px]">content_copy</span>
              </button>
            </div>
          </div>

          <!-- Warning -->
          <div v-if="device.warning_message" class="warning-box rounded-lg p-3 mb-2">
            <p class="text-xs text-yellow-200 flex gap-2 items-start">
              <span class="material-symbols-outlined text-[16px] shrink-0">info</span>
              {{ device.warning_message }}
            </p>
          </div>

          <!-- IP & Sync info -->
          <div v-if="device.ip_address" class="space-y-0" :class="{ 'opacity-60': device.status === 'offline' }">
            <div class="flex justify-between text-sm py-2 info-row">
              <span style="color: var(--text-muted)">IP Address</span>
              <span class="font-mono" style="color: var(--text-heading)">{{ device.ip_address }}</span>
            </div>
            <div class="flex justify-between text-sm py-2 info-row">
              <span style="color: var(--text-muted)">Last Heartbeat</span>
              <span style="color: var(--text-heading)">{{ timeAgo(device.last_heartbeat) }}</span>
            </div>
            <div v-if="device.firmware_version" class="flex justify-between text-sm py-2 info-row">
              <span style="color: var(--text-muted)">Firmware</span>
              <span style="color: var(--text-heading)">{{ device.firmware_version }}</span>
            </div>
            <div v-if="unitStore.activeUnitId === 'all'" class="flex justify-between text-sm py-2 info-row">
              <span style="color: var(--text-muted)">Unit</span>
              <span style="color: var(--text-heading)">{{ device.unit?.name || '-' }}</span>
            </div>
            <div class="flex justify-between text-sm py-2 info-row">
              <span style="color: var(--text-muted)">Menampilkan</span>
              <div class="text-right max-w-[200px]">
                <span class="font-medium truncate block" :class="device.status === 'online' ? 'text-accent' : ''" :style="device.status !== 'online' ? 'color: var(--text-heading)' : ''" :title="device.current_page">{{ device.current_page || '-' }}</span>
                <!-- Global banner (applies to all TVs) -->
                <span v-if="store.globalBanner" class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-purple-500/15 text-purple-400 mt-1">
                  <span class="w-1.5 h-1.5 rounded-full" :class="store.globalBanner.type === 'emergency' ? 'bg-red-400' : store.globalBanner.type === 'announcement' ? 'bg-amber-400' : 'bg-blue-400'"></span>
                  🌐 {{ store.globalBanner.title }}
                </span>
                <!-- Per-device banner (targeted) -->
                <span v-if="device.active_banner" class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400 mt-1">
                  <span class="w-1.5 h-1.5 rounded-full" :class="device.active_banner.type === 'emergency' ? 'bg-red-400' : device.active_banner.type === 'announcement' ? 'bg-amber-400' : 'bg-blue-400'"></span>
                  🔔 {{ device.active_banner.title }}
                </span>
              </div>
            </div>

          </div>
        </div>

        <!-- Action button -->
        <div class="pt-5 mt-auto flex flex-col gap-2">
          <button v-if="device.status === 'offline'" @click="doRegenerateToken(device)"
                  class="w-full py-2.5 px-4 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10 text-sm font-medium transition-colors flex items-center justify-center gap-2 cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">autorenew</span>
            Regenerate Token
          </button>
          <button v-else-if="device.status === 'setup'" @click="doRegenerateToken(device)"
                  class="w-full py-2.5 px-4 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2 btn-secondary cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">autorenew</span>
            Token Baru
          </button>
          <template v-else>
            <button @click="viewTv(device)"
                    class="w-full py-2.5 px-4 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2 bg-accent/10 border border-accent/30 text-accent hover:bg-accent/20 cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">live_tv</span>
              Lihat TV
            </button>
            <button @click="doImpersonate(device)"
                    class="w-full py-2.5 px-4 rounded-lg text-sm font-bold transition-colors flex items-center justify-center gap-2 bg-purple-500/10 border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 cursor-pointer">
              <span class="material-symbols-outlined text-[18px]">person_play</span>
              Impersonifikasi
            </button>
            <button @click="doForceDisconnect(device)"
                    class="w-full py-2 px-4 rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-2 text-red-400 hover:bg-red-500/10 border border-red-500/20 hover:border-red-500/40 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">link_off</span>
              Putuskan Sambungan
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- ═══ EMPTY STATE ═══ -->
    <div v-if="!loading && store.devices.length === 0" class="flex flex-col items-center justify-center py-16 gap-4" style="color: var(--text-muted)">
      <span class="material-symbols-outlined text-6xl">tv_off</span>
      <p class="text-lg font-medium">Belum ada perangkat TV terdaftar.</p>
    </div>

    <!-- ═══ PAGINATION ═══ -->
    <div v-if="store.totalPages > 1" class="flex justify-between items-center text-sm" style="color: var(--text-muted)">
      <p>Halaman {{ store.currentPage }} dari {{ store.totalPages }}</p>
      <div class="flex gap-2">
        <button @click="goToPage(store.currentPage - 1)" :disabled="store.currentPage <= 1" class="page-nav disabled:opacity-50 cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>
        <button @click="goToPage(store.currentPage + 1)" :disabled="store.currentPage >= store.totalPages" class="page-nav disabled:opacity-50 cursor-pointer">
          <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- ═══ CREATE/EDIT DIALOG ═══ -->
    <Teleport to="#admin-root" defer>
      <Transition name="fade">
        <div v-if="showDialog" class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center" @click.self="showDialog = false">
          <div class="rounded-2xl p-8 w-full max-w-lg shadow-2xl" style="background: var(--bg-card); border: 1px solid var(--border)">
            <h3 class="text-xl font-bold mb-6" style="color: var(--text-heading)">{{ editingDevice ? 'Edit Perangkat' : 'Tambah Perangkat Baru' }}</h3>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-bold uppercase mb-2" style="color: var(--text-muted)">Nama Perangkat *</label>
                <input v-model="form.name" class="w-full px-4 py-2.5 rounded-lg text-sm outline-none" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)" placeholder="contoh: Lobby Utama" />
              </div>
              <div>
                <label class="block text-xs font-bold uppercase mb-2" style="color: var(--text-muted)">Lokasi</label>
                <input v-model="form.location" class="w-full px-4 py-2.5 rounded-lg text-sm outline-none" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)" placeholder="contoh: Gedung A, Lantai 1" />
              </div>
              <div>
                <label class="block text-xs font-bold uppercase mb-2" style="color: var(--text-muted)">Orientasi</label>
                <div class="flex gap-3">
                  <button @click="form.orientation = 'landscape'" :class="form.orientation === 'landscape' ? 'border-accent bg-accent/10 text-accent' : ''" class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-bold cursor-pointer transition-all" style="border: 1px solid var(--border); color: var(--text-heading)">
                    <span class="material-symbols-outlined text-[20px]">stay_current_landscape</span> Landscape
                  </button>
                  <button @click="form.orientation = 'portrait'" :class="form.orientation === 'portrait' ? 'border-accent bg-accent/10 text-accent' : ''" class="flex-1 flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-bold cursor-pointer transition-all" style="border: 1px solid var(--border); color: var(--text-heading)">
                    <span class="material-symbols-outlined text-[20px]">stay_current_portrait</span> Portrait
                  </button>
                </div>
              </div>
              <div v-if="unitStore.activeUnitId === 'all'">
                <label class="block text-xs font-bold uppercase mb-2" style="color: var(--text-muted)">Unit *</label>
                <VueMultiselect
                  v-model="formUnitOption"
                  :options="unitStore.units"
                  :close-on-select="true"
                  :searchable="true"
                  :allow-empty="false"
                  :show-labels="false"
                  label="name"
                  track-by="id"
                  placeholder="Pilih Unit"
                />
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-8">
              <button @click="showDialog = false" class="px-5 py-2.5 rounded-lg text-sm font-medium cursor-pointer btn-secondary">Batal</button>
              <button @click="saveDevice" :disabled="!form.name" class="px-5 py-2.5 rounded-lg bg-accent text-[#0a192f] text-sm font-bold cursor-pointer hover:brightness-110 disabled:opacity-50 transition-all">
                {{ editingDevice ? 'Simpan' : 'Buat Perangkat' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ═══ DELETE CONFIRM ═══ -->
    <Teleport to="#admin-root" defer>
      <Transition name="fade">
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center" @click.self="showDeleteConfirm = false">
          <div class="rounded-2xl p-8 w-full max-w-md shadow-2xl" style="background: var(--bg-card); border: 1px solid var(--border)">
            <div class="flex items-center gap-3 mb-4">
              <div class="p-2 rounded-lg bg-red-500/10"><span class="material-symbols-outlined text-red-400">warning</span></div>
              <h3 class="text-xl font-bold" style="color: var(--text-heading)">Hapus Perangkat?</h3>
            </div>
            <p class="text-sm mb-6" style="color: var(--text-muted)">Perangkat <strong style="color: var(--text-heading)">{{ deletingDevice?.name }}</strong> akan dihapus permanen.</p>
            <div class="flex justify-end gap-3">
              <button @click="showDeleteConfirm = false" class="px-5 py-2.5 rounded-lg text-sm font-medium cursor-pointer btn-secondary">Batal</button>
              <button @click="doDelete" class="px-5 py-2.5 rounded-lg bg-red-500 text-white text-sm font-bold cursor-pointer hover:bg-red-600 transition-all">Hapus</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useTvDeviceStore } from '../../../stores/tvDevice'
import { useUnitStore } from '../../../stores/unit'
import echo from '../../../echo'
import { useRouter } from 'vue-router'
import api from '../../../axios'

const router = useRouter()
const store = useTvDeviceStore()
const unitStore = useUnitStore()

const searchQuery = ref('')
const filterStatus = ref('all')
const loading = ref(false)

const statusTabs = [
  { value: 'all', label: 'Semua' },
  { value: 'online', label: 'Online' },
  { value: 'setup', label: 'Setup' },
  { value: 'offline', label: 'Offline' },
]

const statsCards = computed(() => [
  { label: 'Total Perangkat', value: store.stats.total, icon: 'tv', iconBg: 'bg-accent/10', iconColor: 'text-accent' },
  { label: 'Online', value: store.stats.online, icon: 'check_circle', iconBg: 'bg-green-500/10', iconColor: 'text-green-400' },
  { label: 'Setup', value: store.stats.setup, icon: 'settings', iconBg: 'bg-yellow-500/10', iconColor: 'text-yellow-400' },
  { label: 'Offline', value: store.stats.offline, icon: 'wifi_off', iconBg: 'bg-red-500/10', iconColor: 'text-red-400' },
  { label: 'Warning', value: store.stats.warning, icon: 'warning', iconBg: 'bg-orange-500/10', iconColor: 'text-orange-400' },
])

// ── Dialog state ──
const showDialog = ref(false)
const editingDevice = ref(null)
const form = ref({ name: '', location: '', orientation: 'landscape', unit_id: '' })

const formUnitOption = computed({
  get: () => unitStore.units.find(u => u.id === form.value.unit_id) || null,
  set: (val) => { form.value.unit_id = val ? val.id : '' }
})

const showDeleteConfirm = ref(false)
const deletingDevice = ref(null)

// ── Load ──
async function loadDevices() {
  loading.value = true
  const params = { per_page: 12, page: store.currentPage }
  if (filterStatus.value !== 'all') params.status = filterStatus.value
  if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
  await store.fetchDevices(params)
  loading.value = false
}

let searchTimeout = null
function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { store.currentPage = 1; loadDevices() }, 400)
}

function goToPage(p) {
  if (p >= 1 && p <= store.totalPages) { store.currentPage = p; loadDevices() }
}

// ── Create / Edit ──
function openCreateDialog() {
  editingDevice.value = null
  form.value = { name: '', location: '', orientation: 'landscape', unit_id: '' }
  showDialog.value = true
}

function openEditDialog(device) {
  editingDevice.value = device
  form.value = { 
    name: device.name, 
    location: device.location || '', 
    orientation: device.orientation || 'landscape',
    unit_id: device.unit_id || ''
  }
  showDialog.value = true
}

async function saveDevice() {
  try {
    const payload = { ...form.value }
    if (unitStore.activeUnitId !== 'all') {
      delete payload.unit_id
    }
    
    if (editingDevice.value) {
      await store.updateDevice(editingDevice.value.id, payload)
    } else {
      await store.createDevice(form.value)
    }
    showDialog.value = false
    await Promise.all([loadDevices(), store.fetchStats()])
  } catch (e) { console.error(e) }
}

// ── Delete ──
function confirmDelete(device) {
  deletingDevice.value = device
  showDeleteConfirm.value = true
}

async function doDelete() {
  try {
    await store.deleteDevice(deletingDevice.value.id)
    showDeleteConfirm.value = false
    await Promise.all([loadDevices(), store.fetchStats()])
  } catch (e) { console.error(e) }
}

// ── Regenerate Token ──
async function doRegenerateToken(device) {
  try {
    const updated = await store.regenerateToken(device.id)
    const idx = store.devices.findIndex(d => d.id === device.id)
    if (idx !== -1) store.devices[idx] = updated
  } catch (e) { console.error(e) }
}

// ── Impersonate (Remote Control) ──
function doImpersonate(device) {
  const resolved = router.resolve({ path: '/', query: { impersonate: device.id } })
  window.open(resolved.href, '_blank')
}

// ── Force Disconnect ──
async function doForceDisconnect(device) {
  try {
    await api.post(`/tv-devices/${device.id}/force-disconnect`)
    await Promise.all([loadDevices(), store.fetchStats()])
  } catch (e) { console.error(e) }
}

// ── Path → Label map (for admin display) ──
const pathToLabel = {
  '/': 'Beranda',
  '/info-terkini': 'Info Terkini',
  '/agenda-harian': 'Agenda Harian',
  '/agenda-mingguan': 'Agenda Mingguan',
  '/agenda-bulanan': 'Agenda Bulanan',
  '/gallery-video': 'Galeri Video',
  '/pengumuman': 'Pengumuman',
  '/urgent': 'Pengumuman Darurat',
  '/connect': 'Input Token',
  '/connect-success': 'Koneksi Berhasil',
}

function getPageLabel(path) {
  if (!path) return '-'
  if (pathToLabel[path]) return pathToLabel[path]
  if (path.startsWith('/detail/')) return 'Detail Konten'
  return path
}

async function viewTv(device) {
  try {
    const { data } = await api.get(`/tv-devices/${device.id}/current-page`)
    const path = data.current_page || '/'
    const resolved = router.resolve({ path, query: { admin_view: '1', device_id: device.id } })
    window.open(resolved.href, '_blank')
  } catch (e) {
    console.error('Failed to fetch current page:', e)
    const path = device.current_page || '/'
    const resolved = router.resolve({ path, query: { admin_view: '1', device_id: device.id } })
    window.open(resolved.href, '_blank')
  }
}

function copyToken(token) {
  navigator.clipboard?.writeText(token)
}

function statusDot(status) {
  if (status === 'online') return 'bg-green-400 animate-pulse'
  if (status === 'setup') return 'bg-yellow-400'
  if (status === 'offline') return 'border-2 border-red-500'
  return 'bg-gray-400'
}

function statusTextClass(status) {
  if (status === 'online') return 'text-green-400'
  if (status === 'setup') return 'text-yellow-400'
  if (status === 'offline') return 'text-red-500'
  return 'text-gray-400'
}

function statusLabel(status) {
  if (status === 'online') return 'Online'
  if (status === 'setup') return 'Setup Mode'
  if (status === 'offline') return 'Offline'
  return status
}

function timeAgo(dateStr) {
  if (!dateStr) return '-'
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins} menit lalu`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours} jam lalu`
  return `${Math.floor(hours / 24)} hari lalu`
}

onMounted(async () => {
  await Promise.all([loadDevices(), store.fetchStats()])

  // ── WebSocket: listen for real-time status changes ──
  echo.channel('tv-devices')
    .listen('.status.changed', (e) => {
      // Update device in list if present
      const idx = store.devices.findIndex(d => d.id === e.device_id)
      if (idx !== -1) {
        store.devices[idx] = { ...store.devices[idx], ...e }
      }
      // Always refresh stats
      store.fetchStats()
    })
})

onUnmounted(() => {
  echo.leave('tv-devices')
})
</script>

<style scoped>
.device-card { background: var(--bg-card); border: 1px solid var(--border); transition: border-color 0.2s ease; }
.device-card:hover { border-color: var(--border-light); }
.setup-card { background: linear-gradient(135deg, rgba(251, 191, 36, 0.06), var(--bg-card)); border-color: rgba(251, 191, 36, 0.25); box-shadow: 0 0 20px rgba(251, 191, 36, 0.05); }
.token-box { background: var(--bg-main); border: 1px solid rgba(251, 191, 36, 0.2); }
.warning-box { background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.15); }
.info-row { border-bottom: 1px solid var(--border); }
.info-row:last-child { border-bottom: none; }
.action-btn { color: var(--text-muted); }
.action-btn:hover { color: var(--color-accent); background: var(--bg-input); }
.btn-secondary { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
.btn-secondary:hover { border-color: var(--border-light); background: var(--border); }
.page-nav { width: 32px; height: 32px; border-radius: 0.5rem; background: var(--bg-input); color: var(--text-heading); display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; }
.page-nav:hover:not(:disabled) { background: var(--border); }
.stat-card { background: var(--bg-card); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
