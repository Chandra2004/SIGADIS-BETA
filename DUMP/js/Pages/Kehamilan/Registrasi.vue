<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ConditionsStep from '@/Components/Kehamilan/ConditionsStep.vue';
import ConfirmStep from '@/Components/Kehamilan/ConfirmStep.vue';
import ConsentStep from '@/Components/Kehamilan/ConsentStep.vue';
import IdentityStep from '@/Components/Kehamilan/IdentityStep.vue';
import MidwifeStep from '@/Components/Kehamilan/MidwifeStep.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    consentVersion: { type: String, required: true },
});

/**
 * Flows.md §3.1 "wajib tampil sebelum input data apa pun" -- consent tetap
 * LANGKAH PERTAMA (bukan §3 ke-3 seperti urutan di mockup Figma), karena itu
 * syarat consent tertulis yang eksplisit, bukan preferensi visual.
 */
const STEPS = [
    { key: 'consent', number: 1, title: 'Persetujuan Layanan & Akses Data', subtitle: 'Privasi dan keamanan data kesehatan Ibu adalah prioritas utama kami.' },
    { key: 'identity', number: 2, title: 'Selamat Datang di SIGADIS', subtitle: 'Mari lengkapi data kehamilan Ibu untuk pemantauan yang tepat.' },
    { key: 'conditions', number: 3, title: 'Riwayat Kesehatan Ibu', subtitle: 'Informasi kondisi terdahulu membantu Bidan memetakan tingkat risiko kehamilan sejak dini.' },
    { key: 'midwife', number: 4, title: 'Penetapan Bidan Pendamping', subtitle: 'Pilih Bidan Pendamping atau biarkan sistem memilihkan Bidan Puskesmas terdekat secara otomatis.' },
    { key: 'confirm', number: 5, title: 'Konfirmasi Data', subtitle: 'Periksa kembali data Ibu sebelum menyelesaikan registrasi.' },
];

const step = ref('consent');
const currentStep = computed(() => STEPS.find((s) => s.key === step.value));

const form = useForm({
    mother_name: '',
    estimated_due_date: '',
    hpl_is_estimated: false,
    gestational_age_weeks_at_registration: null,
    is_twin_pregnancy: false,
    has_prior_cesarean: false,
    has_gestational_diabetes: false,
    has_chronic_hypertension: false,
    other_medical_conditions: [],
    medical_notes: '',
    region_code: '',
    address: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    consent_granted: false,
    consent_version: props.consentVersion,
    selected_midwife_id: null,
});

function goTo(name) {
    step.value = name;
}

function agree() {
    form.consent_granted = true;
    goTo('identity');
}

function submit() {
    form.post(route('kehamilan.registrasi.store'));
}
</script>

<template>
    <Head title="Registrasi Kehamilan" />

    <div class="min-h-screen bg-brand-pink-50">
        <TopAppBar>
            <template #actions>
                <span class="rounded-full border border-brand-navy-100 bg-white px-3 py-1 text-xs font-semibold text-brand-navy-900">
                    Langkah {{ currentStep.number }} dari {{ STEPS.length }}
                </span>
            </template>
        </TopAppBar>

        <div class="mx-auto w-full max-w-md px-6 py-6">
            <section v-if="step !== 'confirm'" class="mb-4 flex items-center gap-3 rounded-xl bg-brand-pink-200 p-4">
                <div class="flex-1">
                    <p class="font-bold text-brand-navy-900">{{ currentStep.title }}</p>
                    <p class="text-sm text-brand-navy-700">{{ currentStep.subtitle }}</p>
                </div>
                <img src="/assets/images/mascot/pose-11-menunjuk-arah.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
            </section>

            <div class="rounded-xl bg-white p-4 shadow-sm">
                <ConsentStep v-if="step === 'consent'" @agree="agree" @disagree="router.visit(route('welcome'))" />

                <IdentityStep v-else-if="step === 'identity'" :form="form" @next="goTo('conditions')" @back="goTo('consent')" />

                <ConditionsStep v-else-if="step === 'conditions'" :form="form" @next="goTo('midwife')" @back="goTo('identity')" />

                <MidwifeStep v-else-if="step === 'midwife'" :form="form" @next="goTo('confirm')" @back="goTo('conditions')" />

                <ConfirmStep v-else-if="step === 'confirm'" :form="form" @submit="submit" @back="goTo('midwife')" />
            </div>
        </div>
    </div>
</template>
