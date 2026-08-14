<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppShell from '@/Components/Desktop/AppShell.vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    alertId: { type: Number, required: true },
    motherName: { type: String, required: true },
    facilities: { type: Array, required: true },
});

function logout() {
    router.post(route('auth.staff.logout'));
}

const search = ref('');
const filterIcu = ref(false);
const filterNicu = ref(false);

function applyFilters() {
    router.get(route('bidan.referrals.create', props.alertId), {
        search: search.value || undefined,
        has_icu: filterIcu.value ? 1 : undefined,
        has_nicu: filterNicu.value ? 1 : undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const form = useForm({
    facility_id: null,
    notes: '',
});

const selectedFacility = () => props.facilities.find((f) => f.id === form.facility_id);

function submit() {
    form.post(route('bidan.referrals.store', props.alertId));
}

const ambulanceLabel = {
    siaga: 'Ambulans Siaga',
    dalam_perjalanan: 'Ambulans Dalam Perjalanan',
    tidak_tersedia: 'Ambulans Tidak Tersedia',
};
</script>

<template>
    <Head title="Proses Rujukan" />

    <AppShell @logout="logout">
        <div class="mx-auto max-w-3xl px-6 py-8">
            <h1 class="mb-4 text-xl font-bold text-brand-navy-900">Pilih Fasilitas Kesehatan Rujukan untuk {{ motherName }}</h1>

            <div class="grid grid-cols-1 gap-6 rounded-xl border border-neutral-200 bg-white p-4 md:grid-cols-2">
                <div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari Rumah Sakit atau Puskesmas..."
                        class="input input-bordered mb-2 w-full"
                        @keyup.enter="applyFilters"
                    />
                    <div class="mb-3 flex gap-2">
                        <button
                            type="button"
                            class="btn btn-sm"
                            :class="filterIcu ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline'"
                            @click="filterIcu = !filterIcu; applyFilters()"
                        >
                            Ada ICU
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm"
                            :class="filterNicu ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline'"
                            @click="filterNicu = !filterNicu; applyFilters()"
                        >
                            Ada NICU
                        </button>
                    </div>

                    <div class="max-h-96 space-y-2 overflow-y-auto">
                        <button
                            v-for="f in facilities"
                            :key="f.id"
                            type="button"
                            class="w-full rounded-lg border p-3 text-left"
                            :class="form.facility_id === f.id ? 'border-2 border-brand-navy-900 bg-brand-pink-50' : 'border-neutral-200'"
                            @click="form.facility_id = f.id"
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <p class="font-semibold text-neutral-900">{{ f.name }}</p>
                                <input type="radio" :checked="form.facility_id === f.id" readonly class="radio radio-sm" />
                            </div>
                            <p class="mb-2 text-xs text-neutral-500">
                                <span v-if="f.distance_km !== null">{{ f.distance_km }} km &middot; </span>
                                <span v-if="f.hospital_class">Tipe {{ f.hospital_class }}</span>
                            </p>
                            <div class="flex flex-wrap gap-1 text-xs">
                                <span v-if="f.has_icu" class="rounded-full bg-risk-low-bg px-2 py-0.5 text-risk-low">ICU Tersedia</span>
                                <span v-if="f.has_nicu" class="rounded-full bg-risk-low-bg px-2 py-0.5 text-risk-low">
                                    NICU {{ f.nicu_bed_count ? `(${f.nicu_bed_count} Bed)` : 'Tersedia' }}
                                </span>
                                <span v-if="f.ambulance_status" class="flex items-center gap-1 rounded-full bg-neutral-100 px-2 py-0.5 text-neutral-700">
                                    <Icon name="truck" size="h-3.5 w-3.5" /> {{ ambulanceLabel[f.ambulance_status] }}
                                </span>
                            </div>
                        </button>
                        <p v-if="facilities.length === 0" class="text-sm text-neutral-500">Belum ada data faskes untuk wilayah ini.</p>
                    </div>
                </div>

                <div>
                    <div v-if="selectedFacility()" class="mb-4 rounded-lg border border-brand-navy-100 bg-brand-pink-50 p-3">
                        <p class="mb-1 text-xs font-semibold text-brand-navy-700 uppercase">Faskes Terpilih</p>
                        <p class="font-semibold text-brand-navy-900">{{ selectedFacility().name }}</p>
                    </div>

                    <label class="mb-1 block text-sm font-medium text-neutral-700">Catatan Medis &amp; Instruksi Bidan</label>
                    <textarea
                        v-model="form.notes"
                        rows="6"
                        placeholder="Masukkan detail kondisi pasien saat ini, tindakan awal yang telah diberikan, dan kebutuhan spesifik saat kedatangan..."
                        class="textarea textarea-bordered w-full"
                    ></textarea>
                    <p class="mt-1 text-xs text-neutral-500">Informasi ini akan tercatat bersama rujukan.</p>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-3">
                <a :href="route('bidan.alerts.show', alertId)" class="btn btn-outline">Batal</a>
                <button
                    type="button"
                    :disabled="!form.facility_id || form.processing"
                    class="btn border-none bg-brand-navy-900 text-white"
                    @click="submit"
                >
                    Kirim Rujukan
                </button>
            </div>
        </div>
    </AppShell>
</template>
