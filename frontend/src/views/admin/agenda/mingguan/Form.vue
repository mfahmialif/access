<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ HEADER ═══ -->
    <div class="flex items-center gap-4">
      <router-link to="/administrator/agenda-mingguan"
                   class="flex items-center gap-1 text-sm font-medium cursor-pointer transition-colors hover:text-accent"
                   style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Agenda Mingguan' : 'Tambah Agenda Mingguan' }}</h2>
    </div>

    <!-- ═══ FORM CARD ═══ -->
    <div class="form-card rounded-2xl p-6 flex flex-col gap-6">

      <!-- ── Row 1: Judul + Hari + Waktu ── -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Judul Kegiatan *</label>
          <input v-model="form.title" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama kegiatan" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Hari *</label>
          <VueMultiselect
            v-model="formDayOption"
            :options="dayOptions"
            :close-on-select="true"
            :searchable="false"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="Pilih Hari"
          />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Waktu *</label>
          <input v-model="form.time" type="time" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
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
      <!-- ── Row 2: Lokasi + Pengajar ── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Lokasi</label>
          <input v-model="form.location" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Lokasi kegiatan" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Pengajar / Penanggung Jawab</label>
          <input v-model="form.teacher" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama pengajar" />
        </div>
      </div>

      <!-- ── Row 3: Kategori + Status + Icon ── -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Kategori *</label>
          <VueMultiselect v-model="formCategoryOption" :options="categoryOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Kategori" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
          <VueMultiselect v-model="formStatusOption" :options="statusOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Status" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Icon</label>
          <VueMultiselect v-model="formIconOption" :options="iconOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih Icon">
            <template #option="{ option }">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">{{ option.value }}</span>
                <span>{{ option.name }}</span>
              </div>
            </template>
            <template #singleLabel="{ option }">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">{{ option.value }}</span>
                <span>{{ option.name }}</span>
              </div>
            </template>
          </VueMultiselect>
        </div>
      </div>

      <!-- ── Image Upload ── -->
      <div v-if="form.category === 'Gambar' || form.category === 'Artikel' || form.category === 'Video'" class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">
          {{ form.category === 'Gambar' ? 'Upload Gambar *' : 'Upload Banner' }}
        </label>
        <div class="upload-zone rounded-xl p-6 flex flex-col items-center gap-3 cursor-pointer transition-all"
             @click="$refs.imageInput.click()"
             @dragover.prevent="imageDragOver = true"
             @dragleave="imageDragOver = false"
             @drop.prevent="handleImageDrop"
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

      <!-- ── Video Upload ── -->
      <div v-if="form.category === 'Video'" class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">Upload Video *</label>
        <div class="upload-zone rounded-xl p-6 flex flex-col items-center gap-3 cursor-pointer transition-all"
             @click="$refs.videoInput.click()"
             @dragover.prevent="videoDragOver = true"
             @dragleave="videoDragOver = false"
             @drop.prevent="handleVideoDrop"
             :class="{ 'drag-over': videoDragOver }">
          <div v-if="videoPreview" class="relative w-full max-w-md">
            <video :src="videoPreview" class="w-full h-48 rounded-lg object-cover" controls></video>
            <button @click.stop="removeVideo" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 cursor-pointer">
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
          </div>
          <template v-else>
            <span class="material-symbols-outlined text-[40px]" style="color: var(--text-muted)">videocam</span>
            <p class="text-sm" style="color: var(--text-muted)">Klik atau drag video di sini (max 50MB)</p>
          </template>
        </div>
        <input ref="videoInput" type="file" accept="video/mp4,video/webm,video/ogg" class="hidden" @change="handleVideoSelect" />
      </div>

      <!-- ── Body / Deskripsi (Quill) ── -->
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">Deskripsi / Body Konten</label>
        <QuillEditor
          ref="quillRef"
          v-model:content="form.body"
          content-type="html"
          theme="snow"
          :toolbar="quillToolbar"
          :modules="quillModules"
          class="quill-dark"
          @ready="onQuillReady"
        />
      </div>

      <!-- ── Error ── -->
      <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">{{ formError }}</div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2">
        <router-link to="/administrator/agenda-mingguan"
                     class="px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer"
                     style="color: var(--text-body); border: 1px solid var(--border)">
          Batal
        </router-link>
        <button @click="handleSubmit" :disabled="formLoading"
                class="flex items-center gap-2 px-6 py-2.5 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50 shadow-[0_0_15px_rgba(251,191,36,0.3)]"
                style="color: var(--text-btn)">
          <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          {{ isEdit ? 'Update' : 'Simpan' }}
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import {  ref, computed, onMounted  } from 'vue'
import { useUnitStore } from '../../../../stores/unit'
import { useRouter, useRoute } from 'vue-router'
import { useWeeklyStore } from '../../../../stores/weekly'

// Quill Editor
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import QuillResizeImage from 'quill-resize-image'
import api from '../../../../axios'
import { storageUrl } from '../../../../utils/asset'
const quillRef = ref(null)

const quillToolbar = [
  [{ header: [1, 2, 3, false] }],
  ['bold', 'italic', 'underline', 'strike'],
  [{ color: [] }, { background: [] }],
  [{ align: [] }],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['blockquote', 'code-block'],
  ['link', 'image', 'video'],
  ['clean'],
]

const quillModules = [
  {
    name: 'resize',
    module: QuillResizeImage,
    options: {},
  },
]

// Custom image handler — insert as base64 temporarily (upload on submit)
function imageHandlerEditor() {
  const input = document.createElement('input')
  input.setAttribute('type', 'file')
  input.setAttribute('accept', 'image/*')
  input.click()

  input.addEventListener('change', () => {
    const file = input.files?.[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = () => {
      const quill = quillRef.value?.getQuill()
      if (quill) {
        const range = quill.getSelection(true)
        quill.insertEmbed(range.index, 'image', reader.result)
        quill.setSelection(range.index + 1)
      }
    }
    reader.readAsDataURL(file)
  })
}

// Custom video handler
const pendingVideoFiles = new Map()
let videoBlotRegistered = false

function videoHandlerEditor() {
  const input = document.createElement('input')
  input.setAttribute('type', 'file')
  input.setAttribute('accept', 'video/mp4,video/webm,video/ogg')
  input.click()

  input.addEventListener('change', () => {
    const file = input.files?.[0]
    if (!file) return

    if (file.size > 50 * 1024 * 1024) {
      alert('File video terlalu besar. Maksimal 50MB.')
      return
    }

    const blobUrl = URL.createObjectURL(file)
    pendingVideoFiles.set(blobUrl, file)

    const quill = quillRef.value?.getQuill()
    if (quill) {
      const range = quill.getSelection(true)
      quill.insertEmbed(range.index, 'uploadedVideo', blobUrl)
      quill.setSelection(range.index + 1)
    }
  })
}

async function uploadPendingEditorMedia() {
  const quill = quillRef.value?.getQuill()
  let body = quill ? quill.root.innerHTML : (form.value.body || '')

  // Upload base64 images
  const imgRegex = /src="(data:image\/[^"]+)"/g
  const base64Matches = []
  let match
  while ((match = imgRegex.exec(body)) !== null) {
    base64Matches.push(match[1])
  }
  for (const dataUrl of base64Matches) {
    try {
      const res = await fetch(dataUrl)
      const blob = await res.blob()
      const fd = new FormData()
    if (unitStore.activeUnitId === 'all' && form.value.unit_id) {
      fd.append('unit_id', form.value.unit_id)
    }
      fd.append('file', blob, 'editor-image.png')
      const { data } = await api.post('/upload-editor', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      body = body.replace(dataUrl, data.url)
    } catch (e) { console.error('Failed to upload editor image:', e) }
  }

  // Upload blob videos
  const vidRegex = /src="(blob:[^"]+)"/g
  const blobMatches = []
  while ((match = vidRegex.exec(body)) !== null) {
    blobMatches.push(match[1])
  }
  for (const blobUrl of blobMatches) {
    const file = pendingVideoFiles.get(blobUrl)
    if (!file) continue
    try {
      const fd = new FormData()
      fd.append('file', file)
      const { data } = await api.post('/upload-editor', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      body = body.replace(blobUrl, data.url)
      URL.revokeObjectURL(blobUrl)
      pendingVideoFiles.delete(blobUrl)
    } catch (e) { console.error('Failed to upload editor video:', e) }
  }

  form.value.body = body
}

let initialMediaUrls = new Set()

function extractEditorMediaUrls(html) {
  if (!html) return new Set()
  const urls = new Set()
  const regex = /src="(\/storage\/agendas\/editor\/[^"]+)"/g
  let match
  while ((match = regex.exec(html)) !== null) { urls.add(match[1]) }
  return urls
}

function onQuillReady() {
  const quill = quillRef.value?.getQuill()
  if (!quill) return

  if (!videoBlotRegistered) {
    const QuillLib = quill.constructor
    const BlockEmbed = QuillLib.import('blots/block/embed')
    class UploadedVideoBlot extends BlockEmbed {
      static create(url) {
        const node = super.create()
        const video = document.createElement('video')
        video.setAttribute('src', url)
        video.setAttribute('controls', 'true')
        video.style.maxWidth = '100%'
        video.style.borderRadius = '8px'
        node.appendChild(video)
        return node
      }
      static value(node) {
        const video = node.querySelector('video')
        return video ? video.getAttribute('src') : ''
      }
    }
    UploadedVideoBlot.blotName = 'uploadedVideo'
    UploadedVideoBlot.tagName = 'div'
    UploadedVideoBlot.className = 'ql-uploaded-video'
    QuillLib.register(UploadedVideoBlot, true)
    videoBlotRegistered = true
  }

  const toolbar = quill.getModule('toolbar')
  toolbar.addHandler('image', imageHandlerEditor)
  toolbar.addHandler('video', videoHandlerEditor)
  initialMediaUrls = extractEditorMediaUrls(form.value.body)
}

const router = useRouter()
const route = useRoute()
const weeklyStore = useWeeklyStore() 
const unitStore = useUnitStore()

const isEdit = computed(() => !!route.params.id)
const formLoading = ref(false)
const formError = ref('')

// ── Form data ──
const form = ref({
  title: '',
  day: 'Senin',
  time: '',
  location: '',
  teacher: '',
  icon: 'event',
  category: 'Artikel',
  body: '',
  status: 'Aktif',
  unit_id: '' })

const imageFile = ref(null)
const imagePreview = ref(null)
const imageDragOver = ref(false)
const removeImageFlag = ref(false)

const videoFile = ref(null)
const videoPreview = ref(null)
const videoDragOver = ref(false)
const removeVideoFlag = ref(false)

// ── Options ──
const dayOptions = [
  { name: 'Senin', value: 'Senin' }, { name: 'Selasa', value: 'Selasa' }, { name: 'Rabu', value: 'Rabu' },
  { name: 'Kamis', value: 'Kamis' }, { name: 'Jumat', value: 'Jumat' }, { name: 'Sabtu', value: 'Sabtu' },
  { name: 'Minggu', value: 'Minggu' }
]
const categoryOptions = [{ name: 'Artikel', value: 'Artikel' }, { name: 'Video', value: 'Video' }, { name: 'Gambar', value: 'Gambar' }]
const statusOptions = [{ name: 'Aktif', value: 'Aktif' }, { name: 'Tidak Aktif', value: 'Tidak Aktif' }]
const iconOptions = [
  { name: 'Event', value: 'event' }, { name: 'Masjid', value: 'mosque' }, { name: 'Buku', value: 'menu_book' },
  { name: 'Sekolah', value: 'school' }, { name: 'Olahraga', value: 'sports_soccer' }, { name: 'TV / Siaran', value: 'live_tv' },
  { name: 'Kamera', value: 'videocam' }, { name: 'Kelompok', value: 'groups' }, { name: 'Mic / Pidato', value: 'record_voice_over' },
  { name: 'Megaphone', value: 'campaign' }, { name: 'Bersih-bersih', value: 'cleaning_services' }, { name: 'AutoStories', value: 'auto_stories' },
  { name: 'Waktu', value: 'schedule' }, { name: 'Star', value: 'star' }, { name: 'Checklist', value: 'task_alt' },
]

const formDayOption = computed({
  get: () => dayOptions.find(o => o.value === form.value.day) || dayOptions[0],
  set: (val) => { form.value.day = val.value },
})
const formCategoryOption = computed({
  get: () => categoryOptions.find(o => o.value === form.value.category) || categoryOptions[0],
  set: (val) => { form.value.category = val.value },
})
const formStatusOption = computed({
  get: () => statusOptions.find(o => o.value === form.value.status) || statusOptions[0],
  set: (val) => { form.value.status = val.value },
})
const formIconOption = computed({
  get: () => iconOptions.find(o => o.value === form.value.icon) || iconOptions.find(o => o.value === 'event'),
  set: (val) => { form.value.icon = val.value },
})

// ── Load existing data for edit ──
const formUnitOption = computed({
  get: () => unitStore.units.find(u => u.id === form.value.unit_id) || null,
  set: (val) => { form.value.unit_id = val ? val.id : '' }
})

onMounted(async () => {
  if (isEdit.value) {
    try {
      const data = await weeklyStore.fetchWeekly(route.params.id)
      form.value = { title: data.title,
        day: data.day,
        time: data.time?.substring(0, 5) || '',
        location: data.location || '',
        teacher: data.teacher || '',
        icon: data.icon || 'event',
        category: data.category,
        body: data.body || '',
        status: data.status, unit_id: data.unit_id || '' }
      if (data.image_path) imagePreview.value = storageUrl(data.image_path)
      if (data.video_path) videoPreview.value = storageUrl(data.video_path)
    } catch {
      formError.value = 'Gagal memuat data agenda.'
    }
  }
})

// ── Image handling ──
function handleImageSelect(e) { const file = e.target.files[0]; if (file) setImageFile(file) }
function handleImageDrop(e) { imageDragOver.value = false; const file = e.dataTransfer.files[0]; if (file && file.type.startsWith('image/')) setImageFile(file) }
function setImageFile(file) { imageFile.value = file; imagePreview.value = URL.createObjectURL(file); removeImageFlag.value = false }
function removeImage() { imageFile.value = null; imagePreview.value = null; removeImageFlag.value = true }

// ── Video handling ──
function handleVideoSelect(e) { const file = e.target.files[0]; if (file) setVideoFile(file) }
function handleVideoDrop(e) { videoDragOver.value = false; const file = e.dataTransfer.files[0]; if (file && file.type.startsWith('video/')) setVideoFile(file) }
function setVideoFile(file) { videoFile.value = file; videoPreview.value = URL.createObjectURL(file); removeVideoFlag.value = false }
function removeVideo() { videoFile.value = null; videoPreview.value = null; removeVideoFlag.value = true }

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
    fd.append('day', form.value.day)
    fd.append('time', form.value.time)
    fd.append('location', form.value.location || '')
    fd.append('teacher', form.value.teacher || '')
    fd.append('icon', form.value.icon || 'event')
    fd.append('category', form.value.category)
    fd.append('body', form.value.body || '')
    fd.append('status', form.value.status)

    if (imageFile.value) fd.append('image', imageFile.value)
    if (videoFile.value) fd.append('video', videoFile.value)
    if (removeImageFlag.value) fd.append('remove_image', '1')
    if (removeVideoFlag.value) fd.append('remove_video', '1')

    if (isEdit.value) {
      await weeklyStore.updateWeekly(route.params.id, fd)
    } else {
      await weeklyStore.createWeekly(fd)
    }

    // Clean up removed editor media
    const finalUrls = extractEditorMediaUrls(form.value.body)
    for (const url of initialMediaUrls) {
      if (!finalUrls.has(url)) {
        api.post('/delete-editor-file', { url }).catch(() => {})
      }
    }

    router.push({ name: 'AdminAgendaMingguan' })
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
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }
input[type="time"].filter-input { color-scheme: var(--color-scheme, light); }
textarea.filter-input { min-height: 120px; }
.upload-zone { background: var(--bg-input); border: 2px dashed var(--border); transition: all 0.3s ease; }
.upload-zone:hover, .upload-zone.drag-over { border-color: var(--color-accent); background: rgba(251, 191, 36, 0.05); box-shadow: 0 0 20px rgba(251, 191, 36, 0.1); }

/* Quill Dark Theme */
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
