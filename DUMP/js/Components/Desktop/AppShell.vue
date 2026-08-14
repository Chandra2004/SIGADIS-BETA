<script setup>
import { ref } from 'vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';

defineProps({
    regionCode: { type: String, default: null },
});

const emit = defineEmits(['logout']);

const showLogoutConfirm = ref(false);
</script>

<template>
    <div class="flex min-h-screen bg-neutral-100">
        <!--
            ponytail: Figma menampilkan nav "Patients/Alerts/Reporting" & sidebar
            "Live Map/Facility Status/Inventory/Resource Library" -- tidak dibangun
            di sini karena belum ada halaman/fitur nyata di baliknya. Nav palsu ke
            halaman kosong lebih buruk daripada sidebar minimal yang jujur.
        -->
        <aside class="flex w-56 shrink-0 flex-col justify-between border-r border-neutral-200 bg-white p-4">
            <div>
                <p class="mb-1 text-lg font-bold text-brand-navy-900">SIGADIS</p>
                <p v-if="regionCode" class="mb-6 text-xs text-neutral-500">Wilayah {{ regionCode }}</p>
                <a :href="route('bidan.dashboard')" class="block rounded-lg bg-brand-navy-900 px-3 py-2 text-sm font-semibold text-white">
                    Dashboard
                </a>
            </div>
            <div class="space-y-2">
                <slot name="sidebar-actions" />
                <button type="button" class="btn btn-outline btn-sm w-full" @click="showLogoutConfirm = true">Keluar</button>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <slot />
        </div>

        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Notifikasi darurat baru tidak akan muncul di layar ini sampai Anda masuk kembali."
            @confirm="emit('logout')"
            @cancel="showLogoutConfirm = false"
        />
    </div>
</template>
