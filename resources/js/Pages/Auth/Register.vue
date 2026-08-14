<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar | SIGADIS" />

    <AuthLayout
        mascot-key="menyambut"
        panel-title="Mulai Perjalanan Anda Bersama SIGADIS"
        panel-description="Buat akun untuk mendapatkan pendampingan kesehatan yang lebih terhubung dan siap membantu di kondisi darurat."
    >
        <h1 class="text-2xl font-black tracking-tight text-ink-900">Buat Akun SIGADIS</h1>
        <p class="mt-2 text-sm text-brand-navy-700">
            Sudah punya akun?
            <Link href="/login" class="font-semibold text-brand-navy-900 underline underline-offset-2 hover:text-brand-navy-700">
                Masuk di sini
            </Link>
        </p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div class="form-control">
                <label class="label" for="name">
                    <span class="label-text font-semibold text-ink-900">Nama lengkap</span>
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    autocomplete="name"
                    required
                    autofocus
                    class="input input-bordered w-full rounded-2xl border-brand-navy-900/20 focus:border-brand-navy-900 focus:outline-none"
                    placeholder="Nama Anda"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-error-form">{{ form.errors.name }}</p>
            </div>

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
                    autocomplete="new-password"
                    required
                    class="input input-bordered w-full rounded-2xl border-brand-navy-900/20 focus:border-brand-navy-900 focus:outline-none"
                    placeholder="••••••••"
                />
                <p v-if="form.errors.password" class="mt-1 text-sm text-error-form">{{ form.errors.password }}</p>
            </div>

            <div class="form-control">
                <label class="label" for="password_confirmation">
                    <span class="label-text font-semibold text-ink-900">Konfirmasi kata sandi</span>
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="input input-bordered w-full rounded-2xl border-brand-navy-900/20 focus:border-brand-navy-900 focus:outline-none"
                    placeholder="••••••••"
                />
                <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-error-form">{{ form.errors.password_confirmation }}</p>
            </div>

            <p class="text-xs leading-5 text-brand-navy-700">
                Dengan mendaftar, Anda menyetujui bahwa data kesehatan yang Anda masukkan akan digunakan untuk
                keperluan pendampingan dan respons darurat sesuai kebijakan privasi SIGADIS.
            </p>

            <button type="submit" class="btn btn-primary h-12 w-full rounded-2xl text-base font-semibold" :disabled="form.processing">
                <span v-if="form.processing" class="loading loading-spinner loading-sm" />
                Daftar Sekarang
            </button>
        </form>
    </AuthLayout>
</template>
