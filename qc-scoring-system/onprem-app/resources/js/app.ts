import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue, route } from 'ziggy-js';
import Alpine from 'alpinejs'
import anchor from '@alpinejs/anchor'
import axios from 'axios';

const appName = import.meta.env.VITE_APP_NAME || 'Kontakami';

Alpine.plugin(anchor)
Alpine.start()
window.Alpine = Alpine

window.route = (name: string, params?: any, absolute?: boolean) => route(name, params, absolute, Ziggy)
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#3943B7',
        showSpinner: true,
    },
});
