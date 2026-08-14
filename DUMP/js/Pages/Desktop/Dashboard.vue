<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';
import AlertBanner from '@/Components/Desktop/AlertBanner.vue';
import AppShell from '@/Components/Desktop/AppShell.vue';
import Icon from '@/Components/Shared/Icon.vue';
import NotificationBell from '@/Components/Shared/NotificationBell.vue';

const props = defineProps({
    worker: { type: Object, required: true },
    patients: { type: Array, required: true },
    pendingAlerts: { type: Array, required: true },
    summary: { type: Object, required: true },
    filter: { type: String, default: 'semua' },
});

const page = usePage();
const showDeactivateConfirm = ref(false);
const unavailableFrom = ref('');
const unavailableUntil = ref('');

const riskBadge = {
    tinggi: 'bg-risk-high-bg text-risk-high',
    sedang: 'bg-risk-medium-bg text-risk-medium',
    rendah: 'bg-risk-low-bg text-risk-low',
};

const filters = [
    { key: 'semua', label: 'Semua' },
    { key: 'tinggi', label: 'Risiko Tinggi' },
    { key: 'sedang', label: 'Risiko Sedang' },
    { key: 'rendah', label: 'Risiko Rendah' },
    { key: 'nifas', label: 'Nifas' },
];

function applyFilter(key) {
    router.get(route('bidan.dashboard'), { filter: key }, { preserveState: true, preserveScroll: true });
}

function openAlert(alert) {
    router.visit(route('bidan.alerts.show', alert.id));
}

function logout() {
    router.post(route('auth.staff.logout'));
}

function deactivate() {
    router.post(route('bidan.availability.deactivate'), {
        unavailable_from: unavailableFrom.value || null,
        unavailable_until: unavailableUntil.value || null,
    });
    showDeactivateConfirm.value = false;
    unavailableFrom.value = '';
    unavailableUntil.value = '';
}

function reactivate() {
    router.post(route('bidan.availability.reactivate'));
}

// Architecture.md §5.2: kanal privat per worker, alert baru langsung
// muncul tanpa reload manual selama dashboard terbuka.
onMounted(() => {
    window.Echo.private(`staff.${props.worker.id}`).listen('.emergency-alert', () => {
        router.reload({ only: ['patients', 'pendingAlerts'] });
    });
});

onUnmounted(() => {
    window.Echo.leave(`staff.${props.worker.id}`);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppShell :region-code="worker.region_code" @logout="logout">
        <template #sidebar-actions>
            <button
                v-if="worker.is_available"
                type="button"
                class="btn btn-outline btn-sm w-full"
                @click="showDeactivateConfirm = true"
            >
                Tandai Tidak Bertugas
            </button>
            <button v-else type="button" class="btn btn-sm w-full border-none bg-brand-navy-900 text-white" @click="reactivate">
                Aktifkan Kembali
            </button>
        </template>

        <header class="flex items-center justify-between border-b border-neutral-200 bg-white px-6 py-4">
            <div>
                <h1 class="text-lg font-bold text-brand-navy-900">Halo, {{ worker.full_name }}</h1>
                <span v-if="!worker.is_available" class="mt-1 inline-block rounded-full bg-risk-medium-bg px-2 py-0.5 text-xs font-semibold text-risk-medium">
                    Sedang Tidak Bertugas
                </span>
            </div>
            <div class="flex items-center gap-2">
                <NotificationBell
                    index-route="bidan.notifikasi.index"
                    mark-read-route="bidan.notifikasi.mark-read"
                    mark-all-read-route="bidan.notifikasi.mark-all-read"
                />
            </div>
        </header>

        <main class="mx-auto max-w-3xl space-y-8 px-6 py-8">
            <p v-if="page.props.flash?.success" class="rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <section class="rounded-lg border border-neutral-200 bg-white p-4">
                <div v-if="worker.is_available" class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-900">Status Ketersediaan</p>
                        <p class="text-sm text-neutral-500">Sedang bertugas, siap menerima pasien baru.</p>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm border-neutral-300 text-neutral-700" @click="showDeactivateConfirm = true">
                        Tandai Tidak Bertugas
                    </button>
                </div>
                <div v-else class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-900">Sedang Tidak Bertugas</p>
                        <p class="text-sm text-neutral-500">
                            {{ worker.unavailable_until ? `Sampai ${new Date(worker.unavailable_until).toLocaleDateString('id-ID')}` : 'Sampai diaktifkan manual' }}
                            &middot; Anda tidak menerima penugasan pasien baru.
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm border-none bg-brand-navy-900 text-white" @click="reactivate">
                        Aktifkan Kembali
                    </button>
                </div>
            </section>

            <div v-if="showDeactivateConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
                <div class="w-full max-w-md rounded-xl bg-white shadow-lg">
                    <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4">
                        <h2 class="text-lg font-bold text-brand-navy-900">Atur Status Ketersediaan</h2>
                        <button type="button" aria-label="Tutup" class="cursor-pointer text-neutral-400" @click="showDeactivateConfirm = false">
                            &#10005;
                        </button>
                    </div>
                    <div class="space-y-4 px-6 py-4">
                        <p class="rounded-lg bg-neutral-50 p-3 text-sm text-neutral-700">
                            Selama nonaktif, peringatan darurat pasien Anda tetap terkirim tapi eskalasi ke kader akan
                            lebih cepat. Anda tidak akan menerima penugasan pasien baru.
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm text-neutral-700">Mulai tanggal</label>
                                <input v-model="unavailableFrom" type="date" class="input input-bordered w-full" :min="new Date().toISOString().slice(0, 10)" />
                            </div>
                            <div>
                                <label class="text-sm text-neutral-700">Sampai tanggal (opsional)</label>
                                <input v-model="unavailableUntil" type="date" class="input input-bordered w-full" :min="unavailableFrom || new Date().toISOString().slice(0, 10)" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-neutral-200 px-6 py-4">
                        <button type="button" class="btn btn-outline" @click="showDeactivateConfirm = false">Batal</button>
                        <button type="button" class="btn border-none bg-brand-navy-900 text-white" @click="deactivate">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>

            <section v-if="pendingAlerts.length" class="space-y-3">
                <h2 class="text-sm font-semibold text-neutral-700 uppercase">Peringatan Darurat</h2>
                <AlertBanner v-for="alert in pendingAlerts" :key="alert.id" :alert="alert" @open="openAlert" />
            </section>

            <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-lg border border-neutral-200 bg-white p-4 text-center">
                    <p class="text-2xl font-bold text-neutral-900">{{ summary.total }}</p>
                    <p class="text-xs text-neutral-500">Total Pasien</p>
                </div>
                <div class="rounded-lg border border-risk-high bg-risk-high-bg p-4 text-center">
                    <p class="text-2xl font-bold text-risk-high">{{ summary.risiko_tinggi }}</p>
                    <p class="text-xs text-risk-high">Risiko Tinggi</p>
                </div>
                <div class="rounded-lg border border-risk-medium bg-risk-medium-bg p-4 text-center">
                    <p class="text-2xl font-bold text-risk-medium">{{ summary.risiko_sedang }}</p>
                    <p class="text-xs text-risk-medium">Risiko Sedang</p>
                </div>
                <div class="rounded-lg border border-neutral-200 bg-white p-4 text-center">
                    <p class="text-2xl font-bold text-neutral-900">{{ summary.nifas }}</p>
                    <p class="text-xs text-neutral-500">Nifas</p>
                </div>
            </section>

            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-neutral-700 uppercase">Pasien Dampingan</h2>
                </div>

                <div class="flex gap-2 overflow-x-auto pb-1">
                    <button
                        v-for="f in filters"
                        :key="f.key"
                        type="button"
                        class="btn btn-sm shrink-0"
                        :class="filter === f.key ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline border-neutral-300 text-neutral-700'"
                        @click="applyFilter(f.key)"
                    >
                        {{ f.label }}
                    </button>
                </div>

                <p v-if="patients.length === 0" class="text-sm text-neutral-500">Tidak ada pasien untuk filter ini.</p>
                <div v-else class="overflow-x-auto rounded-lg border border-neutral-200 bg-white">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-neutral-200 bg-neutral-50 text-xs text-neutral-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Nama Pasien</th>
                                <th class="px-4 py-3">Usia Kandungan</th>
                                <th class="px-4 py-3">Status Risiko</th>
                                <th class="px-4 py-3">Skrining Terakhir</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="patient in patients" :key="patient.id" class="border-b border-neutral-100 last:border-0 hover:bg-neutral-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-neutral-900">{{ patient.mother_name }}</p>
                                    <p v-if="patient.nifas_overdue" class="text-xs font-semibold text-risk-medium">Tinjau untuk penutupan kasus</p>
                                </td>
                                <td class="px-4 py-3 text-neutral-700">{{ patient.gestational_age_weeks }} minggu</td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="patient.last_risk_level"
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                                        :class="riskBadge[patient.last_risk_level] ?? 'bg-neutral-100 text-neutral-700'"
                                    >
                                        {{ patient.last_risk_level }}
                                    </span>
                                    <span v-else class="text-xs text-neutral-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-neutral-700">
                                    {{ patient.last_screening_at ? new Date(patient.last_screening_at).toLocaleDateString('id-ID') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a
                                        :href="route('bidan.patients.show', patient.id)"
                                        aria-label="Lihat detail pasien"
                                        class="inline-flex cursor-pointer items-center text-neutral-500 hover:text-brand-navy-900"
                                    >
                                        <Icon name="eye" size="h-5 w-5" />
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </AppShell>
</template>
