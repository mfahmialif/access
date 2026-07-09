<template>
  <div class="relative w-full h-dvh overflow-hidden">
    <!-- ═══════ BACKGROUND LAYERS ═══════ -->
    <div class="fixed inset-0 z-0 transform-gpu" style="will-change: transform">
      <div class="absolute inset-0 bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#1e3a8a]"></div>
      <div class="absolute inset-0 opacity-15 mix-blend-overlay"
           :style="{ backgroundImage: patternBg }"></div>
      <div class="absolute inset-0 opacity-30 bg-cover bg-center mix-blend-overlay blur-sm"
           style="background-image: url('/img/background.jpg')"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.2),transparent_60%)] pointer-events-none"></div>
    </div>

    <!-- ═══════ MAIN CONTENT ═══════ -->
    <div class="relative z-10 flex flex-col h-dvh p-3 md:p-4 lg:p-6">

      <!-- ═══════ HEADER ═══════ -->
      <PublicHeader :showBack="false" />

      <!-- ═══════ MAIN GRID ═══════ -->
      <simplebar class="flex-1 min-h-0 landing-scroll" :force-visible="true" :click-on-track="true">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 portrait:lg:grid-cols-2 gap-3 md:gap-5 lg:gap-6 pb-12 md:pb-16">

        <!-- CARD 7: Portal Layanan / Apps -->
        <div class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-6 flex flex-col transition-all duration-500 cursor-pointer"
             @click="navigateTo('apps')">
          <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
            <span class="material-symbols-outlined !text-6xl md:!text-8xl text-emerald-400">apps</span>
          </div>
          <div class="flex justify-between items-center mb-4 md:mb-6 relative z-10">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 p-2 md:p-2.5 rounded-xl text-emerald-400 border border-emerald-500/20 group-hover:border-emerald-400/50 transition-colors shadow-[0_0_15px_rgba(52,211,153,0.1)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl">widgets</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white tracking-wide">Portal Layanan</h3>
            </div>
            <span class="material-symbols-outlined text-slate-500 group-hover:text-emerald-400 transition-colors group-hover:translate-x-1 duration-300">arrow_forward_ios</span>
          </div>
          <div class="flex-1 flex flex-col justify-end relative z-10">
            <div class="bg-gradient-to-r from-emerald-500/10 to-transparent rounded-xl p-4 md:p-5 border-l-4 border-emerald-500/50 backdrop-blur-sm">
              <p class="text-slate-300 text-sm md:text-base font-medium leading-relaxed">
                Kumpulan aplikasi web dan layanan digital.
              </p>
            </div>
          </div>
        </div>

        <!-- CARD 1: Berita Utama -->
        <div class="group relative overflow-hidden rounded-2xl glass-panel glass-panel-hover transition-all duration-500 cursor-pointer min-h-[220px] sm:min-h-[240px]"
             @click="navigateTo('berita')">
          <!-- Skeleton -->
          <template v-if="loadingNews">
            <div class="p-6 flex flex-col justify-end h-full min-h-[200px]">
              <div class="skel h-5 w-28 rounded-full mb-3"></div>
              <div class="skel h-7 w-4/5 rounded mb-2"></div>
              <div class="skel h-4 w-3/5 rounded"></div>
            </div>
          </template>
          <template v-else>
            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a]/95 via-[#0f172a]/40 to-transparent z-10"></div>
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 group-hover:scale-110"
                 :style="{ backgroundImage: latestNews ? `url(${latestNews.image})` : `url('/img/default-agenda.png')` }"></div>
            <div class="relative z-20 h-full flex flex-col justify-end p-4 md:p-6">
              <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-3">
                <span class="bg-accent text-[#0f172a] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-lg shadow-accent/20">Berita Utama</span>
                <div class="text-white/80 text-xs flex items-center gap-1 bg-[#0f172a]/50 px-2 py-1 rounded-full border border-white/10">
                  <span class="material-symbols-outlined text-[14px]">schedule</span> {{ latestNews ? timeAgo(latestNews.datetime) : '-' }}
                </div>
              </div>
              <h2 class="text-lg md:text-2xl font-serif font-bold text-white mb-2 leading-tight group-hover:text-accent-light transition-colors drop-shadow-md">{{ latestNews?.title || 'Memuat...' }}</h2>
              <p class="text-slate-300 line-clamp-2 text-sm md:text-base font-light leading-relaxed">{{ latestNews?.excerpt || '' }}</p>
            </div>
            <div class="absolute top-3 right-3 md:top-4 md:right-4 z-20 bg-[#0f172a]/60 w-9 h-9 md:w-10 md:h-10 flex items-center justify-center rounded-full border border-white/10 group-hover:bg-accent group-hover:text-[#0f172a] transition-all duration-300 group-hover:scale-110">
              <span class="material-symbols-outlined text-lg md:text-xl">newspaper</span>
            </div>
          </template>
        </div>

        <!-- CARD 2: Agenda Harian -->
        <div class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-6 flex flex-col transition-all duration-500 cursor-pointer overflow-hidden"
             @click="navigateTo('agenda-harian')">
          <div class="absolute inset-0 bg-cover bg-center opacity-10 transition-transform duration-1000 group-hover:scale-110 mix-blend-overlay" style="background-image: url('/img/default-agenda.png')"></div>
          <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a]/20 to-[#0f172a]/90 z-0"></div>
          <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity z-10">
            <span class="material-symbols-outlined !text-6xl md:!text-8xl text-accent">calendar_today</span>
          </div>
          <div class="flex justify-between items-center mb-4 md:mb-6 relative z-10">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/10 p-2 md:p-2.5 rounded-xl text-blue-400 border border-blue-500/20 group-hover:border-accent/50 group-hover:text-accent transition-colors shadow-[0_0_15px_rgba(59,130,246,0.1)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl">calendar_today</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white tracking-wide">Agenda Harian</h3>
            </div>
            <span class="material-symbols-outlined text-slate-500 group-hover:text-accent transition-colors group-hover:translate-x-1 duration-300">arrow_forward_ios</span>
          </div>
          <div class="flex-1 flex flex-col justify-end relative z-10">
            <template v-if="loadingAgenda">
              <div class="space-y-3">
                <div v-for="i in 3" :key="i" class="flex gap-4 items-center p-2">
                  <div class="skel h-5 w-14 rounded"></div>
                  <div class="h-8 w-px bg-white/10"></div>
                  <div class="skel h-4 w-40 rounded"></div>
                </div>
              </div>
            </template>
            <ul v-else class="space-y-3">
              <li v-for="item in agendaHarian" :key="item.time"
                  class="flex gap-4 items-center hover:bg-white/5 p-2 rounded-lg transition-colors border border-transparent hover:border-white/5">
                <span class="text-accent font-bold font-mono text-base md:text-lg min-w-[48px] md:min-w-[55px]">{{ item.time }}</span>
                <div class="h-8 w-px bg-white/10"></div>
                <span class="text-slate-100 font-medium text-sm md:text-base">{{ item.label }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- CARD 3: Agenda Mingguan -->
        <div class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-6 flex flex-col transition-all duration-500 cursor-pointer overflow-hidden"
             @click="navigateTo('agenda-mingguan')">
          <div class="absolute inset-0 bg-cover bg-center opacity-10 transition-transform duration-1000 group-hover:scale-110 mix-blend-overlay" style="background-image: url('/img/default-agenda.png')"></div>
          <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a]/20 to-[#0f172a]/90 z-0"></div>
          <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity z-10">
            <span class="material-symbols-outlined !text-6xl md:!text-8xl text-accent">calendar_view_week</span>
          </div>
          <div class="flex justify-between items-center mb-4 md:mb-6 relative z-10">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-accent/20 to-yellow-600/10 p-2 md:p-2.5 rounded-xl text-accent border border-accent/20 group-hover:border-accent/50 transition-colors shadow-[0_0_15px_rgba(251,191,36,0.1)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl">calendar_view_week</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white tracking-wide">Agenda Mingguan</h3>
            </div>
            <span class="material-symbols-outlined text-slate-500 group-hover:text-accent transition-colors group-hover:translate-x-1 duration-300">arrow_forward_ios</span>
          </div>
          <div class="flex-1 flex flex-col justify-end relative z-10">
            <template v-if="loadingWeekly">
              <div class="bg-white/5 rounded-xl p-4 md:p-5 border-l-4 border-accent/30">
                <div class="skel h-3 w-24 rounded mb-3"></div>
                <div class="skel h-6 w-3/4 rounded mb-2"></div>
                <div class="skel h-4 w-1/3 rounded"></div>
              </div>
            </template>
            <template v-else>
              <div v-if="latestWeekly" class="bg-gradient-to-r from-white/5 to-transparent rounded-xl p-4 md:p-5 border-l-4 border-accent">
                <p class="text-accent text-xs font-bold uppercase tracking-widest mb-2 flex items-center gap-2">
                  <span class="w-2 h-2 bg-accent rounded-full animate-pulse"></span> Terbaru
                </p>
                <p class="text-white text-base md:text-xl font-serif font-medium mb-1">{{ latestWeekly.title }}</p>
                <div class="flex items-center gap-2 text-slate-400 text-sm mt-2">
                  <span class="material-symbols-outlined text-base">location_on</span>
                  {{ latestWeekly.location || '-' }}
                </div>
              </div>
              <div v-else class="bg-gradient-to-r from-white/5 to-transparent rounded-xl p-4 md:p-5 border-l-4 border-accent/30">
                <p class="text-slate-400 text-sm italic">Belum ada agenda minggu ini</p>
              </div>
            </template>
          </div>
        </div>

        <!-- CARD 4: Agenda Bulanan -->
        <div class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-6 flex flex-col transition-all duration-500 cursor-pointer overflow-hidden"
             @click="navigateTo('agenda-bulanan')">
          <div class="absolute inset-0 bg-cover bg-center opacity-10 transition-transform duration-1000 group-hover:scale-110 mix-blend-overlay" style="background-image: url('/img/default-agenda.png')"></div>
          <div class="absolute inset-0 bg-gradient-to-b from-[#0f172a]/20 to-[#0f172a]/90 z-0"></div>
          <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity z-10">
            <span class="material-symbols-outlined !text-6xl md:!text-8xl text-blue-400">calendar_month</span>
          </div>
          <div class="flex justify-between items-center mb-4 md:mb-6 relative z-10">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/10 p-2 md:p-2.5 rounded-xl text-blue-400 border border-blue-500/20 group-hover:border-blue-400/50 transition-colors shadow-[0_0_15px_rgba(96,165,250,0.1)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl">calendar_month</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white tracking-wide">Agenda Bulanan</h3>
            </div>
            <span class="material-symbols-outlined text-slate-500 group-hover:text-accent transition-colors group-hover:translate-x-1 duration-300">arrow_forward_ios</span>
          </div>
          <div class="flex-1 flex flex-col justify-end relative z-10 space-y-4">
            <template v-if="latestMonthly.length > 0">
              <div v-for="(item, idx) in latestMonthly" :key="item.id"
                   class="flex items-center gap-4 bg-white/5 p-3 rounded-xl hover:bg-white/10 transition-colors border border-transparent hover:border-white/5">
                <div class="flex flex-col items-center justify-center rounded-lg w-12 h-12 md:w-14 md:h-14 shrink-0 border"
                     :class="idx === 0 ? 'bg-blue-500/10 border-blue-500/20' : 'bg-white/5 border-white/10'">
                  <span class="text-[10px] font-bold uppercase tracking-widest" :class="idx === 0 ? 'text-blue-300' : 'text-slate-400'">{{ getMonthShort(item.date || item.created_at) }}</span>
                  <span class="text-xl md:text-2xl font-serif font-bold leading-none" :class="idx === 0 ? 'text-white' : 'text-slate-300'">{{ getDay(item.date || item.created_at) }}</span>
                </div>
                <div>
                  <span class="block text-base md:text-lg font-medium line-clamp-1" :class="idx === 0 ? 'text-slate-200' : 'text-slate-400'">{{ item.title }}</span>
                  <span class="text-xs" :class="idx === 0 ? 'text-slate-500' : 'text-slate-600'">{{ item.location || '-' }}</span>
                </div>
              </div>
            </template>
            <div v-else class="flex items-center justify-center py-6">
              <p class="text-slate-500 text-sm italic">Belum ada agenda bulan ini</p>
            </div>
          </div>
        </div>

        <!-- CARD 5: Galeri & Video -->
        <div class="group relative overflow-hidden rounded-2xl glass-panel glass-panel-hover transition-all duration-500 cursor-pointer"
             @click="navigateTo('gallery-video')">
          <div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:opacity-30 transition-opacity duration-700 transform group-hover:scale-105"
               style="background-image: url('/img/galeri-bg.jpg')"></div>
          <div class="absolute inset-0 bg-gradient-to-t from-[#020617] via-[#020617]/90 to-transparent"></div>
          <div class="relative z-10 h-full p-4 md:p-6 flex flex-col min-h-[200px]">
            <div class="flex justify-between items-start mb-4">
              <div class="bg-gradient-to-br from-indigo-500/20 to-indigo-600/10 p-2 md:p-2.5 rounded-xl text-indigo-400 border border-indigo-500/20 group-hover:border-indigo-400/50 transition-colors shadow-[0_0_15px_rgba(129,140,248,0.15)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl">play_circle</span>
              </div>
              <span class="material-symbols-outlined text-slate-500 group-hover:text-indigo-400 transition-colors">arrow_forward_ios</span>
            </div>
            <div class="mt-auto">
              <h3 class="text-lg md:text-xl font-bold text-white mb-1">Galeri &amp; Video</h3>
              <p class="text-slate-400 text-sm mb-4">Dokumentasi Kegiatan Santri</p>
              <div class="flex items-center gap-3">
                <template v-if="galleryThumbs.length > 0">
                  <div v-for="thumb in galleryThumbs.slice(0, 2)" :key="thumb.id" class="h-12 w-20 rounded-lg overflow-hidden border border-white/10 relative">
                    <div class="absolute inset-0 bg-black/20 hover:bg-transparent transition-colors"></div>
                    <img :alt="thumb.title" class="w-full h-full object-cover" :src="thumb.image" />
                  </div>
                </template>
                <div v-if="galleryCount > 2" class="h-12 w-12 rounded-lg border border-white/10 flex items-center justify-center bg-white/5 text-xs font-bold text-white hover:bg-white/10 transition-colors">
                  +{{ galleryCount - 2 }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 6: Pengumuman -->
        <div class="col-span-full group relative rounded-2xl bg-gradient-to-br from-yellow-900/40 to-yellow-950/40 border border-yellow-500/30 hover:border-yellow-500/60 p-4 md:p-6 flex flex-col transition-all duration-500 cursor-pointer shadow-[0_0_30px_rgba(234,179,8,0.1)] hover:shadow-[0_0_40px_rgba(234,179,8,0.2)]"
             @click="navigateTo('pengumuman')">
          <div class="absolute right-0 -top-2 z-20">
            <span class="relative flex h-5 w-5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-5 w-5 bg-yellow-500 border-2 border-[#020617]"></span>
            </span>
          </div>
          <div class="flex justify-between items-center mb-4 md:mb-6 relative z-10">
            <div class="flex items-center gap-3">
              <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/10 p-2 md:p-2.5 rounded-xl text-yellow-500 border border-yellow-500/20 group-hover:border-yellow-400/50 transition-colors shadow-[0_0_15px_rgba(234,179,8,0.2)]">
                <span class="material-symbols-outlined text-2xl md:text-3xl animate-pulse-slow">campaign</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white tracking-wide">Pengumuman</h3>
            </div>
            <span class="material-symbols-outlined text-slate-500 group-hover:text-yellow-400 transition-colors group-hover:translate-x-1 duration-300">arrow_forward_ios</span>
          </div>
          <div class="flex-1 flex flex-col justify-end relative z-10">
            <p class="text-yellow-400 font-bold text-xs uppercase tracking-widest mb-2 flex items-center gap-2">
              <span class="material-symbols-outlined text-base">warning</span> {{ latestAnnouncement?.priority || 'Penting' }}
            </p>
            <div class="bg-yellow-500/10 rounded-xl p-3 border border-yellow-500/20">
              <div class="flex items-start gap-3">
                <span class="bg-yellow-500 text-[#0f172a] text-[10px] font-bold px-2 py-0.5 rounded shadow-lg shadow-yellow-500/30 mt-0.5">BARU</span>
                <p class="text-slate-200 text-sm font-medium leading-relaxed">{{ latestAnnouncement?.title || 'Memuat pengumuman...' }}</p>
              </div>
            </div>
          </div>
        </div>
        </div>
      </simplebar>
    </div>

    <!-- ═══════ TICKER BAR ═══════ -->
    <TickerBar />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'
import PublicHeader from '../../components/PublicHeader.vue'
import TickerBar from '../../components/TickerBar.vue'

const router = useRouter()

const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23fbbf24' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`

// ── API Data (per-card loading) ──
const loadingNews = ref(true)
const loadingAgenda = ref(true)
const loadingWeekly = ref(true)
const loadingMonthly = ref(true)
const loadingGallery = ref(true)
const loadingAnnouncement = ref(true)
const latestNews = ref(null)
const agendaHarian = ref([])
const latestWeekly = ref(null)
const latestMonthly = ref([])
const galleryThumbs = ref([])
const galleryCount = ref(0)
const latestAnnouncement = ref(null)

function timeAgo(dateStr) {
  if (!dateStr) return '-'
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'Baru saja'
  if (mins < 60) return `${mins} Menit lalu`
  const hrs = Math.floor(mins / 60)
  if (hrs < 24) return `${hrs} Jam lalu`
  return `${Math.floor(hrs / 24)} Hari lalu`
}

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
function getMonthShort(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return isNaN(d.getTime()) ? '' : monthNames[d.getMonth()]
}
function getDay(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return isNaN(d.getTime()) ? '' : String(d.getDate()).padStart(2, '0')
}

const routeMap = {
  berita: 'InfoTerkini',
  'agenda-harian': 'AgendaHarian',
  'agenda-mingguan': 'AgendaMingguan',
  'agenda-bulanan': 'AgendaBulanan',
  'gallery-video': 'GalleryVideo',
  pengumuman: 'Pengumuman',
  apps: 'Apps',
}

function navigateTo(section) {
  const routeName = routeMap[section]
  if (routeName) {
    router.push({ name: routeName })
  }
}

function fetchLandingData() {
  // Each card loads independently
  api.get('/news', { params: { per_page: 1 } }).then(res => {
    const items = res.data?.data || res.data || []
    if (items.length > 0) {
      const n = items[0]
      latestNews.value = {
        ...n,
        image: n.image_path ? storageUrl(n.image_path) : '/img/default-agenda.png',
        excerpt: n.body ? n.body.replace(/<[^>]*>/g, '').substring(0, 120) + '...' : '',
      }
    }
  }).catch(() => {}).finally(() => { loadingNews.value = false })

  api.get('/agendas', { params: { per_page: 50, status: 'Aktif' } }).then(res => {
    const items = res.data?.data || res.data || []
    const now = new Date()
    const nowMins = now.getHours() * 60 + now.getMinutes()
    const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
    const todayName = dayNames[now.getDay()]

    // Harian: sort by closest time to now (upcoming first, then past)
    const withDiff = items.map(a => {
      const parts = (a.time || '00:00').split(':')
      const agendaMins = parseInt(parts[0]) * 60 + parseInt(parts[1])
      const diff = agendaMins - nowMins
      return { ...a, _diff: diff, _absDiff: Math.abs(diff) }
    })
    // Upcoming first (diff >= 0 sorted asc), then past (diff < 0 sorted desc)
    const upcoming = withDiff.filter(a => a._diff >= 0).sort((a, b) => a._diff - b._diff)
    const past = withDiff.filter(a => a._diff < 0).sort((a, b) => a._absDiff - b._absDiff)
    const sorted = [...upcoming, ...past]

    agendaHarian.value = sorted.slice(0, 3).map(a => ({
      time: a.time ? a.time.substring(0, 5) : '--:--',
      label: a.title,
    }))
    if (agendaHarian.value.length === 0) {
      agendaHarian.value = [{ time: '--:--', label: 'Belum ada agenda hari ini' }]
    }
  }).catch(() => {}).finally(() => { loadingAgenda.value = false })

  // Mingguan: fetch from /weeklies filtered by today's day name, pick closest time
  const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const todayName = dayNames[new Date().getDay()]
  api.get('/weeklies', { params: { per_page: 50, status: 'Aktif', day: todayName } }).then(res => {
    const items = res.data?.data || res.data || []
    if (items.length > 0) {
      const nowMins = new Date().getHours() * 60 + new Date().getMinutes()
      // Sort: upcoming first, then recently passed
      const sorted = items.map(w => {
        const parts = (w.time || '00:00').split(':')
        const mins = parseInt(parts[0]) * 60 + parseInt(parts[1])
        return { ...w, _diff: mins - nowMins }
      })
      const upcoming = sorted.filter(w => w._diff >= 0).sort((a, b) => a._diff - b._diff)
      const past = sorted.filter(w => w._diff < 0).sort((a, b) => Math.abs(a._diff) - Math.abs(b._diff))
      latestWeekly.value = [...upcoming, ...past][0]
    }
  }).catch(() => {}).finally(() => { loadingWeekly.value = false })

  const now = new Date()
  const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
  api.get('/monthlies', { params: { per_page: 10, month: currentMonth } }).then(res => {
    const items = res.data?.data || res.data || []
    const todayStr = now.toISOString().split('T')[0]
    // Sort by closest date to today (upcoming first)
    const withDiff = items.map(m => {
      const mDate = m.date || m.created_at?.split('T')[0] || ''
      const diff = new Date(mDate) - new Date(todayStr)
      return { ...m, _diff: diff, _absDiff: Math.abs(diff) }
    })
    const upcoming = withDiff.filter(m => m._diff >= 0).sort((a, b) => a._diff - b._diff)
    const past = withDiff.filter(m => m._diff < 0).sort((a, b) => a._absDiff - b._absDiff)
    latestMonthly.value = [...upcoming, ...past].slice(0, 2)
  }).catch(() => {}).finally(() => { loadingMonthly.value = false })

  api.get('/galleries', { params: { per_page: 3 } }).then(res => {
    const items = res.data?.data || res.data || []
    galleryThumbs.value = items.slice(0, 2).map(g => ({
      id: g.id, title: g.title,
      image: g.image_path ? storageUrl(g.image_path) : '/img/default-video.png',
    }))
    galleryCount.value = res.data?.total || items.length || 0
  }).catch(() => {}).finally(() => { loadingGallery.value = false })

  api.get('/announcements', { params: { per_page: 1 } }).then(res => {
    const items = res.data?.data || res.data || []
    if (items.length > 0) latestAnnouncement.value = items[0]
  }).catch(() => {}).finally(() => { loadingAnnouncement.value = false })
}

onMounted(() => {
  fetchLandingData()
})


</script>

<style scoped>
/* ═══ Landing SimpleBar Overrides ═══ */
.landing-scroll :deep(.simplebar-scrollbar::before) {
  background: rgba(251, 191, 36, 0.4);
  border-radius: 999px;
  opacity: 0.5;
  transition: opacity 0.3s ease;
}
.landing-scroll :deep(.simplebar-scrollbar.simplebar-visible::before) { opacity: 0.7; }
.landing-scroll:hover :deep(.simplebar-scrollbar::before) { opacity: 0.6; }
.landing-scroll :deep(.simplebar-scrollbar:hover::before) { background: rgba(251, 191, 36, 0.8); opacity: 1; }
.landing-scroll :deep(.simplebar-track.simplebar-vertical) { width: 10px; right: 2px; }
.landing-scroll :deep(.simplebar-track.simplebar-horizontal) { display: none; }

/* ═══ Skeleton Loading ═══ */
.skel {
  background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
  background-size: 200% 100%;
  animation: skel-shimmer 1.5s ease-in-out infinite;
}
@keyframes skel-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
</style>
