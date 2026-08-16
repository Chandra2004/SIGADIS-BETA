<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';

const props = defineProps({
    alerts: {
        type: Array,
        default: () => [],
    },
    statusFilter: {
        type: String,
        default: 'semua',
    },
    activeCount: {
        type: Number,
        default: 0,
    },
});

const searchQuery = ref('');

const setFilter = (s) => {
    router.get(
        route('bidan.alerts.index'),
        { status: s },
        { preserveState: true, replace: true }
    );
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'pending':
        case 'delivered':
            return { label: 'Menunggu Respon', bg: 'bg-rose-100 text-rose-800 border-rose-300', dot: 'bg-rose-600 animate-pulse' };
        case 'being_handled':
            return { label: 'Sedang Ditangani', bg: 'bg-amber-100 text-amber-900 border-amber-300', dot: 'bg-amber-500' };
        case 'resolved':
            return { label: 'Selesai Ditangani', bg: 'bg-emerald-100 text-emerald-800 border-emerald-300', dot: 'bg-emerald-500' };
        default:
            return { label: status, bg: 'bg-neutral-100 text-neutral-800 border-neutral-200', dot: 'bg-neutral-500' };
    }
};

const filteredAlerts = () => {
    if (!searchQuery.value.trim()) return props.alerts;
    const q = searchQuery.value.toLowerCase();
    return props.alerts.filter((a) => a.mother_name?.toLowerCase().includes(q) || a.address?.toLowerCase().includes(q));
};
</script>

<template>
    <Head title="Pusat Kasus Gawat Darurat — SIGADIS Nakes" />

    <BidanLayout>
        <div class="space-y-6">
            <!-- 1. Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-800 text-xs font-bold border border-rose-200">
                        <span class="material-symbols-outlined text-sm">emergency</span>
                        <span>Pusat Tanggap Darurat Maternal</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Riwayat & Antrean Kasus Gawat Darurat
                    </h1>
                    <p class="text-sm text-[#43474E]">
                        Kelola seluruh panggilan SOS darurat dan temuan gejala skrining kritis di wilayah pengawasan Anda.
                    </p>
                </div>

                <div v-if="activeCount > 0" class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-600 text-white font-bold text-xs shadow-md animate-pulse">
                    <span class="w-2.5 h-2.5 rounded-full bg-white"></span>
                    <span>{{ activeCount }} Panggilan Darurat Aktif</span>
                </div>
            </div>

            <!-- 2. Main List Container -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs overflow-hidden">
                <!-- Filters & Search -->
                <div class="p-6 border-b border-[#F2F3F5] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="setFilter('semua')"
                            :class="[
                                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer shadow-xs',
                                statusFilter === 'semua'
                                    ? 'bg-[#123356] text-white shadow-md'
                                    : 'bg-[#FAF9FC] text-[#43474E] hover:bg-neutral-100 border border-[#E3E2E5]'
                            ]"
                        >
                            Semua Kasus ({{ alerts.length }})
                        </button>

                        <button
                            type="button"
                            @click="setFilter('pending')"
                            :class="[
                                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer shadow-xs',
                                statusFilter === 'pending'
                                    ? 'bg-rose-700 text-white shadow-md'
                                    : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-200'
                            ]"
                        >
                            🚨 Menunggu Respon
                        </button>

                        <button
                            type="button"
                            @click="setFilter('being_handled')"
                            :class="[
                                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer shadow-xs',
                                statusFilter === 'being_handled'
                                    ? 'bg-amber-600 text-white shadow-md'
                                    : 'bg-amber-50 text-amber-900 hover:bg-amber-100 border border-amber-200'
                            ]"
                        >
                            🟡 Sedang Ditangani
                        </button>

                        <button
                            type="button"
                            @click="setFilter('resolved')"
                            :class="[
                                'px-4 py-2 rounded-2xl text-xs font-bold transition-all cursor-pointer shadow-xs',
                                statusFilter === 'resolved'
                                    ? 'bg-emerald-700 text-white shadow-md'
                                    : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200'
                            ]"
                        >
                            🟢 Selesai Ditangani
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full md:w-64">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 text-lg">search</span>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama ibu / alamat..."
                            class="w-full pl-10 pr-4 py-2 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Table / Cards of Alerts -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#FAF9FC] text-[#73777F] text-xs uppercase font-bold border-b border-[#E3E2E5]">
                            <tr>
                                <th class="py-3.5 px-6">Identitas Pasien & Pemicu</th>
                                <th class="py-3.5 px-4">Waktu Pemicu</th>
                                <th class="py-3.5 px-4">Status & Nakes Penangan</th>
                                <th class="py-3.5 px-4">Gejala Kritis Terdeteksi</th>
                                <th class="py-3.5 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F2F3F5] text-xs">
                            <tr v-if="filteredAlerts().length === 0">
                                <td colspan="5" class="py-8 text-center text-xs text-[#73777F]">
                                    <span class="material-symbols-outlined text-3xl text-neutral-400 block mb-1">check_circle</span>
                                    Tidak ada data kasus darurat yang sesuai filter.
                                </td>
                            </tr>

                            <tr
                                v-for="alert in filteredAlerts()"
                                :key="alert.id"
                                class="hover:bg-[#FAF9FC] transition-colors"
                            >
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-[#123356] text-sm">{{ alert.mother_name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-900 text-[10px] font-bold uppercase">
                                            {{ alert.trigger_type === 'manual_button' ? 'Tombol SOS' : 'Skrining Red Flag' }}
                                        </span>
                                        <span class="text-[11px] text-[#73777F] font-mono">{{ alert.gestational_age_weeks || '-' }} Minggu</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-mono text-[#26292E] text-xs font-semibold">
                                    {{ new Date(alert.triggered_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }} WIB
                                </td>
                                <td class="py-4 px-4">
                                    <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border', getStatusBadge(alert.status).bg]">
                                        <span :class="['w-2 h-2 rounded-full', getStatusBadge(alert.status).dot]"></span>
                                        <span>{{ getStatusBadge(alert.status).label }}</span>
                                    </span>
                                    <span v-if="alert.handled_by" class="block text-[11px] text-amber-900 font-bold mt-1">
                                        Oleh: {{ alert.handled_by }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <span
                                            v-for="(s, idx) in (alert.triggered_symptoms || [])"
                                            :key="idx"
                                            class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-800 text-[10px] font-bold border border-rose-200"
                                        >
                                            {{ s }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <Link
                                        :href="route('bidan.alerts.show', alert.id)"
                                        class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-[#123356] text-white text-xs font-bold hover:bg-[#2C4A6E] transition-all shadow-xs"
                                    >
                                        <span class="material-symbols-outlined text-sm">near_me</span>
                                        <span>Buka Komando Kasus</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BidanLayout>
</template>
