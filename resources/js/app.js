import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { createPinia } from 'pinia'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
if (import.meta.env.VITE_REVERB_APP_KEY) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    window.Pusher = Pusher
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: import.meta.env.VITE_REVERB_PORT || 80,
        wssPort: import.meta.env.VITE_REVERB_PORT || 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        auth: {
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
        },
    })
}

createInertiaApp({
    title: (title) => title ? `${title} | SimpleOrder` : 'SimpleOrder',
    resolve: (name) =>
        resolvePageComponent(`./pages/${name}.vue`, import.meta.glob('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .mount(el)
    },
    progress: {
        color: '#e85d04',
    },
})
