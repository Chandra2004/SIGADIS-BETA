<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    full_name: '',
});

const submit = () => {
    form.post(route('auth.pregnant.name.save'));
};
</script>

<template>
    <Head title="Lengkapi Nama — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Identitas Ibu Hamil"
        panel-title="Selamat Datang di SIGADIS!"
        panel-description="Masukkan nama lengkap Anda agar tenaga kesehatan dan bidan desa dapat mengenali dan mendampingi Anda secara personal."
    >
        <!-- Header -->
        <div class="text-center md:text-left mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#F3AEC0]/30 text-[#854E5E] mb-3 shadow-sm">
                <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1">badge</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Siapa Nama Anda?
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Nomor WhatsApp Anda telah berhasil diverifikasi. Silakan masukkan nama lengkap Anda untuk melanjutkan.
            </p>
        </div>

        <!-- Form -->
        <form class="space-y-6" @submit.prevent="submit">
            <div>
                <label class="block text-xs font-semibold text-[#73777F] uppercase tracking-wider mb-2">
                    Nama Lengkap Ibu
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-xl text-[#73777F]">person</span>
                    <input
                        v-model="form.full_name"
                        type="text"
                        required
                        autofocus
                        placeholder="Contoh: Siti Rahmawati"
                        class="w-full pl-12 pr-4 py-3.5 border border-[#C3C6CF] rounded-2xl bg-[#FAF9FC] text-[#26292E] text-sm focus:bg-white focus:ring-2 focus:ring-[#F3AEC0]/50 focus:border-[#854E5E] focus:outline-none transition-all shadow-sm"
                    />
                </div>
                <p v-if="form.errors.full_name" class="text-xs text-[#BA1A1A] mt-1.5 font-medium">
                    {{ form.errors.full_name }}
                </p>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing || !form.full_name.trim()"
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
                <span>Simpan & Lanjutkan</span>
            </button>
        </form>
    </AuthLayout>
</template>
