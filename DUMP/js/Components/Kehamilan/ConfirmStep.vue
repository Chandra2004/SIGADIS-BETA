<script setup>
defineProps({
    form: { type: Object, required: true },
});

const emit = defineEmits(['submit', 'back']);

const yaTidak = (v) => (v ? 'Ya' : 'Tidak');

const otherConditionLabel = {
    heart_disease: 'Penyakit Jantung',
    asthma: 'Asma',
    kidney_disorder: 'Gangguan Ginjal',
    severe_anemia: 'Anemia Berat',
};
</script>

<template>
    <div class="space-y-4">
        <h1 class="mb-2 text-xl font-bold text-brand-navy-900">Konfirmasi Data</h1>

        <dl class="space-y-2 rounded-lg border border-brand-navy-100 bg-white p-4 text-sm">
            <div class="flex justify-between"><dt class="text-brand-navy-700">Nama Ibu Hamil</dt><dd class="font-medium text-brand-navy-900">{{ form.mother_name }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Usia Kehamilan</dt><dd class="font-medium text-brand-navy-900">{{ form.gestational_age_weeks_at_registration }} minggu</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Wilayah</dt><dd class="font-medium text-brand-navy-900">{{ form.region_code }}</dd></div>
            <div v-if="form.emergency_contact_name" class="flex justify-between"><dt class="text-brand-navy-700">Kontak Darurat</dt><dd class="font-medium text-brand-navy-900">{{ form.emergency_contact_name }} ({{ form.emergency_contact_phone }})</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Kehamilan Kembar</dt><dd class="font-medium text-brand-navy-900">{{ yaTidak(form.is_twin_pregnancy) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Riwayat Caesar</dt><dd class="font-medium text-brand-navy-900">{{ yaTidak(form.has_prior_cesarean) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Diabetes Gestasional</dt><dd class="font-medium text-brand-navy-900">{{ yaTidak(form.has_gestational_diabetes) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-navy-700">Hipertensi Kronis</dt><dd class="font-medium text-brand-navy-900">{{ yaTidak(form.has_chronic_hypertension) }}</dd></div>
            <div v-if="form.other_medical_conditions?.length" class="flex justify-between">
                <dt class="text-brand-navy-700">Kondisi Lain</dt>
                <dd class="font-medium text-brand-navy-900">{{ form.other_medical_conditions.map((c) => otherConditionLabel[c] ?? c).join(', ') }}</dd>
            </div>
        </dl>

        <p v-if="form.errors && Object.keys(form.errors).length" class="text-sm text-[--color-error-form]">
            Ada data yang belum valid, mohon periksa kembali langkah sebelumnya.
        </p>

        <div class="flex gap-3 pt-2">
            <button type="button" class="btn btn-ghost flex-1 text-brand-navy-700" @click="emit('back')">Kembali</button>
            <button
                type="button"
                :disabled="form.processing"
                class="btn flex-1 border-none bg-brand-navy-900 text-white"
                @click="emit('submit')"
            >
                Daftar Sekarang
            </button>
        </div>
    </div>
</template>
