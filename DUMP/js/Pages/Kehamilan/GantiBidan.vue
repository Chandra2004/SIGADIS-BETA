<script setup>
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref } from 'vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    regionCode: { type: String, required: true },
    currentMidwifeId: { type: Number, default: null },
});

const midwives = ref([]);
const loading = ref(true);
const selectedId = ref(props.currentMidwifeId);
const submitting = ref(false);

onMounted(async () => {
    const { data } = await axios.get(route('kehamilan.midwife-candidates'), {
        params: { region_code: props.regionCode },
    });
    midwives.value = data.midwives;
    loading.value = false;
});

function submit() {
    if (!selectedId.value || submitting.value) {
        return;
    }

    submitting.value = true;
    router.post(
        route('kehamilan.ganti-bidan.store'),
        { midwife_id: selectedId.value },
        { onFinish: () => (submitting.value = false) },
    );
}
</script>

<template>
    <Head title="Ganti Bidan Pendamping" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="Ganti Bidan Pendamping" :back-href="route('kehamilan.beranda')" :show-close="false" />

        <div class="mx-auto w-full max-w-md flex-1 px-6 py-6">
            <p class="mb-6 text-sm text-brand-navy-700">Pilih bidan pendamping baru untuk wilayah Ibu.</p>

            <div v-if="loading" class="text-sm text-brand-navy-700">Mencari bidan di wilayah Ibu...</div>

            <div v-else-if="midwives.length === 0" class="rounded-xl bg-white p-4 text-sm text-brand-navy-700 shadow-sm">
                Belum ada bidan lain terdaftar di wilayah Ibu.
            </div>

            <div v-else class="space-y-2">
                <button
                    v-for="m in midwives"
                    :key="m.id"
                    type="button"
                    class="btn w-full justify-start"
                    :class="selectedId === m.id ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline border-brand-navy-100 text-brand-navy-700'"
                    @click="selectedId = m.id"
                >
                    {{ m.full_name }}
                </button>
            </div>

            <div class="flex gap-3 pt-6">
                <a :href="route('kehamilan.beranda')" class="btn btn-ghost flex-1 text-brand-navy-700">Batal</a>
                <button
                    type="button"
                    class="btn flex-1 border-none bg-brand-navy-900 text-white"
                    :disabled="!selectedId || selectedId === currentMidwifeId || submitting"
                    @click="submit"
                >
                    Simpan
                </button>
            </div>
        </div>
    </div>
</template>
