<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const form = useForm({
    phone_number: '',
});

const isValid = computed(() => /^08[0-9]{8,13}$/.test(form.phone_number));

function submit() {
    if (!isValid.value) return;
    form.post(route('auth.pregnant.otp.send'));
}
</script>

<template>
    <Head title="Masuk / Daftar" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="" :show-close="false" :back-href="route('splash')" />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 pt-4">
            <h1 class="mb-2 text-2xl font-bold text-brand-navy-900">Masukkan Nomor HP</h1>
            <p class="mb-8 text-sm text-brand-navy-700">Kami akan mengirimkan kode verifikasi melalui WhatsApp.</p>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="rounded-lg border border-brand-navy-100 bg-white p-3">
                    <label class="mb-1 block text-xs font-semibold text-brand-navy-700 uppercase">Nomor Handphone</label>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-brand-navy-100 px-2 py-1 text-sm font-medium text-brand-navy-900">+62</span>
                        <input
                            v-model="form.phone_number"
                            type="tel"
                            inputmode="numeric"
                            placeholder="8xxxxxxxxx"
                            autofocus
                            class="input input-ghost w-full border-none text-lg focus:outline-none"
                        />
                    </div>
                </div>
                <p v-if="form.errors.phone_number" class="text-sm text-[--color-error-form]">
                    {{ form.errors.phone_number }}
                </p>

                <div class="flex justify-end pb-4">
                    <img src="/assets/images/mascot/pose-11-menunjuk-arah.png" alt="" class="h-20 w-20 object-contain" />
                </div>

                <button
                    type="submit"
                    :disabled="!isValid || form.processing"
                    class="btn w-full border-none bg-brand-navy-900 text-white disabled:bg-brand-navy-100 disabled:text-brand-navy-700"
                >
                    Kirim Kode
                </button>
            </form>
        </div>
    </div>
</template>
