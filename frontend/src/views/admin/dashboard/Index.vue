<template>
  <div class="flex flex-col gap-8">

    <!-- ═══ STAT CARDS ═══ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="stat in stats" :key="stat.label"
           class="stat-card flex flex-col gap-3 rounded-xl p-6 backdrop-blur-md border border-accent/30 relative overflow-hidden group transition-all duration-500">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-accent/10 rounded-full blur-xl group-hover:bg-accent/40 transition-all duration-700"></div>
        <div class="absolute -left-8 -bottom-8 w-20 h-20 bg-accent/5 rounded-full blur-xl group-hover:bg-accent/20 transition-all duration-700 delay-100"></div>
        <div class="flex justify-between items-start z-10">
          <p class="text-accent font-medium tracking-wide">{{ stat.label }}</p>
          <span class="material-symbols-outlined text-accent text-[28px] drop-shadow-[0_0_5px_rgba(251,191,36,0.5)] group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">{{ stat.icon }}</span>
        </div>
        <div class="flex items-end gap-3 z-10 mt-1">
          <p class="text-3xl font-bold tracking-tight" style="color: var(--text-heading)">{{ stat.value }}</p>
          <span :class="[
            'flex items-center text-sm font-semibold mb-1',
            stat.trend > 0 ? 'text-green-500' : stat.trend === 0 ? 'text-slate-400' : 'text-red-500'
          ]">
            <span class="material-symbols-outlined text-[16px]">{{ stat.trend > 0 ? 'arrow_upward' : stat.trend === 0 ? 'horizontal_rule' : 'arrow_downward' }}</span>
            {{ Math.abs(stat.trend) }}%
          </span>
        </div>
      </div>
    </div>

    <!-- ═══ ACTIVITY TABLE + NETWORK STATUS ═══ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Recent Activity -->
      <div class="lg:col-span-2 flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold" style="color: var(--text-heading)">Aktivitas Terbaru</h3>
          <router-link :to="{ name: 'AdminLogAktivitas' }"
                       class="text-sm font-semibold text-accent hover:underline flex items-center gap-1 cursor-pointer">
            Lihat Semua <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          </router-link>
        </div>
        <div class="table-wrapper rounded-xl overflow-hidden shadow-lg">
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="table-head">
                  <th class="px-6 py-4 text-sm font-semibold" style="color: var(--text-heading)">Waktu</th>
                  <th class="px-6 py-4 text-sm font-semibold" style="color: var(--text-heading)">User</th>
                  <th class="px-6 py-4 text-sm font-semibold" style="color: var(--text-heading)">Aktivitas</th>
                  <th class="px-6 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
                </tr>
              </thead>
              <tbody class="table-body">
                <tr v-for="(activity, i) in activities" :key="i"
                    class="table-row-hover">
                  <td class="px-6 py-4 text-sm whitespace-nowrap" style="color: var(--text-muted)">
                    {{ activity.time }}<span v-if="activity.date" class="block text-xs opacity-70">{{ activity.date }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm font-medium" style="color: var(--text-body)">{{ activity.user }}</td>
                  <td class="px-6 py-4 text-sm" style="color: var(--text-body)">{{ activity.action }}</td>
                  <td class="px-6 py-4">
                    <span :class="statusBadge(activity.status)">
                      {{ activity.status }}
                    </span>
                  </td>
                </tr>
                <tr v-if="activities.length === 0">
                  <td colspan="4" class="px-6 py-8 text-center text-sm" style="color: var(--text-muted)">
                    Belum ada aktivitas.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Network Status -->
      <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold" style="color: var(--text-heading)">Status Jaringan</h3>
          <button class="p-1.5 rounded-lg transition-colors cursor-pointer" style="background: var(--bg-input); color: var(--text-muted)">
            <span class="material-symbols-outlined text-[20px]">more_horiz</span>
          </button>
        </div>
        <div class="network-card rounded-xl p-5 flex flex-col gap-6 h-full min-h-[300px] shadow-lg">
          <!-- Uptime display -->
          <div class="uptime-display w-full h-40 rounded-lg flex items-center justify-center relative overflow-hidden">
            <span class="material-symbols-outlined text-[64px] uptime-globe absolute">public</span>
            <div class="absolute inset-0 uptime-gradient pointer-events-none"></div>
            <div class="z-10 text-center">
              <p class="text-3xl font-bold text-accent drop-shadow-[0_0_8px_rgba(251,191,36,0.6)]">{{ networkUptime }}%</p>
              <p class="text-xs font-medium uppercase tracking-wider mt-1" style="color: var(--text-muted)">Network Uptime</p>
            </div>
          </div>
          <!-- Online / Offline -->
          <div class="flex flex-col gap-4 flex-1">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse shadow-[0_0_8px_rgba(251,191,36,0.8)]"></div>
                <span class="text-sm font-medium" style="color: var(--text-body)">Online Devices</span>
              </div>
              <span class="text-sm font-bold" style="color: var(--text-heading)">{{ onlineDevices }}</span>
            </div>
            <div class="progress-track w-full h-2 rounded-full overflow-hidden">
              <div class="h-full bg-accent rounded-full shadow-[0_0_10px_rgba(251,191,36,0.8)]"
                   :style="{ width: onlinePercent + '%' }"></div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]"></div>
                <span class="text-sm font-medium" style="color: var(--text-body)">Offline Devices</span>
              </div>
              <span class="text-sm font-bold" style="color: var(--text-heading)">{{ offlineDevices }}</span>
            </div>
            <div class="progress-track w-full h-2 rounded-full overflow-hidden">
              <div class="h-full bg-red-500 rounded-full"
                   :style="{ width: offlinePercent + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '../../../axios'

// ── Dashboard Stats ──
const stats = ref([
  { label: 'Total TVs', icon: 'tv', value: '0', trend: 0 },
  { label: 'Active TVs', icon: 'router', value: '0', trend: 0 },
  { label: 'Total Content', icon: 'library_books', value: '0', trend: 0 },
  { label: 'Users', icon: 'manage_accounts', value: '0', trend: 0 }
])

// ── Recent Activity ──
const activities = ref([])

const statusLabels = { sent: 'Success', failed: 'Failed' }

function statusBadge(status) {
  if (status === 'Success') return 'badge badge-published'
  if (status === 'Pending') return 'badge badge-draft'
  return 'badge badge-video'
}

// ── Network Status ──
const networkUptime = ref(0)
const onlineDevices = ref(0)
const offlineDevices = ref(0)
const totalDevices = computed(() => onlineDevices.value + offlineDevices.value)
const onlinePercent = computed(() => totalDevices.value > 0 ? Math.round((onlineDevices.value / totalDevices.value) * 100) : 0)
const offlinePercent = computed(() => totalDevices.value > 0 ? Math.round((offlineDevices.value / totalDevices.value) * 100) : 0)

// ── Fetch real data ──
async function fetchDashboard() {
  try {
    const { data } = await api.get('/dashboard/stats')

    stats.value = [
      { label: 'Total TVs', icon: 'tv', value: data.stats.total_tvs.value.toLocaleString('id-ID'), trend: data.stats.total_tvs.trend },
      { label: 'Active TVs', icon: 'router', value: data.stats.active_tvs.value.toLocaleString('id-ID'), trend: data.stats.active_tvs.trend },
      { label: 'Total Content', icon: 'library_books', value: data.stats.total_content.value.toLocaleString('id-ID'), trend: data.stats.total_content.trend },
      { label: 'Users', icon: 'manage_accounts', value: data.stats.users.value.toLocaleString('id-ID'), trend: data.stats.users.trend }
    ]

    activities.value = data.activities.map(a => ({
      ...a,
      status: statusLabels[a.status] ?? 'Pending'
    }))

    networkUptime.value = data.network.uptime
    onlineDevices.value = data.network.online
    offlineDevices.value = data.network.offline
  } catch (e) {
    console.error('Gagal memuat data dashboard:', e)
  }
}

let refreshTimer = null
onMounted(() => {
  fetchDashboard()
  refreshTimer = setInterval(fetchDashboard, 30000)
})
onUnmounted(() => clearInterval(refreshTimer))
</script>
