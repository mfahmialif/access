<template>
  <div>
  <!-- ═══════ LOADING ═══════ -->
  <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B1120]">
    <div class="flex flex-col items-center gap-4">
      <span class="material-symbols-outlined text-accent text-6xl animate-spin">progress_activity</span>
      <p class="text-white/60 text-lg">Memuat konten...</p>
    </div>
  </div>

  <!-- ═══════ ERROR ═══════ -->
  <div v-else-if="error" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B1120]">
    <div class="flex flex-col items-center gap-4 text-center">
      <span class="material-symbols-outlined text-red-400 text-6xl">error</span>
      <p class="text-white/80 text-xl font-bold">Konten Tidak Ditemukan</p>
      <p class="text-white/50">{{ error }}</p>
      <button @click="goBack"
              class="mt-2 px-6 py-2 rounded-full bg-accent/20 border border-accent/40 text-accent hover:bg-accent/30 transition-colors cursor-pointer">
        Kembali
      </button>
    </div>
  </div>

  <!-- ═══════ CONTENT ═══════ -->
  <template v-else-if="detailItem">
    <!-- ── Group Tabs (for agenda items sharing same time) ── -->
    <div v-if="groupItems.length > 1" v-show="showGroupTabs" class="fixed top-4 left-1/2 -translate-x-1/2 z-[60] w-auto max-w-[90vw] transition-opacity duration-300">
      <div class="relative bg-[#0a192f]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-1.5 shadow-[0_8px_40px_rgba(0,0,0,0.5),0_0_0_1px_rgba(255,255,255,0.05)]">
        <!-- Glow behind active tab -->
        <div class="absolute inset-0 rounded-2xl overflow-hidden pointer-events-none">
          <div class="absolute inset-0 bg-accent/5"></div>
        </div>

        <div class="relative flex gap-1 overflow-x-auto no-scrollbar">
          <button v-for="(item, idx) in groupItems" :key="item.id"
                  @click="switchToItem(item)"
                  :class="[
                    'relative flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all duration-300 cursor-pointer shrink-0 min-w-0 max-w-[220px]',
                    item.id === detailItem.id
                      ? 'bg-accent text-[#0a192f] shadow-[0_4px_20px_rgba(251,191,36,0.3)]'
                      : 'text-slate-400 hover:text-white hover:bg-white/5'
                  ]">
            <span :class="[
              'shrink-0 text-[11px] font-black transition-all',
              item.id === detailItem.id ? 'text-[#0a192f]/60' : 'text-slate-600'
            ]">{{ idx + 1 }}</span>
            <span class="text-xs font-semibold truncate">{{ item.title }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════ ARTICLE DETAIL (Artikel category) ═══════ -->
    <DetailNews v-if="displayMode === 'article'"
      :item="detailItem"
      @close="goBack" />

    <!-- ═══════ IMAGE DETAIL (Gambar category) ═══════ -->
    <DetailImage v-else-if="displayMode === 'image'"
      :item="detailItem"
      :hasPrev="hasPrevItem"
      :hasNext="hasNextItem"
      @toggleUI="v => showGroupTabs = v"
      @prev="goPrevItem"
      @next="goNextItem"
      @close="goBack" />

    <!-- ═══════ VIDEO DETAIL (Video category) ═══════ -->
    <DetailVideo v-else-if="displayMode === 'video'"
      :item="detailItem"
      :hasPrev="hasPrevItem"
      :hasNext="hasNextItem"
      @toggleUI="v => showGroupTabs = v"
      @prev="goPrevItem"
      @next="goNextItem"
      @close="goBack" />

    <!-- ═══════ ANNOUNCEMENT DETAIL ═══════ -->
    <div v-else-if="displayMode === 'announcement'" class="fixed inset-0 z-50 bg-[#0B1120] flex items-center justify-center p-4">
      <div class="absolute inset-0 opacity-100 pointer-events-none z-0"
           :style="{ backgroundImage: patternBg }"></div>
      <div class="relative z-10 max-w-4xl w-full max-h-[90vh] overflow-y-auto bg-[#0a192f] border border-accent/20 rounded-2xl md:rounded-3xl p-6 md:p-10 shadow-[0_0_60px_rgba(0,0,0,0.8)]">
        <!-- Priority Badge -->
        <div class="flex items-center justify-between mb-6">
          <span :class="priorityBadge(detailItem.priority)" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border">
            <span class="material-symbols-outlined text-sm!">{{ priorityIcon(detailItem.priority) }}</span>
            {{ detailItem.priority }}
          </span>
          <button @click="goBack" class="text-slate-400 hover:text-accent transition-colors cursor-pointer">
            <span class="material-symbols-outlined text-2xl">close</span>
          </button>
        </div>

        <!-- Title -->
        <h1 class="text-3xl lg:text-4xl font-black text-white mb-4 leading-tight">{{ detailItem.title }}</h1>

        <!-- Meta -->
        <div class="flex flex-wrap items-center gap-4 mb-8 text-sm text-slate-400">
          <span class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-accent text-base!">schedule</span>
            {{ detailItem.date }}
          </span>
          <span v-if="detailItem.audience" class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-accent text-base!">groups</span>
            {{ detailItem.audience }}
          </span>
          <span v-if="detailItem.location" class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-accent text-base!">location_on</span>
            {{ detailItem.location }}
          </span>
        </div>

        <!-- Body -->
        <div class="text-slate-200 text-lg leading-relaxed space-y-4 border-l-4 border-accent/40 pl-6">
          <div v-if="detailItem.body" class="prose prose-invert prose-lg max-w-none" v-html="fixHtmlAssetUrls(detailItem.body)"></div>
          <p v-else-if="detailItem.description">{{ detailItem.description }}</p>
        </div>

        <!-- Back button -->
        <div class="flex items-center justify-end mt-10 pt-6 border-t border-white/10">
          <button @click="goBack"
                  class="group flex items-center gap-3 px-6 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-accent/50 transition-all active:scale-95 cursor-pointer">
            <span class="text-lg font-medium text-white group-hover:text-accent transition-colors">Kembali</span>
            <span class="material-symbols-outlined text-accent group-hover:text-accent transition-colors">close</span>
          </button>
        </div>
      </div>
    </div>
  </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl, fixHtmlAssetUrls } from '../../utils/asset'
import DetailNews from '../../components/DetailNews.vue'
import DetailImage from '../../components/DetailImage.vue'
import DetailVideo from '../../components/DetailVideo.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const detailItem = ref(null)
const displayMode = ref('article')
const groupItems = ref([])
const showGroupTabs = ref(true) // sibling items sharing same time/date

// ── API endpoint map ──
const apiMap = {
  news: '/news',
  agenda: '/agendas',
  weekly: '/weeklies',
  monthly: '/monthlies',
  gallery: '/galleries',
  announcement: '/announcements',
}

// ── Group navigation ──
const currentGroupIdx = computed(() => groupItems.value.findIndex(i => i.id === detailItem.value?.id))
const hasPrevItem = computed(() => currentGroupIdx.value > 0)
const hasNextItem = computed(() => currentGroupIdx.value < groupItems.value.length - 1)

function goPrevItem() {
  if (hasPrevItem.value) switchToItem(groupItems.value[currentGroupIdx.value - 1])
}
function goNextItem() {
  if (hasNextItem.value) switchToItem(groupItems.value[currentGroupIdx.value + 1])
}

function switchToItem(item) {
  const type = route.meta.detailType
  const raw = item._raw || item
  detailItem.value = transformItem(raw, type)
  // Update URL without full navigation
  const routeNameMap = { news: 'DetailNews', agenda: 'DetailAgenda', weekly: 'DetailWeekly', monthly: 'DetailMonthly', gallery: 'DetailGallery', announcement: 'DetailAnnouncement' }
  router.replace({ name: routeNameMap[type], params: { id: item.id } })
}

// ── Fetch sibling items with same time slot ──
async function fetchGroupSiblings(currentItem, type) {
  if (!['agenda', 'weekly', 'monthly'].includes(type)) return

  const endpoint = apiMap[type]
  try {
    const params = { per_page: 100, status: 'Aktif' }

    // For weekly, also filter by day
    if (type === 'weekly' && currentItem.day) {
      params.day = currentItem.day
    }
    // For monthly, filter by month
    if (type === 'monthly' && currentItem.date) {
      const monthStr = currentItem.date.substring(0, 7)
      params.month = monthStr
    }

    const { data } = await api.get(endpoint, { params })
    const items = data.data || data || []

    // Filter items sharing same time
    const currentTime = (currentItem.time || '').substring(0, 5)
    let siblings = items.filter(i => (i.time || '').substring(0, 5) === currentTime)

    // For monthly, also match by date
    if (type === 'monthly') {
      siblings = siblings.filter(i => i.date === currentItem.date)
    }

    // Store raw data for switching
    groupItems.value = siblings.map(s => ({ ...s, _raw: s }))
  } catch {
    // Silently fail — just show single item
  }
}

// ── Transform raw API data to detail component format ──
function transformItem(raw, type) {
  const image = raw.image_path ? storageUrl(raw.image_path) : '/img/default-agenda.png'
  const videoSrc = raw.video_path ? storageUrl(raw.video_path) : null

  // Determine display mode
  if (type === 'announcement') {
    displayMode.value = 'announcement'
  } else if (raw.category === 'Video' || (type === 'gallery' && raw.video_path)) {
    displayMode.value = 'video'
  } else if (raw.category === 'Gambar') {
    displayMode.value = 'image'
  } else {
    displayMode.value = 'article'
  }

  return {
    ...raw,
    image,
    videoSrc,
    videoUrl: videoSrc,
    videoTag: raw.category || 'Video',
    time: formatTime(raw.created_at || raw.time),
    date: formatDate(raw.created_at || raw.date),
    speaker: raw.teacher || raw.speaker || null,
    description: raw.body || raw.description || '',
  }
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  if (/^\d{2}:\d{2}/.test(dateStr)) return dateStr.substring(0, 5)
  const d = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  return `${Math.floor(diff / 86400)} Hari lalu`
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  if (/^\d{2}:\d{2}/.test(dateStr)) return dateStr
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`
}

// ── Announcement Helpers ──
function priorityBadge(p) {
  if (p === 'Penting') return 'bg-red-500/20 text-red-400 border-red-500/40'
  if (p === 'Segera') return 'bg-orange-500/20 text-orange-400 border-orange-500/40'
  return 'bg-blue-500/20 text-blue-400 border-blue-500/40'
}
function priorityIcon(p) {
  if (p === 'Penting') return 'warning'
  if (p === 'Segera') return 'priority_high'
  return 'info'
}

const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`

// ── Parent route map ──
const parentRouteMap = {
  news: 'InfoTerkini',
  agenda: 'AgendaHarian',
  weekly: 'AgendaMingguan',
  monthly: 'AgendaBulanan',
  gallery: 'GalleryVideo',
  announcement: 'Pengumuman',
}

function goBack() {
  const type = route.meta.detailType
  const parentRoute = parentRouteMap[type] || 'Landing'
  router.push({ name: parentRoute })
}

onMounted(async () => {
  const type = route.meta.detailType
  const id = route.params.id
  const endpoint = apiMap[type]

  if (!endpoint) {
    error.value = `Tipe konten "${type}" tidak dikenali`
    loading.value = false
    return
  }

  try {
    const { data } = await api.get(`${endpoint}/${id}`)
    const raw = data.data || data
    detailItem.value = transformItem(raw, type)

    // Fetch sibling items for grouping (agenda types only)
    await fetchGroupSiblings(raw, type)

    // If no siblings found, at least include current item
    if (groupItems.value.length === 0) {
      groupItems.value = [{ ...raw, _raw: raw }]
    }
  } catch (e) {
    console.error(e)
    error.value = e.response?.status === 404
      ? 'Konten tidak ditemukan atau sudah dihapus'
      : 'Terjadi kesalahan saat memuat konten'
  } finally {
    loading.value = false
  }
})
</script>
