<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    from: {
        type: String,
        default: '',
    },
    to: {
        type: String,
        default: '',
    },
    summary: {
        type: Object,
        default: () => ({ total_alerts: 0, responded_count: 0, unresponded_count: 0, average_seconds: null, median_seconds: null }),
    },
    distribution: {
        type: Object,
        default: () => ({ under_5min: 0, between_5_10min: 0, between_10_30min: 0, over_30min: 0 }),
    },
    riskTrend: {
        type: Object,
        default: () => ({ total: 0, rendah: 0, sedang: 0, tinggi: 0 }),
    },
    maternalOutcome: {
        type: Object,
        default: () => ({ nifas_active: 0, case_closed_safe: 0 }),
    },
    alertLogs: {
        type: Array,
        default: () => [],
    },
});

const fromDate = ref(props.from);
const toDate = ref(props.to);

const applyDateRange = () => {
    router.get(
        route('admin.reporting.index'),
        {
            from: fromDate.value,
            to: toDate.value,
        },
        { preserveState: true, replace: true }
    );
};

const setPreset = (days) => {
    const end = new Date();
    const start = new Date();
    start.setDate(end.getDate() - days);

    fromDate.value = start.toISOString().split('T')[0];
    toDate.value = end.toISOString().split('T')[0];
    applyDateRange();
};

const exportCsvUrl = () => {
    return route('admin.reporting.export', {
        from: fromDate.value,
        to: toDate.value,
    });
};

const formatSeconds = (sec) => {
    if (sec === null || sec === undefined) return '-';
    if (sec < 60) return `${sec} detik`;
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}m ${s}d`;
};
</script>

<template>
    <Head title="Laporan & Metrik Analitik — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">analytics</span>
                        <span>Evaluasi Kinerja Pelayanan Klinis</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Laporan, Metrik & Ekspor Data
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Analisis data gawat darurat maternal, kecepatan tanggap nakes (KPI Kemenkes), tren risiko kehamilan, dan ekspor pelaporan resmi.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        :href="exportCsvUrl()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700 text-white text-xs font-bold hover:bg-emerald-800 transition-all shadow-xs active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base">download</span>
                        <span>Ekspor Laporan CSV</span>
                    </a>
                </div>
            </div>

            <!-- Filter Rentang Tanggal & Preset -->
            <div class="bg-white p-4 rounded-2xl border border-[#E3E2E5] shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <span class="text-xs font-extrabold text-[#26292E] mr-1">Preset:</span>
                    <button
                        type="button"
                        @click="setPreset(7)"
                        class="px-3 py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-xs font-bold text-[#43474E] transition-all cursor-pointer"
                    >
                        7 Hari Terakhir
                    </button>
                    <button
                        type="button"
                        @click="setPreset(30)"
                        class="px-3 py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-xs font-bold text-[#43474E] transition-all cursor-pointer"
                    >
                        30 Hari Terakhir
                    </button>
                    <button
                        type="button"
                        @click="setPreset(90)"
                        class="px-3 py-1.5 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-xs font-bold text-[#43474E] transition-all cursor-pointer"
                    >
                        3 Bulan Terakhir
                    </button>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <input
                        type="date"
                        v-model="fromDate"
                        class="p-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:outline-none"
                    />
                    <span class="text-xs text-[#73777F]">s/d</span>
                    <input
                        type="date"
                        v-model="toDate"
                        class="p-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs font-mono focus:bg-white focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="applyDateRange"
                        class="px-4 py-2 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all cursor-pointer"
                    >
                        Terapkan
                    </button>
                </div>
            </div>

            <!-- Metrik KPI Kecepatan Respons Nakes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Rata-rata Waktu Tanggap</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-blue-900">{{ formatSeconds(summary.average_seconds) }}</span>
                        <span class="text-xs font-bold text-emerald-700">&le; 5 Menit (Kemenkes)</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Median Kecepatan</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ formatSeconds(summary.median_seconds) }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Titik Tengah</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Kasus Direspons</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-emerald-700">{{ summary.responded_count }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">dari {{ summary.total_alerts }} Alert</span>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                    <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Nifas Selesai Aman</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-extrabold text-purple-900">{{ maternalOutcome.case_closed_safe }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Ibu (42 Hari)</span>
                    </div>
                </div>
            </div>

            <!-- Analisis Distribusi Kecepatan & Tren Risiko Kehamilan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Distribusi Waktu Tanggap Nakes -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-5">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-blue-50 text-blue-700">
                            <span class="material-symbols-outlined text-xl">timer</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Distribusi Kecepatan Respons Darurat</h2>
                            <p class="text-xs text-[#73777F]">Benchmark standar emas kegawatdaruratan maternal Kemenkes</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-extrabold text-emerald-950">&lt; 5 Menit (Standar Emas Kemenkes)</span>
                            </div>
                            <span class="text-sm font-black text-emerald-800">{{ distribution.under_5min }} Kasus</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-amber-50 border border-amber-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                <span class="text-xs font-extrabold text-amber-950">5 - 10 Menit (Perlu Evaluasi)</span>
                            </div>
                            <span class="text-sm font-black text-amber-800">{{ distribution.between_5_10min }} Kasus</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-orange-50 border border-orange-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                <span class="text-xs font-extrabold text-orange-950">10 - 30 Menit (Lambat)</span>
                            </div>
                            <span class="text-sm font-black text-orange-800">{{ distribution.between_10_30min }} Kasus</span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-2xl bg-rose-50 border border-rose-200">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                <span class="text-xs font-extrabold text-rose-950">&gt; 30 Menit (Kritis / Terlambat)</span>
                            </div>
                            <span class="text-sm font-black text-rose-800">{{ distribution.over_30min }} Kasus</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Tren Penilaian Risiko Kehamilan -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-5">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-50 text-purple-700">
                            <span class="material-symbols-outlined text-xl">vital_signs</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Tren Risiko Klinis Kehamilan</h2>
                            <p class="text-xs text-[#73777F]">Hasil skrining mandiri ibu hamil pada periode yang dipilih</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <div class="p-4 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-[#73777F] block">Total Sesi Skrining</span>
                                <span class="text-xl font-extrabold text-[#123356]">{{ riskTrend.total }} Skrining Selesai</span>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-900 font-bold text-xs">
                                100% Tercatat
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-center space-y-1">
                                <span class="text-[11px] font-bold text-emerald-800 block">Risiko Rendah</span>
                                <span class="text-2xl font-black text-emerald-700">{{ riskTrend.rendah }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-center space-y-1">
                                <span class="text-[11px] font-bold text-amber-800 block">Risiko Sedang</span>
                                <span class="text-2xl font-black text-amber-700">{{ riskTrend.sedang }}</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-center space-y-1">
                                <span class="text-[11px] font-bold text-rose-800 block">Risiko Tinggi</span>
                                <span class="text-2xl font-black text-rose-700">{{ riskTrend.tinggi }}</span>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-blue-50/60 border border-blue-200 text-xs text-[#123356] flex items-center justify-between">
                            <span>Ibu Masa Nifas Aktif dalam Pemantauan:</span>
                            <span class="font-bold font-mono">{{ maternalOutcome.nifas_active }} Ibu</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat Aktivasi Darurat dalam Periode -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-rose-50 text-rose-700">
                            <span class="material-symbols-outlined text-xl">emergency</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Log Kasus Kegawatdaruratan Maternal</h2>
                            <p class="text-xs text-[#73777F]">Catatan waktu aktivasi, respons tenaga medis, dan status tindakan</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Pasien & Wilayah</th>
                                <th class="py-3.5 px-4">Pemicu Alert</th>
                                <th class="py-3.5 px-4">Waktu Trigger</th>
                                <th class="py-3.5 px-4">Kecepatan Respons</th>
                                <th class="py-3.5 px-4">Nakes Penanggap</th>
                                <th class="py-3.5 px-6 text-right">Status Kasus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="alertLogs.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-[#73777F]">
                                    Tidak ada kasus gawat darurat yang tercatat pada rentang waktu ini.
                                </td>
                            </tr>

                            <tr
                                v-for="alert in alertLogs"
                                :key="alert.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#123356]">{{ alert.mother_name }}</div>
                                    <div class="text-[11px] text-[#73777F] font-mono">Wilayah: {{ alert.region_code }}</div>
                                </td>
                                <td class="py-4 px-4 font-semibold text-[#26292E]">
                                    <span class="px-2 py-0.5 rounded-md bg-neutral-100 text-neutral-800 text-[11px] font-bold">
                                        {{ alert.trigger_type }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-mono text-[#73777F]">
                                    {{ alert.triggered_at }}
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-md font-extrabold text-[11px] font-mono',
                                            alert.response_seconds !== null && alert.response_seconds < 300
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : (alert.response_seconds !== null ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-600')
                                        ]"
                                    >
                                        {{ alert.response_formatted }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-medium text-[#123356]">
                                    {{ alert.first_responder }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase',
                                            alert.status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800 animate-pulse'
                                        ]"
                                    >
                                        {{ alert.status === 'resolved' ? 'Terselesaikan' : 'Dalam Penanganan' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
