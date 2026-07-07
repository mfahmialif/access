<template>
  <div class="relative w-screen h-screen overflow-hidden text-white font-display antialiased">
    <!-- ═══════ BACKGROUND ═══════ -->
    <div class="absolute inset-0 bg-bg-deepest/95 pointer-events-none z-0 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-linear-to-b from-blue-950/80 to-bg-deepest pointer-events-none z-0"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col h-full w-full p-4 md:p-8 gap-4 md:gap-6 max-w-[1920px] mx-auto">

      <!-- ═══════ HEADER ═══════ -->
      <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between w-full shrink-0 gap-3">
        <div class="flex items-center gap-3 md:gap-4">
          <button @click="goBack"
                  class="size-10 md:size-12 rounded-lg bg-[#0f172a]/80 border border-accent/30 flex items-center justify-center text-accent hover:bg-accent hover:text-[#0a192f] transition-all duration-300 shadow-[0_0_10px_rgba(251,191,36,0.1)] hover:shadow-[0_0_20px_rgba(251,191,36,0.4)] cursor-pointer">
            <span class="material-symbols-outlined text-xl md:text-2xl">arrow_back</span>
          </button>
          <div class="size-10 md:size-12 rounded-lg bg-linear-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-blue-950 shadow-[0_0_15px_rgba(250,204,21,0.4)]">
            <span class="material-symbols-outlined text-2xl md:text-3xl font-bold">mosque</span>
          </div>
          <div>
            <h1 class="text-xl md:text-3xl font-bold tracking-tight text-white leading-none drop-shadow-md">Access</h1>
            <p class="text-yellow-300 text-xs md:text-sm font-medium tracking-wide uppercase mt-1">Sistem Informasi Pesantren</p>
          </div>
        </div>
        <div class="hidden md:flex items-center gap-6 glass-panel rounded-xl px-6 py-3 border border-yellow-500/20">
          <div class="text-right border-r border-yellow-500/20 pr-6">
            <div class="text-3xl font-bold font-mono text-yellow-400 tabular-nums tracking-widest drop-shadow-sm">
              {{ currentTime }}<span class="text-lg text-yellow-400/60 ml-1">WIB</span>
            </div>
          </div>
          <div class="flex flex-col">
            <span class="text-white text-lg font-medium">{{ hijriDate }}</span>
            <span class="text-slate-400 text-sm">{{ currentDate }}</span>
          </div>
        </div>
      </header>

      <!-- ═══════ MAIN CONTENT ═══════ -->
      <main class="flex flex-col md:flex-row portrait:flex-col flex-1 gap-4 md:gap-8 overflow-hidden">

        <!-- ═══════ LEFT: TIMELINE SIDEBAR ═══════ -->
        <div class="w-full md:w-1/3 portrait:w-full flex flex-col glass-panel rounded-2xl overflow-hidden shadow-2xl ring-1 ring-yellow-500/10 max-h-none md:max-h-none portrait:max-h-none">
          <div class="p-4 md:p-6 bg-blue-950/40 border-b border-yellow-500/10">
            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2 text-accent">
              <span class="material-symbols-outlined text-yellow-400">calendar_month</span>
              Jadwal Hari Ini
            </h2>
          </div>
          <div class="flex-1 overflow-y-auto no-scrollbar py-6 px-4 relative">
            <!-- Timeline Line -->
            <div class="absolute left-[39px] top-6 bottom-6 w-[2px] z-0"
                 style="background: linear-gradient(to bottom, rgba(255,255,255,0.05) 0%, rgba(250,204,21,0.6) 20%, rgba(250,204,21,0.6) 80%, rgba(255,255,255,0.05) 100%)"></div>

            <!-- ══ Grouped Items by Time ══ -->
            <div v-for="(group, gIdx) in timeGroups" :key="group.time"
                 :class="[
                   'relative z-10 cursor-pointer transition-all duration-500 mb-8',
                   gIdx === selectedGroupIdx ? 'pl-2' : '',
                   gIdx !== selectedGroupIdx && group.isPast ? 'opacity-50 hover:opacity-70' : '',
                   gIdx !== selectedGroupIdx && !group.isPast ? 'hover:opacity-80' : ''
                 ]"
                 @click="selectGroup(gIdx)">
              <!-- Glow bar (active only) -->
              <div v-if="gIdx === selectedGroupIdx" class="absolute -left-2 top-0 bottom-0 w-1 bg-linear-to-b from-accent/0 via-accent to-accent/0 rounded-r-lg shadow-[0_0_10px_rgba(250,204,21,0.6)] transition-opacity duration-500"></div>

              <div class="flex gap-4">
                <!-- Dot -->
                <div class="flex flex-col items-center">
                  <div :class="[
                    'rounded-full mt-1.5 transition-all duration-500',
                    gIdx === selectedGroupIdx
                      ? 'w-5 h-5 bg-yellow-400 border-2 border-white shadow-[0_0_15px_rgba(250,204,21,0.4)] active-pulse'
                      : group.isPast
                        ? 'w-4 h-4 bg-slate-800 border-2 border-slate-600 shadow-md'
                        : 'w-4 h-4 bg-blue-950 border-2 border-slate-500 ring-2 ring-blue-900'
                  ]"></div>
                </div>

                <!-- Group Content -->
                <div :class="[
                  'flex-1 flex flex-col transition-all duration-500',
                  gIdx === selectedGroupIdx
                    ? 'bg-blue-900/60 p-4 rounded-xl border border-yellow-500/40 shadow-lg shadow-yellow-900/10 -mt-2 backdrop-blur-sm'
                    : 'bg-transparent p-0 border-transparent'
                ]">
                  <!-- Time Header -->
                  <div class="flex justify-between items-start">
                    <span :class="[
                      'font-bold font-mono drop-shadow-sm transition-all duration-500',
                      gIdx === selectedGroupIdx ? 'text-2xl text-yellow-400' : 'text-lg text-slate-400'
                    ]">{{ group.time }}</span>
                    <div class="flex items-center gap-2">
                      <span v-if="group.items.length > 1" :class="[
                        'text-xs font-bold px-2 py-0.5 rounded-full',
                        gIdx === selectedGroupIdx ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-400/50' : 'bg-white/10 text-slate-400'
                      ]">{{ group.items.length }} kegiatan</span>
                      <span v-if="gIdx === selectedGroupIdx"
                            class="animate-pulse bg-yellow-500/20 text-yellow-300 text-xs font-bold px-2 py-1 rounded border border-yellow-400/50 flex items-center gap-1 shadow-[0_0_20px_rgba(253,224,71,0.5)]">
                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 shadow-[0_0_5px_#facc15]"></span> AKTIF
                      </span>
                    </div>
                  </div>

                  <!-- Items in this time group -->
                  <div v-for="(item, iIdx) in group.items" :key="item.id"
                       :class="[
                         'transition-all duration-300',
                         group.items.length > 1 && iIdx > 0 ? 'mt-3 pt-3' : 'mt-1',
                         group.items.length > 1 && iIdx > 0 && gIdx === selectedGroupIdx ? 'border-t border-yellow-500/20' : ''
                       ]">
                    <h3 :class="[
                      'font-medium transition-all duration-500',
                      gIdx === selectedGroupIdx ? 'text-lg font-bold text-white' : 'text-base',
                      gIdx !== selectedGroupIdx && group.isPast ? 'text-slate-300' : '',
                      gIdx !== selectedGroupIdx && !group.isPast ? 'text-white' : ''
                    ]">
                      <span v-if="group.items.length > 1" class="material-symbols-outlined text-[14px] mr-1 align-middle" :class="gIdx === selectedGroupIdx ? 'text-yellow-400' : 'text-slate-500'">{{ item.icon }}</span>
                      {{ item.title }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                      <span :class="[
                        'text-sm flex items-center gap-1 transition-all duration-500',
                        gIdx === selectedGroupIdx ? 'text-yellow-200 font-medium' : group.isPast ? 'text-slate-500' : 'text-slate-400'
                      ]">
                        <span v-if="gIdx === selectedGroupIdx" class="material-symbols-outlined text-sm text-yellow-400">location_on</span>
                        {{ item.location }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ═══════ RIGHT: DETAIL PANEL ═══════ -->
        <div class="hidden md:flex portrait:hidden w-2/3 flex-col gap-6">
          <!-- Main Detail Card -->
          <div class="flex-1 glass-panel rounded-2xl p-1 overflow-hidden shadow-2xl flex flex-col relative group border-t border-t-yellow-500/20">
            <div class="absolute inset-0 z-0">
              <img alt="Background" class="w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-[20s] ease-linear grayscale-20 sepia-20 hue-rotate-180 brightness-75 contrast-125"
                   :src="selectedGroup.items?.[activeItemIdx]?.image || '/img/default-agenda.png'" />
              <div class="absolute inset-0 bg-linear-to-t from-bg-deepest via-bg-deepest/80 to-blue-900/20 mix-blend-multiply"></div>
              <div class="absolute inset-0 bg-linear-to-t from-blue-950 via-transparent to-transparent opacity-80"></div>
            </div>
            <div class="relative z-10 flex flex-col h-full p-4 md:p-8 pb-4 md:pb-6 justify-end">
              <!-- Top Info -->
              <div class="flex items-start gap-6 mb-auto">
                <div class="bg-blue-900/40 backdrop-blur-md border border-yellow-500/30 p-4 rounded-2xl text-yellow-400 shadow-[0_0_15px_rgba(250,204,21,0.4)]">
                  <span class="material-symbols-outlined text-5xl">{{ selectedGroup.items?.[activeItemIdx]?.icon || 'event' }}</span>
                </div>
                <div>
                  <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 bg-yellow-500 text-blue-950 text-sm font-bold rounded-md uppercase tracking-wider shadow-lg shadow-yellow-500/30 border border-yellow-400/20">
                      {{ selectedGroup.isActive ? 'Sedang Berlangsung' : 'Detail Kegiatan' }}
                    </span>
                    <span :class="categoryBadgeClass(selectedGroup.items?.[activeItemIdx]?.category)">{{ selectedGroup.items?.[activeItemIdx]?.category }}</span>
                    <!-- Multi-item tabs -->
                    <div v-if="selectedGroup.items?.length > 1" class="flex items-center gap-1 ml-2">
                      <button v-for="(item, tIdx) in selectedGroup.items" :key="item.id"
                              @click="activeItemIdx = tIdx"
                              :class="[
                                'px-3 py-1 text-xs font-bold rounded-md transition-all cursor-pointer',
                                tIdx === activeItemIdx
                                  ? 'bg-accent text-blue-950 shadow-lg'
                                  : 'bg-white/10 text-white/70 hover:bg-white/20'
                              ]">
                        {{ tIdx + 1 }}
                      </button>
                    </div>
                  </div>
                  <h2 class="text-2xl md:text-5xl font-bold text-white tracking-tight leading-tight mb-2 drop-shadow-lg">{{ selectedGroup.items?.[activeItemIdx]?.title }}</h2>
                </div>
              </div>

              <!-- Bottom Info -->
              <div class="grid grid-cols-1 gap-3 mt-6">
                <div class="flex gap-3">
                  <div class="flex-1 bg-blue-950/70 backdrop-blur-md p-4 rounded-xl border border-yellow-500/20 flex items-center gap-4 hover:border-yellow-400/50 transition-colors">
                    <div class="bg-yellow-500/20 p-3 rounded-full text-yellow-400 ring-1 ring-yellow-500/30">
                      <span class="material-symbols-outlined text-3xl">location_on</span>
                    </div>
                    <div>
                      <p class="text-sm text-slate-400 uppercase tracking-wide">Lokasi</p>
                      <p class="text-xl font-bold text-white">{{ selectedGroup.items?.[activeItemIdx]?.location }}</p>
                    </div>
                  </div>
                  <div class="flex-1 bg-blue-950/70 backdrop-blur-md p-4 rounded-xl border border-yellow-500/20 flex items-center gap-4 hover:border-yellow-400/50 transition-colors">
                    <div class="bg-yellow-500/20 p-3 rounded-full text-yellow-400 ring-1 ring-yellow-500/30">
                      <span class="material-symbols-outlined text-3xl">group</span>
                    </div>
                    <div>
                      <p class="text-sm text-slate-400 uppercase tracking-wide">Pengajar Utama</p>
                      <p class="text-xl font-bold text-white">{{ selectedGroup.items?.[activeItemIdx]?.teacher }}</p>
                    </div>
                  </div>
                </div>
                <!-- Lihat Selengkapnya Button -->
                <button @click="router.push({ name: 'DetailAgenda', params: { id: selectedGroup.items?.[activeItemIdx]?.id } })"
                        class="w-full bg-accent/10 backdrop-blur-md px-6 py-3 rounded-xl border border-accent/40 flex items-center justify-center gap-3 hover:bg-accent/20 hover:border-accent transition-all cursor-pointer active:scale-[0.99] group/btn">
                  <span class="material-symbols-outlined text-accent text-xl group-hover/btn:text-yellow-300 transition-colors">open_in_full</span>
                  <span class="text-lg font-bold text-accent group-hover/btn:text-yellow-300 transition-colors">
                    Lihat Selengkapnya {{ selectedGroup.items?.length > 1 ? `(${selectedGroup.items.length} konten)` : '' }}
                  </span>
                  <span class="material-symbols-outlined text-accent/50 text-base">arrow_forward</span>
                </button>
              </div>
            </div>
          </div>

          <!-- ═══════ NEXT UP BAR ═══════ -->
          <div class="hidden md:flex portrait:hidden h-24 glass-panel rounded-2xl items-center px-8 relative overflow-hidden border-t-4 border-t-yellow-500 shadow-lg shadow-yellow-900/20">
            <div class="absolute left-0 top-0 bottom-0 w-2 bg-linear-to-b from-yellow-400 to-yellow-600"></div>
            <div class="flex flex-col mr-8 min-w-[120px]">
              <span class="text-yellow-400 text-sm font-bold uppercase tracking-wider mb-1">Selanjutnya</span>
              <span class="text-white text-3xl font-mono font-bold drop-shadow-sm">{{ nextGroup.time }}</span>
            </div>
            <div class="h-12 w-px bg-white/10 mx-6"></div>
            <div class="flex-1 flex justify-between items-center">
              <div class="flex flex-col">
                <h3 class="text-2xl font-bold text-white">{{ nextGroup.title }}</h3>
                <p class="text-slate-400 text-lg flex items-center gap-2">
                  <span class="material-symbols-outlined text-base text-yellow-500">arrow_forward</span>
                  {{ nextGroup.note }}
                </p>
              </div>
              <div class="bg-blue-950/60 p-3 rounded-lg border border-yellow-500/20 text-yellow-200">
                <span class="material-symbols-outlined text-4xl">{{ nextGroup.icon }}</span>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const router = useRouter()

// ── Time & Date ──
const currentTime = ref('')
const currentDate = ref('')
const hijriDate = ref('')

function updateTime() {
  const now = new Date()
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`

  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  currentDate.value = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`

  try {
    hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(now)
  } catch {
    hijriDate.value = 'Senin, 24 Rajab 1445 H'
  }
}

let timeInterval
onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
  loadAgendas()
})
onUnmounted(() => clearInterval(timeInterval))

// ── Navigation ──
function goBack() {
  router.push({ name: 'Landing' })
}

// ── Category Badge Styling ──
function categoryBadgeClass(category) {
  const base = 'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider'
  switch (category) {
    case 'Artikel':
      return `${base} bg-accent/20 text-accent border border-accent/40`
    case 'Gambar':
      return `${base} bg-blue-500/20 text-blue-400 border border-blue-500/40`
    case 'Video':
      return `${base} bg-red-500/20 text-red-400 border border-red-500/40`
    default:
      return `${base} bg-accent/20 text-accent border border-accent/40`
  }
}

// ── Agenda Data (from API) ──
const allItems = ref([])
const loading = ref(true)

async function loadAgendas() {
  loading.value = true
  try {
    const { data } = await api.get('/agendas', { params: { per_page: 50, status: 'Aktif' } })
    const now = new Date()
    const currentMinutes = now.getHours() * 60 + now.getMinutes()

    allItems.value = data.data.map(item => {
      const timeParts = item.time?.substring(0, 5).split(':') || [0, 0]
      const itemMinutes = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1])
      const isPast = itemMinutes < currentMinutes - 30
      const isActive = !isPast && itemMinutes <= currentMinutes + 30 && itemMinutes >= currentMinutes - 30

      const imgPath = item.image_path ? storageUrl(item.image_path) : '/img/default-agenda.png'

      return {
        id: item.id,
        time: item.time?.substring(0, 5) || '00:00',
        title: item.title,
        location: item.location || '-',
        icon: item.icon || 'event',
        teacher: item.teacher || '-',
        category: item.category,
        isPast,
        isActive,
        description: item.body || item.title,
        detailTitle: item.title,
        image: imgPath,
        bgImage: imgPath,
        body: item.body || '',
        speaker: item.teacher || '',
        videoTag: item.category === 'Video' ? 'Video' : 'Kajian',
        videoSrc: item.video_path ? storageUrl(item.video_path) : null,
        duration: 3600,
        date: new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
      }
    })
  } catch (e) {
    console.error('Failed to load agendas:', e)
  } finally {
    loading.value = false
  }
}

// ── Group items by time ──
const timeGroups = computed(() => {
  const groups = []
  const groupMap = {}

  for (const item of allItems.value) {
    if (!groupMap[item.time]) {
      groupMap[item.time] = { time: item.time, items: [], isPast: item.isPast, isActive: item.isActive }
      groups.push(groupMap[item.time])
    }
    groupMap[item.time].items.push(item)
    // If any item in group is active, mark group as active
    if (item.isActive) groupMap[item.time].isActive = true
    if (!item.isPast) groupMap[item.time].isPast = false
  }

  return groups
})

// ── Selected Group ──
const selectedGroupIdx = ref(0)
const activeItemIdx = ref(0)

const selectedGroup = computed(() => timeGroups.value[selectedGroupIdx.value] || { items: [] })

// Reset active item when changing group
watch(selectedGroupIdx, () => { activeItemIdx.value = 0 })

function selectGroup(gIdx) {
  selectedGroupIdx.value = gIdx
}



// ── Next Group ──
const nextGroup = computed(() => {
  const nextIdx = selectedGroupIdx.value + 1
  if (nextIdx < timeGroups.value.length) {
    const g = timeGroups.value[nextIdx]
    const titles = g.items.map(i => i.title).join(', ')
    const locations = [...new Set(g.items.map(i => i.location))].join(', ')
    return {
      time: g.time,
      title: titles,
      icon: g.items[0].icon,
      note: `Persiapan dimulai sebelum pukul ${g.time} di ${locations}.`
    }
  }
  if (timeGroups.value.length > 0) {
    return {
      time: timeGroups.value[timeGroups.value.length - 1].time,
      title: 'Jadwal terakhir hari ini',
      icon: 'check_circle',
      note: 'Tidak ada agenda selanjutnya.'
    }
  }
  return { time: '--:--', title: 'Memuat...', note: '', icon: 'schedule' }
})
</script>

<style scoped>
.active-pulse {
  animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulse-ring {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(250, 204, 21, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(250, 204, 21, 0); }
}

/* Detail overlay transitions */
.detail-enter-active {
  animation: fadeIn 0.3s ease-out;
}
.detail-leave-active {
  animation: fadeOut 0.2s ease-in;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(1.02); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes fadeOut {
  from { opacity: 1; transform: scale(1); }
  to { opacity: 0; transform: scale(0.98); }
}
</style>
