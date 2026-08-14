<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import EmergencyButton from '@/Components/Shared/EmergencyButton.vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    riskAssessment: { type: Object, required: true },
    alertSent: { type: Boolean, required: true },
    triggeredSymptoms: { type: Array, default: () => [] },
});

const emergencyRef = ref(null);

const badge = {
    tinggi: { bg: 'bg-risk-high-bg', border: 'border-risk-high', text: 'text-risk-high', label: 'Risiko Tinggi', mascot: 'pose-07-sigap-mendukung' },
    sedang: { bg: 'bg-risk-medium-bg', border: 'border-risk-medium', text: 'text-risk-medium', label: 'Risiko Sedang', mascot: 'pose-06-peduli-serius' },
    rendah: { bg: 'bg-risk-low-bg', border: 'border-risk-low', text: 'text-risk-low', label: 'Risiko Rendah', mascot: 'pose-05-lega-gembira' },
};

const style = computed(() => badge[props.riskAssessment.risk_level]);
</script>

<template>
    <Head title="Hasil Skrining" />

    <div class="min-h-screen bg-brand-pink-50">
        <TaskHeader title="Hasil Skrining SADAR" :back-href="route('kehamilan.beranda')" :show-close="false" />

        <div class="mx-auto w-full max-w-sm px-6 py-6">
            <div class="mb-4 rounded-xl border p-4" :class="[style.bg, style.border]">
                <span class="mb-2 inline-block rounded-full px-3 py-1 text-xs font-bold text-white" :class="riskAssessment.risk_level === 'tinggi' ? 'bg-risk-high' : riskAssessment.risk_level === 'sedang' ? 'bg-risk-medium' : 'bg-risk-low'">
                    {{ style.label.toUpperCase() }}
                </span>
                <div class="flex items-start justify-between gap-3">
                    <p class="text-lg font-bold" :class="style.text">{{ riskAssessment.recommendation_text }}</p>
                    <img :src="`/assets/images/mascot/${style.mascot}.png`" alt="" class="h-16 w-16 shrink-0 object-contain" />
                </div>
            </div>

            <div v-if="triggeredSymptoms.length" class="mb-4 rounded-xl border border-brand-navy-100 bg-white p-4">
                <h2 class="mb-3 text-sm font-bold text-brand-navy-900">Tanda Terlaporkan</h2>
                <div class="space-y-2">
                    <div v-for="(symptom, i) in triggeredSymptoms" :key="i" class="rounded-lg bg-brand-pink-50 px-3 py-2 text-sm text-brand-navy-900">
                        {{ symptom }}
                    </div>
                </div>
            </div>

            <p v-if="alertSent" class="mb-4 rounded-lg bg-emergency-pending-bg p-3 text-sm font-medium text-emergency-alert">
                Bidan dan kader Ibu telah diberi tahu secara otomatis.
            </p>

            <p v-if="riskAssessment.is_data_incomplete" class="mb-4 rounded-lg bg-white p-3 text-sm text-[--color-accent-amber]">
                Beberapa pertanyaan penting belum terjawab — sebaiknya Ibu segera konsultasi ke bidan meski hasil belum lengkap.
            </p>

            <p class="mb-6 rounded-lg bg-brand-navy-100 p-3 text-xs text-brand-navy-900">
                Hasil ini adalah alat bantu deteksi dini, bukan diagnosis medis resmi. Untuk kepastian, periksakan diri ke
                tenaga kesehatan.
            </p>

            <div class="space-y-3">
                <template v-if="riskAssessment.risk_level === 'tinggi'">
                    <button type="button" class="btn w-full border-none bg-emergency-alert text-white" @click="emergencyRef?.open()">
                        Hubungi Bidan / Kirim SOS Darurat &rarr;
                    </button>
                    <a :href="route('kehamilan.faskes')" class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700">
                        Lihat Rute Faskes Terdekat
                    </a>
                </template>
                <template v-else-if="riskAssessment.risk_level === 'sedang'">
                    <a :href="route('kehamilan.faskes')" class="btn w-full border-none bg-brand-navy-900 text-white">
                        Info Faskes / Hubungi Bidan &rarr;
                    </a>
                    <a :href="route('kehamilan.beranda')" class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700">
                        Kembali ke Beranda
                    </a>
                </template>
                <template v-else>
                    <a :href="route('kehamilan.beranda')" class="btn w-full border-none bg-brand-navy-900 text-white">
                        Kembali ke Beranda &rarr;
                    </a>
                    <a :href="route('kehamilan.riwayat')" class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700">
                        Lihat Riwayat Skrining
                    </a>
                </template>
            </div>
        </div>

        <EmergencyButton ref="emergencyRef" />
    </div>
</template>
