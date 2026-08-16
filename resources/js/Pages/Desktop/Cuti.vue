<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    worker: {
        type: Object,
        required: true,
    },
});

const isLeaveModalOpen = ref(false);
const isSubmitting = ref(false);

const leaveForm = useForm({
    reason: 'Cuti Tahunan / Melahirkan',
    unavailable_from: new Date().toISOString().split('T')[0],
    unavailable_until: '',
    notes: '',
});

const submitLeave = () => {
    isSubmitting.value = true;
    leaveForm.post(route('bidan.availability.deactivate'), {
        onFinish: () => {
            isSubmitting.value = false;
            isLeaveModalOpen.value = false;
        },
    });
};

const reactivateDuty = () => {
    isSubmitting.value = true;
    router.post(route('bidan.availability.reactivate'), {}, {
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Kontrol Ketersediaan & Cuti — SIGADIS Nakes" />

    <BidanLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- 1. Header Section -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                    <span class="material-symbols-outlined text-sm">event_available</span>
                    <span>Zero Blind Spot Escalation System</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Kontrol Ketersediaan & Mode Cuti
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E]">
                    Atur status kesiapan tugas harian Anda agar sistem SIGADIS dapat secara cerdas mengalihkan alert darurat langsung ke nakes pengganti saat Anda berhalangan.
                </p>
            </div>

            <!-- 2. Status Card Live -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div :class="['p-6 sm:p-8 border-b', worker.is_available ? 'bg-gradient-to-r from-emerald-50 to-green-50/40 border-emerald-200' : 'bg-gradient-to-r from-rose-50 to-red-50/40 border-rose-200']">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span :class="['w-3.5 h-3.5 rounded-full', worker.is_available ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500']"></span>
                                <span class="text-xs font-black uppercase tracking-wider text-[#123356]">Status Kesiapan Saat Ini:</span>
                            </div>
                            <h2 class="text-2xl font-black text-[#123356]">
                                {{ worker.is_available ? 'Sedang Bertugas (On-Duty)' : 'Sedang Cuti / Berhalangan (On-Leave)' }}
                            </h2>
                            <p class="text-xs text-[#43474E]">
                                {{ worker.is_available
                                    ? 'Anda saat ini aktif menerima sinyal panggilan SOS darurat dan penugasan ibu hamil di wilayah binaan.'
                                    : `Anda ditandai tidak aktif sementara sejak ${worker.unavailable_from || 'hari ini'} hingga ${worker.unavailable_until || 'reaktivasi manual'}.`
                                }}
                            </p>
                        </div>

                        <div>
                            <button
                                v-if="worker.is_available"
                                type="button"
                                @click="isLeaveModalOpen = true"
                                class="px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">event_busy</span>
                                <span>Ajukan Mode Cuti / Berhalangan</span>
                            </button>

                            <button
                                v-else
                                type="button"
                                @click="reactivateDuty"
                                :disabled="isSubmitting"
                                class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md active:scale-95 cursor-pointer disabled:opacity-50 flex items-center gap-2"
                            >
                                <span class="material-symbols-outlined text-base">check_circle</span>
                                <span>Kembali Bertugas Sekarang</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Guidance & Explanation -->
                <div class="p-6 sm:p-8 space-y-4">
                    <h3 class="font-extrabold text-sm text-[#123356] flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">info</span>
                        <span>Mekanisme Pengalihan Eskalasi Darurat Otomatis</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-[#43474E]">
                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-2">
                            <strong class="text-[#123356] block">🛡️ Pengalihan Instan Tanpa Delay</strong>
                            <p>Saat Anda berstatus Cuti, sistem tidak akan menunggu timeout 3 menit. Alert SOS darurat dari pasien wilayah Anda akan langsung diarahkan ke Kader Pendamping Secondary atau Bidan Wilayah terdekat.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-2">
                            <strong class="text-[#123356] block">⚡ Reaktivasi Kapan Saja</strong>
                            <p>Jika masa berhalangan selesai lebih cepat, Anda dapat mengklik tombol "Kembali Bertugas Sekarang" tanpa perlu konfirmasi admin tambahan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ModalBox: Ajukan Mode Cuti -->
        <ModalBox
            :show="isLeaveModalOpen"
            type="warning"
            title="Ajukan Mode Cuti / Berhalangan"
            message="Selama masa cuti, sinyal darurat dari pasien binaan Anda akan otomatis dialihkan ke kader/nakes pendamping lain untuk memastikan keselamatan maternal tetap terjaga."
            confirm-text="Aktifkan Status Cuti"
            :confirm-disabled="isSubmitting"
            :loading="isSubmitting"
            @close="isLeaveModalOpen = false"
            @cancel="isLeaveModalOpen = false"
            @confirm="submitLeave"
        >
            <form @submit.prevent="submitLeave" class="space-y-3 pt-2">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Alasan Berhalangan</label>
                    <select
                        v-model="leaveForm.reason"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    >
                        <option value="Cuti Tahunan / Melahirkan">Cuti Tahunan / Melahirkan</option>
                        <option value="Sakit / Izin Medis">Sakit / Izin Medis</option>
                        <option value="Tugas Luar / Pelatihan">Tugas Luar / Pelatihan Kedinasan</option>
                        <option value="Lainnya">Keperluan Mendesak Lainnya</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">Mulai Tanggal</label>
                        <input
                            v-model="leaveForm.unavailable_from"
                            type="date"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">Sampai Tanggal (Opsional)</label>
                        <input
                            v-model="leaveForm.unavailable_until"
                            type="date"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Catatan Nakes Pengganti (Opsional)</label>
                    <textarea
                        v-model="leaveForm.notes"
                        rows="2"
                        placeholder="Contoh: Dilimpahkan sementara ke Bidan Nurul di Poskesdes B..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>
    </BidanLayout>
</template>
