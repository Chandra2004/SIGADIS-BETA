<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    questions: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ category: 'semua', session_type: 'semua', critical_only: false, search: '' }),
    },
    metrics: {
        type: Object,
        default: () => ({ total: 0, critical: 0, initial: 0, periodic: 0, nifas: 0, reviewed: 0 }),
    },
});

const search = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || 'semua');
const sessionTypeFilter = ref(props.filters.session_type || 'semua');
const criticalOnly = ref(Boolean(props.filters.critical_only));

const applyFilter = () => {
    router.get(
        route('admin.bank-soal.index'),
        {
            search: search.value,
            category: categoryFilter.value,
            session_type: sessionTypeFilter.value,
            critical_only: criticalOnly.value ? 1 : 0,
        },
        { preserveState: true, replace: true }
    );
};

// Modal State: Create / Edit Form
const isFormModalOpen = ref(false);
const isEditing = ref(false);
const selectedQuestionId = ref(null);

const form = useForm({
    code: '',
    question_text: '',
    category: 'perdarahan',
    applies_to_session_type: ['initial', 'periodic'],
    is_critical_symptom: false,
    rule_reviewed_by: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    selectedQuestionId.value = null;
    form.reset();
    form.category = 'perdarahan';
    form.applies_to_session_type = ['initial', 'periodic'];
    form.is_critical_symptom = false;
    form.rule_reviewed_by = '';
    isFormModalOpen.value = true;
};

const openEditModal = (q) => {
    isEditing.value = true;
    selectedQuestionId.value = q.id;
    form.code = q.code;
    form.question_text = q.question_text;
    form.category = q.category;
    form.applies_to_session_type = Array.isArray(q.applies_to_session_type) ? [...q.applies_to_session_type] : [];
    form.is_critical_symptom = Boolean(q.is_critical_symptom);
    form.rule_reviewed_by = q.rule_reviewed_by || '';
    isFormModalOpen.value = true;
};

const toggleSessionType = (type) => {
    const idx = form.applies_to_session_type.indexOf(type);
    if (idx > -1) {
        form.applies_to_session_type.splice(idx, 1);
    } else {
        form.applies_to_session_type.push(type);
    }
};

const submitForm = () => {
    if (isEditing.value && selectedQuestionId.value) {
        form.put(route('admin.bank-soal.update', selectedQuestionId.value), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.bank-soal.store'), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                form.reset();
            },
        });
    }
};

// Modal State: Review / Validate Protocol
const isReviewModalOpen = ref(false);
const questionToReview = ref(null);
const reviewerName = ref('');
const isReviewing = ref(false);

const openReviewModal = (q) => {
    questionToReview.value = q;
    reviewerName.value = q.rule_reviewed_by || 'dr. Sp.OG / Tim Medis Puskesmas';
    isReviewModalOpen.value = true;
};

const submitReview = () => {
    if (!questionToReview.value || !reviewerName.value.trim()) return;
    isReviewing.value = true;
    router.post(
        route('admin.bank-soal.review', questionToReview.value.id),
        { reviewer_name: reviewerName.value },
        {
            onFinish: () => {
                isReviewing.value = false;
                isReviewModalOpen.value = false;
                questionToReview.value = null;
            },
        }
    );
};

// Modal State: Delete Question
const isDeleteModalOpen = ref(false);
const questionToDelete = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (q) => {
    questionToDelete.value = q;
    isDeleteModalOpen.value = true;
};

const submitDelete = () => {
    if (!questionToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('admin.bank-soal.destroy', questionToDelete.value.id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            questionToDelete.value = null;
        },
    });
};

const getCategoryBadge = (cat) => {
    switch (cat) {
        case 'perdarahan':
            return { label: 'Perdarahan', bg: 'bg-rose-100 text-rose-900 border-rose-200' };
        case 'preeklamsia':
            return { label: 'Preeklamsia / Hipertensi', bg: 'bg-purple-100 text-purple-900 border-purple-200' };
        case 'infeksi':
            return { label: 'Infeksi / Demam', bg: 'bg-amber-100 text-amber-900 border-amber-200' };
        case 'gerakan_janin':
            return { label: 'Gerakan Janin', bg: 'bg-blue-100 text-blue-900 border-blue-200' };
        case 'nyeri_perut':
            return { label: 'Nyeri Perut / Kontraksi', bg: 'bg-orange-100 text-orange-900 border-orange-200' };
        case 'kejang':
            return { label: 'Kejang / Eklamsia', bg: 'bg-red-200 text-red-950 border-red-300 font-black' };
        case 'nifas_lain':
            return { label: 'Masa Nifas / Postpartum', bg: 'bg-emerald-100 text-emerald-900 border-emerald-200' };
        default:
            return { label: cat, bg: 'bg-neutral-100 text-neutral-800 border-neutral-200' };
    }
};
</script>

<template>
    <Head title="Bank Soal Skrining & Protokol Medis — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 text-teal-900 text-xs font-bold border border-teal-200">
                        <span class="material-symbols-outlined text-sm">quiz</span>
                        <span>Standar Medis Kemenkes RI & WHO</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Bank Soal & Protokol Skrining
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Kelola instrumen pertanyaan deteksi dini, aturan gejala kritis (Red Flag), dan riwayat audit validasi tata kelola klinis.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#F3AEC0]">add_task</span>
                        <span>Tambah Pertanyaan Baru</span>
                    </button>
                </div>
            </div>

            <!-- Metrik Ringkas Bank Soal -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#73777F] uppercase tracking-wider block">Total Soal</span>
                    <div class="text-2xl font-extrabold text-[#123356]">{{ metrics.total }}</div>
                </div>

                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Red Flag 🚨</span>
                    <div class="text-2xl font-extrabold text-rose-600">{{ metrics.critical }}</div>
                </div>

                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#73777F] uppercase tracking-wider block">Skrining Awal</span>
                    <div class="text-2xl font-extrabold text-[#123356]">{{ metrics.initial }}</div>
                </div>

                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#73777F] uppercase tracking-wider block">Berkala (Periodic)</span>
                    <div class="text-2xl font-extrabold text-[#123356]">{{ metrics.periodic }}</div>
                </div>

                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-[#73777F] uppercase tracking-wider block">Skrining Nifas</span>
                    <div class="text-2xl font-extrabold text-[#123356]">{{ metrics.nifas }}</div>
                </div>

                <div class="bg-white p-4 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-1">
                    <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">Divalidasi Medis</span>
                    <div class="text-2xl font-extrabold text-emerald-700">{{ metrics.reviewed }}</div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-2xl border border-[#E3E2E5] shadow-xs flex flex-col lg:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari kode gejala, teks pertanyaan, atau peninjau medis..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    <select
                        v-model="categoryFilter"
                        @change="applyFilter"
                        class="py-2.5 px-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="semua">Semua Kategori</option>
                        <option value="perdarahan">Perdarahan</option>
                        <option value="preeklamsia">Preeklamsia</option>
                        <option value="infeksi">Infeksi</option>
                        <option value="gerakan_janin">Gerakan Janin</option>
                        <option value="nyeri_perut">Nyeri Perut</option>
                        <option value="kejang">Kejang</option>
                        <option value="nifas_lain">Nifas</option>
                    </select>

                    <select
                        v-model="sessionTypeFilter"
                        @change="applyFilter"
                        class="py-2.5 px-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="semua">Semua Tipe Sesi</option>
                        <option value="initial">Sesi Awal (Initial)</option>
                        <option value="periodic">Sesi Berkala (Periodic)</option>
                        <option value="nifas">Sesi Nifas (Postpartum)</option>
                    </select>

                    <label class="flex items-center gap-1.5 px-3 py-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-bold cursor-pointer select-none">
                        <input
                            type="checkbox"
                            v-model="criticalOnly"
                            @change="applyFilter"
                            class="rounded text-rose-600"
                        />
                        <span class="text-rose-700">🚨 Red Flag</span>
                    </label>

                    <button
                        type="button"
                        @click="applyFilter"
                        class="px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <!-- Tabel Daftar Pertanyaan Skrining -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-teal-50 text-teal-700">
                            <span class="material-symbols-outlined text-xl">medical_information</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Daftar Pertanyaan Instrumen Skrining</h2>
                            <p class="text-xs text-[#73777F]">Pedoman deteksi dini risiko kehamilan dan nifas</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Kode & Teks Pertanyaan</th>
                                <th class="py-3.5 px-4">Kategori Medis</th>
                                <th class="py-3.5 px-4 text-center">Distribusi Sesi</th>
                                <th class="py-3.5 px-4">Tata Kelola Medis</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="questions.length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    Tidak ada pertanyaan skrining yang sesuai dengan filter.
                                </td>
                            </tr>

                            <tr
                                v-for="q in questions"
                                :key="q.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 max-w-md">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-mono font-bold text-[#123356] bg-neutral-100 px-2 py-0.5 rounded text-[11px]">
                                            {{ q.code }}
                                        </span>
                                        <span
                                            v-if="q.is_critical_symptom"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-extrabold text-[10px] animate-pulse"
                                        >
                                            🚨 RED FLAG
                                        </span>
                                    </div>
                                    <p class="text-xs font-semibold text-[#26292E] leading-relaxed">
                                        {{ q.question_text }}
                                    </p>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="['px-2.5 py-1 rounded-md font-bold text-[10px] uppercase border', getCategoryBadge(q.category).bg]">
                                        {{ getCategoryBadge(q.category).label }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="flex flex-wrap gap-1 justify-center">
                                        <span
                                            v-if="q.applies_to_session_type?.includes('initial')"
                                            class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-800 text-[10px] font-bold border border-blue-200"
                                        >
                                            Awal
                                        </span>
                                        <span
                                            v-if="q.applies_to_session_type?.includes('periodic')"
                                            class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200"
                                        >
                                            Berkala
                                        </span>
                                        <span
                                            v-if="q.applies_to_session_type?.includes('nifas')"
                                            class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-800 text-[10px] font-bold border border-purple-200"
                                        >
                                            Nifas
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div v-if="q.rule_reviewed_by" class="space-y-0.5">
                                        <div class="flex items-center gap-1 text-emerald-800 font-bold text-[11px]">
                                            <span class="material-symbols-outlined text-sm">verified</span>
                                            <span>{{ q.rule_reviewed_by }}</span>
                                        </div>
                                        <span class="text-[10px] text-[#73777F] font-mono block">
                                            {{ q.rule_reviewed_at ? new Date(q.rule_reviewed_at).toLocaleDateString('id-ID') : 'Tervalidasi' }}
                                        </span>
                                    </div>
                                    <button
                                        v-else
                                        type="button"
                                        @click="openReviewModal(q)"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 font-bold text-[10px] transition-all cursor-pointer"
                                    >
                                        <span class="material-symbols-outlined text-xs">edit_document</span>
                                        <span>Validasi Medis</span>
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button
                                            type="button"
                                            @click="openEditModal(q)"
                                            class="p-2 rounded-xl text-blue-600 hover:bg-blue-50 transition-all cursor-pointer"
                                            title="Edit Pertanyaan"
                                        >
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openDeleteModal(q)"
                                            class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition-all cursor-pointer"
                                            title="Hapus Pertanyaan"
                                        >
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1. ModalBox Form Tambah / Edit Pertanyaan -->
        <ModalBox
            :show="isFormModalOpen"
            type="primary"
            :title="isEditing ? 'Edit Pertanyaan Skrining' : 'Tambah Pertanyaan Skrining Baru'"
            :message="isEditing ? 'Perbarui teks pertanyaan, kategori klinis, atau aturan gejala kritis.' : 'Masukkan instrumen pertanyaan baru sesuai standar acuan Kemenkes RI/WHO.'"
            :confirm-text="isEditing ? 'Simpan Perubahan' : 'Tambah Soal'"
            :confirm-disabled="!form.code || !form.question_text || form.applies_to_session_type.length === 0 || form.processing"
            :loading="form.processing"
            @close="isFormModalOpen = false"
            @cancel="isFormModalOpen = false"
            @confirm="submitForm"
        >
            <form @submit.prevent="submitForm" class="space-y-3.5 pt-1 max-h-[60vh] overflow-y-auto pr-1">
                <!-- Kode & Kategori -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Kode Gejala / Rule (Wajib)</label>
                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="Contoh: bleeding_heavy"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[11px] font-extrabold text-[#26292E]">Kategori Medis</label>
                        <select
                            v-model="form.category"
                            class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:border-[#123356] focus:outline-none"
                        >
                            <option value="perdarahan">Perdarahan</option>
                            <option value="preeklamsia">Preeklamsia / Hipertensi</option>
                            <option value="infeksi">Infeksi / Demam</option>
                            <option value="gerakan_janin">Gerakan Janin</option>
                            <option value="nyeri_perut">Nyeri Perut / Kontraksi</option>
                            <option value="kejang">Kejang / Eklamsia</option>
                            <option value="nifas_lain">Masa Nifas / Postpartum</option>
                        </select>
                    </div>
                </div>

                <!-- Teks Pertanyaan Lengkap -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-extrabold text-[#26292E]">Teks Pertanyaan (Wajib)</label>
                    <textarea
                        v-model="form.question_text"
                        rows="3"
                        required
                        placeholder="Contoh: Apakah Ibu mengalami perdarahan banyak dari jalan lahir (lebih dari haid biasa)?"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    ></textarea>
                </div>

                <!-- Tipe Sesi Skrining -->
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-extrabold text-[#26292E]">Terapkan Pada Sesi (Pilih Minimal 1)</label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <button
                            type="button"
                            @click="toggleSessionType('initial')"
                            :class="[
                                'p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer',
                                form.applies_to_session_type.includes('initial')
                                    ? 'bg-blue-100 text-blue-900 border-blue-400'
                                    : 'bg-[#FAF9FC] text-[#73777F] border-[#C3C6CF]'
                            ]"
                        >
                            Skrining Awal
                        </button>
                        <button
                            type="button"
                            @click="toggleSessionType('periodic')"
                            :class="[
                                'p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer',
                                form.applies_to_session_type.includes('periodic')
                                    ? 'bg-emerald-100 text-emerald-900 border-emerald-400'
                                    : 'bg-[#FAF9FC] text-[#73777F] border-[#C3C6CF]'
                            ]"
                        >
                            Berkala
                        </button>
                        <button
                            type="button"
                            @click="toggleSessionType('nifas')"
                            :class="[
                                'p-2.5 rounded-xl border text-center font-bold transition-all cursor-pointer',
                                form.applies_to_session_type.includes('nifas')
                                    ? 'bg-purple-100 text-purple-900 border-purple-400'
                                    : 'bg-[#FAF9FC] text-[#73777F] border-[#C3C6CF]'
                            ]"
                        >
                            Nifas
                        </button>
                    </div>
                </div>

                <!-- Red Flag Toggle -->
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.is_critical_symptom"
                            class="rounded text-rose-600 mt-0.5"
                        />
                        <div class="space-y-0.5">
                            <span class="text-xs font-extrabold text-rose-900 block">Tandai sebagai Gejala Kritis (Red Flag) 🚨</span>
                            <span class="text-[11px] text-rose-800 leading-tight block">
                                Jawaban 'Ya' pada soal ini akan otomatis mengklasifikasikan ibu hamil ke Risiko Tinggi dan memicu tombol darurat SOS.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Peninjau Medis -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-extrabold text-[#26292E]">Peninjau Medis (Medical Governance)</label>
                    <input
                        v-model="form.rule_reviewed_by"
                        type="text"
                        placeholder="Contoh: dr. Sp.OG / Tim Medis Puskesmas"
                        class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>
            </form>
        </ModalBox>

        <!-- 2. ModalBox Validasi Medis (Review Governance) -->
        <ModalBox
            :show="isReviewModalOpen"
            type="success"
            title="Validasi Medis Protokol Skrining"
            :message="`Bubuhkan nama dokter/ahli medis untuk mencatat validasi tata kelola klinis pada pertanyaan [${questionToReview?.code}].`"
            confirm-text="Validasi Protokol"
            :confirm-disabled="!reviewerName.trim() || isReviewing"
            :loading="isReviewing"
            @close="isReviewModalOpen = false"
            @cancel="isReviewModalOpen = false"
            @confirm="submitReview"
        >
            <div class="space-y-2 mt-2">
                <label class="block text-xs font-extrabold text-[#26292E]">Nama Ahli / Peninjau Medis (Wajib)</label>
                <input
                    v-model="reviewerName"
                    type="text"
                    required
                    placeholder="Contoh: dr. Sp.OG / Tim Klinis Dinas Kesehatan"
                    class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-emerald-600 focus:outline-none"
                    autofocus
                />
            </div>
        </ModalBox>

        <!-- 3. ModalBox Hapus Pertanyaan -->
        <ModalBox
            :show="isDeleteModalOpen"
            type="danger"
            title="Hapus Pertanyaan Skrining"
            :message="`Apakah Anda yakin ingin menghapus pertanyaan [${questionToDelete?.code}] dari instrumen skrining?`"
            confirm-text="Ya, Hapus Soal"
            :loading="isDeleting"
            @close="isDeleteModalOpen = false"
            @cancel="isDeleteModalOpen = false"
            @confirm="submitDelete"
        />
    </AdminLayout>
</template>
