<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    coverage: {
        type: Array,
        default: () => [],
    },
    assignments: {
        type: Array,
        default: () => [],
    },
    verifiedKaders: {
        type: Array,
        default: () => [],
    },
    metrics: {
        type: Object,
        default: () => ({ total_regions: 0, safe_regions: 0, gap_regions: 0, total_assignments: 0 }),
    },
});

// Modal State: Create Assignment
const isCreateModalOpen = ref(false);
const form = useForm({
    kader_id: '',
    region_code: '',
    kader_priority: 'primary',
});

const openCreateModal = (prefillRegion = '') => {
    form.reset();
    if (prefillRegion) {
        form.region_code = prefillRegion;
    }
    isCreateModalOpen.value = true;
};

const submitCreateAssignment = () => {
    form.post(route('admin.zonasi.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            form.reset();
        },
    });
};

// Modal State: Delete Assignment
const isDeleteModalOpen = ref(false);
const selectedAssignment = ref(null);
const isDeleting = ref(false);

const openDeleteModal = (assignment) => {
    selectedAssignment.value = assignment;
    isDeleteModalOpen.value = true;
};

const submitDeleteAssignment = () => {
    if (!selectedAssignment.value) return;
    isDeleting.value = true;
    router.delete(route('admin.zonasi.destroy', selectedAssignment.value.id), {
        onFinish: () => {
            isDeleting.value = false;
            isDeleteModalOpen.value = false;
            selectedAssignment.value = null;
        },
    });
};

// Filtered Gap Regions
const gapRegions = computed(() => {
    return props.coverage.filter((r) => r.has_gap);
});
</script>

<template>
    <Head title="Zonasi & Penugasan Wilayah Kader — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-900 text-xs font-bold border border-purple-200">
                        <span class="material-symbols-outlined text-sm">map</span>
                        <span>Hierarki Pengawalan Maternal</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Zonasi & Penugasan Wilayah Kader
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Atur hierarki eskalasi darurat kader desa (Primary & Secondary) untuk mencegah kekosongan pengawalan (zero coverage).
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openCreateModal('')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#F3AEC0]">add_location_alt</span>
                        <span>Tugaskan Kader Baru</span>
                    </button>
                </div>
            </div>

            <!-- Metrik Ringkas Zonasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Total Wilayah Binaan</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total_regions }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Desa / Wilayah</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Cakupan Lengkap</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-700">{{ metrics.safe_regions }}</span>
                        <span class="text-xs font-semibold text-emerald-700 font-bold">Aman</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Celah Cakupan (Gap)</span>
                    <div class="flex items-baseline gap-2">
                        <span :class="['text-3xl font-extrabold', metrics.gap_regions > 0 ? 'text-amber-700' : 'text-emerald-700']">
                            {{ metrics.gap_regions }}
                        </span>
                        <span class="text-xs font-semibold text-[#73777F]">Wilayah</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Total Penugasan Aktif</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total_assignments }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Kader Bertugas</span>
                    </div>
                </div>
            </div>

            <!-- Gap Alert Warning Banner (Jika ada desa tanpa kader) -->
            <div
                v-if="gapRegions.length > 0"
                class="p-5 rounded-3xl bg-amber-50 border border-amber-200 text-amber-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">warning</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold text-amber-900">
                            Peringatan: Terdapat {{ gapRegions.length }} Wilayah Belum Memiliki Pengawalan Lengkap!
                        </h2>
                        <p class="text-xs text-amber-800">
                            Wilayah berikut membutuhkan penugasan kader aktif agar alert darurat ibu hamil dapat segera direspons cepat:
                            <strong>{{ gapRegions.map(g => g.village_name).join(', ') }}</strong>.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="openCreateModal(gapRegions[0]?.region_code || '')"
                    class="px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition-all shrink-0 cursor-pointer shadow-xs"
                >
                    Tugaskan Sekarang
                </button>
            </div>

            <!-- 1. Matriks Cakupan Wilayah (Desa/Kelurahan) -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-50 text-purple-700">
                            <span class="material-symbols-outlined text-xl">grid_view</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Matriks Cakupan & Kesiapan Wilayah</h2>
                            <p class="text-xs text-[#73777F]">Sebaran nakes pendamping dan status eskalasi darurat per desa</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Desa / Wilayah</th>
                                <th class="py-3.5 px-4 text-center">Ibu Hamil</th>
                                <th class="py-3.5 px-4 text-center">Bidan Siaga</th>
                                <th class="py-3.5 px-4">Primary Kader (Paralel)</th>
                                <th class="py-3.5 px-4">Secondary Kader (Eskalasi)</th>
                                <th class="py-3.5 px-4 text-center">Status Cakupan</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="coverage.length === 0">
                                <td colspan="7" class="py-8 text-center text-xs text-[#73777F]">
                                    Belum ada data wilayah binaan yang tercatat di database.
                                </td>
                            </tr>

                            <tr
                                v-for="region in coverage"
                                :key="region.region_code"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 font-bold text-[#123356]">
                                    <div>{{ region.village_name }}</div>
                                    <span class="text-[11px] text-[#8A8D96] font-mono font-normal">{{ region.region_code }}</span>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-[#26292E]">
                                    <div>{{ region.total_pregnant }} Jiwa</div>
                                    <span v-if="region.high_risk > 0" class="text-[10px] text-rose-600 font-extrabold">
                                        ({{ region.high_risk }} Risiko Tinggi)
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full font-bold text-xs',
                                            region.bidan_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800'
                                        ]"
                                    >
                                        {{ region.bidan_count }} Bidan
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div v-if="region.primary_kaders.length > 0" class="space-y-1">
                                        <span
                                            v-for="kname in region.primary_kaders"
                                            :key="kname"
                                            class="inline-block px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-bold mr-1"
                                        >
                                            {{ kname }}
                                        </span>
                                    </div>
                                    <span v-else class="text-rose-600 font-bold italic text-[11px]">
                                        Belum Ada
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div v-if="region.secondary_kaders.length > 0" class="space-y-1">
                                        <span
                                            v-for="kname in region.secondary_kaders"
                                            :key="kname"
                                            class="inline-block px-2.5 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[11px] font-bold mr-1"
                                        >
                                            {{ kname }}
                                        </span>
                                    </div>
                                    <span v-else class="text-[#8A8D96] italic text-[11px]">
                                        -
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        v-if="!region.has_gap"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Lengkap
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold animate-pulse"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Perlu Kader
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button
                                        type="button"
                                        @click="openCreateModal(region.region_code)"
                                        class="px-3 py-1.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer shadow-xs active:scale-95"
                                    >
                                        + Kader
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Tabel Daftar Penugasan Kader Aktif -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-blue-50 text-[#123356]">
                            <span class="material-symbols-outlined text-xl">assignment_ind</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Daftar Penugasan Kader Aktif</h2>
                            <p class="text-xs text-[#73777F]">Kader yang terdaftar dan siap menerima notifikasi darurat wilayah</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Nama Kader & Kontak</th>
                                <th class="py-3.5 px-4">Wilayah Tugas</th>
                                <th class="py-3.5 px-4">Prioritas Penugasan</th>
                                <th class="py-3.5 px-4">Waktu Ditugaskan</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="assignments.length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    Belum ada penugasan kader yang aktif.
                                </td>
                            </tr>

                            <tr
                                v-for="assignment in assignments"
                                :key="assignment.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#123356]">{{ assignment.kader_name }}</div>
                                    <div class="text-[11px] text-[#73777F] font-mono">{{ assignment.phone_number }}</div>
                                </td>
                                <td class="py-4 px-4 font-bold text-[#26292E]">
                                    <div>{{ assignment.region_name }}</div>
                                    <span class="text-[11px] text-[#8A8D96] font-mono font-normal">{{ assignment.region_code }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-md font-bold text-[11px] uppercase tracking-wider',
                                            assignment.kader_priority === 'primary' ? 'bg-emerald-100 text-emerald-800' : 'bg-purple-100 text-purple-800'
                                        ]"
                                    >
                                        {{ assignment.kader_priority === 'primary' ? 'Primary (Utama)' : 'Secondary (Eskalasi)' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-[#73777F]">
                                    {{ assignment.created_at }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button
                                        type="button"
                                        @click="openDeleteModal(assignment)"
                                        class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-all cursor-pointer"
                                        title="Hapus Penugasan"
                                    >
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1. ModalBox Tambah Penugasan Kader -->
        <ModalBox
            :show="isCreateModalOpen"
            type="primary"
            title="Tugaskan Kader ke Wilayah"
            message="Pilih kader terverifikasi dan tentukan prioritas penerimaan notifikasi darurat."
            confirm-text="Simpan Penugasan"
            :confirm-disabled="!form.kader_id || !form.region_code || form.processing"
            :loading="form.processing"
            @close="isCreateModalOpen = false"
            @cancel="isCreateModalOpen = false"
            @confirm="submitCreateAssignment"
        >
            <form @submit.prevent="submitCreateAssignment" class="space-y-4 pt-2">
                <!-- Dropdown Kader Terverifikasi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-extrabold text-[#26292E]">Pilih Kader Terverifikasi (Wajib)</label>
                    <select
                        v-model="form.kader_id"
                        required
                        class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:border-[#123356] focus:outline-none"
                    >
                        <option value="" disabled>-- Pilih Kader --</option>
                        <option
                            v-for="kader in verifiedKaders"
                            :key="kader.id"
                            :value="kader.id"
                        >
                            {{ kader.full_name }} ({{ kader.phone_number }})
                        </option>
                    </select>
                    <p v-if="verifiedKaders.length === 0" class="text-[11px] text-amber-700">
                        Belum ada kader terverifikasi. Silakan setujui nakes di menu Verifikasi terlebih dahulu.
                    </p>
                </div>

                <!-- Input / Pilihan Wilayah -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-extrabold text-[#26292E]">Kode / Wilayah Penugasan (Wajib)</label>
                    <select
                        v-model="form.region_code"
                        required
                        class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:border-[#123356] focus:outline-none"
                    >
                        <option value="" disabled>-- Pilih Wilayah Desa --</option>
                        <option
                            v-for="region in coverage"
                            :key="region.region_code"
                            :value="region.region_code"
                        >
                            {{ region.village_name }} ({{ region.region_code }})
                        </option>
                    </select>
                </div>

                <!-- Prioritas Kader -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-extrabold text-[#26292E]">Prioritas Notifikasi Darurat</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            :class="[
                                'p-3 rounded-2xl border flex flex-col gap-1 cursor-pointer transition-all',
                                form.kader_priority === 'primary' ? 'border-[#123356] bg-blue-50/50 ring-2 ring-[#123356]/20' : 'border-[#C3C6CF] bg-[#FAF9FC]'
                            ]"
                        >
                            <input type="radio" v-model="form.kader_priority" value="primary" class="sr-only" />
                            <span class="text-xs font-extrabold text-[#123356]">Primary (Utama)</span>
                            <span class="text-[10px] text-[#73777F] leading-tight">Menerima alert darurat paralel bersamaan dengan Bidan.</span>
                        </label>

                        <label
                            :class="[
                                'p-3 rounded-2xl border flex flex-col gap-1 cursor-pointer transition-all',
                                form.kader_priority === 'secondary' ? 'border-[#123356] bg-blue-50/50 ring-2 ring-[#123356]/20' : 'border-[#C3C6CF] bg-[#FAF9FC]'
                            ]"
                        >
                            <input type="radio" v-model="form.kader_priority" value="secondary" class="sr-only" />
                            <span class="text-xs font-extrabold text-[#123356]">Secondary (Eskalasi)</span>
                            <span class="text-[10px] text-[#73777F] leading-tight">Lapisan kedua jika Primary Kader tidak merespons dalam 3 menit.</span>
                        </label>
                    </div>
                </div>
            </form>
        </ModalBox>

        <!-- 2. ModalBox Hapus Penugasan -->
        <ModalBox
            :show="isDeleteModalOpen"
            type="danger"
            title="Hapus Penugasan Wilayah Kader"
            :message="`Apakah Anda yakin ingin mencabut penugasan ${selectedAssignment?.kader_name} dari wilayah ${selectedAssignment?.region_name}?`"
            confirm-text="Ya, Hapus Penugasan"
            :loading="isDeleting"
            @close="isDeleteModalOpen = false"
            @cancel="isDeleteModalOpen = false"
            @confirm="submitDeleteAssignment"
        />
    </AdminLayout>
</template>
