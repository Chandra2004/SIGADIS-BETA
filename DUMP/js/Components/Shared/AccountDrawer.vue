<script setup>
import Icon from '@/Components/Shared/Icon.vue';

defineProps({
    show: { type: Boolean, required: true },
});

const emit = defineEmits(['close', 'logout']);

const links = [
    { label: 'Profil Saya', icon: 'user', route: 'kehamilan.profil' },
    { label: 'Pengaturan Aplikasi', icon: 'document', route: 'kehamilan.pengaturan' },
    { label: 'Privasi & Data Saya', icon: 'shield', route: 'kehamilan.privasi' },
    { label: 'Ganti Nomor HP', icon: 'phone', route: 'akun.ganti-nomor.show' },
];
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-end bg-black/50" @click.self="emit('close')">
        <div class="w-full rounded-t-2xl bg-white pb-6 shadow-lg">
            <div class="flex justify-center py-3">
                <span class="h-1.5 w-10 rounded-full bg-neutral-200" />
            </div>

            <nav class="space-y-1 px-4">
                <a
                    v-for="link in links"
                    :key="link.route"
                    :href="route(link.route)"
                    class="flex cursor-pointer items-center gap-3 rounded-lg p-3 text-sm font-medium text-brand-navy-900 transition-colors hover:bg-brand-pink-50"
                >
                    <Icon :name="link.icon" size="h-5 w-5" class="text-brand-navy-700" />
                    {{ link.label }}
                </a>

                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 rounded-lg p-3 text-left text-sm font-medium text-risk-high transition-colors hover:bg-risk-high-bg"
                    @click="emit('logout')"
                >
                    <Icon name="x" size="h-5 w-5" />
                    Keluar
                </button>
            </nav>
        </div>
    </div>
</template>
