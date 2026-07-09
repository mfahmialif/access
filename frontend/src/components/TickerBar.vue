<template>
  <div class="fixed bottom-0 left-0 w-full z-50 h-10 md:h-12 flex items-center">
    <div class="absolute inset-0 bg-[#020617] opacity-90 border-t border-[#1e293b]"></div>
    <div class="relative bg-accent h-full px-3 md:px-8 flex items-center justify-center shrink-0 z-20 shadow-[5px_0_20px_rgba(0,0,0,0.5)]">
      <span class="text-[#0f172a] font-bold uppercase tracking-widest text-[10px] md:text-sm flex items-center gap-1.5 md:gap-2">
        <span class="material-symbols-outlined animate-pulse text-base md:text-2xl">campaign</span>
        <span class="hidden sm:inline">Access</span>
        <span class="sm:hidden">Access</span>
      </span>
      <div class="absolute right-[-10px] top-0 bottom-0 w-0 h-0 border-t-[40px] md:border-t-[56px] border-t-accent border-r-[14px] md:border-r-[20px] border-r-transparent"></div>
    </div>
    <div class="relative flex overflow-x-hidden flex-1 h-full items-center pl-5 md:pl-8">
      <div class="animate-marquee whitespace-nowrap flex items-center gap-10 md:gap-24">
        <span v-for="i in 3" :key="i"
              class="text-slate-200 text-sm md:text-lg font-light tracking-wide flex items-center gap-2 md:gap-3">
          <span class="material-symbols-outlined text-accent text-xs md:text-sm">diamond</span>
          {{ welcomeMessage }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../axios'

const welcomeMessage = ref('Selamat Datang di Portal Aplikasi UII Dalwa')

function fetchSettings() {
  api.get('/settings/welcome_message').then(res => {
    if (res.data?.value) {
      welcomeMessage.value = res.data.value
    }
  }).catch(() => {})
}

onMounted(() => {
  fetchSettings()
})
</script>
