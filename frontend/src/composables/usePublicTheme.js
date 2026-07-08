import { ref, watch, onMounted } from 'vue'

const STORAGE_KEY = 'public-theme'

// Shared reactive state (singleton across all components)
const isDark = ref(true)

// Initialize from localStorage
function initTheme() {
  const saved = localStorage.getItem(STORAGE_KEY)
  isDark.value = saved !== 'light'
  applyTheme()
}

function applyTheme() {
  document.documentElement.setAttribute('data-public-theme', isDark.value ? 'dark' : 'light')
}

function toggleTheme() {
  isDark.value = !isDark.value
  localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light')
  applyTheme()
}

// Auto-init on first import
if (typeof window !== 'undefined') {
  const saved = localStorage.getItem(STORAGE_KEY)
  isDark.value = saved !== 'light'
  // Apply immediately (before any component mounts)
  document.documentElement.setAttribute('data-public-theme', isDark.value ? 'dark' : 'light')
}

export function usePublicTheme() {
  return { isDark, toggleTheme, initTheme }
}
