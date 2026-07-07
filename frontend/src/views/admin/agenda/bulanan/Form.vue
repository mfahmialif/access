<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ HEADER ═══ -->
    <div class="flex items-center gap-4">
      <router-link to="/administrator/agenda-bulanan"
                   class="flex items-center gap-1 text-sm font-medium cursor-pointer transition-colors hover:text-accent"
                   style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Agenda Bulanan' : 'Tambah Agenda Bulanan' }}</h2>
    </div>

    <!-- ═══ FORM CARD ═══ -->
    <div class="form-card rounded-2xl p-6 flex flex-col gap-6">

      <!-- ── Row 1: Judul + Tanggal + Waktu ── -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Judul Kegiatan *</label>
          <input v-model="form.title" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama kegiatan" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Tanggal *</label>
          <input v-model="form.date" type="date" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
        </div>
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-medium" style="color: var(--text-body)">Waktu *</label>
          <input v-model="form.time" type="time" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" />
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
            <template #option="{ option }"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">{{ option.value }}</span><span>{{ option.name }}</span></div></template>
            <template #singleLabel="{ option }"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">{{ option.value }}</span><span>{{ option.name }}</span></div></template>
          </VueMultiselect>
        </div>
      </div>

      <!-- ── Image Upload ── -->
      <div v-if="form.category === 'Gambar' || form.category === 'Artikel' || form.category === 'Video'" class="flex flex-col gap-1.5">
        <label class="text-sm font-medium" style="color: var(--text-body)">{{ form.category === 'Gambar' ? 'Upload Gambar *' : 'Upload Banner' }}</label>
        <div class="upload-zone rounded-xl p-6 flex flex-col items-center gap-3 cursor-pointer transition-all" @click="$refs.imageInput.click()" @dragover.prevent="imageDragOver = true" @dragleave="imageDragOver = false" @drop.prevent="handleImageDrop" :class="{ 'drag-over': imageDragOver }">
          <div v-if="imagePreview" class="relative w-full max-w-md">
            <img :src="imagePreview" class="w-full h-48 object-cover rounded-lg" />
            <button @click.stop="removeImage" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 cursor-pointer"><span class="material-symbols-outlined text-[16px]">close</span></button>
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
        <div class="upload-zone rounded-xl p-6 flex flex-col items-center gap-3 cursor-pointer transition-all" @click="$refs.videoInput.click()" @dragover.prevent="videoDragOver = true" @dragleave="videoDragOver = false" @drop.prevent="handleVideoDrop" :class="{ 'drag-over': videoDragOver }">
          <div v-if="videoPreview" class="relative w-full max-w-md">
            <video :src="videoPreview" class="w-full h-48 rounded-lg object-cover" controls></video>
            <button @click.stop="removeVideo" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600 cursor-pointer"><span class="material-symbols-outlined text-[16px]">close</span></button>
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
        <QuillEditor ref="quillRef" v-model:content="form.body" content-type="html" theme="snow" :toolbar="quillToolbar" :modules="quillModules" class="quill-dark" @ready="onQuillReady" />
      </div>

      <!-- ── Error ── -->
      <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">{{ formError }}</div>

      <!-- ── Actions ── -->
      <div class="flex justify-end gap-3 pt-2">
        <router-link to="/administrator/agenda-bulanan" class="px-6 py-2.5 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</router-link>
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
import { useMonthlyStore } from '../../../../stores/monthly'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import QuillResizeImage from 'quill-resize-image'
import api from '../../../../axios'
import { storageUrl } from '../../../../utils/asset'
const quillRef = ref(null)
const quillToolbar = [
  [{ header: [1, 2, 3, false] }], ['bold', 'italic', 'underline', 'strike'],
  [{ color: [] }, { background: [] }], [{ align: [] }],
  [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'code-block'],
  ['link', 'image', 'video'], ['clean'],
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

const pendingVideoFiles = new Map()
let videoBlotRegistered = false

function videoHandlerEditor() {
  const input = document.createElement('input')
  input.setAttribute('type', 'file'); input.setAttribute('accept', 'video/mp4,video/webm,video/ogg'); input.click()
  input.addEventListener('change', () => {
    const file = input.files?.[0]; if (!file) return
    if (file.size > 50 * 1024 * 1024) { alert('File video terlalu besar. Maksimal 50MB.'); return }
    const blobUrl = URL.createObjectURL(file); pendingVideoFiles.set(blobUrl, file)
    const q = quillRef.value?.getQuill(); if (q) { const r = q.getSelection(true); q.insertEmbed(r.index, 'uploadedVideo', blobUrl); q.setSelection(r.index + 1) }
  })
}

async function uploadPendingEditorMedia() {
  const q = quillRef.value?.getQuill(); let body = q ? q.root.innerHTML : (form.value.body || '')
  const imgRegex = /src="(data:image\/[^"]+)"/g; const base64Matches = []; let match
  while ((match = imgRegex.exec(body)) !== null) base64Matches.push(match[1])
  for (const dataUrl of base64Matches) {
    try { const res = await fetch(dataUrl); const blob = await res.blob(); const fd = new FormData(); fd.append('file', blob, 'editor-image.png')
      const { data } = await api.post('/upload-editor', fd, { headers: { 'Content-Type': 'multipart/form-data' } }); body = body.replace(dataUrl, data.url)
    } catch (e) { console.error('Failed to upload editor image:', e) }
  }
  const vidRegex = /src="(blob:[^"]+)"/g; const blobMatches = []
  while ((match = vidRegex.exec(body)) !== null) blobMatches.push(match[1])
  for (const blobUrl of blobMatches) {
    const file = pendingVideoFiles.get(blobUrl); if (!file) continue
    try { const fd = new FormData(); fd.append('file', file)
      const { data } = await api.post('/upload-editor', fd, { headers: { 'Content-Type': 'multipart/form-data' } }); body = body.replace(blobUrl, data.url)
      URL.revokeObjectURL(blobUrl); pendingVideoFiles.delete(blobUrl)
    } catch (e) { console.error('Failed to upload editor video:', e) }
  }
  form.value.body = body
}

let initialMediaUrls = new Set()
function extractEditorMediaUrls(html) { if (!html) return new Set(); const urls = new Set(); const regex = /src="(\/storage\/agendas\/editor\/[^"]+)"/g; let m; while ((m = regex.exec(html)) !== null) urls.add(m[1]); return urls }

function onQuillReady() {
  const q = quillRef.value?.getQuill(); if (!q) return
  if (!videoBlotRegistered) {
    const Q = q.constructor; const BE = Q.import('blots/block/embed')
    class UploadedVideoBlot extends BE {
      static create(url) { const n = super.create(); const v = document.createElement('video'); v.setAttribute('src', url); v.setAttribute('controls', 'true'); v.style.maxWidth = '100%'; v.style.borderRadius = '8px'; n.appendChild(v); return n }
      static value(n) { const v = n.querySelector('video'); return v ? v.getAttribute('src') : '' }
    }
    UploadedVideoBlot.blotName = 'uploadedVideo'; UploadedVideoBlot.tagName = 'div'; UploadedVideoBlot.className = 'ql-uploaded-video'
    Q.register(UploadedVideoBlot, true); videoBlotRegistered = true
  }
  const toolbar = q.getModule('toolbar'); toolbar.addHandler('image', imageHandlerEditor); toolbar.addHandler('video', videoHandlerEditor)
  initialMediaUrls = extractEditorMediaUrls(form.value.body)
}

const router = useRouter(); const route = useRoute(); const monthlyStore = useMonthlyStore()
const isEdit = computed(() => !!route.params.id)
const formLoading = ref(false); const formError = ref('')

const form = ref({ title: '', date: '', time: '', location: '', teacher: '', icon: 'event', category: 'Artikel', body: '', status: 'Aktif' })
const imageFile = ref(null); const imagePreview = ref(null); const imageDragOver = ref(false); const removeImageFlag = ref(false)
const videoFile = ref(null); const videoPreview = ref(null); const videoDragOver = ref(false); const removeVideoFlag = ref(false)

const categoryOptions = [{ name: 'Artikel', value: 'Artikel' }, { name: 'Video', value: 'Video' }, { name: 'Gambar', value: 'Gambar' }]
const statusOptions = [{ name: 'Aktif', value: 'Aktif' }, { name: 'Tidak Aktif', value: 'Tidak Aktif' }]
const iconOptions = [
  { name: 'Event', value: 'event' }, { name: 'Masjid', value: 'mosque' }, { name: 'Buku', value: 'menu_book' },
  { name: 'Sekolah', value: 'school' }, { name: 'Olahraga', value: 'sports_soccer' }, { name: 'TV / Siaran', value: 'live_tv' },
  { name: 'Kamera', value: 'videocam' }, { name: 'Kelompok', value: 'groups' }, { name: 'Mic / Pidato', value: 'record_voice_over' },
  { name: 'Megaphone', value: 'campaign' }, { name: 'Komputer', value: 'computer' }, { name: 'Flag', value: 'flag' },
  { name: 'Palette', value: 'palette' }, { name: 'Kerja', value: 'work' }, { name: 'AutoStories', value: 'auto_stories' },
]

const formCategoryOption = computed({ get: () => categoryOptions.find(o => o.value === form.value.category) || categoryOptions[0], set: (val) => { form.value.category = val.value } })
const formStatusOption = computed({ get: () => statusOptions.find(o => o.value === form.value.status) || statusOptions[0], set: (val) => { form.value.status = val.value } })
const formIconOption = computed({ get: () => iconOptions.find(o => o.value === form.value.icon) || iconOptions[0], set: (val) => { form.value.icon = val.value } })

onMounted(async () => {
  if (isEdit.value) {
    try {
      const data = await monthlyStore.fetchMonthly(route.params.id)
      form.value = { title: data.title, date: data.date?.substring(0, 10) || '', time: data.time?.substring(0, 5) || '', location: data.location || '', teacher: data.teacher || '', icon: data.icon || 'event', category: data.category, body: data.body || '', status: data.status }
      if (data.image_path) imagePreview.value = storageUrl(data.image_path)
      if (data.video_path) videoPreview.value = storageUrl(data.video_path)
    } catch { formError.value = 'Gagal memuat data.' }
  }
})

function handleImageSelect(e) { const f = e.target.files[0]; if (f) setImageFile(f) }
function handleImageDrop(e) { imageDragOver.value = false; const f = e.dataTransfer.files[0]; if (f?.type.startsWith('image/')) setImageFile(f) }
function setImageFile(f) { imageFile.value = f; imagePreview.value = URL.createObjectURL(f); removeImageFlag.value = false }
function removeImage() { imageFile.value = null; imagePreview.value = null; removeImageFlag.value = true }
function handleVideoSelect(e) { const f = e.target.files[0]; if (f) setVideoFile(f) }
function handleVideoDrop(e) { videoDragOver.value = false; const f = e.dataTransfer.files[0]; if (f?.type.startsWith('video/')) setVideoFile(f) }
function setVideoFile(f) { videoFile.value = f; videoPreview.value = URL.createObjectURL(f); removeVideoFlag.value = false }
function removeVideo() { videoFile.value = null; videoPreview.value = null; removeVideoFlag.value = true }

async function handleSubmit() {
  formError.value = ''; formLoading.value = true
  try {
    await uploadPendingEditorMedia()
    const fd = new FormData()
    fd.append('title', form.value.title); fd.append('date', form.value.date); fd.append('time', form.value.time)
    fd.append('location', form.value.location || ''); fd.append('teacher', form.value.teacher || '')
    fd.append('icon', form.value.icon || 'event'); fd.append('category', form.value.category)
    fd.append('body', form.value.body || ''); fd.append('status', form.value.status)
    if (imageFile.value) fd.append('image', imageFile.value)
    if (videoFile.value) fd.append('video', videoFile.value)
    if (removeImageFlag.value) fd.append('remove_image', '1')
    if (removeVideoFlag.value) fd.append('remove_video', '1')
    if (isEdit.value) await monthlyStore.updateMonthly(route.params.id, fd)
    else await monthlyStore.createMonthly(fd)
    const finalUrls = extractEditorMediaUrls(form.value.body)
    for (const url of initialMediaUrls) { if (!finalUrls.has(url)) api.post('/delete-editor-file', { url }).catch(() => {}) }
    router.push({ name: 'AdminAgendaBulanan' })
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
input[type="time"].filter-input, input[type="date"].filter-input { color-scheme: var(--color-scheme, light); }
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
