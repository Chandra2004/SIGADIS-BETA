<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    worker: {
        type: Object,
        default: () => ({}),
    },
});

const logout = () => {
    router.post(route('auth.staff.logout'));
};
</script>

<template>
    <Head title="Menunggu Verifikasi — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Status Pendaftaran"
        panel-title="Menunggu Verifikasi Administrator"
        panel-description="Akun Anda sedang dalam proses verifikasi oleh administrator Dinas Kesehatan / Puskesmas setempat untuk memastikan validitas STR/SK."
    >
        <div class="text-center md:text-left mb-6">
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 mb-4 shadow-sm">
                <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">hourglass_top</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Menunggu Verifikasi
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Pendaftaran akun tenaga kesehatan Anda berhasil dikirimkan.
            </p>
        </div>

        <!-- Worker Information Card -->
        <div class="rounded-2xl border border-[#C3C6CF] bg-[#FAF9FC] p-5 space-y-4 shadow-sm mb-6">
            <div class="flex items-center justify-between border-b border-[#E3E2E5] pb-3">
                <span class="text-xs font-semibold text-[#73777F]">Status Akun</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Menunggu Persetujuan
                </span>
            </div>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-[#73777F]">Nama Lengkap:</span>
                    <span class="font-bold text-[#26292E]">{{ worker?.full_name || 'Tenaga Kesehatan' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#73777F]">Peran:</span>
                    <span class="font-bold text-[#2C4A6E] uppercase">{{ worker?.role || 'Bidan' }}</span>
                </div>
                <div v-if="worker?.str_number" class="flex justify-between">
                    <span class="text-[#73777F]">Nomor STR:</span>
                    <span class="font-medium text-[#26292E] font-mono">{{ worker.str_number }}</span>
                </div>
                <div v-if="worker?.appointment_letter_ref" class="flex justify-between">
                    <span class="text-[#73777F]">Nomor SK:</span>
                    <span class="font-medium text-[#26292E] font-mono">{{ worker.appointment_letter_ref }}</span>
                </div>
                <div v-if="worker?.region_code" class="flex justify-between">
                    <span class="text-[#73777F]">Kode Wilayah:</span>
                    <span class="font-medium text-[#26292E]">{{ worker.region_code }}</span>
                </div>
            </div>
        </div>

        <!-- Alert Notification -->
        <div class="rounded-xl bg-[#ABC9F3]/20 border border-[#ABC9F3]/50 p-4 text-xs text-[#2C4A6E] leading-relaxed mb-6">
            <div class="flex items-start gap-2.5">
                <span class="material-symbols-outlined text-base mt-0.5 shrink-0">mark_email_read</span>
                <span>
                    Anda akan mendapatkan akses penuh ke <strong>Dashboard Monitoring Maternal</strong> segera setelah verifikasi disetujui. Silakan hubungi admin puskesmas jika membutuhkan percepatan verifikasi.
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <button
                type="button"
                class="w-full flex justify-center items-center py-3 px-4 rounded-xl font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] shadow-md transition-all h-12"
                @click="router.reload()"
            >
                <span class="material-symbols-outlined text-lg mr-2">refresh</span>
                Cek Status Sekarang
            </button>

            <button
                type="button"
                class="w-full flex justify-center items-center py-3 px-4 rounded-xl font-semibold text-sm text-[#43474E] bg-white border border-[#C3C6CF] hover:bg-[#F4F3F6] transition-all h-12"
                @click="logout"
            >
                <span class="material-symbols-outlined text-lg mr-2">logout</span>
                Keluar dari Akun
            </button>
        </div>
    </AuthLayout>
</template>
