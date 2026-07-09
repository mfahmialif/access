<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <button @click="openCreateModal" class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95"
              style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">person_add</span>
        <span>Tambah User</span>
      </button>
    </div>
    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <!-- Search -->
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery"
               class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
               placeholder="Search users..." type="text" />
      </div>
      <!-- Filter Controls -->
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <!-- Role Filter -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Role:</span>
          <VueMultiselect
            v-model="filterRole"
            :options="roleOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="All Roles"
            class="flex-1 sm:w-[160px] sm:flex-none"
          />
        </div>
        <!-- Status Filter -->
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
            placeholder="All Status"
            class="flex-1 sm:w-[150px] sm:flex-none"
          />
        </div>
        <!-- Entries per page -->
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

    <!-- ═══ USER TABLE ═══ -->
    <div class="flex flex-col gap-4">
      <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto p-2">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="table-head">
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Name</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Email</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Role</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Status</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Last Active</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide text-right" style="color: var(--text-heading)">Actions</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-for="user in userStore.users" :key="user.id" class="table-row-hover">
                <td class="px-6 py-5">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden shadow-sm flex items-center justify-center shrink-0"
                         style="background: var(--bg-input); border: 1px solid var(--border)">
                      <span class="material-symbols-outlined text-accent text-xl">person</span>
                    </div>
                    <span class="text-sm font-medium" style="color: var(--text-heading)">{{ user.name }}</span>
                  </div>
                </td>
                <td class="px-6 py-5 text-sm" style="color: var(--text-muted)">{{ user.email }}</td>
                <td class="px-6 py-5">
                  <span :class="roleBadge(user.role?.name)">{{ user.role?.name || '-' }}</span>
                </td>
                <td class="px-6 py-5">
                  <span :class="statusBadge(user.status)">{{ user.status }}</span>
                </td>
                <td class="px-6 py-5 text-sm whitespace-nowrap" style="color: var(--text-muted)">{{ formatLastActive(user.last_active_at) }}</td>
                <td class="px-6 py-5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openEditModal(user)" class="action-btn p-2 rounded-lg transition-all duration-200" title="Edit">
                      <span class="material-symbols-outlined text-[20px]">edit</span>
                    </button>
                    <button @click="confirmDelete(user)" class="action-btn action-btn-delete p-2 rounded-lg transition-all duration-200" title="Delete">
                      <span class="material-symbols-outlined text-[20px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-bar flex items-center justify-between px-6 py-4">
          <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ userStore.pagination.from || 0 }} to {{ userStore.pagination.to || 0 }} of {{ userStore.pagination.total || 0 }} users</span>
          <div class="flex items-center gap-1.5 ml-auto">
            <button @click="goToPage(currentPage - 1)" class="page-btn p-2 rounded-lg flex items-center justify-center disabled:opacity-50 cursor-pointer" :disabled="currentPage <= 1">
              <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </button>
            <template v-for="p in pageNumbers" :key="p">
              <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-sm" style="color: var(--text-muted)">...</span>
              <button v-else @click="goToPage(p)" :class="p === currentPage ? 'page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center' : 'page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center cursor-pointer'">{{ p }}</button>
            </template>
            <button @click="goToPage(currentPage + 1)" class="page-btn p-2 rounded-lg flex items-center justify-center cursor-pointer" :disabled="currentPage >= userStore.pagination.lastPage">
              <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ MODAL ═══ -->
    <Teleport to="#admin-root" defer>
      <Transition name="fade">
        <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
          <div class="modal-card w-full max-w-lg rounded-2xl p-6 flex flex-col gap-5">
          <h3 class="text-lg font-bold" style="color: var(--text-heading)">{{ editingUser ? 'Edit User' : 'Tambah User' }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Username</label>
              <input v-model="form.username" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Username" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Nama Lengkap</label>
              <input v-model="form.name" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama lengkap" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Email</label>
              <input v-model="form.email" type="email" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="email@example.com" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Password {{ editingUser ? '(kosongkan jika tidak diubah)' : '' }}</label>
              <input v-model="form.password" type="password" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="••••••" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Role</label>
              <VueMultiselect
                v-model="formRoleOption"
                :options="modalRoleOptions"
                :close-on-select="true"
                :searchable="true"
                :allow-empty="false"
                :show-labels="false"
                label="name"
                track-by="id"
                placeholder="Pilih Role"
              />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Units</label>
              <VueMultiselect
                v-model="formUnitsOption"
                :options="modalUnitOptions"
                :multiple="true"
                :close-on-select="false"
                :clear-on-select="false"
                :searchable="true"
                label="name"
                track-by="id"
                placeholder="Pilih Unit"
              />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
              <VueMultiselect
                v-model="formStatusOption"
                :options="modalStatusOptions"
                :close-on-select="true"
                :searchable="false"
                :allow-empty="false"
                :show-labels="false"
                label="name"
                track-by="value"
                placeholder="Pilih Status"
              />
            </div>
          </div>
          <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">{{ formError }}</div>
          <div class="flex justify-end gap-3">
            <button @click="showModal = false" class="px-5 py-2 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</button>
            <button @click="saveUser" :disabled="formLoading" class="flex items-center gap-2 px-5 py-2 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50" style="color: var(--text-btn)">
              <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
              {{ editingUser ? 'Simpan' : 'Tambah' }}
            </button>
          </div>
        </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useUserStore } from '../../../stores/user'
import api from '../../../axios'

const userStore = useUserStore()
const searchQuery = ref('')

const statusOptions = [
  { name: 'All Status', value: 'all' },
  { name: 'Active', value: 'Active' },
  { name: 'Inactive', value: 'Inactive' }
]
const perPageOptions = [
  { name: '10', value: 10 },
  { name: '25', value: 25 },
  { name: '50', value: 50 },
  { name: '100', value: 100 }
]

const filterRole = ref({ name: 'All Roles', value: 'all' })
const filterStatus = ref(statusOptions[0])
const perPage = ref(perPageOptions[0])
const currentPage = ref(1)

// ── Role list for filters & modal ──
const allRoles = ref([])
const roleOptions = computed(() => [
  { name: 'All Roles', value: 'all' },
  ...allRoles.value.map(r => ({ name: r.name, value: r.name }))
])

async function fetchAllRoles() {
  try {
    const { data } = await api.get('/roles', { params: { per_page: 100 } })
    allRoles.value = data.data
  } catch { /* ignore */ }
}

// ── Modal state ──
const showModal = ref(false)
const editingUser = ref(null)
const form = ref({ username: '', name: '', email: '', password: '', role_id: '', status: 'Active', unit_ids: [] })
const formError = ref('')
const formLoading = ref(false)

// ── Fetch ──
const allUnits = ref([])
async function fetchAllUnits() {
  try {
    const { data } = await api.get('/units', { params: { per_page: 100 } })
    allUnits.value = data
  } catch { /* ignore */ }
}

function loadUsers() {
  userStore.fetchUsers({
    search: searchQuery.value || undefined,
    role: filterRole.value.value !== 'all' ? filterRole.value.value : undefined,
    status: filterStatus.value.value !== 'all' ? filterStatus.value.value : undefined,
    per_page: perPage.value.value,
    page: currentPage.value,
  })
}

onMounted(() => {
  loadUsers()
  fetchAllRoles()
  fetchAllUnits()
})

// Watchers
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { currentPage.value = 1; loadUsers() }, 400)
})
watch([filterRole, filterStatus, perPage], () => { currentPage.value = 1; loadUsers() })

// ── Pagination ──
function goToPage(page) {
  if (page >= 1 && page <= userStore.pagination.lastPage) {
    currentPage.value = page
    loadUsers()
  }
}

const pageNumbers = computed(() => {
  const last = userStore.pagination.lastPage || 1
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

// ── CRUD ──
function openCreateModal() {
  editingUser.value = null
  form.value = { username: '', name: '', email: '', password: '', role_id: '', status: 'Active', unit_ids: [] }
  formError.value = ''
  showModal.value = true
}

function openEditModal(user) {
  editingUser.value = user
  form.value = {
    username: user.username,
    name: user.name,
    email: user.email,
    password: '',
    role_id: user.role_id || user.role?.id || '',
    status: user.status,
    unit_ids: user.units?.map(u => u.id) || []
  }
  formError.value = ''
  showModal.value = true
}

async function saveUser() {
  formError.value = ''
  formLoading.value = true
  try {
    const payload = { ...form.value }
    if (editingUser.value && !payload.password) {
      delete payload.password
    }
    if (editingUser.value) {
      await userStore.updateUser(editingUser.value.id, payload)
    } else {
      await userStore.createUser(payload)
    }
    showModal.value = false
    loadUsers()
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      formError.value = Object.values(errors).flat().join(' ')
    } else {
      formError.value = e.response?.data?.message || 'Terjadi kesalahan.'
    }
  } finally {
    formLoading.value = false
  }
}

async function confirmDelete(user) {
  if (!confirm(`Hapus user "${user.name}"?`)) return
  try {
    await userStore.deleteUser(user.id)
    loadUsers()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus user.')
  }
}

// ── Modal VueMultiselect bindings ──
const modalStatusOptions = [
  { name: 'Active', value: 'Active' },
  { name: 'Inactive', value: 'Inactive' }
]
const formStatusOption = computed({
  get: () => modalStatusOptions.find(o => o.value === form.value.status) || modalStatusOptions[0],
  set: (val) => { form.value.status = val.value }
})

const modalRoleOptions = computed(() => allRoles.value)
const formRoleOption = computed({
  get: () => allRoles.value.find(r => r.id === form.value.role_id) || null,
  set: (val) => { form.value.role_id = val ? val.id : '' }
})

const modalUnitOptions = computed(() => allUnits.value)
const formUnitsOption = computed({
  get: () => allUnits.value.filter(u => form.value.unit_ids.includes(u.id)),
  set: (vals) => { form.value.unit_ids = vals ? vals.map(v => v.id) : [] }
})

// ── Helpers ──
function formatLastActive(dateStr) {
  if (!dateStr) return '-'
  const diff = Date.now() - new Date(dateStr).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days}d ago`
  return new Date(dateStr).toLocaleDateString('id-ID')
}

function roleBadge(role) {
  const base = 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold'
  if (role === 'Superadmin') return `${base} badge badge-superadmin`
  if (role === 'Admin') return `${base} badge badge-admin`
  if (role === 'Operator') return `${base} badge badge-operator`
  if (role === 'User') return `${base} badge badge-user`
  return `${base} badge badge-default`
}

function statusBadge(status) {
  if (status === 'Active') return 'badge badge-aktif'
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

/* ═══ Modal ═══ */
.modal-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
