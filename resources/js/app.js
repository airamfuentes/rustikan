import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, ref } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { vReveal } from '@/Directives/reveal.js';
import { vOnlyDigits, vOnlyDecimal, vOnlySignedDecimal, vOnlyLetters, vOnlyPhone } from '@/Directives/inputValidations.js';
import ChatIA from '@/Components/ChatIA.vue';
import ChatConSuppliers from '@/Components/ChatConSuppliers.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import { useChatState } from '@/Composables/useChatState';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Rutas donde NO queremos mostrar el chat IA (paneles internos y auth)
const CHAT_IA_HIDDEN_PREFIXES = [
    '/mi-tienda', '/dashboard', '/supplier', '/admin',
    '/login', '/register', '/forgot-password', '/reset-password',
    '/verify-email', '/confirm-password',
];

createInertiaApp({
    title: (title) => title ? `${title} – ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Reactive state que se actualiza en cada navegación de Inertia.
        const role = ref(props.initialPage?.props?.auth?.user?.role ?? null);
        const currentUrl = ref(
            typeof window !== 'undefined' ? window.location.pathname : '/'
        );

        router.on('navigate', (event) => {
            role.value = event.detail.page.props?.auth?.user?.role ?? null;
            // Inertia expone la URL completa con query; nos quedamos con la path.
            try {
                currentUrl.value = new URL(event.detail.page.url, window.location.origin).pathname;
            } catch (_) {
                currentUrl.value = window.location.pathname;
            }
        });

        const { suppliersOpen } = useChatState();

        return createApp({
            setup() {
                return () => {
                    const currentRole = role.value;
                    const url = currentUrl.value;

                    const showChatIA = currentRole !== 'supplier'
                        && !suppliersOpen.value
                        && !CHAT_IA_HIDDEN_PREFIXES.some(
                            p => url === p || url.startsWith(p + '/') || url.startsWith(p + '?'),
                        );

                    return [
                        h(App, props),
                        h(ToastContainer),
                        showChatIA ? h(ChatIA) : null,
                        currentRole === 'admin' ? h(ChatConSuppliers, { chatIaVisible: showChatIA }) : null,
                    ];
                };
            },
        })
            .use(plugin)
            .use(ZiggyVue)
            .directive('reveal', vReveal)
            .directive('only-digits', vOnlyDigits)
            .directive('only-decimal', vOnlyDecimal)
            .directive('only-signed-decimal', vOnlySignedDecimal)
            .directive('only-letters', vOnlyLetters)
            .directive('only-phone', vOnlyPhone)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
