<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    sessionType: { type: String, required: true },
});

const starting = ref(false);

function start() {
    starting.value = true;
    router.post(route('skrining.mulai'), { session_type: props.sessionType }, {
        onFinish: () => (starting.value = false),
    });
}
</script>

<template>
    <Head title="Mulai Skrining" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TopAppBar />

        <div class="mx-auto flex w-full max-w-sm flex-1 flex-col items-center justify-center px-6 py-10">
            <div class="w-full rounded-xl bg-white p-8 text-center shadow-sm">
                <img src="/assets/images/mascot/pose-19-memperkenalkan-diri.png" alt="" class="mx-auto mb-6 h-24 w-24 object-contain" />
                <h1 class="mb-2 text-xl font-bold text-brand-navy-900">Mulai Skrining</h1>
                <p class="mb-6 text-sm text-brand-navy-700">
                    Skrining ini terdiri dari beberapa pertanyaan Ya/Tidak untuk memantau kesehatan Bunda dan janin.
                </p>
                <button
                    type="button"
                    :disabled="starting"
                    class="btn w-full border-none bg-brand-navy-900 text-white"
                    @click="start"
                >
                    {{ starting ? 'Memulai...' : 'Mulai' }} <span v-if="!starting" aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </div>
    </div>
</template>
