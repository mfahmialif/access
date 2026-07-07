<template>
  <nav class="horiz-nav flex items-center flex-wrap gap-1 px-4 lg:px-8 py-2 transition-colors duration-500">

    <!-- Main nav items -->
    <router-link v-for="item in navItems" :key="item.label"
                 :to="item.route"
                 :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                   isActiveRoute(item.route) ? 'active' : '']">
      <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span>
      <span>{{ item.label }}</span>
    </router-link>

    <!-- Separator -->
    <div class="w-px h-5 mx-1 shrink-0" style="background: var(--border)"></div>

    <!-- Informasi dropdown -->
    <div class="relative" ref="infoRef">
      <button @click="infoOpen = !infoOpen"
              :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap cursor-pointer',
                isInfoActive ? 'active' : '']">
        <span class="material-symbols-outlined text-[18px]">article</span>
        <span>Informasi</span>
        <span class="material-symbols-outlined text-[14px] transition-transform duration-200" :class="{ 'rotate-180': infoOpen }">expand_more</span>
      </button>
      <Transition name="dropdown">
        <div v-if="infoOpen" class="dropdown-menu absolute top-full left-0 mt-1 min-w-[180px] rounded-xl p-1.5 z-50">
          <router-link v-for="sub in infoItems" :key="sub.route" :to="sub.route" @click="infoOpen = false"
                       :class="['flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors',
                         isActiveRoute(sub.route) ? 'active-sub' : 'dropdown-item']">
            <span class="material-symbols-outlined text-[18px]">{{ sub.icon }}</span>
            {{ sub.label }}
          </router-link>
        </div>
      </Transition>
    </div>

    <!-- Manajemen dropdown -->
    <div class="relative" ref="mgmtRef">
      <button @click="mgmtOpen = !mgmtOpen"
              :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap cursor-pointer',
                isMgmtActive ? 'active' : '']">
        <span class="material-symbols-outlined text-[18px]">settings_suggest</span>
        <span>Manajemen</span>
        <span class="material-symbols-outlined text-[14px] transition-transform duration-200" :class="{ 'rotate-180': mgmtOpen }">expand_more</span>
      </button>
      <Transition name="dropdown">
        <div v-if="mgmtOpen" class="dropdown-menu absolute top-full left-0 mt-1 min-w-[180px] rounded-xl p-1.5 z-50">
          <router-link v-for="sub in mgmtItems" :key="sub.route" :to="sub.route" @click="mgmtOpen = false"
                       :class="['flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors',
                         isActiveRoute(sub.route) ? 'active-sub' : 'dropdown-item']">
            <span class="material-symbols-outlined text-[18px]">{{ sub.icon }}</span>
            {{ sub.label }}
          </router-link>
        </div>
      </Transition>
    </div>

    <!-- Separator -->
    <div class="w-px h-5 mx-1 shrink-0" style="background: var(--border)"></div>

    <!-- Website items -->
    <router-link v-for="item in websiteItems" :key="item.label"
                 :to="item.route"
                 :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                   isActiveRoute(item.route) ? 'active' : '']">
      <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span>
      <span>{{ item.label }}</span>
    </router-link>

  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const infoOpen = ref(false)
const mgmtOpen = ref(false)
const infoRef = ref(null)
const mgmtRef = ref(null)

const navItems = [
  { icon: 'dashboard', label: 'Dashboard', route: '/administrator/dashboard' },
  { icon: 'settings_remote', label: 'Control Center', route: '/administrator/control-center' }
]

const infoItems = [
  { icon: 'newspaper', label: 'Info Terkini', route: '/administrator/info-terkini' },
  { icon: 'today', label: 'Agenda Harian', route: '/administrator/agenda-harian' },
  { icon: 'date_range', label: 'Agenda Mingguan', route: '/administrator/agenda-mingguan' },
  { icon: 'calendar_month', label: 'Agenda Bulanan', route: '/administrator/agenda-bulanan' },
  { icon: 'perm_media', label: 'Gallery & Video', route: '/administrator/gallery-video' },
  { icon: 'campaign', label: 'Pengumuman', route: '/administrator/pengumuman' }
]

const mgmtItems = [
  { icon: 'tv', label: 'Manajemen TV', route: '/administrator/manajemen-tv' },
  { icon: 'person', label: 'Manajemen User', route: '/administrator/manajemen-user' },
  { icon: 'admin_panel_settings', label: 'Manajemen Role', route: '/administrator/manajemen-role' }
]

const websiteItems = [
  { icon: 'person', label: 'Profile', route: '/administrator/profile' },
  { icon: 'settings', label: 'Pengaturan', route: '/administrator/pengaturan' },
  { icon: 'monitoring', label: 'Log', route: '/administrator/log-aktivitas' }
]

const isInfoActive = computed(() => infoItems.some(i => route.path === i.route))
const isMgmtActive = computed(() => mgmtItems.some(i => route.path === i.route))

function isActiveRoute(r) { return route.path === r }

function handleClickOutside(e) {
  if (infoRef.value && !infoRef.value.contains(e.target)) infoOpen.value = false
  if (mgmtRef.value && !mgmtRef.value.contains(e.target)) mgmtOpen.value = false
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.horiz-nav {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
}
.horiz-link { color: var(--text-muted); }
.horiz-link:hover { color: var(--text-heading); background: var(--hover-nav-bg); }
.horiz-link.active {
  color: var(--text-btn); background: var(--color-accent); font-weight: 700;
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.25);
}

.dropdown-menu {
  background: var(--bg-card); border: 1px solid var(--border);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.dropdown-item { color: var(--text-body); }
.dropdown-item:hover { color: var(--color-accent); background: var(--hover-nav-bg); }
.active-sub { color: var(--text-btn); background: var(--color-accent); font-weight: 700; }

.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
.dropdown-leave-active { transition: all 0.15s ease; }
.dropdown-enter-from { opacity: 0; transform: translateY(-6px); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
