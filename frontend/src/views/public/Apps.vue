<template>
  <div class="relative w-full h-dvh overflow-hidden">
    <!-- ═══════ BACKGROUND LAYERS ═══════ -->
    <div class="fixed inset-0 z-0 bg-gradient-to-br from-[#020617] via-[#0f172a] to-[#1e3a8a]"></div>
    <div class="fixed inset-0 z-0 opacity-15 mix-blend-overlay"
         :style="{ backgroundImage: patternBg }"></div>
    <div class="fixed inset-0 z-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.2),transparent_60%)] pointer-events-none"></div>
    <div class="fixed inset-0 z-0 bg-[radial-gradient(circle_at_bottom_left,rgba(251,191,36,0.08),transparent_50%)] pointer-events-none"></div>

    <!-- ═══════ MAIN CONTENT ═══════ -->
    <div class="relative z-10 flex flex-col h-dvh p-3 md:p-4 lg:p-6">

      <!-- ═══════ HEADER ═══════ -->
      <header class="flex flex-wrap items-center justify-between mb-2 md:mb-3 pb-2 border-b border-white/5 gap-2">
        <div class="flex items-center gap-2 md:gap-4">
          <!-- Back Button -->
          <button @click="$router.push({ name: 'Landing' })"
                  class="glass-panel flex items-center justify-center size-9 md:size-14 rounded-lg md:rounded-xl border border-white/10 hover:border-accent/50 transition-all duration-300 cursor-pointer group">
            <span class="material-symbols-outlined text-xl md:text-3xl text-slate-300 group-hover:text-accent transition-colors">arrow_back</span>
          </button>
          <div class="glass-panel flex items-center justify-center size-9 md:size-14 rounded-lg md:rounded-xl border border-accent/30 shadow-[0_0_20px_rgba(251,191,36,0.15)]">
            <span class="material-symbols-outlined text-xl md:text-3xl text-accent">apps</span>
          </div>
          <div>
            <h1 class="text-lg md:text-3xl font-serif font-bold tracking-tight text-white drop-shadow-lg">Dalwa Apps</h1>
            <div class="flex items-center gap-2">
              <span class="h-px w-4 md:w-6 bg-accent/60"></span>
              <p class="text-accent/90 text-[10px] md:text-xs font-medium tracking-[0.2em] uppercase">Portal Layanan Digital</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4">
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

      <!-- ═══════ LINK GRID ═══════ -->
      <div class="flex-1 min-h-0 overflow-y-auto no-scrollbar pb-6">
        <!-- Section Title -->
        <div class="flex items-center gap-3 mb-4 md:mb-6">
          <div class="h-px flex-1 bg-gradient-to-r from-accent/30 to-transparent"></div>
          <p class="text-slate-400 text-xs md:text-sm font-medium tracking-widest uppercase">Portal yang tersedia</p>
          <div class="h-px flex-1 bg-gradient-to-l from-accent/30 to-transparent"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 portrait:lg:grid-cols-3 gap-3 md:gap-4 lg:gap-5">
          <!-- Link Cards -->
          <a v-for="(app, idx) in apps" :key="idx"
             :href="app.url"
             target="_blank"
             rel="noopener noreferrer"
             class="group relative rounded-2xl glass-panel glass-panel-hover p-4 md:p-5 flex flex-col items-start transition-all duration-500 cursor-pointer min-h-[140px] md:min-h-[160px]"
             :style="{ animationDelay: `${idx * 60}ms` }">
            <!-- Icon -->
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center border transition-all duration-300"
                 :class="app.colorClass">
              <span class="material-symbols-outlined text-2xl md:text-3xl"
                    style="font-variation-settings: 'FILL' 1;">{{ app.icon }}</span>
            </div>

            <!-- Label -->
            <div class="mt-auto pt-3">
              <h3 class="text-sm md:text-base font-bold text-white mb-0.5 leading-snug group-hover:text-accent-light transition-colors">{{ app.title }}</h3>
              <p class="text-[11px] md:text-xs text-slate-400 line-clamp-1">{{ app.subtitle }}</p>
            </div>

            <!-- Hover Arrow -->
            <div class="absolute top-3 right-3 md:top-4 md:right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-1 group-hover:translate-x-0">
              <span class="material-symbols-outlined text-accent text-lg">open_in_new</span>
            </div>

            <!-- Glow effect on hover -->
            <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
                 :style="`background: radial-gradient(circle at 30% 30%, ${app.glowColor}, transparent 70%);`"></div>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

// ── Static Link Data ──
const apps = ref([
  {
    title: 'OPAC Perpustakaan Dalwa',
    subtitle: 'Katalog Online',
    icon: 'menu_book',
    url: '#',
    colorClass: 'bg-amber-500/15 border-amber-500/25 text-amber-400 group-hover:border-amber-400/50 group-hover:bg-amber-500/25',
    glowColor: 'rgba(245, 158, 11, 0.06)',
  },
  {
    title: 'Visitor Dalwa',
    subtitle: 'Buku Tamu Kunjungan',
    icon: 'assignment_ind',
    url: '#',
    colorClass: 'bg-blue-500/15 border-blue-500/25 text-blue-400 group-hover:border-blue-400/50 group-hover:bg-blue-500/25',
    glowColor: 'rgba(59, 130, 246, 0.06)',
  },
  {
    title: 'Perpustakaan Dalwa',
    subtitle: 'Website Resmi',
    icon: 'language',
    url: '#',
    colorClass: 'bg-emerald-500/15 border-emerald-500/25 text-emerald-400 group-hover:border-emerald-400/50 group-hover:bg-emerald-500/25',
    glowColor: 'rgba(16, 185, 129, 0.06)',
  },
  {
    title: 'Kamus Almaany',
    subtitle: 'Kamus Arab-Indonesia',
    icon: 'translate',
    url: '#',
    colorClass: 'bg-violet-500/15 border-violet-500/25 text-violet-400 group-hover:border-violet-400/50 group-hover:bg-violet-500/25',
    glowColor: 'rgba(139, 92, 246, 0.06)',
  },
  {
    title: 'Maktabah Shamela',
    subtitle: 'Perpustakaan Digital',
    icon: 'library_books',
    url: '#',
    colorClass: 'bg-rose-500/15 border-rose-500/25 text-rose-400 group-hover:border-rose-400/50 group-hover:bg-rose-500/25',
    glowColor: 'rgba(244, 63, 94, 0.06)',
  },
  {
    title: 'Turath.io',
    subtitle: 'Pencarian Teks Turath',
    icon: 'search',
    url: '#',
    colorClass: 'bg-cyan-500/15 border-cyan-500/25 text-cyan-400 group-hover:border-cyan-400/50 group-hover:bg-cyan-500/25',
    glowColor: 'rgba(6, 182, 212, 0.06)',
  },
])

// ── Time & Date ──
const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23fbbf24' fill-opacity='0.15'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`

const hours = ref('00')
const minutes = ref('00')
const currentDate = ref('')
const hijriDate = ref('')

function updateTime() {
  const now = new Date()
  hours.value = String(now.getHours()).padStart(2, '0')
  minutes.value = String(now.getMinutes()).padStart(2, '0')

  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]}`

  try {
    const hijri = new Intl.DateTimeFormat('id-u-ca-islamic', {
      day: 'numeric', month: 'long', year: 'numeric'
    }).format(now)
    hijriDate.value = hijri
  } catch {
    hijriDate.value = ''
  }
}

let timeInterval
onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
})

onUnmounted(() => {
  clearInterval(timeInterval)
})
</script>

<style scoped>
/* ═══ Card Entrance Animation ═══ */
.glass-panel-hover {
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
</style>
