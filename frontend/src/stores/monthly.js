import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useMonthlyStore = defineStore('monthly', () => {
  const monthlies = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchMonthlies(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/monthlies', { params })
      monthlies.value = data.data
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

  async function fetchMonthly(id) {
    const { data } = await api.get(`/monthlies/${id}`)
    return data
  }

  async function createMonthly(formData) {
    const { data } = await api.post('/monthlies', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateMonthly(id, formData) {
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/monthlies/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function deleteMonthly(id) {
    await api.delete(`/monthlies/${id}`)
  }

  return { monthlies, pagination, loading, fetchMonthlies, fetchMonthly, createMonthly, updateMonthly, deleteMonthly }
})
