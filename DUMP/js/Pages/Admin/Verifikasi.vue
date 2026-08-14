<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';

const props = defineProps({
    filters: { type: Object, required: true },
    pendingCount: { type: Number, required: true },
    workers: { type: Array, required: true },
    verifiedKader: { type: Array, required: true },
    areaAssignments: { type: Array, required: true },
    coverage: { type: Array, required: true },
});

const page = usePage();

const statusFilter = ref(props.filters.status);
const roleFilter = ref(props.filters.role);
const regionFilter = ref(props.filters.region);
const searchFilter = ref(props.filters.search);

function applyFilters() {
    router.get(route('admin.verifikasi.index'), {
        status: statusFilter.value,
        role: roleFilter.value,
        region: regionFilter.value,
        search: searchFilter.value,
    }, { preserveState: true, preserveScroll: true });
}

const rejectingId = ref(null);
const rejectNote = ref('');

function verify(worker) {
    router.post(route('admin.verifikasi.verify', worker.id));
}

function startReject(worker) {
    rejectingId.value = worker.id;
    rejectNote.value = '';
}

function confirmReject(worker) {
    router.post(route('admin.verifikasi.reject', worker.id), { note: rejectNote.value }, {
        onSuccess: () => { rejectingId.value = null; },
    });
}

function cancelRejection(worker) {
    router.post(route('admin.verifikasi.cancel-reject', worker.id));
}

const newAssignment = ref({ kader_id: '', region_code: '', kader_priority: 'primary' });

function assignKader() {
    router.post(route('admin.area-assignments.store'), newAssignment.value, {
        onSuccess: () => { newAssignment.value = { kader_id: '', region_code: '', kader_priority: 'primary' }; },
    });
}

function removeAssignment(assignment) {
    router.delete(route('admin.area-assignments.destroy', assignment.id));
}

const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('auth.admin.logout'));
}
</script>

<template>
    <Head title="Dashboard Admin" />

    <div class="min-h-screen bg-neutral-100">
        <header class="flex items-center justify-between border-b border-neutral-200 bg-white px-6 py-4">
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-brand-navy-900">Dashboard Admin</h1>
                <span v-if="pendingCount > 0" class="rounded-full bg-risk-high px-2 py-0.5 text-xs font-semibold text-white">
                    {{ pendingCount }} menunggu
                </span>
            </div>
            <div class="flex items-center gap-3">
                <a :href="route('admin.reporting.index')" class="text-sm text-brand-navy-700 underline">Laporan &amp; Metrik</a>
                <a :href="route('admin.ganti-nomor.index')" class="text-sm text-brand-navy-700 underline">Ganti Nomor HP</a>
                <button type="button" class="btn btn-ghost btn-sm" @click="showLogoutConfirm = true">Keluar</button>
            </div>
        </header>

        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Notifikasi darurat baru tidak akan muncul di layar ini sampai Anda masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />

        <main class="mx-auto max-w-4xl space-y-8 px-6 py-8">
            <p v-if="page.props.flash?.success" class="rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-neutral-700 uppercase">Akun Bidan/Kader</h2>

                <div class="flex flex-wrap gap-2 rounded-lg border border-neutral-200 bg-white p-3">
                    <select v-model="statusFilter" class="select select-sm select-bordered" @change="applyFilters">
                        <option value="semua">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select v-model="roleFilter" class="select select-sm select-bordered" @change="applyFilters">
                        <option value="semua">Semua Role</option>
                        <option value="bidan">Bidan</option>
                        <option value="kader">Kader</option>
                    </select>
                    <input
                        v-model="regionFilter"
                        type="text"
                        placeholder="Kode wilayah"
                        class="input input-sm input-bordered"
                        @keyup.enter="applyFilters"
                    />
                    <input
                        v-model="searchFilter"
                        type="text"
                        placeholder="Cari nama/nomor HP"
                        class="input input-sm input-bordered flex-1"
                        @keyup.enter="applyFilters"
                    />
                    <button type="button" class="btn btn-sm border-none bg-brand-navy-900 text-white" @click="applyFilters">
                        Cari
                    </button>
                </div>

                <p v-if="workers.length === 0" class="text-sm text-neutral-500">Tidak ada akun untuk filter ini.</p>

                <div v-for="worker in workers" :key="worker.id" class="rounded-lg border border-neutral-200 bg-white p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="font-medium text-neutral-900">{{ worker.full_name }}</p>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-neutral-500 capitalize">{{ worker.role }}</span>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                                :class="{
                                    'bg-risk-medium-bg text-risk-medium': worker.status === 'pending',
                                    'bg-risk-low-bg text-risk-low': worker.status === 'verified',
                                    'bg-risk-high-bg text-risk-high': worker.status === 'rejected',
                                }"
                            >
                                {{ worker.status }}
                            </span>
                        </div>
                    </div>
                    <p class="mb-1 text-sm text-neutral-600">{{ worker.phone_number }} &middot; wilayah {{ worker.region_code }}</p>
                    <p class="mb-3 text-sm text-neutral-600">
                        {{ worker.role === 'bidan' ? `STR: ${worker.str_number}` : `Surat penunjukan: ${worker.appointment_letter_ref}` }}
                    </p>
                    <p v-if="worker.admin_note" class="mb-3 rounded bg-neutral-50 p-2 text-xs text-neutral-600">
                        Catatan admin: {{ worker.admin_note }}
                    </p>

                    <div v-if="worker.status === 'pending'">
                        <div v-if="rejectingId !== worker.id" class="flex gap-3">
                            <button type="button" class="btn btn-sm flex-1 border-none bg-risk-low text-white" @click="verify(worker)">
                                Verifikasi
                            </button>
                            <button type="button" class="btn btn-sm btn-outline flex-1" @click="startReject(worker)">Tolak</button>
                        </div>
                        <div v-else class="space-y-2">
                            <label class="text-xs text-neutral-600">Alasan penolakan (wajib, min. 10 karakter)</label>
                            <textarea v-model="rejectNote" class="textarea textarea-bordered w-full text-sm" rows="2" />
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-ghost btn-sm flex-1" @click="rejectingId = null">Batal</button>
                                <button
                                    type="button"
                                    class="btn btn-sm flex-1 border-none bg-risk-high text-white"
                                    :disabled="rejectNote.trim().length < 10"
                                    @click="confirmReject(worker)"
                                >
                                    Kirim Penolakan
                                </button>
                            </div>
                        </div>
                    </div>
                    <button
                        v-else-if="worker.can_cancel_reject"
                        type="button"
                        class="text-xs font-semibold text-brand-navy-900 underline"
                        @click="cancelRejection(worker)"
                    >
                        Batalkan Penolakan
                    </button>
                </div>
            </section>

            <!-- Flows.md §26.4: cakupan wilayah, membantu identifikasi wilayah tanpa kader (§24). -->
            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-neutral-700 uppercase">Cakupan Wilayah</h2>
                <p v-if="coverage.length === 0" class="text-sm text-neutral-500">Belum ada data wilayah.</p>
                <div v-else class="overflow-x-auto rounded-lg border border-neutral-200 bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-neutral-50 text-xs text-neutral-500 uppercase">
                            <tr>
                                <th class="px-3 py-2">Wilayah</th>
                                <th class="px-3 py-2">Bidan Verified</th>
                                <th class="px-3 py-2">Kader Primary</th>
                                <th class="px-3 py-2">Kader Secondary</th>
                                <th class="px-3 py-2">Ibu Hamil Aktif</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in coverage" :key="row.region_code" class="border-t border-neutral-100">
                                <td class="px-3 py-2 font-medium">{{ row.region_code }}</td>
                                <td class="px-3 py-2">{{ row.bidan_verified }}</td>
                                <td class="px-3 py-2" :class="{ 'font-semibold text-risk-high': row.kader_primary === 0 }">
                                    {{ row.kader_primary }}
                                </td>
                                <td class="px-3 py-2">{{ row.kader_secondary }}</td>
                                <td class="px-3 py-2">{{ row.pregnant_count }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Flows.md §24: sumber data kader_area_assignments dipakai eskalasi alert darurat. -->
            <section class="space-y-3">
                <h2 class="text-sm font-semibold text-neutral-700 uppercase">Penugasan Wilayah Kader</h2>

                <div class="flex flex-wrap items-end gap-2 rounded-lg border border-neutral-200 bg-white p-3">
                    <div>
                        <label class="block text-xs text-neutral-600">Kader</label>
                        <select v-model="newAssignment.kader_id" class="select select-sm select-bordered">
                            <option value="" disabled>Pilih kader</option>
                            <option v-for="k in verifiedKader" :key="k.id" :value="k.id">{{ k.full_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-600">Kode Wilayah</label>
                        <input v-model="newAssignment.region_code" type="text" class="input input-sm input-bordered" />
                    </div>
                    <div>
                        <label class="block text-xs text-neutral-600">Prioritas</label>
                        <select v-model="newAssignment.kader_priority" class="select select-sm select-bordered">
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="btn btn-sm border-none bg-brand-navy-900 text-white"
                        :disabled="!newAssignment.kader_id || !newAssignment.region_code"
                        @click="assignKader"
                    >
                        Tugaskan
                    </button>
                </div>

                <p v-if="areaAssignments.length === 0" class="text-sm text-neutral-500">Belum ada penugasan wilayah kader.</p>
                <div
                    v-for="assignment in areaAssignments"
                    :key="assignment.id"
                    class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 text-sm"
                >
                    <span>{{ assignment.kader.full_name }} &middot; {{ assignment.region_code }} &middot; {{ assignment.kader_priority }}</span>
                    <button type="button" class="text-xs font-semibold text-risk-high underline" @click="removeAssignment(assignment)">
                        Hapus
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>
