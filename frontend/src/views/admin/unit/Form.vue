<template>
  <div class="max-w-3xl mx-auto">
    <!-- Page Header -->
    <div class="flex items-center gap-4 mb-8">
      <router-link to="/administrator/manajemen-unit"
                   class="w-10 h-10 rounded-full bg-input border border-border flex items-center justify-center text-muted hover:text-accent hover:border-accent/30 transition-colors">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
      </router-link>
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-heading tracking-tight mb-1">
          {{ isEdit ? 'Edit Unit' : 'Tambah Unit' }}
        </h1>
        <p class="text-sm text-muted">Isi formulir di bawah ini untuk menyimpan data unit.</p>
      </div>
    </div>

    <!-- Form Card -->
    <div class="stat-card rounded-2xl p-6 sm:p-8">
      <form @submit.prevent="save" class="space-y-6">
        
        <!-- Field: Name -->
        <div>
          <label class="block text-sm font-bold text-heading mb-2">Nama Unit <span class="text-red-500">*</span></label>
          <input v-model="form.name" required
                 class="w-full rounded-xl bg-input border border-border px-4 py-3 text-sm text-heading placeholder-muted focus:border-accent focus:ring-1 focus:ring-accent transition-colors"
                 placeholder="Contoh: Pesantren Putra" type="text" />
        </div>

        <!-- Field: Status -->
        <div>
          <label class="block text-sm font-bold text-heading mb-2">Status <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-4">
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="radio" v-model="form.status" value="Aktif" class="hidden peer" />
              <div class="w-5 h-5 rounded-full border-2 border-border peer-checked:border-accent peer-checked:bg-accent flex items-center justify-center transition-colors group-hover:border-accent/50">
                <span class="w-2 h-2 rounded-full bg-btn-text opacity-0 peer-checked:opacity-100 transition-opacity"></span>
              </div>
              <span class="text-sm font-medium text-body peer-checked:text-heading peer-checked:font-bold transition-colors">Aktif</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer group">
              <input type="radio" v-model="form.status" value="Tidak Aktif" class="hidden peer" />
              <div class="w-5 h-5 rounded-full border-2 border-border peer-checked:border-muted peer-checked:bg-muted flex items-center justify-center transition-colors group-hover:border-muted/50">
                <span class="w-2 h-2 rounded-full bg-btn-text opacity-0 peer-checked:opacity-100 transition-opacity"></span>
              </div>
              <span class="text-sm font-medium text-body peer-checked:text-heading peer-checked:font-bold transition-colors">Tidak Aktif</span>
            </label>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 mt-6 border-t border-border flex justify-end">
          <button type="submit" :disabled="loading"
                  class="flex items-center justify-center gap-2 rounded-xl px-6 py-3 bg-accent text-btn-text font-bold transition-all hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] active:scale-95 disabled:opacity-70 disabled:active:scale-100">
            <span v-if="loading" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
            <span v-else class="material-symbols-outlined text-[20px]">save</span>
            <span>Simpan Data</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../../axios'

const router = useRouter()
const route = useRoute()

const isEdit = ref(false)
const id = route.params.id
const loading = ref(false)

const form = ref({
  name: '',
  status: 'Aktif'
})

onMounted(async () => {
  if (id) {
    isEdit.value = true
    try {
      const { data } = await api.get(`/units/${id}`)
      form.value.name = data.name
      form.value.status = data.status
    } catch (err) {
      console.error(err)
      router.push('/administrator/manajemen-unit')
    }
  }
})

async function save() {
  loading.value = true
  try {
    if (isEdit.value) {
      await api.put(`/admin/units/${route.params.id}`, form.value)
    } else {
      await api.post('/admin/units', form.value)
    }
    alert('Data unit telah disimpan.')
    router.push('/administrator/manajemen-unit')
  } catch (err) {
    alert(err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.')
  } finally {
    loading.value = false
  }
}
</script>
