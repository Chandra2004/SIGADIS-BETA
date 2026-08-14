<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    // Naikkan posisi FAB kalau halaman juga menampilkan BottomTabBar, biar tidak tumpang tindih.
    raised: { type: Boolean, default: false },
});

const showConfirm = ref(false);
const sending = ref(false);

/**
 * Privasi & Data Saya (toggle GPS) hanya soal preferensi tampilan/consent —
 * kalau browser tolak izin atau timeout, SOS tetap terkirim tanpa lokasi
 * (backend menerima latitude/longitude nullable), jangan sampai lokasi
 * yang gagal memblokir sinyal darurat.
 */
function send() {
    if (sending.value) return;
    sending.value = true;

    if (!navigator.geolocation) {
        submit();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => submit(position.coords.latitude, position.coords.longitude),
        () => submit(),
        { timeout: 5000 },
    );
}

function submit(latitude = null, longitude = null) {
    showConfirm.value = false;
    router.post(
        route('darurat.aktivasi'),
        { latitude, longitude },
        { onFinish: () => (sending.value = false) },
    );
}

// Beranda §3.6.2: grid aksi cepat "Darurat" memicu dialog konfirmasi yang sama, bukan tombol terpisah.
defineExpose({ open: () => (showConfirm.value = true) });
</script>

<template>
    <button
        type="button"
        aria-label="Aktivasi darurat"
        class="btn btn-circle fixed right-5 z-50 h-16 w-16 border-none bg-emergency-alert text-2xl text-white shadow-lg"
        :class="raised ? 'bottom-20' : 'bottom-5'"
        @click="showConfirm = true"
    >
        !
    </button>

    <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-6">
        <div class="w-full max-w-sm rounded-xl bg-white p-6 text-center">
            <img src="/assets/images/mascot/pose-09-terbang-siaga.png" alt="" class="mx-auto mb-4 h-24 w-24 object-contain" />
            <p class="mb-6 text-base font-semibold text-neutral-900">
                Ibu yakin ingin mengirim peringatan darurat ke bidan &amp; kader sekarang?
            </p>
            <div class="space-y-3">
                <button type="button" class="btn w-full border-none bg-emergency-alert text-white" :disabled="sending" @click="send">
                    {{ sending ? 'Mengirim...' : 'Ya, Kirim Sekarang' }}
                </button>
                <button type="button" class="btn btn-ghost w-full" :disabled="sending" @click="showConfirm = false">Batal</button>
            </div>
        </div>
    </div>
</template>
