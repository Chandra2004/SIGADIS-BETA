<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    alertId: {
        type: Number,
        required: true,
    },
    motherName: {
        type: String,
        required: true,
    },
    facilities: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');
const filterIcu = ref(false);
const filterNicu = ref(false);

const selectedFacility = ref(null);
const isReferralModalOpen = ref(false);

const referralForm = useForm({
    facility_id: null,
    notes: '',
});

const applyFilters = () => {
    router.get(
        route('bidan.referrals.create', props.alertId),
        {
            search: searchQuery.value,
            has_icu: filterIcu.value ? 1 : 0,
            has_nicu: filterNicu.value ? 1 : 0,
        },
        { preserveState: true, replace: true }
    );
};

const openReferralModal = (facility) => {
    selectedFacility.value = facility;
    referralForm.facility_id = facility.id;
    referralForm.notes = '';
    isReferralModalOpen.value = true;
};

const submitReferral = () => {
    referralForm.post(route('bidan.referrals.store', props.alertId), {
        onSuccess: () => {
            isReferralModalOpen.value = false;
        },
    });
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
    <Head :title="`Proses Rujukan: ${motherName} — SIGADIS Nakes`" />

    <BidanLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- 1. Header & Navigation Back -->
            <div class="flex items-center justify-between gap-4">
                <Link
                    :href="route('bidan.alerts.show', alertId)"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white border border-[#E3E2E5] text-xs font-bold text-[#123356] hover:bg-neutral-50 transition-all shadow-xs"
                >
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Detail Kasus</span>
                </Link>

                <div class="text-xs text-[#73777F]">
                    Pasien: <strong class="text-[#123356] font-extrabold">{{ motherName }}</strong>
                </div>
            </div>

            <!-- 2. Header Box -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-800 text-xs font-extrabold border border-rose-200">
                    <span class="material-symbols-outlined text-sm">local_hospital</span>
                    <span>Pusat Rujukan Emergensi Maternal</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Pilih Fasilitas Kesehatan Rujukan PONEK
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E]">
                    Pilih rumah sakit atau puskesmas rujukan dengan kesiapan NICU, ICU, dan ambulans siaga terdekat untuk pasien <strong class="text-[#123356]">{{ motherName }}</strong>.
                </p>
            </div>

            <!-- 3. Filter & Search Controls -->
            <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="filterIcu = !filterIcu; applyFilters()"
                        :class="[
                            'px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer border',
                            filterIcu ? 'bg-[#123356] text-white border-[#123356]' : 'bg-[#FAF9FC] text-[#43474E] border-[#C3C6CF] hover:bg-neutral-100'
                        ]"
                    >
                        🩺 Memiliki ICU
                    </button>

                    <button
                        type="button"
                        @click="filterNicu = !filterNicu; applyFilters()"
                        :class="[
                            'px-3.5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer border',
                            filterNicu ? 'bg-[#123356] text-white border-[#123356]' : 'bg-[#FAF9FC] text-[#43474E] border-[#C3C6CF] hover:bg-neutral-100'
                        ]"
                    >
                        👶 Memiliki NICU Bed
                    </button>
                </div>

                <!-- Search Box -->
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

            <!-- 4. Facilities List Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="facilities.length === 0" class="col-span-2 py-12 text-center text-xs text-[#73777F] bg-white rounded-3xl border border-[#E3E2E5]">
                    <span class="material-symbols-outlined text-4xl text-neutral-300 block mb-2">domain_disabled</span>
                    Tidak ditemukan faskes yang sesuai dengan kriteria filter.
                </div>

                <div
                    v-for="f in facilities"
                    :key="f.id"
                    class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4 hover:border-[#123356] transition-all flex flex-col justify-between"
                >
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider border border-blue-200">
                                    {{ f.type }} • Kelas {{ f.hospital_class || '-' }}
                                </span>
                                <h3 class="text-base font-extrabold text-[#123356] mt-1">{{ f.name }}</h3>
                            </div>

                            <span v-if="f.distance_km !== null" class="text-xs font-mono font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg">
                                📍 {{ f.distance_km }} km
                            </span>
                        </div>

                        <p class="text-xs text-[#73777F] line-clamp-2">
                            {{ f.address || 'Alamat tidak tersedia' }}
                        </p>

                        <!-- Live Capacity Pills -->
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

                    <!-- Bottom Action: Kontak & Tombol Pilih -->
                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-[#F2F3F5]">
                        <a
                            v-if="f.phone_number"
                            :href="'tel:' + f.phone_number"
                            class="text-xs text-[#123356] font-bold hover:underline flex items-center gap-1"
                            title="Early Warning Call IGD"
                        >
                            <span class="material-symbols-outlined text-sm text-emerald-600">call</span>
                            <span>IGD: {{ f.phone_number }}</span>
                        </a>
                        <span v-else class="text-xs text-[#73777F]">Kontak tidak ada</span>

                        <button
                            type="button"
                            @click="openReferralModal(f)"
                            class="px-4 py-2 rounded-xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold transition-all shadow-xs cursor-pointer active:scale-95 flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-sm">send</span>
                            <span>Rujuk ke Faskes Ini</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ModalBox: Konfirmasi & Pengantar Rujukan -->
        <ModalBox
            :show="isReferralModalOpen"
            type="primary"
            title="Konfirmasi Pengantar Rujukan Medis"
            :message="`Anda akan merujuk pasien ${motherName} ke ${selectedFacility?.name}.`"
            confirm-text="Kirim & Catat Rujukan"
            :confirm-disabled="referralForm.processing"
            :loading="referralForm.processing"
            @close="isReferralModalOpen = false"
            @cancel="isReferralModalOpen = false"
            @confirm="submitReferral"
        >
            <form @submit.prevent="submitReferral" class="space-y-3 pt-2">
                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-[#123356] space-y-1">
                    <div class="font-extrabold">{{ selectedFacility?.name }}</div>
                    <div class="text-[11px] text-blue-900">Kontak IGD: {{ selectedFacility?.phone_number || '-' }}</div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Catatan Klinis & Stabilisasi Pra-Rujukan (Opsional)</label>
                    <textarea
                        v-model="referralForm.notes"
                        rows="3"
                        placeholder="Contoh: Terpasang Infus RL 20 tpm, O2 nasal kanul 3 lpm, MgSO4 loading dose telah diberikan..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>
    </BidanLayout>
</template>
