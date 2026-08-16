<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    pregnancy: {
        type: Object,
        required: true,
    },
    screeningSessions: {
        type: Array,
        default: () => [],
    },
    referrals: {
        type: Array,
        default: () => [],
    },
    clinicalVisits: {
        type: Array,
        default: () => [],
    },
    postpartumAssessment: {
        type: Object,
        default: null,
    },
    caseStatus: {
        type: Object,
        default: () => ({}),
    },
    canManage: {
        type: Boolean,
        default: false,
    },
    canCancelNifas: {
        type: Boolean,
        default: false,
    },
});

// Modal states
const isDeliveryModalOpen = ref(false);
const isCancelNifasModalOpen = ref(false);
const isCloseCaseModalOpen = ref(false);
const isClinicalVisitModalOpen = ref(false);

const deliveryForm = useForm({
    delivered_at: new Date().toISOString().split('T')[0],
    delivery_notes: '',
});

const closeCaseForm = useForm({
    summary_notes: '',
    contraception_chosen: '',
});

const clinicalVisitForm = useForm({
    visited_at: new Date().toISOString().split('T')[0],
    systolic_bp: '',
    diastolic_bp: '',
    weight_kg: '',
    fundal_height_cm: '',
    fetal_heart_rate_bpm: '',
    clinical_notes: '',
});

const submitDelivery = () => {
    deliveryForm.post(route('bidan.patients.mark-delivered', props.pregnancy.id), {
        onSuccess: () => {
            isDeliveryModalOpen.value = false;
        },
    });
};

const submitCancelNifas = () => {
    router.post(route('bidan.patients.cancel-nifas', props.pregnancy.id), {}, {
        onSuccess: () => {
            isCancelNifasModalOpen.value = false;
        },
    });
};

const submitCloseCase = () => {
    closeCaseForm.post(route('bidan.patients.close-case', props.pregnancy.id), {
        onSuccess: () => {
            isCloseCaseModalOpen.value = false;
        },
    });
};

const submitClinicalVisit = () => {
    clinicalVisitForm.post(route('bidan.patients.clinical-visits.store', props.pregnancy.id), {
        onSuccess: () => {
            isClinicalVisitModalOpen.value = false;
            clinicalVisitForm.reset();
        },
    });
};

const getRiskBadge = (level) => {
    switch (level) {
        case 'tinggi':
            return { label: 'Risiko Tinggi', bg: 'bg-rose-100 text-rose-800 border-rose-300', dot: 'bg-rose-600 animate-pulse' };
        case 'sedang':
            return { label: 'Risiko Sedang', bg: 'bg-amber-100 text-amber-900 border-amber-300', dot: 'bg-amber-500' };
        case 'rendah':
            return { label: 'Risiko Rendah', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300', dot: 'bg-emerald-500' };
        default:
            return { label: 'Belum Dinilai', bg: 'bg-neutral-100 text-neutral-700 border-neutral-200', dot: 'bg-neutral-400' };
    }
};
</script>

<template>
    <Head :title="`Rekam Medis: ${pregnancy.mother_name} — SIGADIS`" />

    <BidanLayout>
        <div class="space-y-6">
            <!-- 1. Navigation Back & Quick Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <Link
                    :href="route('bidan.dashboard')"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-white border border-[#E3E2E5] text-xs font-bold text-[#123356] hover:bg-neutral-50 transition-all shadow-xs"
                >
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali ke Dashboard</span>
                </Link>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-if="canManage"
                        type="button"
                        @click="isClinicalVisitModalOpen = true"
                        class="px-4 py-2 rounded-2xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-base">add_circle</span>
                        <span>+ Rekam Kunjungan ANC</span>
                    </button>

                    <button
                        v-if="canManage && pregnancy.status === 'hamil'"
                        type="button"
                        @click="isDeliveryModalOpen = true"
                        class="px-4 py-2 rounded-2xl bg-purple-700 text-white text-xs font-bold hover:bg-purple-800 transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-base">child_care</span>
                        <span>Tandai Telah Bersalin</span>
                    </button>

                    <button
                        v-if="canCancelNifas"
                        type="button"
                        @click="isCancelNifasModalOpen = true"
                        class="px-3.5 py-2 rounded-2xl bg-neutral-200 text-[#123356] text-xs font-bold hover:bg-neutral-300 transition-all cursor-pointer flex items-center gap-1"
                    >
                        <span class="material-symbols-outlined text-sm">undo</span>
                        <span>Batal Nifas (24 Jam)</span>
                    </button>

                    <button
                        v-if="canManage && pregnancy.status === 'nifas'"
                        type="button"
                        @click="isCloseCaseModalOpen = true"
                        class="px-4 py-2 rounded-2xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span>Tutup Kasus (42 Hari Selesai)</span>
                    </button>

                    <a
                        :href="route('bidan.patients.export-history', pregnancy.id)"
                        class="px-3.5 py-2 rounded-2xl bg-white border border-[#E3E2E5] text-xs font-bold text-[#123356] hover:bg-neutral-50 transition-all shadow-xs flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-sm">download</span>
                        <span>Unduh PDF</span>
                    </a>
                </div>
            </div>

            <!-- 2. Patient Profile Banner Card -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                                {{ pregnancy.mother_name }}
                            </h1>
                            <span
                                :class="[
                                    'px-2.5 py-0.5 rounded-full text-xs font-bold uppercase',
                                    pregnancy.status === 'hamil' ? 'bg-blue-100 text-blue-900 border border-blue-200' : (pregnancy.status === 'nifas' ? 'bg-purple-100 text-purple-900 border border-purple-200' : 'bg-neutral-100 text-neutral-800')
                                ]"
                            >
                                Status: {{ pregnancy.status }}
                            </span>
                        </div>
                        <p class="text-xs text-[#73777F]">
                            Domisili: {{ pregnancy.address || 'Belum terisi' }} • Bidan Pendamping: <strong class="text-[#123356]">{{ caseStatus.primary_midwife || 'Bidan Desa' }}</strong>
                        </p>
                    </div>

                    <!-- Right Stats Pill -->
                    <div class="flex items-center gap-3">
                        <div v-if="pregnancy.status === 'hamil'" class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-center">
                            <span class="text-[10px] font-bold text-blue-800 uppercase block">Usia Kehamilan</span>
                            <span class="text-lg font-black text-blue-950">{{ pregnancy.gestational_age_weeks || '-' }} Minggu</span>
                        </div>
                        <div v-else-if="pregnancy.status === 'nifas'" class="p-3 rounded-2xl bg-purple-50 border border-purple-200 text-center">
                            <span class="text-[10px] font-bold text-purple-800 uppercase block">Masa Nifas</span>
                            <span class="text-lg font-black text-purple-950">Hari ke-{{ caseStatus.nifas_day || 1 }} / 42</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] font-bold text-slate-700 uppercase block">Total Kunjungan/Skrining</span>
                            <span class="text-lg font-black text-slate-900">{{ caseStatus.total_visits || 0 }} Kali</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Two Columns: Timeline Skrining & Kunjungan ANC -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Screening History Timeline (2 Cols) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-6">
                        <div class="flex items-center justify-between border-b border-[#F2F3F5] pb-4">
                            <h2 class="text-base font-extrabold text-[#123356] flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600">history_edu</span>
                                <span>Garis Waktu Skrining Mandiri</span>
                            </h2>
                            <span class="text-xs font-bold text-[#73777F]">{{ screeningSessions.length }} Sesi Skrining</span>
                        </div>

                        <div v-if="screeningSessions.length === 0" class="py-8 text-center text-xs text-[#73777F]">
                            Belum ada riwayat skrining mandiri yang dicatat.
                        </div>

                        <!-- Stepper Timeline -->
                        <div v-else class="relative border-l-2 border-[#E3E2E5] ml-4 space-y-6 pb-2">
                            <div
                                v-for="session in screeningSessions"
                                :key="session.id"
                                class="relative pl-6 space-y-2"
                            >
                                <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-[#123356] border-4 border-white shadow-xs"></div>
                                <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-2 hover:shadow-xs transition-shadow">
                                    <div class="flex items-center justify-between gap-2">
                                        <div>
                                            <span class="text-[11px] font-bold text-[#73777F] uppercase tracking-wider block">
                                                {{ new Date(session.started_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }} WIB
                                            </span>
                                            <span class="text-xs font-extrabold text-[#123356] capitalize">Sesi Skrining: {{ session.session_type || 'Berkala' }}</span>
                                        </div>

                                        <span
                                            v-if="session.risk_assessment"
                                            :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border', getRiskBadge(session.risk_assessment.risk_level).bg]"
                                        >
                                            <span :class="['w-1.5 h-1.5 rounded-full', getRiskBadge(session.risk_assessment.risk_level).dot]"></span>
                                            <span>{{ getRiskBadge(session.risk_assessment.risk_level).label }}</span>
                                        </span>
                                    </div>

                                    <div v-if="session.risk_assessment?.recommendation_text" class="p-2.5 rounded-xl bg-white border border-[#E3E2E5] text-xs text-[#43474E]">
                                        <strong>Rekomendasi Medis:</strong> {{ session.risk_assessment.recommendation_text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: ANC Physical Visits & Referrals (1 Col) -->
                <div class="space-y-6">
                    <!-- ANC Physical Visits Box -->
                    <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-[#F2F3F5] pb-3">
                            <h3 class="text-sm font-extrabold text-[#123356] flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">monitor_heart</span>
                                <span>Pemeriksaan ANC Fisik</span>
                            </h3>
                            <button
                                v-if="canManage"
                                type="button"
                                @click="isClinicalVisitModalOpen = true"
                                class="text-xs font-bold text-blue-600 hover:text-blue-800 cursor-pointer"
                            >
                                + Catat
                            </button>
                        </div>

                        <div v-if="clinicalVisits.length === 0" class="py-4 text-center text-xs text-[#73777F]">
                            Belum ada catatan kunjungan fisik.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="visit in clinicalVisits"
                                :key="visit.id"
                                class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-1.5 text-xs"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[#123356]">{{ new Date(visit.visited_at).toLocaleDateString('id-ID') }}</span>
                                    <span class="text-[10px] text-[#73777F]">{{ visit.midwife?.full_name }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-1 text-[11px] text-[#43474E]">
                                    <div>TD: <strong>{{ visit.systolic_bp || '-' }}/{{ visit.diastolic_bp || '-' }} mmHg</strong></div>
                                    <div>BB: <strong>{{ visit.weight_kg || '-' }} kg</strong></div>
                                    <div>TFU: <strong>{{ visit.fundal_height_cm || '-' }} cm</strong></div>
                                    <div>DJJ: <strong>{{ visit.fetal_heart_rate_bpm || '-' }} bpm</strong></div>
                                </div>
                                <p v-if="visit.clinical_notes" class="text-[11px] text-[#26292E] italic bg-white p-2 rounded-lg border border-[#E3E2E5]">
                                    "{{ visit.clinical_notes }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Referrals History Box -->
                    <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                        <div class="border-b border-[#F2F3F5] pb-3">
                            <h3 class="text-sm font-extrabold text-[#123356] flex items-center gap-2">
                                <span class="material-symbols-outlined text-rose-600">local_hospital</span>
                                <span>Riwayat Rujukan PONEK</span>
                            </h3>
                        </div>

                        <div v-if="referrals.length === 0" class="py-4 text-center text-xs text-[#73777F]">
                            Tidak ada riwayat rujukan medis.
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="refItem in referrals"
                                :key="refItem.id"
                                class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-200 space-y-1 text-xs"
                            >
                                <div class="font-bold text-rose-950">{{ refItem.facility?.name || 'RS Rujukan' }}</div>
                                <div class="text-[10px] text-rose-800">
                                    Waktu Rujuk: {{ new Date(refItem.referred_at).toLocaleDateString('id-ID') }}
                                </div>
                                <p v-if="refItem.notes" class="text-[11px] text-[#26292E] bg-white p-2 rounded-lg border border-rose-200">
                                    Catatan: {{ refItem.notes }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ModalBox: Tandai Telah Bersalin -->
        <ModalBox
            :show="isDeliveryModalOpen"
            type="primary"
            title="Tandai Telah Bersalin & Transisi ke Nifas"
            message="Pasien akan otomatis dialihkan ke fase pemantauan Nifas 42 Hari. Pastikan tanggal persalinan telah sesuai."
            confirm-text="Simpan & Transisi ke Nifas"
            :confirm-disabled="deliveryForm.processing"
            :loading="deliveryForm.processing"
            @close="isDeliveryModalOpen = false"
            @cancel="isDeliveryModalOpen = false"
            @confirm="submitDelivery"
        >
            <form @submit.prevent="submitDelivery" class="space-y-3.5 pt-2">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Tanggal Persalinan</label>
                    <input
                        v-model="deliveryForm.delivered_at"
                        type="date"
                        required
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Catatan Persalinan (Kondisi Ibu & Bayi)</label>
                    <textarea
                        v-model="deliveryForm.delivery_notes"
                        rows="3"
                        placeholder="Contoh: Lahir spontan di Puskesmas, BB 3100g, APGAR 9/10, ibu sehat dan masa pemulihan baik..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>

        <!-- ModalBox: Tutup Kasus 42 Hari Selesai -->
        <ModalBox
            :show="isCloseCaseModalOpen"
            type="success"
            title="Konfirmasi Penutupan Kasus 42 Hari Nifas"
            message="Setelah ditutup, kehamilan dan nifas ini akan diarsipkan sebagai Kasus Selesai Selamat (Case Closed Safe)."
            confirm-text="Tutup Kasus Sekarang"
            :confirm-disabled="closeCaseForm.processing"
            :loading="closeCaseForm.processing"
            @close="isCloseCaseModalOpen = false"
            @cancel="isCloseCaseModalOpen = false"
            @confirm="submitCloseCase"
        >
            <form @submit.prevent="submitCloseCase" class="space-y-3.5 pt-2">
                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Metode KB Pascapersalinan Terpilih</label>
                    <input
                        v-model="closeCaseForm.contraception_chosen"
                        type="text"
                        placeholder="Contoh: IUD / Suntik 3 Bulan / Implan"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Catatan Penutup Bidan</label>
                    <textarea
                        v-model="closeCaseForm.summary_notes"
                        rows="3"
                        placeholder="Contoh: Masa nifas 42 hari selesai tanpa penyulit, involusi uteri baik, ASI eksklusif lancar..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>

        <!-- ModalBox: Batalkan Status Nifas (24 Jam) -->
        <ModalBox
            :show="isCancelNifasModalOpen"
            type="danger"
            title="Batalkan Status Nifas"
            message="Opsi ini hanya tersedia dalam 24 jam jika terjadi kesalahan pencatatan persalinan. Status pasien akan dikembalikan ke hamil."
            confirm-text="Ya, Kembalikan ke Hamil"
            @close="isCancelNifasModalOpen = false"
            @cancel="isCancelNifasModalOpen = false"
            @confirm="submitCancelNifas"
        />

        <!-- ModalBox: Tambah Kunjungan ANC Fisik -->
        <ModalBox
            :show="isClinicalVisitModalOpen"
            type="primary"
            title="Rekam Kunjungan ANC / Pemeriksaan Fisik"
            confirm-text="Simpan Hasil Pemeriksaan"
            :confirm-disabled="clinicalVisitForm.processing"
            :loading="clinicalVisitForm.processing"
            @close="isClinicalVisitModalOpen = false"
            @cancel="isClinicalVisitModalOpen = false"
            @confirm="submitClinicalVisit"
        >
            <form @submit.prevent="submitClinicalVisit" class="space-y-3 pt-2">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">Tekanan Darah Sistol</label>
                        <input
                            v-model="clinicalVisitForm.systolic_bp"
                            type="number"
                            placeholder="120"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">Tekanan Darah Diastol</label>
                        <input
                            v-model="clinicalVisitForm.diastolic_bp"
                            type="number"
                            placeholder="80"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">Berat Badan (kg)</label>
                        <input
                            v-model="clinicalVisitForm.weight_kg"
                            type="number"
                            step="0.1"
                            placeholder="60.5"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">TFU (cm)</label>
                        <input
                            v-model="clinicalVisitForm.fundal_height_cm"
                            type="number"
                            step="0.5"
                            placeholder="28"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-extrabold text-[#26292E]">DJJ (bpm)</label>
                        <input
                            v-model="clinicalVisitForm.fetal_heart_rate_bpm"
                            type="number"
                            placeholder="140"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-extrabold text-[#26292E]">Catatan Pemeriksaan / Terapi</label>
                    <textarea
                        v-model="clinicalVisitForm.clinical_notes"
                        rows="2"
                        placeholder="Contoh: Kondisi janin baik, berikan tablet Fe dan asam folat..."
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>
            </form>
        </ModalBox>
    </BidanLayout>
</template>
