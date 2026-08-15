<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    phoneNumber: { type: String, default: '' },
    otpRequestId: { type: String, default: '' },
    debugCode: { type: String, default: '' },
});

const otpDigits = ref(['', '', '', '', '', '']);
const resendTimer = ref(59);
let timerInterval = null;

const form = useForm({
    phone_number: props.phoneNumber,
    otp_request_id: props.otpRequestId,
    otp_code: '',
});

const fillDebugCode = () => {
    if (!props.debugCode) return;
    const digits = props.debugCode.toString().split('');
    digits.forEach((d, i) => {
        if (i < 6) otpDigits.value[i] = d;
    });
    form.otp_code = props.debugCode;
};

const handleOtpInput = (index, event) => {
    const val = event.target.value.replace(/\D/g, '').slice(-1);
    otpDigits.value[index] = val;
    form.otp_code = otpDigits.value.join('');

    if (val && index < 5) {
        const nextInput = document.getElementById(`reset-otp-${index + 1}`);
        if (nextInput) nextInput.focus();
    }
};

const handleOtpKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
        const prevInput = document.getElementById(`reset-otp-${index - 1}`);
        if (prevInput) prevInput.focus();
    }
};

const submit = () => {
    form.otp_code = otpDigits.value.join('');
    form.post(route('auth.staff.password-reset.verify'));
};

const resendOtp = () => {
    if (resendTimer.value === 0) {
        router.post(route('auth.staff.password-reset.send'), {
            phone_number: props.phoneNumber,
        }, {
            onSuccess: () => {
                startTimer();
            },
        });
    }
};

const startTimer = () => {
    resendTimer.value = 59;
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (resendTimer.value > 0) {
            resendTimer.value--;
        } else {
            clearInterval(timerInterval);
        }
    }, 1000);
};

onMounted(() => {
    startTimer();
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="Verifikasi Kode OTP — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Verifikasi Keamanan"
        panel-title="Masukkan Kode Verifikasi WhatsApp"
        panel-description="Kami telah mengirimkan 6-digit kode OTP ke nomor WhatsApp Anda untuk mengonfirmasi identitas pemulihan akun."
    >
        <!-- Header -->
        <div class="text-center md:text-left mb-8">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-[#ABC9F3]/30 text-[#2C4A6E] mb-3">
                <span class="material-symbols-outlined text-2xl">phonelink_lock</span>
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Verifikasi Kode OTP
            </h1>
            <p class="text-sm text-[#43474E] leading-relaxed">
                Kode verifikasi telah dikirim ke nomor <strong>{{ phoneNumber }}</strong>.
            </p>
        </div>

        <!-- Debug Code (Mode Simulasi / OTP_GATEWAY_STATUS=false) -->
        <div v-if="debugCode" class="mb-5 rounded-2xl bg-amber-50 border border-amber-300 p-4 text-xs text-amber-900 shadow-sm">
            <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-1.5 font-bold text-amber-800">
                    <span class="material-symbols-outlined text-base text-amber-600">developer_mode</span>
                    <span>Mode Simulasi (OTP_GATEWAY_STATUS=false)</span>
                </div>
                <button
                    type="button"
                    class="px-2.5 py-1 text-[11px] font-bold bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white rounded-lg transition-colors shadow-xs cursor-pointer"
                    @click="fillDebugCode"
                >
                    Isi Otomatis
                </button>
            </div>
            <div class="flex items-center justify-between bg-white rounded-xl p-2.5 border border-amber-200 shadow-inner">
                <span class="text-xs text-[#73777F] font-medium">Kode OTP Anda:</span>
                <span class="font-mono font-black text-base tracking-[0.25em] text-amber-900 bg-amber-100/60 px-2 py-0.5 rounded-md">{{ debugCode }}</span>
            </div>
        </div>

        <!-- Form -->
        <form class="space-y-6" @submit.prevent="submit">
            
            <!-- OTP Input 6 Digits -->
            <div>
                <label class="block text-center text-xs font-semibold text-[#73777F] uppercase tracking-wider mb-3">
                    Kode 6-Digit OTP
                </label>
                <div class="flex justify-between gap-2 max-w-sm mx-auto">
                    <input
                        v-for="(_, i) in otpDigits"
                        :id="`reset-otp-${i}`"
                        :key="i"
                        v-model="otpDigits[i]"
                        type="text"
                        inputmode="numeric"
                        maxlength="1"
                        :autofocus="i === 0"
                        class="w-12 h-14 text-center text-2xl font-extrabold border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] focus:bg-white focus:ring-2 focus:ring-[#2C4A6E]/20 focus:border-[#2C4A6E] focus:outline-none transition-all shadow-sm"
                        @input="handleOtpInput(i, $event)"
                        @keydown="handleOtpKeydown(i, $event)"
                    />
                </div>
                <p v-if="form.errors.otp_code" class="text-xs text-[#BA1A1A] mt-2 font-medium text-center">
                    {{ form.errors.otp_code }}
                </p>
            </div>

            <!-- Resend Timer -->
            <div class="text-center">
                <button
                    type="button"
                    :disabled="resendTimer > 0"
                    class="text-xs font-semibold text-[#2C4A6E] hover:text-[#3D6086] disabled:text-[#8A8D96] transition-colors"
                    @click="resendOtp"
                >
                    <span v-if="resendTimer > 0">
                        Kirim Ulang Kode (00:{{ resendTimer.toString().padStart(2, '0') }})
                    </span>
                    <span v-else class="underline">
                        Kirim Ulang Kode Sekarang
                    </span>
                </button>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing || otpDigits.some(d => !d)"
                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C4A6E] h-12 transition-all disabled:opacity-60 mt-4"
            >
                <svg
                    v-if="form.processing"
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Verifikasi Kode</span>
            </button>

            <!-- Back -->
            <div class="text-center pt-2">
                <Link
                    :href="route('auth.staff.password-reset.request')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#73777F] hover:text-[#26292E] transition-colors"
                >
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Ganti Nomor Handphone
                </Link>
            </div>
        </form>
    </AuthLayout>
</template>
