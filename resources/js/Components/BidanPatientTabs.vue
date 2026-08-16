<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    activeFilter: {
        type: String,
        default: 'semua',
    },
    summary: {
        type: Object,
        default: () => ({ total: 0, risiko_tinggi: 0, risiko_sedang: 0, nifas: 0 }),
    },
});

const setFilter = (filterKey) => {
    router.get(
        route('bidan.dashboard'),
        { filter: filterKey },
        { preserveState: true, replace: true }
    );
};
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <!-- Semua Pasien -->
        <button
            type="button"
            @click="setFilter('semua')"
            :class="[
                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs',
                activeFilter === 'semua'
                    ? 'bg-[#123356] text-white shadow-md'
                    : 'bg-[#FAF9FC] text-[#43474E] hover:bg-neutral-200 border border-[#E3E2E5]'
            ]"
        >
            <span class="material-symbols-outlined text-sm">groups</span>
            <span>Semua Pasien</span>
            <span
                :class="[
                    'px-1.5 py-0.5 rounded-md text-[10px] font-black',
                    activeFilter === 'semua' ? 'bg-white/20 text-white' : 'bg-neutral-200 text-[#123356]'
                ]"
            >
                {{ summary.total }}
            </span>
        </button>

        <!-- Risiko Tinggi -->
        <button
            type="button"
            @click="setFilter('tinggi')"
            :class="[
                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs',
                activeFilter === 'tinggi'
                    ? 'bg-rose-700 text-white shadow-md'
                    : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'
            ]"
        >
            <span class="material-symbols-outlined text-sm">warning</span>
            <span>Risiko Tinggi</span>
            <span
                :class="[
                    'px-1.5 py-0.5 rounded-md text-[10px] font-black',
                    activeFilter === 'tinggi' ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-900'
                ]"
            >
                {{ summary.risiko_tinggi }}
            </span>
        </button>

        <!-- Risiko Sedang -->
        <button
            type="button"
            @click="setFilter('sedang')"
            :class="[
                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs',
                activeFilter === 'sedang'
                    ? 'bg-amber-600 text-white shadow-md'
                    : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200'
            ]"
        >
            <span class="material-symbols-outlined text-sm">error</span>
            <span>Risiko Sedang</span>
            <span
                :class="[
                    'px-1.5 py-0.5 rounded-md text-[10px] font-black',
                    activeFilter === 'sedang' ? 'bg-white/20 text-white' : 'bg-amber-200 text-amber-950'
                ]"
            >
                {{ summary.risiko_sedang }}
            </span>
        </button>

        <!-- Risiko Rendah -->
        <button
            type="button"
            @click="setFilter('rendah')"
            :class="[
                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs',
                activeFilter === 'rendah'
                    ? 'bg-emerald-700 text-white shadow-md'
                    : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'
            ]"
        >
            <span class="material-symbols-outlined text-sm">check_circle</span>
            <span>Risiko Rendah</span>
        </button>

        <!-- Masa Nifas (42 Hari) -->
        <button
            type="button"
            @click="setFilter('nifas')"
            :class="[
                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5 shadow-xs',
                activeFilter === 'nifas'
                    ? 'bg-purple-700 text-white shadow-md'
                    : 'bg-purple-50 text-purple-900 hover:bg-purple-100 border border-purple-200'
            ]"
        >
            <span class="material-symbols-outlined text-sm">child_care</span>
            <span>Masa Nifas (42 Hari)</span>
            <span
                :class="[
                    'px-1.5 py-0.5 rounded-md text-[10px] font-black',
                    activeFilter === 'nifas' ? 'bg-white/20 text-white' : 'bg-purple-200 text-purple-950'
                ]"
            >
                {{ summary.nifas }}
            </span>
        </button>
    </div>
</template>
