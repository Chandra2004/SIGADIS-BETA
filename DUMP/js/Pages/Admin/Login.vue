<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Shared/Icon.vue';

const form = useForm({
    email: '',
    password: '',
});

function submit() {
    form.post(route('auth.admin.login'));
}
</script>

<template>
    <Head title="Login Admin" />

    <div class="flex min-h-screen">
        <aside class="hidden w-1/3 flex-col justify-between bg-brand-navy-900 p-10 text-white md:flex">
            <div>
                <p class="mb-8 flex items-center gap-2 text-xl font-bold">
                    <Icon name="shield" size="h-6 w-6" /> SIGADIS
                </p>
                <h1 class="mb-3 text-2xl font-bold">Panel Admin</h1>
                <p class="text-sm text-white/80">
                    Verifikasi akun bidan &amp; kader, kelola cakupan wilayah, dan pulihkan akses ibu hamil yang
                    kehilangan nomor HP.
                </p>
            </div>
            <p class="text-xs text-white/50">© {{ new Date().getFullYear() }} SIGADIS. Mendukung Kesehatan Ibu Hamil Indonesia.</p>
        </aside>

        <div class="flex flex-1 items-center justify-center bg-neutral-100 px-6">
            <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
                <p class="mb-1 text-center text-xs font-semibold tracking-wide text-brand-navy-700 uppercase md:hidden">SIGADIS</p>
                <h1 class="mb-1 text-xl font-bold text-brand-navy-900">Masuk ke Panel Admin</h1>
                <p class="mb-6 text-sm text-neutral-500">Khusus admin puskesmas/dinas kesehatan.</p>

                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm text-neutral-700">Email</label>
                        <input v-model="form.email" type="email" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm text-neutral-700">Password</label>
                        <input v-model="form.password" type="password" class="input input-bordered w-full" />
                    </div>

                    <p v-if="form.errors.password" class="text-sm text-[--color-error-form]">{{ form.errors.password }}</p>

                    <button type="submit" :disabled="form.processing" class="btn w-full border-none bg-brand-navy-900 text-white">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
