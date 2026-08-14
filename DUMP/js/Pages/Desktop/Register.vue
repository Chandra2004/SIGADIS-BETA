<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    full_name: '',
    phone_number: '',
    password: '',
    role: 'bidan',
    str_number: '',
    appointment_letter_ref: '',
    region_code: '',
});

function submit() {
    form.post(route('auth.staff.register'));
}
</script>

<template>
    <Head title="Daftar Bidan/Kader" />

    <div class="flex min-h-screen items-center justify-center bg-neutral-100 px-6 py-10">
        <div class="w-full max-w-sm rounded-xl border border-neutral-200 bg-white p-8 shadow-sm">
            <p class="mb-1 text-center text-xs font-semibold tracking-wide text-brand-navy-700 uppercase">SIGADIS</p>
            <h1 class="mb-6 text-center text-xl font-bold text-brand-navy-900">Daftar Bidan/Kader</h1>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm text-neutral-700">Peran</label>
                    <select v-model="form.role" class="select select-bordered w-full">
                        <option value="bidan">Bidan</option>
                        <option value="kader">Kader</option>
                    </select>
                </div>

                <input v-model="form.full_name" type="text" placeholder="Nama Lengkap" class="input input-bordered w-full" />
                <input v-model="form.phone_number" type="tel" placeholder="Nomor HP" class="input input-bordered w-full" />
                <input v-model="form.password" type="password" placeholder="Password" class="input input-bordered w-full" />
                <input v-model="form.region_code" type="text" placeholder="Kode Wilayah Tugas" class="input input-bordered w-full" />

                <input
                    v-if="form.role === 'bidan'"
                    v-model="form.str_number"
                    type="text"
                    placeholder="Nomor STR"
                    class="input input-bordered w-full"
                />
                <input
                    v-else
                    v-model="form.appointment_letter_ref"
                    type="text"
                    placeholder="Referensi Surat Penunjukan"
                    class="input input-bordered w-full"
                />

                <p v-for="(msg, key) in form.errors" :key="key" class="text-sm text-[--color-error-form]">{{ msg }}</p>

                <button type="submit" :disabled="form.processing" class="btn w-full border-none bg-brand-navy-900 text-white">
                    Daftar
                </button>
            </form>

            <a :href="route('auth.staff.login.show')" class="mt-4 block text-center text-sm text-brand-navy-700 underline">
                Sudah punya akun? Masuk
            </a>
        </div>
    </div>
</template>
