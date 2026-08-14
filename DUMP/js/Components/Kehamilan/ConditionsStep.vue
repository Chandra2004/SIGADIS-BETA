<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    form: { type: Object, required: true },
});

const emit = defineEmits(['next', 'back']);

if (!Array.isArray(props.form.other_medical_conditions)) {
    props.form.other_medical_conditions = [];
}

/**
 * 4 pertama pakai kolom booleannya sendiri (Flows.md §3.3.1, dipakai
 * RiskAssessmentEngine). 4 sisanya disimpan di other_medical_conditions
 * (json) -- data konteks tambahan buat bidan, belum memengaruhi skor.
 */
const conditions = [
    { key: 'is_twin_pregnancy', label: 'Kehamilan Kembar', icon: 'user', type: 'field' },
    { key: 'has_chronic_hypertension', label: 'Hipertensi', icon: 'pulse', type: 'field' },
    { key: 'heart_disease', label: 'Penyakit Jantung', icon: 'heart', type: 'other' },
    { key: 'has_gestational_diabetes', label: 'Diabetes', icon: 'droplet', type: 'field' },
    { key: 'asthma', label: 'Asma', icon: 'wind', type: 'other' },
    { key: 'kidney_disorder', label: 'Gangguan Ginjal', icon: 'shield', type: 'other' },
    { key: 'severe_anemia', label: 'Anemia Berat', icon: 'droplet', type: 'other' },
    { key: 'has_prior_cesarean', label: 'Pernah Operasi Caesar (SC)', icon: 'document', type: 'field' },
];

function isChecked(condition) {
    return condition.type === 'field' ? !!props.form[condition.key] : props.form.other_medical_conditions.includes(condition.key);
}

function toggle(condition) {
    if (condition.type === 'field') {
        props.form[condition.key] = !props.form[condition.key];
        return;
    }

    const list = props.form.other_medical_conditions;
    const idx = list.indexOf(condition.key);
    if (idx === -1) list.push(condition.key);
    else list.splice(idx, 1);
}

const noneSelected = computed(() => conditions.every((c) => !isChecked(c)));

function clearAll() {
    conditions.forEach((c) => {
        if (c.type === 'field') props.form[c.key] = false;
    });
    props.form.other_medical_conditions = [];
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="mb-1 text-xl font-bold text-brand-navy-900">Pilih Penyakit / Kondisi yang Pernah/Sedang Dialami</h1>
        <p class="mb-2 text-sm text-brand-navy-700">
            Jawaban ini membantu sistem &amp; bidan menyesuaikan kepekaan pemantauan Ibu berikutnya.
        </p>

        <div class="space-y-2">
            <button
                v-for="c in conditions"
                :key="c.key"
                type="button"
                class="flex w-full cursor-pointer items-center gap-3 rounded-lg border p-3 text-left transition-colors"
                :class="isChecked(c) ? 'border-brand-navy-900 bg-brand-pink-50' : 'border-brand-navy-100 bg-white hover:bg-neutral-50'"
                @click="toggle(c)"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="isChecked(c) ? 'bg-brand-navy-900 text-white' : 'bg-brand-navy-100 text-brand-navy-700'">
                    <Icon :name="c.icon" size="h-5 w-5" />
                </span>
                <span class="flex-1 text-sm font-medium text-brand-navy-900">{{ c.label }}</span>
                <span
                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                    :class="isChecked(c) ? 'border-brand-navy-900 bg-brand-navy-900 text-white' : 'border-neutral-300'"
                >
                    <Icon v-if="isChecked(c)" name="check" size="h-3 w-3" />
                </span>
            </button>

            <button
                type="button"
                class="flex w-full cursor-pointer items-center gap-3 rounded-lg border p-3 text-left transition-colors"
                :class="noneSelected ? 'border-brand-navy-900 bg-brand-pink-50' : 'border-brand-navy-100 bg-white hover:bg-neutral-50'"
                @click="clearAll"
            >
                <span
                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                    :class="noneSelected ? 'border-brand-navy-900 bg-brand-navy-900 text-white' : 'border-neutral-300'"
                >
                    <Icon v-if="noneSelected" name="check" size="h-3 w-3" />
                </span>
                <span class="text-sm font-medium text-brand-navy-900">Tidak Ada Riwayat Penyakit Terdahulu</span>
            </button>
        </div>

        <div>
            <label class="mb-1 block text-sm text-brand-navy-700">Catatan Medis Lainnya (Opsional)</label>
            <textarea
                v-model="form.medical_notes"
                rows="2"
                placeholder="Tambahkan detail jika ada kondisi lain yang perlu diperhatikan..."
                class="textarea textarea-bordered w-full border-brand-navy-100 bg-white"
            ></textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="button" class="btn btn-ghost flex-1 text-brand-navy-700" @click="emit('back')">Kembali</button>
            <button type="button" class="btn flex-1 border-none bg-brand-navy-900 text-white" @click="emit('next')">
                Lanjut
            </button>
        </div>
    </div>
</template>
