<template>
  <div class="w-full min-h-full flex flex-col">
    <!-- ═══════ BACKGROUND LAYERS ═══════ -->
      <div class="fixed inset-0 z-[-1] transform-gpu pointer-events-none" style="will-change: transform;">
        <div class="absolute inset-0 bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#1e3a8a]"></div>
        <div class="absolute inset-0 opacity-15 mix-blend-overlay"
             :style="{ backgroundImage: patternBg }"></div>
        <div class="absolute inset-0 opacity-30 bg-cover bg-center mix-blend-overlay blur-sm"
             style="background-image: url('/img/background.jpg')"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.2),transparent_60%)] pointer-events-none"></div>
      </div>

    <!-- ═══════ MAIN CONTENT (Grid View) ═══════ -->
    <div v-show="!activeApp" class="relative z-10 flex flex-col flex-1 w-full">

      <!-- ═══════ LINK GRID ═══════ -->
      <div class="flex-1 pb-12">
        <!-- Section Title -->
        <div class="flex items-center gap-3 mb-4 md:mb-6">
          <div class="h-px flex-1 bg-gradient-to-r from-accent/30 to-transparent"></div>
          <p class="text-slate-400 text-xs md:text-sm font-medium tracking-widest uppercase">Portal yang tersedia</p>
          <div class="h-px flex-1 bg-gradient-to-l from-accent/30 to-transparent"></div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 portrait:lg:grid-cols-2 gap-3 md:gap-4 lg:gap-5 portrait:gap-5">
          <div v-for="i in 6" :key="i" class="rounded-2xl glass-panel p-4 md:p-5 portrait:p-7 flex flex-col items-start min-h-[140px] md:min-h-[160px] portrait:min-h-[220px]">
            <div class="skel w-10 h-10 md:w-12 md:h-12 portrait:w-16 portrait:h-16 rounded-xl mb-4"></div>
            <div class="mt-auto w-full">
              <div class="skel h-4 w-3/4 rounded mb-1.5"></div>
              <div class="skel h-3 w-1/2 rounded"></div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="apps.length === 0" class="flex flex-col items-center justify-center py-20">
          <span class="material-symbols-outlined text-slate-600 text-6xl mb-4">apps</span>
          <p class="text-slate-400 text-lg">Belum ada portal tersedia</p>
        </div>

        <!-- Grid Cards -->
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 portrait:lg:grid-cols-2 gap-3 md:gap-4 lg:gap-5 portrait:gap-5">
          <button v-for="(app, idx) in apps" :key="app.id"
             @click="openEmbed(app)"
             class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-5 portrait:p-7 flex flex-col items-start transition-all duration-500 cursor-pointer min-h-[140px] md:min-h-[160px] portrait:min-h-[220px] card-animate text-left"
             :style="{ animationDelay: `${idx * 60}ms` }">
            <!-- Icon -->
            <div class="w-10 h-10 md:w-12 md:h-12 portrait:w-16 portrait:h-16 rounded-xl portrait:rounded-2xl flex items-center justify-center border transition-all duration-300"
                 :class="colorClasses(app.color)">
              <span class="material-symbols-outlined text-2xl md:text-3xl portrait:text-4xl"
                    style="font-variation-settings: 'FILL' 1;">{{ app.icon }}</span>
            </div>

            <!-- Label -->
            <div class="mt-auto pt-3">
              <h3 class="text-sm md:text-base portrait:text-xl font-bold text-white mb-0.5 portrait:mb-1 leading-snug group-hover:text-accent-light transition-colors">{{ app.title }}</h3>
              <p class="text-[11px] md:text-xs portrait:text-sm text-slate-400 line-clamp-1">{{ app.subtitle }}</p>
            </div>

            <!-- Hover Arrow -->
            <div class="absolute top-3 right-3 md:top-4 md:right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-1 group-hover:translate-x-0">
              <span class="material-symbols-outlined text-accent text-lg">arrow_right_alt</span>
            </div>

            <!-- Glow effect on hover -->
            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                 :style="`background: radial-gradient(circle at 30% 30%, ${glowColor(app.color)}, transparent 70%);`"></div>
          </button>
        </div>
      </div>
    </div>

    <!-- ═══════ EMBED VIEWER (Fullscreen) ═══════ -->
    <Teleport to="body">
      <div v-if="activeApp" class="fixed inset-0 z-[9999] flex flex-col bg-[#020617]">

        <!-- ── Iframe Area — z-index HIGHER than toolbar ── -->
        <div class="relative flex-1 min-h-0 z-20">
          <iframe v-if="embedUrl"
                  ref="iframeRef"
                  :src="embedUrl"
                  @load="onIframeLoad"
                  allow="clipboard-read; clipboard-write"
                  referrerpolicy="no-referrer"
                  scrolling="yes"
                  class="absolute inset-0 w-full h-full border-none block"></iframe>

          <!-- Loading indicator -->
          <div v-if="iframeLoading"
               class="absolute inset-0 flex flex-col items-center justify-center bg-[#020617] z-30 pointer-events-none">
            <div class="relative w-16 h-16 flex items-center justify-center">
              <div class="absolute inset-0 rounded-full border-4 border-accent/20 border-t-accent animate-spin"></div>
              <span class="material-symbols-outlined text-accent text-2xl relative z-10"
                    style="font-variation-settings: 'FILL' 1;">{{ activeApp.icon }}</span>
            </div>
            <p class="text-slate-400 mt-4 text-sm font-medium">Memuat {{ activeApp.title }}…</p>
          </div>
        </div>

        <!-- ── Bottom Toolbar — z-index dynamic: low when closed, high when panel open ── -->
        <div class="shrink-0 relative" :class="dockExpanded ? 'z-30' : 'z-10'" :style="{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }">

          <!-- App Switcher Panel (slides up from toolbar) -->
          <transition name="slide-up">
            <div v-if="dockExpanded"
                 class="app-switcher-panel absolute bottom-full right-0 left-0 mx-2 mb-2 z-50 flex flex-col gap-1 p-2 rounded-2xl border backdrop-blur-xl shadow-2xl max-h-[50vh] overflow-y-auto no-scrollbar"
                 :class="isDark ? 'border-white/10 bg-[#0a1128]/95' : 'border-slate-200 bg-white/95'">

              <!-- App List -->
              <button @click="closeEmbed"
                      class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 cursor-pointer w-full text-left mb-1"
                      :class="isDark ? 'hover:bg-white/10 border border-transparent' : 'hover:bg-slate-50 border border-transparent'">
                <div class="size-9 rounded-full flex items-center justify-center shrink-0 transition-all"
                     :class="isDark ? 'bg-white/10 text-slate-300 group-hover:bg-white/15' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'">
                  <span class="material-symbols-outlined text-lg">home</span>
                </div>
                <div class="text-left min-w-0">
                  <p class="text-sm font-semibold truncate" :class="isDark ? 'text-slate-200' : 'text-slate-700'">Kembali ke Portal Apps</p>
                </div>
              </button>
              
              <button v-for="app in apps" :key="'bar-' + app.id"
                      @click="switchApp(app); dockExpanded = false"
                      class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 cursor-pointer w-full text-left"
                      :class="activeApp?.id === app.id
                        ? (isDark ? 'bg-accent/15 border border-accent/30' : 'bg-amber-50 border border-amber-300')
                        : (isDark ? 'hover:bg-white/10 border border-transparent' : 'hover:bg-slate-50 border border-transparent')">
                <div class="size-9 rounded-full flex items-center justify-center shrink-0 transition-all"
                     :class="activeApp?.id === app.id
                       ? (isDark ? 'bg-accent text-[#0a1128] shadow-[0_0_12px_rgba(251,191,36,0.4)]' : 'bg-amber-500 text-white shadow-sm')
                       : (isDark ? 'bg-white/10 text-slate-300 group-hover:bg-white/15' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200')">
                  <span class="material-symbols-outlined text-lg"
                        style="font-variation-settings: 'FILL' 1;">{{ app.icon }}</span>
                </div>
                <div class="text-left min-w-0">
                  <p class="text-sm font-semibold truncate" :class="activeApp?.id === app.id ? (isDark ? 'text-accent' : 'text-amber-600') : (isDark ? 'text-slate-200' : 'text-slate-700')">{{ app.title }}</p>
                  <p class="text-[11px] truncate" :class="isDark ? 'text-slate-500' : 'text-slate-400'" v-if="app.subtitle">{{ app.subtitle }}</p>
                </div>
                <span v-if="activeApp?.id === app.id" class="material-symbols-outlined text-base ml-auto shrink-0" :class="isDark ? 'text-accent' : 'text-amber-500'">check_circle</span>
              </button>
            </div>
          </transition>

          <!-- Toolbar Bar -->
          <div class="flex items-center h-12 md:h-14 px-2 md:px-3 gap-1 border-t backdrop-blur-xl"
               :class="isDark ? 'bg-gradient-to-t from-[#0a1128]/98 to-[#0a1128]/92 border-white/10' : 'bg-white/95 border-slate-200'">

            <!-- History Nav -->
            <button @click="goIframeBack"
                    class="flex items-center justify-center size-10 rounded-xl transition-all cursor-pointer shrink-0"
                    :class="isDark ? 'hover:bg-white/10 active:bg-white/15 text-slate-300 hover:text-accent' : 'hover:bg-slate-100 active:bg-slate-200 text-slate-500 hover:text-amber-500'"
                    title="Kembali (Back)">
              <span class="material-symbols-outlined text-xl">arrow_back</span>
            </button>
            <button @click="goIframeForward"
                    class="flex items-center justify-center size-10 rounded-xl transition-all cursor-pointer shrink-0"
                    :class="isDark ? 'hover:bg-white/10 active:bg-white/15 text-slate-300 hover:text-accent' : 'hover:bg-slate-100 active:bg-slate-200 text-slate-500 hover:text-amber-500'"
                    title="Maju (Forward)">
              <span class="material-symbols-outlined text-xl">arrow_forward</span>
            </button>

            <!-- Refresh -->
            <button @click="refreshIframe"
                    class="flex items-center justify-center size-10 rounded-xl transition-all cursor-pointer shrink-0"
                    :class="isDark ? 'hover:bg-white/10 active:bg-white/15 text-slate-300 hover:text-accent' : 'hover:bg-slate-100 active:bg-slate-200 text-slate-500 hover:text-amber-500'">
              <span class="material-symbols-outlined text-xl">refresh</span>
            </button>

            <!-- Active App Indicator (center) — tap to open switcher -->
            <button @click="dockExpanded = !dockExpanded"
                    class="flex-1 flex items-center justify-center gap-2 min-w-0 px-2 py-1.5 rounded-xl transition-all cursor-pointer group"
                    :class="isDark ? 'hover:bg-white/10 active:bg-white/15' : 'hover:bg-slate-100 active:bg-slate-200'">
              <div class="size-7 rounded-lg flex items-center justify-center shrink-0 transition-all"
                   :class="colorClasses(activeApp.color)">
                <span class="material-symbols-outlined text-sm"
                      style="font-variation-settings: 'FILL' 1;">{{ activeApp.icon }}</span>
              </div>
              <p class="text-sm font-semibold truncate transition-colors" :class="isDark ? 'text-white group-hover:text-accent-light' : 'text-slate-800 group-hover:text-amber-600'">{{ activeApp.title }}</p>
              <span v-if="isProxied" class="material-symbols-outlined text-sm shrink-0" :class="isDark ? 'text-amber-400/70' : 'text-amber-500'" title="Dimuat melalui proxy">swap_horiz</span>
              <span class="material-symbols-outlined text-lg shrink-0 transition-transform duration-200"
                    :class="[dockExpanded ? 'rotate-180' : '', isDark ? 'text-slate-400' : 'text-slate-500']">expand_less</span>
            </button>
          </div>
        </div>

        <!-- Backdrop (only when panel is open) -->
        <transition name="fade">
          <div v-if="dockExpanded"
               class="absolute inset-0 bg-black/20 z-[5]"
               @click="dockExpanded = false"></div>
        </transition>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../axios'
import { usePublicTheme } from '../../composables/usePublicTheme'

const { isDark } = usePublicTheme()
const route = useRoute()

// ── API Data ──
const loading = ref(true)
const apps = ref([])

async function fetchApps() {
  loading.value = true
  try {
    const { data } = await api.get('/app-links', { params: { per_page: 50, status: 'Published' } })
    apps.value = data.data || data || []
  } catch {
    apps.value = []
  } finally {
    loading.value = false
  }
}

// ── Embed State ──
const activeApp = ref(null)
const iframeLoading = ref(false)
const dockExpanded = ref(false)
const isProxied = ref(false)
const iframeRef = ref(null)

function goIframeBack() {
  try {
    window.history.back()
  } catch(e) {
    console.warn("History block:", e)
  }
}

function goIframeForward() {
  try {
    window.history.forward()
  } catch(e) {
    console.warn("History block:", e)
  }
}

// Cache proxy check results per URL so we don't re-check every time
const proxyCache = new Map()

// The actual URL the iframe will load
const embedUrl = ref('')

/**
 * Check if a URL needs proxy (via backend HEAD request), with caching.
 * Returns true if proxy is needed.
 */
async function checkNeedsProxy(url) {
  if (proxyCache.has(url)) return proxyCache.get(url)
  try {
    const { data } = await api.get('/proxy/check', { params: { url } })
    const needs = !!data.needs_proxy
    proxyCache.set(url, needs)
    return needs
  } catch {
    proxyCache.set(url, false)
    return false
  }
}

/**
 * Resolve the embed URL: direct if embeddable, proxy if blocked.
 */
async function resolveEmbedUrl(url) {
  const needsProxy = await checkNeedsProxy(url)
  isProxied.value = needsProxy
  embedUrl.value = needsProxy
    ? `/api/proxy?url=${encodeURIComponent(url)}`
    : url
    
  // Hide loader early so user doesn't have to wait for the entire page to finish loading
  setTimeout(() => {
    iframeLoading.value = false
  }, 1500)
}

async function openEmbed(app) {
  activeApp.value = app
  iframeLoading.value = true
  dockExpanded.value = false
  isProxied.value = false
  embedUrl.value = ''
  history.pushState({ embed: true, appId: app.id }, '', '')
  await resolveEmbedUrl(app.url)
}

function onIframeLoad(e) {
  if (!embedUrl.value) return
  iframeLoading.value = false
  e.target.focus()
}

function refreshIframe() {
  const url = embedUrl.value
  if (!url) return
  iframeLoading.value = true
  embedUrl.value = ''
  // Next tick — re-set the src so the iframe actually reloads
  setTimeout(() => { 
    embedUrl.value = url 
    // Hide loader early
    setTimeout(() => {
      iframeLoading.value = false
    }, 1500)
  }, 50)
}

function closeEmbed() {
  activeApp.value = null
  iframeLoading.value = false
  dockExpanded.value = false
  isProxied.value = false
  embedUrl.value = ''
}

async function switchApp(app) {
  if (activeApp.value?.id === app.id) return
  iframeLoading.value = true
  dockExpanded.value = false
  isProxied.value = false
  embedUrl.value = ''
  activeApp.value = app
  await resolveEmbedUrl(app.url)
}

// Handle browser back button
function onPopState(e) {
  if (activeApp.value) {
    closeEmbed()
  }
}

// ── Color Mapping ──
const colorMapping = {
  amber: {
    classes: 'bg-amber-500/15 border-amber-500/25 text-amber-400 group-hover:border-amber-400/50 group-hover:bg-amber-500/25',
    glow: 'rgba(245, 158, 11, 0.06)',
  },
  blue: {
    classes: 'bg-blue-500/15 border-blue-500/25 text-blue-400 group-hover:border-blue-400/50 group-hover:bg-blue-500/25',
    glow: 'rgba(59, 130, 246, 0.06)',
  },
  emerald: {
    classes: 'bg-emerald-500/15 border-emerald-500/25 text-emerald-400 group-hover:border-emerald-400/50 group-hover:bg-emerald-500/25',
    glow: 'rgba(16, 185, 129, 0.06)',
  },
  violet: {
    classes: 'bg-violet-500/15 border-violet-500/25 text-violet-400 group-hover:border-violet-400/50 group-hover:bg-violet-500/25',
    glow: 'rgba(139, 92, 246, 0.06)',
  },
  rose: {
    classes: 'bg-rose-500/15 border-rose-500/25 text-rose-400 group-hover:border-rose-400/50 group-hover:bg-rose-500/25',
    glow: 'rgba(244, 63, 94, 0.06)',
  },
  cyan: {
    classes: 'bg-cyan-500/15 border-cyan-500/25 text-cyan-400 group-hover:border-cyan-400/50 group-hover:bg-cyan-500/25',
    glow: 'rgba(6, 182, 212, 0.06)',
  },
}

function colorClasses(c) { return colorMapping[c]?.classes || colorMapping.amber.classes }
function glowColor(c) { return colorMapping[c]?.glow || colorMapping.amber.glow }

onMounted(async () => {
  await fetchApps()
  window.addEventListener('popstate', onPopState)
  // Auto-open app if ?open=<id> is in query
  autoOpenFromQuery()
})

function autoOpenFromQuery() {
  const openId = route.query.open
  if (openId && apps.value.length) {
    const target = apps.value.find(a => String(a.id) === String(openId))
    if (target) openEmbed(target)
  }
}

// Watch for route query changes (e.g. push command changes the URL)
watch(() => route.query.open, (newId) => {
  if (newId && apps.value.length) {
    const target = apps.value.find(a => String(a.id) === String(newId))
    if (target) {
      openEmbed(target)
    } else {
      closeEmbed()
    }
  } else {
    closeEmbed()
  }
})

onUnmounted(() => {
  window.removeEventListener('popstate', onPopState)
  closeEmbed()
})
</script>

<style scoped>
/* ═══ Card Entrance Animation ═══ */
.card-animate {
  animation: card-fade-in 0.4s ease-out both;
}

@keyframes card-fade-in {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ═══ Skeleton Loading ═══ */
.skel {
  background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
  background-size: 200% 100%;
  animation: skel-shimmer 1.5s ease-in-out infinite;
}
@keyframes skel-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ═══ Embed Toolbar ═══ */

.app-switcher-panel {
  box-shadow:
    0 -8px 40px rgba(0, 0, 0, 0.5),
    0 0 0 1px rgba(255, 255, 255, 0.05),
    inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

/* ═══ Slide-up transition ═══ */
.slide-up-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-leave-active { transition: all 0.2s cubic-bezier(0.4, 0, 1, 1); }
.slide-up-enter-from, .slide-up-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* ═══ Fade transition ═══ */
.fade-enter-active { transition: opacity 0.25s ease; }
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ═══ Hide scrollbar ═══ */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
