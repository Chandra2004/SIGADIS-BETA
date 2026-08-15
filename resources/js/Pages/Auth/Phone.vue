<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    phone_number: '',
});

const normalizePhone = (num) => {
    let clean = (num || '').toString().replace(/\D/g, '');
    if (clean.startsWith('62')) return '0' + clean.substring(2);
    if (clean.startsWith('8')) return '0' + clean;
    return clean;
};

const submit = () => {
    form.phone_number = normalizePhone(form.phone_number);
    form.post(route('auth.pregnant.otp.send'));
};
</script>

<template>
    <Head title="Masuk / Daftar Ibu Hamil — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Portal Ibu Hamil"
        panel-title="Mulai Perjalanan Kehamilan Anda"
        panel-description="Masukkan nomor WhatsApp aktif Anda untuk masuk atau mendaftarkan kehamilan baru di SIGADIS."
    >
        <!-- Header -->
        <div class="text-center md:text-left mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F3AEC0]/30 text-[#854E5E] mb-3 shadow-sm">
                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1">pregnant_woman</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Daftar Akun Ibu Hamil
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Kami akan mengirimkan kode verifikasi OTP langsung ke nomor WhatsApp Anda.
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-6" @submit.prevent="submit">
            <div>
                <label class="block text-xs font-semibold text-[#73777F] uppercase tracking-wider mb-2">
                    Nomor WhatsApp
                </label>
                <div class="relative flex rounded-2xl shadow-sm">
                    <span class="inline-flex items-center px-4 rounded-l-2xl border border-r-0 border-[#C3C6CF] bg-[#EDEBEF] text-[#43474E] font-bold text-sm select-none">
                        +62
                    </span>
                    <input
                        v-model="form.phone_number"
                        type="tel"
                        inputmode="numeric"
                        required
                        autofocus
                        placeholder="81234567890"
                        class="w-full px-4 py-3.5 border border-[#C3C6CF] rounded-r-2xl bg-[#FAF9FC] text-[#26292E] text-sm focus:bg-white focus:ring-2 focus:ring-[#F3AEC0]/50 focus:border-[#854E5E] focus:outline-none transition-all"
                    />
                </div>
                <p v-if="form.errors.phone_number" class="text-xs text-[#BA1A1A] mt-1.5 font-medium">
                    {{ form.errors.phone_number }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing || !form.phone_number"
                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-2xl shadow-md font-semibold text-sm text-[#26292E] bg-[#F3AEC0] hover:bg-[#EE98AD] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#F3AEC0] h-12 transition-all disabled:opacity-50 mt-4"
            >
                <svg
                    v-if="form.processing"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-[#26292E]"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Kirim Kode OTP</span>
            </button>

            <!-- Back to Login -->
            <div class="text-center pt-2">
                <Link
                    :href="route('auth.staff.login.show')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#73777F] hover:text-[#26292E] transition-colors"
                >
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali ke Halaman Masuk
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
