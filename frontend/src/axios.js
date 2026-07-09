import axios from 'axios'
import router from './router'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor — attach token and unit id
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  
  // Check if we are currently on an admin route
  const isAdminPath = window.location.pathname.startsWith('/admin')
  
  if (isAdminPath) {
    // Attach Unit ID for admin/protected routes
    const unitId = localStorage.getItem('active_unit_id')
    if (unitId) {
      config.headers['X-Unit-Id'] = unitId
    }
  } else {
    // For public pages, TV unit ID is inside the tv_device JSON object
    const tvDeviceStr = localStorage.getItem('tv_device')
    if (tvDeviceStr && !config.url.startsWith('/login')) {
        try {
            const tvDevice = JSON.parse(tvDeviceStr)
            if (tvDevice && tvDevice.unit_id && !config.headers['X-Unit-Id']) {
                config.headers['X-Unit-Id'] = tvDevice.unit_id
            }
        } catch (e) {}
    }
  }

  return config
})

// Response interceptor — handle 401
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      router.push({ name: 'Login' })
    }
    return Promise.reject(error)
  }
)

export default api
