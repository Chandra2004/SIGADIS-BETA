<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AppShell from '@/Components/Desktop/AppShell.vue';

const props = defineProps({
    alert: { type: Object, required: true },
});

function logout() {
    router.post(route('auth.staff.logout'));
}

const page = usePage();

// Figma "Otomatis kembali dalam 01:45" -- jendela 2 menit buat batalkan salah pencet.
const secondsLeft = ref(null);
let timer = null;

function tick() {
    if (!props.alert.cancel_handling_expires_at) {
        secondsLeft.value = null;
        return;
    }

    const diff = Math.round((new Date(props.alert.cancel_handling_expires_at).getTime() - Date.now()) / 1000);
    secondsLeft.value = Math.max(0, diff);

    if (secondsLeft.value === 0) {
        clearInterval(timer);
    }
}

onMounted(() => {
    tick();
    timer = setInterval(tick, 1000);
});

onBeforeUnmount(() => clearInterval(timer));

const formattedCountdown = computed(() => {
    if (secondsLeft.value === null) return null;
    const minutes = String(Math.floor(secondsLeft.value / 60)).padStart(2, '0');
    const seconds = String(secondsLeft.value % 60).padStart(2, '0');
    return `${minutes}:${seconds}`;
});

function acknowledge() {
    router.post(route('bidan.alerts.acknowledge', props.alert.id));
}

function resolve() {
    router.post(route('bidan.alerts.resolve', props.alert.id));
}

function cancelHandling() {
    router.post(route('bidan.alerts.cancel-handling', props.alert.id));
}
</script>

<template>
    <Head title="Detail Peringatan Darurat" />

    <AppShell @logout="logout">
        <div class="mx-auto max-w-xl space-y-6 px-6 py-8">
            <a :href="route('bidan.dashboard')" class="text-sm text-brand-navy-700">&larr; Kembali ke Dashboard</a>

            <p v-if="page.props.flash?.success" class="rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">{{ page.props.flash.success }}</p>
            <p v-if="page.props.flash?.info" class="rounded-lg bg-neutral-200 p-3 text-sm text-neutral-700">{{ page.props.flash.info }}</p>

            <div class="rounded-xl border-2 border-emergency-alert bg-white p-6">
                <p class="mb-1 text-2xl font-bold text-neutral-900">{{ alert.pregnancy.mother_name }}</p>
                <p class="mb-4 text-sm text-neutral-500">{{ alert.pregnancy.gestational_age_weeks }} minggu kehamilan</p>

                <div v-if="alert.pregnancy.address || alert.pregnancy.emergency_contact_name" class="mb-4 grid grid-cols-2 gap-3 rounded-lg bg-neutral-50 p-3 text-sm">
                    <div v-if="alert.pregnancy.address" class="col-span-2">
                        <p class="text-xs text-neutral-500 uppercase">Alamat</p>
                        <p class="text-neutral-800">{{ alert.pregnancy.address }}</p>
                    </div>
                    <div v-if="alert.pregnancy.emergency_contact_name">
                        <p class="text-xs text-neutral-500 uppercase">Kontak Darurat</p>
                        <p class="text-neutral-800">{{ alert.pregnancy.emergency_contact_name }}</p>
                    </div>
                    <div v-if="alert.pregnancy.emergency_contact_phone">
                        <p class="text-xs text-neutral-500 uppercase">No. HP</p>
                        <a :href="`tel:${alert.pregnancy.emergency_contact_phone}`" class="text-brand-navy-900 underline">{{ alert.pregnancy.emergency_contact_phone }}</a>
                    </div>
                </div>

                <div class="mb-4 flex items-center gap-2">
                    <span class="rounded-full bg-risk-high-bg px-3 py-1 text-xs font-semibold text-risk-high capitalize">
                        Risiko {{ alert.risk_level }}
                    </span>
                    <span class="text-xs text-neutral-500">{{ new Date(alert.triggered_at).toLocaleString('id-ID') }}</span>
                </div>

                <p v-if="alert.escalated_to_kader_at" class="mb-4 rounded-lg bg-neutral-100 p-2 text-xs text-neutral-600">
                    Belum ditangani tepat waktu, dieskalasi ke kader cadangan wilayah pada
                    {{ new Date(alert.escalated_to_kader_at).toLocaleString('id-ID') }}.
                </p>

                <p class="mb-2 text-sm font-semibold text-neutral-700">Gejala pemicu:</p>
                <ul class="mb-4 list-disc pl-5 text-sm text-neutral-700">
                    <li v-for="symptom in alert.triggered_symptoms" :key="symptom">{{ symptom }}</li>
                </ul>

                <p class="mb-6 text-sm text-neutral-700">{{ alert.recommendation_text }}</p>

                <div v-if="alert.status === 'pending' || alert.status === 'delivered'">
                    <button type="button" class="btn w-full border-none bg-emergency-alert text-white" @click="acknowledge">
                        Terima / Tangani
                    </button>
                </div>
                <div v-else-if="alert.status === 'being_handled'" class="space-y-3">
                    <p class="text-sm font-medium text-neutral-700">Sedang ditangani oleh {{ alert.handled_by }}</p>
                    <div class="flex gap-3">
                        <a :href="route('bidan.referrals.create', alert.id)" class="btn btn-outline flex-1">Proses Rujukan</a>
                        <button type="button" class="btn flex-1 border-none bg-brand-navy-900 text-white" @click="resolve">
                            Tandai Selesai
                        </button>
                    </div>
                    <div v-if="alert.can_cancel_handling && secondsLeft !== 0" class="text-center">
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm w-full text-neutral-500"
                            @click="cancelHandling"
                        >
                            Salah pencet? Batalkan, kembalikan ke antrean
                        </button>
                        <p v-if="formattedCountdown" class="mt-1 text-xs text-neutral-400">
                            Otomatis tidak bisa dibatalkan lagi dalam {{ formattedCountdown }}
                        </p>
                    </div>
                </div>
                <p v-else class="text-sm font-medium text-risk-low">Penanganan selesai.</p>
            </div>

            <a :href="route('bidan.alerts.history', alert.id)" class="block text-center text-sm text-brand-navy-700 underline">
                Lihat Riwayat Pasien
            </a>
        </div>
    </AppShell>
</template>
