<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ LIVE STATUS BAR ═══ -->
    <div class="status-bar rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
          <span class="material-symbols-outlined text-green-400 text-[22px] animate-pulse">cell_tower</span>
        </div>
        <div>
          <p class="text-sm font-bold" style="color: var(--text-heading)">Broadcast System Active</p>
          <p class="text-xs" style="color: var(--text-muted)">Semua sistem berjalan normal</p>
        </div>
      </div>
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg" style="background: var(--bg-input)">
          <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
          <span class="text-xs font-bold text-green-400">{{ stats.online }} Online</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg" style="background: var(--bg-input)">
          <span class="w-2 h-2 rounded-full bg-red-400"></span>
          <span class="text-xs font-bold text-red-400">{{ stats.offline }} Offline</span>
        </div>
        <span class="text-xs font-mono" style="color: var(--text-muted)">Last sync: {{ lastSync }}</span>
      </div>
    </div>

    <!-- ═══ TOAST ═══ -->
    <Transition name="toast">
      <div v-if="toast.show" class="fixed top-20 right-6 z-[9999] max-w-sm"
           :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border">
          <span class="material-symbols-outlined text-lg">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
          <span class="text-sm font-medium">{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

    <!-- ═══ ACTION CARDS GRID ═══ -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-5">

      <!-- Push Konten ke TV Tertentu -->
      <div class="action-card rounded-xl p-6 flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-accent/15 flex items-center justify-center border border-accent/30">
            <span class="material-symbols-outlined text-accent text-[28px]">send_to_mobile</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Push Konten ke TV</h3>
            <p class="text-xs" style="color: var(--text-muted)">Kirim konten ke TV tertentu</p>
          </div>
        </div>
        <div class="flex flex-col gap-3">
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Pilih TV</label>
            <VueMultiselect v-model="selectedTV" :options="tvDevices" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="id" placeholder="Pilih device..." />
          </div>
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Pilih Halaman</label>
            <VueMultiselect v-model="selectedContent" :options="contentOptions" :close-on-select="true" :searchable="true" :internal-search="false" :allow-empty="false" :show-labels="false" :loading="contentSearching" label="name" track-by="path" placeholder="Ketik untuk cari konten..." @search-change="searchContent" @open="onDropdownOpen" />
          </div>
        </div>
        <button @click="pushContent" :disabled="sending.push" class="action-btn-primary w-full flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.push" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">send</span>
          Push Konten
        </button>
      </div>

      <!-- Push ke Semua TV -->
      <div class="action-card rounded-xl p-6 flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-blue-500/15 flex items-center justify-center border border-blue-500/30">
            <span class="material-symbols-outlined text-blue-400 text-[28px]">cast_connected</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Push ke Semua TV</h3>
            <p class="text-xs" style="color: var(--text-muted)">Broadcast ke seluruh device</p>
          </div>
        </div>
        <div class="flex flex-col gap-3">
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Pilih Halaman</label>
            <VueMultiselect v-model="broadcastContent" :options="contentOptions" :close-on-select="true" :searchable="true" :internal-search="false" :allow-empty="false" :show-labels="false" :loading="contentSearching" label="name" track-by="path" placeholder="Ketik untuk cari konten..." @search-change="searchContent" @open="onDropdownOpen" />
          </div>
          <div class="info-box rounded-lg px-3 py-2 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-400 text-[16px]">info</span>
            <span class="text-xs" style="color: var(--text-muted)">Akan dikirim ke <strong class="text-blue-400">{{ stats.online }} TV</strong> yang online</span>
          </div>
        </div>
        <button @click="broadcastToAll" :disabled="sending.broadcast" class="action-btn-blue w-full flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.broadcast" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">cast</span>
          Broadcast ke Semua
        </button>
      </div>

      <!-- Force Reload -->
      <div class="action-card rounded-xl p-6 flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-green-500/15 flex items-center justify-center border border-green-500/30">
            <span class="material-symbols-outlined text-green-400 text-[28px]">refresh</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Force Reload</h3>
            <p class="text-xs" style="color: var(--text-muted)">Paksa TV untuk reload halaman</p>
          </div>
        </div>
        <div class="flex flex-col gap-3 flex-1">
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Target</label>
            <VueMultiselect v-model="reloadTarget" :options="targetOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih target..." />
          </div>
        </div>
        <button @click="forceReload" :disabled="sending.reload" class="action-btn-green w-full flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.reload" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">sync</span>
          Force Reload
        </button>
      </div>

      <!-- Force Home -->
      <div class="action-card rounded-xl p-6 flex flex-col gap-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-purple-500/15 flex items-center justify-center border border-purple-500/30">
            <span class="material-symbols-outlined text-purple-400 text-[28px]">home</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Force Home</h3>
            <p class="text-xs" style="color: var(--text-muted)">Kembalikan TV ke halaman utama</p>
          </div>
        </div>
        <div class="flex flex-col gap-3 flex-1">
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Target</label>
            <VueMultiselect v-model="homeTarget" :options="targetOptions" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih target..." />
          </div>
        </div>
        <button @click="forceHome" :disabled="sending.home" class="action-btn-purple w-full flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.home" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">home</span>
          Force Home
        </button>
      </div>

    </div>

    <!-- ═══ BANNER CARD (Full-width, separate from grid) ═══ -->
    <div class="banner-card-wrapper action-card rounded-xl p-6 flex flex-col gap-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-red-500/15 flex items-center justify-center border border-red-500/30">
          <span class="material-symbols-outlined text-red-400 text-[28px]">emergency</span>
        </div>
        <div>
          <h3 class="font-bold text-sm" style="color: var(--text-heading)">Tampilkan Banner Global</h3>
          <p class="text-xs" style="color: var(--text-muted)">Tampilkan banner darurat / pengumuman di semua TV</p>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Judul Banner</label>
          <input v-model="bannerTitle" class="filter-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Masukkan judul banner..." />
        </div>
        <div>
          <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Tipe Banner</label>
          <VueMultiselect v-model="bannerType" :options="bannerTypes" :close-on-select="true" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih tipe..." />
        </div>
      </div>
      <div>
        <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Pesan Banner</label>
        <QuillEditor ref="bannerQuillRef" v-model:content="bannerMessage" content-type="html" theme="snow" :toolbar="bannerQuillToolbar" class="quill-dark quill-banner" />
      </div>
      <div class="flex flex-col sm:flex-row gap-3">
        <button @click="showBanner" :disabled="sending.banner" class="action-btn-red flex-1 flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.banner" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">notifications_active</span>
          Tampilkan Banner
        </button>
        <button @click="dismissBanner" :disabled="sending.bannerDismiss" class="action-btn-dismiss flex items-center justify-center gap-2 rounded-lg h-10 px-6 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.bannerDismiss" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">close</span>
          Hapus Banner Aktif
        </button>
      </div>
    </div>

    <!-- ═══ TARGETED BANNER PER TV ═══ -->
    <div class="banner-card-wrapper action-card rounded-xl p-6 flex flex-col gap-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-amber-500/15 flex items-center justify-center border border-amber-500/30">
            <span class="material-symbols-outlined text-amber-400 text-[28px]">ads_click</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Banner ke TV Tertentu</h3>
            <p class="text-xs" style="color: var(--text-muted)">Pilih TV mana saja yang akan menampilkan banner</p>
          </div>
        </div>
        <!-- Mode Toggle -->
        <div class="flex rounded-lg overflow-hidden border" style="border-color: var(--border)">
          <button @click="targetedMode = 'add'; targetedTvIds = []"
                  class="px-4 py-2 text-xs font-bold cursor-pointer transition-all"
                  :class="targetedMode === 'add' ? 'bg-amber-500 text-black' : ''"
                  :style="targetedMode !== 'add' ? 'background: var(--bg-input); color: var(--text-muted)' : ''">
            <span class="material-symbols-outlined text-[14px] align-middle mr-1">add_circle</span>Tambah
          </button>
          <button @click="targetedMode = 'delete'; targetedTvIds = []"
                  class="px-4 py-2 text-xs font-bold cursor-pointer transition-all"
                  :class="targetedMode === 'delete' ? 'bg-red-500 text-white' : ''"
                  :style="targetedMode !== 'delete' ? 'background: var(--bg-input); color: var(--text-muted)' : ''">
            <span class="material-symbols-outlined text-[14px] align-middle mr-1">delete</span>Hapus
          </button>
        </div>
      </div>

      <div :class="targetedMode === 'add' ? 'grid grid-cols-1 lg:grid-cols-2 gap-5' : ''">
        <!-- TV Selection -->
        <div class="flex flex-col gap-3">
          <div class="flex items-center justify-between">
            <label class="text-xs font-medium" style="color: var(--text-muted)">
              {{ targetedMode === 'add' ? 'Pilih TV' : 'Pilih TV yang akan dihapus bannernya' }}
            </label>
            <div class="flex items-center gap-3">
              <input v-model="targetedTvSearch" class="filter-input rounded-lg py-1.5 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-accent w-36" placeholder="Cari TV..." />
              <label class="flex items-center gap-1.5 cursor-pointer select-none">
                <input type="checkbox" :checked="isAllTvSelected" @change="toggleAllTv" class="accent-amber-500 w-3.5 h-3.5 rounded cursor-pointer" />
                <span class="text-xs font-medium" style="color: var(--text-muted)">Semua</span>
              </label>
            </div>
          </div>
          <div class="tv-checklist rounded-xl p-1 max-h-[260px] overflow-y-auto space-y-1">
            <div v-if="tvListForMode.length === 0" class="text-center py-6 text-xs" style="color: var(--text-muted)">
              {{ targetedMode === 'delete' ? 'Tidak ada TV dengan banner aktif' : 'Tidak ada TV ditemukan' }}
            </div>
            <label v-for="tv in tvListForMode" :key="tv.id"
                   class="tv-check-item flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-all select-none"
                   :class="{ 'tv-check-selected': targetedTvIds.includes(tv.id) }">
              <input type="checkbox" :value="tv.id" v-model="targetedTvIds"
                     class="w-4 h-4 rounded cursor-pointer" :class="targetedMode === 'delete' ? 'accent-red-500' : 'accent-amber-500'" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate" style="color: var(--text-heading)">{{ tv.name }}</p>
                <p class="text-[11px] truncate" style="color: var(--text-muted)">{{ tv.location || 'No location' }}</p>
              </div>
              <span v-if="activeBannerDeviceIds.includes(tv.id)" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 whitespace-nowrap">BANNER AKTIF</span>
              <span v-else class="w-2 h-2 rounded-full shrink-0" :class="tv.status === 'online' ? 'bg-green-400' : 'bg-red-400'"></span>
            </label>
          </div>
          <p class="text-[11px]" style="color: var(--text-muted)">
            <strong :class="targetedMode === 'delete' ? 'text-red-400' : 'text-amber-400'">{{ targetedTvIds.length }}</strong> TV dipilih
            <span v-if="activeBannerDeviceIds.length"> · <strong class="text-amber-400">{{ activeBannerDeviceIds.length }}</strong> sudah punya banner</span>
          </p>
        </div>

        <!-- Right: Banner Content (only in add mode) -->
        <div v-if="targetedMode === 'add'" class="flex flex-col gap-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Judul Banner</label>
              <input v-model="targetedBannerTitle" class="filter-input w-full rounded-lg py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Judul..." />
            </div>
            <div>
              <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Tipe</label>
              <VueMultiselect v-model="targetedBannerType" :options="bannerTypes" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" placeholder="Pilih..." />
            </div>
          </div>
          <div>
            <label class="text-xs font-medium mb-1.5 block" style="color: var(--text-muted)">Pesan Banner</label>
            <QuillEditor ref="targetedQuillRef" v-model:content="targetedBannerMessage" content-type="html" theme="snow" :toolbar="bannerQuillToolbar" class="quill-dark quill-targeted" />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-3">
        <button v-if="targetedMode === 'add'" @click="pushTargetedBanner" :disabled="sending.targetedPush || targetedTvIds.length === 0"
                class="action-btn-primary flex-1 flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.targetedPush" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">send</span>
          Tampilkan Banner ({{ targetedTvIds.length }} TV)
        </button>
        <button v-if="targetedMode === 'delete'" @click="removeTargetedBanner" :disabled="sending.targetedRemove || targetedTvIds.length === 0"
                class="action-btn-red flex-1 flex items-center justify-center gap-2 rounded-lg h-10 font-bold text-sm cursor-pointer active:scale-[0.98] transition-all disabled:opacity-50">
          <span v-if="sending.targetedRemove" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
          <span v-else class="material-symbols-outlined text-[18px]">delete</span>
          Hapus Banner ({{ targetedTvIds.length }} TV)
        </button>
      </div>
    </div>

    <!-- ═══ ACTIVE BANNERS LIST ═══ -->
    <div class="action-card rounded-xl p-6 flex flex-col gap-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-indigo-500/15 flex items-center justify-center border border-indigo-500/30">
            <span class="material-symbols-outlined text-indigo-400 text-[28px]">list_alt</span>
          </div>
          <div>
            <h3 class="font-bold text-sm" style="color: var(--text-heading)">Banner Aktif</h3>
            <p class="text-xs" style="color: var(--text-muted)">Semua banner yang sedang aktif di database</p>
          </div>
        </div>
        <button @click="fetchAllBanners" class="text-xs font-medium px-3 py-1 rounded-lg cursor-pointer transition-colors" style="color: var(--text-muted); background: var(--bg-input)">
          <span class="material-symbols-outlined text-[14px] align-middle">refresh</span> Refresh
        </button>
      </div>

      <div v-if="allBannersLoading" class="py-6 text-center">
        <span class="material-symbols-outlined text-accent animate-spin text-2xl">progress_activity</span>
      </div>

      <div v-else-if="allBanners.length === 0" class="py-6 text-center rounded-lg" style="background: var(--bg-input)">
        <span class="material-symbols-outlined text-3xl mb-2" style="color: var(--text-muted)">notifications_off</span>
        <p class="text-sm" style="color: var(--text-muted)">Tidak ada banner aktif</p>
      </div>

      <div v-else class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
        <div v-for="b in allBanners" :key="b.id" class="rounded-xl p-4 border transition-all"
             :class="editingBannerId === b.id ? 'border-accent/40' : ''"
             :style="editingBannerId !== b.id ? 'background: var(--bg-input); border-color: var(--border)' : 'background: var(--bg-input); border-color: var(--accent)'">
          <!-- View Mode -->
          <div v-if="editingBannerId !== b.id">
            <div class="flex items-start justify-between gap-3">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1.5">
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full"
                        :class="b.scope === 'global' ? 'bg-purple-500/15 text-purple-400' : 'bg-amber-500/15 text-amber-400'">
                    {{ b.scope === 'global' ? '🌐 Global' : '🔔 ' + (b.device_name || 'TV') }}
                  </span>
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full" :class="b.type === 'emergency' ? 'bg-red-500/15 text-red-400' : b.type === 'announcement' ? 'bg-amber-500/15 text-amber-400' : 'bg-blue-500/15 text-blue-400'">
                    {{ b.type === 'emergency' ? 'Darurat' : b.type === 'announcement' ? 'Pengumuman' : 'Info' }}
                  </span>
                </div>
                <h4 class="font-bold text-sm truncate" style="color: var(--text-heading)">{{ b.title }}</h4>
                <p class="text-xs mt-1 line-clamp-2" style="color: var(--text-muted)" v-html="fixHtmlAssetUrls(b.message)"></p>
                <p class="text-[10px] mt-2" style="color: var(--text-muted)">oleh {{ b.created_by }} · {{ formatBannerTime(b.created_at) }}</p>
              </div>
              <div class="flex gap-1 shrink-0">
                <button @click="startEditBanner(b)" class="p-1.5 rounded-lg hover:bg-accent/10 text-accent cursor-pointer transition-colors" title="Edit">
                  <span class="material-symbols-outlined text-[18px]">edit</span>
                </button>
                <button @click="doDeleteBanner(b.id)" class="p-1.5 rounded-lg hover:bg-red-500/10 text-red-400 cursor-pointer transition-colors" title="Hapus">
                  <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
              </div>
            </div>
          </div>
          <!-- Edit Mode -->
          <div v-else class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold text-accent">✏️ Edit Banner</span>
              <button @click="editingBannerId = null" class="text-xs px-2 py-1 rounded cursor-pointer" style="color: var(--text-muted)">Batal</button>
            </div>
            <input v-model="editBannerForm.title" class="w-full filter-input rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Judul" />
            <textarea v-model="editBannerForm.message" rows="3" class="w-full filter-input rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-accent resize-none" placeholder="Pesan"></textarea>
            <div class="flex items-center gap-2">
              <select v-model="editBannerForm.type" class="filter-input rounded-lg py-2 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-accent cursor-pointer">
                <option value="info">Info</option>
                <option value="announcement">Pengumuman</option>
                <option value="emergency">Darurat</option>
              </select>
              <button @click="doUpdateBanner" :disabled="savingBanner" class="action-btn-primary px-4 py-2 rounded-lg text-sm font-bold cursor-pointer flex items-center gap-2 disabled:opacity-50">
                <span v-if="savingBanner" class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                <span v-else class="material-symbols-outlined text-[16px]">save</span>
                Simpan
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ COMMAND LOG ═══ -->
    <div class="log-card rounded-xl overflow-hidden">
      <div class="log-header px-6 py-4 flex items-center justify-between">
        <h3 class="font-bold flex items-center gap-2" style="color: var(--text-heading)">
          <span class="material-symbols-outlined text-accent text-[20px]">terminal</span>
          Command Log
        </h3>
        <button @click="fetchLogs" class="text-xs font-medium px-3 py-1 rounded-lg cursor-pointer transition-colors" style="color: var(--text-muted); background: var(--bg-input)">Refresh</button>
      </div>
      <div v-if="logsLoading" class="px-6 py-8 text-center">
        <span class="material-symbols-outlined text-accent animate-spin text-2xl">progress_activity</span>
      </div>
      <div v-else class="px-6 py-4 space-y-2 max-h-[300px] overflow-y-auto font-mono text-xs">
        <div v-if="commandLogs.length === 0" class="text-center py-4" style="color: var(--text-muted)">Belum ada command log</div>
        <div v-for="log in commandLogs" :key="log.id" class="flex items-start gap-3 py-1.5 border-b" style="border-color: var(--border)">
          <span style="color: var(--text-muted)">{{ formatLogTime(log.created_at) }}</span>
          <span :class="log.status === 'sent' ? 'text-green-400' : log.status === 'failed' ? 'text-red-400' : 'text-blue-400'">
            {{ log.status === 'sent' ? '✓' : log.status === 'failed' ? '✗' : '→' }}
          </span>
          <span style="color: var(--text-body)">
            <strong class="text-accent">{{ commandLabel(log.command) }}</strong>
            → {{ log.target }}
            <span v-if="log.payload?.path" class="opacity-60"> ({{ log.payload.path }})</span>
            <span v-if="log.payload?.title" class="opacity-60"> — "{{ log.payload.title }}"</span>
          </span>
          <span v-if="log.user" class="ml-auto opacity-40">{{ log.user.name }}</span>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '../../../axios'
import { fixHtmlAssetUrls } from '../../../utils/asset'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'

// ── State ──
const stats = reactive({ online: 0, offline: 0, total: 0 })
const lastSync = ref('—')
const tvDevices = ref([])
const commandLogs = ref([])
const logsLoading = ref(false)

const sending = reactive({
  push: false, broadcast: false, reload: false,
  home: false, banner: false, bannerDismiss: false,
  targetedPush: false, targetedRemove: false,
})

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimeout = null

// ── Quill toolbar for banner ──
const bannerQuillRef = ref(null)
const targetedQuillRef = ref(null)
const bannerQuillToolbar = [
  [{ header: [1, 2, 3, false] }], ['bold', 'italic', 'underline'],
  [{ color: [] }], [{ align: [] }],
  [{ list: 'ordered' }, { list: 'bullet' }], ['clean'],
]

// ── Static index pages (always shown) ──
const staticPages = [
  { name: '🏠 Landing Page', path: '/' },
  { name: '📰 Info Terkini', path: '/info-terkini' },
  { name: '📅 Agenda Harian', path: '/agenda-harian' },
  { name: '📆 Agenda Mingguan', path: '/agenda-mingguan' },
  { name: '🗓️ Agenda Bulanan', path: '/agenda-bulanan' },
  { name: '🎬 Gallery & Video', path: '/gallery-video' },
  { name: '📢 Pengumuman', path: '/pengumuman' },
]

// ── Content dropdown (async search) ──
const contentOptions = ref([...staticPages])
const contentSearching = ref(false)
let contentSearchTimer = null

function onDropdownOpen() {
  // Show static pages when dropdown first opens
  contentOptions.value = [...staticPages]
}

function searchContent(query) {
  clearTimeout(contentSearchTimer)
  if (!query || query.length < 2) {
    contentOptions.value = [...staticPages]
    return
  }
  contentSearchTimer = setTimeout(async () => {
    contentSearching.value = true
    const results = [...staticPages.filter(p => p.name.toLowerCase().includes(query.toLowerCase()))]
    try {
      const endpoints = [
        { url: '/news', prefix: 'info-terkini', emoji: '📰' },
        { url: '/agendas', prefix: 'agenda-harian', emoji: '📅' },
        { url: '/weeklies', prefix: 'agenda-mingguan', emoji: '📆' },
        { url: '/monthlies', prefix: 'agenda-bulanan', emoji: '🗓️' },
        { url: '/galleries', prefix: 'gallery-video', emoji: '🎬' },
        { url: '/announcements', prefix: 'pengumuman', emoji: '📢' },
      ]
      const responses = await Promise.all(
        endpoints.map(ep => api.get(ep.url, { params: { search: query, per_page: 5 } }).catch(() => ({ data: { data: [] } })))
      )
      responses.forEach((res, i) => {
        const list = res.data?.data || res.data || []
        list.forEach(item => {
          results.push({ name: `${endpoints[i].emoji} ${item.title}`, path: `/${endpoints[i].prefix}/${item.id}` })
        })
      })
    } catch { /* silent */ }
    contentOptions.value = results
    contentSearching.value = false
  }, 300)
}

const bannerTypes = [
  { name: '🔴 Darurat', value: 'emergency' },
  { name: '🟡 Pengumuman', value: 'announcement' },
  { name: '🔵 Informasi', value: 'info' },
]

// ── Form state ──
const selectedTV = ref(null)
const selectedContent = ref(null)
const broadcastContent = ref(null)
const reloadTarget = ref(null)
const homeTarget = ref(null)
const bannerTitle = ref('')
const bannerMessage = ref('')
const bannerType = ref(null)
const targetOptions = ref([{ name: 'Semua TV', value: 'all' }])

// ── Targeted banner state ──
const targetedTvIds = ref([])
const targetedTvSearch = ref('')
const targetedBannerTitle = ref('')
const targetedBannerMessage = ref('')
const targetedBannerType = ref(null)
const activeBannerDeviceIds = ref([])
const targetedMode = ref('add') // 'add' or 'delete'

const filteredTvForTargeted = computed(() => {
  const q = targetedTvSearch.value.toLowerCase()
  if (!q) return tvDevices.value
  return tvDevices.value.filter(d => d.name.toLowerCase().includes(q) || (d.location || '').toLowerCase().includes(q))
})

// In delete mode, only show TVs that have active banners
const tvListForMode = computed(() => {
  if (targetedMode.value === 'delete') {
    return filteredTvForTargeted.value.filter(tv => activeBannerDeviceIds.value.includes(tv.id))
  }
  return filteredTvForTargeted.value
})

const isAllTvSelected = computed(() => {
  const list = targetedMode.value === 'delete'
    ? tvListForMode.value
    : filteredTvForTargeted.value.filter(tv => !activeBannerDeviceIds.value.includes(tv.id))
  return list.length > 0 && list.every(tv => targetedTvIds.value.includes(tv.id))
})

function toggleAllTv() {
  const list = targetedMode.value === 'delete'
    ? tvListForMode.value
    : filteredTvForTargeted.value.filter(tv => !activeBannerDeviceIds.value.includes(tv.id))
  if (isAllTvSelected.value) {
    targetedTvIds.value = targetedTvIds.value.filter(id => !list.some(tv => tv.id === id))
  } else {
    const newIds = list.map(tv => tv.id).filter(id => !targetedTvIds.value.includes(id))
    targetedTvIds.value = [...targetedTvIds.value, ...newIds]
  }
}

async function fetchActiveBanners() {
  try {
    const { data } = await api.get('/tv-commands/targeted-banners')
    activeBannerDeviceIds.value = data.map(d => d.device_id)
  } catch { /* silent */ }
}

// ── Active Banners List (all banners card) ──
const allBanners = ref([])
const allBannersLoading = ref(false)
const editingBannerId = ref(null)
const editBannerForm = reactive({ title: '', message: '', type: 'info' })
const savingBanner = ref(false)

async function fetchAllBanners() {
  allBannersLoading.value = true
  try {
    const { data } = await api.get('/tv-commands/all-active-banners')
    allBanners.value = data
  } catch { allBanners.value = [] } finally { allBannersLoading.value = false }
}

function startEditBanner(b) {
  editingBannerId.value = b.id
  editBannerForm.title = b.title
  editBannerForm.message = b.message
  editBannerForm.type = b.type
}

async function doUpdateBanner() {
  savingBanner.value = true
  try {
    await api.put(`/tv-commands/active-banners/${editingBannerId.value}`, editBannerForm)
    showToast('Banner berhasil diperbarui', 'success')
    editingBannerId.value = null
    await Promise.all([fetchAllBanners(), fetchActiveBanners()])
  } catch (e) {
    showToast('Gagal memperbarui banner', 'error')
  } finally { savingBanner.value = false }
}

async function doDeleteBanner(id) {
  try {
    await api.delete(`/tv-commands/active-banners/${id}`)
    showToast('Banner berhasil dihapus', 'success')
    await Promise.all([fetchAllBanners(), fetchActiveBanners()])
  } catch {
    showToast('Gagal menghapus banner', 'error')
  }
}

function formatBannerTime(dt) {
  if (!dt) return '-'
  const d = new Date(dt)
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function pushTargetedBanner() {
  if (!targetedTvIds.value.length || !targetedBannerTitle.value || !targetedBannerMessage.value || !targetedBannerType.value) {
    return showToast('Pilih TV, isi judul, pesan, dan tipe', 'error')
  }
  sending.targetedPush = true
  try {
    const { data } = await api.post('/tv-commands/targeted-banner', {
      action: 'show',
      device_ids: targetedTvIds.value,
      title: targetedBannerTitle.value,
      message: targetedBannerMessage.value,
      type: targetedBannerType.value.value,
    })
    showToast(data.message, data.success ? 'success' : 'error')
    targetedTvIds.value = []
    fetchActiveBanners()
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal push banner', 'error')
  }
  sending.targetedPush = false
}

async function removeTargetedBanner() {
  if (!targetedTvIds.value.length) return
  sending.targetedRemove = true
  try {
    const { data } = await api.post('/tv-commands/targeted-banner', {
      action: 'dismiss',
      device_ids: targetedTvIds.value,
    })
    showToast(data.message, data.success ? 'success' : 'error')
    targetedTvIds.value = []
    fetchActiveBanners()
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal hapus banner', 'error')
  }
  sending.targetedRemove = false
}

// ── Toast helper ──
function showToast(message, type = 'success') {
  clearTimeout(toastTimeout)
  toast.show = true
  toast.message = message
  toast.type = type
  toastTimeout = setTimeout(() => { toast.show = false }, 4000)
}

// ── Fetch data ──
async function fetchStats() {
  try {
    const { data } = await api.get('/tv-devices/stats')
    stats.online = data.online
    stats.offline = data.offline
    stats.total = data.total
    lastSync.value = new Date().toLocaleTimeString('id-ID')
  } catch { /* silent */ }
}

async function fetchDevices() {
  try {
    const { data } = await api.get('/tv-devices', { params: { per_page: 100 } })
    const devices = data.data || data
    tvDevices.value = devices
    targetOptions.value = [
      { name: 'Semua TV', value: 'all' },
      ...devices.map(d => ({ name: d.name, value: String(d.id) }))
    ]
  } catch { /* silent */ }
}

async function fetchLogs() {
  logsLoading.value = true
  try {
    const { data } = await api.get('/tv-commands/logs', { params: { limit: 30 } })
    commandLogs.value = data
  } catch { /* silent */ }
  logsLoading.value = false
}

// ── Command actions ──
async function pushContent() {
  if (!selectedTV.value || !selectedContent.value) return showToast('Pilih TV dan halaman terlebih dahulu', 'error')
  sending.push = true
  try {
    const { data } = await api.post('/tv-commands/push', {
      device_id: selectedTV.value.id,
      path: selectedContent.value.path,
      label: selectedContent.value.name,
    })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal mengirim', 'error')
  }
  sending.push = false
}

async function broadcastToAll() {
  if (!broadcastContent.value) return showToast('Pilih halaman terlebih dahulu', 'error')
  sending.broadcast = true
  try {
    const { data } = await api.post('/tv-commands/broadcast', {
      path: broadcastContent.value.path,
      label: broadcastContent.value.name,
    })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal broadcast', 'error')
  }
  sending.broadcast = false
}

async function forceReload() {
  if (!reloadTarget.value) return showToast('Pilih target terlebih dahulu', 'error')
  sending.reload = true
  try {
    const { data } = await api.post('/tv-commands/reload', { target: reloadTarget.value.value })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal reload', 'error')
  }
  sending.reload = false
}

async function forceHome() {
  if (!homeTarget.value) return showToast('Pilih target terlebih dahulu', 'error')
  sending.home = true
  try {
    const { data } = await api.post('/tv-commands/home', { target: homeTarget.value.value })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal kirim', 'error')
  }
  sending.home = false
}

async function showBanner() {
  if (!bannerTitle.value || !bannerMessage.value || !bannerType.value) {
    return showToast('Isi judul, pesan, dan tipe banner', 'error')
  }
  sending.banner = true
  try {
    const { data } = await api.post('/tv-commands/banner', {
      action: 'show',
      title: bannerTitle.value,
      message: bannerMessage.value,
      type: bannerType.value.value,
    })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal tampilkan banner', 'error')
  }
  sending.banner = false
}

async function dismissBanner() {
  sending.bannerDismiss = true
  try {
    const { data } = await api.post('/tv-commands/banner', { action: 'dismiss' })
    showToast(data.message, data.success ? 'success' : 'error')
    fetchLogs()
  } catch (e) {
    showToast(e.response?.data?.message || 'Gagal hapus banner', 'error')
  }
  sending.bannerDismiss = false
}

// ── Helpers ──
function formatLogTime(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function commandLabel(cmd) {
  const map = {
    navigate: 'Navigate',
    reload: 'Reload',
    home: 'Home',
    banner_show: 'Banner',
    banner_dismiss: 'Dismiss Banner',
  }
  return map[cmd] || cmd
}

// ── Init ──
onMounted(() => {
  fetchStats()
  fetchDevices()
  fetchLogs()
  fetchActiveBanners()
  fetchAllBanners()
})
</script>

<style scoped>
.status-bar { background: var(--bg-card); border: 1px solid var(--border); }
.action-card { background: var(--bg-card); border: 1px solid var(--border); transition: border-color 0.3s, box-shadow 0.3s; }
.action-card:hover { border-color: rgba(251, 191, 36, 0.2); box-shadow: 0 0 20px rgba(251, 191, 36, 0.08); }
.info-box { background: var(--bg-input); border: 1px solid var(--border); }
.log-card { background: var(--bg-card); border: 1px solid var(--border); }
.log-header { border-bottom: 1px solid var(--border); }

.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

.action-btn-primary { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 15px rgba(251, 191, 36, 0.3); }
.action-btn-primary:hover { box-shadow: 0 0 25px rgba(251, 191, 36, 0.5); }

.action-btn-blue { background: #3b82f6; color: white; box-shadow: 0 0 15px rgba(59, 130, 246, 0.3); }
.action-btn-blue:hover { box-shadow: 0 0 25px rgba(59, 130, 246, 0.5); }

.banner-card-wrapper { overflow: visible; position: relative; z-index: 1; }
.banner-card-wrapper .ql-toolbar { position: relative; z-index: 2; }
.quill-banner :deep(.ql-picker-options) { z-index: 10 !important; }

.action-btn-green { background: #22c55e; color: white; box-shadow: 0 0 15px rgba(34, 197, 94, 0.3); }
.action-btn-green:hover { box-shadow: 0 0 25px rgba(34, 197, 94, 0.5); }

.action-btn-purple { background: #a855f7; color: white; box-shadow: 0 0 15px rgba(168, 85, 247, 0.3); }
.action-btn-purple:hover { box-shadow: 0 0 25px rgba(168, 85, 247, 0.5); }

.action-btn-red { background: #ef4444; color: white; box-shadow: 0 0 15px rgba(239, 68, 68, 0.3); }
.action-btn-red:hover { box-shadow: 0 0 25px rgba(239, 68, 68, 0.5); }

.action-btn-dismiss { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
.action-btn-dismiss:hover { border-color: #ef4444; color: #ef4444; }

/* Toast */
.toast-success { background: #065f46; border-color: #10b981; color: #6ee7b7; }
.toast-error { background: #7f1d1d; border-color: #ef4444; color: #fca5a5; }
.toast-enter-active { animation: toastIn 0.3s ease-out; }
.toast-leave-active { animation: toastOut 0.2s ease-in; }
@keyframes toastIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
@keyframes toastOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100px); } }

/* Quill dark theme */
.quill-dark :deep(.ql-toolbar) { background: var(--bg-input); border-color: var(--border) !important; border-radius: 12px 12px 0 0; }
.quill-dark :deep(.ql-toolbar .ql-stroke) { stroke: var(--text-muted); }
.quill-dark :deep(.ql-toolbar .ql-fill) { fill: var(--text-muted); }
.quill-dark :deep(.ql-toolbar .ql-picker-label) { color: var(--text-muted); }
.quill-dark :deep(.ql-toolbar button:hover .ql-stroke), .quill-dark :deep(.ql-toolbar .ql-active .ql-stroke) { stroke: var(--color-accent) !important; }
.quill-dark :deep(.ql-toolbar button:hover .ql-fill), .quill-dark :deep(.ql-toolbar .ql-active .ql-fill) { fill: var(--color-accent) !important; }
.quill-dark :deep(.ql-toolbar button:hover), .quill-dark :deep(.ql-toolbar .ql-active) { color: var(--color-accent) !important; }
.quill-dark :deep(.ql-container) { background: var(--bg-input); border-color: var(--border) !important; border-radius: 0 0 12px 12px; color: var(--text-heading); font-family: 'Inter', sans-serif; font-size: 14px; min-height: 120px; }
.quill-dark :deep(.ql-editor) { min-height: 120px; }
.quill-dark :deep(.ql-editor.ql-blank::before) { color: var(--text-muted); font-style: normal; }
.quill-dark :deep(.ql-picker-options) { background: var(--bg-card) !important; border-color: var(--border) !important; }
.quill-dark :deep(.ql-picker-item) { color: var(--text-body) !important; }
.quill-dark :deep(.ql-picker-item:hover) { color: var(--color-accent) !important; }

/* TV checklist */
.tv-checklist { background: var(--bg-input); border: 1px solid var(--border); }
.tv-check-item { background: transparent; border: 1px solid transparent; }
.tv-check-item:hover { background: rgba(251, 191, 36, 0.05); border-color: rgba(251, 191, 36, 0.1); }
.tv-check-selected { background: rgba(251, 191, 36, 0.1) !important; border-color: rgba(251, 191, 36, 0.25) !important; }
.tv-checklist::-webkit-scrollbar { width: 4px; }
.tv-checklist::-webkit-scrollbar-track { background: transparent; }
.tv-checklist::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.tv-checklist::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }
</style>
