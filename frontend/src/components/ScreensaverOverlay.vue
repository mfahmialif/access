<template>
  <Transition name="screensaver">
    <div v-if="visible" class="screensaver-overlay" @click="dismiss" @touchstart="dismiss" @keydown="dismiss" tabindex="0" ref="overlayRef">
      <!-- Background images with crossfade -->
      <div class="screensaver-slides">
        <Transition name="crossfade" mode="out-in">
          <div :key="currentIndex" class="screensaver-slide" :style="{ backgroundImage: `url(${currentImage})` }"></div>
        </Transition>
      </div>

      <!-- Gradient overlay -->
      <div class="screensaver-gradient"></div>

      <!-- Bottom info bar -->
      <div class="screensaver-info">
        <div class="flex items-center gap-3">
          <img src="/img/logo.png" alt="Access" class="w-10 h-10 object-contain opacity-60" />
          <div>
            <p class="text-white/80 text-sm font-bold tracking-widest uppercase">Access TV</p>
            <p class="text-white/40 text-xs">Sentuh layar untuk buka aplikasi</p>
          </div>
        </div>
        <div class="flex items-center gap-2 text-white/30">
          <span class="material-symbols-outlined text-lg">touch_app</span>
          <span class="text-xs font-medium">Tap to open apps</span>
        </div>
      </div>

      <!-- Image counter dots -->
      <div v-if="images.length > 1" class="screensaver-dots">
        <span v-for="(_, i) in images" :key="i" class="dot" :class="{ active: i === currentIndex }"></span>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import api from '../axios'
import echo from '../echo'

const route = useRoute()
const overlayRef = ref(null)

// ── State ──
const visible = ref(false)
const images = ref([])
const currentIndex = ref(0)
const idleTimeout = ref(30) // seconds
const interval = ref(8)    // seconds
const configLoaded = ref(false)

// ── Timers ──
let idleTimer = null
let slideTimer = null
let echoListening = false

// ── Computed ──
const currentImage = ref('')

watch(currentIndex, (idx) => {
  if (images.value.length > 0) {
    currentImage.value = images.value[idx]?.url || ''
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
      const oldImageUrls = images.value.map(i => i.url).join(',')

      idleTimeout.value = data.idle_timeout || 30
      interval.value = data.interval || 8
      images.value = data.images
      currentImage.value = data.images[0]?.url || ''
      configLoaded.value = true

      const newImageUrls = data.images.map(i => i.url).join(',')
      const configChanged = oldTimeout !== idleTimeout.value ||
                            oldInterval !== interval.value ||
                            oldImageUrls !== newImageUrls

      // If config changed while screensaver is showing, restart slideshow
      if (configChanged && visible.value) {
        currentIndex.value = 0
        currentImage.value = images.value[0]?.url || ''
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
  idleTimer = setTimeout(() => {
    if (isTvDisplay() && configLoaded.value) {
      showScreensaver()
    }
  }, idleTimeout.value * 1000)
}

function startIdleDetection() {
  userEvents.forEach(ev => {
    document.addEventListener(ev, resetIdleTimer, { passive: true })
  })
  resetIdleTimer()
}

function stopIdleDetection() {
  userEvents.forEach(ev => {
    document.removeEventListener(ev, resetIdleTimer)
  })
  clearTimeout(idleTimer)
}

// ── Slideshow ──
function showScreensaver() {
  if (images.value.length === 0) return
  currentIndex.value = 0
  currentImage.value = images.value[0]?.url || ''
  visible.value = true

  nextTick(() => {
    overlayRef.value?.focus()
  })

  startSlideshow()
}

function startSlideshow() {
  clearInterval(slideTimer)
  if (images.value.length <= 1) return

  slideTimer = setInterval(() => {
    currentIndex.value = (currentIndex.value + 1) % images.value.length
  }, interval.value * 1000)
}

function dismiss() {
  visible.value = false
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

// ── Lifecycle ──
onMounted(async () => {
  if (isTvDisplay()) {
    await fetchConfig()
    if (configLoaded.value) {
      startIdleDetection()
    }
    startEchoListener()
  }
})

onUnmounted(() => {
  stopIdleDetection()
  clearInterval(slideTimer)
  stopEchoListener()
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
  z-index: 9999;
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
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.screensaver-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.7) 0%,
    rgba(0, 0, 0, 0) 30%,
    rgba(0, 0, 0, 0) 70%,
    rgba(0, 0, 0, 0.3) 100%
  );
  pointer-events: none;
}

.screensaver-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 40px;
  pointer-events: none;
}

.screensaver-dots {
  position: absolute;
  bottom: 80px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 8px;
  pointer-events: none;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transition: all 0.4s ease;
}

.dot.active {
  background: rgba(251, 191, 36, 0.9);
  width: 24px;
  border-radius: 4px;
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.5);
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
