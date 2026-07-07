<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ LOG TABS ═══ -->
    <div class="flex items-center gap-1 p-1 rounded-xl overflow-x-auto" style="background: var(--bg-card); border: 1px solid var(--border)">
      <button v-for="tab in tabs" :key="tab.key"
              @click="activeTab = tab.key"
              :class="['tab-btn flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all whitespace-nowrap', activeTab === tab.key ? 'active' : '']">
        <span class="material-symbols-outlined text-[18px]">{{ tab.icon }}</span>
        {{ tab.label }}
        <span class="tab-count text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1">{{ tab.count }}</span>
      </button>
    </div>

    <!-- ═══ FILTERS ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Search logs..." type="text" />
      </div>
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Periode:</span>
          <VueMultiselect v-model="filterPeriod" :options="periodOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" class="flex-1 sm:w-[150px] sm:flex-none" />
        </div>
        <button class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">
          <span class="material-symbols-outlined text-[16px]">download</span>
          Export CSV
        </button>
      </div>
    </div>

    <!-- ═══ LOG TABLE ═══ -->
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold w-44" style="color: var(--text-heading)">Waktu</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Aktivitas</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">User / Device</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Detail</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-for="log in filteredLogs" :key="log.id" class="table-row-hover">
              <td class="px-4 py-4">
                <span class="text-xs font-mono font-bold block" style="color: var(--text-heading)">{{ log.time }}</span>
                <span class="text-[10px] font-mono" style="color: var(--text-muted)">{{ log.date }}</span>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :class="logIconBg(log.type)">
                    <span class="material-symbols-outlined text-[18px]" :class="logIconColor(log.type)">{{ logIcon(log.type) }}</span>
                  </div>
                  <span class="text-sm font-bold" style="color: var(--text-heading)">{{ log.action }}</span>
                </div>
              </td>
              <td class="px-4 py-4">
                <span class="text-sm font-medium" style="color: var(--text-body)">{{ log.actor }}</span>
                <span v-if="log.ip" class="text-[10px] font-mono block mt-0.5" style="color: var(--text-muted)">{{ log.ip }}</span>
              </td>
              <td class="px-4 py-4">
                <span class="text-sm line-clamp-1" style="color: var(--text-muted)">{{ log.detail }}</span>
              </td>
              <td class="px-4 py-4"><span :class="statusBadge(log.status)">{{ log.status }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination-bar flex items-center justify-between px-6 py-4">
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ filteredLogs.length }} entries</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button class="page-btn p-2 rounded-lg disabled:opacity-50" disabled><span class="material-symbols-outlined text-[20px]">chevron_left</span></button>
          <button class="page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center">1</button>
          <button class="page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center">2</button>
          <button class="page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center">3</button>
          <button class="page-btn p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">chevron_right</span></button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

const searchQuery = ref('')
const periodOptions = [
  { name: 'Hari Ini', value: 'today' }, { name: '7 Hari', value: '7d' },
  { name: '30 Hari', value: '30d' }, { name: 'Semua', value: 'all' }
]
const filterPeriod = ref(periodOptions[0])

const tabs = [
  { key: 'login', label: 'Log Login', icon: 'login', count: 12 },
  { key: 'push', label: 'Push Konten', icon: 'send', count: 8 },
  { key: 'tv', label: 'TV Connect', icon: 'cast_connected', count: 15 },
  { key: 'content', label: 'Perubahan Konten', icon: 'edit_note', count: 6 }
]
const activeTab = ref('login')

const allLogs = ref([
  // Login logs
  { id: 1, type: 'login', time: '21:30:15', date: '06 Mar 2026', action: 'Login Berhasil', actor: 'Administrator', ip: '192.168.1.100', detail: 'Chrome 120 / Windows 11', status: 'Success' },
  { id: 2, type: 'login', time: '21:28:40', date: '06 Mar 2026', action: 'Login Gagal', actor: 'admin@access.id', ip: '192.168.1.55', detail: 'Password salah (3x percobaan)', status: 'Failed' },
  { id: 3, type: 'login', time: '18:00:00', date: '06 Mar 2026', action: 'Logout', actor: 'Administrator', ip: '192.168.1.100', detail: 'Sesi berakhir normal', status: 'Info' },
  { id: 4, type: 'login', time: '14:22:10', date: '06 Mar 2026', action: 'Login Berhasil', actor: 'Editor Konten', ip: '192.168.1.42', detail: 'Firefox 121 / macOS', status: 'Success' },
  { id: 5, type: 'login', time: '08:15:30', date: '06 Mar 2026', action: 'Login Berhasil', actor: 'Administrator', ip: '192.168.1.100', detail: 'Chrome 120 / Windows 11', status: 'Success' },

  // Push logs
  { id: 10, type: 'push', time: '21:58:30', date: '06 Mar 2026', action: 'Push ke TV Lobby', actor: 'Administrator', ip: null, detail: 'Konten: Agenda Harian - Jadwal Hari Ini', status: 'Success' },
  { id: 11, type: 'push', time: '21:55:12', date: '06 Mar 2026', action: 'Broadcast Semua TV', actor: 'Administrator', ip: null, detail: 'Konten: Info Terkini - Kunjungan Dubes', status: 'Success' },
  { id: 12, type: 'push', time: '20:30:00', date: '06 Mar 2026', action: 'Push ke TV Masjid', actor: 'Editor Konten', ip: null, detail: 'Konten: Video Kajian Kitab Al-Hikam', status: 'Success' },
  { id: 13, type: 'push', time: '19:45:22', date: '06 Mar 2026', action: 'Push Gagal', actor: 'Administrator', ip: null, detail: 'TV Asrama Putri — timeout', status: 'Failed' },

  // TV Connect logs
  { id: 20, type: 'tv', time: '22:00:05', date: '06 Mar 2026', action: 'TV Connected', actor: 'TV Lobby Utama', ip: '192.168.1.201', detail: 'Reconnected setelah restart', status: 'Success' },
  { id: 21, type: 'tv', time: '21:45:00', date: '06 Mar 2026', action: 'TV Disconnected', actor: 'TV Asrama Putri', ip: '192.168.1.210', detail: 'Connection lost — no heartbeat', status: 'Failed' },
  { id: 22, type: 'tv', time: '20:15:30', date: '06 Mar 2026', action: 'TV Connected', actor: 'TV Masjid Jami', ip: '192.168.1.202', detail: 'Initial connection', status: 'Success' },
  { id: 23, type: 'tv', time: '19:00:10', date: '06 Mar 2026', action: 'TV Disconnected', actor: 'TV Gedung B Lt.2', ip: '192.168.1.206', detail: 'Manual shutdown', status: 'Info' },
  { id: 24, type: 'tv', time: '18:30:45', date: '06 Mar 2026', action: 'TV Connected', actor: 'TV Perpustakaan', ip: '192.168.1.207', detail: 'Firmware update complete', status: 'Success' },

  // Content change logs
  { id: 30, type: 'content', time: '20:15:00', date: '06 Mar 2026', action: 'Konten Diedit', actor: 'Administrator', ip: null, detail: 'Info Terkini: "Kunjungan Duta Besar" — body updated', status: 'Info' },
  { id: 31, type: 'content', time: '19:45:30', date: '06 Mar 2026', action: 'Konten Ditambah', actor: 'Editor Konten', ip: null, detail: 'Video baru: "Kajian Pagi — Kitab Al-Hikam"', status: 'Success' },
  { id: 32, type: 'content', time: '18:20:00', date: '06 Mar 2026', action: 'Konten Dihapus', actor: 'Administrator', ip: null, detail: 'Gambar: "Foto Lama Renovasi Asrama" dihapus', status: 'Failed' },
  { id: 33, type: 'content', time: '15:10:22', date: '06 Mar 2026', action: 'Pengumuman Diubah', actor: 'Administrator', ip: null, detail: 'Pengumuman "Libur Isra Mi\'raj" — status: Aktif', status: 'Info' }
])

const filteredLogs = computed(() => {
  return allLogs.value.filter(log => {
    const matchTab = log.type === activeTab.value
    const matchSearch = log.action.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        log.actor.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        log.detail.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchTab && matchSearch
  })
})

function logIcon(type) {
  const icons = { login: 'login', push: 'send', tv: 'cast_connected', content: 'edit_note' }
  return icons[type] || 'info'
}
function logIconBg(type) {
  const bgs = { login: 'bg-accent/15', push: 'bg-blue-500/15', tv: 'bg-green-500/15', content: 'bg-purple-500/15' }
  return bgs[type] || 'bg-accent/15'
}
function logIconColor(type) {
  const colors = { login: 'text-accent', push: 'text-blue-400', tv: 'text-green-400', content: 'text-purple-400' }
  return colors[type] || 'text-accent'
}

function statusBadge(status) {
  if (status === 'Success') return 'badge badge-published' // Using published (green) for success
  if (status === 'Failed') return 'badge badge-video' // Using video (red) for failed
  return 'badge badge-gambar' // Using gambar (blue) for others/pending
}
</script>

<style scoped>
.tab-btn { color: var(--text-muted); background: transparent; }
.tab-btn:hover { color: var(--text-heading); background: var(--bg-input); }
.tab-btn.active { color: var(--text-btn); background: var(--color-accent); box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
.tab-count { background: rgba(255,255,255,0.15); }
.tab-btn:not(.active) .tab-count { background: var(--bg-input); color: var(--text-muted); }

.filter-input { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-heading); }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

.pagination-bar { border-top: 1px solid var(--border); background: var(--bg-card); }
.page-btn { color: var(--text-muted); border: 1px solid transparent; }
.page-btn:hover { background: var(--bg-input); color: var(--text-heading); }
.page-btn-active { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }
</style>
