<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import BidanLayout from '@/Layouts/BidanLayout.vue';

const props = defineProps({
    worker: {
        type: Object,
        required: true,
    },
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.post(route('bidan.profile.update-password'), {
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Profil Tenaga Medis — SIGADIS Nakes" />

    <BidanLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- 1. Header Section -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-[#123356] text-xs font-bold border border-blue-200">
                    <span class="material-symbols-outlined text-sm">badge</span>
                    <span>Kredensial & Pengaturan Akun Nakes</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123356] tracking-tight">
                    Profil Tenaga Medis
                </h1>
                <p class="text-xs sm:text-sm text-[#43474E]">
                    Informasi identitas profesi, status verifikasi administrasi kesehatan, dan pengaturan keamanan kata sandi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 2. Profile Details Card -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#123356] to-[#2C4A6E] text-white text-2xl font-black flex items-center justify-center shadow-md">
                            {{ worker.full_name?.charAt(0) || 'N' }}
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-200">
                                {{ worker.role }} Terverifikasi
                            </span>
                            <h2 class="text-lg font-black text-[#123356] mt-0.5">{{ worker.full_name }}</h2>
                            <p class="text-xs text-[#73777F] font-mono">{{ worker.phone_number }}</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-xs pt-4 border-t border-[#F2F3F5]">
                        <div class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5]">
                            <span class="text-[11px] text-[#73777F] font-bold block uppercase mb-1">
                                {{ worker.role === 'bidan' ? 'Nomor STR (Surat Tanda Registrasi)' : 'Nomor SK Pengangkatan Kader' }}
                            </span>
                            <strong class="font-mono text-[#123356] text-sm">
                                {{ worker.role === 'bidan' ? (worker.str_number || '-') : (worker.appointment_letter_ref || '-') }}
                            </strong>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5]">
                            <span class="text-[11px] text-[#73777F] font-bold block uppercase mb-1">Wilayah Penugasan</span>
                            <strong class="text-[#123356] text-sm">{{ worker.region_code || 'Puskesmas Kecamatan' }}</strong>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-[#FAF9FC] border border-[#E3E2E5]">
                            <span class="text-[11px] text-[#73777F] font-bold block uppercase mb-1">Status Kesiapan Darurat</span>
                            <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold', worker.is_available ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800']">
                                <span :class="['w-2 h-2 rounded-full', worker.is_available ? 'bg-emerald-600' : 'bg-rose-600']"></span>
                                <span>{{ worker.is_available ? 'Sedang Bertugas' : 'Sedang Cuti' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Change Password Card -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-[#E3E2E5] shadow-xs space-y-4">
                    <h3 class="font-extrabold text-base text-[#123356] flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">lock_reset</span>
                        <span>Perbarui Kata Sandi</span>
                    </h3>
                    <p class="text-xs text-[#73777F]">
                        Gunakan kombinasi kata sandi yang kuat untuk menjaga kerahasiaan data medis pasien binaan.
                    </p>

                    <form @submit.prevent="submitPassword" class="space-y-4 pt-2">
                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Kata Sandi Saat Ini</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                            <span v-if="passwordForm.errors.current_password" class="text-rose-600 text-[11px] block">
                                {{ passwordForm.errors.current_password }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Kata Sandi Baru</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                required
                                minlength="6"
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                            <span v-if="passwordForm.errors.password" class="text-rose-600 text-[11px] block">
                                {{ passwordForm.errors.password }}
                            </span>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-extrabold text-[#26292E]">Konfirmasi Kata Sandi Baru</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                minlength="6"
                                class="w-full p-2.5 rounded-xl border border-[#C3C6CF] bg-[#FAF9FC] text-xs focus:bg-white focus:border-[#123356] focus:outline-none"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="w-full py-2.5 rounded-xl bg-[#123356] hover:bg-[#2C4A6E] text-white text-xs font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50"
                        >
                            Simpan Perubahan Kata Sandi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </BidanLayout>
</template>
