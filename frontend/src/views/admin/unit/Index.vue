<template>
  <div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-heading tracking-tight mb-1">Manajemen Unit</h1>
        <p class="text-sm sm:text-base text-muted font-medium">Kelola data unit-unit dalam sistem.</p>
      </div>
      <div class="flex items-center gap-3 w-full sm:w-auto">
        <div class="relative flex-1 sm:w-64">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-muted text-[20px]">search</span>
          <input v-model="search" @input="debouncedFetch"
                 class="w-full rounded-xl bg-input border border-border py-2.5 pl-10 pr-4 text-sm text-heading placeholder-muted focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                 placeholder="Cari unit..." type="text" />
        </div>
        <router-link to="/administrator/manajemen-unit/create"
                     class="flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 bg-accent text-btn-text font-bold transition-all hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 active:scale-95">
          <span class="material-symbols-outlined text-[20px]">add</span>
          <span class="hidden sm:inline">Tambah Unit</span>
        </router-link>
      </div>
    </div>

    <!-- Table Card -->
    <div class="stat-card rounded-2xl overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead class="table-head">
            <tr>
              <th class="p-4 text-xs font-bold text-muted uppercase tracking-wider w-16 text-center">No</th>
              <th class="p-4 text-xs font-bold text-muted uppercase tracking-wider w-1/4">Nama Unit</th>
              <th class="p-4 text-xs font-bold text-muted uppercase tracking-wider w-1/4">Slug</th>
              <th class="p-4 text-xs font-bold text-muted uppercase tracking-wider text-center">Status</th>
              <th class="p-4 text-xs font-bold text-muted uppercase tracking-wider text-right w-28">Aksi</th>
            </tr>
          </thead>
          <tbody class="table-body">
            <tr v-if="loading">
              <td colspan="5" class="p-8 text-center text-muted">
                <span class="material-symbols-outlined animate-spin text-[32px] mb-2 text-accent">progress_activity</span>
                <p>Memuat data...</p>
              </td>
            </tr>
            <tr v-else-if="units.length === 0">
              <td colspan="5" class="p-8 text-center text-muted">
                <span class="material-symbols-outlined text-[48px] mb-2 opacity-50">domain_disabled</span>
                <p>Tidak ada data unit yang ditemukan.</p>
              </td>
            </tr>
            <tr v-else v-for="(unit, index) in units" :key="unit.id" class="table-row-hover group">
              <td class="p-4 text-sm text-muted text-center">{{ index + 1 }}</td>
              <td class="p-4 text-sm font-bold text-heading">{{ unit.name }}</td>
              <td class="p-4 text-sm text-body"><code class="px-2 py-1 bg-input rounded text-xs">{{ unit.slug }}</code></td>
              <td class="p-4 text-center">
                <span :class="['badge', unit.status === 'Aktif' ? 'badge-aktif' : 'badge-default']">
                  {{ unit.status }}
                </span>
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <router-link :to="`/administrator/manajemen-unit/${unit.id}/edit`"
                               class="w-8 h-8 rounded-lg bg-input flex items-center justify-center text-muted hover:text-accent hover:border hover:border-accent/30 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </router-link>
                  <button @click="confirmDelete(unit.id)"
                          class="w-8 h-8 rounded-lg bg-input flex items-center justify-center text-muted hover:text-red-400 hover:border hover:border-red-400/30 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../axios'

const units = ref([])
const loading = ref(false)
const search = ref('')
let timeout = null

async function fetchUnits() {
  loading.value = true
  try {
    const { data } = await api.get('/units', {
      params: { search: search.value }
    })
    units.value = data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function debouncedFetch() {
  if (timeout) clearTimeout(timeout)
  timeout = setTimeout(() => {
    fetchUnits()
  }, 500)
}

function confirmDelete(id) {
  if (confirm('Yakin ingin menghapus unit ini?')) {
    api.delete(`/admin/units/${id}`).then(() => {
      fetchUnits()
    }).catch(err => {
      alert('Gagal menghapus unit.')
    })
  }
}

onMounted(() => {
  fetchUnits()
})
</script>
