<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    alert: {
        type: Object,
        required: true,
    },
});

// Modal states
const isCancelModalOpen = ref(false);
const isResolveModalOpen = ref(false);
const isAcknowledgeLoading = ref(false);
const isCancelLoading = ref(false);
const isResolveLoading = ref(false);

const cancelForm = useForm({
    reason: '',
});

const resolveForm = useForm({
    resolution_notes: '',
});

const acknowledgeAlert = () => {
    isAcknowledgeLoading.value = true;
    router.post(route('bidan.alerts.acknowledge', props.alert.id), {}, {
        onFinish: () => {
            isAcknowledgeLoading.value = false;
        },
    });
};

const cancelHandling = () => {
    isCancelLoading.value = true;
    cancelForm.post(route('bidan.alerts.cancel-handling', props.alert.id), {
        onFinish: () => {
            isCancelLoading.value = false;
            isCancelModalOpen.value = false;
        },
    });
};

const resolveAlert = () => {
    isResolveLoading.value = true;
    router.post(route('bidan.alerts.resolve', props.alert.id), {
        onFinish: () => {
            isResolveLoading.value = false;
            isResolveModalOpen.value = false;
        },
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending':
        case 'delivered':
            return { label: 'Menunggu Respon', bg: 'bg-rose-100 text-rose-800 border-rose-300', dot: 'bg-rose-600 animate-pulse' };
        case 'being_handled':
            return { label: 'Sedang Ditangani', bg: 'bg-amber-100 text-amber-900 border-amber-300', dot: 'bg-amber-500' };
        case 'resolved':
            return { label: 'Selesai Ditangani', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300', dot: 'bg-emerald-500' };
        default:
            return { label: status, bg: 'bg-neutral-100 text-neutral-800 border-neutral-200', dot: 'bg-neutral-500' };
    }
};

const formatWaUrl = (phone) => {
    if (!phone) return '#';
    let clean = phone.replace(/[^0-9]/g, '');
    if (clean.startsWith('0')) clean = '62' + clean.substring(1);
    return `https://wa.me/${clean}`;
};
</script>

<template>
    <Head title="Detail Panggilan Darurat — SIGADIS Nakes" />

    <BidanLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- 1. Header & Navigation Back -->
            <div class="flex items-center justify-between gap-4">
                <Link
                    :href="route('bidan.dashboard')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white border border-[#E3E2E5] text-xs font-bold text-[#123356] hover:bg-neutral-50 transition-all shadow-xs"
                >
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Dashboard</span>
                </Link>

                <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border', getStatusBadge(alert.status).bg]">
                    <span :class="['w-2 h-2 rounded-full', getStatusBadge(alert.status).dot]"></span>
                    <span>{{ getStatusBadge(alert.status).label }}</span>
                </span>
            </div>

            <!-- 2. Main Emergency Command Card -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 sm:p-8 bg-gradient-to-r from-rose-50/70 to-red-50/50 border-b border-rose-100">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-600 text-white text-xs font-black uppercase tracking-wider shadow-xs">
                                <span class="material-symbols-outlined text-sm">emergency</span>
                                <span>{{ alert.trigger_type === 'manual_button' ? 'Tombol SOS Darurat' : 'Gejala Skrining Kritis' }}</span>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-black text-[#123356] tracking-tight">
                                {{ alert.pregnancy.mother_name }}
                            </h1>
                            <p class="text-xs sm:text-sm text-[#43474E]">
                                Usia Kehamilan: <strong class="text-[#123356]">{{ alert.pregnancy.gestational_age_weeks || '-' }} Minggu</strong> •
                                Waktu Pemicu: <strong class="text-[#123356]">{{ new Date(alert.triggered_at).toLocaleString('id-ID') }} WIB</strong>
                            </p>
                            <p v-if="alert.handled_by" class="text-xs text-amber-900 font-bold bg-amber-50 px-3 py-1 rounded-lg inline-block border border-amber-200">
                                👨‍⚕️ Sedang ditangani oleh: <strong>{{ alert.handled_by }}</strong>
                            </p>
                        </div>

                        <!-- Action Control Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                v-if="alert.status === 'pending' || alert.status === 'delivered'"
                                type="button"
                                @click="acknowledgeAlert"
                                :disabled="isAcknowledgeLoading"
                                class="px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black transition-all shadow-md active:scale-95 cursor-pointer disabled:opacity-50 flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span>Terima & Tangani Kasus</span>
                            </button>

                            <Link
                                v-if="alert.status === 'being_handled'"
                                :href="route('bidan.referrals.create', alert.id)"
                                class="px-4 py-2.5 rounded-2xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold transition-all shadow-xs flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">local_hospital</span>
                                <span>Proses Rujukan PONEK</span>
                            </Link>

                            <button
                                v-if="alert.status === 'being_handled'"
                                type="button"
                                @click="isResolveModalOpen = true"
                                class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">task_alt</span>
                                <span>Selesaikan Kasus</span>
                            </button>

                            <button
                                v-if="alert.can_cancel_handling"
                                type="button"
                                @click="isCancelModalOpen = true"
                                class="px-4 py-2.5 rounded-2xl bg-neutral-200 hover:bg-neutral-300 text-[#123356] text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5"
                            >
                                <span class="material-symbols-outlined text-base">undo</span>
                                <span>Batalkan Penanganan (2 Menit)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Details Grid -->
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gejala Red Flag & Rekomendasi Klinis -->
                        <div class="p-5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-3">
                            <h3 class="font-extrabold text-sm text-[#123356] flex items-center gap-2">
                                <span class="material-symbols-outlined text-rose-600">warning</span>
                                <span>Gejala Kritis Terdeteksi (Red Flag)</span>
                            </h3>

                            <div v-if="alert.triggered_symptoms && alert.triggered_symptoms.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="(symptom, idx) in alert.triggered_symptoms"
                                    :key="idx"
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-rose-100 text-rose-900 border border-rose-200 text-xs font-bold"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    <span>{{ symptom }}</span>
                                </span>
                            </div>
                            <p v-else class="text-xs text-[#73777F] italic">
                                Alert dipicu secara mandiri via tombol SOS darurat.
                            </p>

                            <div v-if="alert.recommendation_text" class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                                <strong>Rekomendasi Medis:</strong> {{ alert.recommendation_text }}
                            </div>
                        </div>

                        <!-- Titik Lokasi GPS & Navigasi -->
                        <div class="p-5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-3">
                            <h3 class="font-extrabold text-sm text-[#123356] flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">location_on</span>
                                <span>Lokasi Kejadian & Navigasi GPS</span>
                            </h3>

                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-[#73777F] block text-[11px] font-bold uppercase">Alamat Pasien</span>
                                    <p class="font-bold text-[#123356]">{{ alert.pregnancy.address || 'Alamat tidak tersedia' }}</p>
                                </div>

                                <div v-if="alert.latitude && alert.longitude" class="pt-2 border-t border-[#E3E2E5] flex items-center justify-between">
                                    <div>
                                        <span class="text-[#73777F] block text-[11px] font-mono">Koordinat: {{ alert.latitude }}, {{ alert.longitude }}</span>
                                    </div>
                                    <a
                                        :href="`https://www.google.com/maps/dir/?api=1&destination=${alert.latitude},${alert.longitude}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="px-3 py-1.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs flex items-center gap-1"
                                    >
                                        <span class="material-symbols-outlined text-sm">navigation</span>
                                        <span>Buka Google Maps</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kontak Cepat Ibu & Keluarga -->
                    <div class="p-5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-3">
                        <h3 class="font-extrabold text-sm text-[#123356] flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">call</span>
                            <span>Panggilan Komunikasi Cepat</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <!-- Hubungi Pasien -->
                            <div class="p-3 rounded-xl bg-white border border-[#E3E2E5] flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] text-[#73777F] font-bold uppercase block">Ibu Hamil</span>
                                    <strong class="text-sm text-[#123356]">{{ alert.pregnancy.mother_name }}</strong>
                                    <span class="text-[11px] text-[#73777F] block font-mono">{{ alert.pregnancy.phone_number || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a
                                        v-if="alert.pregnancy.phone_number"
                                        :href="'tel:' + alert.pregnancy.phone_number"
                                        class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs"
                                        title="Telepon Seluler"
                                    >
                                        <span class="material-symbols-outlined text-base">call</span>
                                    </a>
                                    <a
                                        v-if="alert.pregnancy.phone_number"
                                        :href="formatWaUrl(alert.pregnancy.phone_number)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="p-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-all shadow-xs"
                                        title="Kirim WhatsApp"
                                    >
                                        <span class="material-symbols-outlined text-base">chat</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Hubungi Kontak Darurat (Keluarga) -->
                            <div class="p-3 rounded-xl bg-white border border-[#E3E2E5] flex items-center justify-between gap-3">
                                <div>
                                    <span class="text-[10px] text-[#73777F] font-bold uppercase block">Kontak Darurat (Keluarga)</span>
                                    <strong class="text-sm text-[#123356]">{{ alert.pregnancy.emergency_contact_name || 'Keluarga' }}</strong>
                                    <span class="text-[11px] text-[#73777F] block font-mono">{{ alert.pregnancy.emergency_contact_phone || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a
                                        v-if="alert.pregnancy.emergency_contact_phone"
                                        :href="'tel:' + alert.pregnancy.emergency_contact_phone"
                                        class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-xs"
                                        title="Telepon Seluler"
                                    >
                                        <span class="material-symbols-outlined text-base">call</span>
                                    </a>
                                    <a
                                        v-if="alert.pregnancy.emergency_contact_phone"
                                        :href="formatWaUrl(alert.pregnancy.emergency_contact_phone)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="p-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white transition-all shadow-xs"
                                        title="Kirim WhatsApp"
                                    >
                                        <span class="material-symbols-outlined text-base">chat</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ModalBox: Batalkan Penanganan -->
        <ModalBox
            :show="isCancelModalOpen"
            type="warning"
            title="Batalkan Penanganan Kasus"
            message="Jika Anda terhalang hal mendesak, kasus ini akan otomatis dikembalikan ke antrean darurat agar segera diambil alih oleh nakes atau kader pendamping lainnya."
            confirm-text="Ya, Batalkan Penanganan"
            :confirm-disabled="isCancelLoading || !cancelForm.reason.trim()"
            :loading="isCancelLoading"
            @close="isCancelModalOpen = false"
            @cancel="isCancelModalOpen = false"
            @confirm="cancelHandling"
        >
            <form @submit.prevent="cancelHandling" class="space-y-3 pt-2">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Alasan Pembatalan (Wajib)</label>
                    <textarea
                        v-model="cancelForm.reason"
                        rows="3"
                        required
                        placeholder="Contoh: Terhalang persalinan darurat lain di klinik, mohon dialihkan ke Bidan Siti..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>

        <!-- ModalBox: Selesaikan Kasus -->
        <ModalBox
            :show="isResolveModalOpen"
            type="success"
            title="Konfirmasi Selesaikan Kasus Darurat"
            message="Pastikan pasien telah mendapatkan pertolongan pertama, evaluasi klinis yang memadai, atau telah tiba dengan selamat di faskes rujukan."
            confirm-text="Tandai Selesai"
            :confirm-disabled="isResolveLoading"
            :loading="isResolveLoading"
            @close="isResolveModalOpen = false"
            @cancel="isResolveModalOpen = false"
            @confirm="resolveAlert"
        />
    </BidanLayout>
</template>
