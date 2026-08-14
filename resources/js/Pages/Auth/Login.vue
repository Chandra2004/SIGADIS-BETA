<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    status: { type: String, default: '' },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk | SIGADIS" />

    <AuthLayout
        mascot-key="menyambutKembali"
        panel-title="Senang Bertemu Lagi"
        panel-description="Masuk untuk melanjutkan pendampingan kesehatan dan tetap terhubung dengan bidan/kader Anda."
    >
        <h1 class="text-2xl font-black tracking-tight text-ink-900">Masuk ke SIGADIS</h1>
        <p class="mt-2 text-sm text-brand-navy-700">
            Belum punya akun?
            <Link href="/register" class="font-semibold text-brand-navy-900 underline underline-offset-2 hover:text-brand-navy-700">
                Daftar di sini
            </Link>
        </p>

        <div v-if="status" class="mt-6 rounded-2xl bg-risk-low-bg px-4 py-3 text-sm font-medium text-risk-low">
            {{ status }}
        </div>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div class="form-control">
                <label class="label" for="email">
                    <span class="label-text font-semibold text-ink-900">Email</span>
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    required
                    autofocus
                    class="input input-bordered w-full rounded-2xl border-brand-navy-900/20 focus:border-brand-navy-900 focus:outline-none"
                    placeholder="nama@email.com"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-error-form">{{ form.errors.email }}</p>
            </div>

            <div class="form-control">
                <label class="label" for="password">
                    <span class="label-text font-semibold text-ink-900">Kata sandi</span>
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="input input-bordered w-full rounded-2xl border-brand-navy-900/20 focus:border-brand-navy-900 focus:outline-none"
                    placeholder="••••••••"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-error-form">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-brand-navy-700">
                    <input v-model="form.remember" type="checkbox" class="checkbox checkbox-sm rounded-md border-brand-navy-900/30" />
                    Ingat saya
                </label>
                <Link href="/forgot-password" class="text-sm font-semibold text-brand-navy-900 hover:text-brand-navy-700">
                    Lupa kata sandi?
                </Link>
            </div>

            <button type="submit" class="btn btn-primary h-12 w-full rounded-2xl text-base font-semibold" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Masuk
            </button>
        </form>
    </AuthLayout>
</template>
