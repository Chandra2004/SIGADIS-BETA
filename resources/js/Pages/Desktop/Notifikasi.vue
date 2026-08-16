<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';

const props = defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
    unreadCount: {
        type: Number,
        default: 0,
    },
});

const markAsRead = (id) => {
    router.post(route('bidan.notifications.mark-read', id), {}, {
        preserveScroll: true,
    });
};

const markAllAsRead = () => {
    router.post(route('bidan.notifications.mark-all-read'), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pusat Notifikasi — SIGADIS Nakes" />

    <BidanLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- 1. Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-3xl border border-[#E3E2E5] shadow-xs">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                        <span class="material-symbols-outlined text-sm">notifications</span>
                        <span>Pemberitahuan Sistem & Alert</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                        Pusat Notifikasi
                    </h1>
                    <p class="text-xs sm:text-sm text-[#43474E]">
                        Riwayat notifikasi darurat masuk, hasil evaluasi risiko skrining ibu hamil, dan pengingat kontrol nifas.
                    </p>
                </div>

                <div v-if="unreadCount > 0" class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="markAllAsRead"
                        class="px-4 py-2 rounded-2xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5"
                    >
                        <span class="material-symbols-outlined text-sm">done_all</span>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                </div>
            </div>

            <!-- 2. Notifications List -->
            <div class="bg-white rounded-3xl border border-[#E3E2E5] shadow-xs divide-y divide-[#F2F3F5] overflow-hidden">
                <div v-if="notifications.length === 0" class="py-12 text-center text-xs text-[#73777F]">
                    <span class="material-symbols-outlined text-4xl text-neutral-300 block mb-2">notifications_off</span>
                    Belum ada notifikasi baru untuk akun Anda.
                </div>

                <div
                    v-for="n in notifications"
                    :key="n.id"
                    :class="[
                        'p-5 transition-colors flex items-start justify-between gap-4',
                        !n.read_at ? 'bg-blue-50/40 hover:bg-blue-50/70' : 'hover:bg-[#FAF9FC]'
                    ]"
                >
                    <div class="flex items-start gap-3.5">
                        <div :class="[
                            'w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 shadow-2xs',
                            n.type.includes('Alert') ? 'bg-rose-100 text-rose-700' : 'bg-blue-100 text-blue-800'
                        ]">
                            <span class="material-symbols-outlined text-xl">
                                {{ n.type.includes('Alert') ? 'emergency' : 'notifications' }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-extrabold text-[#123356]">
                                    {{ n.data?.title || 'Pemberitahuan Sistem' }}
                                </h3>
                                <span v-if="!n.read_at" class="w-2 h-2 rounded-full bg-blue-600"></span>
                            </div>
                            <p class="text-xs text-[#43474E]">
                                {{ n.data?.message || n.data?.body || 'Pesan notifikasi masuk.' }}
                            </p>
                            <span class="text-[11px] font-mono text-[#73777F] block">
                                {{ new Date(n.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }} WIB
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <button
                            v-if="!n.read_at"
                            type="button"
                            @click="markAsRead(n.id)"
                            class="p-2 rounded-xl text-neutral-400 hover:text-[#123356] hover:bg-white transition-all cursor-pointer shadow-2xs"
                            title="Tandai sudah dibaca"
                        >
                            <span class="material-symbols-outlined text-base">check</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </BidanLayout>
</template>
