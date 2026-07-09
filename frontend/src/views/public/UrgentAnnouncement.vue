<template>
  <Teleport to="body">
    <div class="urgent-overlay" style="position:fixed;top:0;left:0;right:0;bottom:0;width:100vw;height:100vh;z-index:2147483647;overflow:hidden;">
      <!-- ═══════ SOLID BACKGROUND (fully opaque, no blur, no opacity) ═══════ -->
      <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:#020c1b;"></div>
      <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,#020c1b 0%,#0a1628 50%,#0d1f3c 100%);"></div>

      <!-- ═══════ CENTERED OVERLAY ═══════ -->
      <div class="relative w-full h-full flex flex-col items-center justify-center p-6 sm:p-12" style="position:relative;z-index:10;">

        <!-- ═══════ MODAL CARD ═══════ -->
        <div class="w-full max-w-5xl bg-[#112240] border-2 border-accent rounded-2xl overflow-hidden relative flex flex-col md:flex-row"
             style="box-shadow: 0 0 50px rgba(251,191,36,0.15);">

          <!-- ═══ LEFT: BRANDING PANEL ═══ -->
          <div class="relative w-full md:w-2/5 bg-[#0a192f] flex flex-col items-center justify-center p-8 md:p-12" style="border-right: 1px solid rgba(255,255,255,0.05);">
            <div class="absolute top-4 left-4">
              <span class="material-symbols-outlined text-4xl" style="color: rgba(251,191,36,0.4);">branding_watermark</span>
            </div>
            <!-- Alert Icon Ring -->
            <div class="w-32 h-32 rounded-full flex items-center justify-center mb-6 animate-pulse-slow"
                 style="background: rgba(251,191,36,0.1); box-shadow: 0 0 20px rgba(251,191,36,0.2); border: 1px solid rgba(251,191,36,0.3);">
              <span class="material-symbols-outlined text-accent text-[64px]">notifications_active</span>
            </div>
            <h2 class="text-accent font-bold text-xl tracking-wider text-center uppercase mb-2">Access Alert</h2>
            <p class="text-slate-400 text-center text-sm">System Broadcast • {{ currentTime }} WIB</p>
          </div>

          <!-- ═══ RIGHT: CONTENT PANEL ═══ -->
          <div class="w-full md:w-3/5 flex flex-col relative bg-[#112240]">
            <!-- Priority Header -->
            <div class="flex items-center justify-between px-8 py-5 bg-[#0a192f]" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
              <div class="flex items-center gap-3">
                <span class="flex h-3 w-3 relative">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500" style="opacity:0.75;"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                </span>
                <p class="text-white font-bold tracking-widest text-sm uppercase">Priority Message</p>
              </div>
              <!-- Countdown Timer -->
              <div class="flex items-center gap-2 rounded-lg px-3 py-1.5" style="background:rgba(255,255,255,0.05); border:1px solid rgba(251,191,36,0.3);">
                <span class="material-symbols-outlined text-accent text-lg">timer</span>
                <span class="text-accent font-bold font-mono text-lg leading-none">{{ countdown }}<span class="text-xs font-normal ml-0.5" style="color:rgba(251,191,36,0.7);">s</span></span>
              </div>
            </div>

            <!-- Message Content -->
            <div class="flex-1 px-8 py-8 flex flex-col justify-center">
              <div class="mb-2">
                <span class="inline-block px-2 py-1 rounded text-accent text-xs font-bold tracking-wider uppercase mb-3" style="background: rgba(251,191,36,0.2);">{{ announcement.tag }}</span>
                <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-6">
                  {{ announcement.title }}
                </h1>
              </div>
              <div class="max-w-none">
                <p class="text-lg text-slate-300 leading-relaxed font-normal" v-html="fixHtmlAssetUrls(announcement.body)"></p>
                <!-- Info Box -->
                <div v-if="announcement.infoBox" class="mt-6 p-4 rounded-r-lg" style="background: rgba(255,255,255,0.05); border-left: 4px solid var(--color-accent);">
                  <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-accent mt-0.5">{{ announcement.infoBox.icon }}</span>
                    <div>
                      <p class="text-white font-medium">{{ announcement.infoBox.title }}</p>
                      <p class="text-slate-400 text-sm" v-html="fixHtmlAssetUrls(announcement.infoBox.description)"></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-5 bg-[#0a192f] flex flex-col sm:flex-row items-center justify-between gap-4" style="border-top: 1px solid rgba(255,255,255,0.05);">
              <div class="flex items-center gap-2 text-slate-500 text-sm">
                <span class="material-symbols-outlined text-lg">info</span>
                <span>Pengumuman akan ditutup otomatis dalam <span class="font-bold text-slate-300">{{ countdown }}</span> detik</span>
              </div>
              <button @click="dismiss"
                      class="group flex items-center justify-center gap-2 bg-accent hover:bg-yellow-400 text-[#0a192f] font-bold py-3 px-8 rounded-lg transition-all duration-200 cursor-pointer active:scale-95"
                      style="box-shadow: 0 0 15px rgba(251,191,36,0.3);">
                <span>Tutup Sekarang</span>
                <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ═══════ BOTTOM STATUS BAR ═══════ -->
        <div class="absolute bottom-8 flex gap-6 text-slate-500 text-sm font-medium tracking-wide" style="opacity:0.6;">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">rss_feed</span>
            <span>Live Feed Paused</span>
          </div>
          <div style="width:1px;height:20px;background:#334155;"></div>
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">schedule</span>
            <span>{{ hijriDate }}</span>
          </div>
          <div style="width:1px;height:20px;background:#334155;"></div>
          <div>{{ currentDate }}</div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { fixHtmlAssetUrls } from '../../utils/asset'

const router = useRouter()

// ── Time & Date ──
const currentTime = ref('')
const currentDate = ref('')
const hijriDate = ref('')

function updateTime() {
  const now = new Date()
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
  try {
    hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    }).format(now)
  } catch { hijriDate.value = '' }
}

// ── Countdown Timer ──
const countdown = ref(30)
let countdownInterval

function startCountdown() {
  countdownInterval = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value--
    } else {
      clearInterval(countdownInterval)
      dismiss()
    }
  }, 1000)
}

// ── Dismiss ──
function dismiss() {
  restoreHiddenElements()
  router.push({ name: 'Landing' })
}

// ── Hide all competing elements (iframes, other teleports) ──
let hiddenElements = []

function hideCompetingElements() {
  // Hide any iframes on the page (like Apps embed)
  document.querySelectorAll('iframe').forEach(el => {
    if (el.style.display !== 'none') {
      hiddenElements.push({ el, prevDisplay: el.style.display })
      el.style.display = 'none'
    }
  })
  // Hide any other fixed overlays with high z-index (like Apps teleport)
  document.querySelectorAll('[class*="z-[9999"]').forEach(el => {
    if (!el.classList.contains('urgent-overlay') && el.style.display !== 'none') {
      hiddenElements.push({ el, prevDisplay: el.style.display })
      el.style.display = 'none'
    }
  })
}

function restoreHiddenElements() {
  hiddenElements.forEach(({ el, prevDisplay }) => {
    el.style.display = prevDisplay
  })
  hiddenElements = []
}

// ── Announcement Data ──
const announcement = ref({
  tag: 'Perubahan Jadwal',
  title: 'Sholat Maghrib Ditunda',
  body: 'Diinformasikan bahwa jamaah sholat Maghrib akan <span class="text-white font-semibold">dimulai 15 menit lebih lambat dari jadwal</span> karena adanya pemeliharaan teknis di aula utama masjid.',
  infoBox: {
    icon: 'meeting_room',
    title: 'Lokasi Baru',
    description: 'Silakan berkumpul di <span class="text-white">Halaman Tengah Pesantren</span> segera.'
  }
})

// ── Lifecycle ──
let timeInterval
onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
  startCountdown()
  // Force hide iframes and competing overlays
  hideCompetingElements()
})

onUnmounted(() => {
  clearInterval(timeInterval)
  clearInterval(countdownInterval)
  restoreHiddenElements()
})
</script>

<style scoped>
/* Slow pulse for alert icon */
@keyframes subtle-pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}
.animate-pulse-slow {
  animation: subtle-pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
