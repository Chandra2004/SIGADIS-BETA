<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    from: { type: String, required: true },
    to: { type: String, required: true },
    summary: { type: Object, required: true },
    distribution: { type: Object, required: true },
});

const fromDate = ref(props.from);
const toDate = ref(props.to);

function applyRange() {
    router.get(route('admin.reporting.index'), { from: fromDate.value, to: toDate.value }, { preserveState: true });
}

function formatDuration(seconds) {
    if (seconds === null || seconds === undefined) return '-';
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return minutes > 0 ? `${minutes} menit ${secs} detik` : `${secs} detik`;
}

const distributionRows = [
    { key: 'under_5min', label: '< 5 menit', class: 'bg-risk-low-bg text-risk-low' },
    { key: 'between_5_10min', label: '5 - 10 menit', class: 'bg-risk-medium-bg text-risk-medium' },
    { key: 'between_10_30min', label: '10 - 30 menit', class: 'bg-risk-medium-bg text-risk-medium' },
    { key: 'over_30min', label: '> 30 menit', class: 'bg-risk-high-bg text-risk-high' },
];
</script>

<template>
    <Head title="Laporan & Metrik" />

    <div class="min-h-screen bg-neutral-100">
        <header class="flex items-center justify-between border-b border-neutral-200 bg-white px-6 py-4">
            <h1 class="flex items-center gap-2 text-lg font-bold text-brand-navy-900">
                <Icon name="chartBar" size="h-5 w-5" /> Laporan &amp; Metrik
            </h1>
            <a :href="route('admin.verifikasi.index')" class="text-sm text-brand-navy-700 underline">Kembali ke Dashboard</a>
        </header>

        <main class="mx-auto max-w-3xl space-y-6 px-6 py-8">
            <p class="text-sm text-neutral-600">
                Waktu respons dihitung dari alert darurat dibuat sampai pertama kali dibuka/direspon oleh
                bidan/kader (Flows.md §27.1) — bukan sampai penanganan selesai.
            </p>

            <section class="flex flex-wrap items-end gap-3 rounded-xl border border-neutral-200 bg-white p-4">
                <div>
                    <label class="mb-1 block text-xs text-neutral-500 uppercase">Dari Tanggal</label>
                    <input v-model="fromDate" type="date" class="input input-bordered input-sm" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-neutral-500 uppercase">Sampai Tanggal</label>
                    <input v-model="toDate" type="date" class="input input-bordered input-sm" />
                </div>
                <button type="button" class="btn btn-sm cursor-pointer border-none bg-brand-navy-900 text-white" @click="applyRange">
                    Terapkan
                </button>
                <a
                    :href="route('admin.reporting.export', { from: fromDate, to: toDate })"
                    class="btn btn-sm btn-outline ml-auto cursor-pointer gap-1 border-neutral-300 text-neutral-700"
                >
                    <Icon name="download" size="h-4 w-4" /> Unduh CSV
                </a>
            </section>

            <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl border border-neutral-200 bg-white p-4 text-center">
                    <p class="text-2xl font-bold text-neutral-900">{{ summary.total_alerts }}</p>
                    <p class="text-xs text-neutral-500">Total Alert</p>
                </div>
                <div class="rounded-xl border border-risk-low bg-risk-low-bg p-4 text-center">
                    <p class="text-2xl font-bold text-risk-low">{{ summary.responded_count }}</p>
                    <p class="text-xs text-risk-low">Direspon</p>
                </div>
                <div class="rounded-xl border border-risk-high bg-risk-high-bg p-4 text-center">
                    <p class="text-2xl font-bold text-risk-high">{{ summary.unresponded_count }}</p>
                    <p class="text-xs text-risk-high">Belum Direspon</p>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-neutral-200 bg-white p-4">
                    <p class="text-xs text-neutral-500 uppercase">Rata-rata Waktu Respons</p>
                    <p class="text-xl font-bold text-brand-navy-900">{{ formatDuration(summary.average_seconds) }}</p>
                </div>
                <div class="rounded-xl border border-neutral-200 bg-white p-4">
                    <p class="text-xs text-neutral-500 uppercase">Median Waktu Respons</p>
                    <p class="text-xl font-bold text-brand-navy-900">{{ formatDuration(summary.median_seconds) }}</p>
                </div>
            </section>

            <section class="rounded-xl border border-neutral-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold text-neutral-700 uppercase">Distribusi Waktu Respons</h2>
                <div v-if="summary.responded_count === 0" class="text-sm text-neutral-500">
                    Belum ada alert yang direspon di rentang tanggal ini.
                </div>
                <div v-else class="space-y-2">
                    <div v-for="row in distributionRows" :key="row.key" class="flex items-center justify-between">
                        <span class="text-sm text-neutral-700">{{ row.label }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="row.class">
                            {{ distribution[row.key] }} alert
                        </span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
