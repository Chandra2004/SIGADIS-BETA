<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';

defineProps({
    worker: { type: Object, required: true },
});

const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('auth.staff.logout'));
}
</script>

<template>
    <Head title="Menunggu Verifikasi" />

    <div class="flex min-h-screen flex-col bg-neutral-100">
        <header class="border-b border-neutral-200 bg-brand-navy-900 px-6 py-4">
            <p class="text-lg font-bold text-white">SIGADIS</p>
        </header>

        <div class="flex flex-1 items-center justify-center px-6">
            <div class="grid w-full max-w-2xl overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm md:grid-cols-2">
                <div class="flex flex-col items-center justify-center gap-3 bg-neutral-50 p-8 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-navy-100">
                        <Icon name="clock" size="h-8 w-8" class="text-brand-navy-900" />
                    </div>
                    <span class="rounded-full bg-brand-navy-100 px-3 py-1 text-xs font-semibold text-brand-navy-900">
                        STATUS: MENUNGGU
                    </span>
                </div>

                <div class="p-8">
                    <h1 class="mb-2 text-xl font-bold text-brand-navy-900">Akun Menunggu Verifikasi</h1>
                    <p class="mb-4 text-sm text-neutral-600">
                        Akun Anda sedang dalam proses verifikasi oleh Admin Puskesmas/Dinkes. Kami akan memberi tahu
                        Anda setelah akun aktif.
                    </p>

                    <dl class="mb-6 space-y-2 rounded-lg bg-neutral-50 p-4 text-sm">
                        <div class="flex justify-between border-b border-neutral-200 pb-2">
                            <dt class="text-neutral-500">Nama Lengkap</dt>
                            <dd class="font-medium text-neutral-900">{{ worker.full_name }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-neutral-200 pb-2">
                            <dt class="text-neutral-500">{{ worker.role === 'bidan' ? 'STR' : 'Surat Penunjukan' }}</dt>
                            <dd class="font-medium text-neutral-900">{{ worker.role === 'bidan' ? worker.str_number : worker.appointment_letter_ref }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-neutral-500">Wilayah Tugas</dt>
                            <dd class="font-medium text-neutral-900">{{ worker.region_code }}</dd>
                        </div>
                    </dl>

                    <button type="button" class="btn btn-outline btn-block" @click="showLogoutConfirm = true">Keluar</button>
                </div>
            </div>
        </div>

        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Notifikasi darurat baru tidak akan muncul di layar ini sampai Anda masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />
    </div>
</template>
