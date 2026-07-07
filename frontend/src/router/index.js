import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '../views/public/LandingPage.vue'
import InfoTerkini from '../views/public/InfoTerkini.vue'
import AgendaHarian from '../views/public/agenda/harian.vue'
import AgendaMingguan from '../views/public/agenda/mingguan.vue'
import AgendaBulanan from '../views/public/agenda/bulanan.vue'
import GalleryVideo from '../views/public/GalleryVideo.vue'
import Pengumuman from '../views/public/Pengumuman.vue'
import UrgentAnnouncement from '../views/public/UrgentAnnouncement.vue'
import ConnectToken from '../views/public/connect/token.vue'
import ConnectSuccess from '../views/public/connect/success.vue'
import ConnectFail from '../views/public/connect/fail.vue'
import DetailPage from '../views/public/DetailPage.vue'
import Apps from '../views/public/Apps.vue'
import Login from '../views/public/Login.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import AdminDashboard from '../views/admin/dashboard/Index.vue'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: LandingPage,
    meta: { title: 'Access — Smart TV Management System', requiresTv: true }
  },
  {
    path: '/info-terkini',
    name: 'InfoTerkini',
    component: InfoTerkini,
    meta: { title: 'Access — Info Terkini', requiresTv: true }
  },
  {
    path: '/info-terkini/:id',
    name: 'DetailNews',
    component: DetailPage,
    meta: { title: 'Access — Detail Berita', requiresTv: true, detailType: 'news' }
  },

  {
    path: '/agenda-harian',
    name: 'AgendaHarian',
    component: AgendaHarian,
    meta: { title: 'Access — Agenda Harian', requiresTv: true }
  },
  {
    path: '/agenda-harian/:id',
    name: 'DetailAgenda',
    component: DetailPage,
    meta: { title: 'Access — Detail Agenda Harian', requiresTv: true, detailType: 'agenda' }
  },

  {
    path: '/agenda-mingguan',
    name: 'AgendaMingguan',
    component: AgendaMingguan,
    meta: { title: 'Access — Agenda Mingguan', requiresTv: true }
  },
  {
    path: '/agenda-mingguan/:id',
    name: 'DetailWeekly',
    component: DetailPage,
    meta: { title: 'Access — Detail Agenda Mingguan', requiresTv: true, detailType: 'weekly' }
  },

  {
    path: '/agenda-bulanan',
    name: 'AgendaBulanan',
    component: AgendaBulanan,
    meta: { title: 'Access — Agenda Bulanan', requiresTv: true }
  },
  {
    path: '/agenda-bulanan/:id',
    name: 'DetailMonthly',
    component: DetailPage,
    meta: { title: 'Access — Detail Agenda Bulanan', requiresTv: true, detailType: 'monthly' }
  },

  {
    path: '/gallery-video',
    name: 'GalleryVideo',
    component: GalleryVideo,
    meta: { title: 'Access — Gallery & Video', requiresTv: true }
  },
  {
    path: '/gallery-video/:id',
    name: 'DetailGallery',
    component: DetailPage,
    meta: { title: 'Access — Detail Gallery', requiresTv: true, detailType: 'gallery' }
  },

  {
    path: '/pengumuman',
    name: 'Pengumuman',
    component: Pengumuman,
    meta: { title: 'Access — Pengumuman', requiresTv: true }
  },
  {
    path: '/pengumuman/:id',
    name: 'DetailAnnouncement',
    component: DetailPage,
    meta: { title: 'Access — Detail Pengumuman', requiresTv: true, detailType: 'announcement' }
  },

  {
    path: '/urgent',
    name: 'UrgentAnnouncement',
    component: UrgentAnnouncement,
    meta: { title: 'Access — Urgent Announcement', requiresTv: true }
  },
  {
    path: '/connect',
    name: 'ConnectToken',
    component: ConnectToken,
    meta: { title: 'Access — Hubungkan Perangkat' }
  },
  {
    path: '/connect/success',
    name: 'ConnectSuccess',
    component: ConnectSuccess,
    meta: { title: 'Access — Terhubung Berhasil' }
  },
  {
    path: '/connect/fail',
    name: 'ConnectFail',
    component: ConnectFail,
    meta: { title: 'Access — Koneksi Gagal' }
  },
  {
    path: '/apps',
    name: 'Apps',
    component: Apps,
    meta: { title: 'Access — Dalwa Apps' }
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { title: 'Access — Login' }
  },

  // ═══ Admin Pages (wrapped in AdminLayout) ═══
  {
    path: '/administrator',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { title: 'Access — Admin Dashboard', pageTitle: 'Dashboard Overview', requiresAuth: true }
      },
      {
        path: 'manajemen-user',
        name: 'AdminManajemenUser',
        component: () => import('../views/admin/user/User.vue'),
        meta: { title: 'Access — Manajemen User', pageTitle: 'Manajemen User', requiresAuth: true }
      },
      {
        path: 'manajemen-role',
        name: 'AdminManajemenRole',
        component: () => import('../views/admin/user/Role.vue'),
        meta: { title: 'Access — Manajemen Role', pageTitle: 'Manajemen Role', requiresAuth: true }
      },
      {
        path: 'manajemen-tv',
        name: 'AdminManajemenTV',
        component: () => import('../views/admin/tv/Index.vue'),
        meta: { title: 'Access — Manajemen TV', pageTitle: 'Manajemen TV', requiresAuth: true }
      },
      {
        path: 'apps',
        name: 'AdminApps',
        component: () => import('../views/admin/apps/Index.vue'),
        meta: { title: 'Access — Apps Portal', pageTitle: 'Apps Portal', requiresAuth: true }
      },
      {
        path: 'apps/create',
        name: 'AdminAppsCreate',
        component: () => import('../views/admin/apps/Form.vue'),
        meta: { title: 'Access — Tambah Link', pageTitle: 'Tambah Link', requiresAuth: true }
      },
      {
        path: 'apps/:id/edit',
        name: 'AdminAppsEdit',
        component: () => import('../views/admin/apps/Form.vue'),
        meta: { title: 'Access — Edit Link', pageTitle: 'Edit Link', requiresAuth: true }
      },
      {
        path: 'info-terkini',
        name: 'AdminInfoTerkini',
        component: () => import('../views/admin/info/Index.vue'),
        meta: { title: 'Access — Info Terkini', pageTitle: 'Info Terkini', requiresAuth: true }
      },
      {
        path: 'info-terkini/create',
        name: 'AdminInfoTerkiniCreate',
        component: () => import('../views/admin/info/Form.vue'),
        meta: { title: 'Access — Tambah Konten', pageTitle: 'Tambah Konten', requiresAuth: true }
      },
      {
        path: 'info-terkini/:id/edit',
        name: 'AdminInfoTerkiniEdit',
        component: () => import('../views/admin/info/Form.vue'),
        meta: { title: 'Access — Edit Konten', pageTitle: 'Edit Konten', requiresAuth: true }
      },
      {
        path: 'agenda-harian',
        name: 'AdminAgendaHarian',
        component: () => import('../views/admin/agenda/harian/Index.vue'),
        meta: { title: 'Access — Agenda Harian', pageTitle: 'Agenda Harian', requiresAuth: true }
      },
      {
        path: 'agenda-harian/create',
        name: 'AdminAgendaHarianCreate',
        component: () => import('../views/admin/agenda/harian/Form.vue'),
        meta: { title: 'Access — Tambah Agenda', pageTitle: 'Tambah Agenda', requiresAuth: true }
      },
      {
        path: 'agenda-harian/:id/edit',
        name: 'AdminAgendaHarianEdit',
        component: () => import('../views/admin/agenda/harian/Form.vue'),
        meta: { title: 'Access — Edit Agenda', pageTitle: 'Edit Agenda', requiresAuth: true }
      },
      {
        path: 'agenda-mingguan',
        name: 'AdminAgendaMingguan',
        component: () => import('../views/admin/agenda/mingguan/Index.vue'),
        meta: { title: 'Access — Agenda Mingguan', pageTitle: 'Agenda Mingguan', requiresAuth: true }
      },
      {
        path: 'agenda-mingguan/create',
        name: 'AdminAgendaMingguanCreate',
        component: () => import('../views/admin/agenda/mingguan/Form.vue'),
        meta: { title: 'Access — Tambah Agenda Mingguan', pageTitle: 'Tambah Agenda Mingguan', requiresAuth: true }
      },
      {
        path: 'agenda-mingguan/:id/edit',
        name: 'AdminAgendaMingguanEdit',
        component: () => import('../views/admin/agenda/mingguan/Form.vue'),
        meta: { title: 'Access — Edit Agenda Mingguan', pageTitle: 'Edit Agenda Mingguan', requiresAuth: true }
      },
      {
        path: 'agenda-bulanan',
        name: 'AdminAgendaBulanan',
        component: () => import('../views/admin/agenda/bulanan/Index.vue'),
        meta: { title: 'Access — Agenda Bulanan', pageTitle: 'Agenda Bulanan', requiresAuth: true }
      },
      {
        path: 'agenda-bulanan/create',
        name: 'AdminAgendaBulananCreate',
        component: () => import('../views/admin/agenda/bulanan/Form.vue'),
        meta: { title: 'Access — Tambah Agenda Bulanan', pageTitle: 'Tambah Agenda Bulanan', requiresAuth: true }
      },
      {
        path: 'agenda-bulanan/:id/edit',
        name: 'AdminAgendaBulananEdit',
        component: () => import('../views/admin/agenda/bulanan/Form.vue'),
        meta: { title: 'Access — Edit Agenda Bulanan', pageTitle: 'Edit Agenda Bulanan', requiresAuth: true }
      },
      {
        path: 'gallery-video',
        name: 'AdminGalleryVideo',
        component: () => import('../views/admin/gallery/video/Index.vue'),
        meta: { title: 'Access — Gallery & Video', pageTitle: 'Gallery & Video', requiresAuth: true }
      },
      {
        path: 'gallery-video/create',
        name: 'AdminGalleryVideoCreate',
        component: () => import('../views/admin/gallery/video/Form.vue'),
        meta: { title: 'Access — Upload Media', pageTitle: 'Upload Media', requiresAuth: true }
      },
      {
        path: 'gallery-video/:id/edit',
        name: 'AdminGalleryVideoEdit',
        component: () => import('../views/admin/gallery/video/Form.vue'),
        meta: { title: 'Access — Edit Media', pageTitle: 'Edit Media', requiresAuth: true }
      },
      {
        path: 'pengumuman',
        name: 'AdminPengumuman',
        component: () => import('../views/admin/pengumuman/Index.vue'),
        meta: { title: 'Access — Pengumuman', pageTitle: 'Pengumuman', requiresAuth: true }
      },
      {
        path: 'pengumuman/create',
        name: 'AdminPengumumanCreate',
        component: () => import('../views/admin/pengumuman/Form.vue'),
        meta: { title: 'Access — Tambah Pengumuman', pageTitle: 'Tambah Pengumuman', requiresAuth: true }
      },
      {
        path: 'pengumuman/:id/edit',
        name: 'AdminPengumumanEdit',
        component: () => import('../views/admin/pengumuman/Form.vue'),
        meta: { title: 'Access — Edit Pengumuman', pageTitle: 'Edit Pengumuman', requiresAuth: true }
      },
      {
        path: 'profile',
        name: 'AdminProfile',
        component: () => import('../views/admin/profile/Index.vue'),
        meta: { title: 'Access — Profile', pageTitle: 'Profile', requiresAuth: true }
      },
      {
        path: 'control-center',
        name: 'AdminControlCenter',
        component: () => import('../views/admin/control/Index.vue'),
        meta: { title: 'Access — Control Center', pageTitle: 'Control Center', requiresAuth: true }
      },
      {
        path: 'pengaturan',
        name: 'AdminPengaturan',
        component: () => import('../views/admin/pengaturan/Index.vue'),
        meta: { title: 'Access — Pengaturan', pageTitle: 'Pengaturan', requiresAuth: true }
      },
      {
        path: 'log-aktivitas',
        name: 'AdminLogAktivitas',
        component: () => import('../views/admin/log/Index.vue'),
        meta: { title: 'Access — Log Aktivitas', pageTitle: 'Log Aktivitas', requiresAuth: true }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) {
      return { el: to.hash, behavior: 'smooth' }
    }
    return savedPosition || { top: 0 }
  }
})

// ── Navigation guard ──
router.beforeEach((to) => {
  document.title = to.meta.title || 'Access'

  const isAuthenticated = !!localStorage.getItem('auth_token')

  // Redirect ke login jika route butuh auth tapi belum login
  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'Login' }
  }

  // Cek role: hanya Admin dan Operator yang boleh akses halaman admin
  if (to.meta.requiresAuth && isAuthenticated) {
    try {
      const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}')
      const roleName = authUser?.role?.name
      if (!roleName || !['Admin', 'Operator'].includes(roleName)) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        return { name: 'Login' }
      }
    } catch {
      return { name: 'Login' }
    }
  }

  // Redirect ke /connect jika route butuh TV terhubung tapi belum ada token
  // Admin/Operator yang sudah login tetap bisa akses (untuk fitur Lihat TV)
  // Impersonate mode juga diizinkan
  const isTvConnected = !!localStorage.getItem('tv_token')
  const isImpersonating = !!to.query.impersonate
  if (to.meta.requiresTv && !isTvConnected && !isAuthenticated && !isImpersonating) {
    return { name: 'ConnectToken' }
  }

  // Redirect ke dashboard jika sudah login tapi akses halaman login
  if (to.name === 'Login' && isAuthenticated) {
    return { name: 'AdminDashboard' }
  }
})

export default router
