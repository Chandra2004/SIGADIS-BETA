<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
        default: null,
    },
});

const form = useForm({
    identifier: '',
    password: '',
    remember: true,
});

const showPassword = ref(false);

const normalizePhone = (num) => {
    let clean = (num || '').toString().trim().replace(/\D/g, '');
    if (clean.startsWith('62')) clean = '0' + clean.substring(2);
    else if (clean.startsWith('8')) clean = '0' + clean;
    return clean;
};

const submit = () => {
    form.identifier = normalizePhone(form.identifier);
    form.post(route('mobile.login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk Akun Ibu Hamil — SIGADIS Mobile" />

    <div class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-hidden select-none">
        <!-- Background Decorative Glows -->
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-[#F3AEC0]/30 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -left-24 w-64 h-64 bg-[#ABC9F3]/25 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top App Bar -->
        <header class="relative z-20 flex items-center justify-between px-5 pt-5 pb-2">
            <Link
                :href="route('onboarding')"
                class="w-11 h-11 rounded-full bg-white/80 backdrop-blur-md border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center shadow-xs active:scale-95 transition-all"
                aria-label="Kembali ke Onboarding"
            >
                <span class="material-symbols-outlined text-2xl">arrow_back</span>
            </Link>

            <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/70 backdrop-blur-md border border-[#F3AEC0]/30 shadow-xs">
                <span class="w-2 h-2 rounded-full bg-[#E0703D]"></span>
                <span class="text-xs font-bold text-[#123356] tracking-wider uppercase">Portal Ibu Hamil</span>
            </div>

            <div class="w-11 h-11"></div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 flex-1 flex flex-col px-6 pt-4 pb-6 max-w-md w-full mx-auto justify-between">
            <!-- Header Title -->
            <div class="space-y-1.5 mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Masuk Akun Bunda
                </h1>
                <p class="text-sm text-[#43474E] leading-relaxed">
                    Masukkan nomor WhatsApp dan kata sandi yang telah terdaftar.
                </p>
            </div>

            <!-- Flash Success Status Banner -->
            <div
                v-if="status || $page.props.flash?.status || $page.props.flash?.success"
                class="mb-4 p-3.5 rounded-2xl bg-[#E6F4EC] border border-[#4C9A6E]/30 text-[#2B6645] text-xs font-medium flex items-start gap-2.5 shadow-xs animate-fade-in"
            >
                <span class="material-symbols-outlined text-lg text-[#4C9A6E] shrink-0 mt-0.5">check_circle</span>
                <span>{{ status || $page.props.flash?.status || $page.props.flash?.success }}</span>
            </div>

            <!-- Bidan / Kader Attempting Mobile Login Notice -->
            <div
                v-if="form.errors.identifier && (form.errors.identifier.includes('Portal Website') || form.errors.identifier.includes('Bidan') || form.errors.identifier.includes('Administrator'))"
                class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-sm animate-fade-in"
            >
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-xl">desktop_windows</span>
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-amber-900 uppercase tracking-wider">Khusus Tenaga Medis (Bidan & Kader)</p>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            {{ form.errors.identifier }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- General Error Alert -->
            <div
                v-else-if="form.errors.identifier && form.errors.identifier !== 'Nomor Handphone atau kata sandi tidak cocok.'"
                class="mb-4 p-3.5 rounded-2xl bg-[#FBE4E5] border border-[#D64550]/30 text-[#93000A] text-xs font-medium flex items-start gap-2.5 shadow-xs"
            >
                <span class="material-symbols-outlined text-lg text-[#D64550] shrink-0 mt-0.5">error</span>
                <span>{{ form.errors.identifier }}</span>
            </div>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="flex-1 flex flex-col justify-between gap-5">
                <div class="space-y-4 bg-white/90 backdrop-blur-md rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-sm relative">
                    <!-- Input Nomor WhatsApp -->
                    <div class="space-y-1.5">
                        <label for="mobile-phone" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                            Nomor Handphone (WhatsApp)
                        </label>
                        <div class="flex overflow-hidden rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <span class="flex items-center justify-center px-4 bg-[#EDEBEF] text-[#43474E] font-bold text-sm border-r border-[#C3C6CF] select-none">
                                +62
                            </span>
                            <input
                                id="mobile-phone"
                                v-model="form.identifier"
                                type="tel"
                                inputmode="numeric"
                                autocomplete="tel"
                                required
                                autofocus
                                placeholder="81234567890"
                                class="flex-1 h-12 px-3.5 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white"
                            />
                        </div>
                    </div>

                    <!-- Input Kata Sandi -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="mobile-password" class="block text-xs font-bold text-[#123356] uppercase tracking-wider">
                                Kata Sandi
                            </label>
                            <Link
                                :href="route('mobile.password-reset.request')"
                                class="text-xs font-bold text-[#E0703D] hover:underline"
                            >
                                Lupa Kata Sandi?
                            </Link>
                        </div>
                        <div class="relative flex items-center rounded-2xl border border-[#C3C6CF] focus-within:border-[#123356] focus-within:ring-2 focus-within:ring-[#123356]/20 bg-[#FAF9FC] transition-all">
                            <input
                                id="mobile-password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                placeholder="Masukkan kata sandi"
                                class="w-full h-12 pl-4 pr-11 bg-transparent border-none text-sm text-[#26292E] placeholder:text-[#8A8D96] focus:outline-none focus:bg-white"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 text-[#73777F] hover:text-[#26292E] p-1 flex items-center justify-center transition-colors"
                                :aria-label="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
                            >
                                <span class="material-symbols-outlined text-xl">
                                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                        <p v-if="form.errors.identifier && form.errors.identifier === 'Nomor HP atau kata sandi tidak cocok.'" class="text-xs text-[#BA1A1A] font-semibold mt-1">
                            {{ form.errors.identifier }}
                        </p>
                    </div>
                </div>

                <!-- Floating Mascot Illustration -->
                <div class="relative flex items-center justify-end pr-2 -my-2 pointer-events-none">
                    <img
                        src="/assets/mascot/mascot-pose-1.webp"
                        alt="Maskot SIGADIS"
                        class="w-28 sm:w-32 h-auto object-contain drop-shadow-lg transform translate-y-1"
                    />
                </div>

                <!-- Bottom CTA Button & Register Link -->
                <div class="space-y-3 pt-2">
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
                        <span>{{ form.processing ? 'Memproses Masuk...' : 'Masuk Sekarang' }}</span>
                        <span v-if="!form.processing" class="material-symbols-outlined text-xl">arrow_forward</span>
                    </button>

                    <div class="text-center">
                        <p class="text-xs text-[#73777F]">
                            Belum memiliki akun Bunda?
                            <Link
                                :href="route('mobile.register.show')"
                                class="font-bold text-[#E0703D] hover:underline ml-1"
                            >
                                Daftar di sini
                            </Link>
                        </p>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>
