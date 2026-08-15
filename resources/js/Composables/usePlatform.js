import { ref, computed } from 'vue';
import { Capacitor } from '@capacitor/core';
import { usePage } from '@inertiajs/vue3';

/**
 * Composable untuk deteksi platform (Web vs Mobile Native Capacitor)
 */
export function usePlatform() {
    const page = usePage();

    // Deteksi dari Capacitor native runtime
    const isNative = ref(Capacitor.isNativePlatform());
    const platformName = ref(Capacitor.getPlatform()); // 'android', 'ios', 'web'

    // Deteksi fallback dari server/Inertia shared props jika ada
    const isMobileApp = computed(() => {
        return isNative.value || Boolean(page.props?.isMobileApp);
    });

    const isWeb = computed(() => {
        return !isMobileApp.value;
    });

    const isAndroid = computed(() => {
        return platformName.value === 'android';
    });

    const isIos = computed(() => {
        return platformName.value === 'ios';
    });

    return {
        isNative,
        isMobileApp,
        isWeb,
        isAndroid,
        isIos,
        platformName,
    };
}
