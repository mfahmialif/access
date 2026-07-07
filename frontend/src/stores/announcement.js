import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useAnnouncementStore = defineStore('announcement', () => {
  const announcements = ref([])
  const pagination = ref({})
  const stats = ref({ total: 0, aktif: 0, urgent: 0 })
  const loading = ref(false)

  async function fetchAnnouncements(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/announcements', { params })
      announcements.value = data.data
      pagination.value = { currentPage: data.current_page, lastPage: data.last_page, total: data.total, perPage: data.per_page, from: data.from, to: data.to }
    } finally { loading.value = false }
  }

  async function fetchStats() {
    const { data } = await api.get('/announcements/stats')
    stats.value = data
  }

  async function fetchAnnouncement(id) {
    const { data } = await api.get(`/announcements/${id}`)
    return data
  }

  async function createAnnouncement(formData) {
    const { data } = await api.post('/announcements', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    return data
  }

  async function updateAnnouncement(id, formData) {
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/announcements/${id}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    return data
  }

  async function deleteAnnouncement(id) {
    await api.delete(`/announcements/${id}`)
  }

  return { announcements, pagination, stats, loading, fetchAnnouncements, fetchStats, fetchAnnouncement, createAnnouncement, updateAnnouncement, deleteAnnouncement }
})
