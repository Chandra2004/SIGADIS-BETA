<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    status: { type: String, default: '' },
});

const showPassword = ref(false);
const showOtpModal = ref(false);
const otpDigits = ref(['', '', '', '', '', '']);
const resendTimer = ref(59);
let timerInterval = null;

const form = useForm({
    identifier: '', // nomor HP atau STR
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('auth.staff.login'), {
        onSuccess: () => {
            // Inertia will handle redirect based on server response
        },
        onError: () => {
            form.reset('password');
        },
    });
};

const openOtpDemo = () => {
    showOtpModal.value = true;
    startResendTimer();
};

const startResendTimer = () => {
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

const handleOtpInput = (index, event) => {
    const val = event.target.value.replace(/\D/g, '').slice(-1);
    otpDigits.value[index] = val;
    if (val && index < 5) {
        const nextInput = document.getElementById(`otp-digit-${index + 1}`);
        if (nextInput) nextInput.focus();
    }
};

const handleOtpKeydown = (index, event) => {
    if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
        const prevInput = document.getElementById(`otp-digit-${index - 1}`);
        if (prevInput) prevInput.focus();
    }
};

const resendOtp = () => {
    if (resendTimer.value === 0) {
        startResendTimer();
    }
};

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="Portal Bidan & Kader — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Portal Bidan & Kader"
        panel-title="Sistem Gawat Darurat Ibu-Selamat"
        panel-description="Portal Bidan & Kader. Akses data maternal terpusat, pantau risiko tinggi, dan berikan respon cepat untuk keselamatan ibu dan bayi."
    >
        <!-- Form Header -->
        <div class="text-center md:text-left mb-8">
            <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                Masuk ke Portal
            </h1>
            <p class="text-sm text-[#43474E]">
                Silakan masuk menggunakan kredensial Anda yang terdaftar.
            </p>
        </div>

        <!-- Flash status notification -->
        <div
            v-if="status"
            class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800"
        >
            <span class="material-symbols-outlined text-emerald-600 text-lg">check_circle</span>
            <span>{{ status }}</span>
        </div>

        <!-- General Error Alert -->
        <div
            v-if="form.errors.phone_number || form.errors.identifier"
            class="mb-6 flex items-center gap-3 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-700"
        >
            <span class="material-symbols-outlined text-rose-600 text-lg">error</span>
            <span>{{ form.errors.phone_number || form.errors.identifier }}</span>
        </div>

        <!-- Form -->
        <form class="space-y-5" @submit.prevent="submit">
            
            <!-- Input: Nomor Handphone / STR / Email -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-[#26292E]" for="identifier">
                    Nomor Handphone / STR / Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                        <span class="material-symbols-outlined text-xl">account_circle</span>
                    </div>
                    <input
                        id="identifier"
                        v-model="form.identifier"
                        type="text"
                        autocomplete="username"
                        required
                        autofocus
                        placeholder="Nomor HP, STR, atau Email terdaftar"
                        class="block w-full pl-11 pr-4 py-3 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-12"
                    />
                </div>
            </div>

            <!-- Input: Kata Sandi -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-[#26292E]" for="password">
                    Kata Sandi
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                        <span class="material-symbols-outlined text-xl">lock</span>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        required
                        placeholder="••••••••"
                        class="block w-full pl-11 pr-11 py-3 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-12"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#73777F] hover:text-[#26292E] transition-colors focus:outline-none"
                        @click="showPassword = !showPassword"
                    >
                        <span class="material-symbols-outlined text-xl">
                            {{ showPassword ? 'visibility_off' : 'visibility' }}
                        </span>
                    </button>
                </div>
                <p v-if="form.errors.password" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Remember Me + Lupa Password Link -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex cursor-pointer items-center gap-2 text-sm text-[#43474E] select-none">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-[#C3C6CF] text-[#2C4A6E] focus:ring-[#2C4A6E]/30"
                    />
                    <span>Ingat Saya</span>
                </label>
                <Link
                    :href="route('auth.staff.password-reset.request')"
                    class="text-sm font-semibold text-[#2C4A6E] hover:text-[#3D6086] hover:underline transition-colors"
                >
                    Lupa Password?
                </Link>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] active:bg-[#1E334D] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C4A6E] h-12 transition-all disabled:opacity-60 mt-6"
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
                <span>Masuk</span>
            </button>

            <!-- Divider -->
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-[#E3E2E5]"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-[#73777F]">atau</span>
                </div>
            </div>

            <!-- Register Link -->
            <p class="text-center text-sm text-[#43474E]">
                Belum punya akun?
                <Link
                    :href="route('auth.staff.register.show')"
                    class="font-semibold text-[#2C4A6E] hover:text-[#3D6086] underline underline-offset-2 transition-colors ml-1"
                >
                    Daftar di sini
                </Link>
            </p>
        </form>
    </AuthLayout>

    <!-- OTP Modal Overlay (matching login-web.html specifications) -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showOtpModal"
                class="fixed inset-0 z-50 overflow-y-auto"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-title"
            >
                <!-- Background backdrop -->
                <div
                    class="fixed inset-0 bg-[#26292E]/75 backdrop-blur-sm transition-opacity"
                    @click="showOtpModal = false"
                ></div>

                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <!-- Modal panel -->
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-[#C3C6CF]">
                        <!-- Close button -->
                        <div class="absolute right-0 top-0 pr-4 pt-4">
                            <button
                                type="button"
                                class="rounded-lg bg-white p-1 text-[#73777F] hover:text-[#26292E] hover:bg-[#FAF9FC] transition-colors focus:outline-none"
                                @click="showOtpModal = false"
                            >
                                <span class="sr-only">Tutup</span>
                                <span class="material-symbols-outlined text-xl">close</span>
                            </button>
                        </div>

                        <div class="bg-white p-6 sm:p-8">
                            <div class="sm:flex sm:items-start gap-4">
                                <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#ABC9F3]/30 sm:mx-0">
                                    <span class="material-symbols-outlined text-[#2C4A6E] text-2xl">phonelink_lock</span>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1">
                                    <h3 id="modal-title" class="text-xl font-bold text-[#26292E]">
                                        Verifikasi Perangkat Baru
                                    </h3>
                                    <p class="mt-2 text-sm text-[#43474E] leading-relaxed">
                                        Kami telah mengirimkan 6-digit kode OTP ke nomor handphone Anda yang terdaftar. Masukkan kode untuk melanjutkan.
                                    </p>

                                    <!-- OTP Inputs -->
                                    <div class="mt-6 flex justify-between gap-2 max-w-xs mx-auto sm:mx-0">
                                        <input
                                            v-for="(_, i) in otpDigits"
                                            :id="`otp-digit-${i}`"
                                            :key="i"
                                            v-model="otpDigits[i]"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="1"
                                            :autofocus="i === 0"
                                            class="w-11 h-13 text-center text-xl font-bold border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] focus:bg-white focus:ring-2 focus:ring-[#2C4A6E]/20 focus:border-[#2C4A6E] focus:outline-none transition-all"
                                            @input="handleOtpInput(i, $event)"
                                            @keydown="handleOtpKeydown(i, $event)"
                                        />
                                    </div>

                                    <!-- Resend Timer -->
                                    <div class="mt-4 text-center sm:text-left">
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
                                </div>
                            </div>
                        </div>

                        <!-- Modal Action Buttons -->
                        <div class="bg-[#F4F3F6] px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                            <button
                                type="button"
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-[#26292E] shadow-sm ring-1 ring-inset ring-[#C3C6CF] hover:bg-neutral-100 transition-colors h-11 items-center"
                                @click="showOtpModal = false"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-[#2C4A6E] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#3D6086] transition-colors h-11 items-center"
                                @click="showOtpModal = false"
                            >
                                Verifikasi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
