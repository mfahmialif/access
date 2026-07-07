import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useWeeklyStore = defineStore('weekly', () => {
  const weeklies = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchWeeklies(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/weeklies', { params })
      weeklies.value = data.data
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

  async function fetchWeekly(id) {
    const { data } = await api.get(`/weeklies/${id}`)
    return data
  }

  async function createWeekly(formData) {
    const { data } = await api.post('/weeklies', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateWeekly(id, formData) {
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/weeklies/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function deleteWeekly(id) {
    await api.delete(`/weeklies/${id}`)
  }

  return { weeklies, pagination, loading, fetchWeeklies, fetchWeekly, createWeekly, updateWeekly, deleteWeekly }
})
