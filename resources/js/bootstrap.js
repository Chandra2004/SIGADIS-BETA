import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo & Pusher hanya diinisialisasi jika environment variable tersedia
// Dibungkus try/catch agar tidak crash di environment yang tidak support WebSocket
// (contoh: InfinityFree shared hosting yang memblokir koneksi ws://)
try {
    const REVERB_KEY = import.meta.env.VITE_REVERB_APP_KEY;

    if (REVERB_KEY) {
        const { default: Echo } = await import('laravel-echo');
        const { default: Pusher } = await import('pusher-js');

        window.Pusher = Pusher;

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: REVERB_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }
} catch (e) {
    console.warn('[SIGADIS] Laravel Echo tidak dapat diinisialisasi:', e.message);
}
