<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    facilities: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');
const selectedType = ref('all'); // 'all', 'Puskesmas', 'Rumah Sakit', 'Klinik'

const filteredFacilities = computed(() => {
    return props.facilities.filter(f => {
        const matchesQuery = !searchQuery.value ||
            f.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            f.address.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchesType = selectedType.value === 'all' ||
            (selectedType.value === 'Puskesmas' && f.type.toLowerCase().includes('puskesmas')) ||
            (selectedType.value === 'Rumah Sakit' && (f.type.toLowerCase().includes('rs') || f.type.toLowerCase().includes('rumah sakit'))) ||
            (selectedType.value === 'Klinik' && f.type.toLowerCase().includes('klinik'));

        return matchesQuery && matchesType;
    });
});

const openGoogleMaps = (f) => {
    const query = encodeURIComponent(`${f.name}, ${f.address}`);
    window.open(`https://www.google.com/maps/search/?api=1&query=${query}`, '_blank');
};
</script>

<template>
    <MobileLayout
        title="Fasilitas Kesehatan Terdekat — SIGADIS Mobile"
        activeTab="facilities"
    >
        <div class="space-y-4">
            <!-- Header Judul -->
            <div class="pt-1 pb-1">
                <h1 class="text-xl font-black text-[#123356] tracking-tight">
                    Fasilitas Kesehatan Terdekat
                </h1>
                <p class="text-xs text-[#73777F]">
                    Direktori Puskesmas PONED, RS PONEK, dan IGD Siaga 24 Jam
                </p>
            </div>

            <!-- Header Search Bar -->
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xl">
                    search
                </span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari nama Puskesmas, RS PONEK, atau Klinik..."
                    class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-[#F3AEC0]/40 text-xs text-[#26292E] placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#123356] shadow-xs"
                />
            </div>

            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
                <button
                    @click="selectedType = 'all'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all"
                    :class="selectedType === 'all' ? 'bg-[#123356] text-white shadow-xs' : 'bg-white text-[#73777F] border border-gray-200'"
                >
                    Semua Faskes
                </button>
                <button
                    @click="selectedType = 'Puskesmas'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all"
                    :class="selectedType === 'Puskesmas' ? 'bg-[#123356] text-white shadow-xs' : 'bg-white text-[#73777F] border border-gray-200'"
                >
                    Puskesmas PONED
                </button>
                <button
                    @click="selectedType = 'Rumah Sakit'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all"
                    :class="selectedType === 'Rumah Sakit' ? 'bg-[#123356] text-white shadow-xs' : 'bg-white text-[#73777F] border border-gray-200'"
                >
                    RSUD PONEK
                </button>
                <button
                    @click="selectedType = 'Klinik'"
                    class="px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all"
                    :class="selectedType === 'Klinik' ? 'bg-[#123356] text-white shadow-xs' : 'bg-white text-[#73777F] border border-gray-200'"
                >
                    Klinik Bersalin
                </button>
            </div>

            <!-- Facility Cards List -->
            <div class="space-y-3">
                <div
                    v-if="filteredFacilities.length === 0"
                    class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-xs"
                >
                    <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">local_hospital</span>
                    <h3 class="text-sm font-bold text-[#123356]">Fasilitas Tidak Ditemukan</h3>
                    <p class="text-xs text-[#73777F] mt-1">Coba sesuaikan kata kunci atau filter pencarian Anda.</p>
                </div>

                <div
                    v-for="facility in filteredFacilities"
                    :key="facility.id"
                    class="bg-white rounded-3xl p-4 border border-gray-100 shadow-xs hover:border-[#F3AEC0] transition-all space-y-3"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-[#123356] text-[10px] font-extrabold uppercase tracking-wide">
                                    {{ facility.type }}
                                </span>
                                <span v-if="facility.has_emergency_room" class="px-2 py-0.5 rounded-full bg-red-50 text-[#C81E2C] text-[10px] font-extrabold">
                                    IGD 24 Jam
                                </span>
                            </div>
                            <h4 class="text-sm font-extrabold text-[#123356] leading-tight">{{ facility.name }}</h4>
                            <p class="text-xs text-[#73777F] mt-0.5 leading-snug">{{ facility.address }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-1 rounded-xl bg-[#FDF3F6] text-[#123356] font-extrabold text-xs block">
                                {{ facility.distance_km }} km
                            </span>
                            <span class="text-[10px] text-[#73777F] block mt-0.5">dari domisili</span>
                        </div>
                    </div>

                    <!-- Fasilitas & Kesiapan Badge -->
                    <div class="flex flex-wrap gap-1.5 text-[11px] text-[#123356] pt-1">
                        <span v-if="facility.ambulance_available" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-emerald-50 text-[#4C9A6E] font-semibold">
                            <span class="material-symbols-outlined text-xs">emergency</span>
                            Ambulans Siaga
                        </span>
                        <span v-if="facility.has_nicu" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-purple-50 text-purple-700 font-semibold">
                            <span class="material-symbols-outlined text-xs">child_care</span>
                            Ruang NICU
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                        <a
                            :href="`tel:${facility.phone_number}`"
                            class="py-2.5 px-3 rounded-xl bg-[#123356] text-white text-xs font-bold flex items-center justify-center gap-1.5 active:scale-95 transition-all shadow-xs"
                        >
                            <span class="material-symbols-outlined text-base">call</span>
                            <span>Panggilan IGD</span>
                        </a>
                        <button
                            @click="openGoogleMaps(facility)"
                            class="py-2.5 px-3 rounded-xl bg-white border border-[#123356] text-[#123356] text-xs font-bold flex items-center justify-center gap-1.5 active:scale-95 transition-all hover:bg-gray-50"
                        >
                            <span class="material-symbols-outlined text-base">near_me</span>
                            <span>Rute Peta</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
