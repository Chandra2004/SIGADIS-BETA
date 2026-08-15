<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('auth.staff.password-reset.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Atur Kata Sandi Baru — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Kata Sandi Baru"
        panel-title="Buat Kata Sandi Akun Baru"
        panel-description="Buat kata sandi baru yang kuat untuk mengamankan akun dan data maternal pasien kehamilan Anda di SIGADIS."
    >
        <!-- Header -->
        <div class="text-center md:text-left mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 mb-3">
                <span class="material-symbols-outlined text-2xl">lock_open</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Atur Kata Sandi Baru
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Silakan masukkan kata sandi baru untuk akun Anda. Minimal 8 karakter.
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-4" @submit.prevent="submit">
            
            <!-- Kata Sandi Baru -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-[#26292E]" for="new-password">
                    Kata Sandi Baru
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                        <span class="material-symbols-outlined text-xl">lock</span>
                    </div>
                    <input
                        id="new-password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="new-password"
                        required
                        autofocus
                        placeholder="Min. 8 karakter"
                        class="block w-full pl-11 pr-11 py-3 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-12"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#73777F] hover:text-[#26292E]"
                        @click="showPassword = !showPassword"
                    >
                        <span class="material-symbols-outlined text-xl">
                            {{ showPassword ? 'visibility_off' : 'visibility' }}
                        </span>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Konfirmasi Kata Sandi Baru -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-[#26292E]" for="new-password-confirm">
                    Konfirmasi Kata Sandi
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                        <span class="material-symbols-outlined text-xl">lock_reset</span>
                    </div>
                    <input
                        id="new-password-confirm"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        required
                        placeholder="Ulangi kata sandi baru"
                        class="block w-full pl-11 pr-11 py-3 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-12"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#73777F] hover:text-[#26292E]"
                        @click="showPasswordConfirm = !showPasswordConfirm"
                    >
                        <span class="material-symbols-outlined text-xl">
                            {{ showPasswordConfirm ? 'visibility_off' : 'visibility' }}
                        </span>
                    </button>
                </div>
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
                <span>Simpan & Masuk ke Akun</span>
            </button>
        </form>
    </AuthLayout>
</template>
