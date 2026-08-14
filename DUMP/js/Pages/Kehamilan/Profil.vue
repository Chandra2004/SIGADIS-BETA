<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BottomTabBar from '@/Components/Shared/BottomTabBar.vue';
import Icon from '@/Components/Shared/Icon.vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    motherName: { type: String, required: true },
    phoneNumber: { type: String, required: true },
    profilePhotoUrl: { type: String, default: null },
    hasActivePregnancy: { type: Boolean, required: true },
    canChangeMidwife: { type: Boolean, required: true },
});

const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('auth.pregnant.logout'));
}

const links = [
    { label: 'Pengaturan Aplikasi', description: 'Ukuran teks, suara, notifikasi', icon: 'document', route: 'kehamilan.pengaturan' },
    { label: 'Privasi & Data Saya', description: 'Consent, izin akses, hapus data', icon: 'shield', route: 'kehamilan.privasi' },
    { label: 'Ganti Nomor HP', description: 'Perbarui nomor HP akun Ibu', icon: 'phone', route: 'akun.ganti-nomor.show' },
];
</script>

<template>
    <Head title="Profil Saya" />

    <div class="min-h-screen bg-brand-pink-50 pb-24">
        <TopAppBar title="Profil" />

        <div class="mx-auto w-full max-w-md px-6 py-6">
            <section class="mb-6 flex flex-col items-center rounded-xl bg-white p-6 text-center shadow-sm">
                <img
                    v-if="profilePhotoUrl"
                    :src="profilePhotoUrl"
                    alt=""
                    class="mb-3 h-20 w-20 rounded-full object-cover"
                />
                <div v-else class="mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-brand-navy-100">
                    <Icon name="user" size="h-10 w-10" class="text-brand-navy-700" />
                </div>
                <p class="text-lg font-bold text-brand-navy-900">{{ motherName }}</p>
                <p class="text-sm text-neutral-500">{{ phoneNumber }}</p>
            </section>

            <section class="mb-4 rounded-xl bg-white p-2 shadow-sm">
                <a
                    v-for="(link, i) in links"
                    :key="link.route"
                    :href="route(link.route)"
                    class="flex cursor-pointer items-center gap-3 p-3 transition-colors hover:bg-neutral-50"
                    :class="{ 'border-t border-neutral-100': i > 0 }"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-navy-100">
                        <Icon :name="link.icon" size="h-5 w-5" class="text-brand-navy-700" />
                    </span>
                    <span class="flex-1">
                        <span class="block text-sm font-medium text-brand-navy-900">{{ link.label }}</span>
                        <span class="block text-xs text-neutral-500">{{ link.description }}</span>
                    </span>
                </a>

                <a
                    v-if="canChangeMidwife"
                    :href="route('kehamilan.ganti-bidan.show')"
                    class="flex cursor-pointer items-center gap-3 border-t border-neutral-100 p-3 transition-colors hover:bg-neutral-50"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-navy-100">
                        <Icon name="heart" size="h-5 w-5" class="text-brand-navy-700" />
                    </span>
                    <span class="flex-1">
                        <span class="block text-sm font-medium text-brand-navy-900">Ganti Bidan Pendamping</span>
                        <span class="block text-xs text-neutral-500">Pilih bidan pendamping lain di wilayah Ibu</span>
                    </span>
                </a>
            </section>

            <button
                type="button"
                class="btn btn-outline w-full gap-2 border-risk-high text-risk-high"
                @click="showLogoutConfirm = true"
            >
                <Icon name="x" size="h-4 w-4" /> Keluar
            </button>
        </div>

        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Ibu perlu memasukkan nomor HP lagi untuk masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />

        <BottomTabBar v-if="hasActivePregnancy" active="profil" />
    </div>
</template>
