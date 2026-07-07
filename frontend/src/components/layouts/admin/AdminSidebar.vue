<template>
  <aside :class="['sidebar flex h-screen flex-col p-4 shrink-0 transition-all duration-300', collapsed ? 'sidebar-collapsed' : '']">
    <!-- Logo — fixed at top -->
    <div class="flex items-center px-3 pb-4 shrink-0" :class="collapsed ? 'justify-center' : 'justify-between'">
      <div class="overflow-hidden transition-all duration-300 flex items-center" :style="collapsed ? 'width: 0; opacity: 0' : 'width: auto; opacity: 1'">
        <div class="overflow-hidden h-14">
          <img src="/img/logo-full.png" alt="Access Admin" class="h-25 object-contain -mt-[22px]" />
        </div>
      </div>
      <img v-if="collapsed" src="/img/logo.png" alt="Access" class="w-12 h-12 object-contain mx-auto" />
      <button @click="$emit('close-sidebar')"
              class="close-btn p-1.5 rounded-lg transition-colors cursor-pointer lg:hidden">
        <span class="material-symbols-outlined text-[22px]">close</span>
      </button>
    </div>

    <!-- Navigation — scrollable with SimpleBar -->
    <div class="flex-1 min-h-0">
      <simplebar class="h-full" :auto-hide="true">
        <nav class="flex flex-col gap-1 pr-1">
          <router-link v-for="item in navItems" :key="item.label"
                       :to="item.route || '#'"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute(item.route)
                           ? 'bg-accent text-btn-text font-bold hover:bg-accent/90'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? item.label : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">{{ item.icon }}</span>
            <span class="sidebar-label text-sm leading-normal">{{ item.label }}</span>
          </router-link>

          <!-- Separator: Informasi -->
          <div class="mt-4 mb-1 px-3" v-if="!collapsed">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Informasi</p>
          </div>
          <div v-else class="mt-3 mb-2 mx-3 border-t" style="border-color: var(--border)"></div>
          <!-- Apps Portal -->
          <router-link to="/administrator/apps"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/apps')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Apps Portal' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">apps</span>
            <span class="sidebar-label text-sm leading-normal">Apps Portal</span>
          </router-link>
          <!-- Info Terkini -->
          <router-link to="/administrator/info-terkini"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/info-terkini')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Info Terkini' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">newspaper</span>
            <span class="sidebar-label text-sm leading-normal">Info Terkini</span>
          </router-link>

          <!-- Agenda — expandable (hidden when collapsed) -->
          <button v-if="!collapsed" @click="agendaOpen = !agendaOpen"
                  class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-colors cursor-pointer nav-item text-body font-medium">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[24px] shrink-0">calendar_today</span>
              <span class="sidebar-label text-sm leading-normal">Agenda</span>
            </div>
            <span class="material-symbols-outlined text-[18px] transition-transform duration-300 sidebar-label"
                  :class="{ 'rotate-180': agendaOpen }">expand_more</span>
          </button>
          <router-link v-if="collapsed" to="/administrator/agenda-harian"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         route.path.startsWith('/administrator/agenda-')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       title="Agenda">
            <span class="material-symbols-outlined text-[24px] shrink-0">calendar_today</span>
          </router-link>
          <!-- Agenda Submenu -->
          <div v-if="!collapsed" :class="['submenu-wrapper', { open: agendaOpen }]">
            <div class="submenu-inner flex flex-col gap-0.5 ml-6 pl-3 border-l border-accent/20">
              <router-link to="/administrator/agenda-harian"
                           :class="[
                             'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                             isActiveRoute('/administrator/agenda-harian')
                               ? 'bg-accent text-btn-text font-bold'
                               : 'nav-item text-body font-medium cursor-pointer'
                           ]">
                <span class="material-symbols-outlined text-[20px]">today</span>
                <span>Harian</span>
              </router-link>
              <router-link to="/administrator/agenda-mingguan"
                           :class="[
                             'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                             isActiveRoute('/administrator/agenda-mingguan')
                               ? 'bg-accent text-btn-text font-bold'
                               : 'nav-item text-body font-medium cursor-pointer'
                           ]">
                <span class="material-symbols-outlined text-[20px]">date_range</span>
                <span>Mingguan</span>
              </router-link>
              <router-link to="/administrator/agenda-bulanan"
                           :class="[
                             'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                             isActiveRoute('/administrator/agenda-bulanan')
                               ? 'bg-accent text-btn-text font-bold'
                               : 'nav-item text-body font-medium cursor-pointer'
                           ]">
                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                <span>Bulanan</span>
              </router-link>
            </div>
          </div>

          <!-- Gallery & Video -->
          <router-link to="/administrator/gallery-video"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/gallery-video')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Gallery & Video' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">perm_media</span>
            <span class="sidebar-label text-sm leading-normal">Gallery & Video</span>
          </router-link>
          <!-- Pengumuman -->
          <router-link to="/administrator/pengumuman"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/pengumuman')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Pengumuman' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">campaign</span>
            <span class="sidebar-label text-sm leading-normal">Pengumuman</span>
          </router-link>

          <!-- Separator: Manajemen -->
          <div class="mt-4 mb-1 px-3" v-if="!collapsed">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Manajemen</p>
          </div>
          <div v-else class="mt-3 mb-2 mx-3 border-t" style="border-color: var(--border)"></div>
          <router-link to="/administrator/manajemen-tv"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/manajemen-tv')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Manajemen TV' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">tv</span>
            <span class="sidebar-label text-sm leading-normal">Manajemen TV</span>
          </router-link>

          <!-- Manajemen User — expandable (hidden when collapsed) -->
          <button v-if="!collapsed" @click="manajemenUserOpen = !manajemenUserOpen"
                  :class="[
                    'w-full flex items-center justify-between px-3 py-2 rounded-lg transition-colors cursor-pointer',
                    isManajemenUserActive
                      ? 'bg-accent/15 text-accent font-bold'
                      : 'nav-item text-body font-medium'
                  ]">
            <div class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[24px] shrink-0">group</span>
              <span class="sidebar-label text-sm leading-normal">Manajemen User</span>
            </div>
            <span class="material-symbols-outlined text-[18px] transition-transform duration-300 sidebar-label"
                  :class="{ 'rotate-180': manajemenUserOpen }">expand_more</span>
          </button>
          <router-link v-if="collapsed" to="/administrator/manajemen-user"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isManajemenUserActive
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       title="Manajemen User">
            <span class="material-symbols-outlined text-[24px] shrink-0">group</span>
          </router-link>
          <!-- Submenu -->
          <div v-if="!collapsed" :class="['submenu-wrapper', { open: manajemenUserOpen }]">
            <div class="submenu-inner flex flex-col gap-0.5 ml-6 pl-3 border-l border-accent/20">
              <router-link to="/administrator/manajemen-user"
                           :class="[
                             'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                             isActiveRoute('/administrator/manajemen-user')
                               ? 'bg-accent text-btn-text font-bold'
                               : 'nav-item text-body font-medium cursor-pointer'
                           ]">
                <span class="material-symbols-outlined text-[20px]">person</span>
                <span>User</span>
              </router-link>
              <router-link to="/administrator/manajemen-role"
                           :class="[
                             'flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors',
                             isActiveRoute('/administrator/manajemen-role')
                               ? 'bg-accent text-btn-text font-bold'
                               : 'nav-item text-body font-medium cursor-pointer'
                           ]">
                <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                <span>Role</span>
              </router-link>
            </div>
          </div>

          <!-- Separator: Website -->
          <div class="mt-4 mb-1 px-3" v-if="!collapsed">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Website</p>
          </div>
          <div v-else class="mt-3 mb-2 mx-3 border-t" style="border-color: var(--border)"></div>
          <router-link to="/administrator/profile"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/profile')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Profile' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">person</span>
            <span class="sidebar-label text-sm leading-normal">Profile</span>
          </router-link>
          <router-link to="/administrator/pengaturan"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/pengaturan')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Pengaturan' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">settings</span>
            <span class="sidebar-label text-sm leading-normal">Pengaturan</span>
          </router-link>
          <router-link to="/administrator/log-aktivitas"
                       :class="[
                         'flex items-center gap-3 px-3 py-2 rounded-lg transition-colors sidebar-link',
                         isActiveRoute('/administrator/log-aktivitas')
                           ? 'bg-accent text-btn-text font-bold'
                           : 'nav-item text-body font-medium cursor-pointer'
                       ]"
                       :title="collapsed ? 'Log Aktivitas' : ''">
            <span class="material-symbols-outlined text-[24px] shrink-0">monitoring</span>
            <span class="sidebar-label text-sm leading-normal">Log Aktivitas</span>
          </router-link>
        </nav>
      </simplebar>
    </div>

    <!-- Logout — fixed at bottom -->
    <div class="pt-4 shrink-0">
      <!-- Logout -->
      <button @click="handleLogout"
              class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 w-full shadow-[0_0_15px_rgba(251,191,36,0.3)] cursor-pointer active:scale-95"
              :title="collapsed ? 'Logout' : ''">
        <span class="material-symbols-outlined text-[20px] shrink-0">logout</span>
        <span v-if="!collapsed">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import { useAuthStore } from '../../../stores/auth'

defineProps({ collapsed: { type: Boolean, default: false } })
defineEmits(['close-sidebar', 'toggle-collapse'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const navItems = [
  { icon: 'dashboard', label: 'Dashboard', route: '/administrator/dashboard' },
  { icon: 'settings_remote', label: 'Control Center', route: '/administrator/control-center' }
]
// Agenda submenu
const agendaOpen = ref(route.path.startsWith('/administrator/agenda-'))

// Manajemen User submenu
const isManajemenUserActive = computed(() =>
  route.path.startsWith('/administrator/manajemen-user') ||
  route.path.startsWith('/administrator/manajemen-role')
)
const manajemenUserOpen = ref(isManajemenUserActive.value)

function isActiveRoute(itemRoute) {
  if (!itemRoute) return false
  return route.path === itemRoute
}

function handleLogout() {
  authStore.logout()
}
</script>

<style scoped>
/* ═══ SimpleBar Sidebar Overrides ═══ */
.sidebar :deep(.simplebar-scrollbar::before) {
  background: var(--border-light);
  border-radius: 999px;
  opacity: 0;
  transition: opacity 0.4s ease;
}
.sidebar :deep(.simplebar-scrollbar.simplebar-visible::before) {
  opacity: 0.7;
}
.sidebar:hover :deep(.simplebar-scrollbar::before) {
  opacity: 0.5;
}
.sidebar :deep(.simplebar-hover .simplebar-scrollbar::before),
.sidebar :deep(.simplebar-scrollbar:hover::before) {
  background: var(--text-muted);
  opacity: 1;
}
.sidebar :deep(.simplebar-track.simplebar-vertical) {
  width: 8px;
  right: 0;
}
.sidebar :deep(.simplebar-track.simplebar-horizontal) {
  display: none;
}

/* ═══ Element Theming ═══ */
.sidebar { background: var(--bg-sidebar); }
.close-btn { color: var(--text-muted); }
.close-btn:hover { background: var(--bg-input); color: var(--color-accent); }
.text-heading { color: var(--text-heading); }
.text-body { color: var(--text-body); }
.text-muted { color: var(--text-muted); }
.text-btn-text { color: var(--text-btn); }

/* ═══ Premium Nav Hover ═══ */
.nav-item {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-left: 3px solid transparent;
  position: relative;
}
.nav-item:hover {
  background: var(--hover-nav-bg);
  color: var(--color-accent);
  border-left-color: var(--color-accent);
  transform: translateX(4px);
  box-shadow: -3px 0 12px -2px rgba(251, 191, 36, 0.15);
}
.nav-item:hover .material-symbols-outlined {
  transform: scale(1.15);
  filter: drop-shadow(0 0 6px rgba(251, 191, 36, 0.5));
  transition: all 0.3s ease;
}
.nav-item:active {
  transform: translateX(2px) scale(0.98);
}

/* ═══ Submenu Slide Animation ═══ */
.submenu-wrapper {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.submenu-wrapper.open {
  grid-template-rows: 1fr;
}
.submenu-inner {
  overflow: hidden;
  opacity: 0;
  transition: opacity 0.25s ease 0.05s;
}
.submenu-wrapper.open .submenu-inner {
  opacity: 1;
  transition: opacity 0.25s ease 0.1s;
}

/* ═══ Collapsed Mode ═══ */
.sidebar-collapsed .sidebar-label {
  width: 0;
  opacity: 0;
  overflow: hidden;
  white-space: nowrap;
  transition: width 0.3s ease, opacity 0.2s ease;
}
.sidebar-label {
  transition: width 0.3s ease, opacity 0.2s ease 0.1s;
  white-space: nowrap;
}
.sidebar-collapsed .sidebar-link {
  justify-content: center;
  gap: 0;
  padding: 8px;
  width: 40px;
  height: 40px;
  margin: 0 auto;
  border-radius: 12px;
}
.sidebar-collapsed nav.flex {
  align-items: center;
}

/* ═══ Collapse Toggle — Edge Pill ═══ */
.collapse-toggle {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-muted);
  opacity: 0;
  transform: scale(0.8);
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.group\/sidebar:hover .collapse-toggle,
.sidebar-collapsed .collapse-toggle {
  opacity: 1;
  transform: scale(1);
}
.collapse-toggle:hover {
  background: var(--color-accent);
  color: var(--text-btn);
  border-color: var(--color-accent);
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.4);
  transform: scale(1.15);
}
</style>
