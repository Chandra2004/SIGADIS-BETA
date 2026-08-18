<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    motherName: {
        type: String,
        default: 'Ibu Hamil',
    },
    pregnancy: {
        type: Object,
        default: () => ({}),
    },
    screeningHistory: {
        type: Array,
        default: () => [],
    },
    clinicalVisits: {
        type: Array,
        default: () => [],
    },
});

const activeTab = ref('screening'); // 'screening' or 'anc'

const getRiskBadge = (level) => {
    switch (level) {
        case 'tinggi':
            return { label: 'Risiko Tinggi', color: 'bg-red-100 text-[#D64550]', icon: 'warning' };
        case 'sedang':
            return { label: 'Risiko Sedang', color: 'bg-amber-100 text-[#E0A030]', icon: 'info' };
        default:
            return { label: 'Risiko Rendah', color: 'bg-emerald-100 text-[#4C9A6E]', icon: 'check_circle' };
    }
};
</script>

<template>
    <MobileLayout
        :title="`Riwayat Kehamilan — SIGADIS Mobile`"
        activeTab="history"
        :motherName="motherName"
    >
        <!-- Header Judul Halaman -->
        <div class="pt-1 pb-2">
            <h1 class="text-xl font-black text-[#123356] tracking-tight">
                Riwayat Rekam Medis
            </h1>
            <p class="text-xs text-[#73777F]">
                Arsip hasil skrining mandiri dan catatan kunjungan klinis ANC
            </p>
        </div>

        <!-- Header Profil Ringkas -->
        <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-[#73777F] uppercase tracking-wider">Profil Pemantauan</span>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-[#123356] text-[11px] font-bold capitalize">
                    Status: {{ pregnancy?.status || 'hamil' }}
                </span>
            </div>
            <h2 class="text-lg font-extrabold text-[#123356]">{{ pregnancy?.mother_name || motherName }}</h2>
            <div class="grid grid-cols-2 gap-2 text-xs text-[#73777F] mt-2 pt-2 border-t border-gray-100">
                <div>
                    <span>Usia Kehamilan:</span>
                    <p class="font-bold text-[#123356]">{{ pregnancy?.current_gestational_age_weeks || '-' }} Minggu</p>
                </div>
                <div>
                    <span>Perkiraan Lahir (HPL):</span>
                    <p class="font-bold text-[#123356]">{{ pregnancy?.estimated_due_date || '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Tab Selector: Skrining Mandiri vs Kunjungan Nakes ANC -->
        <div class="flex bg-gray-100 p-1 rounded-2xl mb-4">
            <button
                @click="activeTab = 'screening'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                :class="activeTab === 'screening' ? 'bg-white text-[#123356] shadow-xs' : 'text-[#73777F]'"
            >
                <span class="material-symbols-outlined text-base">quiz</span>
                <span>Skrining Mandiri</span>
            </button>
            <button
                @click="activeTab = 'anc'"
                class="flex-1 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                :class="activeTab === 'anc' ? 'bg-white text-[#123356] shadow-xs' : 'text-[#73777F]'"
            >
                <span class="material-symbols-outlined text-base">clinical_notes</span>
                <span>Catatan Bidan (ANC)</span>
            </button>
        </div>

        <!-- 1. TAB RIWAYAT SKRINING -->
        <section v-if="activeTab === 'screening'" class="space-y-3 animate-fade-in">
            <div v-if="screeningHistory.length === 0" class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-xs">
                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">history_edu</span>
                <h3 class="text-sm font-bold text-[#123356]">Belum Ada Riwayat Skrining</h3>
                <p class="text-xs text-[#73777F] mt-1 mb-4">Ibu belum pernah melakukan skrining mandiri sebelumnya.</p>
                <Link
                    :href="route('mobile.screening.index')"
                    class="inline-flex items-center gap-1.5 py-2.5 px-4 rounded-xl bg-[#123356] text-white text-xs font-bold shadow-xs"
                >
                    Mulai Skrining Pertama
                </Link>
            </div>

            <div
                v-for="session in screeningHistory"
                :key="session.id"
                class="bg-white rounded-3xl p-4 border border-gray-100 shadow-xs hover:border-[#F3AEC0] transition-all space-y-2.5"
            >
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[#73777F] flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">schedule</span>
                        {{ session.date }}
                    </span>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1"
                        :class="getRiskBadge(session.risk_level).color"
                    >
                        <span class="material-symbols-outlined text-[12px]">{{ getRiskBadge(session.risk_level).icon }}</span>
                        {{ getRiskBadge(session.risk_level).label }}
                    </span>
                </div>

                <div class="bg-[#FDF3F6] p-3 rounded-2xl text-xs text-[#123356] leading-relaxed">
                    <strong class="block text-[10px] text-[#73777F] uppercase tracking-wide mb-0.5">Rekomendasi</strong>
                    {{ session.recommendation }}
                </div>

                <!-- Toggle Jawaban Gejala -->
                <div v-if="session.answers && session.answers.length > 0" class="pt-1 text-[11px] text-[#73777F]">
                    <span class="font-semibold text-[#123356] block mb-1">Gejala yang dilaporkan:</span>
                    <ul class="space-y-1 list-disc list-inside text-[#26292E]">
                        <li v-for="(ans, idx) in session.answers" :key="idx">
                            {{ ans.question }} : <strong class="capitalize" :class="ans.answer === 'ya' ? 'text-[#D64550]' : 'text-[#4C9A6E]'">{{ ans.answer }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 2. TAB CATATAN FISIK DARI BIDAN (ANC LOG) -->
        <section v-else class="space-y-3 animate-fade-in">
            <div v-if="clinicalVisits.length === 0" class="bg-white rounded-3xl p-8 text-center border border-gray-100 shadow-xs">
                <span class="material-symbols-outlined text-4xl text-gray-300 mb-2">medical_services</span>
                <h3 class="text-sm font-bold text-[#123356]">Belum Ada Catatan Pemeriksaan</h3>
                <p class="text-xs text-[#73777F] mt-1">Bidan akan mencatat hasil tensi dan detak jantung janin saat kunjungan fisik posyandu/Puskesmas.</p>
            </div>

            <div
                v-for="visit in clinicalVisits"
                :key="visit.id"
                class="bg-white rounded-3xl p-4 border border-gray-100 shadow-xs space-y-2.5"
            >
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <span class="text-xs font-bold text-[#123356]">{{ visit.date }}</span>
                    <span class="text-[11px] text-[#73777F]">{{ visit.midwife_name }}</span>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2 bg-blue-50/50 rounded-xl">
                        <span class="text-[10px] text-[#73777F] block">Tekanan Darah</span>
                        <strong class="text-[#123356]">{{ visit.blood_pressure }}</strong>
                    </div>
                    <div class="p-2 bg-pink-50/50 rounded-xl">
                        <span class="text-[10px] text-[#73777F] block">Berat Badan</span>
                        <strong class="text-[#123356]">{{ visit.weight_kg }}</strong>
                    </div>
                    <div class="p-2 bg-emerald-50/50 rounded-xl">
                        <span class="text-[10px] text-[#73777F] block">Denyut Janin</span>
                        <strong class="text-[#123356]">{{ visit.fetal_heart_rate }}</strong>
                    </div>
                </div>

                <div class="p-2.5 bg-gray-50 rounded-xl text-xs text-[#26292E]">
                    <strong class="block text-[10px] text-[#73777F] uppercase tracking-wide mb-0.5">Catatan Bidan</strong>
                    {{ visit.clinical_notes }}
                </div>
            </div>
        </section>
    </MobileLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
