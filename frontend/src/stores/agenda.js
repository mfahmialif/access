import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useAgendaStore = defineStore('agenda', () => {
  const agendas = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchAgendas(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/agendas', { params })
      agendas.value = data.data
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

  async function fetchAgenda(id) {
    const { data } = await api.get(`/agendas/${id}`)
    return data
  }

  async function createAgenda(formData) {
    const { data } = await api.post('/agendas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateAgenda(id, formData) {
    // Laravel tidak support PUT dengan multipart, pakai POST + _method
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/agendas/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function deleteAgenda(id) {
    await api.delete(`/agendas/${id}`)
  }

  return { agendas, pagination, loading, fetchAgendas, fetchAgenda, createAgenda, updateAgenda, deleteAgenda }
})
