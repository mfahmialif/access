<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ HEADER ═══ -->
    <div class="flex items-center gap-4">
      <router-link to="/administrator/pengumuman"
                   class="flex items-center gap-1 text-sm font-medium cursor-pointer transition-colors hover:text-accent"
                   style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Pengumuman' : 'Tambah Pengumuman' }}</h2>
    </div>

    <!-- ═══ LOADING ═══ -->
    <div v-if="pageLoading" class="flex justify-center py-16">
      <span class="material-symbols-outlined text-accent text-4xl animate-spin">progress_activity</span>
    </div>

    <!-- ═══ FORM CARD ═══ -->
    <div v-else class="form-card rounded-2xl p-6 flex flex-col gap-6">

      <!-- ── Row 1: Judul & Waktu ── -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Judul Pengumuman *</label>
          <input v-model="form.title" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Judul pengumuman" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Waktu & Tanggal</label>
          <input type="datetime-local" v-model="form.datetime" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4" v-if="unitStore.activeUnitId === 'all'">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Unit *</label>
          <VueMultiselect
            v-model="formUnitOption"
            :options="unitStore.units"
            :close-on-select="true"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="id"
            placeholder="Pilih Unit"
          />
        </div>
      </div>
      <!-- ── Row 2: Target + Lokasi ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Target Audiens</label>
          <input v-model="form.audience" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Seluruh Santri" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Lokasi</label>
          <input v-model="form.location" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Pesantren" />
        </div>
      </div>

      <!-- ── Row 3: Prioritas + Status ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Prioritas *</label>
          <VueMultiselect v-model="formPriorityOption" :options="priorityOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Prioritas" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
          <VueMultiselect v-model="formStatusOption" :options="statusOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Status" />
        </div>
      </div>

      <!-- ── Ringkasan ── -->
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">Ringkasan</label>
        <textarea v-model="form.excerpt" class="filter-input rounded-xl py-2.5 px-4 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-accent" rows="2" placeholder="Ringkasan singkat pengumuman..."></textarea>
      </div>

      <!-- ── Image Upload ── -->
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">Gambar</label>
        <div class="upload-zone rounded-xl p-6 flex flex-col items-center gap-3 cursor-pointer transition-all"
             @click="$refs.imageInput.click()"
             @dragover.prevent="imageDragOver = true" @dragleave="imageDragOver = false" @drop.prevent="handleImageDrop"
             :class="{ 'drag-over': imageDragOver }">
          <div v-if="imagePreview" class="relative w-full max-w-md">
            <img :src="imagePreview" class="w-full h-48 object-cover rounded-lg" />
            <button @click.stop="removeImage" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>
          <template v-else>
            <span class="material-symbols-outlined text-[40px]" style="color: var(--text-muted)">cloud_upload</span>
            <p class="text-sm" style="color: var(--text-muted)">Klik atau drag gambar di sini (max 5MB)</p>
          </template>
        </div>
        <input ref="imageInput" type="file" accept="image/*" class="hidden" @change="handleImageSelect" />
      </div>

      <!-- ── Body / Isi (Quill) ── -->
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">Isi Pengumuman</label>
        <QuillEditor ref="quillRef" v-model:content="form.body" content-type="html" theme="snow" :toolbar="quillToolbar" :modules="quillModules" class="quill-dark" @ready="onQuillReady" />
      </div>

      <!-- ── Error ── -->
      <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">{{ formError }}</div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2">
        <router-link to="/administrator/pengumuman" class="px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</router-link>
        <button @click="handleSubmit" :disabled="formLoading" class="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50 shadow-[0_0_15px_rgba(251,191,36,0.3)]" style="color: var(--text-btn)">
          <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          {{ isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import {  ref, computed, onMounted  } from 'vue'
import { useUnitStore } from '../../../stores/unit'
import { useRouter, useRoute } from 'vue-router'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import QuillResizeImage from 'quill-resize-image'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
const router = useRouter() 
const unitStore = useUnitStore()
const route = useRoute()
const isEdit = computed(() => !!route.params.id)
const pageLoading = ref(false)
const formLoading = ref(false)
const formError = ref('')

function getCurrentDateTimeLocal() {
  const now = new Date()
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset())
  return now.toISOString().slice(0, 16)
}

const form = ref({
  title: '', body: '', excerpt: '', priority: 'Normal',
  audience: '', location: '', status: 'Aktif', datetime: getCurrentDateTimeLocal(),
  unit_id: '' })

const imageFile = ref(null)
const imagePreview = ref(null)
const imageDragOver = ref(false)
const removeImageFlag = ref(false)

// ── Options ──
const priorityOptions = [
  { name: 'Urgent', value: 'Urgent' },
  { name: 'Normal', value: 'Normal' },
  { name: 'Info', value: 'Info' },
]
const statusOptions = [
  { name: 'Aktif', value: 'Aktif' },
  { name: 'Expired', value: 'Expired' },
]

const formPriorityOption = computed({
  get: () => priorityOptions.find(o => o.value === form.value.priority) || priorityOptions[1],
  set: (val) => { form.value.priority = val.value }
})
const formStatusOption = computed({
  get: () => statusOptions.find(o => o.value === form.value.status) || statusOptions[0],
  set: (val) => { form.value.status = val.value }
})

// ── Quill Editor ──
const quillRef = ref(null)
const quillToolbar = [
  [{ header: [1, 2, 3, false] }], ['bold', 'italic', 'underline', 'strike'],
  [{ color: [] }, { background: [] }], [{ align: [] }],
  [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote'],
  ['link', 'image'], ['clean'],
]
const quillModules = [{ name: 'resize', module: QuillResizeImage, options: {} }]

function imageHandlerEditor() {
  const input = document.createElement('input')
  input.setAttribute('type', 'file'); input.setAttribute('accept', 'image/*'); input.click()
  input.addEventListener('change', () => {
    const file = input.files?.[0]; if (!file) return
    const reader = new FileReader()
    reader.onload = () => { const q = quillRef.value?.getQuill(); if (q) { const r = q.getSelection(true); q.insertEmbed(r.index, 'image', reader.result); q.setSelection(r.index + 1) } }
    reader.readAsDataURL(file)
  })
}

async function uploadPendingEditorMedia() {
  const q = quillRef.value?.getQuill()
  let body = q ? q.root.innerHTML : (form.value.body || '')
  const imgRegex = /src="(data:image\/[^"]+)"/g
  const base64Matches = []
  let match
  while ((match = imgRegex.exec(body)) !== null) base64Matches.push(match[1])
  for (const dataUrl of base64Matches) {
    try {
      const res = await fetch(dataUrl); const blob = await res.blob()
      const fd = new FormData()
    if (unitStore.activeUnitId === 'all' && form.value.unit_id) {
      fd.append('unit_id', form.value.unit_id)
    }; fd.append('file', blob, 'editor-image.png')
      const { data } = await api.post('/upload-editor', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      body = body.replace(dataUrl, data.url)
    } catch (e) { console.error('Failed to upload editor image:', e) }
  }
  form.value.body = body
}

function onQuillReady() {
  const q = quillRef.value?.getQuill(); if (!q) return
  const toolbar = q.getModule('toolbar')
  toolbar.addHandler('image', imageHandlerEditor)
}

// ── Image Handlers ──
function handleImageSelect(e) { const f = e.target.files[0]; if (f) setImageFile(f) }
function handleImageDrop(e) { imageDragOver.value = false; const f = e.dataTransfer.files[0]; if (f?.type.startsWith('image/')) setImageFile(f) }
function setImageFile(f) { imageFile.value = f; imagePreview.value = URL.createObjectURL(f); removeImageFlag.value = false }
function removeImage() { imageFile.value = null; imagePreview.value = null; removeImageFlag.value = true }

// ── Load data for edit ──
const formUnitOption = computed({
  get: () => unitStore.units.find(u => u.id === form.value.unit_id) || null,
  set: (val) => { form.value.unit_id = val ? val.id : '' }
})

onMounted(async () => {
  if (isEdit.value) {
    pageLoading.value = true
    try {
      const { data } = await api.get(`/announcements/${route.params.id}`)
      form.value = { title: data.title || '',
        body: data.body || '',
        excerpt: data.excerpt || '',
        priority: data.priority || 'Normal',
        audience: data.audience || '',
        location: data.location || '',
        status: data.status || 'Aktif',
        datetime: data.datetime ? data.datetime.substring(0, 16) : '', unit_id: data.unit_id || '' }
      if (data.image_path) imagePreview.value = storageUrl(data.image_path)
    } catch { formError.value = 'Gagal memuat data.' }
    pageLoading.value = false
  }
})

// ── Submit ──
async function handleSubmit() {
  formError.value = ''
  formLoading.value = true
  try {
    await uploadPendingEditorMedia()
    const fd = new FormData()
    if (unitStore.activeUnitId === 'all' && form.value.unit_id) {
      fd.append('unit_id', form.value.unit_id)
    }
    fd.append('title', form.value.title)
    fd.append('priority', form.value.priority)
    fd.append('status', form.value.status)
    if (form.value.body) fd.append('body', form.value.body)
    if (form.value.excerpt) fd.append('excerpt', form.value.excerpt)
    if (form.value.audience) fd.append('audience', form.value.audience)
    if (form.value.location) fd.append('location', form.value.location)
    if (form.value.datetime) fd.append('datetime', form.value.datetime)
    if (imageFile.value) fd.append('image', imageFile.value)
    if (removeImageFlag.value) fd.append('remove_image', '1')

    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/announcements/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/announcements', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    router.push({ name: 'AdminPengumuman' })
  } catch (e) {
    const errors = e.response?.data?.errors
    formError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Terjadi kesalahan.')
  } finally {
    formLoading.value = false
  }
}
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }
.upload-zone { background: var(--bg-input); border: 2px dashed var(--border); transition: all 0.3s ease; }
.upload-zone:hover, .upload-zone.drag-over { border-color: var(--color-accent); background: rgba(251, 191, 36, 0.05); box-shadow: 0 0 20px rgba(251, 191, 36, 0.1); }
.quill-dark :deep(.ql-toolbar) { background: var(--bg-input); border-color: var(--border) !important; border-radius: 12px 12px 0 0; }
.quill-dark :deep(.ql-toolbar .ql-stroke) { stroke: var(--text-muted); }
.quill-dark :deep(.ql-toolbar .ql-fill) { fill: var(--text-muted); }
.quill-dark :deep(.ql-toolbar .ql-picker-label) { color: var(--text-muted); }
.quill-dark :deep(.ql-toolbar button:hover .ql-stroke), .quill-dark :deep(.ql-toolbar .ql-active .ql-stroke) { stroke: var(--color-accent) !important; }
.quill-dark :deep(.ql-toolbar button:hover .ql-fill), .quill-dark :deep(.ql-toolbar .ql-active .ql-fill) { fill: var(--color-accent) !important; }
.quill-dark :deep(.ql-toolbar button:hover), .quill-dark :deep(.ql-toolbar .ql-active) { color: var(--color-accent) !important; }
.quill-dark :deep(.ql-container) { background: var(--bg-input); border-color: var(--border) !important; border-radius: 0 0 12px 12px; color: var(--text-heading); font-family: 'Inter', sans-serif; font-size: 14px; min-height: 200px; }
.quill-dark :deep(.ql-editor) { min-height: 200px; }
.quill-dark :deep(.ql-editor.ql-blank::before) { color: var(--text-muted); font-style: normal; }
.quill-dark :deep(.ql-picker-options) { background: var(--bg-card) !important; border-color: var(--border) !important; }
.quill-dark :deep(.ql-picker-item) { color: var(--text-body) !important; }
.quill-dark :deep(.ql-picker-item:hover) { color: var(--color-accent) !important; }
</style>
