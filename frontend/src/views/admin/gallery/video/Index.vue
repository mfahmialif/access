<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <router-link to="/administrator/gallery-video/create"
                   class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95"
                   style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Upload Media</span>
      </router-link>
    </div>

    <!-- ═══ STATS ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="stat in statsCards" :key="stat.label" class="stat-card rounded-xl p-4 flex items-center gap-4 border border-transparent" style="background: var(--bg-card)">
        <div class="p-3 rounded-lg" :class="stat.iconBg"><span class="material-symbols-outlined text-[24px]" :class="stat.iconColor">{{ stat.icon }}</span></div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ stat.label }}</p>
          <p class="text-2xl font-bold" style="color: var(--text-heading)">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Search media..." type="text" />
      </div>
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Tipe:</span>
          <VueMultiselect v-model="filterType" :options="typeOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" class="flex-1 sm:w-[140px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
          <VueMultiselect v-model="filterStatus" :options="statusOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" class="flex-1 sm:w-[150px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Show:</span>
          <VueMultiselect v-model="perPage" :options="perPageOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" class="w-[90px]" />
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">entries</span>
        </div>
      </div>
    </div>

    <!-- ═══ TABLE ═══ -->
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold tracking-wide w-16" style="color: var(--text-heading)">#</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Thumbnail</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Judul</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Tipe</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Durasi</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Upload</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold tracking-wide text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-for="(item, idx) in galleryStore.galleries" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4 text-sm font-mono" style="color: var(--text-muted)">{{ (galleryStore.pagination.from || 0) + idx }}</td>
              <td class="px-4 py-4">
                <div class="w-16 h-10 rounded-lg overflow-hidden bg-cover bg-center border" :style="{ backgroundImage: `url('${thumbUrl(item)}')`, borderColor: 'var(--border)' }">
                  <div v-if="item.category === 'Video'" class="w-full h-full flex items-center justify-center bg-black/40"><span class="material-symbols-outlined text-white text-[18px]">play_arrow</span></div>
                </div>
              </td>
              <td class="px-4 py-4"><span class="text-sm font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</span></td>
              <td class="px-4 py-4"><span :class="typeBadge(item.category)">{{ item.category }}</span></td>
              <td class="px-4 py-4 text-sm font-mono" style="color: var(--text-muted)">{{ formatDuration(item.duration) }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-muted)">{{ formatDate(item.created_at) }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link :to="{ name: 'AdminGalleryVideoEdit', params: { id: item.id } }" class="action-btn p-2 rounded-lg transition-all duration-200" title="Edit">
                    <span class="material-symbols-outlined text-[20px] text-accent">edit</span>
                  </router-link>
                  <button @click="confirmDelete(item)" class="action-btn action-btn-delete p-2 rounded-lg transition-all duration-200 cursor-pointer" title="Delete">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination-bar flex items-center justify-between px-6 py-4">
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ galleryStore.pagination.from || 0 }} to {{ galleryStore.pagination.to || 0 }} of {{ galleryStore.pagination.total || 0 }} items</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button @click="goToPage(currentPage - 1)" class="page-btn p-2 rounded-lg flex items-center justify-center disabled:opacity-50 cursor-pointer" :disabled="currentPage <= 1">
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-sm" style="color: var(--text-muted)">...</span>
            <button v-else @click="goToPage(p)" :class="p === currentPage ? 'page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center' : 'page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center cursor-pointer'">{{ p }}</button>
          </template>
          <button @click="goToPage(currentPage + 1)" class="page-btn p-2 rounded-lg flex items-center justify-center cursor-pointer" :disabled="currentPage >= galleryStore.pagination.lastPage">
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import { useGalleryStore } from '../../../../stores/gallery'
import { storageUrl } from '../../../../utils/asset'

const galleryStore = useGalleryStore()
const searchQuery = ref('')

const typeOptions = [{ name: 'Semua', value: 'all' }, { name: 'Gambar', value: 'Gambar' }, { name: 'Video', value: 'Video' }]
const statusOptions = [{ name: 'All', value: 'all' }, { name: 'Published', value: 'Published' }, { name: 'Draft', value: 'Draft' }]
const perPageOptions = [{ name: '10', value: 10 }, { name: '25', value: 25 }, { name: '50', value: 50 }]

const filterType = ref(typeOptions[0])
const filterStatus = ref(statusOptions[0])
const perPage = ref(perPageOptions[0])
const currentPage = ref(1)

const statsCards = computed(() => [
  { label: 'Total Media', value: galleryStore.stats.total, icon: 'perm_media', iconBg: 'bg-accent/10', iconColor: 'text-accent' },
  { label: 'Foto', value: galleryStore.stats.gambar, icon: 'photo_camera', iconBg: 'bg-blue-500/10', iconColor: 'text-blue-400' },
  { label: 'Video', value: galleryStore.stats.video, icon: 'videocam', iconBg: 'bg-red-500/10', iconColor: 'text-red-400' }
])

function thumbUrl(item) {
  if (item.image_path) return storageUrl(item.image_path)
  return '/img/default-agenda.png'
}

function formatDuration(seconds) {
  if (!seconds) return '—'
  const m = Math.floor(seconds / 60)
  return m >= 60 ? `${Math.floor(m / 60)}j ${m % 60}m` : `${m} menit`
}

function formatDate(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}

function loadGalleries() {
  galleryStore.fetchGalleries({
    search: searchQuery.value || undefined,
    category: filterType.value.value !== 'all' ? filterType.value.value : undefined,
    status: filterStatus.value.value !== 'all' ? filterStatus.value.value : undefined,
    per_page: perPage.value.value,
    page: currentPage.value,
  })
}

onMounted(() => { loadGalleries(); galleryStore.fetchStats() })

let searchTimeout
watch(searchQuery, () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { currentPage.value = 1; loadGalleries() }, 400) })
watch([filterType, filterStatus, perPage], () => { currentPage.value = 1; loadGalleries() })

function goToPage(page) {
  if (page >= 1 && page <= galleryStore.pagination.lastPage) { currentPage.value = page; loadGalleries() }
}

const pageNumbers = computed(() => {
  const last = galleryStore.pagination.lastPage || 1
  const curr = currentPage.value
  const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) pages.push(i)
    else if (pages[pages.length - 1] !== '...') pages.push('...')
  }
  return pages
})

async function confirmDelete(item) {
  if (!confirm(`Hapus media "${item.title}"?`)) return
  try { await galleryStore.deleteGallery(item.id); loadGalleries(); galleryStore.fetchStats() }
  catch (e) { alert(e.response?.data?.message || 'Gagal menghapus media.') }
}

function typeBadge(type) {
  const b = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  return type === 'Gambar' ? `${b} bg-blue-900/30 text-blue-400 border border-blue-800/40` : `${b} bg-red-900/30 text-red-400 border border-red-800/40`
}
function statusBadge(s) {
  const b = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  return s === 'Published' ? `${b} text-green-400 border border-green-500/30 bg-green-900/20` : `${b} text-yellow-400 border border-yellow-500/30 bg-yellow-900/20`
}
</script>

<style scoped>
.filter-input { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }
.action-btn { color: var(--text-muted); }
.action-btn:hover { color: var(--color-accent); background: var(--bg-input); }
.action-btn-delete:hover { color: #f87171; background: var(--bg-input); }
.pagination-bar { border-top: 1px solid var(--border); background: var(--bg-card); }
.page-btn { color: var(--text-muted); border: 1px solid transparent; transition: all 0.2s ease; }
.page-btn:hover { background: var(--bg-input); color: var(--text-heading); }
.page-btn-active { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }
</style>
