import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useGalleryStore = defineStore('gallery', () => {
  const galleries = ref([])
  const pagination = ref({})
  const loading = ref(false)
  const stats = ref({ total: 0, gambar: 0, video: 0 })

  async function fetchGalleries(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/galleries', { params })
      galleries.value = data.data
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        total: data.total,
        perPage: data.per_page,
        from: data.from,
        to: data.to,
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchStats() {
    try {
      const { data } = await api.get('/galleries/stats')
      stats.value = data
    } catch {}
  }

  async function fetchGallery(id) {
    const { data } = await api.get(`/galleries/${id}`)
    return data
  }

  async function createGallery(formData) {
    const { data } = await api.post('/galleries', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateGallery(id, formData) {
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/galleries/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function deleteGallery(id) {
    await api.delete(`/galleries/${id}`)
  }

  return { galleries, pagination, loading, stats, fetchGalleries, fetchStats, fetchGallery, createGallery, updateGallery, deleteGallery }
})
