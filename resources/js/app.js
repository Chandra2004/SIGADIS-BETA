import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import OfflineBanner from './Components/Shared/OfflineBanner.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Flows.md §29.2.2: preferensi ukuran teks berlaku ke seluruh layar, termasuk
// sesi yang sedang berjalan — cukup 1 listener global, bukan per-halaman.
router.on('navigate', (event) => {
    document.documentElement.dataset.textSize = event.detail.page.props.textSize ?? 'normal';
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        // eager: true → semua halaman Vue dibundle jadi 1 file (tidak lazy-load)
        // Fix untuk InfinityFree: dynamic import chunks gagal dimuat
        import.meta.glob('./Pages/**/*.vue', { eager: true })
    ),

    setup({ el, App, props, plugin }) {
        document.documentElement.dataset.textSize = props.initialPage.props.textSize ?? 'normal';

        // Flows.md §20: indikator koneksi global, di atas semua halaman (mobile & desktop).
        return createApp({ render: () => [h(OfflineBanner), h(App, props)] })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },


    progress: {
        color: '#4B5563',
        // showSpinner: true,
    },
});
