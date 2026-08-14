<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';
import TaskHeader from '@/Components/Shared/TaskHeader.vue';

const props = defineProps({
    motherName: { type: String, required: true },
    consentActive: { type: Boolean, required: true },
    revokedAt: { type: String, default: null },
    deletionRequestedAt: { type: String, default: null },
    gpsPermissionEnabled: { type: Boolean, required: true },
    shareDataWithMidwifeEnabled: { type: Boolean, required: true },
});

const page = usePage();
const showRevokeConfirm = ref(false);
const showDeleteConfirm = ref(false);
const deleteConfirmationText = ref('');
const gpsEnabled = ref(props.gpsPermissionEnabled);
const shareDataEnabled = ref(props.shareDataWithMidwifeEnabled);

function toggleGps() {
    gpsEnabled.value = !gpsEnabled.value;
    router.post(route('kehamilan.privasi.gps-permission'), { enabled: gpsEnabled.value });
}

function toggleShareData() {
    shareDataEnabled.value = !shareDataEnabled.value;
    router.post(route('kehamilan.privasi.share-data-permission'), { enabled: shareDataEnabled.value });
}

function revokeConsent() {
    router.post(route('kehamilan.privasi.revoke-consent'));
    showRevokeConfirm.value = false;
}

function reactivateConsent() {
    router.post(route('kehamilan.privasi.reactivate-consent'));
}

function requestDeletion() {
    router.post(route('kehamilan.privasi.request-deletion'), { confirmation: deleteConfirmationText.value }, {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deleteConfirmationText.value = '';
        },
    });
}
</script>

<template>
    <Head title="Privasi & Data Saya" />

    <div class="min-h-screen bg-brand-pink-50">
        <TaskHeader title="Privasi & Data Saya" :back-href="route('kehamilan.beranda')" :show-close="false" />

        <div class="mx-auto w-full max-w-md px-6 py-6">
            <p v-if="page.props.flash?.success" class="mb-4 rounded-lg bg-risk-low-bg p-3 text-sm text-risk-low">
                {{ page.props.flash.success }}
            </p>

            <section class="mb-6 flex items-center gap-3 rounded-xl bg-brand-pink-200 p-4">
                <Icon name="shield" size="h-10 w-10" class="shrink-0 text-brand-navy-900" />
                <div>
                    <p class="font-bold text-brand-navy-900">Kontrol Penuh Atas Data Ibu</p>
                    <p class="text-sm text-brand-navy-700">Seluruh data medis dan lokasi dilindungi, hanya diakses oleh pihak yang berwenang.</p>
                </div>
            </section>

            <section class="mb-4 space-y-1 rounded-xl bg-white p-2 shadow-sm">
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center gap-3">
                        <Icon name="location" size="h-5 w-5" class="text-brand-navy-700" />
                        <div>
                            <p class="text-sm font-medium text-brand-navy-900">Izin Akses Lokasi GPS</p>
                            <p class="text-xs text-neutral-500">Dipakai saat Ibu menekan tombol darurat.</p>
                        </div>
                    </div>
                    <input type="checkbox" class="toggle shrink-0" :checked="gpsEnabled" @change="toggleGps" />
                </div>

                <div class="flex items-center justify-between border-t border-neutral-100 p-3">
                    <div class="flex items-center gap-3">
                        <Icon name="shield" size="h-5 w-5" class="text-brand-navy-700" />
                        <div>
                            <p class="text-sm font-medium text-brand-navy-900">Izin Berbagi Data ke Bidan</p>
                            <p class="text-xs text-neutral-500">Data keselamatan tetap sampai ke bidan meski dimatikan.</p>
                        </div>
                    </div>
                    <input type="checkbox" class="toggle shrink-0" :checked="shareDataEnabled" @change="toggleShareData" />
                </div>

                <a :href="route('kehamilan.privasi.export-data')" class="flex cursor-pointer items-center gap-3 border-t border-neutral-100 p-3 hover:bg-neutral-50">
                    <Icon name="document" size="h-5 w-5" class="text-brand-navy-700" />
                    <span class="text-sm font-medium text-brand-navy-900">Unduh Salinan Data Saya (PDF)</span>
                </a>
            </section>

            <section class="mb-4 rounded-xl bg-white p-2 shadow-sm">
                <button
                    v-if="consentActive"
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 p-3 text-left hover:bg-neutral-50"
                    @click="showRevokeConfirm = true"
                >
                    <Icon name="alert" size="h-5 w-5" class="text-risk-medium" />
                    <span class="text-sm font-medium text-risk-medium">Cabut Consent / Izin Layanan</span>
                </button>
                <div v-else class="flex items-center justify-between p-3">
                    <div>
                        <p class="text-sm font-medium text-neutral-900">Persetujuan dicabut</p>
                        <p class="text-xs text-neutral-500">
                            {{ revokedAt ? `Sejak ${new Date(revokedAt).toLocaleDateString('id-ID')}` : '' }}
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm border-none bg-brand-navy-900 text-white" @click="reactivateConsent">
                        Aktifkan Kembali
                    </button>
                </div>

                <button
                    v-if="!deletionRequestedAt"
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 border-t border-neutral-100 p-3 text-left hover:bg-neutral-50"
                    @click="showDeleteConfirm = true"
                >
                    <Icon name="trash" size="h-5 w-5" class="text-risk-high" />
                    <span class="text-sm font-medium text-risk-high">Minta Penghapusan Akun & Data</span>
                </button>
                <p v-else class="border-t border-neutral-100 p-3 text-sm text-neutral-600">
                    Permintaan penghapusan diterima pada {{ new Date(deletionRequestedAt).toLocaleDateString('id-ID') }}. Data Ibu sedang diproses.
                </p>
            </section>
        </div>

        <!-- Dialog cabut consent -->
        <div v-if="showRevokeConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
            <div class="w-full max-w-sm rounded-xl bg-white p-6">
                <div class="mb-4 flex justify-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-risk-high-bg">
                        <Icon name="alert" size="h-7 w-7" class="text-risk-high" />
                    </div>
                </div>
                <p class="mb-2 text-center text-lg font-bold text-brand-navy-900">Yakin Ingin Mencabut Consent?</p>
                <p class="mb-4 text-center text-sm text-neutral-600">
                    Mencabut consent akan menghentikan pemantauan otomatis dan pengiriman sinyal darurat SOS ke Bidan Pendamping.
                </p>
                <div class="mb-4 space-y-1 rounded-lg bg-risk-high-bg p-3 text-sm text-risk-high">
                    <p>&#9888; Sinyal SOS darurat tidak dapat terhubung otomatis.</p>
                    <p>&#9888; Bidan tidak lagi menerima pembaruan skrining.</p>
                </div>
                <div class="space-y-3">
                    <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="showRevokeConfirm = false">
                        Batalkan, Tetap Aktifkan Consent
                    </button>
                    <button type="button" class="btn btn-outline w-full border-risk-high text-risk-high" @click="revokeConsent">
                        Ya, Cabut Consent Saya
                    </button>
                </div>
            </div>
        </div>

        <!-- Dialog minta hapus data -->
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
            <div class="w-full max-w-sm rounded-xl bg-white p-6">
                <div class="mb-4 flex justify-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-risk-high-bg">
                        <Icon name="trash" size="h-7 w-7" class="text-risk-high" />
                    </div>
                </div>
                <p class="mb-2 text-center text-lg font-bold text-brand-navy-900">Hapus Seluruh Data &amp; Akun?</p>
                <p class="mb-4 text-center text-sm text-neutral-600">
                    Tindakan ini bersifat permanen. Seluruh riwayat skrining, profil kehamilan, dan koneksi ke Bidan
                    Pendamping akan dihapus.
                </p>
                <label class="mb-1 block text-sm font-medium text-brand-navy-900">Ketik "HAPUS" untuk mengonfirmasi:</label>
                <input v-model="deleteConfirmationText" type="text" class="input input-bordered mb-4 w-full" placeholder="HAPUS" />
                <div class="space-y-3">
                    <button type="button" class="btn w-full border-none bg-brand-navy-900 text-white" @click="showDeleteConfirm = false">
                        Batal &amp; Kembali ke Aplikasi
                    </button>
                    <button
                        type="button"
                        class="btn w-full border-none bg-risk-high text-white"
                        :disabled="deleteConfirmationText !== 'HAPUS'"
                        @click="requestDeletion"
                    >
                        Kirim Permintaan Hapus Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
