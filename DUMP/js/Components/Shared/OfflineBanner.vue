<script setup>
import { useOnlineStatus } from '@/composables/useOnlineStatus';
import Icon from '@/Components/Shared/Icon.vue';

const { isOnline } = useOnlineStatus();
</script>

<template>
    <div v-if="!isOnline" class="fixed inset-x-0 top-0 z-[70] border-b border-accent-amber bg-white px-4 py-2 shadow-sm">
        <div class="mx-auto flex max-w-md items-center gap-3">
            <Icon name="wifiOff" size="h-5 w-5" class="shrink-0 text-[--color-accent-amber]" />
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-brand-navy-900">Mode Offline Aktif</p>
                <p class="text-xs text-neutral-600">
                    Koneksi internet terputus. Perubahan yang Ibu buat sekarang mungkin belum tersimpan sampai koneksi
                    kembali.
                </p>
            </div>
        </div>
    </div>
</template>
