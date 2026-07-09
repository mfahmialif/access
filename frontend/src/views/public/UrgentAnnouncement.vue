<template>
  <div class="fixed inset-0 z-[100] w-screen h-screen overflow-hidden font-display text-slate-100">
    <!-- ═══════ LAYERED BACKGROUND (TV-friendly, no blur) ═══════ -->
    <div class="absolute inset-0 w-full h-full z-0 overflow-hidden">
      <!-- Base deep gradient -->
      <div class="absolute inset-0 bg-gradient-to-br from-[#020c1b] via-[#0a1628] to-[#0d1f3c]"></div>
      <!-- Radial accent glow top-right -->
      <div class="absolute inset-0" style="background: radial-gradient(ellipse 60% 50% at 80% 10%, rgba(251,191,36,0.07) 0%, transparent 70%)"></div>
      <!-- Radial accent glow bottom-left -->
      <div class="absolute inset-0" style="background: radial-gradient(ellipse 50% 60% at 15% 85%, rgba(59,130,246,0.06) 0%, transparent 70%)"></div>
      <!-- Animated floating orbs -->
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
      <!-- Subtle grid pattern overlay -->
      <div class="absolute inset-0 opacity-[0.03]"
           style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 60px 60px;"></div>
      <!-- Vignette edges -->
      <div class="absolute inset-0" style="background: radial-gradient(ellipse 70% 70% at 50% 50%, transparent 40%, rgba(2,12,27,0.8) 100%)"></div>
    </div>

    <!-- ═══════ CENTERED OVERLAY ═══════ -->
    <div class="relative z-50 w-full h-full flex flex-col items-center justify-center p-6 sm:p-12">

      <!-- ═══════ MODAL CARD ═══════ -->
      <div class="w-full max-w-5xl bg-[#112240] border-2 border-accent shadow-[0_0_50px_rgba(251,191,36,0.15)] rounded-2xl overflow-hidden relative flex flex-col md:flex-row modal-enter">
        <!-- Corner glow -->
        <div class="absolute top-0 left-0 w-32 h-32 bg-linear-to-br from-accent/10 to-transparent pointer-events-none"></div>

        <!-- ═══ LEFT: BRANDING PANEL ═══ -->
        <div class="relative w-full md:w-2/5 bg-[#0a192f] flex flex-col items-center justify-center p-8 md:p-12 border-b md:border-b-0 md:border-r border-white/5">
          <div class="absolute top-4 left-4">
            <span class="material-symbols-outlined text-accent/40 text-4xl">branding_watermark</span>
          </div>
          <!-- Alert Icon Ring -->
          <div class="w-32 h-32 rounded-full bg-accent/10 flex items-center justify-center mb-6 ring-1 ring-accent/30 shadow-[0_0_20px_rgba(251,191,36,0.2)] animate-pulse-slow">
            <span class="material-symbols-outlined text-accent text-[64px]">notifications_active</span>
          </div>
          <h2 class="text-accent font-bold text-xl tracking-wider text-center uppercase mb-2">Access Alert</h2>
          <p class="text-slate-400 text-center text-sm">System Broadcast • {{ currentTime }} WIB</p>
        </div>

        <!-- ═══ RIGHT: CONTENT PANEL ═══ -->
        <div class="w-full md:w-3/5 flex flex-col relative bg-[#112240]">
          <!-- Priority Header -->
          <div class="flex items-center justify-between px-8 py-5 border-b border-white/5 bg-[#0a192f]">
            <div class="flex items-center gap-3">
              <span class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
              </span>
              <p class="text-white font-bold tracking-widest text-sm uppercase">Priority Message</p>
            </div>
            <!-- Countdown Timer -->
            <div class="flex items-center gap-2 bg-white/5 rounded-lg px-3 py-1.5 border border-accent/30 shadow-[0_0_10px_rgba(251,191,36,0.1)]">
              <span class="material-symbols-outlined text-accent text-lg">timer</span>
              <span class="text-accent font-bold font-mono text-lg leading-none">{{ countdown }}<span class="text-xs text-accent/70 font-normal ml-0.5">s</span></span>
            </div>
          </div>

          <!-- Message Content -->
          <div class="flex-1 px-8 py-8 flex flex-col justify-center">
            <div class="mb-2">
              <span class="inline-block px-2 py-1 rounded bg-accent/20 text-accent text-xs font-bold tracking-wider uppercase mb-3">{{ announcement.tag }}</span>
              <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-6">
                {{ announcement.title }}
              </h1>
            </div>
            <div class="max-w-none">
              <p class="text-lg text-slate-300 leading-relaxed font-normal" v-html="fixHtmlAssetUrls(announcement.body)"></p>
              <!-- Info Box -->
              <div v-if="announcement.infoBox" class="mt-6 p-4 bg-white/5 border-l-4 border-accent rounded-r-lg">
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
          <div class="px-8 py-5 bg-[#0a192f] border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-slate-500 text-sm">
              <span class="material-symbols-outlined text-lg">info</span>
              <span>Pengumuman akan ditutup otomatis dalam <span class="font-bold text-slate-300">{{ countdown }}</span> detik</span>
            </div>
            <button @click="dismiss"
                    class="group flex items-center justify-center gap-2 bg-accent hover:bg-yellow-400 text-[#0a192f] font-bold py-3 px-8 rounded-lg transition-all duration-200 cursor-pointer active:scale-95 shadow-[0_0_15px_rgba(251,191,36,0.3)]">
              <span>Tutup Sekarang</span>
              <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </button>
          </div>
        </div>
      </div>

      <!-- ═══════ BOTTOM STATUS BAR ═══════ -->
      <div class="absolute bottom-8 flex gap-6 text-slate-500 text-sm font-medium tracking-wide opacity-60">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-lg">rss_feed</span>
          <span>Live Feed Paused</span>
        </div>
        <div class="w-px h-5 bg-slate-700"></div>
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-lg">schedule</span>
          <span>{{ hijriDate }}</span>
        </div>
        <div class="w-px h-5 bg-slate-700"></div>
        <div>{{ currentDate }}</div>
      </div>
    </div>
  </div>
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
  router.push({ name: 'Landing' })
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
})

onUnmounted(() => {
  clearInterval(timeInterval)
  clearInterval(countdownInterval)
})
</script>

<style scoped>
/* Animated floating orbs (TV-friendly, no blur) */
.orb {
  position: absolute;
  border-radius: 50%;
  opacity: 0.08;
  pointer-events: none;
  will-change: transform;
}
.orb-1 {
  width: 400px;
  height: 400px;
  top: -10%;
  right: -5%;
  background: radial-gradient(circle, rgba(251,191,36,0.5) 0%, rgba(251,191,36,0) 70%);
  animation: orbFloat1 20s ease-in-out infinite;
}
.orb-2 {
  width: 350px;
  height: 350px;
  bottom: -8%;
  left: -5%;
  background: radial-gradient(circle, rgba(59,130,246,0.4) 0%, rgba(59,130,246,0) 70%);
  animation: orbFloat2 25s ease-in-out infinite;
}
.orb-3 {
  width: 250px;
  height: 250px;
  top: 40%;
  left: 50%;
  background: radial-gradient(circle, rgba(139,92,246,0.3) 0%, rgba(139,92,246,0) 70%);
  animation: orbFloat3 18s ease-in-out infinite;
}

@keyframes orbFloat1 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(-40px, 30px) scale(1.1); }
  66% { transform: translate(20px, -20px) scale(0.95); }
}
@keyframes orbFloat2 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -40px) scale(1.08); }
  66% { transform: translate(-20px, 15px) scale(0.92); }
}
@keyframes orbFloat3 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-35px, -25px) scale(1.15); }
}

/* Modal entrance animation */
.modal-enter {
  animation: modalIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.92) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Slow pulse for alert icon */
@keyframes subtle-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}
.animate-pulse-slow {
  animation: subtle-pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
