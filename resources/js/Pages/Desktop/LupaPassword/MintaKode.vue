<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    phone_number: '',
});

const submit = () => {
    form.post(route('auth.staff.password-reset.send'));
};
</script>

<template>
    <Head title="Lupa Kata Sandi — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Pemulihan Akun"
        panel-title="Reset Kata Sandi Tenaga Kesehatan"
        panel-description="Masukkan nomor handphone WhatsApp Anda yang terdaftar pada sistem SIGADIS untuk menerima kode verifikasi OTP pemulihan kata sandi."
    >
        <!-- Header -->
        <div class="text-center md:text-left mb-8">
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Lupa Kata Sandi?
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Kami akan mengirimkan 6-digit kode verifikasi ke WhatsApp Anda untuk mengatur ulang kata sandi.
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-5" @submit.prevent="submit">
            
            <!-- Nomor Handphone / Email -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-[#26292E]" for="reset-phone">
                    Nomor WhatsApp / Email Terdaftar
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                        <span class="material-symbols-outlined text-xl">account_circle</span>
                    </div>
                    <input
                        id="reset-phone"
                        v-model="form.phone_number"
                        type="text"
                        autocomplete="username"
                        required
                        autofocus
                        placeholder="08123456789 atau email terdaftar"
                        class="block w-full pl-11 pr-4 py-3 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-12"
                    />
                </div>
                <p v-if="form.errors.phone_number" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                    {{ form.errors.phone_number }}
                </p>
            </div>

            <!-- Notice Box -->
            <div class="flex items-start gap-3 rounded-xl bg-[#ABC9F3]/20 border border-[#ABC9F3]/50 p-3.5 text-xs text-[#2C4A6E] leading-relaxed">
                <span class="material-symbols-outlined text-base mt-0.5 shrink-0">sms</span>
                <span>Kode verifikasi OTP 6 digit akan dikirimkan ke WhatsApp/Email Anda untuk mengamankan proses pengaturan ulang kata sandi.</span>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C4A6E] h-12 transition-all disabled:opacity-60 mt-6"
            >
                <svg
                    v-if="form.processing"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Kirim Kode Verifikasi</span>
            </button>

            <!-- Back to Login -->
            <div class="text-center pt-2">
                <Link
                    :href="route('auth.staff.login.show')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2C4A6E] hover:text-[#3D6086] transition-colors"
                >
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Kembali ke Halaman Masuk
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
