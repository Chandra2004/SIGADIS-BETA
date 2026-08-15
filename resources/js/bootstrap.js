import axios from 'axios';
import { Capacitor } from '@capacitor/core';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-Capacitor-Platform'] = Capacitor.getPlatform();
window.axios.defaults.headers.common['X-Is-Native'] = Capacitor.isNativePlatform() ? '1' : '0';

