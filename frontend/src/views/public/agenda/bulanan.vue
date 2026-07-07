<template>
  <div class="relative w-screen h-screen flex flex-col overflow-hidden text-white font-display antialiased bg-[#0a192f]">
    <!-- ═══════ AMBIENT ═══════ -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-accent/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- ═══════ HEADER ═══════ -->
    <header class="flex-none h-14 md:h-18 px-4 md:px-8 flex items-center justify-between border-b border-white/10 bg-[#0b1711]/50 backdrop-blur-md z-50">
      <div class="flex items-center gap-3 md:gap-4">
        <button @click="goBack"
                class="size-9 md:size-11 rounded-lg bg-[#0f172a]/80 border border-accent/30 flex items-center justify-center text-accent hover:bg-accent hover:text-[#0a192f] transition-all duration-300 cursor-pointer">
          <span class="material-symbols-outlined text-lg md:text-xl">arrow_back</span>
        </button>
        <div class="size-8 md:size-10 text-accent flex items-center justify-center">
          <span class="material-symbols-outlined text-2xl md:text-3xl">live_tv</span>
        </div>
        <div class="flex flex-col">
          <h1 class="text-base md:text-xl font-bold tracking-tight text-white uppercase leading-none">Access</h1>
          <span class="text-[10px] md:text-xs text-accent/80 font-medium tracking-widest uppercase">Sistem Informasi</span>
        </div>
      </div>
      <div class="hidden md:flex items-center gap-6">
        <div class="flex flex-col items-end text-right">
          <span class="text-xl font-bold text-white leading-none tabular-nums">{{ currentTime }} <span class="text-sm text-blue-200/50">WIB</span></span>
          <span class="text-sm text-accent font-medium">{{ hijriDate }}</span>
          <span class="text-xs text-slate-400">{{ currentDate }}</span>
        </div>
      </div>
    </header>

    <!-- ═══════ MAIN CONTENT ═══════ -->
    <main class="flex-1 flex flex-col md:flex-row portrait:flex-col overflow-hidden p-2 md:p-6 gap-3 md:gap-6 relative">

      <!-- ═══ LEFT: CALENDAR GRID (70%) ═══ -->
      <section class="flex-1 md:flex-[0.7] flex flex-col gap-4 h-full">
        <!-- Calendar Header -->
        <div class="glass-panel rounded-2xl p-3 md:p-5 flex items-center justify-between">
          <div>
            <h2 class="text-xl md:text-3xl font-bold text-white tracking-tight">{{ monthYearDisplay }}</h2>
            <p class="text-accent text-sm md:text-base font-medium mt-1">{{ hijriMonthDisplay }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="prevMonth" class="size-8 md:size-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition-colors border border-white/5">
              <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button @click="nextMonth" class="size-8 md:size-10 rounded-xl bg-white/5 hover:bg-white/10 flex items-center justify-center text-white transition-colors border border-white/5">
              <span class="material-symbols-outlined">chevron_right</span>
            </button>
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="glass-panel rounded-2xl flex-1 p-2 md:p-5 flex flex-col">
          <!-- Days Header -->
          <div class="grid grid-cols-7 mb-3 border-b border-white/10 pb-3">
            <div v-for="d in dayHeaders" :key="d.name"
                 :class="['text-center font-bold text-[10px] md:text-sm tracking-wider uppercase', d.isJumat ? 'text-accent' : 'text-slate-400']">
              {{ d.name }}
            </div>
          </div>
          <!-- Days Grid -->
          <div class="grid grid-cols-7 gap-1 md:gap-2 flex-1" style="grid-auto-rows: 1fr">
            <div v-for="(cell, ci) in calendarCells" :key="ci"
                 @click="cell.day && cell.isCurrentMonth ? selectDate(cell.day) : null"
                 :class="[
                   'relative p-1 md:p-2 rounded-lg md:rounded-xl flex flex-col items-start justify-start transition-all duration-300',
                   !cell.isCurrentMonth ? 'text-slate-600' : '',
                   cell.isCurrentMonth && !cell.isSelected ? 'bg-[#0b1711]/40 hover:bg-[#0b1711]/60 text-white border border-white/5 cursor-pointer' : '',
                   cell.isSelected ? 'calendar-cell-active text-white cursor-pointer' : ''
                 ]">
              <span :class="['font-bold', cell.isSelected ? 'text-sm md:text-xl text-accent' : 'text-xs md:text-base']">{{ cell.day || '' }}</span>
              <!-- Event dots -->
              <div v-if="cell.events && cell.events.length" class="flex gap-1 mt-1">
                <div v-for="(ev, ei) in cell.events.slice(0, 3)" :key="ei"
                     :class="['h-1.5 w-1.5 rounded-full', categoryDotColor(ev.category)]"></div>
                <div v-if="cell.events.length > 3" class="text-[8px] text-slate-400 ml-0.5">+{{ cell.events.length - 3 }}</div>
              </div>
              <span v-if="cell.events && cell.events.length && cell.isCurrentMonth"
                    :class="['material-symbols-outlined absolute bottom-1 right-1 text-lg', cell.isSelected ? 'text-accent animate-pulse' : 'text-white/20']">
                {{ cell.events[0].icon || 'event' }}
              </span>
              <span v-if="cell.isToday && cell.isSelected" class="text-[8px] uppercase font-bold text-accent tracking-wider mt-0.5">Hari Ini</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══ RIGHT: EVENTS PANEL (30%) ═══ -->
      <aside class="hidden md:flex portrait:hidden flex-[0.3] min-h-[200px] md:min-h-0 flex-col h-full gap-4">
        <div class="events-panel h-full rounded-2xl flex flex-col overflow-hidden">

          <!-- ─── Selected Date Events ─── -->
          <template v-if="selectedDateEvents.length > 0 && !showAllMonth">
            <div class="p-5 border-b border-white/10 bg-[#0b1711]/50 flex flex-col gap-2">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                  <span class="material-symbols-outlined text-accent">event_note</span>
                  {{ selectedDay }} {{ months[currentMonth] }}
                </h3>
                <span class="text-sm text-accent font-medium">{{ selectedDateEvents.length }} event</span>
              </div>
              <button @click="showAllMonth = true" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg bg-accent/10 border border-accent/30 text-accent text-xs font-bold hover:bg-accent/20 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                Event Bulan Ini
              </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
              <!-- Grouped by time -->
              <div v-for="group in selectedDateGroups" :key="group.time">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-accent font-bold text-lg font-mono">{{ group.time }}</span>
                  <div class="h-px bg-white/10 flex-1"></div>
                  <span v-if="group.items.length > 1" class="text-[10px] text-yellow-300 font-bold">{{ group.items.length }} kegiatan</span>
                </div>
                <div v-for="(item, iIdx) in group.items" :key="item.id"
                     @click="router.push({ name: 'DetailMonthly', params: { id: item.id } })"
                     :class="[
                       'rounded-xl p-4 cursor-pointer transition-all duration-300 hover:scale-[1.02]',
                       'bg-linear-to-br from-blue-900/40 to-[#0b1711] border border-blue-500/20 hover:border-accent/50',
                       iIdx > 0 ? 'mt-2' : ''
                     ]">
                  <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 bg-accent/10 border border-accent/30">
                      <span class="material-symbols-outlined text-accent text-xl">{{ item.icon || 'event' }}</span>
                    </div>
                    <div class="flex flex-col flex-1 min-w-0">
                      <h4 class="text-base font-bold text-white leading-tight truncate">{{ item.title }}</h4>
                      <div class="flex items-center gap-2 mt-1">
                        <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                        <span class="text-slate-400 text-xs flex items-center gap-1">
                          <span class="material-symbols-outlined text-[12px]">location_on</span>
                          {{ item.location || '-' }}
                        </span>
                      </div>
                      <span v-if="item.teacher" class="text-xs text-blue-200/60 mt-0.5 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px]">person</span>
                        {{ item.teacher }}
                      </span>
                    </div>
                    <div class="flex items-center">
                      <span class="material-symbols-outlined text-white/30 text-lg">chevron_right</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- ─── All month events (fallback or toggled) ─── -->
          <template v-else>
            <div class="p-5 border-b border-white/10 bg-[#0b1711]/50 flex flex-col gap-2">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                  <span class="material-symbols-outlined text-accent">event_upcoming</span>
                  Event Bulan Ini
                </h3>
                <span class="text-sm text-accent font-medium">{{ monthEventGroups.reduce((a, g) => a + g.items.length, 0) }} event</span>
              </div>
              <button v-if="showAllMonth && selectedDateEvents.length > 0" @click="showAllMonth = false" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs font-bold hover:bg-white/10 transition-all cursor-pointer">
                <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                Kembali ke {{ selectedDay }} {{ months[currentMonth] }}
              </button>
              <p v-if="!showAllMonth && selectedDateEvents.length === 0" class="text-xs text-slate-400">Tidak ada event pada tanggal {{ selectedDay }}.</p>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
              <div v-for="group in monthEventGroups" :key="group.key"
                   @click="router.push({ name: 'DetailMonthly', params: { id: group.items[0].id } })"
                   :class="[
                     'rounded-xl p-4 cursor-pointer',
                     group.isToday
                       ? 'bg-linear-to-br from-blue-900/60 to-[#0b1711] border border-accent/40 relative overflow-hidden'
                       : 'bg-[#0b1711]/40 border border-white/5 hover:bg-[#0b1711]/60',
                     group.isPast ? 'opacity-50' : ''
                   ]">
                <div v-if="group.isToday" class="absolute top-0 right-0 p-2 opacity-10">
                  <span class="material-symbols-outlined text-7xl text-accent">{{ group.items[0].icon || 'event' }}</span>
                </div>
                <div class="flex gap-3 relative z-10">
                  <div class="flex flex-col items-center justify-center bg-white/5 rounded-lg w-14 h-14 shrink-0 border border-white/5">
                    <span class="text-[10px] text-slate-400 font-bold uppercase">{{ monthShort }}</span>
                    <span class="text-xl font-bold text-white leading-tight">{{ group.dateNum }}</span>
                  </div>
                  <div class="flex flex-col justify-center flex-1 min-w-0">
                    <h4 class="text-base font-bold text-white leading-tight truncate">
                      {{ group.items[0].title }}
                      <span v-if="group.items.length > 1" class="text-xs text-yellow-300 ml-1">+{{ group.items.length - 1 }}</span>
                    </h4>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span :class="categoryBadgeClass(group.items[0].category)">{{ group.items[0].category }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-slate-400 text-xs mt-0.5">
                      <span class="material-symbols-outlined text-sm">schedule</span>
                      <span>{{ group.time }} — {{ group.items[0].location || '-' }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="monthEventGroups.length === 0" class="flex flex-col items-center justify-center py-12 gap-3">
                <span class="material-symbols-outlined text-4xl text-white/20">event_busy</span>
                <p class="text-slate-400 text-sm">Tidak ada event bulan ini</p>
              </div>
            </div>
          </template>

          <div class="h-6 bg-linear-to-t from-[#0b1711] to-transparent w-full shrink-0"></div>
        </div>
      </aside>

      <!-- ═══════ MOBILE DETAIL MODAL ═══════ -->
      <Transition name="detail">
        <div v-if="showMobileModal" @click.self="showMobileModal = false" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:hidden portrait:flex bg-[#0a192f]/90 backdrop-blur-sm">
          <div class="relative w-full max-w-lg h-[80vh] max-h-[90vh] events-panel rounded-2xl flex flex-col overflow-hidden shadow-2xl border border-white/10">
            <!-- Close Button -->
            <button @click="showMobileModal = false" class="absolute top-4 right-4 z-50 size-8 rounded-full bg-red-500/80 text-white flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors cursor-pointer border border-white/20">
              <span class="material-symbols-outlined text-base">close</span>
            </button>
            
            <div class="flex flex-col h-full">
              <!-- ─── Selected Date Events ─── -->
              <template v-if="selectedDateEvents.length > 0 && !showAllMonth">
                <div class="p-5 border-b border-white/10 bg-[#0b1711]/50 flex flex-col gap-2 shrink-0 pr-14">
                  <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                      <span class="material-symbols-outlined text-accent">event_note</span>
                      {{ selectedDay }} {{ months[currentMonth] }}
                    </h3>
                    <span class="text-xs font-bold text-[#0a192f] bg-accent px-2 py-0.5 rounded-full shadow-sm">{{ selectedDateEvents.length }} event</span>
                  </div>
                  <button @click="showAllMonth = true" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg bg-accent/10 border border-accent/30 text-accent text-xs font-bold hover:bg-accent/20 transition-all cursor-pointer mt-2">
                    <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                    Event Bulan Ini
                  </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
                  <!-- Grouped by time -->
                  <div v-for="group in selectedDateGroups" :key="group.time">
                    <div class="flex items-center gap-2 mb-2">
                      <span class="text-accent font-bold text-lg font-mono">{{ group.time }}</span>
                      <div class="h-px bg-white/10 flex-1"></div>
                      <span v-if="group.items.length > 1" class="text-[10px] text-yellow-300 font-bold">{{ group.items.length }} kegiatan</span>
                    </div>
                    <div v-for="(item, iIdx) in group.items" :key="item.id"
                         @click="router.push({ name: 'DetailMonthly', params: { id: item.id } })"
                         :class="[
                           'rounded-xl p-4 cursor-pointer transition-all duration-300 hover:scale-[1.02]',
                           'bg-linear-to-br from-blue-900/40 to-[#0b1711] border border-blue-500/20 hover:border-accent/50',
                           iIdx > 0 ? 'mt-2' : ''
                         ]">
                      <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 bg-accent/10 border border-accent/30">
                          <span class="material-symbols-outlined text-accent text-xl">{{ item.icon || 'event' }}</span>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                          <h4 class="text-base font-bold text-white leading-tight truncate">{{ item.title }}</h4>
                          <div class="flex items-center gap-2 mt-1">
                            <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                            <span class="text-slate-400 text-xs flex items-center gap-1">
                              <span class="material-symbols-outlined text-[12px]">location_on</span>
                              {{ item.location || '-' }}
                            </span>
                          </div>
                          <span v-if="item.teacher" class="text-xs text-blue-200/60 mt-0.5 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[12px]">person</span>
                            {{ item.teacher }}
                          </span>
                        </div>
                        <div class="flex items-center">
                          <span class="material-symbols-outlined text-white/30 text-lg">chevron_right</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- ─── All month events (fallback or toggled) ─── -->
              <template v-else>
                <div class="p-5 border-b border-white/10 bg-[#0b1711]/50 flex flex-col gap-2 shrink-0 pr-14">
                  <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                      <span class="material-symbols-outlined text-accent">event_upcoming</span>
                      Event Bulan Ini
                    </h3>
                    <span class="text-xs font-bold text-[#0a192f] bg-accent px-2 py-0.5 rounded-full shadow-sm">{{ monthEventGroups.reduce((a, g) => a + g.items.length, 0) }} event</span>
                  </div>
                  <button v-if="showAllMonth && selectedDateEvents.length > 0" @click="showAllMonth = false" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs font-bold hover:bg-white/10 transition-all cursor-pointer mt-2">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke {{ selectedDay }} {{ months[currentMonth] }}
                  </button>
                  <p v-if="!showAllMonth && selectedDateEvents.length === 0" class="text-xs text-slate-400 mt-2">Tidak ada event pada tanggal {{ selectedDay }}.</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
                  <div v-for="group in monthEventGroups" :key="group.key"
                       @click="router.push({ name: 'DetailMonthly', params: { id: group.items[0].id } })"
                       :class="[
                         'rounded-xl p-4 cursor-pointer',
                         group.isToday
                           ? 'bg-linear-to-br from-blue-900/60 to-[#0b1711] border border-accent/40 relative overflow-hidden'
                           : 'bg-[#0b1711]/40 border border-white/5 hover:bg-[#0b1711]/60',
                         group.isPast ? 'opacity-50' : ''
                       ]">
                    <div v-if="group.isToday" class="absolute top-0 right-0 p-2 opacity-10">
                      <span class="material-symbols-outlined text-7xl text-accent">{{ group.items[0].icon || 'event' }}</span>
                    </div>
                    <div class="flex gap-3 relative z-10">
                      <div class="flex flex-col items-center justify-center bg-white/5 rounded-lg w-14 h-14 shrink-0 border border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase">{{ monthShort }}</span>
                        <span class="text-xl font-bold text-white leading-tight">{{ group.dateNum }}</span>
                      </div>
                      <div class="flex flex-col justify-center flex-1 min-w-0">
                        <h4 class="text-base font-bold text-white leading-tight truncate">
                          {{ group.items[0].title }}
                          <span v-if="group.items.length > 1" class="text-xs text-yellow-300 ml-1">+{{ group.items.length - 1 }}</span>
                        </h4>
                        <div class="flex items-center gap-2 mt-0.5">
                          <span :class="categoryBadgeClass(group.items[0].category)">{{ group.items[0].category }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-slate-400 text-xs mt-0.5">
                          <span class="material-symbols-outlined text-sm">schedule</span>
                          <span>{{ group.time }} — {{ group.items[0].location || '-' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="monthEventGroups.length === 0" class="flex flex-col items-center justify-center py-12 gap-3">
                    <span class="material-symbols-outlined text-4xl text-white/20">event_busy</span>
                    <p class="text-slate-400 text-sm">Tidak ada event bulan ini</p>
                  </div>
                </div>
              </template>
              <div class="h-6 bg-linear-to-t from-[#0b1711] to-transparent w-full shrink-0"></div>
            </div>
          </div>
        </div>
      </Transition>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const router = useRouter()
function goBack() { router.push({ name: 'Landing' }) }

// ── Time ──
const currentTime = ref('')
const currentDate = ref('')
const hijriDate = ref('')

function updateTime() {
  const now = new Date()
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${monthNames[now.getMonth()]} ${now.getFullYear()}`
  try {
    hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(now)
  } catch { hijriDate.value = '' }
}
let ti
onMounted(() => { updateTime(); ti = setInterval(updateTime, 1000); loadMonthlies() })
onUnmounted(() => clearInterval(ti))

// ── Calendar State ──
const today = new Date()
const currentMonth = ref(today.getMonth())
const currentYear = ref(today.getFullYear())
const selectedDay = ref(today.getDate())

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

const monthYearDisplay = computed(() => `${months[currentMonth.value]} ${currentYear.value}`)
const monthShort = computed(() => monthsShort[currentMonth.value])
const hijriMonthDisplay = computed(() => {
  try { return new Intl.DateTimeFormat('id-u-ca-islamic', { month: 'long', year: 'numeric' }).format(new Date(currentYear.value, currentMonth.value, 1)) }
  catch { return '' }
})

function prevMonth() {
  if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
  else currentMonth.value--
  selectedDay.value = 1
}
function nextMonth() {
  if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++ }
  else currentMonth.value++
  selectedDay.value = 1
}
const showAllMonth = ref(false)
const showMobileModal = ref(false)
function selectDate(day) { 
  selectedDay.value = day; 
  showAllMonth.value = false;
  showMobileModal.value = true;
}

watch([currentMonth, currentYear], () => loadMonthlies())

const dayHeaders = [
  { name: 'Min', isJumat: false }, { name: 'Sen', isJumat: false }, { name: 'Sel', isJumat: false },
  { name: 'Rab', isJumat: false }, { name: 'Kam', isJumat: false }, { name: 'Jum', isJumat: true },
  { name: 'Sab', isJumat: false }
]

// ── Events Data from API ──
const allMonthItems = ref([])

async function loadMonthlies() {
  try {
    const m = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}`
    const { data } = await api.get('/monthlies', { params: { per_page: 100, status: 'Aktif', month: m } })
    allMonthItems.value = data.data || []
  } catch (e) {
    console.error('Failed to load monthlies:', e)
    allMonthItems.value = []
  }
}

function getEventsForDate(day) {
  const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
  return allMonthItems.value.filter(item => item.date?.substring(0, 10) === dateStr)
}

// ── Calendar Cells ──
const calendarCells = computed(() => {
  const y = currentYear.value, m = currentMonth.value
  const firstDay = new Date(y, m, 1).getDay()
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const daysInPrevMonth = new Date(y, m, 0).getDate()
  const cells = []

  for (let i = firstDay - 1; i >= 0; i--) cells.push({ day: daysInPrevMonth - i, isCurrentMonth: false })
  for (let d = 1; d <= daysInMonth; d++) {
    const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear()
    cells.push({ day: d, isCurrentMonth: true, isToday, isSelected: d === selectedDay.value, events: getEventsForDate(d) })
  }
  const remaining = 7 - (cells.length % 7)
  if (remaining < 7) { for (let i = 1; i <= remaining; i++) cells.push({ day: i, isCurrentMonth: false }) }
  return cells
})

// ── Selected Date Events (for right panel) ──
const selectedDateEvents = computed(() => getEventsForDate(selectedDay.value))

const selectedDateGroups = computed(() => {
  const groups = []
  const groupMap = {}
  for (const item of selectedDateEvents.value) {
    const t = item.time?.substring(0, 5) || '00:00'
    if (!groupMap[t]) { groupMap[t] = { time: t, items: [] }; groups.push(groupMap[t]) }
    groupMap[t].items.push(item)
  }
  return groups
})

// ── Month Events Grouped by date+time (for fallback when no events on selected date) ──
const monthEventGroups = computed(() => {
  const y = currentYear.value, m = currentMonth.value
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const groups = []
  const groupMap = {}

  for (let d = 1; d <= daysInMonth; d++) {
    const events = getEventsForDate(d)
    for (const ev of events) {
      const time = ev.time?.substring(0, 5) || '00:00'
      const key = `${d}-${time}`
      if (!groupMap[key]) {
        const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear()
        const isPast = new Date(y, m, d) < new Date(today.getFullYear(), today.getMonth(), today.getDate())
        groupMap[key] = { key, dateNum: d, time, isToday, isPast, items: [] }
        groups.push(groupMap[key])
      }
      groupMap[key].items.push(ev)
    }
  }
  return groups
})

// ── Transform item for detail components ──
function transformItem(item) {
  const dateObj = item.date ? new Date(item.date) : new Date()
  return {
    ...item,
    time: item.time?.substring(0, 5),
    description: item.body,
    image: item.image_path ? storageUrl(item.image_path) : '/img/default-agenda.png',
    videoUrl: item.video_path ? storageUrl(item.video_path) : null,
    speaker: item.teacher,
    date: dateObj.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
  }
}




// ── Category Helpers ──
function categoryBadgeClass(cat) {
  const b = 'px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider'
  return cat === 'Artikel' ? `${b} bg-accent/20 text-accent border border-accent/40`
       : cat === 'Gambar' ? `${b} bg-blue-500/20 text-blue-400 border border-blue-500/40`
       : `${b} bg-red-500/20 text-red-400 border border-red-500/40`
}
function categoryDotColor(cat) {
  return cat === 'Artikel' ? 'bg-accent' : cat === 'Gambar' ? 'bg-blue-400' : 'bg-red-400'
}
</script>

<style scoped>
.glass-panel {
  background: rgba(13, 25, 48, 0.6);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(251, 191, 36, 0.1);
}
.calendar-cell-active {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0) 100%);
  border: 1px solid var(--color-accent);
  box-shadow: 0 0 15px rgba(251, 191, 36, 0.15);
}
.events-panel {
  background: rgba(13, 25, 48, 0.85);
  border: 1px solid rgba(251, 191, 36, 0.1);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }

.animate-marquee { animation: marquee 30s linear infinite; }
@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
</style>
