<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ModalBox from '@/Components/ModalBox.vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({ status: 'semua', role: 'semua', region: '', search: '' }),
    },
    pendingCount: {
        type: Number,
        default: 0,
    },
    workers: {
        type: Array,
        default: () => [],
    },
    verifiedKader: {
        type: Array,
        default: () => [],
    },
    areaAssignments: {
        type: Array,
        default: () => [],
    },
    coverage: {
        type: Array,
        default: () => [],
    },
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'semua');
const roleFilter = ref(props.filters.role || 'semua');

const applyFilter = () => {
    router.get(
        route('admin.verifikasi.index'),
        {
            search: search.value,
            status: statusFilter.value,
            role: roleFilter.value,
        },
        { preserveState: true, replace: true }
    );
};

// Modal States
const isApproveModalOpen = ref(false);
const isRejectModalOpen = ref(false);
const isCancelRejectModalOpen = ref(false);
const selectedWorker = ref(null);
const rejectNote = ref('');
const isSubmitting = ref(false);

// 1. Approve Flow
const openApproveModal = (worker) => {
    selectedWorker.value = worker;
    isApproveModalOpen.value = true;
};

const handleConfirmApprove = () => {
    if (!selectedWorker.value) return;
    isSubmitting.value = true;
    router.post(
        route('admin.verifikasi.verify', selectedWorker.value.id),
        {},
        {
            onFinish: () => {
                isSubmitting.value = false;
                isApproveModalOpen.value = false;
                selectedWorker.value = null;
            },
        }
    );
};

// 2. Reject Flow
const openRejectModal = (worker) => {
    selectedWorker.value = worker;
    rejectNote.value = '';
    isRejectModalOpen.value = true;
};

const handleConfirmReject = () => {
    if (!selectedWorker.value || !rejectNote.value.trim()) return;
    isSubmitting.value = true;
    router.post(
        route('admin.verifikasi.reject', selectedWorker.value.id),
        { note: rejectNote.value },
        {
            onFinish: () => {
                isSubmitting.value = false;
                isRejectModalOpen.value = false;
                selectedWorker.value = null;
            },
        }
    );
};

// 3. Cancel Rejection Flow
const openCancelRejectModal = (worker) => {
    selectedWorker.value = worker;
    isCancelRejectModalOpen.value = true;
};

const handleConfirmCancelReject = () => {
    if (!selectedWorker.value) return;
    isSubmitting.value = true;
    router.post(
        route('admin.verifikasi.cancel-reject', selectedWorker.value.id),
        {},
        {
            onFinish: () => {
                isSubmitting.value = false;
                isCancelRejectModalOpen.value = false;
                selectedWorker.value = null;
            },
        }
    );
};
</script>

<template>
    <Head title="Verifikasi Tenaga Kesehatan — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-900 text-xs font-bold border border-amber-200">
                        <span class="material-symbols-outlined text-sm">verified_user</span>
                        <span>Tata Kelola Akun Nakes</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Verifikasi Bidan & Kader
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Tinjau keabsahan nomor STR Bidan dan SK Penugasan Desa Kader Posyandu untuk mengaktifkan akses portal.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-4 py-2 rounded-2xl bg-amber-100 text-amber-900 font-bold text-xs">
                        Antrean: {{ pendingCount }} Pending
                    </span>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="bg-white p-4 rounded-2xl border border-[#E3E2E5] shadow-xs flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                    <input
                        v-model="search"
                        @keyup.enter="applyFilter"
                        type="text"
                        placeholder="Cari nama nakes atau nomor HP..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select
                        v-model="statusFilter"
                        @change="applyFilter"
                        class="py-2.5 px-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="semua">Semua Status</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="rejected">Ditolak</option>
                    </select>

                    <select
                        v-model="roleFilter"
                        @change="applyFilter"
                        class="py-2.5 px-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-medium focus:bg-white focus:outline-none"
                    >
                        <option value="semua">Semua Peran</option>
                        <option value="bidan">Bidan</option>
                        <option value="kader">Kader</option>
                    </select>

                    <button
                        type="button"
                        @click="applyFilter"
                        class="px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <!-- Workers Table -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div v-if="workers.length === 0" class="py-12 text-center text-xs text-[#73777F]">
                    <span class="material-symbols-outlined text-4xl text-neutral-400 block mb-2">person_search</span>
                    Tidak ada data tenaga kesehatan yang sesuai dengan filter.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Nama & Nomor HP</th>
                                <th class="py-3.5 px-4">Peran</th>
                                <th class="py-3.5 px-4">Nomor STR / SK Desa</th>
                                <th class="py-3.5 px-4">Wilayah</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr
                                v-for="worker in workers"
                                :key="worker.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#123356]">{{ worker.full_name }}</div>
                                    <div class="text-[11px] text-[#73777F] font-mono">{{ worker.phone_number }}</div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-md bg-blue-100 text-blue-900 font-bold uppercase text-[10px]">
                                        {{ worker.role }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-mono font-medium text-[#26292E]">
                                    {{ worker.str_number || worker.appointment_letter_ref || '-' }}
                                </td>
                                <td class="py-4 px-4 text-[#73777F] font-mono text-[11px]">
                                    {{ worker.region_code }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        v-if="worker.status === 'verified'"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Terverifikasi
                                    </span>
                                    <span
                                        v-else-if="worker.status === 'pending'"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[11px] animate-pulse"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Menunggu
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-[11px]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Jika Pending: Tombol Setujui & Tolak (Memanggil ModalBox) -->
                                        <template v-if="worker.status === 'pending'">
                                            <button
                                                type="button"
                                                @click="openApproveModal(worker)"
                                                class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all shadow-xs cursor-pointer active:scale-95"
                                            >
                                                Setujui
                                            </button>
                                            <button
                                                type="button"
                                                @click="openRejectModal(worker)"
                                                class="px-3 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition-all shadow-xs cursor-pointer active:scale-95"
                                            >
                                                Tolak
                                            </button>
                                        </template>

                                        <!-- Jika Ditolak & masih dalam 24 jam: Batalkan Penolakan -->
                                        <template v-else-if="worker.can_cancel_reject">
                                            <button
                                                type="button"
                                                @click="openCancelRejectModal(worker)"
                                                class="px-3 py-1.5 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition-all shadow-xs cursor-pointer active:scale-95"
                                            >
                                                Batalkan Penolakan
                                            </button>
                                        </template>

                                        <span v-else class="text-[#8A8D96] text-[11px] italic">
                                            Selesai Ditinjau
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 1. ModalBox Persetujuan (Approve) -->
        <ModalBox
            :show="isApproveModalOpen"
            type="success"
            title="Setujui Pendaftaran Tenaga Kesehatan"
            :message="`Apakah Anda yakin ingin menyetujui pendaftaran ${selectedWorker?.full_name} sebagai ${selectedWorker?.role?.toUpperCase()}? Akun akan langsung aktif dan dapat mengakses portal monitoring.`"
            confirm-text="Ya, Setujui Akun"
            :loading="isSubmitting"
            @close="isApproveModalOpen = false"
            @cancel="isApproveModalOpen = false"
            @confirm="handleConfirmApprove"
        />

        <!-- 2. ModalBox Penolakan (Reject) -->
        <ModalBox
            :show="isRejectModalOpen"
            type="danger"
            title="Tolak Pendaftaran Tenaga Kesehatan"
            :message="`Masukkan alasan penolakan untuk ${selectedWorker?.full_name} agar nakes mengetahui kelengkapan berkas yang perlu diperbaiki.`"
            confirm-text="Kirim Penolakan"
            :confirm-disabled="!rejectNote.trim()"
            :loading="isSubmitting"
            @close="isRejectModalOpen = false"
            @cancel="isRejectModalOpen = false"
            @confirm="handleConfirmReject"
        >
            <div class="space-y-1.5 mt-2">
                <label class="block text-xs font-extrabold text-[#26292E]">Alasan Penolakan (Wajib)</label>
                <textarea
                    v-model="rejectNote"
                    rows="3"
                    placeholder="Contoh: Nomor STR tidak terdaftar di KTKI atau masa berlaku telah habis."
                    class="w-full p-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-rose-500 focus:outline-none"
                    autofocus
                ></textarea>
            </div>
        </ModalBox>

        <!-- 3. ModalBox Pembatalan Penolakan (Cancel Rejection) -->
        <ModalBox
            :show="isCancelRejectModalOpen"
            type="warning"
            title="Batalkan Penolakan Pendaftaran"
            :message="`Apakah Anda ingin membatalkan status penolakan untuk ${selectedWorker?.full_name} dan mengembalikannya ke status Menunggu Verifikasi (Pending)?`"
            confirm-text="Ya, Batalkan Penolakan"
            :loading="isSubmitting"
            @close="isCancelRejectModalOpen = false"
            @cancel="isCancelRejectModalOpen = false"
            @confirm="handleConfirmCancelReject"
        />
    </AdminLayout>
</template>
