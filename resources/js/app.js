import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { ZiggyVue } from 'ziggy-js';
import '../css/app.css';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import './bootstrap';

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(Vue3Toastify, {
        autoClose: 3000,
        // position: 'top-right',
      })
      .mount(el)
  },
})




const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT || (window.location.protocol === 'https:' ? 443 : 80));
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || (window.location.protocol === 'https:' ? 'https' : 'http');
const forceTls = reverbScheme === 'https';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: forceTls,
    enabledTransports: ['ws', 'wss'],
});

