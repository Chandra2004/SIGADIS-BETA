<script setup>
import Icon from '@/Components/Shared/Icon.vue';

defineProps({
    active: { type: String, required: true },
});

const tabs = [
    { key: 'beranda', label: 'Beranda', icon: 'home', route: 'kehamilan.beranda' },
    { key: 'riwayat', label: 'Riwayat', icon: 'clock', route: 'kehamilan.riwayat' },
    { key: 'faskes', label: 'Faskes', icon: 'hospital', route: 'kehamilan.faskes' },
    { key: 'profil', label: 'Profil', icon: 'user', route: 'kehamilan.profil' },
];
</script>

<template>
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-brand-navy-100 bg-white px-4 py-2">
        <div class="mx-auto flex max-w-md justify-around">
            <a
                v-for="tab in tabs"
                :key="tab.key"
                :href="route(tab.route)"
                class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1 text-xs"
                :class="active === tab.key ? 'font-semibold text-white bg-brand-navy-900' : 'text-brand-navy-700'"
            >
                <Icon :name="tab.icon" size="h-5 w-5" />
                {{ tab.label }}
            </a>
        </div>
    </nav>
</template>
