<template>
  <div class="w-full shrink-0">
    <!-- ═══════ HEADER ═══════ -->
    <header class="flex items-center justify-between mb-2 md:mb-3 pb-2 border-b border-white/5 gap-2 shrink-0">
      <div class="flex items-center gap-2 md:gap-4 shrink-0 min-w-0">
        <div class="min-w-0">
          <img :src="unitLogoFull" alt="Access" class="h-14 md:h-20 object-contain drop-shadow-lg shrink-0" />
          <div class="flex items-center gap-2 mt-1 hidden sm:flex">
            <span class="h-px w-4 md:w-6 bg-accent/60 shrink-0"></span>
            <p class="text-accent/90 text-[10px] md:text-xs font-medium tracking-[0.2em] uppercase truncate">Portal Layanan Digital</p>
          </div>
        </div>
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

// ── Unit Logo (from TV device in localStorage) ──
const unitLogoFull = computed(() => {
  try {
    const device = JSON.parse(localStorage.getItem('tv_device') || 'null')
    if (device?.unit?.logo_full_path) {
      return `/storage/${device.unit.logo_full_path}`
    }
  } catch { /* ignore */ }
  return '/img/logo-full.png'
})

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
  

})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>
