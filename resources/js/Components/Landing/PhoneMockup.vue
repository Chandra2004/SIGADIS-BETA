<script setup>
defineProps({
    dark: { type: Boolean, default: false },
});
</script>

<template>
    <div class="relative w-full max-w-75">
        <!-- Glow/shadow behind phone -->
        <div class="absolute -inset-4 rounded-3xl bg-brand-navy-900/10 blur-2xl" />

        <!-- Phone frame -->
        <div
            :class="[
                'relative w-full rounded-[2.8rem] border-[7px] p-2 shadow-2xl',
                dark ? 'border-brand-navy-700 bg-brand-navy-900' : 'border-neutral-200 bg-neutral-100',
            ]"
            style="box-shadow: 0 40px 80px -12px rgba(44,74,110,0.35), 0 0 0 1px rgba(44,74,110,0.06);"
        >
            <!-- Notch -->
            <div class="absolute left-1/2 top-3 z-20 -translate-x-1/2 h-5 w-20 rounded-full bg-neutral-800" />

            <!-- Screen -->
            <div class="relative h-150 w-full overflow-hidden rounded-[2.2rem] bg-brand-pink-50 flex flex-col">
                <!-- Status bar -->
                <div class="flex justify-between items-center px-5 pt-8 pb-2 text-[9px] font-bold text-brand-navy-900">
                    <span>9:41</span>
                    <div class="flex items-center gap-1">
                        <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3a4.237 4.237 0 0 0-6 0zm-4-4 2 2a7.074 7.074 0 0 1 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg>
                        <svg class="h-2.5 w-3" viewBox="0 0 24 24" fill="currentColor"><rect x="0" y="6" width="20" height="12" rx="2" opacity=".3"/><rect x="0" y="6" width="16" height="12" rx="2"/><rect x="21" y="10" width="2" height="4" rx="1"/></svg>
                    </div>
                </div>

                <!-- App Header -->
                <div class="bg-white px-5 pb-5 pt-2 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-brand-pink-50 px-3 py-1 text-[10px] font-black tracking-widest text-brand-navy-900">
                            SIGADIS
                        </span>
                        <div class="relative">
                            <div class="h-7 w-7 rounded-full bg-brand-navy-100 flex items-center justify-center">
                                <svg class="h-3.5 w-3.5 text-brand-navy-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 5-2 6-2 8h16c0-2-2-3-2-8Z" stroke-linecap="round"/><path d="M10 21a2 2 0 0 0 4 0" stroke-linecap="round"/></svg>
                            </div>
                            <span class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-emergency-alert" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-[10px] font-semibold text-neutral-500">Selamat pagi, 👋</p>
                        <h3 class="text-base font-black text-brand-navy-900">Ibu Sehat</h3>
                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-risk-low-bg px-2.5 py-1 text-[10px] font-bold text-risk-low">
                            <span class="h-1.5 w-1.5 rounded-full bg-risk-low" />
                            Risiko Rendah
                        </div>
                    </div>
                </div>

                <!-- App Content -->
                <div class="flex-1 p-4 space-y-3 overflow-hidden">
                    <!-- Stats row -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-neutral-100/80">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-neutral-400">Tensi</p>
                            <p class="mt-1 text-sm font-black text-brand-navy-900">120/80</p>
                            <p class="text-[9px] text-risk-low font-semibold">Normal</p>
                        </div>
                        <div class="rounded-2xl bg-white p-3.5 shadow-sm border border-neutral-100/80">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-neutral-400">Kandungan</p>
                            <p class="mt-1 text-sm font-black text-brand-navy-900">24 Mgg</p>
                            <p class="text-[9px] text-brand-navy-700 font-semibold">Trimester 2</p>
                        </div>
                    </div>

                    <!-- Screening progress -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm border border-neutral-100/80">
                        <div class="flex items-center justify-between text-[10px] font-bold text-brand-navy-900 mb-2.5">
                            <span>Skrining SADAR</span>
                            <span class="text-brand-navy-700">4/5 selesai</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-neutral-100">
                            <div class="h-full w-[80%] rounded-full bg-linear-to-r from-brand-navy-700 to-brand-navy-900 transition-all" />
                        </div>
                        <p class="mt-2 text-[9px] text-neutral-400">1 pertanyaan tersisa</p>
                    </div>

                    <!-- Bidan card -->
                    <div class="rounded-2xl bg-brand-navy-900 p-4 shadow-sm">
                        <p class="text-[9px] font-bold uppercase tracking-wider text-brand-navy-100">Bidan Pendamping</p>
                        <div class="mt-2 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold text-white">Bidan Sari</p>
                                <p class="text-[9px] text-brand-navy-100">Aktif sekarang</p>
                            </div>
                            <div class="h-2 w-2 rounded-full bg-risk-low animate-pulse" />
                        </div>
                    </div>
                </div>

                <!-- Emergency Button -->
                <div class="p-4 pb-6">
                    <button type="button" class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-emergency-alert text-sm font-black text-white shadow-lg shadow-emergency-alert/30 transition-transform active:scale-95">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                        DARURAT
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
