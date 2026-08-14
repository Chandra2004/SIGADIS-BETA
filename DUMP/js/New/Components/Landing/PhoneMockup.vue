<script setup>
defineProps({
    dark: { type: Boolean, default: false },
});
</script>

<template>
    <div
        :class="[
            'relative w-full max-w-md rounded-[2.2rem] border p-3 shadow-[0_30px_80px_rgba(44,74,110,0.18)]',
            dark ? 'border-white/10 bg-brand-navy-900/80' : 'border-brand-navy-900/10 bg-brand-navy-900',
        ]"
    >
        <div class="rounded-[1.7rem] bg-gradient-to-br from-brand-navy-900 via-[#335A82] to-[#1E3150] p-5">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-brand-pink-500" />
                    <span class="h-3 w-3 rounded-full bg-accent-amber" />
                    <span class="h-3 w-3 rounded-full bg-risk-low" />
                </div>
                <span class="rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.22em] text-white">
                    SIGADIS
                </span>
            </div>

            <div class="rounded-[1.5rem] bg-white p-4 shadow-inner">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-navy-700">Dashboard</p>
                        <h3 class="mt-1 text-xl font-black text-ink-900">Ibu Sehat</h3>
                    </div>
                    <div class="rounded-full bg-risk-high-bg px-2.5 py-1 text-xs font-bold text-risk-high">Darurat</div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-brand-navy-100 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-navy-700">Lokasi</p>
                        <p class="mt-2 text-sm font-bold text-ink-900">Aktif</p>
                    </div>
                    <div class="rounded-2xl bg-risk-low-bg p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-risk-low">Status</p>
                        <p class="mt-2 text-sm font-bold text-ink-900">Terpantau</p>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl bg-ink-100 p-3">
                    <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.16em] text-brand-navy-700">
                        <span>Hari Ini</span>
                        <span>4/5</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-ink-200">
                        <div class="h-full w-[80%] rounded-full bg-gradient-to-r from-brand-navy-900 via-brand-pink-500 to-risk-low" />
                    </div>
                </div>

                <button type="button" class="btn mt-5 h-12 w-full rounded-2xl border-none bg-emergency text-base font-black text-white shadow-lg shadow-emergency/25 hover:bg-emergency/90">
                    DARURAT
                </button>
            </div>
        </div>
    </div>
</template>
