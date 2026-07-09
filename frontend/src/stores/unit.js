import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../axios'
import { useAuthStore } from './auth'

export const useUnitStore = defineStore('unit', () => {
  const units = ref([])
  const activeUnitId = ref(localStorage.getItem('active_unit_id') || 'all')
  const loading = ref(false)
  const authStore = useAuthStore()

  const activeUnit = computed(() => {
    if (activeUnitId.value === 'all') return { id: 'all', name: 'Semua Unit' }
    return units.value.find(u => u.id == activeUnitId.value) || null
  })

  async function fetchMyUnits() {
    loading.value = true
    try {
      const { data } = await api.get('/my-units')
      units.value = data
      
      // If activeUnitId is not found in the list, or if it is 'all' but user is not superadmin
      if ((activeUnitId.value !== 'all' && !units.value.find(u => u.id == activeUnitId.value)) || 
          (activeUnitId.value === 'all' && !authStore.isSuperadmin)) {
        if (units.value.length > 0) {
          switchUnit(units.value[0].id)
        } else if (authStore.isSuperadmin) {
          switchUnit('all')
        }
      }
    } finally {
      loading.value = false
    }
  }

  function switchUnit(id) {
    activeUnitId.value = id.toString()
    localStorage.setItem('active_unit_id', activeUnitId.value)
    
    // Reload page to refresh all content with new unit
    window.location.reload()
  }

  return { units, activeUnitId, activeUnit, loading, fetchMyUnits, switchUnit }
})
