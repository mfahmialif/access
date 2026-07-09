<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <router-link :to="{ name: 'AdminAgendaHarianCreate' }"
                   class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95"
                   style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Agenda</span>
      </router-link>
    </div>

    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <!-- Search -->
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery"
               class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
               placeholder="Search agenda..." type="text" />
      </div>
      <!-- Filter Controls -->
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <!-- Kategori -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Kategori:</span>
          <VueMultiselect
            v-model="filterCategory"
            :options="categoryOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="Semua"
            class="flex-1 sm:w-[160px] sm:flex-none"
          />
        </div>
        <!-- Status -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
          <VueMultiselect
            v-model="filterStatus"
            :options="statusOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="All"
            class="flex-1 sm:w-[150px] sm:flex-none"
          />
        </div>
        <!-- Entries -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Show:</span>
          <VueMultiselect
            v-model="perPage"
            :options="perPageOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="10"
            class="w-[90px]"
          />
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">entries</span>
        </div>
      </div>
    </div>

    <!-- ═══ AGENDA TABLE ═══ -->
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="table-head">
              <th class="px-4 py-4 text-sm font-semibold tracking-wide w-24" style="color: var(--text-heading)">Waktu</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Kegiatan</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Lokasi</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Pengajar</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Status</th>
              <th v-if="unitStore.activeUnitId === 'all'" class="px-4 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Unit</th>
              <th class="px-4 py-4 text-sm font-semibold tracking-wide text-right" style="color: var(--text-heading)">Actions</th>
            </tr>
          </thead>
          <tbody class="table-body">
            <tr v-for="item in agendaStore.agendas" :key="item.id" class="table-row-hover">
              <!-- Time -->
              <td class="px-4 py-4">
                <span class="text-sm font-mono font-bold" style="color: var(--text-heading)">{{ item.time?.substring(0, 5) }}</span>
              </td>
              <!-- Activity name + icon -->
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background: var(--bg-input)">
                    <span class="material-symbols-outlined text-[20px] text-accent">{{ item.icon || 'event' }}</span>
                  </div>
                  <span class="text-sm font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</span>
                </div>
              </td>
              <!-- Location -->
              <td class="px-4 py-4">
                <div class="flex items-center gap-1.5 text-sm" style="color: var(--text-muted)">
                  <span class="material-symbols-outlined text-[16px]">location_on</span>
                  {{ item.location || '-' }}
                </div>
              </td>
              <!-- Teacher -->
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.teacher || '-' }}</td>
              <!-- Category -->
              <td class="px-4 py-4">
                <span :class="categoryBadge(item.category)">{{ item.category }}</span>
              </td>
              <!-- Status -->
              <td class="px-4 py-4">
                <span :class="agendaStatusBadge(item.status)">{{ item.status }}</span>
              </td>
              <!-- Unit -->
              <td v-if="unitStore.activeUnitId === 'all'" class="px-4 py-4 text-sm" style="color: var(--text-heading)">
                {{ item.unit?.name || '-' }}
              </td>
              <!-- Actions -->
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link :to="{ name: 'AdminAgendaHarianEdit', params: { id: item.id } }" class="action-btn p-2 rounded-lg transition-all duration-200" title="Edit">
                    <span class="material-symbols-outlined text-[20px] text-accent">edit</span>
                  </router-link>
                  <button @click="confirmDelete(item)" class="action-btn action-btn-delete p-2 rounded-lg transition-all duration-200" title="Delete">
                    <span class="material-symbols-outlined text-[20px] text-accent">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-bar flex items-center justify-between px-6 py-4">
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ agendaStore.pagination.from || 0 }} to {{ agendaStore.pagination.to || 0 }} of {{ agendaStore.pagination.total || 0 }} items</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button @click="goToPage(currentPage - 1)" class="page-btn p-2 rounded-lg flex items-center justify-center disabled:opacity-50 cursor-pointer" :disabled="currentPage <= 1">
            <span class="material-symbols-outlined text-[20px]">chevron_left</span>
          </button>
          <template v-for="p in pageNumbers" :key="p">
            <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-sm" style="color: var(--text-muted)">...</span>
            <button v-else @click="goToPage(p)" :class="p === currentPage ? 'page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center' : 'page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center cursor-pointer'">{{ p }}</button>
          </template>
          <button @click="goToPage(currentPage + 1)" class="page-btn p-2 rounded-lg flex items-center justify-center cursor-pointer" :disabled="currentPage >= agendaStore.pagination.lastPage">
            <span class="material-symbols-outlined text-[20px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAgendaStore } from '../../../../stores/agenda'
import { useUnitStore } from '../../../../stores/unit'

const agendaStore = useAgendaStore()
const unitStore = useUnitStore()
const router = useRouter()
const searchQuery = ref('')

const categoryOptions = [
  { name: 'Semua', value: 'all' },
  { name: 'Artikel', value: 'Artikel' },
  { name: 'Video', value: 'Video' },
  { name: 'Gambar', value: 'Gambar' }
]
const statusOptions = [
  { name: 'All', value: 'all' },
  { name: 'Aktif', value: 'Aktif' },
  { name: 'Tidak Aktif', value: 'Tidak Aktif' }
]
const perPageOptions = [
  { name: '10', value: 10 },
  { name: '25', value: 25 },
  { name: '50', value: 50 }
]

const filterCategory = ref(categoryOptions[0])
const filterStatus = ref(statusOptions[0])
const perPage = ref(perPageOptions[0])
const currentPage = ref(1)

// ── Fetch ──
function loadAgendas() {
  agendaStore.fetchAgendas({
    search: searchQuery.value || undefined,
    category: filterCategory.value.value !== 'all' ? filterCategory.value.value : undefined,
    status: filterStatus.value.value !== 'all' ? filterStatus.value.value : undefined,
    per_page: perPage.value.value,
    page: currentPage.value,
  })
}

onMounted(() => loadAgendas())

// Watchers
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { currentPage.value = 1; loadAgendas() }, 400)
})
watch([filterCategory, filterStatus, perPage], () => { currentPage.value = 1; loadAgendas() })

// ── Pagination ──
function goToPage(page) {
  if (page >= 1 && page <= agendaStore.pagination.lastPage) {
    currentPage.value = page
    loadAgendas()
  }
}

const pageNumbers = computed(() => {
  const last = agendaStore.pagination.lastPage || 1
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

// ── Delete ──
async function confirmDelete(item) {
  if (!confirm(`Hapus agenda "${item.title}"?`)) return
  try {
    await agendaStore.deleteAgenda(item.id)
    loadAgendas()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus agenda.')
  }
}

// ── Badges ──
function categoryBadge(category) {
  if (category === 'Artikel') return 'badge badge-artikel'
  if (category === 'Video') return 'badge badge-video'
  if (category === 'Gambar') return 'badge badge-gambar'
  return 'badge badge-default'
}

function agendaStatusBadge(status) {
  if (status === 'Aktif') return 'badge badge-aktif'
  return 'badge badge-default'
}
</script>

<style scoped>
/* ═══ Filter Inputs ═══ */
.filter-input {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-heading);
  transition: box-shadow 0.3s ease;
}
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

/* ═══ Action Buttons ═══ */
.action-btn { color: var(--text-muted); }
.action-btn:hover { color: var(--color-accent); background: var(--bg-input); }
.action-btn-delete:hover { color: #f87171; background: var(--bg-input); }

/* ═══ Pagination ═══ */
.pagination-bar { border-top: 1px solid var(--border); background: var(--bg-card); }
.page-btn { color: var(--text-muted); border: 1px solid transparent; transition: all 0.2s ease; }
.page-btn:hover { background: var(--bg-input); color: var(--text-heading); }
.page-btn-active { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }

/* ═══ Status Badges ═══ */
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
/* ═══ Category Badges ═══ */
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
