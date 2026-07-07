<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <router-link :to="{ name: 'AdminAppsCreate' }" class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Link</span>
      </router-link>
    </div>

    <!-- ═══ STATS ROW ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="stat in statsCards" :key="stat.label" class="stat-card rounded-xl p-4 flex items-center gap-4 border border-transparent">
        <div class="p-3 rounded-lg" :class="stat.iconBg">
          <span class="material-symbols-outlined text-[24px]" :class="stat.iconColor">{{ stat.icon }}</span>
        </div>
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
        <input v-model="searchQuery" @input="debouncedFetch" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Cari link..." type="text" />
      </div>
      <div class="flex items-center gap-2">
        <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
        <VueMultiselect v-model="filterStatus" :options="statusOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" @select="fetchData" class="w-[150px]" />
      </div>
    </div>

    <!-- ═══ LOADING ═══ -->
    <div v-if="loading" class="flex justify-center py-12">
      <span class="material-symbols-outlined text-accent text-4xl animate-spin">progress_activity</span>
    </div>

    <!-- ═══ CONTENT TABLE ═══ -->
    <div v-else class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold w-16" style="color: var(--text-heading)">#</th>
            <th class="px-4 py-4 text-sm font-semibold w-16" style="color: var(--text-heading)">Icon</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Judul</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">URL</th>
            <th class="px-4 py-4 text-sm font-semibold w-20" style="color: var(--text-heading)">Urutan</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-if="items.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Tidak ada data link</td>
            </tr>
            <tr v-for="(item, idx) in items" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4 text-sm font-mono" style="color: var(--text-muted)">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
              <td class="px-4 py-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="colorIconBg(item.color)">
                  <span class="material-symbols-outlined text-[22px]" :class="colorIconText(item.color)" style="font-variation-settings: 'FILL' 1;">{{ item.icon }}</span>
                </div>
              </td>
              <td class="px-4 py-4">
                <span class="text-sm font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</span>
                <span v-if="item.subtitle" class="text-xs block mt-0.5" style="color: var(--text-muted)">{{ item.subtitle }}</span>
              </td>
              <td class="px-4 py-4">
                <span class="text-sm font-mono line-clamp-1" style="color: var(--text-muted)">{{ item.url }}</span>
              </td>
              <td class="px-4 py-4 text-sm text-center font-bold" style="color: var(--text-body)">{{ item.sort_order }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link :to="{ name: 'AdminAppsEdit', params: { id: item.id } }" class="action-btn p-2 rounded-lg cursor-pointer" title="Edit"><span class="material-symbols-outlined text-[20px] text-accent">edit</span></router-link>
                  <button @click="confirmDelete(item)" class="action-btn action-btn-delete p-2 rounded-lg cursor-pointer" title="Delete"><span class="material-symbols-outlined text-[20px] text-accent">delete</span></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination-bar flex items-center justify-between px-6 py-4">
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ items.length }} of {{ totalItems }} items</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button @click="goPage(currentPage - 1)" :disabled="currentPage <= 1" class="page-btn p-2 rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"><span class="material-symbols-outlined text-[20px]">chevron_left</span></button>
          <template v-for="p in totalPages" :key="p">
            <button @click="goPage(p)" :class="p === currentPage ? 'page-btn-active' : 'page-btn'" class="w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center cursor-pointer">{{ p }}</button>
          </template>
          <button @click="goPage(currentPage + 1)" :disabled="currentPage >= totalPages" class="page-btn p-2 rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"><span class="material-symbols-outlined text-[20px]">chevron_right</span></button>
        </div>
      </div>
    </div>

    <!-- ═══ DELETE CONFIRM ═══ -->
    <Transition name="modal">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>
        <div class="relative w-full max-w-md rounded-2xl p-6 shadow-2xl text-center" style="background: var(--bg-card); border: 1px solid var(--border)">
          <span class="material-symbols-outlined text-red-400 text-5xl mb-3">warning</span>
          <h3 class="text-lg font-bold mb-2" style="color: var(--text-heading)">Hapus Link?</h3>
          <p class="text-sm mb-6" style="color: var(--text-muted)">{{ deletingItem?.title }}</p>
          <div class="flex justify-center gap-3">
            <button @click="showDeleteConfirm = false" class="px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer" style="color: var(--text-muted); background: var(--bg-input); border: 1px solid var(--border)">Batal</button>
            <button @click="deleteItem" :disabled="deleting" class="px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer bg-red-500 text-white disabled:opacity-50">{{ deleting ? 'Menghapus...' : 'Hapus' }}</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ═══ TOAST ═══ -->
    <Transition name="toast">
      <div v-if="toast.show" class="fixed top-6 right-6 z-60 max-w-sm" :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border">
          <span class="material-symbols-outlined text-lg">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
          <span class="text-sm font-medium">{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../../../axios'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

// ── State ──
const loading = ref(false)
const items = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = 10

const searchQuery = ref('')
const statusOptions = [{ name: 'All Status', value: 'all' }, { name: 'Published', value: 'Published' }, { name: 'Draft', value: 'Draft' }]
const filterStatus = ref(statusOptions[0])

// ── Stats ──
const statsCards = ref([
  { label: 'Total Link', value: 0, icon: 'apps', iconBg: 'bg-accent/10', iconColor: 'text-accent' },
  { label: 'Published', value: 0, icon: 'check_circle', iconBg: 'bg-green-500/10', iconColor: 'text-green-400' },
  { label: 'Draft', value: 0, icon: 'edit_note', iconBg: 'bg-yellow-500/10', iconColor: 'text-yellow-400' },
])

// ── Delete ──
const showDeleteConfirm = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

// ── Toast ──
const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimeout = null
function showToast(msg, type = 'success') {
  clearTimeout(toastTimeout)
  toast.show = true; toast.message = msg; toast.type = type
  toastTimeout = setTimeout(() => { toast.show = false }, 4000)
}

// ── Fetch ──
let debounceTimer = null
function debouncedFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 300) }

async function fetchData() {
  loading.value = true
  try {
    const params = { page: currentPage.value, per_page: perPage }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStatus.value?.value !== 'all') params.status = filterStatus.value.value

    const { data } = await api.get('/app-links', { params })
    items.value = data.data
    totalPages.value = data.last_page
    totalItems.value = data.total
    currentPage.value = data.current_page
  } catch { showToast('Gagal memuat data', 'error') }
  loading.value = false
}

async function fetchStats() {
  try {
    const [all, published, draft] = await Promise.all([
      api.get('/app-links', { params: { per_page: 1 } }),
      api.get('/app-links', { params: { per_page: 1, status: 'Published' } }),
      api.get('/app-links', { params: { per_page: 1, status: 'Draft' } }),
    ])
    statsCards.value[0].value = all.data.total
    statsCards.value[1].value = published.data.total
    statsCards.value[2].value = draft.data.total
  } catch { /* silent */ }
}

function goPage(p) { if (p < 1 || p > totalPages.value) return; currentPage.value = p; fetchData() }

// ── Delete ──
function confirmDelete(item) { deletingItem.value = item; showDeleteConfirm.value = true }
async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/app-links/${deletingItem.value.id}`)
    showToast('Link berhasil dihapus')
    showDeleteConfirm.value = false
    fetchData(); fetchStats()
  } catch (e) { showToast(e.response?.data?.message || 'Gagal menghapus', 'error') }
  deleting.value = false
}

// ── Helpers ──
const colorMap = {
  amber:   { bg: 'bg-amber-500/15',   text: 'text-amber-400' },
  blue:    { bg: 'bg-blue-500/15',     text: 'text-blue-400' },
  emerald: { bg: 'bg-emerald-500/15',  text: 'text-emerald-400' },
  violet:  { bg: 'bg-violet-500/15',   text: 'text-violet-400' },
  rose:    { bg: 'bg-rose-500/15',     text: 'text-rose-400' },
  cyan:    { bg: 'bg-cyan-500/15',     text: 'text-cyan-400' },
}
function colorIconBg(c) { return colorMap[c]?.bg || 'bg-accent/15' }
function colorIconText(c) { return colorMap[c]?.text || 'text-accent' }

function statusBadge(s) {
  if (s === 'Published') return 'badge badge-published'
  return 'badge badge-draft'
}

onMounted(() => { fetchData(); fetchStats() })
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

.modal-enter-active { animation: modalIn 0.3s ease-out; }
.modal-leave-active { animation: modalOut 0.2s ease-in; }
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
@keyframes modalOut { from { opacity: 1; } to { opacity: 0; } }

.toast-success { background: #065f46; border-color: #10b981; color: #6ee7b7; }
.toast-error { background: #7f1d1d; border-color: #ef4444; color: #fca5a5; }
.toast-enter-active { animation: toastIn 0.3s ease-out; }
.toast-leave-active { animation: toastOut 0.2s ease-in; }
@keyframes toastIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
@keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateX(100px); } }
</style>
