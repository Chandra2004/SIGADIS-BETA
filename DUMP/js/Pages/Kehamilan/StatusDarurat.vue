<script setup>
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    alert: { type: Object, required: true },
    midwifePhone: { type: String, default: null },
});

// ponytail: polling tiap 10 detik, bukan WebSocket — cukup buat status
// yang jarang berubah, upgrade ke Echo kalau nanti butuh update instan.
let poll = null;

onMounted(() => {
    if (props.alert.status === 'resolved') return;

    poll = setInterval(() => {
        router.reload({ only: ['alert'] });
    }, 10000);
});

onUnmounted(() => clearInterval(poll));

function formatTime(value) {
    return value ? new Date(value).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : null;
}
</script>

<template>
    <Head title="Status Darurat" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader
            :title="alert.status === 'resolved' ? 'Penanganan Selesai' : 'Status Darurat Aktif'"
            :back-href="route('kehamilan.beranda')"
            :show-close="false"
        />

        <div class="mx-auto w-full max-w-md flex-1 px-6 py-6">
            <div class="mb-6 rounded-xl bg-emergency-alert p-6 text-center text-white">
                <img src="/assets/images/mascot/pose-10-menemani-berjaga.png" alt="" class="mx-auto mb-3 h-24 w-24 object-contain" />
                <p v-if="alert.status !== 'resolved'" class="mb-1 text-xs font-semibold tracking-wide uppercase">Sinyal SOS Terkirim</p>
                <h1 class="text-xl font-bold">
                    {{ alert.status === 'resolved' ? 'Penanganan Selesai' : 'Bantuan Sedang Diproses!' }}
                </h1>
                <p class="mt-1 text-sm text-white/90">
                    {{ alert.status === 'resolved'
                        ? 'Terima kasih sudah tetap tenang, Ibu.'
                        : 'Tetap tenang Ibu, titik lokasi dan data kehamilan telah diterima bidan.' }}
                </p>
            </div>

            <section class="mb-6 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-brand-navy-700 uppercase">Status Penanganan</h2>

                <div class="space-y-4">
                    <div v-for="step in alert.steps" :key="step.key" class="flex items-start gap-3">
                        <span
                            class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full"
                            :class="step.done ? 'bg-risk-low text-white' : 'bg-brand-navy-100 text-brand-navy-700'"
                        >
                            <Icon v-if="step.done" name="check" size="h-3.5 w-3.5" />
                        </span>
                        <div>
                            <p class="text-sm font-medium" :class="step.done ? 'text-brand-navy-900' : 'text-brand-navy-700'">
                                {{ step.label }}
                            </p>
                            <p v-if="step.detail" class="text-xs text-brand-navy-700">{{ step.detail }}</p>
                            <p v-if="step.at" class="text-xs text-brand-navy-700">{{ formatTime(step.at) }} WIB</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="space-y-3">
                <a
                    v-if="midwifePhone && alert.status !== 'resolved'"
                    :href="`tel:${midwifePhone}`"
                    class="btn w-full gap-2 border-none bg-brand-navy-900 text-white"
                >
                    <Icon name="phone" size="h-4 w-4" /> Hubungi Bidan Langsung
                </a>
                <a :href="route('kehamilan.faskes')" class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700">
                    Lihat Rute Faskes Terdekat
                </a>
                <a :href="route('kehamilan.beranda')" class="btn w-full border-none bg-brand-navy-900 text-white">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</template>
