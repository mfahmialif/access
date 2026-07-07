import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useTvDeviceStore = defineStore('tvDevice', () => {
  const devices = ref([])
  const stats = ref({ total: 0, online: 0, offline: 0, setup: 0, warning: 0 })
  const globalBanner = ref(null)
  const loading = ref(false)
  const currentPage = ref(1)
  const totalPages = ref(1)

  async function fetchDevices(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/tv-devices', { params })
      devices.value = data.data || []
      currentPage.value = data.current_page || 1
      totalPages.value = data.last_page || 1
      globalBanner.value = data.global_banner || null
    } catch (e) { console.error(e); devices.value = [] } finally { loading.value = false }
  }

  async function fetchStats() {
    try {
      const { data } = await api.get('/tv-devices/stats')
      stats.value = data
    } catch (e) { console.error(e) }
  }

  async function createDevice(payload) {
    const { data } = await api.post('/tv-devices', payload)
    return data
  }

  async function updateDevice(id, payload) {
    const { data } = await api.put(`/tv-devices/${id}`, payload)
    return data
  }

  async function deleteDevice(id) {
    await api.delete(`/tv-devices/${id}`)
  }

  async function regenerateToken(id) {
    const { data } = await api.post(`/tv-devices/${id}/regenerate-token`)
    return data
  }

  return { devices, stats, globalBanner, loading, currentPage, totalPages, fetchDevices, fetchStats, createDevice, updateDevice, deleteDevice, regenerateToken }
})
