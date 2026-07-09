<template>
  <div class="public-layout relative w-full h-dvh overflow-hidden"
       :class="isDark ? 'text-slate-100 bg-[#0a192f] selection:bg-accent selection:text-[#0a192f]' : 'text-slate-800 bg-slate-50 selection:bg-accent selection:text-white'">
    
    <!-- ═══════ BACKGROUND TELEPORT TARGET ═══════ -->
    <div id="public-bg" class="fixed inset-0 z-0 pointer-events-none overflow-hidden"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col h-dvh w-full px-3 py-3 sm:px-4 md:px-6 lg:px-8 md:py-4 lg:py-6 max-w-[1920px] mx-auto overflow-hidden">
      
      <!-- ═══════ HEADER ═══════ -->
      <div class="relative z-50 w-full shrink-0">
        <PublicHeader :showBack="route.name !== 'Landing'" />
      </div>

      <!-- ═══════ CHILD PAGE CONTENT ═══════ -->
      <main class="relative z-10 flex-1 min-h-0 flex flex-col pt-3 md:pt-4 lg:pt-5 w-full overflow-hidden">
        <simplebar ref="simplebarEl" class="h-full w-full public-content-scroll" :auto-hide="false">
          <router-view v-slot="{ Component }">
            <Transition name="page" mode="out-in">
              <component :is="Component" />
            </Transition>
          </router-view>
        </simplebar>
      </main>
    </div>

    <!-- ═══════ CONFIG MENU (floating, fixed) ═══════ -->
    <Transition name="fade">
      <div v-if="showConfigMenu" 
           class="fixed right-[16px] z-50 flex flex-col gap-1 p-1.5 rounded-full shadow-[0_10px_40px_rgba(0,0,0,0.3)] border transition-all duration-300"
           :class="[
             isDark ? 'bg-[#0f172a] opacity-90 border border-[#1e293b]' : 'bg-white opacity-90 border border-slate-300',
             route.name !== 'Landing' ? 'bottom-[170px]' : 'bottom-[115px]'
           ]">
        
        <!-- TV Device Info (Only if connected) -->
        <template v-if="deviceName">
          <div class="relative flex items-center justify-center">
            <button @click="showTvMenu = !showTvMenu" 
                    class="flex items-center justify-center size-10 rounded-full transition-all cursor-pointer group"
                    :class="isDark ? (showTvMenu ? 'bg-[#0f2e20] text-green-400' : 'hover:bg-[#072418] text-green-400') : (showTvMenu ? 'bg-green-100 text-green-600' : 'hover:bg-green-50 text-green-500')"
                    title="Opsi TV">
              <div class="absolute top-2 right-2 flex h-2 w-2 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
              </div>
              <span class="material-symbols-outlined text-[20px]">tv</span>
            </button>
            
            <!-- TV Menu Popup (Slide Left) -->
            <Transition name="fade">
              <div v-if="showTvMenu" 
                   class="absolute right-[calc(100%+12px)] top-1/2 -translate-y-1/2 z-50 flex items-center gap-3 p-2.5 rounded-2xl shadow-xl border whitespace-nowrap"
                   :class="isDark ? 'bg-[#0f172a] opacity-90 border-[#1e293b]' : 'bg-white opacity-90 border-slate-300'">
                <template v-if="disconnectCountdown > 0">
                  <div class="flex items-center gap-2 px-1">
                    <div class="relative flex items-center justify-center size-7 shrink-0">
                      <svg class="absolute inset-0 size-full -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-slate-200 dark:stroke-[#1e293b]" stroke-width="4"></circle>
                        <circle cx="18" cy="18" r="16" fill="none" class="stroke-red-500 transition-all duration-1000 ease-linear" stroke-width="4" stroke-dasharray="100" :stroke-dashoffset="100 - ((disconnectCountdown / 3) * 100)"></circle>
                      </svg>
                      <span class="text-[10px] font-bold text-red-500 relative z-10">{{ disconnectCountdown }}</span>
                    </div>
                    <div class="flex flex-col ml-1">
                      <span class="text-xs font-bold text-red-500">Memutuskan...</span>
                    </div>
                    <div class="w-px h-6 mx-1" :class="isDark ? 'bg-[#1e293b]' : 'bg-slate-200'"></div>
                    <button @click="cancelDisconnect" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-[#1e293b] dark:hover:bg-[#334155] transition-all cursor-pointer">
                      Batal
                    </button>
                  </div>
                </template>
                
                <template v-else>
                  <div class="flex flex-col px-1">
                    <span class="text-[9px] uppercase tracking-widest text-slate-500 font-bold mb-0.5">Terhubung ke</span>
                    <span class="text-sm font-bold" :class="isDark ? 'text-green-400' : 'text-green-600'">{{ deviceName }}</span>
                    <span class="text-[10px] font-medium mt-0.5" :class="isDark ? 'text-slate-400' : 'text-slate-500'">{{ unitName }}</span>
                  </div>
                  <div class="w-px h-8" :class="isDark ? 'bg-[#1e293b]' : 'bg-slate-200'"></div>
                  <button @click="initiateDisconnect" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-red-500 hover:bg-[#450a0a] border border-transparent hover:border-[#7f1d1d] transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">link_off</span>
                    Putuskan
                  </button>
                </template>
              </div>
            </Transition>
          </div>
          <div class="h-px w-6 mx-auto my-0.5" :class="isDark ? 'bg-[#1e293b]' : 'bg-slate-200'"></div>
        </template>
        
        <!-- Screensaver (activate immediately) -->
        <button @click="activateScreensaver(); showConfigMenu = false"
                class="flex items-center justify-center size-10 rounded-full transition-all cursor-pointer"
                :class="isDark ? 'hover:bg-[#1e293b] text-accent hover:text-[#fcd34d]' : 'hover:bg-slate-100 text-slate-600 hover:text-amber-500'"
                title="Aktifkan Screensaver">
          <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">dark_mode</span>
        </button>
        
        <!-- Theme Toggle -->
        <button @click="toggleTheme(); showConfigMenu = false"
                class="flex items-center justify-center size-10 rounded-full transition-all cursor-pointer"
                :class="isDark ? 'hover:bg-[#1e293b] text-accent hover:text-[#fcd34d]' : 'hover:bg-slate-100 text-slate-600 hover:text-amber-500'"
                :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
          <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">
            {{ isDark ? 'light_mode' : 'dark_mode' }}
          </span>
        </button>
        
        <div class="h-px w-6 mx-auto my-0.5" :class="isDark ? 'bg-[#1e293b]' : 'bg-slate-200'"></div>
        
        <!-- Refresh Page -->
        <button @click="refreshPage"
                class="flex items-center justify-center size-10 rounded-full transition-all cursor-pointer"
                :class="isDark ? 'hover:bg-[#1e293b] text-accent hover:text-[#fcd34d]' : 'hover:bg-slate-100 text-slate-600 hover:text-amber-500'"
                title="Muat Ulang Halaman">
          <span class="material-symbols-outlined text-[22px]">refresh</span>
        </button>
      </div>
    </Transition>

    <!-- ═══════ HOME BUTTON (only on non-Landing pages) ═══════ -->
    <Transition name="fade">
      <button v-if="route.name !== 'Landing'"
              @click="router.push({ name: 'Landing' })"
              class="fixed right-[16px] z-40 flex items-center justify-center size-[44px] rounded-full shadow-[0_4px_15px_rgba(0,0,0,0.3)] transition-all cursor-pointer"
              style="bottom: 115px;"
              :class="isDark ? 'bg-[#0f172a] opacity-90 border border-[#1e293b] text-accent hover:opacity-100 hover:border-accent hover:shadow-[0_0_20px_rgba(251,191,36,0.3)]' : 'bg-white opacity-90 border border-slate-300 text-slate-600 hover:opacity-100 hover:border-slate-400 hover:shadow-[0_4px_20px_rgba(0,0,0,0.15)]'"
              title="Kembali ke Beranda">
        <span class="material-symbols-outlined text-[22px]">home</span>
      </button>
    </Transition>

    <!-- ═══════ CONFIG TOGGLE BUTTON ═══════ -->
    <Transition name="fade">
      <button @click="showConfigMenu = !showConfigMenu"
              class="public-theme-toggle"
              :class="[
                { 'rotate-90': showConfigMenu },
                isDark ? 'bg-[#0f172a] opacity-90 border border-[#1e293b] text-accent hover:opacity-100 hover:border-accent hover:shadow-[0_0_20px_rgba(251,191,36,0.3)]' : 'bg-white opacity-90 border border-slate-300 text-slate-600 hover:opacity-100 hover:border-slate-400 hover:shadow-[0_4px_20px_rgba(0,0,0,0.15)]'
              ]"
              title="Konfigurasi">
        <span class="material-symbols-outlined text-[22px] transition-transform duration-300"
              :class="{ 'rotate-90': showConfigMenu }">
          {{ showConfigMenu ? 'close' : 'settings' }}
        </span>
      </button>
    </Transition>

    <!-- ═══════ TICKER BAR (Footer) ═══════ -->
    <TickerBar />
  </div>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { usePublicTheme } from '../composables/usePublicTheme'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import PublicHeader from '../components/PublicHeader.vue'
import TickerBar from '../components/TickerBar.vue'

const router = useRouter()
const route = useRoute()
const { isDark, toggleTheme } = usePublicTheme()

const simplebarEl = ref(null)
watch(() => route.path, () => {
  if (simplebarEl.value && simplebarEl.value.scrollElement) {
    simplebarEl.value.scrollElement.scrollTop = 0
  }
})

// ── Config Menu State ──
const showConfigMenu = ref(false)
const showTvMenu = ref(false)

// ── Disconnect Countdown ──
const disconnectTimer = ref(null)
const disconnectCountdown = ref(0)

function initiateDisconnect() {
  disconnectCountdown.value = 3
  
  disconnectTimer.value = setInterval(() => {
    disconnectCountdown.value--
    if (disconnectCountdown.value <= 0) {
      cancelDisconnect()
      forceDisconnect()
      showTvMenu.value = false
    }
  }, 1000)
}

function cancelDisconnect() {
  if (disconnectTimer.value) {
    clearInterval(disconnectTimer.value)
    disconnectTimer.value = null
  }
  disconnectCountdown.value = 0
}

function refreshPage() {
  showConfigMenu.value = false
  window.location.reload()
}

function activateScreensaver() {
  window.dispatchEvent(new Event('screensaver-force-activate'))
}

function forceDisconnect() {
  const token = localStorage.getItem('tv_token')
  if (token) {
    const data = new Blob([JSON.stringify({ token })], { type: 'application/json' })
    navigator.sendBeacon('/api/tv/disconnect', data)
  }
  localStorage.removeItem('tv_token')
  localStorage.removeItem('tv_device')
  router.push({ name: 'ConnectToken' })
  showConfigMenu.value = false
}

// ── TV Device Name ──
const deviceName = computed(() => {
  try {
    const device = JSON.parse(localStorage.getItem('tv_device') || 'null')
    return device?.name || ''
  } catch { return '' }
})

// ── TV Unit Name ──
const unitName = computed(() => {
  try {
    const device = JSON.parse(localStorage.getItem('tv_device') || 'null')
    return device?.unit?.name || 'Semua Unit'
  } catch { return 'Semua Unit' }
})

// ── Auto-close menus on config toggle ──
watch(showConfigMenu, (val) => {
  if (!val) {
    showTvMenu.value = false
    cancelDisconnect()
  }
})

onUnmounted(() => {
  cancelDisconnect()
})
</script>

<style scoped>
/* Sleek custom scrollbar on desktop */
.public-content-scroll :deep(.simplebar-scrollbar::before) {
  background: rgba(251, 191, 36, 0.55);
  border-radius: 999px;
  opacity: 0.6;
  transition: opacity 0.3s ease, background 0.3s ease;
}
.public-content-scroll:hover :deep(.simplebar-scrollbar::before) {
  background: rgba(251, 191, 36, 0.85);
  opacity: 1;
}
.public-content-scroll :deep(.simplebar-track.simplebar-vertical) {
  width: 8px;
  right: 2px;
}
.public-content-scroll :deep(.simplebar-track.simplebar-horizontal) {
  display: none;
}

/* Hide simplebar scrollbar track on mobile (< 768px), allowing native default scroll */
@media (max-width: 767px) {
  .public-content-scroll :deep(.simplebar-track) {
    display: none !important;
  }
}
</style>
