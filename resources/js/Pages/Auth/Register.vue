<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

// Step: 'choose-role' | 'fill-form'
const step = ref('choose-role');

// Role: 'bidan_kader' | 'ibu_hamil'
const selectedRole = ref(null);

const showPassword = ref(false);
const showPasswordConfirm = ref(false);
const showIbuPassword = ref(false);
const showIbuPasswordConfirm = ref(false);

const isBidanKader = computed(() => selectedRole.value === 'bidan_kader');
const isIbuHamil = computed(() => selectedRole.value === 'ibu_hamil');

// Form Bidan / Kader
const bidanForm = useForm({
    full_name: '',
    phone_number: '',
    role: 'bidan',
    str_number: '',
    appointment_letter_ref: '',
    region_code: '33.08.05.2009',
    password: '',
    password_confirmation: '',
});

// Sub-role Bidan vs Kader
const staffSubRole = ref('bidan');
watch(staffSubRole, (val) => {
    bidanForm.role = val;
    if (val === 'bidan') {
        bidanForm.appointment_letter_ref = '';
    } else {
        bidanForm.str_number = '';
    }
});

// Form Ibu Hamil
const ibuForm = useForm({
    full_name: '',
    phone_number: '',
    password: '',
    password_confirmation: '',
});

const panelTitle = computed(() => {
    if (selectedRole.value === 'bidan_kader') return 'Portal Tenaga Kesehatan';
    if (selectedRole.value === 'ibu_hamil') return 'Mulai Perjalanan Kehamilan Anda';
    return 'Sistem Gawat Darurat Ibu-Selamat';
});

const panelDesc = computed(() => {
    if (selectedRole.value === 'bidan_kader')
        return 'Daftarkan diri sebagai tenaga kesehatan untuk mendampingi, memantau risiko, dan merespons kondisi darurat ibu hamil.';
    if (selectedRole.value === 'ibu_hamil')
        return 'Buat akun agar bidan dapat mendampingi perjalanan kehamilan Anda dan siap membantu saat kondisi darurat.';
    return 'Portal Bidan & Kader. Akses data maternal terpusat, pantau risiko tinggi, dan berikan respon cepat untuk keselamatan ibu dan bayi.';
});

const selectRole = (roleKey) => {
    selectedRole.value = roleKey;
    step.value = 'fill-form';
};

const backToChoose = () => {
    step.value = 'choose-role';
    selectedRole.value = null;
};

const normalizePhone = (num) => {
    let clean = (num || '').toString().replace(/\D/g, '');
    if (clean.startsWith('62')) {
        return '0' + clean.substring(2);
    }
    if (clean.startsWith('8')) {
        return '0' + clean;
    }
    return clean;
};

const submitBidan = () => {
    bidanForm.phone_number = normalizePhone(bidanForm.phone_number);
    bidanForm.post(route('auth.staff.register'), {
        onFinish: () => bidanForm.reset('password', 'password_confirmation'),
    });
};

const submitIbu = () => {
    ibuForm.phone_number = normalizePhone(ibuForm.phone_number);
    ibuForm.post(route('auth.pregnant.otp.send'), {
        onFinish: () => ibuForm.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Pendaftaran Akun — SIGADIS" />

    <AuthLayout
        panel-eyebrow="Registrasi Platform"
        :panel-title="panelTitle"
        :panel-description="panelDesc"
    >
        <!-- ===== STEP 1: PILIH PERAN ===== -->
        <div v-if="step === 'choose-role'">
            <div class="text-center md:text-left mb-8">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-[#26292E] tracking-tight mb-2">
                    Buat Akun Baru
                </h1>
                <p class="text-sm text-[#43474E]">
                    Pilih peran Anda untuk melanjutkan pendaftaran di SIGADIS.
                </p>
            </div>

            <!-- Role Selection Cards -->
            <div class="space-y-4">
                <!-- Bidan / Kader Card -->
                <button
                    type="button"
                    class="group flex w-full items-center gap-4 rounded-2xl border-2 border-[#C3C6CF] bg-[#FAF9FC] p-5 text-left transition-all hover:border-[#2C4A6E] hover:bg-white hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#2C4A6E]/30"
                    @click="selectRole('bidan_kader')"
                >
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#2C4A6E]/10 text-[#2C4A6E] transition-all group-hover:bg-[#2C4A6E] group-hover:text-white shadow-sm">
                        <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">medical_services</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#26292E] text-base group-hover:text-[#2C4A6E] transition-colors">Bidan / Kader</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-[#2C4A6E]/10 text-[#2C4A6E] rounded-md">Nakes</span>
                        </div>
                        <p class="mt-1 text-xs text-[#43474E] leading-relaxed">
                            Tenaga kesehatan yang memantau kondisi dan mendampingi ibu hamil.
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-[#73777F] transition-transform group-hover:translate-x-1 group-hover:text-[#2C4A6E]">chevron_right</span>
                </button>

                <!-- Ibu Hamil Card -->
                <button
                    type="button"
                    class="group flex w-full items-center gap-4 rounded-2xl border-2 border-[#C3C6CF] bg-[#FAF9FC] p-5 text-left transition-all hover:border-[#F3AEC0] hover:bg-white hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#F3AEC0]/40"
                    @click="selectRole('ibu_hamil')"
                >
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#F3AEC0]/30 text-[#854E5E] transition-all group-hover:bg-[#F3AEC0] group-hover:text-[#26292E] shadow-sm">
                        <span class="material-symbols-outlined text-3xl" style="font-variation-settings:'FILL' 1">pregnant_woman</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-[#26292E] text-base group-hover:text-[#854E5E] transition-colors">Ibu Hamil</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-[#F3AEC0]/30 text-[#854E5E] rounded-md">Ibu</span>
                        </div>
                        <p class="mt-1 text-xs text-[#43474E] leading-relaxed">
                            Ibu hamil yang ingin memantau kondisi dan mendapatkan respon darurat.
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-2xl text-[#73777F] transition-transform group-hover:translate-x-1 group-hover:text-[#854E5E]">chevron_right</span>
                </button>
            </div>

            <!-- Login Link -->
            <p class="mt-8 text-center text-sm text-[#43474E]">
                Sudah memiliki akun terdaftar?
                <Link
                    :href="route('auth.staff.login.show')"
                    class="font-semibold text-[#2C4A6E] hover:text-[#3D6086] underline underline-offset-2 transition-colors ml-1"
                >
                    Masuk di sini
                </Link>
            </p>
        </div>

        <!-- ===== STEP 2A: FORMULIR REGISTRASI BIDAN / KADER ===== -->
        <div v-else-if="step === 'fill-form' && isBidanKader">
            <!-- Back Button -->
            <button
                type="button"
                class="mb-6 inline-flex items-center gap-1.5 text-xs font-semibold text-[#2C4A6E] hover:text-[#3D6086] transition-colors"
                @click="backToChoose"
            >
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Ganti Pilihan Peran
            </button>

            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-[#26292E] tracking-tight mb-1">
                    Daftar Bidan / Kader
                </h1>
                <p class="text-xs text-[#43474E]">
                    Lengkapi data kredensial Anda. Akun akan ditinjau oleh Admin Faskes.
                </p>
            </div>

            <!-- Sub-Role Switcher (Bidan vs Kader) -->
            <div class="mb-6 flex rounded-xl border border-[#C3C6CF] bg-[#F4F3F6] p-1">
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition-all',
                        staffSubRole === 'bidan'
                            ? 'bg-[#2C4A6E] text-white shadow-md'
                            : 'text-[#43474E] hover:text-[#26292E]'
                    ]"
                    @click="staffSubRole = 'bidan'"
                >
                    <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1">stethoscope</span>
                    Bidan
                </button>
                <button
                    type="button"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2 rounded-lg py-2.5 text-xs font-bold transition-all',
                        staffSubRole === 'kader'
                            ? 'bg-[#2C4A6E] text-white shadow-md'
                            : 'text-[#43474E] hover:text-[#26292E]'
                    ]"
                    @click="staffSubRole = 'kader'"
                >
                    <span class="material-symbols-outlined text-base" style="font-variation-settings:'FILL' 1">diversity_1</span>
                    Kader Posyandu
                </button>
            </div>

            <form class="space-y-4" @submit.prevent="submitBidan">
                
                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="bidan-fullname">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                            <span class="material-symbols-outlined text-lg">person</span>
                        </div>
                        <input
                            id="bidan-fullname"
                            v-model="bidanForm.full_name"
                            type="text"
                            autocomplete="name"
                            required
                            autofocus
                            placeholder="Nama lengkap sesuai dokumen resmi"
                            class="block w-full pl-10 pr-4 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                        />
                    </div>
                    <p v-if="bidanForm.errors.full_name" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ bidanForm.errors.full_name }}
                    </p>
                </div>

                <!-- Nomor Handphone -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="bidan-phone">
                        Nomor Handphone (WhatsApp)
                    </label>
                    <div class="flex overflow-hidden rounded-xl border border-[#C3C6CF] focus-within:border-[#2C4A6E] focus-within:ring-2 focus-within:ring-[#2C4A6E]/20 transition-all">
                        <span class="flex items-center bg-[#F4F3F6] px-3.5 text-xs font-bold text-[#43474E] border-r border-[#C3C6CF]">
                            +62
                        </span>
                        <input
                            id="bidan-phone"
                            v-model="bidanForm.phone_number"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                            placeholder="81234567890"
                            class="flex-1 h-11 bg-[#FAF9FC] px-4 text-sm text-[#26292E] placeholder:text-[#8A8D96] border-none focus:outline-none focus:bg-white"
                        />
                    </div>
                    <p v-if="bidanForm.errors.phone_number" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ bidanForm.errors.phone_number }}
                    </p>
                </div>

                <!-- STR (Bidan) -->
                <div v-if="staffSubRole === 'bidan'" class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="bidan-str">
                        Nomor STR (Surat Tanda Registrasi)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                            <span class="material-symbols-outlined text-lg">badge</span>
                        </div>
                        <input
                            id="bidan-str"
                            v-model="bidanForm.str_number"
                            type="text"
                            required
                            placeholder="Contoh: STR-0001-2026"
                            class="block w-full pl-10 pr-4 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                        />
                    </div>
                    <p v-if="bidanForm.errors.str_number" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ bidanForm.errors.str_number }}
                    </p>
                </div>

                <!-- SK Desa (Kader) -->
                <div v-if="staffSubRole === 'kader'" class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="kader-sk">
                        Nomor SK / Surat Keputusan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                            <span class="material-symbols-outlined text-lg">assignment</span>
                        </div>
                        <input
                            id="kader-sk"
                            v-model="bidanForm.appointment_letter_ref"
                            type="text"
                            required
                            placeholder="Contoh: SK-DESA-0007-2026"
                            class="block w-full pl-10 pr-4 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                        />
                    </div>
                    <p v-if="bidanForm.errors.appointment_letter_ref" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ bidanForm.errors.appointment_letter_ref }}
                    </p>
                </div>

                <!-- Password Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Kata Sandi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#26292E]" for="bidan-password">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#73777F]">
                                <span class="material-symbols-outlined text-lg">lock</span>
                            </div>
                            <input
                                id="bidan-password"
                                v-model="bidanForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Min. 8 karakter"
                                class="block w-full pl-9 pr-9 py-2 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-xs placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#73777F] hover:text-[#26292E]"
                                @click="showPassword = !showPassword"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ showPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#26292E]" for="bidan-password-confirm">
                            Konfirmasi Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#73777F]">
                                <span class="material-symbols-outlined text-lg">lock_reset</span>
                            </div>
                            <input
                                id="bidan-password-confirm"
                                v-model="bidanForm.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Ulangi sandi"
                                class="block w-full pl-9 pr-9 py-2 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-xs placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#73777F] hover:text-[#26292E]"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ showPasswordConfirm ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <p v-if="bidanForm.errors.password" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                    {{ bidanForm.errors.password }}
                </p>

                <!-- Information Notice -->
                <div class="flex items-start gap-3 rounded-xl bg-[#ABC9F3]/20 border border-[#ABC9F3]/40 p-3.5 mt-2">
                    <span class="material-symbols-outlined text-[#2C4A6E] text-lg shrink-0 mt-0.5">info</span>
                    <p class="text-xs text-[#2C4A6E] leading-relaxed">
                        Setelah mendaftar, akun Anda akan berstatus <strong>Menunggu Verifikasi</strong> oleh admin Puskesmas/Dinkes setempat sebelum dapat membuka dashboard.
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="bidanForm.processing"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C4A6E] h-12 transition-all disabled:opacity-60 mt-4"
                >
                    <svg
                        v-if="bidanForm.processing"
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Daftar Sekarang</span>
                </button>

                <p class="text-center text-xs text-[#43474E] pt-2">
                    Sudah memiliki akun?
                    <Link :href="route('auth.staff.login.show')" class="font-semibold text-[#2C4A6E] hover:underline ml-1">
                        Masuk di sini
                    </Link>
                </p>
            </form>
        </div>

        <!-- ===== STEP 2B: FORMULIR REGISTRASI IBU HAMIL ===== -->
        <div v-else-if="step === 'fill-form' && isIbuHamil">
            <!-- Back Button -->
            <button
                type="button"
                class="mb-6 inline-flex items-center gap-1.5 text-xs font-semibold text-[#2C4A6E] hover:text-[#3D6086] transition-colors"
                @click="backToChoose"
            >
                <span class="material-symbols-outlined text-base">arrow_back</span>
                Ganti Pilihan Peran
            </button>

            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold text-[#26292E] tracking-tight mb-1">
                    Daftar Akun Ibu Hamil
                </h1>
                <p class="text-xs text-[#43474E]">
                    Masukkan nomor WhatsApp Anda untuk menerima kode verifikasi instan.
                </p>
            </div>

            <form class="space-y-4" @submit.prevent="submitIbu">
                
                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="ibu-fullname">
                        Nama Lengkap
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                            <span class="material-symbols-outlined text-lg">person</span>
                        </div>
                        <input
                            id="ibu-fullname"
                            v-model="ibuForm.full_name"
                            type="text"
                            autocomplete="name"
                            required
                            autofocus
                            placeholder="Nama lengkap Ibu"
                            class="block w-full pl-10 pr-4 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                        />
                    </div>
                    <p v-if="ibuForm.errors.full_name" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ ibuForm.errors.full_name }}
                    </p>
                </div>

                <!-- Nomor Handphone -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-[#26292E]" for="ibu-phone">
                        Nomor Handphone (WhatsApp)
                    </label>
                    <div class="flex overflow-hidden rounded-xl border border-[#C3C6CF] focus-within:border-[#2C4A6E] focus-within:ring-2 focus-within:ring-[#2C4A6E]/20 transition-all">
                        <span class="flex items-center bg-[#F4F3F6] px-3.5 text-xs font-bold text-[#43474E] border-r border-[#C3C6CF]">
                            +62
                        </span>
                        <input
                            id="ibu-phone"
                            v-model="ibuForm.phone_number"
                            type="tel"
                            inputmode="numeric"
                            autocomplete="tel"
                            required
                            placeholder="81234567890"
                            class="flex-1 h-11 bg-[#FAF9FC] px-4 text-sm text-[#26292E] placeholder:text-[#8A8D96] border-none focus:outline-none focus:bg-white"
                        />
                    </div>
                    <p v-if="ibuForm.errors.phone_number" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                        {{ ibuForm.errors.phone_number }}
                    </p>
                </div>

                <!-- Kata Sandi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#26292E]" for="ibu-password">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                                <span class="material-symbols-outlined text-lg">lock</span>
                            </div>
                            <input
                                id="ibu-password"
                                v-model="ibuForm.password"
                                :type="showIbuPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Min. 8 karakter"
                                class="block w-full pl-10 pr-10 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#73777F] hover:text-[#26292E] focus:outline-none"
                                @click="showIbuPassword = !showIbuPassword"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ showIbuPassword ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-[#26292E]" for="ibu-password-confirm">
                            Konfirmasi Sandi
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#73777F]">
                                <span class="material-symbols-outlined text-lg">lock_reset</span>
                            </div>
                            <input
                                id="ibu-password-confirm"
                                v-model="ibuForm.password_confirmation"
                                :type="showIbuPasswordConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                placeholder="Ulangi kata sandi"
                                class="block w-full pl-10 pr-10 py-2.5 border border-[#C3C6CF] rounded-xl bg-[#FAF9FC] text-[#26292E] text-sm placeholder:text-[#8A8D96] transition-all focus:bg-white focus:border-[#2C4A6E] focus:ring-2 focus:ring-[#2C4A6E]/20 focus:outline-none h-11"
                            />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#73777F] hover:text-[#26292E] focus:outline-none"
                                @click="showIbuPasswordConfirm = !showIbuPasswordConfirm"
                            >
                                <span class="material-symbols-outlined text-lg">
                                    {{ showIbuPasswordConfirm ? 'visibility_off' : 'visibility' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
                <p v-if="ibuForm.errors.password" class="text-xs text-[#BA1A1A] mt-1 font-medium">
                    {{ ibuForm.errors.password }}
                </p>

                <!-- Notice OTP -->
                <div class="flex items-start gap-3 rounded-xl bg-pink-50 border border-pink-200 p-3.5 mt-2">
                    <span class="material-symbols-outlined text-[#854E5E] text-lg shrink-0 mt-0.5" style="font-variation-settings:'FILL' 1">sms</span>
                    <p class="text-xs text-[#854E5E] leading-relaxed">
                        Kode 6-digit verifikasi OTP akan dikirimkan otomatis ke nomor <strong>WhatsApp</strong> Anda untuk mengonfirmasi pendaftaran.
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="ibuForm.processing"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md font-semibold text-sm text-white bg-[#2C4A6E] hover:bg-[#3D6086] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2C4A6E] h-12 transition-all disabled:opacity-60 mt-4"
                >
                    <svg
                        v-if="ibuForm.processing"
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Kirim Kode OTP</span>
                </button>

                <p class="text-center text-xs text-[#43474E] pt-2">
                    Sudah memiliki akun?
                    <Link :href="route('auth.staff.login.show')" class="font-semibold text-[#2C4A6E] hover:underline ml-1">
                        Masuk di sini
                    </Link>
                </p>
            </form>
        </div>
    </AuthLayout>
</template>
