<script setup>
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    form: { type: Object, required: true },
});

const emit = defineEmits(['next', 'back']);

const midwives = ref([]);
const loading = ref(false);
const showAllChoices = ref(false);
const search = ref('');

const filteredMidwives = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return midwives.value;
    return midwives.value.filter((m) => m.full_name.toLowerCase().includes(query));
});

async function loadCandidates() {
    loading.value = true;
    try {
        const { data } = await axios.get(route('kehamilan.midwife-candidates'), {
            params: { region_code: props.form.region_code },
        });
        midwives.value = data.midwives;
    } finally {
        loading.value = false;
    }
}

onMounted(loadCandidates);
watch(() => props.form.region_code, loadCandidates);

function choose(id) {
    props.form.selected_midwife_id = id;
}

function useRecommended() {
    choose(midwives.value[0].id);
    emit('next');
}
</script>

<template>
    <div class="space-y-4">
        <h1 class="mb-2 text-xl font-bold text-brand-navy-900">Bidan Pendamping</h1>

        <div v-if="loading" class="text-sm text-brand-navy-700">Mencari bidan di wilayah Ibu...</div>

        <div v-else-if="midwives.length === 0" class="rounded-lg border border-brand-navy-100 bg-white p-4 text-sm text-brand-navy-700">
            Belum ada bidan terdaftar di wilayah Ibu. Registrasi tetap bisa dilanjutkan — tim kami akan
            menindaklanjuti pemasangan bidan pendamping.
        </div>

        <template v-else>
            <!-- Flows.md §3.4.1: 1 kandidat utama (default zonasi) ditampilkan dulu, bukan daftar penuh. -->
            <div v-if="!showAllChoices" class="space-y-3">
                <div class="rounded-lg border-2 border-brand-navy-900 bg-brand-pink-50 p-4">
                    <span class="mb-2 inline-block rounded-full bg-risk-low-bg px-2 py-0.5 text-xs font-semibold text-risk-low">
                        <Icon name="check" size="h-3 w-3" class="inline" /> Rekomendasi Otomatis
                    </span>
                    <p class="font-bold text-brand-navy-900">{{ midwives[0].full_name }}</p>
                    <p class="text-xs text-brand-navy-700">Wilayah {{ midwives[0].region_code }}</p>
                </div>
                <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="useRecommended">
                    Gunakan Bidan Ini
                </button>

                <div v-if="midwives.length > 1" class="flex items-center gap-2 text-xs text-brand-navy-700">
                    <span class="h-px flex-1 bg-brand-navy-100" /> ATAU <span class="h-px flex-1 bg-brand-navy-100" />
                </div>
                <button
                    v-if="midwives.length > 1"
                    type="button"
                    class="btn btn-outline w-full border-brand-navy-100 text-brand-navy-700"
                    @click="showAllChoices = true"
                >
                    Pilih Bidan Lain
                </button>
            </div>

            <!-- Flows.md §3.4.2: opsi "Lihat bidan lain di wilayah ini". -->
            <div v-else class="space-y-2">
                <div class="relative">
                    <Icon name="user" size="h-4 w-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-neutral-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama bidan..."
                        class="input input-bordered w-full pl-9"
                    />
                </div>
                <button
                    v-for="m in filteredMidwives"
                    :key="m.id"
                    type="button"
                    class="btn w-full justify-start"
                    :class="form.selected_midwife_id === m.id ? 'border-none bg-brand-navy-900 text-white' : 'btn-outline border-brand-navy-100 text-brand-navy-700'"
                    @click="choose(m.id)"
                >
                    {{ m.full_name }}
                </button>
                <p v-if="filteredMidwives.length === 0" class="text-sm text-brand-navy-700">Tidak ada bidan yang cocok dengan pencarian.</p>
            </div>
        </template>

        <div class="flex gap-3 pt-2">
            <button type="button" class="btn btn-ghost flex-1 text-brand-navy-700" @click="emit('back')">Kembali</button>
            <button
                v-if="midwives.length === 0 || showAllChoices"
                type="button"
                :disabled="midwives.length > 0 && !form.selected_midwife_id"
                class="btn flex-1 border-none bg-brand-navy-900 text-white disabled:bg-brand-navy-100 disabled:text-brand-navy-700"
                @click="emit('next')"
            >
                Lanjut
            </button>
        </div>
    </div>
</template>
