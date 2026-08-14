<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('auth.staff.password-reset.store'));
}
</script>

<template>
    <Head title="Atur Password Baru" />

    <div class="flex min-h-screen items-center justify-center bg-neutral-100 px-6">
        <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
            <p class="mb-1 text-center text-xs font-semibold tracking-wide text-brand-navy-700 uppercase">SIGADIS</p>
            <div class="mb-4 flex justify-center gap-1.5">
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-900" />
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-900" />
                <span class="h-1.5 w-6 rounded-full bg-brand-navy-900" />
            </div>
            <h1 class="mb-2 text-center text-xl font-bold text-brand-navy-900">Atur Kata Sandi Baru</h1>
            <p class="mb-6 text-center text-sm text-neutral-500">Buat kata sandi baru yang kuat dan mudah diingat.</p>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm text-neutral-700">Kata Sandi Baru</label>
                    <input v-model="form.password" type="password" autofocus class="input input-bordered w-full" />
                </div>
                <div>
                    <label class="mb-1 block text-sm text-neutral-700">Konfirmasi Kata Sandi</label>
                    <input v-model="form.password_confirmation" type="password" class="input input-bordered w-full" />
                </div>

                <p v-if="form.errors.password" class="text-sm text-[--color-error-form]">{{ form.errors.password }}</p>

                <button type="submit" :disabled="form.processing" class="btn w-full border-none bg-brand-navy-900 text-white">
                    Simpan &amp; Masuk
                </button>
            </form>
        </div>
    </div>
</template>
