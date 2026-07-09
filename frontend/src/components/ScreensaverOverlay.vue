<template>
  <Transition name="screensaver">
    <div v-if="visible" class="screensaver-overlay" @click="dismiss" @touchstart="dismiss" @keydown="dismiss" tabindex="0" ref="overlayRef">
      <!-- Background media with crossfade -->
      <div class="screensaver-slides">
        <Transition name="crossfade" mode="out-in">
          <div :key="currentIndex" class="screensaver-slide">
            <!-- Video slide -->
            <video
              v-if="currentMediaType === 'video'"
              ref="videoRef"
              :src="currentMediaUrl"
              class="absolute inset-0 w-full h-full object-cover"
              autoplay
              muted
              playsinline
              @ended="onVideoEnded"
              @error="onVideoError"
            ></video>
            <!-- Image slide -->
            <div
              v-else
              class="absolute inset-0"
              :style="{ backgroundImage: `url(${currentMediaUrl})`, backgroundSize: 'cover', backgroundPosition: 'center', backgroundRepeat: 'no-repeat' }"
            ></div>
          </div>
        </Transition>
      </div>

      <!-- Gradient overlay (adapts to dark/light mode) -->
      <div class="absolute inset-0 pointer-events-none"
           :class="isDark 
             ? 'bg-gradient-to-t from-[#020617]/90 via-transparent to-[#020617]/40' 
             : 'bg-gradient-to-t from-white/95 via-transparent to-white/50'"></div>

      <!-- Bottom info bar -->
      <div class="absolute bottom-0 left-0 right-0 flex items-center justify-end px-10 py-6 pointer-events-none">
        <div class="flex items-center gap-2" :class="isDark ? 'text-white/50' : 'text-slate-600'">
          <span class="material-symbols-outlined text-lg animate-pulse">touch_app</span>
          <span class="text-sm font-medium tracking-wide">Tap to open apps</span>
        </div>
      </div>

    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { usePublicTheme } from '../composables/usePublicTheme'
import api from '../axios'
import echo from '../echo'

const route = useRoute()
const { isDark } = usePublicTheme()
const overlayRef = ref(null)
const videoRef = ref(null)

// ── State ──
const visible = ref(false)
const mediaItems = ref([])   // [{ url, media_type, id, sort_order }]
const currentIndex = ref(0)
const idleTimeout = ref(30) // seconds
const interval = ref(8)    // seconds between image slides
const configLoaded = ref(false)

// ── Timers ──
let idleTimer = null
let slideTimer = null
let echoListening = false

// ── Current media info ──
const currentMediaUrl = ref('')
const currentMediaType = ref('image')

watch(currentIndex, (idx) => {
  if (mediaItems.value.length > 0) {
    const item = mediaItems.value[idx]
    currentMediaUrl.value = item?.url || ''
    currentMediaType.value = item?.media_type || 'image'
  }
})

// ── Check if this is a TV display (not admin) ──
function isTvDisplay() {
  const token = localStorage.getItem('tv_token')
  const isAdmin = !!localStorage.getItem('auth_token')
  const isAdminPath = route.path.startsWith('/administrator') || route.path === '/login'
  return !!token && !isAdmin && !isAdminPath
}

// ── Fetch screensaver config ──
async function fetchConfig() {
  const token = localStorage.getItem('tv_token')
  if (!token) return

  try {
    const { data } = await api.get('/screensaver/tv', { params: { token } })
    if (data.active && data.images?.length) {
      const oldTimeout = idleTimeout.value
      const oldInterval = interval.value
      const oldMediaUrls = mediaItems.value.map(i => i.url).join(',')

      idleTimeout.value = data.idle_timeout || 30
      interval.value = data.interval || 8
      mediaItems.value = data.images.map(img => ({
        url: img.url,
        media_type: img.media_type || 'image',
        id: img.id,
        sort_order: img.sort_order,
      }))
      currentMediaUrl.value = mediaItems.value[0]?.url || ''
      currentMediaType.value = mediaItems.value[0]?.media_type || 'image'
      configLoaded.value = true

      const newMediaUrls = mediaItems.value.map(i => i.url).join(',')
      const configChanged = oldTimeout !== idleTimeout.value ||
                            oldInterval !== interval.value ||
                            oldMediaUrls !== newMediaUrls

      // If config changed while screensaver is showing, restart slideshow
      if (configChanged && visible.value) {
        currentIndex.value = 0
        currentMediaUrl.value = mediaItems.value[0]?.url || ''
        currentMediaType.value = mediaItems.value[0]?.media_type || 'image'
        startSlideshow()
      }

      return configChanged
    } else {
      // Config disabled — dismiss screensaver if showing
      if (visible.value) dismiss()
      configLoaded.value = false
      return true
    }
  } catch {
    configLoaded.value = false
    return false
  }
}

// ── Idle detection ──
const userEvents = ['mousedown', 'mousemove', 'keydown', 'touchstart', 'scroll', 'click']

function resetIdleTimer() {
  if (!configLoaded.value) return
  if (visible.value) return // don't reset while screensaver is showing

  clearTimeout(idleTimer)
  
  let currentTimeout = idleTimeout.value * 1000
  // Jika sedang membuka iframe (Apps), perpanjang timeout menjadi minimal 10 menit (600.000 ms)
  // karena event scroll/mouse dalam cross-origin iframe tidak bisa dideteksi oleh parent.
  if (document.querySelector('iframe')) {
    currentTimeout = Math.max(currentTimeout, 600000)
  }

  idleTimer = setTimeout(() => {
    if (isTvDisplay() && configLoaded.value) {
      showScreensaver()
    }
  }, currentTimeout)
}

function startIdleDetection() {
  userEvents.forEach(ev => {
    document.addEventListener(ev, resetIdleTimer, { capture: true, passive: true })
  })
  window.addEventListener('blur', resetIdleTimer)
  window.addEventListener('focus', resetIdleTimer)
  window.addEventListener('message', resetIdleTimer)
  resetIdleTimer()
}

function stopIdleDetection() {
  userEvents.forEach(ev => {
    document.removeEventListener(ev, resetIdleTimer, { capture: true })
  })
  window.removeEventListener('blur', resetIdleTimer)
  window.removeEventListener('focus', resetIdleTimer)
  window.removeEventListener('message', resetIdleTimer)
  clearTimeout(idleTimer)
}

// ── Slideshow ──
function showScreensaver() {
  if (mediaItems.value.length === 0) return
  currentIndex.value = 0
  currentMediaUrl.value = mediaItems.value[0]?.url || ''
  currentMediaType.value = mediaItems.value[0]?.media_type || 'image'
  visible.value = true

  nextTick(() => {
    overlayRef.value?.focus()
  })

  startSlideshow()
}

function startSlideshow() {
  clearInterval(slideTimer)
  if (mediaItems.value.length <= 1) return

  // Only start interval timer if current slide is an image.
  // For videos, we wait for `onended` event.
  const currentItem = mediaItems.value[currentIndex.value]
  if (currentItem?.media_type !== 'video') {
    scheduleNextSlide()
  }
  // If video, the onended handler will advance the slide.
}

function scheduleNextSlide() {
  clearTimeout(slideTimer)
  slideTimer = setTimeout(() => {
    advanceSlide()
  }, interval.value * 1000)
}

function advanceSlide() {
  if (mediaItems.value.length <= 1) return
  currentIndex.value = (currentIndex.value + 1) % mediaItems.value.length

  // After advancing, check if the new slide is image or video
  nextTick(() => {
    const newItem = mediaItems.value[currentIndex.value]
    if (newItem?.media_type !== 'video') {
      // Image: schedule next advance on interval timer
      scheduleNextSlide()
    }
    // Video: wait for onended
  })
}

function onVideoEnded() {
  // Video finished playing — advance to next slide
  if (mediaItems.value.length > 1) {
    advanceSlide()
  }
  // If only 1 media (a single video), replay it
  else if (videoRef.value) {
    videoRef.value.currentTime = 0
    videoRef.value.play()
  }
}

function onVideoError() {
  // If video fails to load, skip to next slide after a short delay
  setTimeout(() => {
    advanceSlide()
  }, 2000)
}

function dismiss() {
  visible.value = false
  clearTimeout(slideTimer)
  clearInterval(slideTimer)
  resetIdleTimer()
}

// ── Echo/Reverb WebSocket listener ──
function startEchoListener() {
  if (echoListening) return
  echoListening = true

  echo.channel('tv-devices')
    .listen('.screensaver.updated', async () => {
      if (!isTvDisplay()) return
      const changed = await fetchConfig()
      if (changed && configLoaded.value && !visible.value) {
        stopIdleDetection()
        startIdleDetection()
      }
    })
}

function stopEchoListener() {
  if (!echoListening) return
  // Note: don't leave the channel here since other listeners (App.vue) use it too
  echoListening = false
}

// ── Force activate/deactivate from admin (via Control Center) ──
async function handleForceActivate() {
  // Only respond on TV displays
  if (!isTvDisplay()) return

  // If config not loaded yet, fetch it first
  if (!configLoaded.value || mediaItems.value.length === 0) {
    await fetchConfig()
  }

  // Show screensaver immediately (skip idle timeout)
  if (configLoaded.value && mediaItems.value.length > 0) {
    showScreensaver()
  }
}

function handleForceDeactivate() {
  if (!isTvDisplay()) return
  dismiss()
}

// ── Lifecycle ──
onMounted(async () => {
  if (isTvDisplay()) {
    await fetchConfig()
    if (configLoaded.value) {
      startIdleDetection()
    }
    startEchoListener()
  }

  // Listen for admin-pushed screensaver commands
  window.addEventListener('screensaver-force-activate', handleForceActivate)
  window.addEventListener('screensaver-force-deactivate', handleForceDeactivate)
})

onUnmounted(() => {
  stopIdleDetection()
  clearInterval(slideTimer)
  clearTimeout(slideTimer)
  stopEchoListener()
  window.removeEventListener('screensaver-force-activate', handleForceActivate)
  window.removeEventListener('screensaver-force-deactivate', handleForceDeactivate)
})

// ── Re-check on route changes ──
watch(() => route.fullPath, async () => {
  if (isTvDisplay()) {
    if (!configLoaded.value) {
      await fetchConfig()
    }
    if (configLoaded.value) {
      startIdleDetection()
    }
    startEchoListener()
  } else {
    stopIdleDetection()
    stopEchoListener()
    visible.value = false
  }
})
</script>

<style scoped>
.screensaver-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: #000;
  outline: none;
  cursor: pointer;
}

.screensaver-slides {
  position: absolute;
  inset: 0;
}

.screensaver-slide {
  position: absolute;
  inset: 0;
}

/* Screensaver enter/leave */
.screensaver-enter-active {
  animation: screensaverIn 0.8s ease-out;
}
.screensaver-leave-active {
  animation: screensaverOut 0.4s ease-in;
}

@keyframes screensaverIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
@keyframes screensaverOut {
  from { opacity: 1; }
  to   { opacity: 0; }
}

/* Crossfade between slides */
.crossfade-enter-active {
  transition: opacity 1s ease;
}
.crossfade-leave-active {
  transition: opacity 0.8s ease;
}
.crossfade-enter-from {
  opacity: 0;
}
.crossfade-leave-to {
  opacity: 0;
}
</style>
