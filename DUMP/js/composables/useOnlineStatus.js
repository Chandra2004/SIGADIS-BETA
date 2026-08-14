import { onMounted, onUnmounted, ref } from 'vue';

/** Flows.md §20: deteksi status koneksi, dipakai indikator mode offline global. */
export function useOnlineStatus() {
    const isOnline = ref(navigator.onLine);

    const goOnline = () => (isOnline.value = true);
    const goOffline = () => (isOnline.value = false);

    onMounted(() => {
        window.addEventListener('online', goOnline);
        window.addEventListener('offline', goOffline);
    });

    onUnmounted(() => {
        window.removeEventListener('online', goOnline);
        window.removeEventListener('offline', goOffline);
    });

    return { isOnline };
}
