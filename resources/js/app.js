import '../css/app.css';
import './bootstrap';

import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, defineComponent } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { vReveal } from '@/Directives/reveal.js';
import ChatIA from '@/Components/ChatIA.vue';
import ChatConSuppliers from '@/Components/ChatConSuppliers.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Rutas donde NO queremos mostrar el chat IA (paneles internos)
const CHAT_IA_HIDDEN_PREFIXES = ['/mi-tienda', '/dashboard', '/supplier'];

createInertiaApp({
    title: (title) => `${appName} - ${title}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const RootApp = defineComponent({
            setup() {
                const page = usePage();
                return () => {
                    const role = page.props.auth?.user?.role;
                    const url  = page.url ?? '/';

                    const showChatIA = role !== 'supplier' && !CHAT_IA_HIDDEN_PREFIXES.some(
                        p => url === p || url.startsWith(p + '/') || url.startsWith(p + '?'),
                    );

                    return [
                        h(App, props),
                        showChatIA ? h(ChatIA) : null,
                        role === 'admin' ? h(ChatConSuppliers) : null,
                    ];
                };
            },
        });

        return createApp(RootApp)
            .use(plugin)
            .use(ZiggyVue)
            .directive('reveal', vReveal)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
