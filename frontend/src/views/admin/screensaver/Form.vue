<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ HEADER ═══ -->
    <div class="flex items-center gap-4">
      <router-link to="/administrator/screensaver"
                   class="flex items-center gap-1 text-sm font-medium cursor-pointer transition-colors hover:text-accent"
                   style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Screensaver' : 'Tambah Screensaver' }}</h2>
    </div>

    <!-- ═══ LOADING ═══ -->
    <div v-if="pageLoading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-accent text-4xl animate-spin">progress_activity</span>
    </div>

    <!-- ═══ FORM CARD ═══ -->
    <div v-else class="form-card rounded-2xl p-6 flex flex-col gap-6">

      <!-- ── Row 1: TV Target + Status ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">TV Target</label>
          <VueMultiselect v-model="formTvOption" :options="tvOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih TV" />
          <p class="text-[11px]" style="color: var(--text-muted)">Pilih "Default" untuk berlaku di semua TV yang tidak punya screensaver sendiri</p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
          <div class="flex items-center gap-3 h-10">
            <button @click="form.is_active = !form.is_active"
                    class="toggle-btn relative w-12 h-6 rounded-full transition-colors duration-300 cursor-pointer"
                    :class="form.is_active ? 'bg-green-500' : 'toggle-inactive'">
              <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-300"
                    :class="form.is_active ? 'translate-x-6' : ''"></span>
            </button>
            <span class="text-sm font-bold" :class="form.is_active ? 'text-green-400' : ''" :style="!form.is_active ? 'color: var(--text-muted)' : ''">
              {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── Row 2: Idle Timeout + Interval ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Idle Timeout (detik) *</label>
          <input v-model.number="form.idle_timeout" type="number" min="1" max="3600" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="30" />
          <p class="text-[11px]" style="color: var(--text-muted)">Berapa detik idle sebelum screensaver muncul</p>
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Interval Gambar (detik) *</label>
          <input v-model.number="form.interval" type="number" min="1" max="120" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="8" />
          <p class="text-[11px]" style="color: var(--text-muted)">Durasi tampil setiap gambar sebelum berganti</p>
        </div>
      </div>

      <!-- ── Image Upload Area ── -->
      <div class="flex flex-col gap-3">
        <label class="text-sm font-medium" style="color: var(--text-body)">Gambar Screensaver</label>

        <!-- Existing images (edit mode) -->
        <div v-if="existingImages.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
          <div v-for="(img, idx) in existingImages" :key="img.id" class="image-card relative group rounded-xl overflow-hidden border" style="border-color: var(--border)">
            <img :src="img.url" :alt="'Screensaver ' + (idx + 1)" class="w-full h-32 object-cover" />
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
              <button @click="removeExistingImage(img)" class="p-2 rounded-lg bg-red-500/80 text-white cursor-pointer hover:bg-red-500 transition-colors">
                <span class="material-symbols-outlined text-[20px]">delete</span>
              </button>
            </div>
            <div class="absolute bottom-1 left-1 px-2 py-0.5 rounded text-[10px] font-bold bg-black/60 text-white">
              #{{ idx + 1 }}
            </div>
          </div>
        </div>

        <!-- Upload zone -->
        <div class="upload-zone rounded-xl border-2 border-dashed p-6 text-center cursor-pointer transition-colors"
             :class="isDragging ? 'border-accent bg-accent/5' : ''"
             @click="triggerFileInput"
             @dragover.prevent="isDragging = true"
             @dragleave.prevent="isDragging = false"
             @drop.prevent="handleDrop">
          <input ref="fileInputRef" type="file" multiple accept="image/*" class="hidden" @change="handleFileSelect" />
          <span class="material-symbols-outlined text-4xl mb-2" :class="isDragging ? 'text-accent' : ''" :style="!isDragging ? 'color: var(--text-muted)' : ''">cloud_upload</span>
          <p class="text-sm font-medium" style="color: var(--text-body)">Klik atau seret gambar ke sini</p>
          <p class="text-xs mt-1" style="color: var(--text-muted)">JPG, PNG, WEBP — maks 10MB per file</p>
        </div>

        <!-- New files preview -->
        <div v-if="newFiles.length" class="flex flex-col gap-2">
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Gambar baru ({{ newFiles.length }})</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            <div v-for="(file, idx) in newFilePreviews" :key="idx" class="image-card relative group rounded-xl overflow-hidden border" style="border-color: var(--border)">
              <img :src="file.preview" :alt="file.name" class="w-full h-32 object-cover" />
              <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button @click="removeNewFile(idx)" class="p-2 rounded-lg bg-red-500/80 text-white cursor-pointer hover:bg-red-500 transition-colors">
                  <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
              </div>
              <div class="absolute bottom-1 right-1 px-2 py-0.5 rounded text-[10px] font-bold bg-accent/80 text-black">
                Baru
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Error ── -->
      <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">{{ formError }}</div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2">
        <router-link to="/administrator/screensaver" class="px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</router-link>
        <button @click="handleSubmit" :disabled="formLoading" class="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50 shadow-[0_0_15px_rgba(251,191,36,0.3)]" style="color: var(--text-btn)">
          <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          {{ isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </div>

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
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue'
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
  tv_device_id: null,
  idle_timeout: 30,
  interval: 8,
  is_active: true,
})

// ── TV Options ──
const tvOptions = ref([{ name: 'Default (Semua TV)', value: null }])
const formTvOption = computed({
  get: () => tvOptions.value.find(o => o.value === form.value.tv_device_id) || tvOptions.value[0],
  set: (val) => { form.value.tv_device_id = val.value }
})

// ── Image state ──
const existingImages = ref([])
const newFiles = ref([])
const newFilePreviews = ref([])
const isDragging = ref(false)
const fileInputRef = ref(null)

// ── Toast ──
const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimeout = null
function showToast(msg, type = 'success') {
  clearTimeout(toastTimeout)
  toast.show = true; toast.message = msg; toast.type = type
  toastTimeout = setTimeout(() => { toast.show = false }, 4000)
}

// ── File Handling ──
function triggerFileInput() {
  fileInputRef.value?.click()
}

function handleFileSelect(e) {
  addFiles(Array.from(e.target.files))
  e.target.value = '' // reset so same file can be selected again
}

function handleDrop(e) {
  isDragging.value = false
  const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'))
  addFiles(files)
}

function addFiles(files) {
  for (const file of files) {
    newFiles.value.push(file)
    const reader = new FileReader()
    reader.onload = (e) => {
      newFilePreviews.value.push({ name: file.name, preview: e.target.result })
    }
    reader.readAsDataURL(file)
  }
}

function removeNewFile(idx) {
  newFiles.value.splice(idx, 1)
  // Revoke URL to prevent memory leak
  const preview = newFilePreviews.value[idx]
  newFilePreviews.value.splice(idx, 1)
}

async function removeExistingImage(img) {
  try {
    await api.delete(`/screensavers/${route.params.id}/images/${img.id}`)
    existingImages.value = existingImages.value.filter(i => i.id !== img.id)
    showToast('Gambar dihapus')
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal menghapus gambar', 'error')
  }
}

// ── Load TV devices ──
async function fetchTvDevices() {
  try {
    const { data } = await api.get('/tv-devices', { params: { per_page: 100 } })
    const devices = data.data || []
    tvOptions.value = [
      { name: 'Default (Semua TV)', value: null },
      ...devices.map(d => ({ name: `${d.name} — ${d.location}`, value: d.id }))
    ]
  } catch { /* silent */ }
}

// ── Load for edit ──
onMounted(async () => {
  await fetchTvDevices()

  if (isEdit.value) {
    pageLoading.value = true
    try {
      const { data } = await api.get(`/screensavers/${route.params.id}`)
      form.value = {
        tv_device_id: data.tv_device_id ?? null,
        idle_timeout: data.idle_timeout || 30,
        interval: data.interval || 8,
        is_active: data.is_active ?? true,
      }
      existingImages.value = (data.images || []).map(img => ({
        id: img.id,
        url: img.image_path ? `/storage/${img.image_path}` : '',
        sort_order: img.sort_order,
      }))
    } catch { formError.value = 'Gagal memuat data.' }
    pageLoading.value = false
  }
})

// ── Submit ──
async function handleSubmit() {
  formError.value = ''; formLoading.value = true
  try {
    const fd = new FormData()
    if (form.value.tv_device_id !== null) {
      fd.append('tv_device_id', form.value.tv_device_id)
    }
    fd.append('idle_timeout', form.value.idle_timeout)
    fd.append('interval', form.value.interval)
    fd.append('is_active', form.value.is_active ? '1' : '0')

    // Append new images
    for (const file of newFiles.value) {
      fd.append('images[]', file)
    }

    if (isEdit.value) {
      await api.post(`/screensavers/${route.params.id}`, fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/screensavers', fd, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    router.push({ name: 'AdminScreensaver' })
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Terjadi kesalahan.')
  } finally { formLoading.value = false }
}

// ── Cleanup previews ──
onUnmounted(() => {
  // No object URLs to revoke since we use FileReader
})
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

.upload-zone {
  border-color: var(--border);
  background: var(--bg-input);
  transition: all 0.3s ease;
}
.upload-zone:hover {
  border-color: var(--color-accent);
  box-shadow: 0 0 15px rgba(251, 191, 36, 0.1);
}

.image-card {
  background: var(--bg-input);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.image-card:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.toggle-inactive {
  background: var(--border);
}

.toast-success { background: #065f46; border-color: #10b981; color: #6ee7b7; }
.toast-error { background: #7f1d1d; border-color: #ef4444; color: #fca5a5; }
.toast-enter-active { animation: toastIn 0.3s ease-out; }
.toast-leave-active { animation: toastOut 0.2s ease-in; }
@keyframes toastIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
@keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateX(100px); } }
</style>
