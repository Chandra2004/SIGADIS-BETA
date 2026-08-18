<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MobileLayout from '@/Layouts/MobileLayout.vue';

const props = defineProps({
    motherName: {
        type: String,
        default: 'Ibu Hamil',
    },
    consent: {
        type: Object,
        default: () => ({}),
    },
    consentActive: {
        type: Boolean,
        default: true,
    },
});

const showRevokeModal = ref(false);
const showReactivateModal = ref(false);
const showDeleteModal = ref(false);
const confirmInput = ref('');

const confirmRevoke = () => {
    showRevokeModal.value = false;
    router.post(route('mobile.privacy.revoke'), {}, {
        preserveScroll: true,
    });
};

const confirmReactivate = () => {
    showReactivateModal.value = false;
    router.post(route('mobile.privacy.reactivate'), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    if (confirmInput.value !== 'HAPUS') return;
    showDeleteModal.value = false;
    router.post(route('mobile.privacy.delete'), {
        confirmation: 'HAPUS',
    });
};
</script>

<template>
    <MobileLayout
        title="Manajemen Privasi & Data — SIGADIS Mobile"
        activeTab="settings"
        :motherName="motherName"
    >
        <div class="space-y-4">
            <!-- Header Judul & Back to Settings -->
            <div class="pt-1 pb-1 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-[#123356] tracking-tight">
                        Manajemen Privasi & Hak Data
                    </h1>
                    <p class="text-xs text-[#73777F]">
                        Kepatuhan UU PDP No. 27/2022 & Pengaturan Consent
                    </p>
                </div>
                <Link
                    :href="route('mobile.settings.index')"
                    class="w-9 h-9 rounded-full bg-white border border-[#F3AEC0]/40 text-[#123356] flex items-center justify-center shadow-xs hover:bg-[#FDF3F6] active:scale-90 transition-all shrink-0 ml-2"
                    aria-label="Kembali ke Pengaturan"
                >
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                </Link>
            </div>

            <!-- Header Edukasi UU PDP -->
            <div class="bg-white rounded-3xl p-5 border border-[#F3AEC0]/40 shadow-xs space-y-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#123356]">security</span>
                    <h3 class="text-xs font-extrabold text-[#123356] uppercase tracking-wider">
                        Kepatuhan UU PDP No. 27/2022
                    </h3>
                </div>
                <p class="text-xs text-[#73777F] leading-relaxed">
                    SIGADIS menjamin hak privasi dan perlindungan penuh atas data medis kesehatan maternal Anda. Data Anda hanya diproses untuk deteksi dini risiko kehamilan dan koordinasi gawat darurat bersama Bidan resmi.
                </p>
            </div>

            <!-- Status Persetujuan Aktif -->
            <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-xs space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <span class="text-xs font-bold text-[#123356]">Status Persetujuan Data (Consent)</span>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-flex items-center gap-1"
                        :class="consent?.is_revoked ? 'bg-amber-100 text-[#E0703D]' : 'bg-emerald-100 text-[#4C9A6E]'"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="consent?.is_revoked ? 'bg-[#E0703D]' : 'bg-[#4C9A6E]'"></span>
                        {{ consent?.is_revoked ? 'Dicabut' : 'Aktif & Sah' }}
                    </span>
                </div>

                <div class="text-xs text-[#73777F] space-y-1">
                    <p>Versi Dokumen: <strong class="text-[#123356]">v{{ consent?.version || '1.0' }}</strong></p>
                    <p v-if="consent?.granted_at">
                        Disetujui Pada: <strong class="text-[#123356]">{{ consent.granted_at }}</strong>
                    </p>
                    <p v-if="consent?.is_revoked" class="text-amber-700 font-semibold pt-1">
                        Persetujuan dicabut pada: {{ consent?.revoked_at }}
                    </p>
                </div>
            </div>

            <!-- Status Banner Jika Consent Dicabut -->
            <div
                v-if="consent?.is_revoked"
                class="bg-amber-50 rounded-2xl p-4 border border-amber-200 text-xs text-amber-900 space-y-2"
            >
                <div class="flex items-center gap-2 font-bold text-amber-800">
                    <span class="material-symbols-outlined text-lg">info</span>
                    <span>Fitur Skrining Dinonaktifkan Sementara</span>
                </div>
                <p class="text-[11px] leading-relaxed">
                    Karena persetujuan telah dicabut, sistem menonaktifkan fitur skrining mandiri rutin. Tombol darurat SOS tetap aktif untuk keselamatan Anda. Anda dapat mengaktifkan persetujuan kembali kapan saja.
                </p>
                <button
                    @click="showReactivateModal = true"
                    class="mt-1 w-full py-2.5 px-3 rounded-xl bg-[#4C9A6E] hover:bg-[#3d7d59] text-white font-bold text-xs shadow-xs active:scale-98 transition-all flex items-center justify-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    <span>Aktifkan Kembali Persetujuan</span>
                </button>
            </div>

            <!-- Aksi 1: Cabut Persetujuan (Hanya jika sedang aktif) -->
            <div
                v-else
                class="bg-white rounded-3xl p-5 border border-amber-100 shadow-xs space-y-2"
            >
                <h4 class="text-xs font-extrabold text-[#123356]">Cabut Persetujuan Pemrosesan Data</h4>
                <p class="text-xs text-[#73777F] leading-relaxed">
                    Jika dicabut, sistem akan menghentikan pemrosesan data mandiri dan menonaktifkan fitur pengisian skrining berkala untuk profil kehamilan ini.
                </p>
                <button
                    @click="showRevokeModal = true"
                    class="mt-2 py-2.5 px-4 rounded-xl bg-amber-50 hover:bg-amber-100 text-[#E0703D] font-bold text-xs border border-amber-200 active:scale-98 transition-all flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">cancel</span>
                    <span>Cabut Persetujuan (Consent)</span>
                </button>
            </div>

            <!-- Aksi 2: Minta Hapus Akun & Data Pribadi (Self Account Deletion) -->
            <div class="bg-white rounded-3xl p-5 border border-red-100 shadow-xs space-y-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#D64550]">delete_forever</span>
                    <h4 class="text-xs font-extrabold text-[#D64550]">Hapus Akun & Seluruh Data Pribadi</h4>
                </div>
                <p class="text-xs text-[#73777F] leading-relaxed">
                    Menghapus akun Anda secara otomatis beserta seluruh profil kehamilan, riwayat skrining, dan data pribadi dari sistem SIGADIS. Anda akan otomatis keluar dari aplikasi.
                </p>
                <button
                    @click="showDeleteModal = true"
                    class="mt-2 py-2.5 px-4 rounded-xl bg-red-50 hover:bg-red-100 text-[#D64550] font-bold text-xs border border-red-200 active:scale-98 transition-all flex items-center gap-1.5"
                >
                    <span class="material-symbols-outlined text-base">delete</span>
                    <span>Hapus Akun & Data Saya</span>
                </button>
            </div>
        </div>

        <!-- MODAL 1: KONFIRMASI CABUT CONSENT -->
        <div
            v-if="showRevokeModal"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
            @click.self="showRevokeModal = false"
        >
            <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-amber-100 text-[#E0703D] mx-auto flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-2xl font-bold">cancel</span>
                </div>
                <h3 class="text-base font-extrabold text-[#123356] mb-1">Cabut Persetujuan?</h3>
                <p class="text-xs text-[#73777F] mb-4 leading-relaxed">
                    Sistem tidak dapat lagi memproses hasil skrining mandiri Anda. Data riwayat terdahulu tetap diarsipkan secara aman untuk kebutuhan medis.
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        @click="confirmRevoke"
                        class="w-full py-3 px-4 bg-[#E0703D] hover:bg-[#c95b28] text-white font-bold rounded-xl shadow-md transition-all"
                    >
                        Ya, Cabut Persetujuan
                    </button>
                    <button
                        @click="showRevokeModal = false"
                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#26292E] font-semibold rounded-xl transition-all"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 2: KONFIRMASI AKTIFKAN KEMBALI CONSENT -->
        <div
            v-if="showReactivateModal"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
            @click.self="showReactivateModal = false"
        >
            <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-emerald-100 text-[#4C9A6E] mx-auto flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-2xl font-bold">check_circle</span>
                </div>
                <h3 class="text-base font-extrabold text-[#123356] mb-1">Aktifkan Kembali Persetujuan?</h3>
                <p class="text-xs text-[#73777F] mb-4 leading-relaxed">
                    Dengan mengaktifkan persetujuan, Anda menyetujui pemrosesan data kesehatan maternal untuk deteksi dini risiko kehamilan bersama Bidan pendamping.
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        @click="confirmReactivate"
                        class="w-full py-3 px-4 bg-[#4C9A6E] hover:bg-[#3d7d59] text-white font-bold rounded-xl shadow-md transition-all"
                    >
                        Ya, Aktifkan Persetujuan
                    </button>
                    <button
                        @click="showReactivateModal = false"
                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#26292E] font-semibold rounded-xl transition-all"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL 3: KONFIRMASI HAPUS AKUN & DATA (SELF-DELETION) -->
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
            @click.self="showDeleteModal = false"
        >
            <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-red-100 text-[#D64550] mx-auto flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-2xl font-bold">delete_forever</span>
                </div>
                <h3 class="text-base font-extrabold text-[#123356] mb-1">Hapus Akun & Seluruh Data?</h3>
                <p class="text-xs text-[#73777F] mb-3 leading-relaxed">
                    Tindakan ini permanen. Akun dan seluruh rekam data kehamilan Anda akan dihapus dari aplikasi. Ketik kata <strong class="text-red-600 font-black">HAPUS</strong> untuk mengonfirmasi:
                </p>
                <input
                    v-model="confirmInput"
                    type="text"
                    placeholder="Ketik HAPUS"
                    class="w-full p-3 rounded-xl border border-gray-300 text-center font-black tracking-widest text-sm mb-4 focus:ring-2 focus:ring-red-500 uppercase"
                />
                <div class="flex flex-col gap-2">
                    <button
                        @click="confirmDelete"
                        :disabled="confirmInput !== 'HAPUS'"
                        class="w-full py-3 px-4 bg-[#D64550] hover:bg-red-700 disabled:bg-gray-300 text-white font-bold rounded-xl shadow-md transition-all"
                    >
                        Konfirmasi Hapus Akun & Data
                    </button>
                    <button
                        @click="showDeleteModal = false"
                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-[#26292E] font-semibold rounded-xl transition-all"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </MobileLayout>
</template>
