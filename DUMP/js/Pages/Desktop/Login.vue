<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    phone_number: '',
    password: '',
});

function submit() {
    form.post(route('auth.staff.login'));
}
</script>

<template>
    <Head title="Login Bidan/Kader" />

    <div class="flex min-h-screen">
        <aside class="hidden w-1/3 flex-col justify-between bg-brand-navy-900 p-10 text-white md:flex">
            <div>
                <p class="mb-8 text-xl font-bold">SIGADIS</p>
                <h1 class="mb-3 text-2xl font-bold">Sistem Deteksi Dini &amp; Respon Cepat</h1>
                <p class="text-sm text-white/80">
                    Portal Bidan &amp; Kader. Akses data maternal terpusat, pantau risiko tinggi, dan berikan respons
                    cepat untuk keselamatan ibu dan bayi.
                </p>
            </div>
            <p class="text-xs text-white/50">© {{ new Date().getFullYear() }} SIGADIS. Mendukung Kesehatan Ibu Hamil Indonesia.</p>
        </aside>

        <div class="flex flex-1 items-center justify-center bg-neutral-100 px-6">
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
                <p class="mb-1 text-center text-xs font-semibold tracking-wide text-brand-navy-700 uppercase md:hidden">SIGADIS</p>
                <h1 class="mb-1 text-xl font-bold text-brand-navy-900">Masuk ke Portal</h1>
                <p class="mb-6 text-sm text-neutral-500">Silakan masuk menggunakan kredensial Anda yang terdaftar.</p>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm text-neutral-700">Nomor HP</label>
                        <input v-model="form.phone_number" type="tel" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-neutral-700">Password</label>
                        <input v-model="form.password" type="password" class="input input-bordered w-full" />
                    </div>

                    <p v-if="form.errors.password" class="text-sm text-[--color-error-form]">{{ form.errors.password }}</p>

                    <div class="text-right">
                        <a :href="route('auth.staff.password-reset.request')" class="text-sm text-brand-navy-700 underline">Lupa Password?</a>
                    </div>

                    <button type="submit" :disabled="form.processing" class="btn w-full border-none bg-brand-navy-900 text-white">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
