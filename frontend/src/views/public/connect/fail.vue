<template>
  <div class="relative w-screen h-screen overflow-hidden font-display text-slate-100 bg-[#020c1b]">
    <!-- ═══════ PATTERN BACKGROUND ═══════ -->
    <div class="absolute inset-0 z-0 bg-pattern opacity-60 pointer-events-none"></div>
    <!-- Amber ambient glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80vw] h-[80vw] bg-accent/10 rounded-full blur-[120px] pointer-events-none animate-pulse-slow"></div>

    <!-- ═══════ CONTENT ═══════ -->
    <main class="relative z-10 flex flex-col items-center justify-center w-full h-full max-w-5xl mx-auto px-4 md:px-6 py-8 md:py-12 text-center overflow-y-auto">

      <!-- X Icon -->
      <div class="mb-6 md:mb-10 scale-in relative">
        <div class="absolute inset-0 bg-accent/30 blur-2xl rounded-full scale-110"></div>
        <div class="relative w-24 h-24 md:w-40 md:h-40 rounded-full bg-linear-to-br from-accent to-yellow-500 flex items-center justify-center shadow-[0_0_60px_-10px_rgba(251,191,36,0.6)] border-4 border-[#1e1b04]">
          <span class="material-symbols-outlined text-[#020c1b] text-[48px] md:text-[80px] font-bold">close</span>
        </div>
        <!-- Progress ring -->
        <svg class="absolute top-0 left-0 w-24 h-24 md:w-40 md:h-40 -rotate-90 pointer-events-none">
          <circle class="opacity-20" cx="50%" cy="50%" fill="transparent" r="48%" stroke="white" stroke-dasharray="251.2" stroke-dashoffset="50" stroke-width="2"></circle>
          <circle class="progress-ring" cx="50%" cy="50%" fill="transparent" r="48%" stroke="#fbbf24" stroke-dasharray="251.2" stroke-dashoffset="0" stroke-linecap="round" stroke-width="4"></circle>
        </svg>
      </div>

      <!-- Text Content -->
      <div class="flex flex-col items-center gap-6 fade-up">
        <div class="space-y-2">
          <h1 class="text-3xl md:text-6xl font-extrabold tracking-tight text-white drop-shadow-lg">
            Koneksi Gagal
          </h1>
          <p class="text-base md:text-2xl text-slate-300 font-medium">
            Mohon periksa kabel LAN atau token Anda.
          </p>
        </div>

        <!-- Status Card -->
        <div class="mt-4 flex flex-col md:flex-row items-center gap-4 md:gap-8 bg-[#0a192f]/60 backdrop-blur-md border border-white/10 px-5 md:px-8 py-4 md:py-5 rounded-xl shadow-2xl">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-white/5 rounded-full">
              <span class="material-symbols-outlined text-[#ff4500] text-2xl">wifi_off</span>
            </div>
            <div class="text-left">
              <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Status</p>
              <p class="text-lg md:text-xl font-bold text-[#ff4500]">Tidak Terhubung</p>
            </div>
          </div>
        </div>

        <!-- CTA Button -->
        <div class="mt-10 flex flex-col items-center gap-4">
          <button @click="retry"
                  class="group relative flex items-center gap-3 bg-accent hover:bg-yellow-400 active:scale-95 transition-all duration-200 text-[#020c1b] font-bold text-base md:text-xl px-8 md:px-10 py-3 md:py-4 rounded-full shadow-[0_0_30px_-5px_rgba(251,191,36,0.5)] overflow-hidden cursor-pointer">
            <span class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-500">refresh</span>
            <span>Coba Lagi</span>
            <div class="absolute inset-0 -translate-x-full group-hover:animate-shimmer bg-linear-to-r from-transparent via-white/20 to-transparent skew-x-12"></div>
          </button>
        </div>
      </div>
    </main>

    <!-- Corner vignettes -->
    <div class="absolute bottom-0 right-0 w-64 h-64 bg-linear-to-tl from-[#020c1b]/80 to-transparent pointer-events-none"></div>
    <div class="absolute top-0 left-0 w-64 h-64 bg-linear-to-br from-[#020c1b]/80 to-transparent pointer-events-none"></div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function retry() {
  router.push({ name: 'ConnectToken' })
}
</script>

<style scoped>
.bg-pattern {
  background-image: radial-gradient(circle at center, transparent 0%, #020c1b 100%),
    url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230a192f' fill-opacity='0.4'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.scale-in { animation: scaleIn 0.5s ease-out forwards; }
.fade-up { animation: fadeUp 0.8s ease-out 0.2s forwards; opacity: 0; }

.progress-ring { transition: stroke-dashoffset 5s linear; }

@keyframes scaleIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
@keyframes fadeUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.animate-shimmer { animation: shimmer 1.5s infinite; }
@keyframes shimmer {
  from { transform: translateX(-100%) skewX(12deg); }
  to { transform: translateX(200%) skewX(12deg); }
}

.animate-pulse-slow { animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
</style>
