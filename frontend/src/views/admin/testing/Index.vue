<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ WEBSOCKET TESTING ═══ -->
    <div class="settings-card rounded-xl overflow-hidden">
      <div class="card-header px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-accent text-[20px]">cable</span>
          <h3 class="font-bold" style="color: var(--text-heading)">Testing WebSocket</h3>
        </div>
        <span class="text-xs font-mono px-3 py-1 rounded-full" :class="statusBadgeClass">
          {{ statusLabel }}
        </span>
      </div>

      <div class="px-6 py-5 space-y-6">

        <!-- Connection Info -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Server Info</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Konfigurasi WebSocket yang digunakan</p>
          </div>
          <div class="setting-control">
            <div class="space-y-2">
              <div class="flex items-center gap-2 text-sm">
                <span class="font-bold min-w-[80px]" style="color: var(--text-muted)">Host</span>
                <code class="font-mono px-2 py-0.5 rounded" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">{{ wsHost }}</code>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span class="font-bold min-w-[80px]" style="color: var(--text-muted)">Port</span>
                <code class="font-mono px-2 py-0.5 rounded" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">{{ wsPort }}</code>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span class="font-bold min-w-[80px]" style="color: var(--text-muted)">Key</span>
                <code class="font-mono px-2 py-0.5 rounded" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">{{ wsKey }}</code>
              </div>
              <div class="flex items-center gap-2 text-sm">
                <span class="font-bold min-w-[80px]" style="color: var(--text-muted)">Transport</span>
                <code class="font-mono px-2 py-0.5 rounded" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">ws (non-TLS)</code>
              </div>
            </div>
          </div>
        </div>

        <!-- Connection Status Visual -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Status Koneksi</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Status koneksi WebSocket saat ini</p>
          </div>
          <div class="setting-control">
            <div class="status-panel rounded-xl p-5" style="background: var(--bg-input); border: 1px solid var(--border)">
              <!-- Connection Visual -->
              <div class="flex items-center gap-4 mb-4">
                <div class="relative">
                  <div class="w-14 h-14 rounded-full flex items-center justify-center" :class="statusIconBg">
                    <span class="material-symbols-outlined text-2xl" :class="statusIconColor">{{ statusIcon }}</span>
                  </div>
                  <span v-if="wsState === 'connected'" class="absolute -top-0.5 -right-0.5 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500 border-2" style="border-color: var(--bg-input)"></span>
                  </span>
                </div>
                <div>
                  <p class="text-lg font-bold" style="color: var(--text-heading)">{{ statusTitle }}</p>
                  <p class="text-xs" style="color: var(--text-muted)">{{ statusDescription }}</p>
                </div>
              </div>

              <!-- Latency -->
              <div v-if="wsState === 'connected'" class="flex items-center gap-3 p-3 rounded-lg mb-4" style="background: var(--bg-card); border: 1px solid var(--border)">
                <span class="material-symbols-outlined text-green-400 text-lg">speed</span>
                <div class="flex-1">
                  <p class="text-xs font-bold" style="color: var(--text-muted)">Latency</p>
                  <p class="text-sm font-bold font-mono" style="color: var(--text-heading)">{{ latency !== null ? latency + ' ms' : 'Mengukur...' }}</p>
                </div>
                <button @click="measureLatency" class="text-xs px-3 py-1.5 rounded-lg font-bold cursor-pointer transition-all" style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border)">
                  <span class="material-symbols-outlined text-[14px] align-text-bottom">refresh</span> Ping
                </button>
              </div>

              <!-- Actions -->
              <div class="flex flex-wrap gap-3">
                <button v-if="wsState !== 'connected' && wsState !== 'connecting'"
                        @click="connectWebSocket"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98]"
                        style="background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 15px rgba(251, 191, 36, 0.3);">
                  <span class="material-symbols-outlined text-[18px]">power</span>
                  Hubungkan
                </button>
                <button v-if="wsState === 'connecting'"
                        disabled
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold opacity-60 cursor-not-allowed"
                        style="background: var(--color-accent); color: var(--text-btn);">
                  <span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                  Menghubungkan...
                </button>
                <button v-if="wsState === 'connected'"
                        @click="disconnectWebSocket"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all active:scale-[0.98]"
                        style="background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                  <span class="material-symbols-outlined text-[18px]">power_off</span>
                  Putuskan
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Channel Subscribe Test -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Test Channel</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Subscribe ke channel dan pantau event masuk</p>
          </div>
          <div class="setting-control">
            <div class="flex items-center gap-3 mb-4">
              <input v-model="channelName" class="setting-input flex-1 rounded-lg py-2.5 px-4 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-accent" placeholder="nama-channel" />
              <button @click="subscribeChannel"
                      :disabled="wsState !== 'connected' || !channelName"
                      class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold cursor-pointer transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                      style="background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border);">
                <span class="material-symbols-outlined text-[16px]">podcasts</span>
                Subscribe
              </button>
            </div>
            <div v-if="subscribedChannel" class="flex items-center gap-2 px-3 py-2 rounded-lg mb-3 text-sm" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3);">
              <span class="material-symbols-outlined text-green-400 text-[16px]">check_circle</span>
              <span class="text-green-400 font-bold">Subscribed:</span>
              <code class="font-mono" style="color: var(--text-heading)">{{ subscribedChannel }}</code>
              <button @click="unsubscribeChannel" class="ml-auto text-red-400 hover:text-red-300 cursor-pointer transition-colors">
                <span class="material-symbols-outlined text-[16px]">close</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Event Log -->
        <div class="setting-row">
          <div class="setting-label">
            <h4 class="text-sm font-bold" style="color: var(--text-heading)">Event Log</h4>
            <p class="text-xs mt-0.5" style="color: var(--text-muted)">Log event yang diterima secara realtime</p>
          </div>
          <div class="setting-control">
            <div class="rounded-xl overflow-hidden" style="background: var(--bg-input); border: 1px solid var(--border)">
              <!-- Log header -->
              <div class="flex items-center justify-between px-4 py-2.5" style="border-bottom: 1px solid var(--border)">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-accent text-[16px]">terminal</span>
                  <span class="text-xs font-bold" style="color: var(--text-heading)">Console</span>
                  <span class="text-[10px] px-2 py-0.5 rounded-full font-bold" style="background: var(--bg-card); color: var(--text-muted); border: 1px solid var(--border)">{{ eventLogs.length }}</span>
                </div>
                <button @click="clearLogs" class="text-xs px-2.5 py-1 rounded-lg font-bold cursor-pointer transition-all" style="background: var(--bg-card); color: var(--text-muted); border: 1px solid var(--border)">
                  Clear
                </button>
              </div>
              <!-- Log content -->
              <div ref="logContainer" class="log-container p-4 font-mono text-xs space-y-1.5 max-h-[300px] overflow-y-auto">
                <div v-if="eventLogs.length === 0" class="text-center py-8">
                  <span class="material-symbols-outlined text-3xl mb-2" style="color: var(--text-muted)">inbox</span>
                  <p style="color: var(--text-muted)">Belum ada event. Hubungkan WebSocket dan subscribe channel untuk mulai menerima event.</p>
                </div>
                <div v-for="(log, idx) in eventLogs" :key="idx" class="flex gap-2 items-start log-entry py-1 px-2 rounded" style="background: var(--bg-card)">
                  <span class="text-[10px] font-mono shrink-0 mt-0.5" style="color: var(--text-muted)">{{ log.time }}</span>
                  <span class="material-symbols-outlined text-[14px] shrink-0 mt-0.5" :class="logIconClass(log.type)">{{ logIcon(log.type) }}</span>
                  <span :class="logTextClass(log.type)" class="break-all">{{ log.message }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue'
import echo from '../../../echo'

// ── WebSocket Config ──
const wsHost = import.meta.env.VITE_REVERB_HOST || '127.0.0.1'
const wsPort = Number(import.meta.env.VITE_REVERB_PORT) || 8080
const wsKey = import.meta.env.VITE_REVERB_APP_KEY || 'access-key'

// ── State ──
const wsState = ref('disconnected') // disconnected | connecting | connected | error
const latency = ref(null)
const channelName = ref('tv-devices')
const subscribedChannel = ref('')
const eventLogs = ref([])
const logContainer = ref(null)

// ── Computed Status ──
const statusLabel = computed(() => {
  const map = { disconnected: 'Disconnected', connecting: 'Connecting...', connected: 'Connected', error: 'Error' }
  return map[wsState.value] || 'Unknown'
})

const statusBadgeClass = computed(() => {
  const map = {
    disconnected: 'status-badge-offline',
    connecting: 'status-badge-connecting',
    connected: 'status-badge-online',
    error: 'status-badge-error'
  }
  return map[wsState.value]
})

const statusIcon = computed(() => {
  const map = { disconnected: 'wifi_off', connecting: 'sync', connected: 'wifi', error: 'error' }
  return map[wsState.value]
})

const statusIconBg = computed(() => {
  const map = {
    disconnected: 'bg-slate-500/10',
    connecting: 'bg-amber-500/10',
    connected: 'bg-green-500/10',
    error: 'bg-red-500/10'
  }
  return map[wsState.value]
})

const statusIconColor = computed(() => {
  const map = {
    disconnected: 'text-slate-400',
    connecting: 'text-amber-400 animate-spin',
    connected: 'text-green-400',
    error: 'text-red-400'
  }
  return map[wsState.value]
})

const statusTitle = computed(() => {
  const map = {
    disconnected: 'Tidak Terhubung',
    connecting: 'Menghubungkan...',
    connected: 'Terhubung',
    error: 'Gagal Terhubung'
  }
  return map[wsState.value]
})

const statusDescription = computed(() => {
  const map = {
    disconnected: 'WebSocket belum dikoneksikan ke server',
    connecting: `Mencoba koneksi ke ${wsHost}:${wsPort}...`,
    connected: `Terhubung ke ${wsHost}:${wsPort}`,
    error: 'Tidak dapat terhubung ke server WebSocket'
  }
  return map[wsState.value]
})

// ── Log Helpers ──
function addLog(type, message) {
  const now = new Date()
  const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  eventLogs.value.push({ type, message, time })
  // Keep max 100 logs
  if (eventLogs.value.length > 100) eventLogs.value.shift()
  // Auto-scroll to bottom
  nextTick(() => {
    if (logContainer.value) {
      logContainer.value.scrollTop = logContainer.value.scrollHeight
    }
  })
}

function clearLogs() {
  eventLogs.value = []
}

function logIcon(type) {
  const map = { info: 'info', success: 'check_circle', error: 'error', event: 'electric_bolt', warn: 'warning' }
  return map[type] || 'circle'
}
function logIconClass(type) {
  const map = { info: 'text-blue-400', success: 'text-green-400', error: 'text-red-400', event: 'text-amber-400', warn: 'text-amber-400' }
  return map[type] || 'text-slate-400'
}
function logTextClass(type) {
  const map = { info: 'text-blue-300', success: 'text-green-300', error: 'text-red-300', event: 'text-amber-300', warn: 'text-amber-300' }
  return map[type] || ''
}

// ── WebSocket Connection ──
function connectWebSocket() {
  wsState.value = 'connecting'
  addLog('info', `Menghubungkan ke ws://${wsHost}:${wsPort}...`)

  try {
    const connector = echo.connector
    const pusher = connector?.pusher

    if (!pusher) {
      wsState.value = 'error'
      addLog('error', 'Echo/Pusher instance tidak ditemukan')
      return
    }

    // Bind state change events
    pusher.connection.bind('connected', () => {
      wsState.value = 'connected'
      addLog('success', `WebSocket terhubung! Socket ID: ${pusher.connection.socket_id}`)
      measureLatency()
    })

    pusher.connection.bind('connecting', () => {
      wsState.value = 'connecting'
      addLog('info', 'Mencoba koneksi...')
    })

    pusher.connection.bind('disconnected', () => {
      wsState.value = 'disconnected'
      addLog('warn', 'WebSocket terputus')
      latency.value = null
    })

    pusher.connection.bind('error', (err) => {
      wsState.value = 'error'
      addLog('error', `Error: ${err?.error?.data?.message || err?.message || JSON.stringify(err)}`)
    })

    pusher.connection.bind('unavailable', () => {
      wsState.value = 'error'
      addLog('error', 'Server WebSocket tidak tersedia. Pastikan `php artisan reverb:start` sudah berjalan.')
    })

    // Check if already connected
    if (pusher.connection.state === 'connected') {
      wsState.value = 'connected'
      addLog('success', `Sudah terhubung! Socket ID: ${pusher.connection.socket_id}`)
      measureLatency()
    } else {
      // Force connect
      pusher.connect()
    }
  } catch (err) {
    wsState.value = 'error'
    addLog('error', `Exception: ${err.message}`)
  }
}

function disconnectWebSocket() {
  try {
    const pusher = echo.connector?.pusher
    if (pusher) {
      pusher.disconnect()
    }
    // Unsubscribe too
    if (subscribedChannel.value) {
      unsubscribeChannel()
    }
    wsState.value = 'disconnected'
    latency.value = null
    addLog('info', 'WebSocket diputuskan secara manual')
  } catch (err) {
    addLog('error', `Gagal disconnect: ${err.message}`)
  }
}

// ── Latency Measurement ──
function measureLatency() {
  const pusher = echo.connector?.pusher
  if (!pusher || pusher.connection.state !== 'connected') {
    latency.value = null
    return
  }

  const start = performance.now()

  // Use a simple ping by subscribing to a temp private channel
  // Actually for Reverb/Pusher, we measure round-trip via a pong-like mechanism
  // Simplest: measure the time echo takes to subscribe/unsubscribe
  try {
    const tempChannel = `ping-test-${Date.now()}`
    const ch = pusher.subscribe(tempChannel)
    ch.bind('pusher:subscription_succeeded', () => {
      const end = performance.now()
      latency.value = Math.round(end - start)
      pusher.unsubscribe(tempChannel)
      addLog('info', `Latency: ${latency.value}ms`)
    })
    // Timeout fallback
    setTimeout(() => {
      if (latency.value === null) {
        const end = performance.now()
        latency.value = Math.round(end - start)
        try { pusher.unsubscribe(tempChannel) } catch {}
      }
    }, 3000)
  } catch {
    latency.value = null
  }
}

// ── Channel Subscription ──
function subscribeChannel() {
  if (!channelName.value || wsState.value !== 'connected') return

  // Unsubscribe from previous
  if (subscribedChannel.value) {
    unsubscribeChannel()
  }

  const name = channelName.value
  addLog('info', `Subscribing ke channel: ${name}`)

  try {
    const ch = echo.channel(name)
    subscribedChannel.value = name

    // Listen for all events via listenToAll (or .listen for specific events)
    // Echo's listenToAll catches everything
    ch.listenToAll((eventName, data) => {
      addLog('event', `[${name}] ${eventName}: ${JSON.stringify(data)}`)
    })

    addLog('success', `Berhasil subscribe ke channel: ${name}`)
  } catch (err) {
    addLog('error', `Gagal subscribe: ${err.message}`)
  }
}

function unsubscribeChannel() {
  if (!subscribedChannel.value) return
  try {
    echo.leave(subscribedChannel.value)
    addLog('info', `Unsubscribed dari channel: ${subscribedChannel.value}`)
    subscribedChannel.value = ''
  } catch (err) {
    addLog('error', `Gagal unsubscribe: ${err.message}`)
  }
}

// ── Lifecycle ──
onMounted(() => {
  // Auto-detect current state
  try {
    const pusher = echo.connector?.pusher
    if (pusher && pusher.connection.state === 'connected') {
      wsState.value = 'connected'
      addLog('success', `WebSocket sudah terhubung (auto-detect). Socket ID: ${pusher.connection.socket_id}`)
    }
  } catch {}
})

onUnmounted(() => {
  // Clean up channel subscription but don't disconnect WebSocket
  if (subscribedChannel.value) {
    try { echo.leave(subscribedChannel.value) } catch {}
  }
})
</script>

<style scoped>
.settings-card { background: var(--bg-card); border: 1px solid var(--border); }
.card-header { border-bottom: 1px solid var(--border); }

.setting-row { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px) {
  .setting-row { flex-direction: row; align-items: flex-start; }
  .setting-label { width: 280px; flex-shrink: 0; padding-top: 0.5rem; }
  .setting-control { flex: 1; }
}

.setting-input {
  background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading);
}
.setting-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

/* Status badges */
.status-badge-online {
  background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3);
}
.status-badge-offline {
  background: var(--bg-input); color: var(--text-muted); border: 1px solid var(--border);
}
.status-badge-connecting {
  background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);
  animation: pulse-badge 1.5s ease-in-out infinite;
}
.status-badge-error {
  background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);
}

@keyframes pulse-badge {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

/* Log container */
.log-container {
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}
.log-container::-webkit-scrollbar {
  width: 6px;
}
.log-container::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 999px;
}
.log-container::-webkit-scrollbar-track {
  background: transparent;
}

.log-entry {
  border: 1px solid var(--border);
  transition: background-color 0.2s ease;
}
.log-entry:hover {
  background: var(--hover-nav-bg) !important;
}
</style>
