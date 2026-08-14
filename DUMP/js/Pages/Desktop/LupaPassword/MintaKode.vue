<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ phone_number: '' });

function submit() {
    form.post(route('auth.staff.password-reset.send'));
}
</script>

<template>
    <Head title="Lupa Password" />

    <div class="flex min-h-screen items-center justify-center bg-neutral-100 px-6">
        <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
            <p class="mb-1 text-center text-xs font-semibold tracking-wide text-brand-navy-700 uppercase">SIGADIS</p>
            <div class="mb-4 flex justify-center gap-1.5">
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-900" />
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-100" />
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-100" />
            </div>
            <h1 class="mb-2 text-center text-xl font-bold text-brand-navy-900">Reset Kata Sandi</h1>
            <p class="mb-6 text-center text-sm text-neutral-500">
                Masukkan nomor HP terdaftar untuk menerima kode verifikasi lewat WhatsApp.
            </p>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm text-neutral-700">Nomor HP</label>
                    <input v-model="form.phone_number" type="tel" placeholder="08xxxxxxxxxx" autofocus class="input input-bordered w-full" />
                    <p v-if="form.errors.phone_number" class="mt-1 text-sm text-[--color-error-form]">{{ form.errors.phone_number }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="btn w-full border-none bg-brand-navy-900 text-white">
                    Kirim Kode Verifikasi
                </button>
            </form>

            <a :href="route('auth.staff.login.show')" class="mt-4 block text-center text-sm text-brand-navy-700 underline">
                &larr; Kembali ke Masuk
            </a>
        </div>
    </div>
</template>
