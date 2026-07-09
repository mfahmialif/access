<template>
  <div class="relative w-screen h-screen overflow-hidden font-display antialiased"
       :class="!isDark ? 'text-slate-800 bg-slate-50' : 'text-white bg-[#0a192f]'">
    <!-- ═══════ PATTERN + GRADIENT ═══════ -->
    <div class="absolute inset-0 pointer-events-none z-0"
         :style="{ backgroundImage: patternBg, backgroundSize: '30px 30px' }"></div>
    <div class="absolute inset-0 pointer-events-none z-0"
         :class="!isDark ? 'bg-linear-to-b from-slate-100/90 via-white/80 to-slate-100/90' : 'bg-linear-to-b from-[#0a192f]/95 via-[#0f2540]/80 to-[#0a192f]/95'"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col h-full p-4 md:p-8 gap-4 md:gap-5 max-w-[1920px] mx-auto">

      <!-- ═══════ HEADER ═══════ -->
      <!-- ═══════ HEADER ═══════ -->
      <PublicHeader :showBack="true" />

      <!-- ═══════ WEEKLY CARDS — DESKTOP / LANDSCAPE ═══════ -->
      <main class="hidden md:flex flex-1 items-center justify-center w-full overflow-hidden py-2">
        <div class="flex items-center justify-center gap-3 w-full h-full min-w-0">
          <div v-for="(day, index) in weekDays" :key="'desk-'+day.name"
               @click="selectDay(index)"
               :class="[
                 'flex flex-col rounded-xl transition-all duration-500 cursor-pointer',
                 index === selectedDayIndex
                   ? (!isDark ? 'w-[200%] h-full bg-white border border-amber-300 rounded-2xl p-5 relative z-10 shadow-2xl shadow-amber-500/20' : 'w-[200%] h-full glass-card-active rounded-2xl p-5 relative z-10 shadow-2xl')
                   : (!isDark ? 'w-full h-[85%] bg-white border border-slate-200 p-4 scale-95 overflow-hidden shadow-lg hover:border-amber-300' : 'w-full h-[85%] glass-card p-4 scale-95 overflow-hidden hover:bg-blue-900/20'),
                 day.isPast && index !== selectedDayIndex ? (!isDark ? 'opacity-60 grayscale-[30%]' : 'opacity-50') : ''
               ]">
            <!-- Badge -->
            <div v-if="index === selectedDayIndex" class="absolute -top-3 left-1/2 -translate-x-1/2 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider border z-20"
                 :class="!isDark ? 'bg-amber-500 text-white border-amber-400 shadow-lg' : 'bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.6)] border-white/20'">
              {{ day.isToday ? 'Hari Ini' : day.name }}
            </div>

            <!-- Day Header -->
            <div :class="['flex justify-between items-start border-b pb-3 mb-3', index === selectedDayIndex ? (!isDark ? 'border-slate-200 pb-4 mb-4 mt-2' : 'border-blue-400/30 pb-4 mb-4 mt-2') : (!isDark ? 'border-slate-100' : 'border-white/10')]">
              <div>
                <h3 :class="[
                  'font-bold uppercase',
                  index === selectedDayIndex ? (!isDark ? 'text-3xl text-amber-500 tracking-tight drop-shadow-sm' : 'text-3xl text-accent tracking-tight drop-shadow-sm') : 'text-lg',
                  day.name === 'Minggu' && index !== selectedDayIndex ? 'text-red-500' : '',
                  index !== selectedDayIndex && day.name !== 'Minggu' ? (!isDark ? (day.isPast ? 'text-slate-400' : 'text-slate-600') : (day.isPast ? 'text-blue-100/80' : 'text-blue-100')) : ''
                ]">{{ day.name }}</h3>
              </div>
              <span v-if="day.isPast && index !== selectedDayIndex" class="material-symbols-outlined" :class="!isDark ? 'text-slate-300' : 'text-blue-200/30'">check_circle</span>
              <div v-if="index === selectedDayIndex" class="size-10 rounded-full flex items-center justify-center border"
                   :class="!isDark ? 'bg-amber-50 border-amber-200 text-amber-500' : 'bg-blue-900/40 border-blue-500/20 text-accent'">
                <span class="material-symbols-outlined" :class="!isDark ? 'text-amber-500' : 'text-accent'">calendar_today</span>
              </div>
            </div>

            <!-- Agenda Items -->
            <simplebar :class="['agenda-scroll', index === selectedDayIndex ? 'flex-1 min-h-0' : 'max-h-[40vh]']" :force-visible="true" :click-on-track="true">
              <div :class="['flex flex-col', index === selectedDayIndex ? 'gap-4' : 'gap-3']">
              <template v-if="index === selectedDayIndex">
                <div v-for="group in day.timeGroups" :key="group.time"
                     @click.stop="router.push({ name: 'DetailWeekly', params: { id: group.items[0].id } })"
                     class="flex gap-4 items-start p-3 rounded-lg transition-colors cursor-pointer border"
                     :class="!isDark ? 'bg-slate-50 border-slate-200 hover:bg-amber-50 hover:border-amber-300' : 'bg-blue-900/20 border-blue-500/20 hover:bg-blue-800/30'">
                  <div class="flex flex-col items-center justify-center min-w-[55px] border-r pr-3" :class="!isDark ? 'border-slate-200' : 'border-white/10'">
                    <span class="text-lg font-bold" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ group.time }}</span>
                    <span v-if="group.items.length > 1" class="text-[10px] mt-0.5" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">{{ group.items.length }} kegiatan</span>
                  </div>
                  <div class="flex flex-col flex-1 min-w-0 gap-1.5">
                    <div v-for="(item, iIdx) in group.items" :key="item.id" :class="[iIdx > 0 ? (!isDark ? 'pt-1.5 border-t border-slate-200' : 'pt-1.5 border-t border-white/5') : '']">
                      <span class="font-bold text-base leading-snug truncate block" :class="!isDark ? 'text-slate-800' : 'text-white'">
                        <span v-if="group.items.length > 1" class="material-symbols-outlined text-[14px] mr-1 align-middle" :class="!isDark ? 'text-amber-500' : 'text-yellow-400'">{{ item.icon || 'event' }}</span>
                        {{ item.title }}
                      </span>
                      <div class="flex items-center gap-2 mt-0.5">
                        <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                        <span class="text-xs flex items-center gap-1 truncate" :class="!isDark ? 'text-slate-500' : 'text-blue-200/80'">
                          <span class="material-symbols-outlined text-[12px]" :class="!isDark ? 'text-amber-500' : 'text-accent'">location_on</span> {{ item.location || '-' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="day.timeGroups.length === 0" class="flex items-center justify-center h-full pb-4">
                  <span class="italic text-sm" :class="!isDark ? 'text-slate-400' : 'text-blue-200/30'">Tidak ada agenda</span>
                </div>
              </template>
              <template v-else>
                <div v-if="day.timeGroups.length === 0" class="flex items-center justify-center h-full pb-4">
                  <span class="italic text-sm" :class="!isDark ? 'text-slate-400' : 'text-blue-200/30'">Tidak ada agenda</span>
                </div>
                <div v-for="group in day.timeGroups" :key="group.time"
                     @click.stop="router.push({ name: 'DetailWeekly', params: { id: group.items[0].id } })"
                     class="flex flex-col gap-0.5 cursor-pointer hover:opacity-80 transition-opacity">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-bold" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ group.time }}</span>
                    <span v-if="group.items.length > 1" class="text-[8px]" :class="!isDark ? 'text-slate-400' : 'text-yellow-300/70'">×{{ group.items.length }}</span>
                    <span :class="categoryBadgeSmall(group.items[0].category)">{{ group.items[0].category?.charAt(0) }}</span>
                  </div>
                  <span :class="['text-sm leading-tight', day.isPast ? (!isDark ? 'text-slate-400 line-through decoration-slate-300' : 'text-blue-100/70 line-through decoration-blue-200/30') : (!isDark ? 'text-slate-700' : 'text-white')]">
                    {{ group.items[0].title }}{{ group.items.length > 1 ? ` +${group.items.length - 1}` : '' }}
                  </span>
                </div>
              </template>
              </div>
            </simplebar>
          </div>
        </div>
      </main>

      <!-- ═══════ WEEKLY — MOBILE / PORTRAIT (Tab Layout) ═══════ -->
      <main class="md:hidden flex-1 flex flex-col w-full overflow-hidden gap-3">
        <!-- Day Selector Tabs -->
        <div class="flex gap-1.5 overflow-x-auto no-scrollbar shrink-0 px-1 py-1">
          <button v-for="(day, index) in weekDays" :key="'mob-'+day.name"
                  @click="selectDay(index)"
                  :class="[
                    'flex flex-col items-center px-3 py-2 rounded-xl transition-all duration-300 cursor-pointer shrink-0 min-w-[52px]',
                    index === selectedDayIndex
                      ? (!isDark ? 'bg-amber-500 text-white shadow-md' : 'bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.4)]')
                      : day.isPast
                        ? (!isDark ? 'bg-slate-100 text-slate-400 border border-slate-200' : 'bg-white/5 text-slate-500')
                        : (!isDark ? 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' : 'bg-white/5 text-white hover:bg-white/10'),
                    day.name === 'Minggu' && index !== selectedDayIndex ? 'text-red-500!' : ''
                  ]">
            <span class="text-[10px] font-bold uppercase tracking-wider">{{ day.name.substring(0, 3) }}</span>
            <span v-if="day.timeGroups?.length" :class="[
              'text-[8px] font-bold mt-0.5 px-1.5 rounded-full',
              index === selectedDayIndex ? (!isDark ? 'bg-amber-600/50' : 'bg-[#0a192f]/20') : (!isDark ? 'bg-amber-100 text-amber-600' : 'bg-accent/20 text-accent')
            ]">{{ day.timeGroups.reduce((a, g) => a + g.items.length, 0) }}</span>
            <div v-if="day.isToday && index !== selectedDayIndex" class="w-1 h-1 rounded-full mt-0.5" :class="!isDark ? 'bg-amber-500' : 'bg-accent'"></div>
          </button>
        </div>

        <!-- Selected Day Content -->
        <div class="flex-1 rounded-2xl p-4 overflow-hidden flex flex-col relative"
             :class="!isDark ? 'bg-white shadow-xl border border-slate-200' : 'glass-card-active'">
          <!-- Day Title -->
          <div class="flex items-center justify-between border-b pb-3 mb-3" :class="!isDark ? 'border-slate-200' : 'border-blue-400/30'">
            <div class="flex items-center gap-2">
              <h3 class="text-xl font-bold uppercase tracking-tight" :class="!isDark ? 'text-amber-500' : 'text-accent'">{{ weekDays[selectedDayIndex]?.name }}</h3>
              <span v-if="weekDays[selectedDayIndex]?.isToday" class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                    :class="!isDark ? 'bg-amber-100 text-amber-600 border-amber-200' : 'bg-accent/20 text-accent border-accent/30'">Hari Ini</span>
            </div>
            <div class="size-8 rounded-full flex items-center justify-center border"
                 :class="!isDark ? 'bg-amber-50 text-amber-500 border-amber-200' : 'bg-blue-900/40 text-accent border-blue-500/20'">
              <span class="material-symbols-outlined text-lg">calendar_today</span>
            </div>
          </div>

          <!-- Agenda List -->
          <simplebar class="agenda-scroll flex-1 min-h-0" :force-visible="true" :click-on-track="true">
            <div class="flex flex-col gap-3">
              <template v-if="weekDays[selectedDayIndex]?.timeGroups?.length">
                <div v-for="group in weekDays[selectedDayIndex].timeGroups" :key="group.time"
                     @click="router.push({ name: 'DetailWeekly', params: { id: group.items[0].id } })"
                     class="flex gap-3 items-start p-3 rounded-xl transition-colors cursor-pointer active:scale-[0.98] border"
                     :class="!isDark ? 'bg-slate-50 border-slate-200 hover:bg-amber-50' : 'bg-blue-900/20 border-blue-500/20 hover:bg-blue-800/30'">
                  <div class="flex flex-col items-center justify-center min-w-[48px] border-r pr-2" :class="!isDark ? 'border-slate-200' : 'border-white/10'">
                    <span class="text-base font-bold" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ group.time }}</span>
                    <span v-if="group.items.length > 1" class="text-[9px] mt-0.5" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">{{ group.items.length }} item</span>
                  </div>
                  <div class="flex flex-col flex-1 min-w-0 gap-1">
                    <div v-for="(item, iIdx) in group.items" :key="item.id" :class="[iIdx > 0 ? (!isDark ? 'pt-1.5 border-t border-slate-200' : 'pt-1.5 border-t border-white/5') : '']">
                      <span class="font-bold text-sm leading-snug truncate block" :class="!isDark ? 'text-slate-800' : 'text-white'">
                        <span v-if="group.items.length > 1" class="material-symbols-outlined text-[13px] mr-1 align-middle" :class="!isDark ? 'text-amber-500' : 'text-yellow-400'">{{ item.icon || 'event' }}</span>
                        {{ item.title }}
                      </span>
                      <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                        <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                        <span class="text-[11px] flex items-center gap-0.5 truncate" :class="!isDark ? 'text-slate-500' : 'text-blue-200/80'">
                          <span class="material-symbols-outlined text-[11px]" :class="!isDark ? 'text-amber-500' : 'text-accent'">location_on</span> {{ item.location || '-' }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <span class="material-symbols-outlined text-lg shrink-0 self-center" :class="!isDark ? 'text-slate-300' : 'text-white/20'">chevron_right</span>
                </div>
              </template>
              <div v-else class="flex flex-col items-center justify-center py-12 gap-3">
                <span class="material-symbols-outlined text-4xl" :class="!isDark ? 'text-slate-200' : 'text-white/15'">event_busy</span>
                <span class="italic text-sm" :class="!isDark ? 'text-slate-400' : 'text-blue-200/30'">Tidak ada agenda</span>
              </div>
            </div>
          </simplebar>
        </div>
      </main>


    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import { usePublicTheme } from '../../../composables/usePublicTheme'
import PublicHeader from '../../../components/PublicHeader.vue'

const router = useRouter()
const { isDark } = usePublicTheme()

onMounted(() => { loadWeeklies() })

// ── Category Badges ──
function categoryBadgeClass(cat) {
  const b = 'px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border'
  if (isDark.value) {
    return cat === 'Artikel' ? `${b} bg-accent/20 text-accent border-accent/40`
         : cat === 'Gambar' ? `${b} bg-blue-500/20 text-blue-400 border-blue-500/40`
         : `${b} bg-red-500/20 text-red-400 border-red-500/40`
  }
  return cat === 'Artikel' ? `${b} bg-amber-100 text-amber-700 border-amber-300`
       : cat === 'Gambar' ? `${b} bg-blue-100 text-blue-700 border-blue-300`
       : `${b} bg-red-100 text-red-700 border-red-300`
}
function categoryBadgeSmall(cat) {
  const b = 'w-4 h-4 rounded flex items-center justify-center text-[8px] font-bold'
  if (isDark.value) {
    return cat === 'Artikel' ? `${b} bg-accent/20 text-accent`
         : cat === 'Gambar' ? `${b} bg-blue-500/20 text-blue-400`
         : `${b} bg-red-500/20 text-red-400`
  }
  return cat === 'Artikel' ? `${b} bg-amber-100 text-amber-700`
       : cat === 'Gambar' ? `${b} bg-blue-100 text-blue-700`
       : `${b} bg-red-100 text-red-700`
}

// ── Transform item for detail components ──
function transformItem(item) {
  return {
    ...item,
    time: item.time?.substring(0, 5),
    description: item.body,
    image: item.image_path ? storageUrl(item.image_path) : '/img/default-agenda.png',
    videoUrl: item.video_path ? storageUrl(item.video_path) : null,
    speaker: item.teacher,
    date: `${item.day}`,
  }
}



// ── Selected Day ──
const selectedDayIndex = ref(0)
function selectDay(index) { selectedDayIndex.value = index }

// ── Group items by time within a day ──
function groupByTime(items) {
  const groups = []
  const groupMap = {}
  for (const item of items) {
    const t = item.time?.substring(0, 5) || '00:00'
    if (!groupMap[t]) {
      groupMap[t] = { time: t, items: [] }
      groups.push(groupMap[t])
    }
    groupMap[t].items.push(item)
  }
  return groups
}

// ── Week Data from API ──
const dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']
const weekDays = ref([])

async function loadWeeklies() {
  try {
    const { data } = await api.get('/weeklies', { params: { per_page: 100, status: 'Aktif' } })
    const items = data.data || []

    const now = new Date()
    const todayDayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][now.getDay()]
    const todayIdx = dayOrder.indexOf(todayDayName)

    const grouped = {}
    for (const d of dayOrder) {
      grouped[d] = []
    }
    for (const item of items) {
      if (grouped[item.day]) {
        grouped[item.day].push(item)
      }
    }

    weekDays.value = dayOrder.map((name, idx) => {
      const sortedItems = grouped[name].sort((a, b) => (a.time || '').localeCompare(b.time || ''))
      return {
        name,
        isToday: name === todayDayName,
        isPast: idx < todayIdx,
        items: sortedItems,
        timeGroups: groupByTime(sortedItems),
      }
    })

    // Default select today
    selectedDayIndex.value = todayIdx >= 0 ? todayIdx : 0
  } catch (e) {
    console.error('Failed to load weeklies:', e)
    weekDays.value = dayOrder.map(name => ({ name, isToday: false, isPast: false, items: [], timeGroups: [] }))
  }
}

// ── Pattern ──
const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`
</script>

<style scoped>
.glass-card {
  background: rgba(13, 25, 48, 0.4);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(100, 149, 237, 0.15);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
.glass-card-active {
  background: linear-gradient(145deg, rgba(30, 58, 138, 0.6), rgba(10, 25, 47, 0.9));
  backdrop-filter: blur(16px);
  border: 2px solid var(--color-accent);
  box-shadow: 0 0 30px rgba(251, 191, 36, 0.3), inset 0 0 20px rgba(30, 58, 138, 0.5);
}

.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }

.animate-marquee {
  animation: marquee 30s linear infinite;
}
@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}

/* ═══ SimpleBar Overrides ═══ */
.agenda-scroll :deep(.simplebar-scrollbar::before) {
  background: rgba(251, 191, 36, 0.4);
  border-radius: 999px;
  opacity: 0.5;
  transition: opacity 0.3s ease;
}
.agenda-scroll :deep(.simplebar-scrollbar.simplebar-visible::before) { opacity: 0.7; }
.agenda-scroll:hover :deep(.simplebar-scrollbar::before) { opacity: 0.6; }
.agenda-scroll :deep(.simplebar-scrollbar:hover::before) { background: rgba(251, 191, 36, 0.8); opacity: 1; }
.agenda-scroll :deep(.simplebar-track.simplebar-vertical) { width: 10px; right: 2px; }
.agenda-scroll :deep(.simplebar-track.simplebar-horizontal) { display: none; }
</style>
