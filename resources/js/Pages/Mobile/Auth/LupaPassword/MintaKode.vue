<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    phone_number: '',
});

const normalizePhone = (num) => {
    let clean = (num || '').toString().trim().replace(/\D/g, '');
    if (clean.startsWith('62')) clean = '0' + clean.substring(2);
    else if (clean.startsWith('8')) clean = '0' + clean;
    return clean;
};

const submit = () => {
    form.phone_number = normalizePhone(form.phone_number);
    form.post(route('mobile.password-reset.send'));
};
</script>

<template>
    <Head title="Lupa Kata Sandi — SIGADIS Mobile" />

    <div class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-hidden select-none">
        <!-- Background Decorative Glows -->
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-[#F3AEC0]/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -left-24 w-64 h-64 bg-[#ABC9F3]/25 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top App Bar -->
        <header class="relative z-20 flex items-center justify-between px-5 pt-5 pb-2">
            <Link
                :href="route('mobile.login.show')"
                class="w-11 h-11 rounded-full bg-white/80 backdrop-blur-md border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center shadow-xs active:scale-95 transition-all"
                aria-label="Kembali ke Halaman Masuk"
            >
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </Link>

            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/70 backdrop-blur-md border border-[#F3AEC0]/30 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-[#E0703D]"></span>
                <span class="text-xs font-bold text-[#123356] tracking-wider uppercase">Reset Sandi</span>
            </div>

            <div class="w-11 h-11"></div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 flex-1 flex flex-col px-6 pt-4 pb-6 max-w-md w-full mx-auto justify-between">
            <!-- Header Title -->
            <div class="space-y-1.5 mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Atur Ulang Kata Sandi
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E] leading-relaxed">
                    Masukkan nomor WhatsApp Bunda yang terdaftar untuk menerima kode verifikasi OTP.
                </p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="flex-1 flex flex-col justify-between gap-5">
                <div class="space-y-4 bg-white/90 backdrop-blur-md rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-sm">
                    <div class="space-y-1.5">
                        <label for="reset-phone" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Nomor Handphone (WhatsApp)
                        </label>
                        <div class="flex overflow-hidden rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <span class="flex items-center justify-center px-4 bg-[#EDEBEF] text-[#43474E] font-bold text-sm border-r border-[#C3C6CF] select-none">
                                +62
                            </span>
                            <input
                                id="reset-phone"
                                v-model="form.phone_number"
                                type="tel"
                                inputmode="numeric"
                                autocomplete="tel"
                                required
                                autofocus
                                placeholder="81234567890"
                                class="flex-1 h-12 px-3.5 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white"
                            />
                        </div>
                        <p v-if="form.errors.phone_number" class="text-xs text-[#BA1A1A] font-semibold mt-1">
                            {{ form.errors.phone_number }}
                        </p>
                    </div>

                    <!-- Notice Box -->
                    <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-[#ABC9F3]/25 border border-[#ABC9F3]/60 text-xs text-[#123356]">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0 text-[#2C4A6E]">lock_reset</span>
                        <span>Kode OTP 6 digit akan dikirimkan untuk memastikan keamanan proses pemulihan kata sandi Bunda.</span>
                    </div>
                </div>

                <!-- Floating Mascot Pose 2 (Menjelaskan) -->
                <div class="relative flex items-center justify-end pr-2 -my-2 pointer-events-none">
                    <img
                        src="/assets/mascot/mascot-pose-2.webp"
                        alt="Maskot SIGADIS"
                        class="w-28 sm:w-32 h-auto object-contain drop-shadow-md"
                    />
                </div>

                <!-- Bottom CTA Button -->
                <div class="space-y-3 pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing || !form.phone_number"
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
                        <span>{{ form.processing ? 'Mengirim OTP...' : 'Kirim Kode Verifikasi' }}</span>
                        <span v-if="!form.processing" class="material-symbols-outlined text-xl">send</span>
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
