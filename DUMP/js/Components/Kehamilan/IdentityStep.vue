<script setup>
const props = defineProps({
    form: { type: Object, required: true },
});

const emit = defineEmits(['next', 'back']);

function canContinue() {
    if (!props.form.mother_name || !props.form.region_code) return false;
    if (props.form.hpl_is_estimated) return !!props.form.gestational_age_weeks_at_registration;
    return !!props.form.estimated_due_date;
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="mb-2 text-xl font-bold text-brand-navy-900">Identitas & Usia Kehamilan</h1>

        <div>
            <label class="mb-1 block text-sm text-brand-navy-700">Nama Ibu Hamil</label>
            <input v-model="form.mother_name" type="text" class="input input-bordered w-full border-brand-navy-100 bg-white" />
        </div>

        <label class="flex items-center gap-2 text-sm text-brand-navy-700">
            <input v-model="form.hpl_is_estimated" type="checkbox" class="checkbox checkbox-sm" />
            Saya tidak tahu/lupa Hari Perkiraan Lahir (HPL)
        </label>

        <div v-if="!form.hpl_is_estimated">
            <label class="mb-1 block text-sm text-brand-navy-700">Hari Perkiraan Lahir (HPL)</label>
            <input v-model="form.estimated_due_date" type="date" class="input input-bordered w-full border-brand-navy-100 bg-white" />
        </div>

        <div>
            <label class="mb-1 block text-sm text-brand-navy-700">Usia Kehamilan Saat Ini (minggu)</label>
            <input
                v-model.number="form.gestational_age_weeks_at_registration"
                type="number"
                min="0"
                max="45"
                class="input input-bordered w-full border-brand-navy-100 bg-white"
            />
        </div>

        <div>
            <label class="mb-1 block text-sm text-brand-navy-700">Wilayah Domisili (kode wilayah)</label>
            <input
                v-model="form.region_code"
                type="text"
                placeholder="mis. 33.08.05.2009"
                class="input input-bordered w-full border-brand-navy-100 bg-white"
            />
        </div>

        <div class="rounded-lg border border-brand-navy-100 bg-brand-pink-50 p-3">
            <p class="mb-3 text-xs text-brand-navy-700">
                Bagian ini opsional, membantu bidan/kader menemukan Ibu lebih cepat saat kondisi darurat. Boleh
                dilewati kalau belum ingin diisi.
            </p>
            <label class="mb-1 block text-sm text-brand-navy-700">Alamat Lengkap (opsional)</label>
            <textarea
                v-model="form.address"
                rows="2"
                placeholder="Jl., RT/RW, Desa/Kelurahan..."
                class="textarea textarea-bordered mb-3 w-full border-brand-navy-100 bg-white"
            ></textarea>
            <label class="mb-1 block text-sm text-brand-navy-700">Nama Kontak Darurat (opsional)</label>
            <input
                v-model="form.emergency_contact_name"
                type="text"
                placeholder="mis. Suami/Orang Tua"
                class="input input-bordered mb-3 w-full border-brand-navy-100 bg-white"
            />
            <label class="mb-1 block text-sm text-brand-navy-700">Nomor HP Kontak Darurat (opsional)</label>
            <input
                v-model="form.emergency_contact_phone"
                type="tel"
                placeholder="08xxxxxxxxxx"
                class="input input-bordered w-full border-brand-navy-100 bg-white"
            />
        </div>

        <div class="flex gap-3 pt-2">
            <button type="button" class="btn btn-ghost flex-1 text-brand-navy-700" @click="emit('back')">Kembali</button>
            <button
                type="button"
                :disabled="!canContinue()"
                class="btn flex-1 border-none bg-brand-navy-900 text-white disabled:bg-brand-navy-100 disabled:text-brand-navy-700"
                @click="emit('next')"
            >
                Lanjut
            </button>
        </div>
    </div>
</template>
