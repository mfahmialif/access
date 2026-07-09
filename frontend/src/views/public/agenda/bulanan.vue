<template>
  <div class="w-full min-h-full flex flex-col">
    <!-- ═══════ AMBIENT ═══════ -->
      <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-accent/5 rounded-full blur-[120px] pointer-events-none z-[-1]"></div>
      <div class="fixed bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-blue-500/5 rounded-full blur-[100px] pointer-events-none z-[-1]"></div>

  <!-- ═══════ MAIN CONTENT ═══════ -->
  <div class="flex-1 flex flex-col md:flex-row portrait:flex-col overflow-hidden p-2 md:p-6 gap-3 md:gap-6 w-full h-full font-display antialiased"
       :class="!isDark ? 'text-slate-800' : 'text-white'">
    
    <!-- ═══ LEFT: CALENDAR GRID (70%) ═══ -->
      <section class="flex-1 md:flex-[0.7] flex flex-col gap-4 h-full">
        <!-- Calendar Header -->
        <div class="rounded-2xl p-3 md:p-5 flex items-center justify-between"
             :class="!isDark ? 'bg-white border border-slate-200 shadow-sm' : 'glass-panel'">
          <div>
            <h2 class="text-xl md:text-3xl font-bold tracking-tight" :class="!isDark ? 'text-slate-800' : 'text-white'">{{ monthYearDisplay }}</h2>
            <p class="text-sm md:text-base font-medium mt-1" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ hijriMonthDisplay }}</p>
          </div>
          <div class="flex gap-2">
            <button @click="prevMonth" class="size-8 md:size-10 rounded-xl flex items-center justify-center transition-colors border"
                    :class="!isDark ? 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200' : 'bg-white/5 hover:bg-white/10 text-white border-white/5'">
              <span class="material-symbols-outlined">chevron_left</span>
            </button>
            <button @click="nextMonth" class="size-8 md:size-10 rounded-xl flex items-center justify-center transition-colors border"
                    :class="!isDark ? 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200' : 'bg-white/5 hover:bg-white/10 text-white border-white/5'">
              <span class="material-symbols-outlined">chevron_right</span>
            </button>
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="rounded-2xl flex-1 p-2 md:p-5 flex flex-col"
             :class="!isDark ? 'bg-white border border-slate-200 shadow-sm' : 'glass-panel'">
          <!-- Days Header -->
          <div class="grid grid-cols-7 mb-3 border-b pb-3" :class="!isDark ? 'border-slate-200' : 'border-white/10'">
            <div v-for="d in dayHeaders" :key="d.name"
                 :class="['text-center font-bold text-[10px] md:text-sm tracking-wider uppercase', d.isJumat ? (!isDark ? 'text-amber-600' : 'text-accent') : (!isDark ? 'text-slate-500' : 'text-slate-400')]">
              {{ d.name }}
            </div>
          </div>
          <!-- Days Grid -->
          <div class="grid grid-cols-7 gap-1 md:gap-2 flex-1" style="grid-auto-rows: 1fr">
            <div v-for="(cell, ci) in calendarCells" :key="ci"
                 @click="cell.day && cell.isCurrentMonth ? selectDate(cell.day) : null"
                 :class="[
                   'relative p-1 md:p-2 rounded-lg md:rounded-xl flex flex-col items-start justify-start transition-all duration-300',
                   !cell.isCurrentMonth ? (!isDark ? 'text-slate-400' : 'text-slate-600') : '',
                   cell.isCurrentMonth && !cell.isSelected ? (!isDark ? 'bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 cursor-pointer' : 'bg-[#0b1711]/40 hover:bg-[#0b1711]/60 text-white border border-white/5 cursor-pointer') : '',
                   cell.isSelected ? (!isDark ? 'bg-amber-50 text-slate-800 border-2 border-amber-400 shadow-sm cursor-pointer' : 'calendar-cell-active text-white cursor-pointer') : ''
                 ]">
              <span :class="['font-bold', cell.isSelected ? (!isDark ? 'text-sm md:text-xl text-amber-600' : 'text-sm md:text-xl text-accent') : 'text-xs md:text-base']">{{ cell.day || '' }}</span>
              <!-- Event dots -->
              <div v-if="cell.events && cell.events.length" class="flex gap-1 mt-1">
                <div v-for="(ev, ei) in cell.events.slice(0, 3)" :key="ei"
                     :class="['h-1.5 w-1.5 rounded-full', categoryDotColor(ev.category)]"></div>
                <div v-if="cell.events.length > 3" class="text-[8px] ml-0.5" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">+{{ cell.events.length - 3 }}</div>
              </div>
              <span v-if="cell.events && cell.events.length && cell.isCurrentMonth"
                    :class="['material-symbols-outlined absolute bottom-1 right-1 text-lg', cell.isSelected ? (!isDark ? 'text-amber-500 animate-pulse' : 'text-accent animate-pulse') : (!isDark ? 'text-slate-300' : 'text-white/20')]">
                {{ cell.events[0].icon || 'event' }}
              </span>
              <span v-if="cell.isToday && cell.isSelected" class="text-[8px] uppercase font-bold tracking-wider mt-0.5" :class="!isDark ? 'text-amber-600' : 'text-accent'">Hari Ini</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ═══ RIGHT: EVENTS PANEL (30%) ═══ -->
      <aside class="hidden md:flex portrait:hidden flex-[0.3] min-h-[200px] md:min-h-0 flex-col h-full gap-4">
        <div class="h-full rounded-2xl flex flex-col overflow-hidden" :class="!isDark ? 'bg-white border border-slate-200 shadow-sm' : 'events-panel'">

          <!-- ─── Selected Date Events ─── -->
          <template v-if="selectedDateEvents.length > 0 && !showAllMonth">
            <div class="p-5 border-b flex flex-col gap-2" :class="!isDark ? 'border-slate-200 bg-slate-50' : 'border-white/10 bg-[#0b1711]/50'">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2" :class="!isDark ? 'text-slate-800' : 'text-white'">
                  <span class="material-symbols-outlined" :class="!isDark ? 'text-amber-500' : 'text-accent'">event_note</span>
                  {{ selectedDay }} {{ months[currentMonth] }}
                </h3>
                <span class="text-sm font-medium" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ selectedDateEvents.length }} event</span>
              </div>
              <button @click="showAllMonth = true" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer"
                      :class="!isDark ? 'bg-amber-50 border-amber-200 text-amber-600 hover:bg-amber-100' : 'bg-accent/10 border-accent/30 text-accent hover:bg-accent/20'">
                <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                Event Bulan Ini
              </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
              <!-- Grouped by time -->
              <div v-for="group in selectedDateGroups" :key="group.time">
                <div class="flex items-center gap-2 mb-2">
                  <span class="font-bold text-lg font-mono" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ group.time }}</span>
                  <div class="h-px flex-1" :class="!isDark ? 'bg-slate-200' : 'bg-white/10'"></div>
                  <span v-if="group.items.length > 1" class="text-[10px] font-bold" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">{{ group.items.length }} kegiatan</span>
                </div>
                <div v-for="(item, iIdx) in group.items" :key="item.id"
                     @click="router.push({ name: 'DetailMonthly', params: { id: item.id } })"
                     :class="[
                       'rounded-xl p-4 cursor-pointer transition-all duration-300 hover:scale-[1.02]',
                       !isDark ? 'bg-white border border-slate-200 hover:border-amber-400 shadow-sm' : 'bg-linear-to-br from-blue-900/40 to-[#0b1711] border border-blue-500/20 hover:border-accent/50',
                       iIdx > 0 ? 'mt-2' : ''
                     ]">
                  <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border"
                         :class="!isDark ? 'bg-amber-50 border-amber-200' : 'bg-accent/10 border-accent/30'">
                      <span class="material-symbols-outlined text-xl" :class="!isDark ? 'text-amber-500' : 'text-accent'">{{ item.icon || 'event' }}</span>
                    </div>
                    <div class="flex flex-col flex-1 min-w-0">
                      <h4 class="text-base font-bold leading-tight truncate" :class="!isDark ? 'text-slate-800' : 'text-white'">{{ item.title }}</h4>
                      <div class="flex items-center gap-2 mt-1">
                        <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                        <span class="text-xs flex items-center gap-1" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">
                          <span class="material-symbols-outlined text-[12px]">location_on</span>
                          {{ item.location || '-' }}
                        </span>
                      </div>
                      <span v-if="item.teacher" class="text-xs mt-0.5 flex items-center gap-1" :class="!isDark ? 'text-slate-500' : 'text-blue-200/60'">
                        <span class="material-symbols-outlined text-[12px]">person</span>
                        {{ item.teacher }}
                      </span>
                    </div>
                    <div class="flex items-center">
                      <span class="material-symbols-outlined text-lg" :class="!isDark ? 'text-slate-300' : 'text-white/30'">chevron_right</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- ─── All month events (fallback or toggled) ─── -->
          <template v-else>
            <div class="p-5 border-b flex flex-col gap-2" :class="!isDark ? 'border-slate-200 bg-slate-50' : 'border-white/10 bg-[#0b1711]/50'">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold flex items-center gap-2" :class="!isDark ? 'text-slate-800' : 'text-white'">
                  <span class="material-symbols-outlined" :class="!isDark ? 'text-amber-500' : 'text-accent'">event_upcoming</span>
                  Event Bulan Ini
                </h3>
                <span class="text-sm font-medium" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ monthEventGroups.reduce((a, g) => a + g.items.length, 0) }} event</span>
              </div>
              <button v-if="showAllMonth && selectedDateEvents.length > 0" @click="showAllMonth = false" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer"
                      :class="!isDark ? 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' : 'bg-white/5 border-white/10 text-white/70 hover:bg-white/10'">
                <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                Kembali ke {{ selectedDay }} {{ months[currentMonth] }}
              </button>
              <p v-if="!showAllMonth && selectedDateEvents.length === 0" class="text-xs" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">Tidak ada event pada tanggal {{ selectedDay }}.</p>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
              <div v-for="group in monthEventGroups" :key="group.key"
                   @click="router.push({ name: 'DetailMonthly', params: { id: group.items[0].id } })"
                   :class="[
                     'rounded-xl p-4 cursor-pointer',
                     group.isToday
                       ? (!isDark ? 'bg-amber-50 border border-amber-300 relative overflow-hidden' : 'bg-linear-to-br from-blue-900/60 to-[#0b1711] border border-accent/40 relative overflow-hidden')
                       : (!isDark ? 'bg-white border border-slate-200 hover:bg-slate-50 shadow-sm' : 'bg-[#0b1711]/40 border border-white/5 hover:bg-[#0b1711]/60'),
                     group.isPast ? 'opacity-50' : ''
                   ]">
                <div v-if="group.isToday" class="absolute top-0 right-0 p-2 opacity-10">
                  <span class="material-symbols-outlined text-7xl" :class="!isDark ? 'text-amber-500' : 'text-accent'">{{ group.items[0].icon || 'event' }}</span>
                </div>
                <div class="flex gap-3 relative z-10">
                  <div class="flex flex-col items-center justify-center rounded-lg w-14 h-14 shrink-0 border"
                       :class="!isDark ? 'bg-slate-50 border-slate-200' : 'bg-white/5 border-white/5'">
                    <span class="text-[10px] font-bold uppercase" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">{{ monthShort }}</span>
                    <span class="text-xl font-bold leading-tight" :class="!isDark ? 'text-slate-800' : 'text-white'">{{ group.dateNum }}</span>
                  </div>
                  <div class="flex flex-col justify-center flex-1 min-w-0">
                    <h4 class="text-base font-bold leading-tight truncate" :class="!isDark ? 'text-slate-800' : 'text-white'">
                      {{ group.items[0].title }}
                      <span v-if="group.items.length > 1" class="text-xs ml-1" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">+{{ group.items.length - 1 }}</span>
                    </h4>
                    <div class="flex items-center gap-2 mt-0.5">
                      <span :class="categoryBadgeClass(group.items[0].category)">{{ group.items[0].category }}</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs mt-0.5" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">
                      <span class="material-symbols-outlined text-sm">schedule</span>
                      <span>{{ group.time }} — {{ group.items[0].location || '-' }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="monthEventGroups.length === 0" class="flex flex-col items-center justify-center py-12 gap-3">
                <span class="material-symbols-outlined text-4xl" :class="!isDark ? 'text-slate-300' : 'text-white/20'">event_busy</span>
                <p class="text-sm" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">Tidak ada event bulan ini</p>
              </div>
            </div>
          </template>

          <div class="h-6 w-full shrink-0" :class="!isDark ? 'bg-linear-to-t from-white to-transparent' : 'bg-linear-to-t from-[#0b1711] to-transparent'"></div>
        </div>
      </aside>

      <!-- ═══════ MOBILE DETAIL MODAL ═══════ -->
      <Transition name="detail">
        <div v-if="showMobileModal" @click.self="showMobileModal = false" class="fixed inset-0 z-[100] flex items-center justify-center p-4 md:hidden portrait:flex bg-[#0a192f]/90 backdrop-blur-sm">
          <div class="relative w-full max-w-lg h-[80vh] max-h-[90vh] rounded-2xl flex flex-col overflow-hidden shadow-2xl border"
               :class="!isDark ? 'bg-white border-slate-200' : 'events-panel border-white/10'">
            <!-- Close Button -->
            <button @click="showMobileModal = false" class="absolute top-4 right-4 z-50 size-8 rounded-full text-white flex items-center justify-center shadow-lg transition-colors cursor-pointer border"
                    :class="!isDark ? 'bg-red-500 hover:bg-red-600 border-red-400' : 'bg-red-500/80 hover:bg-red-600 border-white/20'">
              <span class="material-symbols-outlined text-base">close</span>
            </button>
            
            <div class="flex flex-col h-full">
              <!-- ─── Selected Date Events ─── -->
              <template v-if="selectedDateEvents.length > 0 && !showAllMonth">
                <div class="p-5 border-b flex flex-col gap-2 shrink-0 pr-14" :class="!isDark ? 'border-slate-200 bg-slate-50' : 'border-white/10 bg-[#0b1711]/50'">
                  <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-lg font-bold flex items-center gap-2" :class="!isDark ? 'text-slate-800' : 'text-white'">
                      <span class="material-symbols-outlined" :class="!isDark ? 'text-amber-500' : 'text-accent'">event_note</span>
                      {{ selectedDay }} {{ months[currentMonth] }}
                    </h3>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full shadow-sm" :class="!isDark ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'text-[#0a192f] bg-accent'">{{ selectedDateEvents.length }} event</span>
                  </div>
                  <button @click="showAllMonth = true" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer mt-2"
                          :class="!isDark ? 'bg-amber-50 border-amber-200 text-amber-600 hover:bg-amber-100' : 'bg-accent/10 border-accent/30 text-accent hover:bg-accent/20'">
                    <span class="material-symbols-outlined text-[14px]">calendar_month</span>
                    Event Bulan Ini
                  </button>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
                  <!-- Grouped by time -->
                  <div v-for="group in selectedDateGroups" :key="group.time">
                    <div class="flex items-center gap-2 mb-2">
                      <span class="font-bold text-lg font-mono" :class="!isDark ? 'text-amber-600' : 'text-accent'">{{ group.time }}</span>
                      <div class="h-px flex-1" :class="!isDark ? 'bg-slate-200' : 'bg-white/10'"></div>
                      <span v-if="group.items.length > 1" class="text-[10px] font-bold" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">{{ group.items.length }} kegiatan</span>
                    </div>
                    <div v-for="(item, iIdx) in group.items" :key="item.id"
                         @click="router.push({ name: 'DetailMonthly', params: { id: item.id } })"
                         :class="[
                           'rounded-xl p-4 cursor-pointer transition-all duration-300 hover:scale-[1.02]',
                           !isDark ? 'bg-white border border-slate-200 hover:border-amber-400 shadow-sm' : 'bg-linear-to-br from-blue-900/40 to-[#0b1711] border border-blue-500/20 hover:border-accent/50',
                           iIdx > 0 ? 'mt-2' : ''
                         ]">
                      <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 border"
                             :class="!isDark ? 'bg-amber-50 border-amber-200' : 'bg-accent/10 border-accent/30'">
                          <span class="material-symbols-outlined text-xl" :class="!isDark ? 'text-amber-500' : 'text-accent'">{{ item.icon || 'event' }}</span>
                        </div>
                        <div class="flex flex-col flex-1 min-w-0">
                          <h4 class="text-base font-bold leading-tight truncate" :class="!isDark ? 'text-slate-800' : 'text-white'">{{ item.title }}</h4>
                          <div class="flex items-center gap-2 mt-1">
                            <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
                            <span class="text-xs flex items-center gap-1" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">
                              <span class="material-symbols-outlined text-[12px]">location_on</span>
                              {{ item.location || '-' }}
                            </span>
                          </div>
                          <span v-if="item.teacher" class="text-xs mt-0.5 flex items-center gap-1" :class="!isDark ? 'text-slate-500' : 'text-blue-200/60'">
                            <span class="material-symbols-outlined text-[12px]">person</span>
                            {{ item.teacher }}
                          </span>
                        </div>
                        <div class="flex items-center">
                          <span class="material-symbols-outlined text-lg" :class="!isDark ? 'text-slate-300' : 'text-white/30'">chevron_right</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- ─── All month events (fallback or toggled) ─── -->
              <template v-else>
                <div class="p-5 border-b flex flex-col gap-2 shrink-0 pr-14" :class="!isDark ? 'border-slate-200 bg-slate-50' : 'border-white/10 bg-[#0b1711]/50'">
                  <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-lg font-bold flex items-center gap-2" :class="!isDark ? 'text-slate-800' : 'text-white'">
                      <span class="material-symbols-outlined" :class="!isDark ? 'text-amber-500' : 'text-accent'">event_upcoming</span>
                      Event Bulan Ini
                    </h3>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full shadow-sm" :class="!isDark ? 'bg-amber-100 text-amber-600 border border-amber-200' : 'text-[#0a192f] bg-accent'">{{ monthEventGroups.reduce((a, g) => a + g.items.length, 0) }} event</span>
                  </div>
                  <button v-if="showAllMonth && selectedDateEvents.length > 0" @click="showAllMonth = false" class="flex items-center justify-center gap-1.5 w-full py-1.5 rounded-lg border text-xs font-bold transition-all cursor-pointer mt-2"
                          :class="!isDark ? 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' : 'bg-white/5 border-white/10 text-white/70 hover:bg-white/10'">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Kembali ke {{ selectedDay }} {{ months[currentMonth] }}
                  </button>
                  <p v-if="!showAllMonth && selectedDateEvents.length === 0" class="text-xs mt-2" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">Tidak ada event pada tanggal {{ selectedDay }}.</p>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="will-change: transform; transform: translateZ(0)">
                  <div v-for="group in monthEventGroups" :key="group.key"
                       @click="router.push({ name: 'DetailMonthly', params: { id: group.items[0].id } })"
                       :class="[
                         'rounded-xl p-4 cursor-pointer',
                         group.isToday
                           ? (!isDark ? 'bg-amber-50 border border-amber-300 relative overflow-hidden' : 'bg-linear-to-br from-blue-900/60 to-[#0b1711] border border-accent/40 relative overflow-hidden')
                           : (!isDark ? 'bg-white border border-slate-200 hover:bg-slate-50 shadow-sm' : 'bg-[#0b1711]/40 border border-white/5 hover:bg-[#0b1711]/60'),
                         group.isPast ? 'opacity-50' : ''
                       ]">
                    <div v-if="group.isToday" class="absolute top-0 right-0 p-2 opacity-10">
                      <span class="material-symbols-outlined text-7xl" :class="!isDark ? 'text-amber-500' : 'text-accent'">{{ group.items[0].icon || 'event' }}</span>
                    </div>
                    <div class="flex gap-3 relative z-10">
                      <div class="flex flex-col items-center justify-center rounded-lg w-14 h-14 shrink-0 border"
                           :class="!isDark ? 'bg-slate-50 border-slate-200' : 'bg-white/5 border-white/5'">
                        <span class="text-[10px] font-bold uppercase" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">{{ monthShort }}</span>
                        <span class="text-xl font-bold leading-tight" :class="!isDark ? 'text-slate-800' : 'text-white'">{{ group.dateNum }}</span>
                      </div>
                      <div class="flex flex-col justify-center flex-1 min-w-0">
                        <h4 class="text-base font-bold leading-tight truncate" :class="!isDark ? 'text-slate-800' : 'text-white'">
                          {{ group.items[0].title }}
                          <span v-if="group.items.length > 1" class="text-xs ml-1" :class="!isDark ? 'text-amber-500' : 'text-yellow-300'">+{{ group.items.length - 1 }}</span>
                        </h4>
                        <div class="flex items-center gap-2 mt-0.5">
                          <span :class="categoryBadgeClass(group.items[0].category)">{{ group.items[0].category }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs mt-0.5" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">
                          <span class="material-symbols-outlined text-sm">schedule</span>
                          <span>{{ group.time }} — {{ group.items[0].location || '-' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="monthEventGroups.length === 0" class="flex flex-col items-center justify-center py-12 gap-3">
                    <span class="material-symbols-outlined text-4xl" :class="!isDark ? 'text-slate-300' : 'text-white/20'">event_busy</span>
                    <p class="text-sm" :class="!isDark ? 'text-slate-500' : 'text-slate-400'">Tidak ada event bulan ini</p>
                  </div>
                </div>
              </template>
              <div class="h-6 w-full shrink-0" :class="!isDark ? 'bg-linear-to-t from-white to-transparent' : 'bg-linear-to-t from-[#0b1711] to-transparent'"></div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import { usePublicTheme } from '../../../composables/usePublicTheme'

const router = useRouter()
const { isDark } = usePublicTheme()
onMounted(() => { loadMonthlies() })

// ── Calendar State ──
const today = new Date()
const currentMonth = ref(today.getMonth())
const currentYear = ref(today.getFullYear())
const selectedDay = ref(today.getDate())

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

const monthYearDisplay = computed(() => `${months[currentMonth.value]} ${currentYear.value}`)
const monthShort = computed(() => monthsShort[currentMonth.value])
const hijriMonthDisplay = computed(() => {
  try { return new Intl.DateTimeFormat('id-u-ca-islamic', { month: 'long', year: 'numeric' }).format(new Date(currentYear.value, currentMonth.value, 1)) }
  catch { return '' }
})

function prevMonth() {
  if (currentMonth.value === 0) { currentMonth.value = 11; currentYear.value-- }
  else currentMonth.value--
  selectedDay.value = 1
}
function nextMonth() {
  if (currentMonth.value === 11) { currentMonth.value = 0; currentYear.value++ }
  else currentMonth.value++
  selectedDay.value = 1
}
const showAllMonth = ref(false)
const showMobileModal = ref(false)
function selectDate(day) { 
  selectedDay.value = day; 
  showAllMonth.value = false;
  showMobileModal.value = true;
}

watch([currentMonth, currentYear], () => loadMonthlies())

const dayHeaders = [
  { name: 'Min', isJumat: false }, { name: 'Sen', isJumat: false }, { name: 'Sel', isJumat: false },
  { name: 'Rab', isJumat: false }, { name: 'Kam', isJumat: false }, { name: 'Jum', isJumat: true },
  { name: 'Sab', isJumat: false }
]

// ── Events Data from API ──
const allMonthItems = ref([])

async function loadMonthlies() {
  try {
    const m = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}`
    const { data } = await api.get('/monthlies', { params: { per_page: 100, status: 'Aktif', month: m } })
    allMonthItems.value = data.data || []
  } catch (e) {
    console.error('Failed to load monthlies:', e)
    allMonthItems.value = []
  }
}

function getEventsForDate(day) {
  const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
  return allMonthItems.value.filter(item => item.date?.substring(0, 10) === dateStr)
}

// ── Calendar Cells ──
const calendarCells = computed(() => {
  const y = currentYear.value, m = currentMonth.value
  const firstDay = new Date(y, m, 1).getDay()
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const daysInPrevMonth = new Date(y, m, 0).getDate()
  const cells = []

  for (let i = firstDay - 1; i >= 0; i--) cells.push({ day: daysInPrevMonth - i, isCurrentMonth: false })
  for (let d = 1; d <= daysInMonth; d++) {
    const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear()
    cells.push({ day: d, isCurrentMonth: true, isToday, isSelected: d === selectedDay.value, events: getEventsForDate(d) })
  }
  const remaining = 7 - (cells.length % 7)
  if (remaining < 7) { for (let i = 1; i <= remaining; i++) cells.push({ day: i, isCurrentMonth: false }) }
  return cells
})

// ── Selected Date Events (for right panel) ──
const selectedDateEvents = computed(() => getEventsForDate(selectedDay.value))

const selectedDateGroups = computed(() => {
  const groups = []
  const groupMap = {}
  for (const item of selectedDateEvents.value) {
    const t = item.time?.substring(0, 5) || '00:00'
    if (!groupMap[t]) { groupMap[t] = { time: t, items: [] }; groups.push(groupMap[t]) }
    groupMap[t].items.push(item)
  }
  return groups
})

// ── Month Events Grouped by date+time (for fallback when no events on selected date) ──
const monthEventGroups = computed(() => {
  const y = currentYear.value, m = currentMonth.value
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const groups = []
  const groupMap = {}

  for (let d = 1; d <= daysInMonth; d++) {
    const events = getEventsForDate(d)
    for (const ev of events) {
      const time = ev.time?.substring(0, 5) || '00:00'
      const key = `${d}-${time}`
      if (!groupMap[key]) {
        const isToday = d === today.getDate() && m === today.getMonth() && y === today.getFullYear()
        const isPast = new Date(y, m, d) < new Date(today.getFullYear(), today.getMonth(), today.getDate())
        groupMap[key] = { key, dateNum: d, time, isToday, isPast, items: [] }
        groups.push(groupMap[key])
      }
      groupMap[key].items.push(ev)
    }
  }
  return groups
})

// ── Transform item for detail components ──
function transformItem(item) {
  const dateObj = item.date ? new Date(item.date) : new Date()
  return {
    ...item,
    time: item.time?.substring(0, 5),
    description: item.body,
    image: item.image_path ? storageUrl(item.image_path) : '/img/default-agenda.png',
    videoUrl: item.video_path ? storageUrl(item.video_path) : null,
    speaker: item.teacher,
    date: dateObj.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }),
  }
}




// ── Category Helpers ──
function categoryBadgeClass(cat) {
  const b = 'px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border'
  if (isDark.value) {
    return cat === 'Artikel' ? `${b} bg-accent/20 text-accent border-accent/40`
         : cat === 'Gambar' ? `${b} bg-blue-500/20 text-blue-400 border-blue-500/40`
         : `${b} bg-red-500/20 text-red-400 border-red-500/40`
  }
  return cat === 'Artikel' ? `${b} bg-amber-100 text-amber-700 border-amber-300`
       : cat === 'Gambar' ? `${b} bg-blue-100 text-blue-700 border-blue-300`
       : `${b} bg-red-100 text-red-700 border-red-300`
}
function categoryDotColor(cat) {
  return cat === 'Artikel' ? 'bg-accent' : cat === 'Gambar' ? 'bg-blue-400' : 'bg-red-400'
}
</script>

<style scoped>
.glass-panel {
  background: rgba(13, 25, 48, 0.6);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(251, 191, 36, 0.1);
}
.calendar-cell-active {
  background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0) 100%);
  border: 1px solid var(--color-accent);
  box-shadow: 0 0 15px rgba(251, 191, 36, 0.15);
}
.events-panel {
  background: rgba(13, 25, 48, 0.85);
  border: 1px solid rgba(251, 191, 36, 0.1);
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }

.animate-marquee { animation: marquee 30s linear infinite; }
@keyframes marquee {
  0% { transform: translateX(100%); }
  100% { transform: translateX(-100%); }
}
</style>
