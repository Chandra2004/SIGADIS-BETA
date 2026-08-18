<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    userFullName: {
        type: String,
        default: '',
    },
    userPhone: {
        type: String,
        default: '',
    },
    midwives: {
        type: Array,
        default: () => [],
    },
});

const currentStep = ref(1); // 1: Consent, 2: Identity, 3: Conditions, 4: Midwife, 5: Review
const midwifeSearchQuery = ref('');
const isRefreshingMidwives = ref(false);

const form = useForm({
    mother_name: props.userFullName || '',
    gestational_age_weeks_at_registration: 12,
    estimated_due_date: '',
    is_twin_pregnancy: false,
    has_prior_cesarean: false,
    has_gestational_diabetes: false,
    has_chronic_hypertension: false,
    other_medical_conditions: '',
    medical_notes: '',
    midwife_id: null, // Kosongan by default
    consent_agreed: false,
});

const filteredMidwives = computed(() => {
    if (!midwifeSearchQuery.value.trim()) return props.midwives;
    const q = midwifeSearchQuery.value.toLowerCase();
    return props.midwives.filter(
        (m) =>
            m.name?.toLowerCase().includes(q) ||
            m.facility?.toLowerCase().includes(q) ||
            m.str_number?.toLowerCase().includes(q) ||
            m.region_code?.toLowerCase().includes(q)
    );
});

const selectedMidwife = computed(() => {
    if (!form.midwife_id) return null;
    return props.midwives.find((m) => m.id === form.midwife_id) || null;
});

// Auto-calculate HPL based on weeks or vice-versa
const onWeeksChange = () => {
    const weeks = parseInt(form.gestational_age_weeks_at_registration) || 12;
    const remainingDays = (40 - weeks) * 7;
    const d = new Date();
    d.setDate(d.getDate() + remainingDays);
    form.estimated_due_date = d.toISOString().split('T')[0];
};

const nextStep = () => {
    if (currentStep.value < 5) {
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const selectMidwife = (id) => {
    if (form.midwife_id === id) {
        form.midwife_id = null; // Batal pilih / uncheck
    } else {
        form.midwife_id = id;
    }
};

const refreshMidwives = () => {
    isRefreshingMidwives.value = true;
    router.reload({
        only: ['midwives'],
        preserveScroll: true,
        onFinish: () => {
            setTimeout(() => {
                isRefreshingMidwives.value = false;
            }, 600);
        },
    });
};

const submitRegistration = () => {
    form.post(route('mobile.pregnancy.register.store'));
};
</script>

<template>
    <div class="min-h-screen bg-[#FDF3F6] text-[#26292E] font-sans flex flex-col justify-between relative overflow-x-hidden select-none">
        <Head title="Daftar Profil Kehamilan — SIGADIS Mobile" />

        <!-- Top App Bar (Header Pendaftaran) -->
        <header class="sticky top-0 z-40 bg-[#FDF3F6]/95 backdrop-blur-md border-b border-[#F3AEC0]/30 px-4 h-16 flex items-center justify-between">
            <button
                v-if="currentStep > 1"
                @click="prevStep"
                type="button"
                class="w-10 h-10 rounded-full bg-white/80 border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center active:scale-95 transition-all cursor-pointer"
                aria-label="Kembali"
            >
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </button>
            <Link
                v-else
                :href="route('mobile.dashboard')"
                class="w-10 h-10 rounded-full bg-white/80 border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center active:scale-95 transition-all"
                aria-label="Batal"
            >
                <span class="material-symbols-outlined text-xl">close</span>
            </Link>

            <!-- Step Indicator -->
            <div class="text-center">
                <span class="text-[10px] font-extrabold text-[#73777F] uppercase tracking-wider block">Langkah {{ currentStep }} dari 5</span>
                <span class="text-xs font-bold text-[#123356]">
                    {{
                        currentStep === 1 ? 'Persetujuan Data' :
                        currentStep === 2 ? 'Identitas Kehamilan' :
                        currentStep === 3 ? 'Riwayat & Kondisi' :
                        currentStep === 4 ? 'Pilih Bidan' : 'Ringkasan & Daftar'
                    }}
                </span>
            </div>

            <div class="w-10 h-10"></div>
        </header>

        <!-- Progress Line Bar -->
        <div class="w-full bg-gray-200 h-1">
            <div
                class="bg-[#123356] h-full transition-all duration-300"
                :style="`width: ${(currentStep / 5) * 100}%`"
            ></div>
        </div>

        <!-- Main Form Scrollable Container (pb-28 agar konten tidak tertutup fixed bottom button) -->
        <main class="flex-1 max-w-md w-full mx-auto px-5 py-4 pb-28 flex flex-col">
            <!-- ========================================================= -->
            <!-- STEP 1: INFORMED CONSENT (informed-consent.html) -->
            <!-- ========================================================= -->
            <section v-if="currentStep === 1" class="space-y-4 animate-fade-in">
                <div class="text-center space-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-pink-100 text-[#854E5E] mx-auto flex items-center justify-center mb-2 shadow-xs">
                        <span class="material-symbols-outlined text-3xl font-bold">verified_user</span>
                    </div>
                    <h2 class="text-lg font-extrabold text-[#123356]">Persetujuan Privasi Data</h2>
                    <p class="text-xs text-[#73777F]">Kepatuhan Perlindungan Data Pribadi (UU PDP No. 27/2022)</p>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs text-xs text-[#26292E] space-y-3 leading-relaxed">
                    <p>
                        <strong>1. Tujuan Pengumpulan Data:</strong> Data medis yang Ibu masukkan digunakan untuk deteksi dini risiko kehamilan dan penerusan alert darurat maternal ke tenaga kesehatan.
                    </p>
                    <p>
                        <strong>2. Pihak yang Mengakses:</strong> Data hanya dapat diakses oleh Bidan Pendamping terverifikasi di wilayah Ibu dan administrator dinas kesehatan resmi.
                    </p>
                    <p>
                        <strong>3. Hak Pengguna:</strong> Ibu memiliki hak untuk mencabut persetujuan atau meminta penghapusan data medis kapan saja melalui menu Pengaturan.
                    </p>
                </div>

                <label class="flex items-start gap-3 p-4 bg-white rounded-2xl border border-[#F3AEC0]/40 cursor-pointer shadow-xs">
                    <input
                        type="checkbox"
                        v-model="form.consent_agreed"
                        class="mt-0.5 rounded border-gray-300 text-[#123356] focus:ring-[#123356]"
                    />
                    <span class="text-xs text-[#123356] font-semibold leading-relaxed">
                        Saya memahami dan menyetujui pemrosesan data kehamilan untuk tujuan pendampingan kesehatan maternal.
                    </span>
                </label>
            </section>

            <!-- ========================================================= -->
            <!-- STEP 2: IDENTITY & GESTATIONAL AGE (identity-and-gestational-age.html) -->
            <!-- ========================================================= -->
            <section v-else-if="currentStep === 2" class="space-y-4 animate-fade-in">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-extrabold text-[#123356]">Identitas & Usia Kehamilan</h2>
                    <p class="text-xs text-[#73777F]">Masukkan informasi dasar kehamilan Anda</p>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs space-y-4">
                    <!-- Nama Ibu Hamil -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-[#123356]">Nama Lengkap Ibu Hamil</label>
                        <input
                            v-model="form.mother_name"
                            type="text"
                            placeholder="Contoh: Anisa Maharani"
                            class="w-full p-3 bg-[#FDF3F6] rounded-xl border border-gray-200 text-xs font-semibold text-[#123356] focus:ring-2 focus:ring-[#123356]"
                        />
                    </div>

                    <!-- Usia Kehamilan (Minggu) -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-[#123356]">
                            <label>Usia Kehamilan Saat Ini</label>
                            <span class="text-[#E0703D] font-extrabold">{{ form.gestational_age_weeks_at_registration }} Minggu</span>
                        </div>
                        <input
                            v-model="form.gestational_age_weeks_at_registration"
                            @input="onWeeksChange"
                            type="range"
                            min="1"
                            max="42"
                            class="w-full accent-[#123356]"
                        />
                    </div>

                    <!-- Hari Perkiraan Lahir (HPL) -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-[#123356]">Hari Perkiraan Lahir (HPL)</label>
                        <input
                            v-model="form.estimated_due_date"
                            type="date"
                            class="w-full p-3 bg-[#FDF3F6] rounded-xl border border-gray-200 text-xs font-semibold text-[#123356]"
                        />
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- STEP 3: PRE-EXISTING CONDITIONS (pre-exiting-conditions.html) -->
            <!-- ========================================================= -->
            <section v-else-if="currentStep === 3" class="space-y-4 animate-fade-in">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-extrabold text-[#123356]">Riwayat & Kondisi Bawaan</h2>
                    <p class="text-xs text-[#73777F]">Pilih atau isi riwayat kesehatan khusus yang Ibu miliki</p>
                </div>

                <div class="space-y-2.5">
                    <label class="flex items-center justify-between p-3.5 bg-white rounded-2xl border border-gray-200 cursor-pointer hover:border-[#F3AEC0] shadow-xs">
                        <span class="text-xs font-bold text-[#123356]">Kehamilan Kembar (Twin Pregnancy)</span>
                        <input type="checkbox" v-model="form.is_twin_pregnancy" class="rounded text-[#123356]" />
                    </label>

                    <label class="flex items-center justify-between p-3.5 bg-white rounded-2xl border border-gray-200 cursor-pointer hover:border-[#F3AEC0] shadow-xs">
                        <span class="text-xs font-bold text-[#123356]">Pernah Operasi Caesar Sebelumnya</span>
                        <input type="checkbox" v-model="form.has_prior_cesarean" class="rounded text-[#123356]" />
                    </label>

                    <label class="flex items-center justify-between p-3.5 bg-white rounded-2xl border border-gray-200 cursor-pointer hover:border-[#F3AEC0] shadow-xs">
                        <span class="text-xs font-bold text-[#123356]">Riwayat Diabetes Saat Hamil (Gestasional)</span>
                        <input type="checkbox" v-model="form.has_gestational_diabetes" class="rounded text-[#123356]" />
                    </label>

                    <label class="flex items-center justify-between p-3.5 bg-white rounded-2xl border border-gray-200 cursor-pointer hover:border-[#F3AEC0] shadow-xs">
                        <span class="text-xs font-bold text-[#123356]">Riwayat Darah Tinggi (Hipertensi Kronis)</span>
                        <input type="checkbox" v-model="form.has_chronic_hypertension" class="rounded text-[#123356]" />
                    </label>

                    <!-- Opsi Kondisi Lainnya / Input Mandiri -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-xs space-y-2.5">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#123356] text-lg">medical_information</span>
                            <span class="text-xs font-bold text-[#123356]">Kondisi / Penyakit Bawaan Lainnya</span>
                        </div>
                        <p class="text-[11px] text-[#73777F]">
                            Tuliskan riwayat kesehatan lain jika ada (misal: Asma, Jantung, Alergi Obat, Anemia, Tiroid, dll).
                        </p>
                        <textarea
                            v-model="form.other_medical_conditions"
                            rows="2"
                            placeholder="Contoh: Asma ringan, Alergi antibiotik amoksisilin"
                            class="w-full p-3 bg-[#FDF3F6] rounded-xl border border-gray-200 text-xs font-medium text-[#123356] focus:ring-2 focus:ring-[#123356] placeholder:text-gray-400"
                        ></textarea>
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- STEP 4: MIDWIFE PAIRING SELECTION (KOSONGAN BY DEFAULT) -->
            <!-- ========================================================= -->
            <section v-else-if="currentStep === 4" class="space-y-4 animate-fade-in">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-extrabold text-[#123356]">Pilih Bidan Pendamping</h2>
                    <p class="text-xs text-[#73777F]">Pilih bidan sesuai wilayah Anda (atau biarkan kosong untuk otomatis)</p>
                </div>

                <!-- Search Filter Bidan & Tombol Refresh Data -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                        <input
                            v-model="midwifeSearchQuery"
                            type="text"
                            placeholder="Cari nama bidan atau faskes..."
                            class="w-full pl-10 pr-3 py-2.5 bg-white rounded-2xl border border-gray-200 text-xs text-[#123356] shadow-xs focus:ring-2 focus:ring-[#123356]"
                        />
                    </div>
                    <!-- Tombol Refresh Data Bidan -->
                    <button
                        type="button"
                        @click="refreshMidwives"
                        :disabled="isRefreshingMidwives"
                        class="p-2.5 rounded-2xl bg-white border border-gray-200 text-[#123356] hover:bg-[#FDF3F6] active:scale-95 transition-all shadow-xs shrink-0 cursor-pointer flex items-center justify-center"
                        title="Segarkan / Refresh Data Bidan"
                        aria-label="Refresh Data Bidan"
                    >
                        <span
                            class="material-symbols-outlined text-lg"
                            :class="{ 'animate-spin': isRefreshingMidwives }"
                        >
                            refresh
                        </span>
                    </button>
                </div>

                <!-- Status Pilihan Saat Ini -->
                <div class="p-3 bg-blue-50/70 rounded-2xl border border-blue-200/60 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#123356] text-lg">info</span>
                        <span class="text-[11px] text-[#123356] font-medium">
                            <strong v-if="selectedMidwife">Terpilih: {{ selectedMidwife.name }}</strong>
                            <span v-else>Belum ada bidan yang dipilih (Opsional).</span>
                        </span>
                    </div>
                    <button
                        v-if="form.midwife_id"
                        type="button"
                        @click="form.midwife_id = null"
                        class="text-[10px] font-bold text-red-600 hover:underline cursor-pointer"
                    >
                        Batalkan
                    </button>
                </div>

                <!-- Daftar Kartu Bidan -->
                <div class="space-y-2.5">
                    <div
                        v-for="midwife in filteredMidwives"
                        :key="midwife.id"
                        @click="selectMidwife(midwife.id)"
                        class="p-3.5 rounded-2xl border cursor-pointer flex items-center justify-between transition-all"
                        :class="form.midwife_id === midwife.id ? 'bg-[#FDF3F6] border-[#123356] ring-2 ring-[#123356] shadow-xs' : 'bg-white border-gray-200 hover:border-[#F3AEC0]'"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                                :class="form.midwife_id === midwife.id ? 'bg-[#123356] text-white' : 'bg-[#123356]/10 text-[#123356]'"
                            >
                                <span class="material-symbols-outlined text-lg">medical_services</span>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-[#123356]">{{ midwife.name }}</h4>
                                <p class="text-[10px] text-[#73777F]">{{ midwife.facility }} • STR: {{ midwife.str_number }}</p>
                            </div>
                        </div>
                        <div
                            class="w-6 h-6 rounded-full flex items-center justify-center border transition-all"
                            :class="form.midwife_id === midwife.id ? 'bg-[#123356] text-white border-[#123356]' : 'border-gray-300 bg-gray-50'"
                        >
                            <span v-if="form.midwife_id === midwife.id" class="material-symbols-outlined text-xs font-bold">check</span>
                        </div>
                    </div>

                    <div v-if="filteredMidwives.length === 0" class="text-center py-6 text-xs text-gray-400 bg-white rounded-2xl border border-gray-100">
                        Tidak ada bidan yang cocok dengan pencarian.
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- STEP 5: REGISTRATION SUMMARY (registration-summary-review.html) -->
            <!-- ========================================================= -->
            <section v-else class="space-y-4 animate-fade-in">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-extrabold text-[#123356]">Ringkasan Pendaftaran</h2>
                    <p class="text-xs text-[#73777F]">Periksa kembali data Anda sebelum disimpan</p>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs space-y-3 text-xs">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-[#73777F]">Nama Ibu:</span>
                        <strong class="text-[#123356]">{{ form.mother_name }}</strong>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-[#73777F]">Usia Kehamilan:</span>
                        <strong class="text-[#123356]">{{ form.gestational_age_weeks_at_registration }} Minggu</strong>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-[#73777F]">Hari Perkiraan Lahir:</span>
                        <strong class="text-[#123356]">{{ form.estimated_due_date || '-' }}</strong>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-[#73777F]">Bidan Pendamping:</span>
                        <strong class="text-[#123356]">
                            {{ selectedMidwife ? selectedMidwife.name : 'Dipilihkan Otomatis Sesuai Wilayah' }}
                        </strong>
                    </div>
                    <div class="space-y-1 pt-1">
                        <span class="text-[#73777F] block">Kondisi Khusus & Riwayat:</span>
                        <p class="font-bold text-[#123356] bg-gray-50 p-2.5 rounded-xl border border-gray-200 leading-relaxed">
                            {{ [
                                form.is_twin_pregnancy ? 'Kehamilan Kembar' : null,
                                form.has_prior_cesarean ? 'Riwayat Caesar' : null,
                                form.has_gestational_diabetes ? 'Diabetes Gestasional' : null,
                                form.has_chronic_hypertension ? 'Hipertensi Kronis' : null,
                                form.other_medical_conditions ? ('Lainnya: ' + form.other_medical_conditions) : null,
                            ].filter(Boolean).join(', ') || 'Tidak ada riwayat kondisi khusus' }}
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- ============================================================= -->
        <!-- FIXED BOTTOM ACTION BAR (Selalu di Bawah, Memberikan Ruang Konten) -->
        <!-- ============================================================= -->
        <div class="fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
            <div class="max-w-md mx-auto p-4">
                <!-- Tombol Step 1 -->
                <button
                    v-if="currentStep === 1"
                    type="button"
                    @click="nextStep"
                    :disabled="!form.consent_agreed"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] disabled:bg-gray-300 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all cursor-pointer"
                >
                    <span>Lanjutkan Pengisian</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>

                <!-- Tombol Step 2 -->
                <button
                    v-else-if="currentStep === 2"
                    type="button"
                    @click="nextStep"
                    :disabled="!form.mother_name"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] disabled:bg-gray-300 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all cursor-pointer"
                >
                    <span>Lanjut ke Riwayat Kesehatan</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>

                <!-- Tombol Step 3 -->
                <button
                    v-else-if="currentStep === 3"
                    type="button"
                    @click="nextStep"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all cursor-pointer"
                >
                    <span>Lanjut ke Pilih Bidan</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>

                <!-- Tombol Step 4 -->
                <button
                    v-else-if="currentStep === 4"
                    type="button"
                    @click="nextStep"
                    class="w-full py-3.5 px-4 rounded-2xl bg-[#123356] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md active:scale-98 transition-all cursor-pointer"
                >
                    <span>Tinjau Ringkasan Pendaftaran</span>
                    <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>

                <!-- Tombol Step 5 (Submit dengan Loading Spinner) -->
                <button
                    v-else
                    type="button"
                    @click="submitRegistration"
                    :disabled="form.processing"
                    class="w-full py-4 px-4 rounded-2xl bg-[#4C9A6E] hover:bg-emerald-700 disabled:bg-[#4C9A6E]/70 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-xl active:scale-98 transition-all cursor-pointer"
                >
                    <!-- Animated Loading Spinner saat proses pendaftaran -->
                    <span
                        v-if="form.processing"
                        class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin shrink-0"
                    ></span>
                    <span
                        v-else
                        class="material-symbols-outlined text-base"
                    >
                        check_circle
                    </span>
                    <span>{{ form.processing ? 'Menyimpan Profil Kehamilan...' : 'Daftarkan Profil Kehamilan' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
