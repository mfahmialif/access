<template>
  <div class="w-full min-h-full flex flex-col">
    <!-- ═══════ PATTERN OVERLAY ═══════ -->
      <div class="fixed inset-0 pointer-events-none z-[-1]"
           :style="{ backgroundImage: isDark ? patternBg : patternBgLight, backgroundSize: '30px 30px' }"></div>

  <!-- ═══════ CONTENT ═══════ -->
  <div class="flex-1 flex flex-col gap-4 overflow-visible pb-12 w-full font-display">
    <!-- ═══ FILTER TOOLBAR ═══ -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between px-1 md:px-2 gap-4">
          <div class="space-y-0.5 md:space-y-1 shrink-0">
            <h2 class="text-2xl md:text-3xl font-black text-accent tracking-tight drop-shadow-sm whitespace-nowrap">Pengumuman</h2>
            <p :class="['font-medium text-sm md:text-base', isDark ? 'text-slate-300' : 'text-slate-500']">Informasi penting untuk seluruh warga</p>
          </div>
          <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:flex xl:w-auto xl:items-center xl:flex-wrap">
            <!-- Search -->
            <div class="relative sm:col-span-2 xl:col-span-1 xl:w-56">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-base!">search</span>
              <input v-model="searchQuery" @input="onSearch" type="text" placeholder="Cari pengumuman..."
                     :class="[
                       'w-full pl-9 pr-3 h-10 xl:h-9 rounded-full border text-sm xl:text-xs outline-none focus:border-accent/50 focus:shadow-[0_0_10px_rgba(251,191,36,0.15)] transition-all',
                       isDark 
                         ? 'bg-[#050e1f]/60 border-white/10 text-white placeholder-slate-500' 
                         : 'bg-white border-slate-200 text-slate-800 placeholder-slate-400 shadow-sm'
                     ]" />
            </div>
            <!-- Priority Filter -->
            <div :class="['flex items-center gap-1 border rounded-full p-1 overflow-x-auto sm:col-span-2 xl:col-span-1 h-10 xl:h-9', isDark ? 'bg-[#050e1f]/60 border-white/10' : 'bg-white border-slate-200 shadow-sm']">
              <button v-for="tab in priorityTabs" :key="tab.value"
                      @click="setPriority(tab.value)"
                      :class="[
                        'flex-1 xl:flex-none px-3 md:px-4 h-full rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer flex items-center justify-center gap-1.5 whitespace-nowrap',
                        activePriority === tab.value 
                          ? (isDark ? 'bg-accent text-[#0a192f] shadow-[0_0_10px_rgba(251,191,36,0.3)]' : 'bg-accent text-white shadow-sm') 
                          : (isDark ? 'text-slate-400 hover:text-white hover:bg-white/5' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50')
                      ]">
                <span class="material-symbols-outlined text-sm!">{{ tab.icon }}</span>
                {{ tab.label }}
              </button>
            </div>
            <!-- Sort -->
            <div :class="['flex items-center gap-1 border rounded-full p-1 overflow-x-auto h-10 xl:h-9', isDark ? 'bg-[#050e1f]/60 border-white/10' : 'bg-white border-slate-200 shadow-sm']">
              <button v-for="opt in sortOptions" :key="opt.value" @click="setSort(opt.value)"
                      :class="[
                        'flex-1 xl:flex-none px-3 md:px-4 h-full rounded-full text-xs font-bold tracking-wider transition-all cursor-pointer flex items-center justify-center gap-1 whitespace-nowrap',
                        activeSortBy === opt.value 
                          ? (isDark ? 'bg-white/15 text-accent' : 'bg-slate-100 text-accent') 
                          : (isDark ? 'text-slate-500 hover:text-white' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50')
                      ]">
                {{ opt.label }}
                <span v-if="activeSortBy === opt.value" class="material-symbols-outlined text-xs!">
                  {{ activeSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                </span>
              </button>
            </div>
            <!-- Per Page -->
            <div :class="['relative flex items-center border rounded-full h-10 xl:h-9 px-2', isDark ? 'bg-[#050e1f]/60 border-white/10' : 'bg-white border-slate-200 shadow-sm']">
              <select v-model="perPage" @change="currentPage = 1; loadData()"
                      :class="['bg-transparent text-xs md:text-sm text-accent font-bold outline-none cursor-pointer appearance-none pl-2 pr-5 w-full h-full z-10', isDark ? '' : 'text-slate-700']">
                <option v-for="n in [6,9,12,18]" :key="n" :value="n" :class="isDark ? 'bg-[#0a192f]' : 'bg-white'">{{ n }}</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 text-base text-slate-400 pointer-events-none z-0">expand_more</span>
            </div>
          </div>
        </div>

        <!-- ═══ LOADING ═══ -->
        <div v-if="loading" class="flex-1 flex items-center justify-center">
          <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-accent/30 border-t-accent rounded-full animate-spin"></div>
            <span class="text-accent font-bold uppercase tracking-widest text-sm">Memuat Pengumuman...</span>
          </div>
        </div>

        <!-- ═══ EMPTY STATE ═══ -->
        <div v-else-if="items.length === 0" class="flex-1 flex items-center justify-center">
          <div class="text-center space-y-3">
            <span class="material-symbols-outlined text-6xl text-slate-600">campaign</span>
            <p :class="['text-lg font-medium', isDark ? 'text-slate-400' : 'text-slate-500']">Belum ada pengumuman</p>
          </div>
        </div>

        <!-- ═══ CARDS GRID ═══ -->
        <div v-else class="flex-1 pr-2">
          <!-- Urgent Hero (first urgent item) -->
          <section v-if="urgentHero" @click="openDetail(urgentHero)"
                   class="relative rounded-2xl overflow-hidden group cursor-pointer mb-5 min-h-[200px] h-auto lg:h-[240px]">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 group-hover:scale-105"
                 :style="{ backgroundImage: `url('${getImageUrl(urgentHero)}')` }"></div>
            <div :class="['absolute inset-0 bg-linear-to-r', isDark ? 'from-[#050e1f]/95 via-[#050e1f]/80 to-[#050e1f]/40' : 'from-slate-50/95 via-white/80 to-white/40']"></div>
            <div class="absolute inset-0 border-4 border-red-600 rounded-2xl urgent-pulse pointer-events-none z-10"></div>
            <div class="relative z-20 h-full flex flex-col justify-between p-4 md:p-6 lg:p-8">
              <div class="flex flex-col md:flex-row md:items-start justify-between gap-3 md:gap-0">
                <div class="flex items-center gap-3">
                  <div class="flex items-center gap-2 md:gap-3 bg-red-600 text-white px-3 py-1.5 md:px-5 md:py-2 rounded-lg backdrop-blur-sm shadow-lg shadow-red-900/20">
                    <span class="material-symbols-outlined text-sm md:text-base animate-pulse">warning</span>
                    <span class="text-sm md:text-lg font-bold tracking-wide">PENGUMUMAN PENTING</span>
                  </div>
                </div>
                <span :class="['text-xs md:text-base font-medium px-3 py-1.5 md:px-4 md:py-2 rounded-lg backdrop-blur-md self-start', isDark ? 'text-slate-300 bg-black/40' : 'text-slate-600 bg-white/70 shadow-sm border border-slate-200']">
                  {{ formatDate(urgentHero.datetime) }}
                </span>
              </div>
              <div class="max-w-4xl mt-4 md:mt-auto">
                <h2 :class="['text-xl md:text-3xl lg:text-4xl font-extrabold mb-2 md:mb-3 leading-tight drop-shadow-lg', isDark ? 'text-white' : 'text-slate-800']">{{ urgentHero.title }}</h2>
                <p :class="['text-sm md:text-lg font-normal leading-relaxed max-w-5xl border-l-2 md:border-l-4 pl-3 md:pl-6 line-clamp-3 md:line-clamp-2', isDark ? 'text-slate-200 border-accent' : 'text-slate-600 border-amber-500']">{{ urgentHero.excerpt || urgentHero.body }}</p>
                <div :class="['flex items-center gap-3 md:gap-6 text-xs md:text-base font-medium mt-3 flex-wrap', isDark ? 'text-slate-300' : 'text-slate-600']">
                  <div v-if="urgentHero.location" class="flex items-center gap-1.5 md:gap-2">
                    <span :class="['material-symbols-outlined text-[16px] md:text-base', isDark ? 'text-accent' : 'text-amber-600']">location_on</span>
                    {{ urgentHero.location }}
                  </div>
                  <div v-if="urgentHero.audience" class="flex items-center gap-1.5 md:gap-2">
                    <span :class="['material-symbols-outlined text-[16px] md:text-base', isDark ? 'text-accent' : 'text-amber-600']">groups</span>
                    {{ urgentHero.audience }}
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Regular Cards -->
          <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 portrait:lg:grid-cols-2 gap-4 md:gap-5">
            <article v-for="item in regularItems" :key="item.id"
                     @click="router.push({ name: 'DetailAnnouncement', params: { id: item.id } })"
                     :class="[
                       'group relative backdrop-blur-md border rounded-2xl overflow-hidden flex flex-col p-6 transition-all duration-300 cursor-pointer',
                       isDark 
                         ? 'bg-[#0a192f]/80 border-white/5 hover:bg-[#0f1d35] hover:border-accent/30 hover:shadow-[0_0_25px_rgba(251,191,36,0.1)]'
                         : 'bg-white border-slate-200 hover:border-accent/50 hover:shadow-[0_8px_30px_rgba(0,0,0,0.04)]'
                     ]">
              <div class="absolute top-0 right-0 p-4 opacity-[0.07]">
                <span class="material-symbols-outlined text-[80px]! text-accent">{{ priorityBgIcon(item.priority) }}</span>
              </div>
              <div class="flex items-center justify-between mb-4 z-10">
                <span :class="priorityBadge(item.priority)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border">
                  <span class="material-symbols-outlined text-xs!">{{ priorityIcon(item.priority) }}</span>
                  {{ item.priority }}
                </span>
                <span :class="['text-xs font-medium', isDark ? 'text-slate-500' : 'text-slate-400']">{{ formatDate(item.datetime) }}</span>
              </div>
              <div class="flex-1 z-10 flex flex-col">
                <h3 :class="['text-lg font-bold mb-2 line-clamp-2 transition-colors', isDark ? 'text-white group-hover:text-accent/90' : 'text-slate-800 group-hover:text-accent']">{{ item.title }}</h3>
                <p :class="['text-sm line-clamp-3 mb-4', isDark ? 'text-slate-400' : 'text-slate-500']">{{ item.excerpt || item.body }}</p>
              </div>
              <div :class="['mt-auto pt-4 border-t flex justify-between items-center z-10', isDark ? 'border-white/10' : 'border-slate-100']">
                <div :class="['flex items-center gap-4 text-xs', isDark ? 'text-slate-500' : 'text-slate-400']">
                  <span v-if="item.audience" class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm!">groups</span> {{ item.audience }}
                  </span>
                  <span v-if="item.location" class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm!">location_on</span> {{ item.location }}
                  </span>
                </div>
                <span class="material-symbols-outlined text-slate-500 group-hover:text-accent group-hover:translate-x-1 transition-all duration-300">arrow_forward</span>
              </div>
            </article>
          </section>
        </div>

        <!-- ═══ PAGINATION ═══ -->
        <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 py-3">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                  :class="[
                    'size-9 rounded-full border flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer',
                    isDark 
                      ? 'bg-[#050e1f]/60 border-white/10 text-slate-400 hover:text-accent hover:border-accent/40' 
                      : 'bg-white border-slate-200 text-slate-500 hover:text-accent hover:border-accent shadow-sm'
                  ]">
            <span class="material-symbols-outlined text-sm!">chevron_left</span>
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" :class="['text-sm px-1', isDark ? 'text-slate-600' : 'text-slate-400']">…</span>
            <button v-else @click="goToPage(p)"
                    :class="[
                      'size-9 rounded-full flex items-center justify-center text-xs font-bold transition-all cursor-pointer',
                      p === currentPage 
                        ? (isDark ? 'bg-accent text-[#0a192f] shadow-[0_0_10px_rgba(251,191,36,0.4)] font-black' : 'bg-accent text-white shadow-sm font-black') 
                        : (isDark ? 'bg-[#050e1f]/60 border border-white/10 text-slate-400 hover:text-accent hover:border-accent/40' : 'bg-white border border-slate-200 text-slate-600 hover:text-accent hover:border-accent shadow-sm')
                    ]">
              {{ p }}
            </button>
          </template>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                  :class="[
                    'size-9 rounded-full border flex items-center justify-center disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer',
                    isDark 
                      ? 'bg-[#050e1f]/60 border-white/10 text-slate-400 hover:text-accent hover:border-accent/40' 
                      : 'bg-white border-slate-200 text-slate-500 hover:text-accent hover:border-accent shadow-sm'
                  ]">
            <span class="material-symbols-outlined text-sm!">chevron_right</span>
          </button>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'
import { usePublicTheme } from '../../composables/usePublicTheme'

const router = useRouter()
const { isDark } = usePublicTheme()

onMounted(() => { loadData() })
// ── Data ──
const items = ref([]); const loading = ref(false)
const currentPage = ref(1); const totalPages = ref(1); const perPage = ref(9)
const activePriority = ref('all')
const activeSortBy = ref('created_at'); const activeSortDir = ref('desc')
const searchQuery = ref('')
let searchTimeout = null
function onSearch() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { currentPage.value = 1; loadData() }, 400) }

const priorityTabs = [
  { value: 'all', label: 'Semua', icon: 'apps' },
  { value: 'Urgent', label: 'Urgent', icon: 'warning' },
  { value: 'Normal', label: 'Normal', icon: 'info' },
  { value: 'Info', label: 'Info', icon: 'tips_and_updates' },
]
const sortOptions = [
  { value: 'created_at', label: 'Waktu' },
  { value: 'priority', label: 'Prioritas' },
  { value: 'title', label: 'Judul' },
]

function setPriority(val) { activePriority.value = val; currentPage.value = 1; loadData() }
function setSort(val) {
  if (activeSortBy.value === val) activeSortDir.value = activeSortDir.value === 'desc' ? 'asc' : 'desc'
  else { activeSortBy.value = val; activeSortDir.value = val === 'created_at' ? 'desc' : 'asc' }
  currentPage.value = 1; loadData()
}

async function loadData() {
  loading.value = true
  try {
    const params = { per_page: perPage.value, page: currentPage.value, status: 'Aktif', sort_by: activeSortBy.value, sort_dir: activeSortDir.value }
    if (activePriority.value !== 'all') params.priority = activePriority.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    const { data } = await api.get('/announcements', { params })
    items.value = data.data || []
    totalPages.value = data.last_page || 1
  } catch (e) { console.error(e); items.value = [] } finally { loading.value = false }
}

function goToPage(p) { if (p >= 1 && p <= totalPages.value) { currentPage.value = p; loadData() } }
const pageNumbers = computed(() => {
  const last = totalPages.value; const curr = currentPage.value; const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) pages.push(i)
    else if (pages[pages.length - 1] !== '...') pages.push('...')
  }
  return pages
})

// ── Computed views ──
const urgentHero = computed(() => items.value.find(i => i.priority === 'Urgent'))
const regularItems = computed(() => {
  if (!urgentHero.value) return items.value
  return items.value.filter(i => i.id !== urgentHero.value.id)
})



// ── Helpers ──
function getImageUrl(item) { return item.image_path ? storageUrl(item.image_path) : '/img/default-agenda.png' }

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr); const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`
}

function priorityBadge(p) {
  if (p === 'Urgent') return isDark.value ? 'bg-red-500/20 text-red-400 border-red-500/40' : 'bg-red-50 text-red-600 border-red-200'
  if (p === 'Normal') return isDark.value ? 'bg-accent/20 text-accent border-accent/40' : 'bg-amber-50 text-amber-600 border-amber-200'
  return isDark.value ? 'bg-blue-500/20 text-blue-400 border-blue-500/40' : 'bg-blue-50 text-blue-600 border-blue-200'
}
function priorityIcon(p) {
  if (p === 'Urgent') return 'warning'
  if (p === 'Normal') return 'info'
  return 'tips_and_updates'
}
function priorityBgIcon(p) {
  if (p === 'Urgent') return 'warning'
  if (p === 'Normal') return 'campaign'
  return 'tips_and_updates'
}

const tickerText = 'Selamat Datang di Access TV  •  Harap menjaga ketenangan selama jam belajar malam berlangsung  •  Pengambilan paket kiriman dapat dilakukan di pos satpam pada pukul 16:00 - 17:30  •  Jangan lupa mematikan lampu kamar saat meninggalkan ruangan  •  Mari kita jaga kebersihan lingkungan pesantren bersama-sama'

const patternBg = `
  radial-gradient(circle at 0% 0%, rgba(251,191,36,0.15) 2px, transparent 2px),
  radial-gradient(circle at 100% 100%, rgba(251,191,36,0.15) 2px, transparent 2px)
`
const patternBgLight = `
  radial-gradient(circle at 0% 0%, rgba(15, 23, 42, 0.05) 2px, transparent 2px),
  radial-gradient(circle at 100% 100%, rgba(15, 23, 42, 0.05) 2px, transparent 2px)
`
</script>

<style scoped>
@keyframes border-pulse {
  0%, 100% { border-color: rgba(220, 38, 38, 0.6); box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
  50% { border-color: rgba(220, 38, 38, 1); box-shadow: 0 0 20px 0 rgba(220, 38, 38, 0.6); }
}
.urgent-pulse { animation: border-pulse 2s infinite; }

@keyframes ticker {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
.animate-ticker { animation: ticker 30s linear infinite; }

.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }

.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(251,191,36,0.2); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(251,191,36,0.4); }
</style>
