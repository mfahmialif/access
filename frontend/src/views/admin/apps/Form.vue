<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ HEADER ═══ -->
    <div class="flex items-center gap-4">
      <router-link to="/administrator/apps"
                   class="flex items-center gap-1 text-sm font-medium cursor-pointer transition-colors hover:text-accent"
                   style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Link' : 'Tambah Link' }}</h2>
    </div>

    <!-- ═══ LOADING ═══ -->
    <div v-if="pageLoading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-accent text-4xl animate-spin">progress_activity</span>
    </div>

    <!-- ═══ FORM CARD ═══ -->
    <div v-else class="form-card rounded-2xl p-6 flex flex-col gap-6">

      <!-- ── Row 1: Judul + Subtitle ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Judul *</label>
          <input v-model="form.title" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama portal" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Subtitle</label>
          <input v-model="form.subtitle" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Deskripsi singkat" />
        </div>
      </div>

      <!-- ── Row 2: URL ── -->
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">URL *</label>
        <input v-model="form.url" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="https://contoh.com atau /path" />
      </div>

      <!-- ── Row 3: Icon + Color + Sort + Status ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Icon *</label>
          <div class="flex items-center gap-2">
            <input v-model="form.icon" class="filter-input flex-1 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="menu_book" />
            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="colorIconBg(form.color)">
              <span class="material-symbols-outlined text-[22px]" :class="colorIconText(form.color)" style="font-variation-settings: 'FILL' 1;">{{ form.icon || 'link' }}</span>
            </div>
          </div>
          <p class="text-[11px]" style="color: var(--text-muted)">Nama icon dari <a href="https://fonts.google.com/icons" target="_blank" class="text-accent underline">Material Symbols</a></p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Warna</label>
          <VueMultiselect v-model="formColorOption" :options="colorOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Warna" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Urutan</label>
          <input v-model.number="form.sort_order" type="number" min="0" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="0" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
          <VueMultiselect v-model="formStatusOption" :options="statusOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Status" />
        </div>
      </div>

      <!-- ── Error ── -->
      <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">{{ formError }}</div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2">
        <router-link to="/administrator/apps" class="px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</router-link>
        <button @click="handleSubmit" :disabled="formLoading" class="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50 shadow-[0_0_15px_rgba(251,191,36,0.3)]" style="color: var(--text-btn)">
          <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          {{ isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import api from '../../../axios'

const router = useRouter()
const route = useRoute()
const isEdit = computed(() => !!route.params.id)
const pageLoading = ref(false)
const formLoading = ref(false)
const formError = ref('')

const form = ref({
  title: '', subtitle: '', icon: 'link', url: '',
  color: 'amber', sort_order: 0, status: 'Published',
})

// ── Options ──
const colorOptions = [
  { name: '🟡 Amber', value: 'amber' },
  { name: '🔵 Blue', value: 'blue' },
  { name: '🟢 Emerald', value: 'emerald' },
  { name: '🟣 Violet', value: 'violet' },
  { name: '🔴 Rose', value: 'rose' },
  { name: '🩵 Cyan', value: 'cyan' },
]
const statusOptions = [{ name: 'Published', value: 'Published' }, { name: 'Draft', value: 'Draft' }]

const formColorOption = computed({
  get: () => colorOptions.find(o => o.value === form.value.color) || colorOptions[0],
  set: (val) => { form.value.color = val.value }
})
const formStatusOption = computed({
  get: () => statusOptions.find(o => o.value === form.value.status) || statusOptions[0],
  set: (val) => { form.value.status = val.value }
})

// ── Color helpers ──
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

// ── Load for edit ──
onMounted(async () => {
  if (isEdit.value) {
    pageLoading.value = true
    try {
      const { data } = await api.get(`/app-links/${route.params.id}`)
      form.value = {
        title: data.title || '', subtitle: data.subtitle || '',
        icon: data.icon || 'link', url: data.url || '',
        color: data.color || 'amber', sort_order: data.sort_order ?? 0,
        status: data.status || 'Published',
      }
    } catch { formError.value = 'Gagal memuat data.' }
    pageLoading.value = false
  }
})

// ── Submit ──
async function handleSubmit() {
  formError.value = ''; formLoading.value = true
  try {
    const payload = { ...form.value }

    if (isEdit.value) {
      await api.put(`/app-links/${route.params.id}`, payload)
    } else {
      await api.post('/app-links', payload)
    }
    router.push({ name: 'AdminApps' })
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Terjadi kesalahan.')
  } finally { formLoading.value = false }
}
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }
</style>
