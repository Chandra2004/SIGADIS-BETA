<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    full_name: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const normalizePhone = (num) => {
    let clean = (num || '').toString().trim().replace(/\D/g, '');
    if (clean.startsWith('62')) clean = '0' + clean.substring(2);
    else if (clean.startsWith('8')) clean = '0' + clean;
    return clean;
};

const submit = () => {
    form.phone_number = normalizePhone(form.phone_number);
    form.post(route('mobile.register.send'));
};
</script>

<template>
    <Head title="Daftar Akun Ibu Hamil — SIGADIS Mobile" />

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
                <span class="text-xs font-bold text-[#123356] tracking-wider uppercase">Registrasi Bunda</span>
            </div>

            <div class="w-11 h-11"></div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 flex-1 flex flex-col px-6 pt-2 pb-6 max-w-md w-full mx-auto justify-between overflow-y-auto">
            <!-- Header Title -->
            <div class="space-y-1 mb-4">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Daftar Akun Baru
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E] leading-relaxed">
                    Lengkapi data Bunda untuk memulai pemantauan kehamilan bersama SIGADIS.
                </p>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="space-y-3.5">
                <div class="space-y-3.5 bg-white/90 backdrop-blur-md rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-sm">
                    <!-- Input Nama Lengkap -->
                    <div class="space-y-1">
                        <label for="reg-name" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Nama Lengkap Bunda
                        </label>
                        <div class="relative flex items-center rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <input
                                id="reg-name"
                                v-model="form.full_name"
                                type="text"
                                autocomplete="name"
                                required
                                autofocus
                                placeholder="Contoh: Siti Rahmawati"
                                class="w-full h-11 px-4 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white rounded-2xl"
                            />
                        </div>
                        <p v-if="form.errors.full_name" class="text-xs text-[#BA1A1A] font-semibold mt-1">
                            {{ form.errors.full_name }}
                        </p>
                    </div>

                    <!-- Input Nomor WhatsApp -->
                    <div class="space-y-1">
                        <label for="reg-phone" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Nomor Handphone (WhatsApp)
                        </label>
                        <div class="flex overflow-hidden rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <span class="flex items-center justify-center px-4 bg-[#EDEBEF] text-[#43474E] font-bold text-sm border-r border-[#C3C6CF] select-none">
                                +62
                            </span>
                            <input
                                id="reg-phone"
                                v-model="form.phone_number"
                                type="tel"
                                inputmode="numeric"
                                autocomplete="tel"
                                required
                                placeholder="81234567890"
                                class="flex-1 h-11 px-3.5 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white"
                            />
                        </div>
                        <p v-if="form.errors.phone_number" class="text-xs text-[#BA1A1A] font-semibold mt-1">
                            {{ form.errors.phone_number }}
                        </p>
                    </div>

                    <!-- Input Kata Sandi -->
                    <div class="space-y-1">
                        <label for="reg-password" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Kata Sandi (Minimal 8 Karakter)
                        </label>
                        <div class="relative flex items-center rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <input
                                id="reg-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Buat kata sandi akun"
                                class="w-full h-11 pl-4 pr-11 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white rounded-2xl"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 text-[#73777F] hover:text-[#26292E] p-1 flex items-center justify-center"
                                :aria-label="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                            >
                                <span class="material-symbols-outlined text-xl">
                                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-[#BA1A1A] font-semibold mt-1">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Input Konfirmasi Kata Sandi -->
                    <div class="space-y-1">
                        <label for="reg-password-confirm" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Ulangi Kata Sandi
                        </label>
                        <div class="relative flex items-center rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <input
                                id="reg-password-confirm"
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Ketik ulang kata sandi"
                                class="w-full h-11 pl-4 pr-11 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white rounded-2xl"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-3 text-[#73777F] hover:text-[#26292E] p-1 flex items-center justify-center"
                                :aria-label="showPasswordConfirm ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                            >
                                <span class="material-symbols-outlined text-xl">
                                    {{ showPasswordConfirm ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- OTP Info Box -->
                <div class="flex items-start gap-2.5 p-3 rounded-2xl bg-[#ABC9F3]/25 border border-[#ABC9F3]/60 text-xs text-[#123356]">
                    <span class="material-symbols-outlined text-lg mt-0.5 shrink-0 text-[#2C4A6E]">info</span>
                    <span>Kode verifikasi OTP 6 digit akan dikirimkan langsung ke nomor WhatsApp Bunda setelah tombol ditekan.</span>
                </div>

                <!-- Bottom CTA Button & Login Link -->
                <div class="space-y-3 pt-1">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full h-14 bg-[#123356] hover:bg-[#1E334D] active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-[#123356]/25 transition-all flex items-center justify-center gap-2 text-base disabled:opacity-60"
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
                        <span>{{ form.processing ? 'Mengirim OTP...' : 'Daftar & Kirim Kode OTP' }}</span>
                        <span v-if="!form.processing" class="material-symbols-outlined text-xl">send</span>
                    </button>

                    <div class="text-center pb-2">
                        <p class="text-xs text-[#73777F]">
                            Sudah memiliki akun Bunda?
                            <Link
                                :href="route('mobile.login.show')"
                                class="font-bold text-[#E0703D] hover:underline ml-1"
                            >
                                Masuk di sini
                            </Link>
                        </p>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>
