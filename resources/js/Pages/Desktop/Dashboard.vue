<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';
import BidanPatientTabs from '@/Components/BidanPatientTabs.vue';

const props = defineProps({
    worker: {
        type: Object,
        default: () => ({}),
    },
    summary: {
        type: Object,
        default: () => ({ total: 0, risiko_tinggi: 0, risiko_sedang: 0, nifas: 0 }),
    },
    filter: {
        type: String,
        default: 'semua',
    },
    patients: {
        type: Array,
        default: () => [],
    },
    pendingAlerts: {
        type: Array,
        default: () => [],
    },
});

const searchQuery = ref('');

const setFilter = (f) => {
    router.get(
        route('bidan.dashboard'),
        { filter: f },
        { preserveState: true, replace: true }
    );
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
            return { label: 'Belum Skrining', bg: 'bg-neutral-100 text-neutral-700 border-neutral-200', dot: 'bg-neutral-400' };
    }
};

const filteredPatients = () => {
    if (!searchQuery.value.trim()) return props.patients;
    const q = searchQuery.value.toLowerCase();
    return props.patients.filter((p) => p.mother_name?.toLowerCase().includes(q));
};
</script>

<template>
    <Head title="Dashboard Monitoring — SIGADIS Nakes" />

    <BidanLayout>
        <div class="space-y-6">
            <!-- 1. Header Section (Gaya Admin SIGADIS) -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">health_and_safety</span>
                        <span>Wilayah Penugasan: {{ worker.region_code || 'Puskesmas Sungai Raya' }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Dashboard Monitoring Pasien Maternal
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Pantau riwayat skrining berkala ibu hamil binaan, deteksi dini gejala risiko klinis, dan respon cepat kegawatdaruratan.
                    </p>
                </div>
            </div>

            <!-- 2. Live Pulsing Emergency Alert Banner (Jika Ada Alert Terbuka) -->
            <div
                v-if="pendingAlerts && pendingAlerts.length > 0"
                class="p-5 rounded-3xl bg-gradient-to-r from-rose-600 to-red-700 text-white shadow-lg border border-red-500 relative overflow-hidden animate-pulse"
            >
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-2xl bg-white/20 backdrop-blur-xs text-white">
                            <span class="material-symbols-outlined text-3xl">emergency_home</span>
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-white/20 text-xs font-black uppercase tracking-wider mb-1">
                                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                                <span>{{ pendingAlerts.length }} Peringatan Gawat Darurat Aktif!</span>
                            </div>
                            <h2 class="text-lg font-black text-white">
                                Pasien {{ pendingAlerts[0].mother_name }} Membutuhkan Respons Segera!
                            </h2>
                            <p class="text-xs text-white/80">
                                Waktu Pemicu: {{ new Date(pendingAlerts[0].triggered_at).toLocaleTimeString('id-ID') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('bidan.alerts.show', pendingAlerts[0].id)"
                            class="px-5 py-3 rounded-2xl bg-white text-rose-700 text-xs font-black hover:bg-neutral-100 transition-all shadow-md active:scale-95 flex items-center gap-2"
                        >
                            <span class="material-symbols-outlined text-base">near_me</span>
                            <span>Buka Layar Tanggap Darurat</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 3. Kartu Ringkasan Metrik Pasien Binaan -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <button
                    type="button"
                    @click="setFilter('semua')"
                    :class="[
                        'p-5 rounded-3xl border transition-all text-left cursor-pointer shadow-xs',
                        filter === 'semua' || !filter
                            ? 'bg-[#123356] text-white border-[#123356] ring-2 ring-[#123356]/20'
                            : 'bg-white border-[#E3E2E5] hover:border-neutral-400'
                    ]"
                >
                    <span :class="['text-[11px] font-bold uppercase tracking-wider block', (filter === 'semua' || !filter) ? 'text-white/70' : 'text-[#73777F]']">
                        Total Pasien Binaan
                    </span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-extrabold">{{ summary.total }}</span>
                        <span :class="['text-xs font-semibold', (filter === 'semua' || !filter) ? 'text-white/70' : 'text-[#73777F]']">Ibu</span>
                    </div>
                </button>

                <button
                    type="button"
                    @click="setFilter('tinggi')"
                    :class="[
                        'p-5 rounded-3xl border transition-all text-left cursor-pointer shadow-xs',
                        filter === 'tinggi'
                            ? 'bg-rose-700 text-white border-rose-700 ring-2 ring-rose-300'
                            : 'bg-white border-[#E3E2E5] hover:border-rose-300'
                    ]"
                >
                    <span :class="['text-[11px] font-bold uppercase tracking-wider block', filter === 'tinggi' ? 'text-white/80' : 'text-rose-700']">
                        Risiko Tinggi 🚨
                    </span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-extrabold text-rose-600" :class="{ '!text-white': filter === 'tinggi' }">{{ summary.risiko_tinggi }}</span>
                        <span :class="['text-xs font-semibold', filter === 'tinggi' ? 'text-white/70' : 'text-[#73777F]']">Ibu</span>
                    </div>
                </button>

                <button
                    type="button"
                    @click="setFilter('sedang')"
                    :class="[
                        'p-5 rounded-3xl border transition-all text-left cursor-pointer shadow-xs',
                        filter === 'sedang'
                            ? 'bg-amber-600 text-white border-amber-600 ring-2 ring-amber-300'
                            : 'bg-white border-[#E3E2E5] hover:border-amber-300'
                    ]"
                >
                    <span :class="['text-[11px] font-bold uppercase tracking-wider block', filter === 'sedang' ? 'text-white/80' : 'text-amber-800']">
                        Risiko Sedang 🟡
                    </span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-extrabold text-amber-700" :class="{ '!text-white': filter === 'sedang' }">{{ summary.risiko_sedang }}</span>
                        <span :class="['text-xs font-semibold', filter === 'sedang' ? 'text-white/70' : 'text-[#73777F]']">Ibu</span>
                    </div>
                </button>

                <button
                    type="button"
                    @click="setFilter('nifas')"
                    :class="[
                        'p-5 rounded-3xl border transition-all text-left cursor-pointer shadow-xs',
                        filter === 'nifas'
                            ? 'bg-purple-700 text-white border-purple-700 ring-2 ring-purple-300'
                            : 'bg-white border-[#E3E2E5] hover:border-purple-300'
                    ]"
                >
                    <span :class="['text-[11px] font-bold uppercase tracking-wider block', filter === 'nifas' ? 'text-white/80' : 'text-purple-800']">
                        Masa Nifas 👶
                    </span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-3xl font-extrabold text-purple-700" :class="{ '!text-white': filter === 'nifas' }">{{ summary.nifas }}</span>
                        <span :class="['text-xs font-semibold', filter === 'nifas' ? 'text-white/70' : 'text-[#73777F]']">Ibu (42 Hari)</span>
                    </div>
                </button>
            </div>

            <!-- 4. Tabel Direktori Pasien Maternal Binaan -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <!-- Reusable In-Page Navigation Tabs Template & Search Bar -->
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Template Komponen Reusable Tabs -->
                    <BidanPatientTabs
                        :active-filter="filter"
                        :summary="summary"
                    />

                    <!-- Search Input -->
                    <div class="relative w-full md:w-64">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama ibu..."
                            class="w-full pl-10 pr-4 py-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Identitas Ibu Hamil / Nifas</th>
                                <th class="py-3.5 px-4 text-center">Fase / Usia Kehamilan</th>
                                <th class="py-3.5 px-4">Tingkat Risiko Klinis</th>
                                <th class="py-3.5 px-4">Skrining Terakhir</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="filteredPatients().length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    <span class="material-symbols-outlined text-3xl text-neutral-400 block mb-1">person_off</span>
                                    Tidak ada pasien yang sesuai dengan filter.
                                </td>
                            </tr>

                            <tr
                                v-for="patient in filteredPatients()"
                                :key="patient.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-[#123356] text-sm">{{ patient.mother_name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded-md text-[10px] font-bold uppercase',
                                                patient.status === 'hamil' ? 'bg-blue-100 text-blue-900' : (patient.status === 'nifas' ? 'bg-purple-100 text-purple-900' : 'bg-neutral-100 text-neutral-700')
                                            ]"
                                        >
                                            Status: {{ patient.status }}
                                        </span>
                                        <span v-if="patient.nifas_overdue" class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-extrabold">
                                            ⚠️ Evaluasi 42 Hari Selesai
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-bold text-[#26292E]">
                                    <span v-if="patient.status === 'hamil'">
                                        {{ patient.gestational_age_weeks ? `${patient.gestational_age_weeks} Minggu` : '-' }}
                                    </span>
                                    <span v-else-if="patient.status === 'nifas'" class="text-purple-800">
                                        Fase Nifas
                                    </span>
                                    <span v-else class="text-[#73777F]">
                                        Kasus Selesai
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border', getRiskBadge(patient.last_risk_level).bg]">
                                        <span :class="['w-2 h-2 rounded-full', getRiskBadge(patient.last_risk_level).dot]"></span>
                                        <span>{{ getRiskBadge(patient.last_risk_level).label }}</span>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-mono text-[#73777F] text-[11px]">
                                    {{ patient.last_screening_at ? new Date(patient.last_screening_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : 'Belum Pernah' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('bidan.patients.show', patient.id)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs cursor-pointer active:scale-95"
                                        >
                                            <span class="material-symbols-outlined text-sm">timeline</span>
                                            <span>Timeline & Rekam Medis</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BidanLayout>
</template>
