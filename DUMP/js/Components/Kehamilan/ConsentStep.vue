<script setup>
import { ref } from 'vue';

const emit = defineEmits(['agree', 'disagree']);

const checked = ref(false);
</script>

<template>
    <div>
        <h1 class="mb-4 text-lg font-bold text-brand-navy-900">Persetujuan Akses Data Kesehatan</h1>

        <div class="mb-6 space-y-3 text-sm leading-relaxed text-brand-navy-700">
            <div class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-xs font-bold text-brand-navy-900">1</span>
                <p><strong class="text-brand-navy-900">Privasi Data:</strong> identitas kehamilan, riwayat kesehatan, dan hasil skrining Ibu disimpan secara aman dalam sistem SIGADIS.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-xs font-bold text-brand-navy-900">2</span>
                <p><strong class="text-brand-navy-900">Tujuan Penggunaan:</strong> data digunakan oleh bidan pendamping dan admin verifikasi wilayah untuk deteksi dini risiko kehamilan, bukan tujuan lain.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-xs font-bold text-brand-navy-900">3</span>
                <p><strong class="text-brand-navy-900">Sinyal Darurat:</strong> dalam kondisi darurat (SOS), lokasi GPS dan data kehamilan Ibu akan dibagikan ke bidan/kader terdekat.</p>
            </div>
            <div class="flex gap-3">
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-navy-100 text-xs font-bold text-brand-navy-900">4</span>
                <p><strong class="text-brand-navy-900">Data Opsional:</strong> alamat dan kontak darurat boleh diisi di langkah berikutnya untuk mempercepat respons tim medis saat darurat, tapi tidak wajib.</p>
            </div>
            <p>Ibu dapat mencabut persetujuan atau meminta penghapusan data kapan saja lewat menu Privasi &amp; Data Saya.</p>
        </div>

        <label class="mb-6 flex cursor-pointer items-start gap-3 rounded-lg border border-brand-navy-100 bg-white p-4">
            <input v-model="checked" type="checkbox" class="checkbox mt-0.5" />
            <span class="text-sm text-brand-navy-900">Saya membaca dan menyetujui penjelasan di atas.</span>
        </label>

        <div class="space-y-3">
            <button
                type="button"
                :disabled="!checked"
                class="btn w-full border-none bg-brand-navy-900 text-white disabled:bg-brand-navy-100 disabled:text-brand-navy-700"
                @click="emit('agree')"
            >
                Saya Setuju & Lanjutkan
            </button>
            <button type="button" class="btn btn-ghost w-full text-brand-navy-700" @click="emit('disagree')">
                Tidak Setuju
            </button>
        </div>
    </div>
</template>
