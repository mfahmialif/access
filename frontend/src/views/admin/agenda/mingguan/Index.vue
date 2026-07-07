<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <router-link to="/administrator/agenda-mingguan/create"
                   class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95"
                   style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Agenda</span>
      </router-link>
    </div>

    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Search agenda..." type="text" />
      </div>
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Hari:</span>
          <VueMultiselect v-model="filterDay" :options="dayOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Semua" class="flex-1 sm:w-[150px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Kategori:</span>
          <VueMultiselect v-model="filterCategory" :options="categoryOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Semua" class="flex-1 sm:w-[150px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
          <VueMultiselect v-model="filterStatus" :options="statusOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="All" class="flex-1 sm:w-[140px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Show:</span>
          <VueMultiselect v-model="perPage" :options="perPageOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="10" class="w-[90px]" />
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">entries</span>
        </div>
      </div>
    </div>

    <!-- ═══ TABLE ═══ -->
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="table-head">
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Hari</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide w-20" style="color: var(--text-heading)">Waktu</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Kegiatan</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Lokasi</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Pengajar</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Status</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide text-right" style="color: var(--text-heading)">Actions</th>
            </tr>
          </thead>
          <tbody class="table-body">
            <tr v-for="item in weeklyStore.weeklies" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4">
                <span class="text-sm font-bold" :class="item.day === 'Minggu' ? 'text-red-400' : ''" :style="item.day !== 'Minggu' ? 'color: var(--text-heading)' : ''">{{ item.day }}</span>
              </td>
              <td class="px-4 py-4"><span class="text-sm font-mono font-bold" style="color: var(--text-heading)">{{ item.time?.substring(0, 5) }}</span></td>
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: var(--bg-input)">
                    <span class="material-symbols-outlined text-[20px] text-accent">{{ item.icon || 'event' }}</span>
                  </div>
                  <span class="text-sm font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</span>
                </div>
              </td>
              <td class="px-4 py-4">
                <div class="flex items-center gap-1.5 text-sm" style="color: var(--text-muted)">
                  <span class="material-symbols-outlined text-[16px]">location_on</span>
                  {{ item.location || '-' }}
                </div>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.teacher || '-' }}</td>
              <td class="px-4 py-4"><span :class="categoryBadge(item.category)">{{ item.category }}</span></td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link :to="{ name: 'AdminAgendaMingguanEdit', params: { id: item.id } }" class="action-btn p-2 rounded-lg transition-all duration-200" title="Edit">
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
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ weeklyStore.pagination.from || 0 }} to {{ weeklyStore.pagination.to || 0 }} of {{ weeklyStore.pagination.total || 0 }} items</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button @click="goToPage(currentPage - 1)" class="page-btn p-2 rounded-lg flex items-center justify-center disabled:opacity-50 cursor-pointer" :disabled="currentPage <= 1">
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-sm" style="color: var(--text-muted)">...</span>
            <button v-else @click="goToPage(p)" :class="p === currentPage ? 'page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center' : 'page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center cursor-pointer'">{{ p }}</button>
          </template>
          <button @click="goToPage(currentPage + 1)" class="page-btn p-2 rounded-lg flex items-center justify-center cursor-pointer" :disabled="currentPage >= weeklyStore.pagination.lastPage">
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
import { useWeeklyStore } from '../../../../stores/weekly'

const weeklyStore = useWeeklyStore()
const searchQuery = ref('')

const dayOptions = [
  { name: 'Semua', value: 'all' }, { name: 'Senin', value: 'Senin' }, { name: 'Selasa', value: 'Selasa' },
  { name: 'Rabu', value: 'Rabu' }, { name: 'Kamis', value: 'Kamis' }, { name: 'Jumat', value: 'Jumat' },
  { name: 'Sabtu', value: 'Sabtu' }, { name: 'Minggu', value: 'Minggu' }
]
const categoryOptions = [
  { name: 'Semua', value: 'all' }, { name: 'Artikel', value: 'Artikel' }, { name: 'Video', value: 'Video' }, { name: 'Gambar', value: 'Gambar' }
]
const statusOptions = [
  { name: 'All', value: 'all' },
  { name: 'Aktif', value: 'Aktif' },
  { name: 'Tidak Aktif', value: 'Tidak Aktif' }
]
const perPageOptions = [{ name: '10', value: 10 }, { name: '25', value: 25 }, { name: '50', value: 50 }]

const filterDay = ref(dayOptions[0])
const filterCategory = ref(categoryOptions[0])
const filterStatus = ref(statusOptions[0])
const perPage = ref(perPageOptions[0])
const currentPage = ref(1)

function loadWeeklies() {
  weeklyStore.fetchWeeklies({
    search: searchQuery.value || undefined,
    day: filterDay.value.value !== 'all' ? filterDay.value.value : undefined,
    category: filterCategory.value.value !== 'all' ? filterCategory.value.value : undefined,
    status: filterStatus.value.value !== 'all' ? filterStatus.value.value : undefined,
    per_page: perPage.value.value,
    page: currentPage.value,
  })
}

onMounted(() => loadWeeklies())

let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { currentPage.value = 1; loadWeeklies() }, 400)
})
watch([filterDay, filterCategory, filterStatus, perPage], () => { currentPage.value = 1; loadWeeklies() })

function goToPage(page) {
  if (page >= 1 && page <= weeklyStore.pagination.lastPage) {
    currentPage.value = page
    loadWeeklies()
  }
}

const pageNumbers = computed(() => {
  const last = weeklyStore.pagination.lastPage || 1
  const curr = currentPage.value
  const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }
  return pages
})

async function confirmDelete(item) {
  if (!confirm(`Hapus agenda "${item.title}"?`)) return
  try {
    await weeklyStore.deleteWeekly(item.id)
    loadWeeklies()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus agenda.')
  }
}

function categoryBadge(category) {
  const base = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  if (category === 'Artikel') return `${base} cat-badge-artikel`
  if (category === 'Video') return `${base} cat-badge-video`
  if (category === 'Gambar') return `${base} cat-badge-gambar`
  return `${base} cat-badge-default`
}

function statusBadge(status) {
  const base = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  if (status === 'Aktif') return `${base} status-badge-aktif`
  return `${base} status-badge-nonaktif`
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

.status-badge-aktif {
  color: var(--status-aktif-text, #16a34a);
  background: var(--status-aktif-bg, rgba(22, 163, 74, 0.1));
  border: 1px solid var(--status-aktif-border, rgba(22, 163, 74, 0.3));
}
.status-badge-nonaktif {
  color: var(--text-muted);
  background: var(--bg-input);
  border: 1px solid var(--border);
}
.cat-badge-artikel {
  color: var(--color-accent);
  background: rgba(251, 191, 36, 0.1);
  border: 1px solid rgba(251, 191, 36, 0.3);
}
.cat-badge-video {
  color: var(--cat-video-text, #ef4444);
  background: var(--cat-video-bg, rgba(239, 68, 68, 0.08));
  border: 1px solid var(--cat-video-border, rgba(239, 68, 68, 0.25));
}
.cat-badge-gambar {
  color: var(--cat-gambar-text, #3b82f6);
  background: var(--cat-gambar-bg, rgba(59, 130, 246, 0.08));
  border: 1px solid var(--cat-gambar-border, rgba(59, 130, 246, 0.25));
}
.cat-badge-default {
  color: var(--text-muted);
  background: var(--bg-input);
  border: 1px solid var(--border);
}
</style>
