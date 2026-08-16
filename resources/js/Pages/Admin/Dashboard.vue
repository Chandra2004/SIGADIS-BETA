<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    risk_distribution: {
        type: Object,
        required: true,
    },
    response_time: {
        type: Object,
        required: true,
    },
    urgent_alerts: {
        type: Array,
        default: () => [],
    },
    pending_workers_list: {
        type: Array,
        default: () => [],
    },
    region_coverage: {
        type: Array,
        default: () => [],
    },
});

const reloadData = () => {
    router.reload({ preserveScroll: true });
};

// Hitung persentase risiko untuk progress bar visual
const riskPercentages = computed(() => {
    const total = props.risk_distribution.total || 1;
    return {
        rendah: Math.round(((props.risk_distribution.rendah || 0) / total) * 100),
        sedang: Math.round(((props.risk_distribution.sedang || 0) / total) * 100),
        tinggi: Math.round(((props.risk_distribution.tinggi || 0) / total) * 100),
    };
});
</script>

<template>
    <Head title="Dashboard Utama — Admin SIGADIS" />

    <AdminLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Banner & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#ABC9F3]/25 text-[#123356] text-xs font-bold">
                        <span class="material-symbols-outlined text-sm">analytics</span>
                        <span>Ringkasan Statistik Maternal</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Dashboard Administrator
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Pemantauan keselamatan ibu hamil, kesiapan tenaga kesehatan, dan respon darurat secara real-time.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="reloadData"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-[#26292E] text-xs font-bold hover:bg-white hover:border-[#123356] transition-all shadow-2xs active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#73777F]">sync</span>
                        <span>Perbarui Data</span>
                    </button>

                    <Link
                        :href="route('admin.verifikasi.index')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs active:scale-95"
                    >
                        <span class="material-symbols-outlined text-base text-[#F3AEC0]">verified_user</span>
                        <span>Verifikasi Nakes</span>
                    </Link>
                </div>
            </div>

            <!-- Urgent Notification Alert (Jika Ada Kasus Darurat atau Antrean Nakes) -->
            <div
                v-if="metrics.active_emergencies > 0"
                class="p-5 rounded-3xl bg-rose-50 border-2 border-rose-300 text-rose-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm animate-pulse"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">emergency</span>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-rose-900">
                            Peringatan Darurat: {{ metrics.active_emergencies }} Kasus Membutuhkan Penanganan!
                        </h2>
                        <p class="text-xs text-rose-800">
                            Terdapat aktivasi tombol SOS atau deteksi risiko tinggi yang sedang dalam proses koordinasi nakes.
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('admin.dashboard')"
                    class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold shadow-xs hover:bg-rose-700 transition-all shrink-0"
                >
                    Lihat Kasus Darurat
                </Link>
            </div>

            <div
                v-else-if="metrics.pending_workers > 0"
                class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs"
            >
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-2xl">pending_actions</span>
                    <div>
                        <span class="text-xs font-bold text-amber-900">
                            {{ metrics.pending_workers }} Tenaga Kesehatan Menunggu Verifikasi Dokumen
                        </span>
                        <p class="text-[11px] text-amber-800">
                            Bidan/Kader baru telah mendaftar dan membutuhkan verifikasi nomor STR atau SK Desa.
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('admin.verifikasi.index')"
                    class="px-3.5 py-1.5 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 transition-all shrink-0"
                >
                    Tinjau Antrean
                </Link>
            </div>

            <!-- 1. KARTU METRIK CEPAT (Fast Metric Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <!-- Metrik 1: Total Ibu Hamil Aktif -->
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Ibu Hamil Aktif</span>
                        <div class="w-10 h-10 rounded-2xl bg-[#FDF3F6] text-[#E0703D] border border-[#F3AEC0]/40 flex items-center justify-center shadow-2xs">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">pregnant_woman</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total_pregnant }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Jiwa</span>
                    </div>
                    <div class="pt-2 border-t border-[#F2F3F5] flex items-center justify-between text-[11px] text-[#73777F]">
                        <span>Hamil: <strong class="text-[#123356]">{{ metrics.pregnant_hamil }}</strong></span>
                        <span>•</span>
                        <span>Nifas: <strong class="text-[#123356]">{{ metrics.pregnant_nifas }}</strong></span>
                    </div>
                </div>

                <!-- Metrik 2: Tenaga Kesehatan Terverifikasi -->
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Nakes Terverifikasi</span>
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#123356] border border-blue-200/60 flex items-center justify-center shadow-2xs">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">medical_services</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-extrabold text-[#123356]">{{ metrics.total_workers }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Petugas</span>
                    </div>
                    <div class="pt-2 border-t border-[#F2F3F5] flex items-center justify-between text-[11px] text-[#73777F]">
                        <span>Bidan: <strong class="text-[#123356]">{{ metrics.verified_bidan }}</strong></span>
                        <span>•</span>
                        <span>Kader: <strong class="text-[#123356]">{{ metrics.verified_kader }}</strong></span>
                    </div>
                </div>

                <!-- Metrik 3: Antrean Verifikasi Pending -->
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Antrean Verifikasi</span>
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center justify-center shadow-2xs">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">pending_actions</span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-3xl font-extrabold text-amber-700">{{ metrics.pending_workers }}</span>
                        <span class="text-xs font-semibold text-[#73777F]">Permohonan</span>
                    </div>
                    <div class="pt-2 border-t border-[#F2F3F5] flex items-center justify-between text-[11px] text-[#73777F]">
                        <span :class="metrics.pending_workers > 0 ? 'text-amber-700 font-bold' : 'text-emerald-700'">
                            {{ metrics.pending_workers > 0 ? 'Perlu Ditinjau Segera' : 'Semua Berkas Bersih' }}
                        </span>
                        <Link :href="route('admin.verifikasi.index')" class="text-[#123356] font-bold hover:underline">
                            Detail &rarr;
                        </Link>
                    </div>
                </div>

                <!-- Metrik 4: Kasus Darurat Aktif -->
                <div class="bg-white p-5 rounded-3xl border border-[#E3E2E5] shadow-xs hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-[#73777F] uppercase tracking-wider">Darurat Aktif</span>
                        <div
                            :class="[
                                'w-10 h-10 rounded-2xl flex items-center justify-center shadow-2xs border',
                                metrics.active_emergencies > 0 ? 'bg-rose-50 text-rose-700 border-rose-200 animate-bounce-subtle' : 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            ]"
                        >
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">
                                {{ metrics.active_emergencies > 0 ? 'e911_emergency' : 'health_and_safety' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-2">
                        <span :class="['text-3xl font-extrabold', metrics.active_emergencies > 0 ? 'text-rose-700' : 'text-emerald-700']">
                            {{ metrics.active_emergencies }}
                        </span>
                        <span class="text-xs font-semibold text-[#73777F]">Kasus</span>
                    </div>
                    <div class="pt-2 border-t border-[#F2F3F5] flex items-center justify-between text-[11px] text-[#73777F]">
                        <span :class="metrics.active_emergencies > 0 ? 'text-rose-700 font-bold' : 'text-emerald-700 font-medium'">
                            {{ metrics.active_emergencies > 0 ? 'Penanganan Sedang Berjalan' : 'Kondisi Wilayah Aman' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. ANALISIS RISIKO & INDIKATOR RESPONS DARURAT -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Card Distribusi Risiko Kehamilan -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-5 lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-[#F2F3F5] pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-xl bg-[#FDF3F6] text-[#123356]">
                                <span class="material-symbols-outlined text-xl">pie_chart</span>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-[#123356]">Distribusi Risiko Maternal Wilayah</h2>
                                <p class="text-xs text-[#73777F]">Berdasarkan hasil skrining mandiri AI & Algoritma Decision Tree</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-[#123356] bg-[#FAF9FC] px-3 py-1 rounded-full border border-[#E3E2E5]">
                            Total: {{ risk_distribution.total }} Skrining
                        </span>
                    </div>

                    <!-- Progress Bar Multi-Segment -->
                    <div class="space-y-2">
                        <div v-if="risk_distribution.total > 0" class="h-4 w-full bg-neutral-100 rounded-full overflow-hidden flex shadow-inner">
                            <div
                                :style="{ width: `${riskPercentages.rendah}%` }"
                                class="bg-[#4C9A6E] h-full transition-all duration-500 relative group"
                                title="Risiko Rendah"
                            ></div>
                            <div
                                :style="{ width: `${riskPercentages.sedang}%` }"
                                class="bg-[#E0A030] h-full transition-all duration-500 relative group"
                                title="Risiko Sedang"
                            ></div>
                            <div
                                :style="{ width: `${riskPercentages.tinggi}%` }"
                                class="bg-[#D64550] h-full transition-all duration-500 relative group"
                                title="Risiko Tinggi"
                            ></div>
                        </div>
                        <div v-else class="h-3 w-full bg-neutral-100 rounded-full"></div>

                        <!-- Keterangan Legend -->
                        <div class="grid grid-cols-3 gap-3 pt-2">
                            <!-- Rendah -->
                            <div class="p-3 rounded-2xl bg-[#E6F4EC]/60 border border-[#4C9A6E]/20 space-y-1">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#2B6645]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#4C9A6E]"></span>
                                    <span>Risiko Rendah</span>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-extrabold text-[#2B6645]">{{ risk_distribution.rendah }}</span>
                                    <span class="text-xs font-bold text-[#4C9A6E]">{{ riskPercentages.rendah }}%</span>
                                </div>
                            </div>

                            <!-- Sedang -->
                            <div class="p-3 rounded-2xl bg-[#FBF0DC]/60 border border-[#E0A030]/20 space-y-1">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#915B06]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#E0A030]"></span>
                                    <span>Risiko Sedang</span>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-extrabold text-[#915B06]">{{ risk_distribution.sedang }}</span>
                                    <span class="text-xs font-bold text-[#E0A030]">{{ riskPercentages.sedang }}%</span>
                                </div>
                            </div>

                            <!-- Tinggi -->
                            <div class="p-3 rounded-2xl bg-[#FBE4E5]/60 border border-[#D64550]/20 space-y-1">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-[#93000A]">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#D64550]"></span>
                                    <span>Risiko Tinggi</span>
                                </div>
                                <div class="flex items-baseline justify-between">
                                    <span class="text-lg font-extrabold text-[#93000A]">{{ risk_distribution.tinggi }}</span>
                                    <span class="text-xs font-bold text-[#D64550]">{{ riskPercentages.tinggi }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Indikator Respons Darurat (KPI) -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center gap-2.5 border-b border-[#F2F3F5] pb-3 mb-4">
                            <div class="p-2 rounded-xl bg-blue-50 text-[#123356]">
                                <span class="material-symbols-outlined text-xl">timer</span>
                            </div>
                            <div>
                                <h2 class="text-base font-extrabold text-[#123356]">Kecepatan Respons Nakes</h2>
                                <p class="text-xs text-[#73777F]">Metrik waktu tanggap alert darurat</p>
                            </div>
                        </div>

                        <div class="text-center py-2">
                            <div class="text-4xl font-extrabold text-[#123356] tracking-tight">
                                {{ response_time.formatted }}
                            </div>
                            <span class="text-xs font-medium text-[#73777F]">Rata-rata Waktu Dilihat & Direspons</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#73777F]">Target Kemenkes:</span>
                            <span class="font-bold text-[#123356]">&lt; 5 Menit (300 d)</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#73777F]">Status Kinerja:</span>
                            <span
                                :class="[
                                    'inline-flex items-center gap-1 font-bold text-xs px-2.5 py-0.5 rounded-full',
                                    response_time.is_on_target ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                                ]"
                            >
                                <span class="material-symbols-outlined text-sm">
                                    {{ response_time.is_on_target ? 'check_circle' : 'warning' }}
                                </span>
                                <span>{{ response_time.is_on_target ? 'Memenuhi Standar' : 'Perlu Peningkatan' }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. MATRIKS CAKUPAN WILAYAH & KESIAPAN NAKES -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-purple-50 text-purple-700">
                            <span class="material-symbols-outlined text-xl">map</span>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#123356]">Matriks Cakupan & Kesiapan Wilayah (Desa/Kelurahan)</h2>
                            <p class="text-xs text-[#73777F]">Deteksi celah pendampingan nakes per wilayah binaan</p>
                        </div>
                    </div>
                    <div class="text-xs text-[#73777F]">
                        Total <strong>{{ region_coverage.length }}</strong> Wilayah Binaan
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Wilayah / Desa</th>
                                <th class="py-3.5 px-4 text-center">Ibu Hamil Aktif</th>
                                <th class="py-3.5 px-4 text-center">Risiko Tinggi</th>
                                <th class="py-3.5 px-4 text-center">Bidan Siaga</th>
                                <th class="py-3.5 px-4 text-center">Kader Wilayah</th>
                                <th class="py-3.5 px-6 text-center">Status Cakupan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="region_coverage.length === 0">
                                <td colspan="6" class="py-8 text-center text-xs text-[#73777F]">
                                    <span class="material-symbols-outlined text-3xl text-neutral-400 block mb-1">location_off</span>
                                    Belum ada data wilayah binaan yang tercatat di sistem database.
                                </td>
                            </tr>
                            <tr
                                v-for="region in region_coverage"
                                :key="region.region_code"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6 font-bold text-[#123356]">
                                    <div>{{ region.village_name }}</div>
                                    <span class="text-[11px] text-[#8A8D96] font-mono font-normal">{{ region.region_code }}</span>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-[#26292E]">
                                    {{ region.total_pregnant }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 rounded-full font-bold text-xs',
                                            region.high_risk > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'
                                        ]"
                                    >
                                        {{ region.high_risk }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center text-[#26292E]">
                                    <span class="font-bold">{{ region.bidan_count }}</span> Bidan
                                </td>
                                <td class="py-4 px-4 text-center text-[#26292E]">
                                    <span class="font-bold">{{ region.kader_count }}</span> Kader
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span
                                        v-if="!region.has_gap"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Cakupan Lengkap
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold animate-pulse"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Perlu Kader Tambahan
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. DUA KOLOM: ANTREAN VERIFIKASI & KASUS DARURAT TERBARU -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Antrean Verifikasi Cepat -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-[#F2F3F5] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600 text-xl">how_to_reg</span>
                            <h2 class="text-base font-extrabold text-[#123356]">Antrean Verifikasi Tenaga Medis</h2>
                        </div>
                        <Link :href="route('admin.verifikasi.index')" class="text-xs font-bold text-[#123356] hover:underline">
                            Lihat Semua &rarr;
                        </Link>
                    </div>

                    <div v-if="pending_workers_list.length === 0" class="py-8 text-center text-xs text-[#73777F]">
                        <span class="material-symbols-outlined text-3xl text-emerald-500 block mb-1">task_alt</span>
                        Tidak ada antrean verifikasi pending saat ini.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="worker in pending_workers_list"
                            :key="worker.id"
                            class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5] flex items-center justify-between gap-3 hover:bg-white hover:border-[#123356] transition-all"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-[#123356]">{{ worker.full_name }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-bold uppercase">
                                        {{ worker.role }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-[#73777F]">
                                    <span>No: <strong>{{ worker.str_or_sk }}</strong></span> • 
                                    <span>HP: {{ worker.phone_number }}</span>
                                </div>
                            </div>
                            <Link
                                :href="route('admin.verifikasi.index')"
                                class="px-3 py-1.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shrink-0"
                            >
                                Periksa
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Log Kasus Darurat Terkini -->
                <div class="bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-[#F2F3F5] pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-rose-600 text-xl">crisis_alert</span>
                            <h2 class="text-base font-extrabold text-[#123356]">Log Aktivasi Darurat Terkini</h2>
                        </div>
                        <span class="text-xs text-[#73777F]">Real-time SOS</span>
                    </div>

                    <div v-if="urgent_alerts.length === 0" class="py-8 text-center text-xs text-[#73777F]">
                        <span class="material-symbols-outlined text-3xl text-emerald-500 block mb-1">verified</span>
                        Tidak ada peringatan darurat aktif. Semua kondisi terpantau aman.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="alert in urgent_alerts"
                            :key="alert.id"
                            class="p-3.5 rounded-2xl bg-rose-50/50 border border-rose-200 flex items-center justify-between gap-3"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-rose-950">{{ alert.mother_name }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-rose-200 text-rose-900 text-[10px] font-bold">
                                        {{ alert.trigger_type }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-rose-800">
                                    <span>Wilayah: {{ alert.region_code }}</span> • 
                                    <span>{{ alert.triggered_at }}</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-rose-700 uppercase">
                                {{ alert.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
