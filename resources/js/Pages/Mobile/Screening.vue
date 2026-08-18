<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    motherName: {
        type: String,
        default: 'Ibu Hamil',
    },
    sessionType: {
        type: String,
        default: 'initial', // 'initial', 'periodic', 'nifas'
    },
    gestationalAgeWeeks: {
        type: Number,
        default: 24,
    },
    pregnancyStatus: {
        type: String,
        default: 'hamil',
    },
    questions: {
        type: Array,
        default: () => [],
    },
    consentRevoked: {
        type: Boolean,
        default: false,
    },
});

// Fallback bank soal jika prop questions kosong
const defaultQuestions = [
    { id: 1, code: 'bleeding_heavy', question_text: 'Apakah Ibu mengalami perdarahan banyak dari jalan lahir (lebih dari haid biasa)?', category: 'perdarahan', is_critical: true },
    { id: 2, code: 'headache_severe', question_text: 'Apakah Ibu mengalami sakit kepala hebat yang tidak hilang meski sudah istirahat?', category: 'preeklamsia', is_critical: false },
    { id: 3, code: 'vision_blurred', question_text: 'Apakah pandangan Ibu mendadak kabur, berkunang-kunang, atau ada bintik cahaya?', category: 'preeklamsia', is_critical: false },
    { id: 4, code: 'swelling_face_hands', question_text: 'Apakah wajah atau kedua tangan Ibu membengkak secara tiba-tiba?', category: 'preeklamsia', is_critical: false },
    { id: 5, code: 'fever_high', question_text: 'Apakah Ibu demam tinggi (badan terasa sangat panas menggigil)?', category: 'infeksi', is_critical: false },
    { id: 6, code: 'fetal_movement_stopped', question_text: 'Apakah gerakan bayi di kandungan sama sekali tidak terasa sejak semalam?', category: 'gerakan_janin', is_critical: true },
    { id: 7, code: 'seizure', question_text: 'Apakah Ibu pernah mengalami kejang (kaku/kelojotan tak terkendali)?', category: 'kejang', is_critical: true },
];

const questionList = computed(() => {
    return props.questions && props.questions.length > 0 ? props.questions : defaultQuestions;
});

// State Alur Skrining: 'intro', 'question', 'result'
const currentStep = ref('intro');
const currentIndex = ref(0);
const answers = ref([]);
const isSubmitting = ref(false);
const resultData = ref(null);

// State Text-to-Speech (TTS Voice Assistant)
const isSpeaking = ref(false);
const ttsSupported = ref('speechSynthesis' in window);

const currentQuestion = computed(() => {
    return questionList.value[currentIndex.value] || null;
});

const progressPercent = computed(() => {
    if (questionList.value.length === 0) return 0;
    return Math.round(((currentIndex.value + 1) / questionList.value.length) * 100);
});

// Helper Kategori Soal
const getCategoryBadge = (category) => {
    switch (category) {
        case 'perdarahan': return { label: 'Tanda Perdarahan', color: 'bg-red-100 text-red-700', icon: 'water_drop' };
        case 'preeklamsia': return { label: 'Tekanan Darah & Kepala', color: 'bg-amber-100 text-amber-800', icon: 'vital_signs' };
        case 'infeksi': return { label: 'Tanda Infeksi', color: 'bg-orange-100 text-orange-800', icon: 'thermostat' };
        case 'gerakan_janin': return { label: 'Aktivitas Janin', color: 'bg-blue-100 text-blue-800', icon: 'child_care' };
        case 'kejang': return { label: 'Tanda Kritis Kejang', color: 'bg-purple-100 text-purple-800', icon: 'bolt' };
        case 'nifas_lain': return { label: 'Kondisi Nifas', color: 'bg-pink-100 text-pink-800', icon: 'pregnant_woman' };
        default: return { label: 'Pemeriksaan Kesehatan', color: 'bg-blue-50 text-blue-700', icon: 'health_and_safety' };
    }
};

// Text-to-Speech Handler
const speakCurrentQuestion = () => {
    if (!ttsSupported.value || !currentQuestion.value) return;

    if (isSpeaking.value) {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
        return;
    }

    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(currentQuestion.value.question_text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.95; // Sedikit lebih lambat agar ramah dipahami

    utterance.onstart = () => {
        isSpeaking.value = true;
    };
    utterance.onend = () => {
        isSpeaking.value = false;
    };
    utterance.onerror = () => {
        isSpeaking.value = false;
    };

    window.speechSynthesis.speak(utterance);
};

// Start Screening
const startScreening = () => {
    currentStep.value = 'question';
    currentIndex.value = 0;
    answers.value = [];
    speakCurrentQuestion();
};

// Handle Answer YA / TIDAK / SKIP
const handleAnswer = (val) => {
    if (isSpeaking.value) {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
    }

    const q = currentQuestion.value;
    answers.value.push({
        question_id: q.id,
        code: q.code,
        answer: val,
        used_tts: isSpeaking.value,
    });

    // Early-exit jika gejala kritis dijawab "Ya"
    if (val === 'ya' && q.is_critical) {
        submitAllAnswers();
        return;
    }

    if (currentIndex.value < questionList.value.length - 1) {
        currentIndex.value++;
        setTimeout(() => {
            speakCurrentQuestion();
        }, 300);
    } else {
        submitAllAnswers();
    }
};

// Tombol Kembali / Undo
const handleBack = () => {
    if (isSpeaking.value) {
        window.speechSynthesis.cancel();
        isSpeaking.value = false;
    }

    if (currentIndex.value > 0) {
        currentIndex.value--;
        answers.value.pop();
        setTimeout(() => {
            speakCurrentQuestion();
        }, 300);
    } else {
        currentStep.value = 'intro';
    }
};

// Kirim Jawaban ke Backend Risk Engine
const submitAllAnswers = async () => {
    isSubmitting.value = true;
    try {
        const response = await axios.post(route('mobile.screening.submit'), {
            session_type: props.sessionType,
            answers: answers.value,
        });

        resultData.value = response.data;
        currentStep.value = 'result';
    } catch (e) {
        // Fallback local risk evaluation jika offline
        let level = 'rendah';
        const hasYes = answers.value.some(a => a.answer === 'ya');
        const hasCritical = answers.value.some(a => a.answer === 'ya' && questionList.value.find(q => q.code === a.code)?.is_critical);

        if (hasCritical) {
            level = 'tinggi';
        } else if (hasYes) {
            level = 'sedang';
        }

        resultData.value = {
            risk_level: level,
            recommendation: level === 'tinggi'
                ? 'Terdeteksi tanda bahaya kritis kegawatdaruratan maternal! Peringatan darurat telah disiapkan untuk Bidan & Faskes.'
                : (level === 'sedang' ? 'Terdapat beberapa gejala yang perlu perhatian. Disarankan untuk konsultasi dengan Bidan.' : 'Kondisi Ibu dan janin tampak baik.'),
            is_data_incomplete: false,
        };
        currentStep.value = 'result';
    } finally {
        isSubmitting.value = false;
    }
};

onUnmounted(() => {
    if (window.speechSynthesis) {
        window.speechSynthesis.cancel();
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-hidden select-none">
        <Head title="Skrining Mandiri — SIGADIS Mobile" />

        <!-- Top App Bar -->
        <header class="sticky top-0 z-40 bg-[#FDF3F6]/90 backdrop-blur-md border-b border-[#F3AEC0]/30 px-4 h-16 flex items-center justify-between">
            <button
                v-if="currentStep === 'question'"
                @click="handleBack"
                class="w-10 h-10 rounded-full bg-white/80 border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center active:scale-95 transition-all"
                aria-label="Kembali"
            >
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </button>
            <Link
                v-else
                :href="route('mobile.dashboard')"
                class="w-10 h-10 rounded-full bg-white/80 border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center active:scale-95 transition-all"
                aria-label="Tutup"
            >
                <span class="material-symbols-outlined text-xl">close</span>
            </Link>

            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/70 border border-[#F3AEC0]/30">
                <span class="w-2 h-2 rounded-full bg-[#E0703D]"></span>
                <span class="text-xs font-bold text-[#123356] uppercase tracking-wider">
                    {{ sessionType === 'nifas' ? 'Skrining Nifas' : 'Skrining Maternal' }}
                </span>
            </div>

            <div class="w-10 h-10"></div>
        </header>

        <!-- ============================================================= -->
        <!-- SCREEN 1: TRANSISI AWAL / STATUS CONSENT DICABUT -->
        <!-- ============================================================= -->
        <main v-if="currentStep === 'intro'" class="flex-1 max-w-md w-full mx-auto px-5 py-6 flex flex-col justify-between animate-fade-in">
            <div v-if="consentRevoked" class="text-center space-y-4 pt-6">
                <div class="w-20 h-20 rounded-full bg-amber-100 text-[#E0703D] mx-auto flex items-center justify-center shadow-md">
                    <span class="material-symbols-outlined text-4xl font-bold">privacy_tip</span>
                </div>
                <h1 class="text-lg font-extrabold text-[#123356]">
                    Persetujuan Data Sedang Dicabut
                </h1>
                <p class="text-xs text-[#73777F] leading-relaxed max-w-xs mx-auto">
                    Anda sebelumnya telah mencabut persetujuan pemrosesan data mandiri. Untuk menjaga privasi Anda, fitur pengisian skrining dinonaktifkan sementara.
                </p>
                <div class="p-4 bg-white rounded-2xl border border-amber-200 text-left text-xs text-[#123356] space-y-1.5 shadow-xs">
                    <div class="flex items-center gap-2 font-bold text-amber-800">
                        <span class="material-symbols-outlined text-base">emergency</span>
                        <span>Tombol Darurat SOS Tetap Siaga</span>
                    </div>
                    <p class="text-[11px] text-[#73777F]">
                        Tombol darurat SOS di aplikasi tetap siaga penuh kapan pun Anda membutuhkan bantuan darurat dari Bidan & faskes terdekat.
                    </p>
                </div>
            </div>

            <div v-else class="text-center space-y-3 pt-4">
                <!-- Maskot Siaga -->
                <div class="relative flex items-center justify-center mb-4">
                    <div class="w-36 h-36 rounded-full bg-white/80 shadow-md flex items-center justify-center p-2">
                        <img
                            src="/assets/mascot/mascot-pose-2.webp"
                            alt="Maskot SIGADIS Menyapa"
                            class="w-32 h-32 object-contain"
                            onerror="this.onerror=null; this.src='/assets/mascot/mascot-pose-1.webp';"
                        />
                    </div>
                </div>

                <h1 class="text-xl font-extrabold text-[#123356]">
                    Halo, {{ motherName }} 👋
                </h1>
                <p class="text-xs text-[#73777F] leading-relaxed max-w-xs mx-auto">
                    Skrining ini terdiri dari beberapa pertanyaan <span class="font-bold text-[#123356]">YA</span> atau <span class="font-bold text-[#123356]">TIDAK</span> sederhana untuk mendeteksi tanda bahaya sejak dini.
                </p>

                <div class="bg-white/80 rounded-2xl p-3.5 border border-[#F3AEC0]/40 max-w-xs mx-auto text-left space-y-2 text-xs text-[#123356]">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#4C9A6E] text-base">check_circle</span>
                        <span>Hanya butuh waktu sekitar 1–2 menit</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#4C9A6E] text-base">volume_up</span>
                        <span>Didukung asisten suara otomatis</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#4C9A6E] text-base">lock</span>
                        <span>Data medis terlindungi & terkirim ke Bidan</span>
                    </div>
                </div>
            </div>

            <div class="space-y-3 pt-6">
                <template v-if="consentRevoked">
                    <Link
                        :href="route('mobile.privacy.index')"
                        class="w-full py-4 px-6 rounded-2xl bg-[#4C9A6E] hover:bg-[#3d7d59] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xl active:scale-98 transition-all"
                    >
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        <span>Aktifkan Persetujuan di Menu Privasi</span>
                    </Link>
                    <Link
                        :href="route('mobile.dashboard')"
                        class="block w-full py-2 text-center text-xs text-[#73777F] hover:text-[#123356]"
                    >
                        Kembali ke Beranda
                    </Link>
                </template>
                <template v-else>
                    <button
                        @click="startScreening"
                        class="w-full py-4 px-6 rounded-2xl bg-[#123356] hover:bg-[#2C4A6E] text-white font-bold text-sm flex items-center justify-center gap-2 shadow-xl active:scale-98 transition-all"
                    >
                        <span>Mulai Skrining Sekarang</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                    <Link
                        :href="route('mobile.dashboard')"
                        class="block w-full py-2 text-center text-xs text-[#73777F] hover:text-[#123356]"
                    >
                        Nanti Saja
                    </Link>
                </template>
            </div>
        </main>

        <!-- ============================================================= -->
        <!-- SCREEN 2: PERTANYAAN (conversational-question-screen-template.html) -->
        <!-- ============================================================= -->
        <main v-else-if="currentStep === 'question'" class="flex-1 max-w-md w-full mx-auto px-5 py-4 flex flex-col justify-between animate-fade-in">
            <!-- Progress Bar -->
            <div class="space-y-1.5 mb-2">
                <div class="flex justify-between text-[11px] font-bold text-[#73777F]">
                    <span>Pertanyaan {{ currentIndex + 1 }} dari {{ questionList.length }}</span>
                    <span>{{ progressPercent }}%</span>
                </div>
                <div class="w-full bg-white rounded-full h-2 overflow-hidden shadow-2xs">
                    <div
                        class="bg-gradient-to-r from-[#F3AEC0] to-[#E0703D] h-full rounded-full transition-all duration-300"
                        :style="`width: ${progressPercent}%`"
                    ></div>
                </div>
            </div>

            <!-- Question Card Canvas -->
            <div class="my-auto py-4 flex flex-col items-center text-center">
                <!-- Badge Kategori -->
                <div
                    v-if="currentQuestion"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold mb-4"
                    :class="getCategoryBadge(currentQuestion.category).color"
                >
                    <span class="material-symbols-outlined text-sm">{{ getCategoryBadge(currentQuestion.category).icon }}</span>
                    <span>{{ getCategoryBadge(currentQuestion.category).label }}</span>
                </div>

                <!-- Teks Pertanyaan Besar & Jelas -->
                <h2
                    v-if="currentQuestion"
                    class="text-xl sm:text-2xl font-black text-[#123356] leading-snug max-w-sm mb-4"
                >
                    {{ currentQuestion.question_text }}
                </h2>

                <!-- Text-to-Speech Button -->
                <button
                    @click="speakCurrentQuestion"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border shadow-xs transition-all"
                    :class="isSpeaking ? 'bg-[#E0703D] text-white border-[#E0703D] animate-pulse' : 'bg-white text-[#123356] border-[#F3AEC0]/40 hover:bg-pink-50'"
                    aria-label="Putar Suara Pertanyaan"
                >
                    <span class="material-symbols-outlined text-lg">{{ isSpeaking ? 'volume_up' : 'volume_mute' }}</span>
                    <span class="text-xs font-bold">{{ isSpeaking ? 'Sedang Membacakan...' : 'Dengarkan Suara' }}</span>
                </button>
            </div>

            <!-- Opsi Jawaban Masif (Touch Target >= 48dp) -->
            <div class="space-y-3 pt-2">
                <!-- Tombol YA -->
                <button
                    @click="handleAnswer('ya')"
                    class="w-full min-h-[4.5rem] bg-white border border-[#F3AEC0]/60 hover:border-red-400 rounded-2xl shadow-sm hover:shadow-md p-4 flex items-center justify-between active:scale-98 transition-all group"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-red-50 text-[#D64550] flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl font-bold">check</span>
                        </div>
                        <span class="text-lg font-extrabold text-[#123356]">YA</span>
                    </div>
                    <span class="text-xs font-semibold text-[#73777F] group-hover:text-[#D64550]">Saya mengalaminya</span>
                </button>

                <!-- Tombol TIDAK -->
                <button
                    @click="handleAnswer('tidak')"
                    class="w-full min-h-[4.5rem] bg-white border border-[#F3AEC0]/60 hover:border-emerald-400 rounded-2xl shadow-sm hover:shadow-md p-4 flex items-center justify-between active:scale-98 transition-all group"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-[#4C9A6E] flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-2xl font-bold">close</span>
                        </div>
                        <span class="text-lg font-extrabold text-[#123356]">TIDAK</span>
                    </div>
                    <span class="text-xs font-semibold text-[#73777F] group-hover:text-[#4C9A6E]">Tidak ada keluhan</span>
                </button>

                <!-- Skip Option Link -->
                <div class="text-center pt-2">
                    <button
                        @click="handleAnswer('skip')"
                        class="text-xs text-[#73777F] hover:text-[#123356] underline py-1"
                    >
                        Lewati pertanyaan ini (Saya ragu)
                    </button>
                </div>
            </div>
        </main>

        <!-- ============================================================= -->
        <!-- SCREEN 3: HASIL PENILAIAN RISIKO (result-low / medium / high) -->
        <!-- ============================================================= -->
        <main v-else-if="currentStep === 'result'" class="flex-1 max-w-md w-full mx-auto px-5 py-4 flex flex-col justify-between animate-fade-in">
            <div class="space-y-4">
                <!-- 1. HASIL RISIKO TINGGI (MERAH) -->
                <div
                    v-if="resultData?.risk_level === 'tinggi'"
                    class="bg-[#D64550] rounded-3xl p-6 text-white text-center shadow-xl space-y-3 animate-fade-in"
                >
                    <div class="w-16 h-16 rounded-full bg-white/20 mx-auto flex items-center justify-center">
                        <span class="material-symbols-outlined text-4xl animate-bounce">warning</span>
                    </div>
                    <span class="inline-block px-3 py-1 rounded-full bg-white text-[#D64550] text-xs font-black uppercase tracking-wider">
                        Kategori Risiko Tinggi
                    </span>
                    <h2 class="text-xl font-extrabold leading-snug">
                        Perlu Penanganan Medis Segera!
                    </h2>
                    <p class="text-xs text-red-100 leading-relaxed">
                        Peringatan darurat telah otomatis diteruskan ke Bidan pendamping Anda. Harap segera menuju fasilitas kesehatan terdekat.
                    </p>
                </div>

                <!-- 2. HASIL RISIKO SEDANG (KUNING) -->
                <div
                    v-else-if="resultData?.risk_level === 'sedang'"
                    class="bg-[#E0A030] rounded-3xl p-6 text-white text-center shadow-xl space-y-3 animate-fade-in"
                >
                    <div class="w-16 h-16 rounded-full bg-white/20 mx-auto flex items-center justify-center">
                        <span class="material-symbols-outlined text-4xl">info</span>
                    </div>
                    <span class="inline-block px-3 py-1 rounded-full bg-white text-[#E0A030] text-xs font-black uppercase tracking-wider">
                        Kategori Risiko Sedang
                    </span>
                    <h2 class="text-xl font-extrabold leading-snug">
                        Perlu Pemantauan & Istirahat
                    </h2>
                    <p class="text-xs text-amber-50 leading-relaxed">
                        Terdeteksi keluhan yang membutuhkan konsultasi lanjutan. Disarankan untuk segera menghubungi Bidan dalam 1–2 hari ke depan.
                    </p>
                </div>

                <!-- 3. HASIL RISIKO RENDAH (HIJAU) -->
                <div
                    v-else
                    class="bg-[#4C9A6E] rounded-3xl p-6 text-white text-center shadow-xl space-y-3 animate-fade-in"
                >
                    <div class="w-16 h-16 rounded-full bg-white/20 mx-auto flex items-center justify-center">
                        <span class="material-symbols-outlined text-4xl">check_circle</span>
                    </div>
                    <span class="inline-block px-3 py-1 rounded-full bg-white text-[#4C9A6E] text-xs font-black uppercase tracking-wider">
                        Kategori Risiko Rendah
                    </span>
                    <h2 class="text-xl font-extrabold leading-snug">
                        Kondisi Ibu & Janin Sehat!
                    </h2>
                    <p class="text-xs text-emerald-100 leading-relaxed">
                        Tidak ditemukan tanda bahaya kritis. Tetap jaga nutrisi seimbang, istirahat cukup, dan rutin ikuti jadwal periksa kehamilan.
                    </p>
                </div>

                <!-- Detail Rekomendasi Medis -->
                <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm space-y-3">
                    <h3 class="text-xs font-extrabold text-[#123356] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base text-[#123356]">notes</span>
                        Rekomendasi Tindakan
                    </h3>
                    <p class="text-xs text-[#26292E] leading-relaxed">
                        {{ resultData?.recommendation }}
                    </p>

                    <div v-if="resultData?.is_data_incomplete" class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800">
                        <strong>Catatan:</strong> Ada beberapa pertanyaan yang Anda lewati. Untuk kepastian kondisi, silakan konsultasikan ke Bidan pendamping.
                    </div>
                </div>

                <!-- Mandatory Medical Disclaimer (Flows.md §5.2) -->
                <div class="p-3 bg-gray-100/80 rounded-2xl text-[10px] text-[#73777F] leading-tight text-center">
                    <em>Hasil skrining ini merupakan alat bantu deteksi dini mandiri dan bukan diagnosis medis final. Untuk kepastian kondisi, periksakan diri ke tenaga kesehatan.</em>
                </div>
            </div>

            <!-- Tombol Navigasi Pasca Skrining -->
            <div class="space-y-2 pt-4">
                <Link
                    v-if="resultData?.risk_level === 'tinggi'"
                    :href="route('mobile.facilities.index')"
                    class="w-full py-4 px-4 rounded-2xl bg-[#D64550] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg active:scale-98 transition-all"
                >
                    <span class="material-symbols-outlined text-base">local_hospital</span>
                    <span>Cari Faskes Terdekat Sekarang</span>
                </Link>

                <Link
                    :href="route('mobile.dashboard')"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all"
                >
                    <span>Kembali ke Beranda</span>
                </Link>

                <Link
                    :href="route('mobile.history.index')"
                    class="block w-full py-2 text-center text-xs text-[#73777F] hover:text-[#123356]"
                >
                    Lihat di Riwayat Skrining
                </Link>
            </div>
        </main>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
