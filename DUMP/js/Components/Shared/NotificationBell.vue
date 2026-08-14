<script setup>
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref } from 'vue';
import Icon from '@/Components/Shared/Icon.vue';

const props = defineProps({
    indexRoute: { type: String, required: true },
    markReadRoute: { type: String, required: true },
    markAllReadRoute: { type: String, required: true },
});

const page = usePage();
const open = ref(false);
const notifications = ref([]);
const loading = ref(false);

async function toggle() {
    open.value = !open.value;
    if (!open.value) return;

    loading.value = true;
    try {
        const { data } = await axios.get(route(props.indexRoute));
        notifications.value = data.notifications;
    } finally {
        loading.value = false;
    }
}

async function markRead(notification) {
    if (notification.read_at) return;
    notification.read_at = new Date().toISOString();
    await axios.post(route(props.markReadRoute, notification.id));
    page.props.unreadNotificationCount = Math.max(0, page.props.unreadNotificationCount - 1);
}

async function markAllRead() {
    notifications.value.forEach((n) => (n.read_at = n.read_at ?? new Date().toISOString()));
    page.props.unreadNotificationCount = 0;
    await axios.post(route(props.markAllReadRoute));
}

function timeAgo(value) {
    return new Date(value).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            aria-label="Notifikasi"
            class="btn btn-circle btn-sm relative border border-neutral-200 bg-white"
            @click="toggle"
        >
            <Icon name="bell" size="h-4 w-4" />
            <span
                v-if="page.props.unreadNotificationCount > 0"
                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-emergency-alert text-[10px] text-white"
            >
                {{ page.props.unreadNotificationCount > 9 ? '9+' : page.props.unreadNotificationCount }}
            </span>
        </button>

        <div v-if="open" class="absolute right-0 z-40 mt-2 w-80 rounded-lg border border-neutral-200 bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-neutral-100 p-3">
                <p class="text-sm font-semibold text-neutral-900">Notifikasi</p>
                <button type="button" class="text-xs text-brand-navy-700 underline" @click="markAllRead">Tandai semua dibaca</button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <p v-if="loading" class="p-4 text-center text-sm text-neutral-500">Memuat...</p>
                <p v-else-if="notifications.length === 0" class="p-4 text-center text-sm text-neutral-500">Belum ada notifikasi.</p>
                <button
                    v-for="n in notifications"
                    :key="n.id"
                    type="button"
                    class="block w-full border-b border-neutral-50 p-3 text-left last:border-0"
                    :class="n.read_at ? 'bg-white' : 'bg-brand-pink-50'"
                    @click="markRead(n)"
                >
                    <p class="text-sm font-medium text-neutral-900">{{ n.data.title }}</p>
                    <p class="text-xs text-neutral-600">{{ n.data.body }}</p>
                    <p class="mt-1 text-xs text-neutral-400">{{ timeAgo(n.created_at) }}</p>
                </button>
            </div>
        </div>
    </div>
</template>
