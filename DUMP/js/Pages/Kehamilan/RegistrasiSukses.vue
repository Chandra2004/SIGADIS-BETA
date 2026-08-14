<script setup>
import { Head } from '@inertiajs/vue3';
import Icon from '@/Components/Shared/Icon.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    motherName: { type: String, required: true },
    gestationalAgeWeeks: { type: Number, required: true },
    estimatedDueDate: { type: String, default: null },
    midwifeName: { type: String, default: null },
    gpsPermissionEnabled: { type: Boolean, required: true },
});

const formattedDueDate = props.estimatedDueDate
    ? new Date(props.estimatedDueDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
    : null;
</script>

<template>
    <Head title="Registrasi Berhasil" />

    <div class="flex min-h-screen flex-col bg-brand-pink-50">
        <TopAppBar />

        <div class="mx-auto w-full max-w-sm flex-1 px-6 py-6">
            <span class="mb-3 inline-block rounded-full bg-brand-pink-200 px-3 py-1 text-xs font-semibold text-brand-navy-900">
                Langkah 5 dari 5
            </span>

            <section class="mb-4 flex items-center gap-3 rounded-xl bg-risk-low-bg p-4">
                <img src="/assets/images/mascot/pose-14-merayakan.png" alt="" class="h-16 w-16 shrink-0 object-contain" />
                <div>
                    <p class="font-bold text-risk-low">Registrasi Berhasil!</p>
                    <p class="text-sm text-brand-navy-700">
                        Data kehamilan dan Bidan Pendamping Ibu telah terhubung dalam sistem SIGADIS.
                    </p>
                </div>
            </section>

            <section class="mb-6 space-y-3 rounded-xl bg-white p-4 shadow-sm">
                <h2 class="font-bold text-brand-navy-900">Ringkasan Profil Ibu</h2>

                <div>
                    <p class="text-xs text-neutral-500 uppercase">Nama Ibu</p>
                    <p class="font-medium text-brand-navy-900">{{ motherName }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 uppercase">Usia Kehamilan</p>
                    <p class="font-medium text-brand-navy-900">
                        {{ gestationalAgeWeeks }} Minggu<span v-if="formattedDueDate"> (HPL: {{ formattedDueDate }})</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 uppercase">Bidan Pendamping</p>
                    <p class="font-medium text-brand-navy-900">{{ midwifeName ?? 'Belum ada, tim kami akan menindaklanjuti' }}</p>
                </div>
                <div>
                    <p class="text-xs text-neutral-500 uppercase">Izin Akses SOS</p>
                    <p class="flex items-center gap-1 font-medium" :class="gpsPermissionEnabled ? 'text-risk-low' : 'text-neutral-500'">
                        <Icon v-if="gpsPermissionEnabled" name="check" size="h-3.5 w-3.5" />
                        <span v-else>&ndash;</span>
                        {{ gpsPermissionEnabled ? 'Aktif (GPS & Data Terhubung)' : 'GPS belum diaktifkan' }}
                    </p>
                </div>
            </section>

            <a :href="route('kehamilan.beranda')" class="btn w-full border-none bg-brand-navy-900 text-white">
                Masuk ke Beranda Utama &rarr;
            </a>
        </div>
    </div>
</template>
