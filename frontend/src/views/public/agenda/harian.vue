<template>
  <div class="relative min-h-screen w-full text-white font-display antialiased bg-[#020617]">
    <!-- ═══════ BACKGROUND ═══════ -->
    <div class="fixed inset-0 bg-[#020617]/95 pointer-events-none z-0 mix-blend-multiply"></div>
    <div class="fixed inset-0 bg-linear-to-b from-blue-950/80 to-[#020617] pointer-events-none z-0"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col min-h-screen w-full p-4 md:p-8 gap-4 md:gap-6 max-w-[1920px] mx-auto">

      <!-- ═══════ HEADER ═══════ -->
      <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between w-full shrink-0 gap-3">
        <div class="flex items-center gap-3 md:gap-4">
          <button @click="$router.push('/')" 
                  class="size-10 md:size-12 rounded-xl bg-[#0f172a]/80 border border-accent/30 flex items-center justify-center text-accent hover:bg-accent hover:text-[#0a192f] transition-all duration-300 shadow-[0_0_10px_rgba(251,191,36,0.1)] hover:shadow-[0_0_20px_rgba(251,191,36,0.4)] cursor-pointer shrink-0">
            <span class="material-symbols-outlined text-xl md:text-2xl">arrow_back</span>
          </button>
          <router-link to="/" class="group cursor-pointer flex flex-col justify-center">
            <img src="/img/logo-full.png" alt="Access" class="h-16 md:h-24 -mt-2 md:-mt-4 object-contain drop-shadow-lg group-hover:drop-shadow-xl transition-all" />
          </router-link>
        </div>
        <div class="hidden md:flex items-center gap-4 glass-panel rounded-xl px-4 py-2 border border-yellow-500/20">
          <div class="text-right border-r border-yellow-500/20 pr-4">
            <div class="text-2xl font-bold font-mono text-yellow-400 tabular-nums tracking-widest drop-shadow-sm">
              {{ currentTime }}<span class="text-base text-yellow-400/60 ml-1">WIB</span>
            </div>
          </div>
          <div class="flex flex-col justify-center">
            <span class="text-white text-base font-medium leading-tight">{{ hijriDate }}</span>
            <span class="text-slate-400 text-xs mt-0.5">{{ currentDate }}</span>
          </div>
        </div>
      </header>

      <!-- ═══════ MAIN CONTENT ═══════ -->
      <main class="flex flex-col md:flex-row portrait:flex-col flex-1 gap-4 md:gap-8 pb-8">

        <!-- ═══════ LEFT: TIMELINE SIDEBAR ═══════ -->
        <div class="w-full md:w-1/3 portrait:w-full flex flex-col glass-panel rounded-2xl overflow-hidden shadow-2xl ring-1 ring-yellow-500/10 md:h-[calc(100vh-10rem)]">
          <div class="p-4 md:p-6 bg-blue-950/40 border-b border-yellow-500/10 flex justify-between items-center shrink-0">
            <h2 class="text-base md:text-xl font-semibold flex items-center gap-2 text-accent">
              <span class="material-symbols-outlined text-yellow-400">calendar_month</span>
              Jadwal Hari Ini
            </h2>
            <button @click="toggleAll" class="text-xs font-medium text-slate-400 hover:text-white transition-colors underline decoration-dashed underline-offset-4 cursor-pointer">
              {{ isAllExpanded ? 'Tutup Semua' : 'Buka Semua' }}
            </button>
          </div>
          <SimpleBar class="flex-1 min-h-0 py-6 px-4 relative md:pr-2 transform-gpu will-change-scroll overscroll-contain" data-simplebar-auto-hide="false">
            <!-- Timeline Line -->
            <div class="absolute left-[31px] top-6 bottom-6 w-[2px] z-0"
                 style="background: linear-gradient(to bottom, rgba(255,255,255,0.05) 0%, rgba(250,204,21,0.6) 20%, rgba(250,204,21,0.6) 80%, rgba(255,255,255,0.05) 100%)"></div>

            <!-- ══ Grouped Items by Time ══ -->
            <div v-for="(group, gIdx) in timeGroups" :key="group.time"
                 :class="[
                   'relative z-10 transition-all duration-500 mb-8',
                   gIdx !== selectedGroupIdx && group.isPast ? 'opacity-50 hover:opacity-70' : '',
                   gIdx !== selectedGroupIdx && !group.isPast ? 'hover:opacity-80' : ''
                 ]">
              <!-- Glow bar (active only) -->
              <div v-if="group.isActive" class="absolute -left-4 top-0 bottom-0 w-1 bg-linear-to-b from-accent/0 via-accent to-accent/0 rounded-r-lg opacity-80"></div>

              <div class="flex gap-4">
                <!-- Dot -->
                <div class="w-8 shrink-0 flex flex-col items-center">
                  <div :class="[
                    'rounded-full mt-1.5 transition-all duration-500',
                    group.isActive
                      ? 'w-5 h-5 bg-yellow-400 border-2 border-white shadow-sm ring-4 ring-yellow-400/30'
                      : group.isPast
                        ? 'w-4 h-4 bg-slate-800 border-2 border-slate-600 shadow-md'
                        : 'w-4 h-4 bg-blue-950 border-2 border-slate-500 ring-2 ring-blue-900'
                  ]"></div>
                </div>

                <!-- Group Content -->
                <div @click="selectGroup(gIdx)" :class="[
                  'flex-1 flex flex-col cursor-pointer rounded-xl border transition-colors duration-300 p-3 -my-1',
                  gIdx === selectedGroupIdx
                    ? 'bg-blue-900/40 border-blue-500/40 shadow-lg'
                    : 'bg-transparent border-transparent hover:bg-white/5'
                ]">
                  <!-- Time Header -->
                  <div class="flex justify-between items-start cursor-pointer hover:bg-white/5 p-1 -m-1 rounded transition-colors" @click.stop="toggleCollapse(gIdx)">
                    <span :class="[
                      'font-bold font-mono drop-shadow-sm transition-colors duration-300',
                      gIdx === selectedGroupIdx || group.isActive ? 'text-2xl text-yellow-400' : 'text-lg text-slate-400'
                    ]">{{ group.time }}</span>
                    <div class="flex items-center gap-2">
                      <span v-if="group.items.length > 1" :class="[
                        'text-xs font-bold px-2 py-0.5 rounded-full',
                        gIdx === selectedGroupIdx || group.isActive ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-400/50' : 'bg-white/10 text-slate-400'
                      ]">{{ group.items.length }} kegiatan</span>
                      <span v-if="group.isActive"
                            class="bg-yellow-500/20 text-yellow-300 text-xs font-bold px-2 py-1 rounded border border-yellow-400/50 flex items-center gap-1 shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> AKTIF
                      </span>
                      <!-- Collapse Toggle Button -->
                      <button class="p-1 rounded hover:bg-white/10 transition-colors text-slate-400" title="Buka/Tutup Jadwal">
                        <span class="material-symbols-outlined text-sm transition-transform duration-300" :class="{ 'rotate-180': closedGroups.includes(gIdx) }">
                          expand_less
                        </span>
                      </button>
                    </div>
                  </div>

                  <!-- Items in this time group -->
                  <div v-show="!closedGroups.includes(gIdx)">
                  <div v-for="(item, iIdx) in group.items" :key="item.id"
                       :class="[
                         'transition-colors duration-300',
                         group.items.length > 1 && iIdx > 0 ? 'mt-3 pt-3' : 'mt-1',
                         group.items.length > 1 && iIdx > 0 && gIdx === selectedGroupIdx ? 'border-t border-yellow-500/20' : (group.items.length > 1 && iIdx > 0 ? 'border-t border-transparent' : '')
                       ]">
                    <h3 :class="[
                      'font-medium transition-colors duration-300',
                      gIdx === selectedGroupIdx || group.isActive ? 'text-lg font-bold text-white' : 'text-base',
                      !(gIdx === selectedGroupIdx || group.isActive) && group.isPast ? 'text-slate-300' : '',
                      !(gIdx === selectedGroupIdx || group.isActive) && !group.isPast ? 'text-white' : ''
                    ]">
                      <span v-if="group.items.length > 1" class="material-symbols-outlined text-[14px] mr-1 align-middle" :class="gIdx === selectedGroupIdx || group.isActive ? 'text-yellow-400' : 'text-slate-500'">{{ item.icon }}</span>
                      {{ item.title }}
                    </h3>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                      <span :class="[
                        'text-sm flex items-center gap-1 transition-colors duration-300',
                        gIdx === selectedGroupIdx || group.isActive ? 'text-yellow-200 font-medium' : group.isPast ? 'text-slate-500' : 'text-slate-400'
                      ]">
                        <span v-if="gIdx === selectedGroupIdx || group.isActive" class="material-symbols-outlined text-sm text-yellow-400">location_on</span>
                        {{ item.location }}
                      </span>
                    </div>
                  </div>
                  <!-- Detail Button -->
                  <button @click.stop="openModal(gIdx)"
                          class="mt-4 w-full bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 border border-blue-500/30 rounded-lg py-2 text-sm font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer active:scale-95">
                    <span class="material-symbols-outlined text-base">info</span>
                    Lihat Detail
                  </button>
                </div>
                </div>
              </div>
            </div>
          </SimpleBar>
        </div>

        <!-- ═══════ RIGHT: DETAIL PANEL ═══════ -->
        <div id="detail-panel" class="hidden md:flex portrait:hidden w-2/3 flex-col gap-6 md:h-[calc(100vh-10rem)]">
          <!-- Main Detail Card -->
          <div class="flex-1 min-h-0 glass-panel rounded-2xl p-1 overflow-hidden shadow-2xl flex flex-col relative group border-t border-t-yellow-500/20">
            <div class="absolute inset-0 z-0">
              <img alt="Background" class="w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-[20s] ease-linear grayscale-20 sepia-20 hue-rotate-180 brightness-75 contrast-125"
                   :src="selectedGroup.items?.[activeItemIdx]?.image || '/img/default-agenda.png'" />
              <div class="absolute inset-0 bg-linear-to-t from-bg-deepest via-bg-deepest/80 to-blue-900/20 mix-blend-multiply"></div>
              <div class="absolute inset-0 bg-linear-to-t from-blue-950 via-transparent to-transparent opacity-80"></div>
            </div>
            <div class="relative z-10 flex flex-col h-full p-4 md:p-8 pb-4 md:pb-6 justify-end overflow-y-auto custom-scrollbar">
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
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm font-bold rounded-md uppercase tracking-wider border border-blue-400/20 flex items-center gap-1">
                      <span class="material-symbols-outlined text-[16px]">schedule</span>
                      {{ selectedGroup.time }}
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
        </div>
      </main>

      <!-- ═══════ MOBILE DETAIL MODAL ═══════ -->
      <Transition name="detail">
        <div v-if="showMobileModal" @click.self="showMobileModal = false" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:hidden portrait:flex bg-[#020617]/90 backdrop-blur-sm">
          <div class="relative w-full max-w-lg h-auto max-h-[90vh] glass-panel rounded-2xl p-1 overflow-hidden shadow-2xl flex flex-col group border-t border-t-yellow-500/20">
            <!-- Close Button -->
            <button @click="showMobileModal = false" class="absolute top-4 right-4 z-50 size-10 rounded-full bg-red-500 text-white flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors cursor-pointer border border-white/20">
              <span class="material-symbols-outlined text-xl">close</span>
            </button>
            <div class="absolute inset-0 z-0">
              <img alt="Background" class="w-full h-full object-cover opacity-50 grayscale-20 sepia-20 hue-rotate-180 brightness-75 contrast-125"
                   :src="selectedGroup.items?.[activeItemIdx]?.image || '/img/default-agenda.png'" />
              <div class="absolute inset-0 bg-linear-to-t from-bg-deepest via-bg-deepest/80 to-blue-900/20 mix-blend-multiply"></div>
              <div class="absolute inset-0 bg-linear-to-t from-blue-950 via-transparent to-transparent opacity-80"></div>
            </div>
            <div class="relative z-10 flex flex-col h-full p-4 pb-4 justify-end overflow-y-auto no-scrollbar">
              <!-- Top Info -->
              <div class="flex items-start gap-4 mb-4 mt-12">
                <div class="bg-blue-900/40 backdrop-blur-md border border-yellow-500/30 p-3 rounded-xl text-yellow-400 shrink-0">
                  <span class="material-symbols-outlined text-4xl">{{ selectedGroup.items?.[activeItemIdx]?.icon || 'event' }}</span>
                </div>
                <div>
                  <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-2 py-1 bg-yellow-500 text-blue-950 text-[10px] font-bold rounded uppercase tracking-wider">
                      {{ selectedGroup.isActive ? 'Sedang Berlangsung' : 'Detail Kegiatan' }}
                    </span>
                    <span class="px-2 py-1 bg-blue-500/20 text-blue-300 text-[10px] font-bold rounded uppercase tracking-wider flex items-center gap-1 border border-blue-400/20">
                      <span class="material-symbols-outlined text-[12px]">schedule</span>
                      {{ selectedGroup.time }}
                    </span>
                    <span :class="categoryBadgeClass(selectedGroup.items?.[activeItemIdx]?.category)">{{ selectedGroup.items?.[activeItemIdx]?.category }}</span>
                    <!-- Multi-item tabs -->
                    <div v-if="selectedGroup.items?.length > 1" class="flex items-center gap-1 mt-1 w-full">
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
                  <h2 class="text-2xl font-bold text-white tracking-tight leading-tight mb-2 drop-shadow-lg">{{ selectedGroup.items?.[activeItemIdx]?.title }}</h2>
                </div>
              </div>
              
              <!-- Bottom Info -->
              <div class="grid grid-cols-1 gap-3 mt-4">
                <div class="flex gap-2 flex-col sm:flex-row">
                  <div class="flex-1 bg-blue-950/70 backdrop-blur-md p-3 rounded-xl border border-yellow-500/20 flex items-center gap-3">
                    <div class="bg-yellow-500/20 p-2 rounded-full text-yellow-400 ring-1 ring-yellow-500/30 shrink-0">
                      <span class="material-symbols-outlined text-2xl">location_on</span>
                    </div>
                    <div>
                      <p class="text-xs text-slate-400 uppercase tracking-wide">Lokasi</p>
                      <p class="text-base font-bold text-white">{{ selectedGroup.items?.[activeItemIdx]?.location }}</p>
                    </div>
                  </div>
                  <div class="flex-1 bg-blue-950/70 backdrop-blur-md p-3 rounded-xl border border-yellow-500/20 flex items-center gap-3">
                    <div class="bg-yellow-500/20 p-2 rounded-full text-yellow-400 ring-1 ring-yellow-500/30 shrink-0">
                      <span class="material-symbols-outlined text-2xl">group</span>
                    </div>
                    <div>
                      <p class="text-xs text-slate-400 uppercase tracking-wide">Pengajar</p>
                      <p class="text-base font-bold text-white">{{ selectedGroup.items?.[activeItemIdx]?.teacher }}</p>
                    </div>
                  </div>
                </div>
                <!-- Lihat Selengkapnya Button -->
                <button @click="router.push({ name: 'DetailAgenda', params: { id: selectedGroup.items?.[activeItemIdx]?.id } })"
                        class="w-full bg-accent/10 backdrop-blur-md px-4 py-3 rounded-xl border border-accent/40 flex items-center justify-center gap-2 hover:bg-accent/20 hover:border-accent transition-all cursor-pointer">
                  <span class="material-symbols-outlined text-accent text-lg">open_in_full</span>
                  <span class="text-base font-bold text-accent">
                    Selengkapnya {{ selectedGroup.items?.length > 1 ? `(${selectedGroup.items.length} konten)` : '' }}
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import SimpleBar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
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
const showMobileModal = ref(false)
const closedGroups = ref([])

const selectedGroup = computed(() => timeGroups.value[selectedGroupIdx.value] || { items: [] })

// Reset active item when changing group
watch(selectedGroupIdx, () => { activeItemIdx.value = 0 })

function selectGroup(gIdx) {
  selectedGroupIdx.value = gIdx
}

function openModal(gIdx) {
  selectedGroupIdx.value = gIdx
  showMobileModal.value = true
}

function toggleCollapse(gIdx) {
  if (closedGroups.value.includes(gIdx)) {
    closedGroups.value = closedGroups.value.filter(i => i !== gIdx)
  } else {
    closedGroups.value.push(gIdx)
  }
}

const isAllExpanded = computed(() => closedGroups.value.length === 0)

function toggleAll() {
  if (isAllExpanded.value) {
    closedGroups.value = timeGroups.value.map((_, i) => i)
  } else {
    closedGroups.value = []
  }
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
