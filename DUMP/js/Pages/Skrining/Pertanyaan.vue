<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import EmergencyButton from '@/Components/Shared/EmergencyButton.vue';
import Icon from '@/Components/Shared/Icon.vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    session: { type: Object, required: true },
    question: { type: Object, required: true },
    progress: { type: Object, required: true },
    ttsEnabled: { type: Boolean, required: true },
    hasPreviousAnswer: { type: Boolean, required: true },
    currentAnswer: { type: String, default: null },
});

const CATEGORY_LABELS = {
    perdarahan: 'Perdarahan',
    preeklamsia: 'Preeklamsia',
    infeksi: 'Infeksi',
    gerakan_janin: 'Gerakan Janin',
    nyeri_perut: 'Nyeri Perut',
    kejang: 'Kejang',
    nifas_lain: 'Nifas Lainnya',
};

const progressPercent = computed(() => {
    if (props.progress.total === 0) return 0;
    return Math.round((props.progress.answered / props.progress.total) * 100);
});

const selected = ref(props.currentAnswer);
const showExitConfirm = ref(false);

// Flows.md §25: TTS gagal/tidak didukung -> ikon sengaja disembunyikan, tidak memblokir alur.
const ttsSupported = typeof window !== 'undefined' && 'speechSynthesis' in window;
const isSpeaking = ref(false);
const usedTts = ref(false);
const showDisabledHint = ref(false);

// Ganti pertanyaan -> reset status audio & jejak pemakaian TTS pertanyaan sebelumnya.
watch(() => props.question.id, () => {
    window.speechSynthesis?.cancel();
    isSpeaking.value = false;
    usedTts.value = false;
    showDisabledHint.value = false;
    selected.value = props.currentAnswer;
});

onBeforeUnmount(() => window.speechSynthesis?.cancel());

function toggleSpeech() {
    if (!props.ttsEnabled) {
        // Flows.md §29.4.1: ikon tetap tampil, tapi tidak memutar audio saat dimatikan di Pengaturan.
        showDisabledHint.value = true;
        return;
    }

    if (isSpeaking.value) {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
        return;
    }

    const utterance = new SpeechSynthesisUtterance(props.question.question_text);
    utterance.lang = 'id-ID';
    utterance.onend = () => (isSpeaking.value = false);
    utterance.onerror = () => (isSpeaking.value = false);

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
    isSpeaking.value = true;
    usedTts.value = true;
}

function answer(value) {
    selected.value = value;
    router.post(route('skrining.jawab', props.session.id), {
        screening_question_id: props.question.id,
        answer: value,
        used_text_to_speech: usedTts.value,
    });
}

function skip() {
    router.post(route('skrining.lewati', props.session.id), {
        screening_question_id: props.question.id,
    });
}

// Flows.md §4.2.3a: pertanyaan pertama sesi (belum ada jawaban sebelumnya) -> dialog konfirmasi keluar,
// bukan navigasi ke pertanyaan sebelumnya karena tidak ada yang bisa dituju.
function goBack() {
    if (props.hasPreviousAnswer) {
        router.get(route('skrining.kembali', props.session.id));
        return;
    }

    showExitConfirm.value = true;
}

function confirmExit() {
    router.visit(route('kehamilan.beranda'));
}
</script>

<template>
    <Head title="Skrining" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TaskHeader title="Skrining SADAR" @back="goBack" @close="showExitConfirm = true" />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 py-6">
            <div class="mb-1 flex items-center justify-between text-xs text-brand-navy-700">
                <span>Pertanyaan {{ progress.answered + 1 }} dari {{ progress.total }}</span>
                <span>{{ progressPercent }}%</span>
            </div>
            <div class="mb-6 h-1.5 w-full rounded-full bg-brand-pink-200">
                <div class="h-1.5 rounded-full bg-brand-navy-900 transition-all" :style="{ width: progressPercent + '%' }" />
            </div>

            <div class="mb-3 flex items-center justify-between">
                <span class="rounded-full bg-brand-pink-200 px-3 py-1 text-xs font-semibold text-brand-navy-900">
                    {{ CATEGORY_LABELS[question.category] ?? question.category }}
                </span>
                <button
                    v-if="ttsSupported"
                    type="button"
                    aria-label="Dengarkan pertanyaan"
                    class="btn btn-circle btn-sm cursor-pointer border-none bg-brand-navy-100 text-brand-navy-900"
                    @click="toggleSpeech"
                >
                    <Icon :name="isSpeaking ? 'stop' : 'speaker'" size="h-4 w-4" />
                </button>
            </div>
            <p v-if="showDisabledHint" class="mb-2 text-xs text-brand-navy-700">Suara dimatikan di Pengaturan</p>

            <div class="mb-6 flex justify-center">
                <div class="avatar">
                    <div class="w-20 rounded-full border-2 border-white bg-white shadow">
                        <img src="/assets/images/mascot/pose-03-mendengarkan.png" alt="" class="h-full w-full object-cover" />
                    </div>
                </div>
            </div>

            <h1 class="mb-10 text-center text-2xl leading-snug font-bold text-brand-navy-900">
                {{ question.question_text }}
            </h1>

            <div class="space-y-3">
                <button
                    type="button"
                    class="btn h-16 w-full gap-2 border-none text-lg"
                    :class="selected === 'ya' ? 'bg-risk-high text-white' : 'bg-risk-high-bg text-risk-high'"
                    @click="answer('ya')"
                >
                    <Icon name="alert" size="h-5 w-5" /> Ya, Saya Mengalami
                </button>
                <button
                    type="button"
                    class="btn h-16 w-full gap-2 border-none text-lg"
                    :class="selected === 'tidak' ? 'bg-brand-navy-900 text-white' : 'btn-outline border-brand-navy-100 text-brand-navy-700'"
                    @click="answer('tidak')"
                >
                    <Icon name="check" size="h-5 w-5" /> Tidak Ada / Normal
                </button>
            </div>

            <button type="button" class="mt-6 block w-full text-center text-sm text-brand-navy-700 underline" @click="skip">
                Lewati pertanyaan ini
            </button>

            <button
                v-if="hasPreviousAnswer"
                type="button"
                class="mt-3 block w-full text-center text-sm text-brand-navy-700"
                @click="goBack"
            >
                &larr; Pertanyaan Sebelumnya
            </button>
        </div>

        <div v-if="showExitConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center">
                <p class="mb-2 text-lg font-semibold text-neutral-900">Keluar dari skrining?</p>
                <p class="mb-6 text-sm text-neutral-600">
                    Jawaban yang sudah diisi akan tersimpan, bisa dilanjutkan nanti.
                </p>
                <div class="space-y-3">
                    <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="confirmExit">
                        Ya, Keluar
                    </button>
                    <button type="button" class="btn btn-ghost w-full" @click="showExitConfirm = false">Batal</button>
                </div>
            </div>
        </div>

        <EmergencyButton />
    </div>
</template>
