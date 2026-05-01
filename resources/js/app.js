import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { vReveal } from '@/Directives/reveal.js';
import ChatIA from '@/Components/ChatIA.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Rutas donde NO queremos mostrar el chat (paneles internos)
const CHAT_HIDDEN_PREFIXES = ['/admin', '/mi-tienda', '/dashboard'];
const shouldShowChat = () => {
    if (typeof window === 'undefined') return true;
    const path = window.location.pathname || '/';
    return !CHAT_HIDDEN_PREFIXES.some((p) => path === p || path.startsWith(p + '/'));
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({
            render: () => [
                h(App, props),
                shouldShowChat() ? h(ChatIA) : null,
            ],
        })
            .use(plugin)
            .use(ZiggyVue)
            .directive('reveal', vReveal)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
