<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const submit = () => {
    form.post(route('auth.admin.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login Administrator — SIGADIS" />

    <div class="min-h-screen bg-[#F2F3F5] flex items-center justify-center p-4 antialiased font-sans">
        <div class="w-full max-w-md bg-white rounded-3xl p-8 border border-[#E3E2E5] shadow-xl space-y-6">
            <!-- Header Brand -->
            <div class="text-center space-y-2">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-[#123356] text-[#F3AEC0] shadow-md mb-2">
                    <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">shield_with_heart</span>
                </div>
                <h1 class="text-2xl font-extrabold text-[#123356] tracking-tight">Portal Administrator</h1>
                <p class="text-xs text-[#73777F]">Masuk ke panel manajemen Dinas Kesehatan / Puskesmas</p>
            </div>

            <!-- Error Banner -->
            <div
                v-if="form.errors.email || form.errors.password"
                class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2.5"
            >
                <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
                <span>{{ form.errors.email || form.errors.password }}</span>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#26292E]">Email Administrator</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        placeholder="admin@sigadis.test"
                        class="w-full px-4 py-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs text-[#26292E] focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-[#26292E]">Kata Sandi</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs text-[#26292E] focus:bg-white focus:border-[#123356] focus:outline-none"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 px-4 rounded-xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold shadow-md transition-all active:scale-95 disabled:opacity-50"
                >
                    {{ form.processing ? 'Memproses...' : 'Masuk Panel Admin' }}
                </button>
            </form>

            <div class="pt-2 text-center border-t border-[#F2F3F5]">
                <Link :href="route('landing.home')" class="text-xs font-bold text-[#73777F] hover:text-[#123356]">
                    &larr; Kembali ke Beranda Utama
                </Link>
            </div>
        </div>
    </div>
</template>
