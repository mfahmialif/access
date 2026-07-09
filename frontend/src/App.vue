<template>
  <router-view />

  <!-- ═══ GLOBAL BANNER OVERLAY (only for TV displays, not admin/operator) ═══ -->
  <Transition name="banner">
    <div v-if="globalBanner && !isAdminUser" class="fixed inset-0 z-999 flex flex-col items-center justify-center p-6 sm:p-12">
      <!-- Blurred backdrop -->
      <div class="absolute inset-0 backdrop-blur-md bg-[#020c1b]/85"></div>

      <!-- Card -->
      <div class="relative w-full max-w-5xl rounded-2xl overflow-hidden flex flex-col md:flex-row"
           :class="bannerCardBorder"
           :style="{ background: '#112240', boxShadow: bannerGlow }">

        <!-- Decorative corner gradient -->
        <div class="absolute top-0 left-0 w-32 h-32 pointer-events-none"
             :style="{ background: `linear-gradient(135deg, ${bannerAccent}15, transparent)` }"></div>

        <!-- Left Panel -->
        <div class="relative w-full md:w-2/5 flex flex-col items-center justify-center p-8 md:p-12 border-b md:border-b-0 md:border-r border-white/5" style="background: #0a192f">
          <div class="absolute top-4 left-4">
            <span class="material-symbols-outlined text-4xl" :style="{ color: bannerAccent + '40' }">branding_watermark</span>
          </div>
          <div class="w-56 h-56 rounded-full flex items-center justify-center mb-6 ring-1 animate-pulse-slow"
               :style="{ background: bannerAccent + '15', ringColor: bannerAccent + '30', boxShadow: `0 0 40px ${bannerAccent}40` }">
            <span class="material-symbols-outlined text-[120px]!" :style="{ color: bannerAccent }">
              {{ globalBanner.type === 'emergency' ? 'warning' : globalBanner.type === 'announcement' ? 'notifications_active' : 'info' }}
            </span>
          </div>
          <h2 class="font-bold text-xl tracking-wider text-center uppercase mb-2" :style="{ color: bannerAccent }">Access Alert</h2>
          <p class="text-slate-400 text-center text-sm">System Broadcast</p>
        </div>

        <!-- Right Panel -->
        <div class="w-full md:w-3/5 flex flex-col" style="background: #112240">

          <!-- Top bar -->
          <div class="flex items-center justify-between px-8 py-5 border-b border-white/5" style="background: #0a192f">
            <div class="flex items-center gap-3">
              <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :style="{ background: bannerDotColor }"></span>
                <span class="relative inline-flex rounded-full h-3 w-3" :style="{ background: bannerDotColor }"></span>
              </span>
              <p class="text-white font-bold tracking-widest text-sm uppercase">{{ bannerLabel }}</p>
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 px-8 py-8 flex flex-col justify-center">
            <div class="mb-2">
              <span class="inline-block px-2 py-1 rounded text-xs font-bold tracking-wider uppercase mb-3"
                    :style="{ background: bannerAccent + '20', color: bannerAccent }">
                {{ globalBanner.type === 'emergency' ? 'DARURAT' : globalBanner.type === 'announcement' ? 'PENGUMUMAN' : 'INFORMASI' }}
              </span>
              <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-6">
                {{ globalBanner.title }}
              </h1>
            </div>
            <div class="banner-content text-lg text-slate-300 leading-relaxed" v-html="fixHtmlAssetUrls(globalBanner.message)"></div>
          </div>

          <!-- Footer -->
          <div class="px-8 py-5 border-t border-white/5 flex items-center justify-between" style="background: #0a192f">
            <div class="flex items-center gap-2 text-slate-500 text-sm">
              <span class="material-symbols-outlined text-lg">info</span>
              <span>Broadcast aktif di seluruh TV</span>
            </div>
            <button @click="forceDisconnect" class="flex items-center gap-2 px-4 py-1.5 rounded-lg text-xs font-bold text-red-400 hover:bg-red-500/10 border border-red-500/20 hover:border-red-500/40 cursor-pointer transition-all">
              <span class="material-symbols-outlined text-sm">link_off</span>
              Putuskan Sambungan
            </button>
          </div>
        </div>
      </div>

      <!-- Bottom info -->
      <div class="relative flex gap-6 text-slate-500 text-sm font-medium tracking-wide opacity-60 mt-6">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-lg">rss_feed</span>
          <span>Live Feed Paused</span>
        </div>
      </div>
    </div>
  </Transition>

  <!-- ═══ IMPERSONATE FLOATING BADGE ═══ -->
  <Transition name="fade">
    <div v-if="impersonateDevice" class="fixed top-4 right-4 z-999 flex items-center gap-3 px-5 py-3 rounded-xl shadow-2xl border border-purple-500/40" style="background: linear-gradient(135deg, rgba(147,51,234,0.15), rgba(15,23,42,0.95)); backdrop-filter: blur(12px)">
      <div class="flex h-3 w-3 relative">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-400"></span>
      </div>
      <div>
        <p class="text-[11px] font-bold uppercase tracking-widest text-purple-300">Mengendalikan</p>
        <p class="text-sm font-bold text-white">{{ impersonateDevice.name }}</p>
      </div>
      <button @click="stopImpersonate" class="ml-2 p-1.5 rounded-lg text-purple-300 hover:text-white hover:bg-purple-500/20 transition-colors cursor-pointer" title="Hentikan Impersonifikasi">
        <span class="material-symbols-outlined text-[18px]">close</span>
      </button>
    </div>
  </Transition>



  <!-- ═══ SCREENSAVER OVERLAY (only for TV displays) ═══ -->
  <ScreensaverOverlay />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from './axios'
import echo from './echo'
import { fixHtmlAssetUrls } from './utils/asset'
import ScreensaverOverlay from './components/ScreensaverOverlay.vue'
import { usePublicTheme } from './composables/usePublicTheme'

const router = useRouter()
const route = useRoute()

// ── Public Theme (used by banner card styling) ──
const { isDark } = usePublicTheme()

// ── Global Banner State ──
const globalBanner = ref(null) // { title, message, type }

// ── Impersonate State ──
const impersonateDevice = ref(null)   // { id, name }
const impersonateDeviceId = ref(null) // device ID from query



// ── Admin/operator detection (don't show banner for them, UNLESS admin_view=1 or impersonating) ──
const isAdminUser = computed(() => {
  // If admin_view=1 or impersonating, allow banner even for admin
  if (route.query.admin_view === '1') return false
  if (impersonateDeviceId.value) return false
  return !!localStorage.getItem('auth_token') || route.path.startsWith('/administrator') || route.path === '/login'
})

const bannerAccent = computed(() => {
  if (!globalBanner.value) return '#ffc800'
  const t = globalBanner.value.type
  return t === 'emergency' ? '#ef4444' : t === 'announcement' ? '#ffc800' : '#3b82f6'
})
const bannerGlow = computed(() => `0 0 50px ${bannerAccent.value}25`)
const bannerDotColor = computed(() => {
  if (!globalBanner.value) return '#ef4444'
  const t = globalBanner.value.type
  return t === 'emergency' ? '#ef4444' : t === 'announcement' ? '#fbbf24' : '#3b82f6'
})
const bannerLabel = computed(() => {
  if (!globalBanner.value) return ''
  const t = globalBanner.value.type
  return t === 'emergency' ? 'PESAN DARURAT' : t === 'announcement' ? 'PRIORITY MESSAGE' : 'INFORMASI'
})
const bannerCardBorder = computed(() => {
  if (!globalBanner.value) return 'border-2 border-amber-500'
  const t = globalBanner.value.type
  return t === 'emergency' ? 'border-2 border-red-500' : t === 'announcement' ? 'border-2 border-amber-500' : 'border-2 border-blue-500'
})

// ── Force disconnect helper ──
function forceDisconnect() {
  const token = localStorage.getItem('tv_token')
  if (token) {
    const data = new Blob([JSON.stringify({ token })], { type: 'application/json' })
    navigator.sendBeacon('/api/tv/disconnect', data)
  }
  localStorage.removeItem('tv_token')
  localStorage.removeItem('tv_device')
  globalBanner.value = null
  if (heartbeatInterval) clearInterval(heartbeatInterval)
  echo.leave('tv-devices')
  router.push({ name: 'ConnectToken' })
}

// ── Current page for heartbeat tracking (sends route path) ──
function getCurrentPageName() {
  return route.fullPath || '/'
}

// ── TV Heartbeat: keep device status "online" ──
let heartbeatInterval = null
let echoListening = false

function startHeartbeat() {
  // Don't duplicate
  if (heartbeatInterval) return

  const token = localStorage.getItem('tv_token')
  if (!token) return

  // Send immediately — validate token still exists
  api.post('/tv/heartbeat', { token, current_page: getCurrentPageName() }).catch((err) => {
    if (err.response?.status === 404) forceDisconnect()
  })

  // Then every 90 seconds
  heartbeatInterval = setInterval(() => {
    const t = localStorage.getItem('tv_token')
    if (t) {
      api.post('/tv/heartbeat', { token: t, current_page: getCurrentPageName() }).catch((err) => {
        if (err.response?.status === 404) forceDisconnect()
      })
    } else {
      clearInterval(heartbeatInterval)
      heartbeatInterval = null
    }
  }, 90000)
}

// ── Disconnect on browser close ──
function handleBeforeUnload() {
  const token = localStorage.getItem('tv_token')
  if (!token) return
  const data = new Blob([JSON.stringify({ token })], { type: 'application/json' })
  navigator.sendBeacon('/api/tv/disconnect', data)
}

// ── Handle TV commands from admin ──
function handleTvCommand(e) {
  const token = localStorage.getItem('tv_token')
  if (!token) return

  // If command targets a specific device, check if it's us
  if (e.target_token && e.target_token !== token) return

  switch (e.command) {
    case 'navigate':
      if (e.payload?.path) router.push(e.payload.path)
      break
    case 'reload':
      window.location.reload()
      break
    case 'home':
      router.push('/')
      break
    case 'banner_show':
      globalBanner.value = {
        title: e.payload?.title || 'Pengumuman',
        message: e.payload?.message || '',
        type: e.payload?.type || 'info',
      }
      break
    case 'banner_dismiss':
      globalBanner.value = null
      // Re-fetch: if this device has a per-device banner in DB, show it
      fetchActiveBanner()
      break
    case 'screensaver_activate':
      window.dispatchEvent(new Event('screensaver-force-activate'))
      break
    case 'screensaver_deactivate':
      window.dispatchEvent(new Event('screensaver-force-deactivate'))
      break
  }
}

// ── Listen for force-disconnect + TV commands ──
function startEchoListener() {
  if (echoListening) return
  const token = localStorage.getItem('tv_token')
  if (!token) return

  echoListening = true
  echo.channel('tv-devices')
    .listen('.device.deleted', (e) => {
      if (e.token === token) forceDisconnect()
    })
    .listen('.tv.command', handleTvCommand)
}

// ── Send instant heartbeat on route change ──
function sendInstantHeartbeat() {
  const token = localStorage.getItem('tv_token')
  if (!token) return
  api.post('/tv/heartbeat', { token, current_page: getCurrentPageName() }).catch(() => {})
}

// ── Re-check on every route change (catches post-connect navigation) ──
watch(() => route.fullPath, () => {
  startHeartbeat()
  startEchoListener()
  sendInstantHeartbeat()
  fetchActiveBanner()
})

// ── Fetch active banner on load (survives refresh) ──
async function fetchActiveBanner() {
  try {
    const token = localStorage.getItem('tv_token')
    const params = { token }
    // If admin_view mode, use device_id from query param
    if (route.query.admin_view === '1' && route.query.device_id) {
      params.device_id = route.query.device_id
    }
    const { data } = await api.get('/tv-commands/active-banner', { params })
    if (data.active && data.banner) {
      globalBanner.value = {
        title: data.banner.title || 'Pengumuman',
        message: data.banner.message || '',
        type: data.banner.type || 'info',
      }
    }
  } catch { /* silent */ }
}

onMounted(async () => {
  startHeartbeat()
  startEchoListener()
  fetchActiveBanner()
  window.addEventListener('beforeunload', handleBeforeUnload)

  // ── Impersonate mode detection ──
  // Must wait for router to be ready — route.query is empty at mount time
  await router.isReady()
  console.log('[Impersonate] Route ready, query:', route.query)

  if (route.query.impersonate) {
    impersonateDeviceId.value = Number(route.query.impersonate)
    console.log('[Impersonate] Detected device_id:', impersonateDeviceId.value)
    try {
      // Fetch device info to display name
      const devRes = await api.get('/tv-devices', { params: { per_page: 100 } })
      const dev = (devRes.data.data || []).find(d => d.id == impersonateDeviceId.value)
      if (dev) {
        impersonateDevice.value = { id: dev.id, name: dev.name, unit_id: dev.unit_id }
        // Store TV's unit_id so axios interceptor uses it instead of admin's
        if (dev.unit_id) {
          localStorage.setItem('impersonate_unit_id', dev.unit_id)
        }
      } else {
        impersonateDevice.value = { id: impersonateDeviceId.value, name: `TV #${impersonateDeviceId.value}` }
      }
    } catch (e) {
      console.warn('Impersonate: failed to fetch device info', e)
      impersonateDevice.value = { id: impersonateDeviceId.value, name: `TV #${impersonateDeviceId.value}` }
    }
    // Push initial page to TV
    pushToTv('/')
  }
})

// ── Impersonate: push navigation to TV on every route change ──
function pushToTv(path) {
  if (!impersonateDeviceId.value) return
  console.log('[Impersonate] Pushing path to TV:', path, 'device_id:', impersonateDeviceId.value)
  api.post('/tv-commands/push', {
    device_id: Number(impersonateDeviceId.value),
    path: path,
    label: 'Impersonate',
  }).then(res => {
    console.log('[Impersonate] Push result:', res.data)
  }).catch(err => {
    console.error('[Impersonate] Push failed:', err.response?.data || err.message)
  })
}

function stopImpersonate() {
  impersonateDevice.value = null
  impersonateDeviceId.value = null
  localStorage.removeItem('impersonate_unit_id')
  window.close()
}

// Watch route changes for impersonate mode — push navigation to real TV
let pushDebounce = null
watch(() => route.fullPath, (newPath) => {
  if (!impersonateDeviceId.value || !newPath) return
  // Debounce to avoid duplicate pushes on rapid navigation
  clearTimeout(pushDebounce)
  pushDebounce = setTimeout(() => {
    // Extract just the path without query params for clarity
    const cleanPath = newPath.split('?')[0]
    pushToTv(cleanPath)
  }, 200)
})

// Re-fetch banner when admin_view query appears (new tab may not have query ready at mount)
watch(() => route.query.admin_view, (val) => {
  if (val === '1') fetchActiveBanner()
})

onUnmounted(() => {
  if (heartbeatInterval) clearInterval(heartbeatInterval)
  window.removeEventListener('beforeunload', handleBeforeUnload)
  echo.leave('tv-devices')
})
</script>

<style>
#app {
  min-height: 100vh;
}

/* Page route transitions */
.page-enter-active {
  animation: pageIn 0.4s ease-out;
}
.page-leave-active {
  animation: pageOut 0.2s ease-in;
}

@keyframes pageIn {
  from {
    opacity: 0;
    transform: scale(1.02) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}
@keyframes pageOut {
  from {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
  to {
    opacity: 0;
    transform: scale(0.98) translateY(-8px);
  }
}

/* Public page crossfade — avoids Teleport + out-in crash */
.page-crossfade-enter-active {
  transition: opacity 0.3s ease-out;
}
.page-crossfade-leave-active {
  transition: opacity 0.2s ease-in;
  position: absolute;
  inset: 0;
  z-index: 0;
}
.page-crossfade-enter-from {
  opacity: 0;
}
.page-crossfade-leave-to {
  opacity: 0;
}

/* Banner transitions */
.banner-enter-active {
  animation: bannerIn 0.5s ease-out;
}
.banner-leave-active {
  animation: bannerOut 0.3s ease-in;
}
@keyframes bannerIn {
  from { opacity: 0; transform: scale(1.1); }
  to   { opacity: 1; transform: scale(1); }
}
@keyframes bannerOut {
  from { opacity: 1; transform: scale(1); }
  to   { opacity: 0; transform: scale(0.95); }
}

/* Slow pulse for banner text */
@keyframes pulseSlow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.85; }
}
.animate-pulse-slow {
  animation: pulseSlow 3s ease-in-out infinite;
}

/* Banner content (rendered HTML from Quill) */
.banner-content p { margin-bottom: 0.5em; }
.banner-content h1 { font-size: 2em; font-weight: 800; margin-bottom: 0.3em; }
.banner-content h2 { font-size: 1.5em; font-weight: 700; margin-bottom: 0.3em; }
.banner-content h3 { font-size: 1.2em; font-weight: 600; margin-bottom: 0.3em; }
.banner-content strong { font-weight: 800; }
.banner-content em { font-style: italic; }
.banner-content u { text-decoration: underline; }
.banner-content ul, .banner-content ol { text-align: left; padding-left: 1.5em; margin: 0.5em auto; max-width: max-content; }
.banner-content li { margin-bottom: 0.25em; }
.banner-content a { color: #93c5fd; text-decoration: underline; }

/* Fade transitions (impersonate badge) */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>

