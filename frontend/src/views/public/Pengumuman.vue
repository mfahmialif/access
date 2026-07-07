<template>
  <div class="relative w-screen h-screen overflow-hidden font-display text-slate-100 selection:bg-accent selection:text-[#0a192f]">
    <!-- ═══════ PATTERN OVERLAY ═══════ -->
    <div class="absolute inset-0 pointer-events-none z-0"
         :style="{ backgroundImage: patternBg, backgroundSize: '30px 30px' }"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col h-screen w-full px-4 py-3 md:px-8 md:py-5 max-w-[1920px] mx-auto">

      <!-- ═══════ HEADER ═══════ -->
      <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-3 md:mb-5 pb-3 md:pb-4 border-b border-accent/20 gap-3">
        <div class="flex items-center gap-3 md:gap-4">
          <button @click="goBack"
                  class="flex items-center justify-center size-10 md:size-12 rounded-full bg-[#0a192f]/50 border border-accent/30 text-accent hover:bg-accent hover:text-[#0a192f] transition-all duration-300 shadow-[0_0_10px_rgba(255,215,0,0.1)] hover:shadow-[0_0_20px_rgba(255,215,0,0.4)] cursor-pointer">
            <span class="material-symbols-outlined text-xl! md:text-2xl!">arrow_back</span>
          </button>
          <div class="flex items-center justify-center size-10 md:size-12 rounded-full bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.4)]">
            <span class="material-symbols-outlined text-2xl! md:text-3xl!">campaign</span>
          </div>
          <div>
            <h1 class="text-lg md:text-2xl font-black uppercase tracking-tight text-white leading-none">Dalwa Vision</h1>
            <span class="text-xs md:text-sm font-bold text-accent uppercase tracking-widest">Pengumuman</span>
          </div>
        </div>
        <div class="hidden md:flex items-center gap-6">
          <div class="flex items-center gap-3 px-5 py-2.5 rounded-full bg-[#050e1f]/50 border border-accent/30 backdrop-blur-sm">
            <span class="material-symbols-outlined text-accent">calendar_month</span>
            <div class="flex flex-col items-end">
              <span class="text-sm font-semibold text-slate-200 leading-tight">{{ hijriDate }}</span>
              <span class="text-xs text-slate-400 leading-tight">{{ currentDate }}</span>
            </div>
            <div class="w-px h-8 bg-accent/40 mx-1"></div>
            <span class="text-base font-bold text-accent tabular-nums">{{ currentTime }} WIB</span>
          </div>
        </div>
      </header>

      <!-- ═══════ CONTENT ═══════ -->
      <main class="flex-1 flex flex-col gap-4 overflow-hidden">

        <!-- ═══ FILTER TOOLBAR ═══ -->
        <div class="flex items-end justify-between px-1">
          <div class="space-y-0.5 md:space-y-1">
            <h2 class="text-2xl md:text-3xl font-black text-accent tracking-tight drop-shadow-sm">Pengumuman Pesantren</h2>
            <p class="text-slate-300 font-medium text-sm md:text-base">Informasi penting untuk seluruh warga pesantren</p>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Search -->
            <div class="relative">
              <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm!">search</span>
              <input v-model="searchQuery" @input="onSearch" type="text" placeholder="Cari pengumuman..."
                     class="w-44 pl-8 pr-3 py-1.5 rounded-full bg-[#050e1f]/60 border border-white/10 text-xs text-white placeholder-slate-500 outline-none focus:border-accent/50 focus:shadow-[0_0_10px_rgba(251,191,36,0.15)] transition-all" />
            </div>
            <!-- Priority Filter -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1">
              <button v-for="tab in priorityTabs" :key="tab.value"
                      @click="setPriority(tab.value)"
                      :class="[activePriority === tab.value ? 'bg-accent text-[#0a192f] shadow-[0_0_10px_rgba(251,191,36,0.3)]' : 'text-slate-400 hover:text-white hover:bg-white/5']"
                      class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer flex items-center gap-1">
                <span class="material-symbols-outlined text-sm!">{{ tab.icon }}</span>
                {{ tab.label }}
              </button>
            </div>
            <!-- Sort -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1">
              <button v-for="opt in sortOptions" :key="opt.value" @click="setSort(opt.value)"
                      :class="[activeSortBy === opt.value ? 'bg-white/15 text-accent' : 'text-slate-500 hover:text-white']"
                      class="px-3 py-1.5 rounded-full text-xs font-bold tracking-wider transition-all cursor-pointer flex items-center gap-1">
                {{ opt.label }}
                <span v-if="activeSortBy === opt.value" class="material-symbols-outlined text-xs!">
                  {{ activeSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                </span>
              </button>
            </div>
            <!-- Per Page -->
            <div class="flex items-center gap-1.5 bg-[#050e1f]/60 border border-white/10 rounded-full px-3 py-1.5">
              <span class="text-xs text-slate-400 font-semibold">Tampil:</span>
              <select v-model="perPage" @change="currentPage = 1; loadData()"
                      class="bg-transparent text-xs text-accent font-bold outline-none cursor-pointer appearance-none">
                <option v-for="n in [6,9,12,18]" :key="n" :value="n" class="bg-[#0a192f]">{{ n }}</option>
              </select>
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
            <p class="text-slate-400 text-lg font-medium">Belum ada pengumuman</p>
          </div>
        </div>

        <!-- ═══ CARDS GRID ═══ -->
        <div v-else class="flex-1 overflow-y-auto custom-scrollbar pr-2">
          <!-- Urgent Hero (first urgent item) -->
          <section v-if="urgentHero" @click="openDetail(urgentHero)"
                   class="relative rounded-2xl overflow-hidden group cursor-pointer mb-5 h-[180px] portrait:h-[200px] lg:h-[240px]">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 group-hover:scale-105"
                 :style="{ backgroundImage: `url('${getImageUrl(urgentHero)}')` }"></div>
            <div class="absolute inset-0 bg-linear-to-r from-[#050e1f]/95 via-[#050e1f]/80 to-[#050e1f]/40"></div>
            <div class="absolute inset-0 border-4 border-red-600 rounded-2xl urgent-pulse pointer-events-none z-10"></div>
            <div class="relative z-20 h-full flex flex-col justify-between p-8">
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div class="flex items-center gap-3 bg-red-600/90 text-white px-5 py-2 rounded-lg backdrop-blur-sm shadow-lg shadow-red-900/20">
                    <span class="material-symbols-outlined animate-pulse">warning</span>
                    <span class="text-lg font-bold tracking-wide">PENGUMUMAN PENTING</span>
                  </div>
                </div>
                <span class="text-slate-300 text-base font-medium bg-black/40 px-4 py-2 rounded-lg backdrop-blur-md">
                  {{ formatDate(urgentHero.created_at) }}
                </span>
              </div>
              <div class="max-w-4xl mt-auto">
                <h2 class="text-3xl lg:text-4xl font-extrabold text-white mb-3 leading-tight drop-shadow-lg">{{ urgentHero.title }}</h2>
                <p class="text-lg text-slate-200 font-normal leading-relaxed max-w-5xl border-l-4 border-accent pl-6 line-clamp-2">{{ urgentHero.excerpt || urgentHero.body }}</p>
                <div class="flex items-center gap-6 text-base font-medium text-slate-300 mt-3">
                  <div v-if="urgentHero.location" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-accent">location_on</span>
                    {{ urgentHero.location }}
                  </div>
                  <div v-if="urgentHero.audience" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-accent">groups</span>
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
                     class="group relative bg-[#0a192f]/80 backdrop-blur-md border border-white/5 rounded-2xl overflow-hidden flex flex-col p-6 hover:bg-[#0f1d35] hover:border-accent/30 transition-all duration-300 cursor-pointer hover:shadow-[0_0_25px_rgba(251,191,36,0.1)]">
              <div class="absolute top-0 right-0 p-4 opacity-[0.07]">
                <span class="material-symbols-outlined text-[80px]! text-accent">{{ priorityBgIcon(item.priority) }}</span>
              </div>
              <div class="flex items-center justify-between mb-4 z-10">
                <span :class="priorityBadge(item.priority)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border">
                  <span class="material-symbols-outlined text-xs!">{{ priorityIcon(item.priority) }}</span>
                  {{ item.priority }}
                </span>
                <span class="text-xs text-slate-500 font-medium">{{ formatDate(item.created_at) }}</span>
              </div>
              <div class="flex-1 z-10 flex flex-col">
                <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-accent/90 transition-colors">{{ item.title }}</h3>
                <p class="text-slate-400 text-sm line-clamp-3 mb-4">{{ item.excerpt || item.body }}</p>
              </div>
              <div class="mt-auto pt-4 border-t border-white/10 flex justify-between items-center z-10">
                <div class="flex items-center gap-4 text-xs text-slate-500">
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
                  class="size-9 rounded-full bg-[#050e1f]/60 border border-white/10 flex items-center justify-center text-slate-400 hover:text-accent hover:border-accent/40 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer">
            <span class="material-symbols-outlined text-sm!">chevron_left</span>
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" class="text-slate-600 text-sm px-1">…</span>
            <button v-else @click="goToPage(p)"
                    :class="[p === currentPage ? 'bg-accent text-[#0a192f] shadow-[0_0_10px_rgba(251,191,36,0.4)] font-black' : 'bg-[#050e1f]/60 border border-white/10 text-slate-400 hover:text-accent hover:border-accent/40']"
                    class="size-9 rounded-full flex items-center justify-center text-xs font-bold transition-all cursor-pointer">
              {{ p }}
            </button>
          </template>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                  class="size-9 rounded-full bg-[#050e1f]/60 border border-white/10 flex items-center justify-center text-slate-400 hover:text-accent hover:border-accent/40 disabled:opacity-30 disabled:cursor-not-allowed transition-all cursor-pointer">
            <span class="material-symbols-outlined text-sm!">chevron_right</span>
          </button>
        </div>
      </main>

      <!-- ═══════ TICKER FOOTER ═══════ -->
      <footer class="mt-3 bg-[#050e1f]/80 border border-white/5 rounded-xl py-3 relative overflow-hidden">
        <div class="flex items-center h-full">
          <div class="bg-accent text-[#0a192f] px-6 py-1 h-full flex items-center justify-center font-bold text-sm uppercase tracking-wider absolute left-0 top-0 bottom-0 z-10 shadow-[4px_0_15px_rgba(0,0,0,0.5)] rounded-l-xl">
            <span class="material-symbols-outlined mr-2 animate-pulse">info</span>
            Info Terkini
          </div>
          <div class="w-full overflow-hidden whitespace-nowrap pl-44">
            <div class="animate-ticker inline-block text-base text-slate-300 font-medium">
              {{ tickerText }}
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'

const router = useRouter()
function goBack() { router.push({ name: 'Landing' }) }

// ── Time ──
const currentTime = ref(''); const currentDate = ref(''); const hijriDate = ref('')
function updateTime() {
  const now = new Date()
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`
  try { hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now) } catch { hijriDate.value = '' }
}
let ti
onMounted(() => { updateTime(); ti = setInterval(updateTime, 1000); loadData() })
onUnmounted(() => clearInterval(ti))

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
  if (p === 'Urgent') return 'bg-red-500/20 text-red-400 border-red-500/40'
  if (p === 'Normal') return 'bg-accent/20 text-accent border-accent/40'
  return 'bg-blue-500/20 text-blue-400 border-blue-500/40'
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

const tickerText = 'Selamat Datang di Dalwa Vision TV  •  Harap menjaga ketenangan selama jam belajar malam berlangsung  •  Pengambilan paket kiriman dapat dilakukan di pos satpam pada pukul 16:00 - 17:30  •  Jangan lupa mematikan lampu kamar saat meninggalkan ruangan  •  Mari kita jaga kebersihan lingkungan pesantren bersama-sama'

const patternBg = `
  radial-gradient(circle at 0% 0%, rgba(251,191,36,0.15) 2px, transparent 2px),
  radial-gradient(circle at 100% 100%, rgba(251,191,36,0.15) 2px, transparent 2px)
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
