<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    phoneNumber: {
        type: String,
        required: true,
    },
    otpRequestId: {
        type: String,
        default: '',
    },
    debugCode: {
        type: String,
        default: null,
    },
});

const digits = ref(['', '', '', '', '', '']);
const digitInputs = ref([]);
const resendTimer = ref(60);
let timerInterval = null;

const form = useForm({
    phone_number: props.phoneNumber,
    otp_request_id: props.otpRequestId,
    otp_code: '',
});

const formattedPhone = computed(() => {
    let p = props.phoneNumber || '';
    if (p.startsWith('0')) p = '62' + p.substring(1);
    if (!p.startsWith('+')) p = '+' + p;
    return p;
});

const handleInput = (index, e) => {
    const val = e.target.value.replace(/\D/g, '');
    digits.value[index] = val ? val.slice(-1) : '';

    if (val && index < 5) {
        digitInputs.value[index + 1]?.focus();
    }

    syncOtpCode();
};

const handleKeyDown = (index, e) => {
    if (e.key === 'Backspace' && !digits.value[index] && index > 0) {
        digitInputs.value[index - 1]?.focus();
    }
};

const handlePaste = (e) => {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (!pasted) return;

    for (let i = 0; i < 6; i++) {
        digits.value[i] = pasted[i] || '';
    }

    const nextIndex = Math.min(pasted.length, 5);
    digitInputs.value[nextIndex]?.focus();
    syncOtpCode();
};

const syncOtpCode = () => {
    form.otp_code = digits.value.join('');
    if (form.otp_code.length === 6) {
        submit();
    }
};

const fillDebugCode = () => {
    if (!props.debugCode) return;
    const chars = props.debugCode.split('');
    for (let i = 0; i < 6; i++) {
        digits.value[i] = chars[i] || '';
    }
    syncOtpCode();
};

const submit = () => {
    form.otp_code = digits.value.join('');
    form.post(route('mobile.password-reset.verify'), {
        preserveScroll: true,
        onError: () => {
            digits.value = ['', '', '', '', '', ''];
            digitInputs.value[0]?.focus();
        },
    });
};

const resendOtp = () => {
    if (resendTimer.value > 0) return;

    router.post(
        route('mobile.password-reset.send'),
        { phone_number: props.phoneNumber },
        {
            preserveScroll: true,
            onSuccess: () => {
                resendTimer.value = 60;
                digits.value = ['', '', '', '', '', ''];
                digitInputs.value[0]?.focus();
            },
        }
    );
};

onMounted(() => {
    digitInputs.value[0]?.focus();

    timerInterval = setInterval(() => {
        if (resendTimer.value > 0) {
            resendTimer.value--;
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="Verifikasi Kode Reset Sandi — SIGADIS Mobile" />

    <div class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-hidden select-none">
        <!-- Background Decorative Glows -->
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-[#F3AEC0]/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -left-24 w-64 h-64 bg-[#ABC9F3]/25 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top App Bar -->
        <header class="relative z-20 flex items-center justify-between px-5 pt-5 pb-2">
            <Link
                :href="route('mobile.password-reset.request')"
                class="w-11 h-11 rounded-full bg-white/80 backdrop-blur-md border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center shadow-xs active:scale-95 transition-all"
                aria-label="Kembali"
            >
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </Link>

            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/70 backdrop-blur-md border border-[#F3AEC0]/30 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-[#E0703D] animate-pulse"></span>
                <span class="text-xs font-bold text-[#123356] tracking-wider uppercase">Verifikasi Reset</span>
            </div>

            <div class="w-11 h-11"></div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 flex-1 flex flex-col px-6 pt-4 pb-6 max-w-md w-full mx-auto justify-between">
            <!-- Header Title -->
            <div class="space-y-2 mb-4 text-center sm:text-left">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Masukkan Kode OTP
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E] leading-relaxed">
                    Kode verifikasi 6-digit untuk pemulihan kata sandi telah dikirimkan ke:
                    <br />
                    <span class="font-bold text-[#123356] text-sm sm:text-base">{{ formattedPhone }}</span>
                </p>
            </div>

            <!-- Simulation Banner -->
            <div
                v-if="debugCode"
                class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 shadow-xs animate-bounce-subtle"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600 text-xl">developer_mode</span>
                        <div class="text-left">
                            <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Simulasi Kode OTP (Dev)</p>
                            <p class="text-lg font-extrabold font-mono tracking-widest text-[#123356]">{{ debugCode }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="fillDebugCode"
                        class="px-3 py-1.5 text-xs font-bold bg-[#123356] text-white rounded-xl shadow-xs hover:bg-[#1E334D] active:scale-95 transition-all"
                    >
                        Isi Otomatis
                    </button>
                </div>
            </div>

            <!-- OTP Input Card -->
            <form @submit.prevent="submit" class="flex-1 flex flex-col justify-between gap-5">
                <div class="bg-white/90 backdrop-blur-md rounded-3xl p-5 sm:p-6 border border-[#F3AEC0]/40 shadow-sm space-y-4 text-center">
                    <label class="block text-xs font-bold text-[#73777F] uppercase tracking-wider">
                        Kode Verifikasi 6-Digit
                    </label>

                    <!-- 6 Digit Boxes -->
                    <div class="flex justify-center items-center gap-2 sm:gap-3" @paste="handlePaste">
                        <input
                            v-for="(_, index) in 6"
                            :key="index"
                            ref="digitInputs"
                            v-model="digits[index]"
                            type="tel"
                            inputmode="numeric"
                            maxlength="1"
                            class="w-11 h-13 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold text-[#123356] bg-[#FAF9FC] border border-[#C3C6CF] rounded-2xl focus:border-[#123356] focus:ring-2 focus:ring-[#123356]/25 focus:bg-white focus:outline-none transition-all"
                            @input="(e) => handleInput(index, e)"
                            @keydown="(e) => handleKeyDown(index, e)"
                        />
                    </div>

                    <p v-if="form.errors.otp_code" class="text-xs text-[#BA1A1A] font-semibold mt-2 animate-shake">
                        {{ form.errors.otp_code }}
                    </p>

                    <!-- Resend OTP -->
                    <div class="pt-2 text-xs text-[#73777F]">
                        <p v-if="resendTimer > 0">
                            Kirim ulang kode dalam <span class="font-bold text-[#123356]">{{ resendTimer }} detik</span>
                        </p>
                        <button
                            v-else
                            type="button"
                            @click="resendOtp"
                            class="font-bold text-[#E0703D] hover:underline inline-flex items-center gap-1"
                        >
                            <span class="material-symbols-outlined text-sm">refresh</span>
                            <span>Kirim Ulang Kode OTP</span>
                        </button>
                    </div>
                </div>

                <!-- Floating Mascot Pose 3 -->
                <div class="relative flex items-center justify-center -my-2 pointer-events-none">
                    <img
                        src="/assets/mascot/mascot-pose-3.webp"
                        alt="Maskot SIGADIS"
                        class="w-28 sm:w-32 h-auto object-contain drop-shadow-md"
                    />
                </div>

                <!-- Bottom CTA Button -->
                <div class="space-y-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing || digits.join('').length < 6"
                        class="w-full h-14 bg-[#123356] hover:bg-[#1E334D] active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-[#123356]/25 transition-all flex items-center justify-center gap-2 text-base disabled:opacity-50"
                    >
                        <svg
                            v-if="form.processing"
                            class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Memverifikasi...' : 'Verifikasi & Lanjutkan' }}</span>
                        <span v-if="!form.processing" class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
.animate-shake {
    animation: shake 0.4s ease-in-out;
}
</style>
