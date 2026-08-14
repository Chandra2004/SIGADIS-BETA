<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const form = useForm({
    full_name: '',
});

function submit() {
    form.post(route('auth.pregnant.name.save'));
}
</script>

<template>
    <Head title="Nama Anda" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="" :show-close="false" />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 pt-4">
            <h1 class="mb-2 text-2xl font-bold text-brand-navy-900">Siapa Nama Anda?</h1>
            <p class="mb-8 text-sm text-brand-navy-700">
                Bisa nama Ibu sendiri, atau anggota keluarga yang mendaftarkan.
            </p>

            <form class="space-y-4" @submit.prevent="submit">
                <input
                    v-model="form.full_name"
                    type="text"
                    placeholder="Nama Anda"
                    autofocus
                    class="input input-bordered w-full border-brand-navy-100 bg-white text-lg"
                />
                <p v-if="form.errors.full_name" class="text-sm text-[--color-error-form]">
                    {{ form.errors.full_name }}
                </p>

                <button
                    type="submit"
                    :disabled="!form.full_name || form.processing"
                    class="btn w-full border-none bg-brand-navy-900 text-white disabled:bg-brand-navy-100 disabled:text-brand-navy-700"
                >
                    Lanjut
                </button>
            </form>
        </div>
    </div>
</template>
