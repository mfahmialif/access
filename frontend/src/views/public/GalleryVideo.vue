<template>
  <div class="w-full min-h-full flex flex-col">
    <!-- ═══════ PATTERN OVERLAY ═══════ -->
      <div class="fixed inset-0 pointer-events-none z-[-1] bg-[#0a192f]"
           :style="{ backgroundImage: patternBg, backgroundSize: '30px 30px' }"></div>

  <!-- ═══════ CONTENT ═══════ -->
  <div class="flex-1 flex flex-col gap-4 md:gap-5 overflow-visible font-display text-slate-100 w-full pb-12">
    <!-- Title + Filter Controls -->
        <div class="flex flex-col xl:flex-row xl:items-end justify-between px-1 md:px-2 gap-4">
          <div class="space-y-0.5 md:space-y-1 shrink-0">
            <h2 class="text-2xl md:text-4xl font-black text-accent tracking-tight drop-shadow-sm">Galeri & Video</h2>
            <p class="text-slate-300 font-medium text-sm md:text-lg">Dokumentasi foto & video kegiatan</p>
          </div>
          <!-- ═══ FILTER TOOLBAR ═══ -->
          <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:flex xl:w-auto xl:items-center xl:flex-wrap">

            <!-- Search -->
            <div class="relative sm:col-span-2 xl:col-span-1 xl:w-56">
              <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-base!">search</span>
              <input v-model="searchQuery" @input="onSearch" type="text" placeholder="Cari media..."
                     class="w-full pl-9 pr-3 h-10 xl:h-9 rounded-full bg-[#050e1f]/60 border border-white/10 text-sm xl:text-xs text-white placeholder-slate-500 outline-none focus:border-accent/50 focus:shadow-[0_0_10px_rgba(251,191,36,0.15)] transition-all" />
            </div>
            <!-- Category Filter -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1 overflow-x-auto sm:col-span-2 xl:col-span-1 h-10 xl:h-9">
              <button v-for="tab in filterTabs" :key="tab.value"
                      @click="setCategory(tab.value)"
                      :class="[
                        'flex-1 xl:flex-none px-3 md:px-4 h-full flex items-center justify-center rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 cursor-pointer whitespace-nowrap',
                        activeCategory === tab.value
                          ? 'bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.4)]'
                          : 'text-slate-400 hover:text-white hover:bg-white/5'
                      ]">
                <span class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-sm">{{ tab.icon }}</span>
                  {{ tab.label }}
                </span>
              </button>
            </div>
            <!-- Sort -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1 overflow-x-auto h-10 xl:h-9">
              <button v-for="sort in sortOptions" :key="sort.value"
                      @click="setSort(sort.value)"
                      :class="[
                        'flex-1 xl:flex-none px-3 md:px-4 h-full flex items-center justify-center rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 cursor-pointer whitespace-nowrap',
                        activeSortBy === sort.value
                          ? 'bg-white/10 text-white border border-white/20'
                          : 'text-slate-500 hover:text-white'
                      ]">
                <span class="flex items-center gap-1">{{ sort.label }}
                  <span v-if="activeSortBy === sort.value" class="material-symbols-outlined text-xs">{{ activeSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                </span>
              </button>
            </div>
            <!-- Limit -->
            <div class="relative flex items-center bg-[#050e1f]/60 border border-white/10 rounded-full h-10 xl:h-9 px-2">
              <select v-model.number="perPage" @change="currentPage = 1; loadGalleries()" class="bg-transparent text-white text-xs md:text-sm font-bold outline-none cursor-pointer appearance-none pl-2 pr-5 w-full h-full z-10">
                <option v-for="n in [6, 9, 12, 18]" :key="n" :value="n" class="bg-[#0a192f]">{{ n }}</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 text-base text-slate-400 pointer-events-none z-0">expand_more</span>
            </div>
          </div>
        </div>

        <!-- ═══════ GALLERY GRID ═══════ -->
        <div v-if="loading" class="flex items-center justify-center flex-1">
          <span class="material-symbols-outlined text-5xl text-accent animate-spin">progress_activity</span>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 lg:gap-6 flex-1 pb-4">
          <!-- All Items -->
          <article v-for="item in galleryItems" :key="item.id"
                   @click="router.push({ name: 'DetailGallery', params: { id: item.id } })"
                   class="group relative flex flex-col justify-end overflow-hidden rounded-xl bg-[#050e1f] border border-white/10 hover:border-accent transition-all duration-300 hover:scale-[1.01] hover:shadow-[0_0_20px_rgba(251,191,36,0.2)] cursor-pointer aspect-video">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                 :style="{ backgroundImage: `url('${getImageUrl(item)}')` }"></div>
            <div class="absolute inset-0 bg-linear-to-t from-[#050e1f] via-[#050e1f]/60 to-transparent opacity-90 group-hover:opacity-95 transition-opacity"></div>
            <div v-if="item.category === 'Video'" class="absolute inset-0 flex items-center justify-center z-10">
              <div class="size-12 rounded-full bg-accent/80 flex items-center justify-center shadow-[0_0_20px_rgba(251,191,36,0.3)] group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[#0a192f] text-2xl ml-0.5">play_arrow</span>
              </div>
            </div>
            <div class="relative z-10 p-4 md:p-5 flex flex-col gap-2">
              <div class="flex items-center gap-2">
                <span :class="categoryBadgeClass(item.category)">
                  <span class="material-symbols-outlined text-xs mr-0.5">{{ item.category === 'Video' ? 'videocam' : 'photo_camera' }}</span>
                  {{ item.category }}
                </span>
                <span v-if="item.duration" class="text-slate-400 text-xs">{{ formatDuration(item.duration) }}</span>
              </div>
              <h3 class="text-base md:text-xl font-bold text-white leading-tight line-clamp-2 opacity-95 group-hover:opacity-100">{{ item.title }}</h3>
              <div class="flex items-center gap-2 text-slate-300 text-xs md:text-sm mt-1">
                <span class="material-symbols-outlined text-[18px]">schedule</span>
                <span>{{ formatDate(item.datetime) }}</span>
              </div>
            </div>
          </article>

          <!-- Empty state -->
          <div v-if="galleryItems.length === 0" class="col-span-full flex flex-col items-center justify-center gap-3 py-12 text-slate-500">
            <span class="material-symbols-outlined text-5xl md:text-6xl">filter_none</span>
            <p class="text-base md:text-lg font-medium">Tidak ada konten untuk filter ini</p>
          </div>
        </div>
      </div>

      <!-- ═══════ PAGINATION FOOTER ═══════ -->
      <footer v-if="totalPages > 1" class="flex items-center justify-center py-3 md:py-4">
        <div class="flex items-center gap-1.5 md:gap-2 bg-[#050e1f]/50 backdrop-blur-sm px-3 md:px-6 py-2 rounded-full border border-accent/20 shadow-[0_0_15px_rgba(0,0,0,0.3)]">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                  class="size-7 md:size-8 flex items-center justify-center rounded-full text-accent hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 cursor-pointer">
            <span class="material-symbols-outlined text-lg md:text-xl">chevron_left</span>
          </button>
          <div class="flex items-center gap-1 md:gap-2 px-1 md:px-2">
            <button v-for="p in pageNumbers" :key="p"
                    @click="typeof p === 'number' && goToPage(p)"
                    :class="[
                      'rounded-full transition-all duration-300 cursor-pointer',
                      p === currentPage
                        ? 'size-7 md:size-8 bg-accent text-[#0a192f] font-bold text-xs md:text-sm flex items-center justify-center shadow-[0_0_12px_rgba(251,191,36,1)]'
                        : p === '...' ? 'size-7 md:size-8 text-slate-500 text-xs md:text-sm flex items-center justify-center' : 'size-7 md:size-8 bg-white/10 text-white text-xs md:text-sm flex items-center justify-center hover:bg-white/20'
                    ]">{{ p }}</button>
          </div>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                  class="size-7 md:size-8 flex items-center justify-center rounded-full text-accent hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 cursor-pointer">
            <span class="material-symbols-outlined text-lg md:text-xl">chevron_right</span>
          </button>
          <span class="text-[10px] md:text-xs text-slate-500 ml-1 md:ml-2 whitespace-nowrap">{{ currentPage }}/{{ totalPages }}</span>
        </div>
      </footer>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'

const router = useRouter()

onMounted(() => { loadGalleries() })

// ── Data ──
const galleryItems = ref([]); const loading = ref(false)
const currentPage = ref(1); const totalPages = ref(1)
const perPage = ref(6)
const activeCategory = ref('all')
const activeSortBy = ref('created_at'); const activeSortDir = ref('desc')
const searchQuery = ref('')
let searchTimeout = null
function onSearch() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { currentPage.value = 1; loadGalleries() }, 400) }

const filterTabs = [
  { value: 'all', label: 'Semua', icon: 'apps' },
  { value: 'Gambar', label: 'Foto', icon: 'photo_camera' },
  { value: 'Video', label: 'Video', icon: 'videocam' }
]
const sortOptions = [
  { value: 'created_at', label: 'Waktu' },
  { value: 'category', label: 'Jenis' },
  { value: 'title', label: 'Judul' },
]

function setCategory(val) { activeCategory.value = val; currentPage.value = 1; loadGalleries() }
function setSort(val) {
  if (activeSortBy.value === val) activeSortDir.value = activeSortDir.value === 'desc' ? 'asc' : 'desc'
  else { activeSortBy.value = val; activeSortDir.value = val === 'created_at' ? 'desc' : 'asc' }
  currentPage.value = 1; loadGalleries()
}

async function loadGalleries() {
  loading.value = true
  try {
    const params = { per_page: perPage.value, page: currentPage.value, status: 'Published', sort_by: activeSortBy.value, sort_dir: activeSortDir.value }
    if (activeCategory.value !== 'all') params.category = activeCategory.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    const { data } = await api.get('/galleries', { params })
    galleryItems.value = data.data || []
    totalPages.value = data.last_page || 1
  } catch (e) { console.error(e); galleryItems.value = [] } finally { loading.value = false }
}

function goToPage(p) { if (p >= 1 && p <= totalPages.value) { currentPage.value = p; loadGalleries() } }

const pageNumbers = computed(() => {
  const last = totalPages.value; const curr = currentPage.value; const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) pages.push(i)
    else if (pages[pages.length - 1] !== '...') pages.push('...')
  }
  return pages
})

function getImageUrl(item) {
  if (item.image_path) return storageUrl(item.image_path)
  return '/img/default-agenda.png'
}

function categoryBadgeClass(cat) {
  const b = 'inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider'
  return cat === 'Gambar'
    ? `${b} bg-blue-500/20 text-blue-400 border border-blue-500/40`
    : `${b} bg-red-500/20 text-red-400 border border-red-500/40`
}

function formatDuration(seconds) {
  if (!seconds) return ''
  const m = Math.floor(seconds / 60)
  return m >= 60 ? `${Math.floor(m / 60)}j ${m % 60}m` : `${m} menit`
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr); const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`
}



const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`
</script>

<style scoped>
.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }
</style>
