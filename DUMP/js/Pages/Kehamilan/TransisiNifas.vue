<script setup>
import { Head, router } from '@inertiajs/vue3';
import Icon from '@/Components/Shared/Icon.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

defineProps({
    motherName: { type: String, required: true },
});

const focusItems = [
    { icon: 'speaker', title: 'Skrining Kesehatan Nifas Berkala', color: 'bg-brand-navy-100 text-brand-navy-900' },
    { icon: 'alert', title: 'Deteksi Dini Perdarahan & Demam Nifas', color: 'bg-risk-high-bg text-risk-high' },
    { icon: 'heart', title: 'Pendampingan Kesehatan Mental Ibu', color: 'bg-brand-pink-200 text-[--color-brand-pink-500]' },
];

function enterBeranda() {
    router.post(route('kehamilan.nifas.transisi.ack'));
}
</script>

<template>
    <Head title="Transisi Masa Nifas" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TopAppBar title="Transisi Masa Nifas" />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 py-6">
            <section class="mb-6 flex items-center gap-3 rounded-xl bg-brand-pink-200 p-4">
                <div class="flex-1">
                    <span class="mb-2 inline-block rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-navy-900">
                        Masa Nifas 42 Hari
                    </span>
                    <p class="font-bold text-brand-navy-900">Selamat atas Kelahiran Buah Hati, Ibu {{ motherName }}!</p>
                    <p class="text-sm text-brand-navy-700">
                        Bidan Pendamping telah mengonfirmasi proses persalinan. SIGADIS siap mendampingi masa pemulihan
                        Ibu &amp; Bayi selama 42 hari ke depan.
                    </p>
                </div>
                <img src="/assets/images/mascot/pose-13-menyambut-kembali.png" alt="" class="h-20 w-20 shrink-0 object-contain" />
            </section>

            <section class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-bold text-brand-navy-900">Fokus Pemantauan Nifas</h2>
                <div class="space-y-2">
                    <div v-for="item in focusItems" :key="item.title" class="flex items-center gap-3 rounded-lg bg-brand-pink-50 p-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="item.color">
                            <Icon :name="item.icon" size="h-5 w-5" />
                        </span>
                        <p class="text-sm font-medium text-brand-navy-900">{{ item.title }}</p>
                    </div>
                </div>
            </section>

            <button type="button" class="btn w-full gap-2 border-none bg-brand-navy-900 text-white" @click="enterBeranda">
                Masuk ke Beranda Nifas <Icon name="arrowRight" size="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
