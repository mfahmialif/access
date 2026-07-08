<template>
  <div class="w-full shrink-0">
    <!-- ═══════ HEADER ═══════ -->
    <header class="flex items-center justify-between mb-2 md:mb-3 pb-2 border-b border-white/5 gap-2 shrink-0">
      <div class="flex items-center gap-2 md:gap-4 shrink-0 min-w-0">
        <div class="min-w-0">
          <img src="/img/logo-full.png" alt="Access" class="h-14 md:h-20 object-contain drop-shadow-lg shrink-0" />
          <div class="flex items-center gap-2 mt-1 hidden sm:flex">
            <span class="h-px w-4 md:w-6 bg-accent/60 shrink-0"></span>
            <p class="text-accent/90 text-[10px] md:text-xs font-medium tracking-[0.2em] uppercase truncate">Portal Layanan Digital</p>
          </div>
        </div>
      </div>

      <!-- Device Name Badge -->
      <div v-if="deviceName" class="relative order-3 md:order-none w-full md:w-auto flex justify-center md:block">
        <button @click="showDeviceMenu = !showDeviceMenu"
                class="flex items-center gap-1.5 md:gap-2 glass-panel px-3 md:px-4 py-1 md:py-1.5 rounded-full border border-green-500/20 cursor-pointer hover:border-green-500/40 transition-all max-w-full">
          <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
          </span>
          <span class="material-symbols-outlined text-green-400 text-sm md:text-base shrink-0">tv</span>
          <span class="text-green-300 text-xs md:text-sm font-medium truncate max-w-[140px] md:max-w-[220px]">{{ deviceName }}</span>
          <span class="material-symbols-outlined text-green-400/60 text-sm transition-transform shrink-0" :class="{ 'rotate-180': showDeviceMenu }">expand_more</span>
        </button>
        <!-- Dropdown -->
        <Transition name="fade">
          <div v-if="showDeviceMenu" class="absolute left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-0 top-full mt-2 z-50 w-52 rounded-xl overflow-hidden shadow-2xl border border-red-500/20" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px)">
            <button @click="disconnectTv" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-400 hover:bg-red-500/10 cursor-pointer transition-colors">
              <span class="material-symbols-outlined text-[18px]">link_off</span>
              Putuskan Sambungan
            </button>
          </div>
        </Transition>
      </div>

      <div class="flex items-center gap-2 md:gap-4 shrink-0">
        <div class="self-end">
          <div class="hidden md:flex items-center gap-3 text-sm font-light text-slate-200 glass-panel px-4 py-1.5 rounded-full border border-white/10">
            <span class="font-medium">{{ currentDate }}</span>
            <span class="w-1.5 h-1.5 bg-accent rounded-full"></span>
            <span class="text-accent-light font-serif italic">{{ hijriDate }}</span>
          </div>
        </div>
        <div class="text-2xl md:text-5xl font-serif font-bold text-white tracking-tight leading-none text-glow">
          {{ hours }}<span class="animate-pulse text-accent">:</span>{{ minutes }}
        </div>
      </div>
    </header>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ── Device Name & TV Controls ──
const deviceName = computed(() => {
  try {
    const device = JSON.parse(localStorage.getItem('tv_device') || 'null')
    return device?.name || ''
  } catch { return '' }
})

const showDeviceMenu = ref(false)

function disconnectTv() {
  const token = localStorage.getItem('tv_token')
  if (token) {
    const data = new Blob([JSON.stringify({ token })], { type: 'application/json' })
    navigator.sendBeacon('/api/tv/disconnect', data)
  }
  localStorage.removeItem('tv_token')
  localStorage.removeItem('tv_device')
  showDeviceMenu.value = false
  router.push({ name: 'ConnectToken' })
}

// ── Time & Date Logic ──
const hours = ref('00')
const minutes = ref('00')
const currentDate = ref('')
const hijriDate = ref('')
let timer = null

function updateTime() {
  const now = new Date()
  hours.value = String(now.getHours()).padStart(2, '0')
  minutes.value = String(now.getMinutes()).padStart(2, '0')

  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`

  try {
    const formatter = new Intl.DateTimeFormat('en-US-u-ca-islamic', {
      day: 'numeric', month: 'numeric', year: 'numeric'
    })
    
    // Apply Hijri adjustment (in milliseconds)
    const hijriNow = new Date(now.getTime() + (hijriAdjustment.value * 24 * 60 * 60 * 1000))
    const parts = formatter.formatToParts(hijriNow)
    
    const hijriMonths = [
      'Muharam', 'Safar', 'Rabiul Awal', 'Rabiul Akhir', 'Jumadil Awal', 'Jumadil Akhir',
      'Rajab', 'Syakban', 'Ramadan', 'Syawal', 'Zulkaidah', 'Zulhijah'
    ]
    
    let d = '', m = '', y = ''
    for (const p of parts) {
      if (p.type === 'day') d = p.value
      if (p.type === 'month') {
        const mIndex = parseInt(p.value, 10) - 1
        m = hijriMonths[mIndex] || p.value
      }
      if (p.type === 'year') y = p.value
    }
    
    if (d && m && y) {
      hijriDate.value = `${d} ${m} ${y} H`
    } else {
      hijriDate.value = ''
    }
  } catch {
    hijriDate.value = ''
  }
}

const hijriAdjustment = ref(0)

function fetchSettings() {
  const token = localStorage.getItem('tv_token')
  // We can fetch from public settings API if available, or just standard /settings
  // Wait, PublicHeader is used on TV (has /api/tv/...) and also maybe logged-in user?
  // Let's use the standard axios instance to get /settings/hijri_adjustment
  // Import axios first
  fetch('/api/settings/hijri_adjustment', {
    headers: { 'Accept': 'application/json' }
  }).then(res => res.json()).then(data => {
    if (data?.value !== undefined) {
      hijriAdjustment.value = parseInt(data.value) || 0
      updateTime() // Force update right after getting adjustment
    }
  }).catch(() => {})
}

onMounted(() => {
  fetchSettings()
  updateTime()
  timer = setInterval(updateTime, 1000)
  
  // Close TV menu if clicked outside
  document.addEventListener('click', (e) => {
    if (showDeviceMenu.value && !e.target.closest('.relative.order-3')) {
      showDeviceMenu.value = false
    }
  })
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
