<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';

const props = defineProps({
    facilities: {
        type: Array,
        default: () => [],
    },
    regionCode: {
        type: String,
        default: 'Puskesmas',
    },
});

const searchQuery = ref('');
const filterIcu = ref(false);
const filterNicu = ref(false);

const applyFilters = () => {
    router.get(
        route('bidan.referrals.index'),
        {
            search: searchQuery.value,
            has_icu: filterIcu.value ? 1 : 0,
            has_nicu: filterNicu.value ? 1 : 0,
        },
        { preserveState: true, replace: true }
    );
};

const getAmbulanceBadge = (status) => {
    switch (status) {
        case 'siaga':
            return { label: 'Ambulans Siaga', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300' };
        case 'dalam_perjalanan':
            return { label: 'Ambulans Operasional', bg: 'bg-amber-100 text-amber-800 border-amber-300' };
        default:
            return { label: 'Tidak Siaga', bg: 'bg-neutral-100 text-neutral-700 border-neutral-200' };
    }
};
</script>

<template>
    <Head title="Fasilitas & Rujukan PONEK — SIGADIS Nakes" />

    <BidanLayout>
        <div class="space-y-6">
            <!-- 1. Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">local_hospital</span>
                        <span>Jaringan Faskes Rujukan PONEK</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Direktori & Kesiapan Faskes Rujukan
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Pantau ketersediaan tempat tidur NICU, ruang ICU maternal, serta kesiapan armada ambulans siaga di wilayah {{ regionCode }}.
                    </p>
                </div>
            </div>

            <!-- 2. Filter & Search Controls -->
            <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="filterIcu = !filterIcu; applyFilters()"
                        :class="[
                            'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer border shadow-xs',
                            filterIcu ? 'bg-[#123356] text-white border-[#123356]' : 'bg-[#FAF9FC] text-[#43474E] border-[#C3C6CF] hover:bg-neutral-100'
                        ]"
                    >
                        🩺 Memiliki Ruang ICU
                    </button>

                    <button
                        type="button"
                        @click="filterNicu = !filterNicu; applyFilters()"
                        :class="[
                            'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer border shadow-xs',
                            filterNicu ? 'bg-[#123356] text-white border-[#123356]' : 'bg-[#FAF9FC] text-[#43474E] border-[#C3C6CF] hover:bg-neutral-100'
                        ]"
                    >
                        👶 Memiliki NICU Bed
                    </button>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-72">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                    <input
                        v-model="searchQuery"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Cari nama faskes..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>
            </div>

            <!-- 3. Facilities Grid Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div v-if="facilities.length === 0" class="col-span-full py-12 text-center text-xs text-[#73777F] bg-white rounded-3xl border border-[#E3E2E5]">
                    <span class="material-symbols-outlined text-4xl text-neutral-300 block mb-2">domain_disabled</span>
                    Tidak ada fasilitas kesehatan yang sesuai dengan filter.
                </div>

                <div
                    v-for="f in facilities"
                    :key="f.id"
                    class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4 hover:border-[#123356] transition-all flex flex-col justify-between"
                >
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-blue-800 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider border border-blue-200">
                                    {{ f.type }} • Kelas {{ f.hospital_class || '-' }}
                                </span>
                                <h3 class="text-base font-extrabold text-[#123356] mt-1">{{ f.name }}</h3>
                            </div>
                        </div>

                        <p class="text-xs text-[#73777F] line-clamp-2">
                            {{ f.address || 'Alamat faskes lengkap' }}
                        </p>

                        <!-- Live Capacity Badges -->
                        <div class="flex flex-wrap gap-2 pt-2 border-t border-[#F2F3F5] text-xs">
                            <span :class="['px-2.5 py-1 rounded-lg border text-[11px] font-bold', getAmbulanceBadge(f.ambulance_status).bg]">
                                🚑 {{ getAmbulanceBadge(f.ambulance_status).label }}
                            </span>
                            <span v-if="f.has_nicu" class="px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 text-[11px] font-bold">
                                👶 NICU: {{ f.nicu_bed_count || 0 }} Bed
                            </span>
                            <span v-if="f.has_icu" class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 text-[11px] font-bold">
                                🩺 ICU Tersedia
                            </span>
                        </div>
                    </div>

                    <!-- Direct Early Warning Call to IGD -->
                    <div class="pt-3 border-t border-[#F2F3F5] flex items-center justify-between">
                        <div class="text-xs font-mono font-bold text-[#123356]">
                            📞 {{ f.phone_number || '-' }}
                        </div>

                        <a
                            v-if="f.phone_number"
                            :href="'tel:' + f.phone_number"
                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1"
                            title="Early Warning Call IGD"
                        >
                            <span class="material-symbols-outlined text-sm">call</span>
                            <span>Panggil IGD</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </BidanLayout>
</template>
