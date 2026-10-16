import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

// Auto-reload with cache bust on Vite dynamic import chunk failure (e.g. after system update)
window.addEventListener('vite:preloadError', (event) => {
  event.preventDefault()
  console.warn('[Vite] Preload error detected, reloading page with cache bust...')
  const url = new URL(window.location.href)
  url.searchParams.set('_v', Date.now().toString())
  window.location.replace(url.toString())
})

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.component('VueMultiselect', VueMultiselect)
app.mount('#app')
