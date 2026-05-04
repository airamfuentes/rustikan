<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Política de cookies - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <Cookie class="h-8 w-8" />
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Política de cookies</h1>
                <p class="mt-3 text-sm text-gray-400">Última actualización: {{ fechaActualizacion }}</p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">

                <!-- Intro -->
                <div class="mb-8 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-5">
                    <p class="text-sm text-amber-900 dark:text-amber-300 leading-relaxed">
                        Una cookie es un pequeño archivo de texto que un sitio web almacena en tu dispositivo cuando lo visitas. Las cookies nos permiten recordar tus preferencias, mantenerte conectado y entender cómo se usa la Plataforma para mejorarla.
                    </p>
                </div>

                <!-- Tipos de cookies -->
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Tipos de cookies que utilizamos</h2>
                <div class="space-y-4 mb-12">
                    <div v-for="tipo in tipos" :key="tipo.nombre" class="rounded-xl border p-5" :class="tipo.clase">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ tipo.nombre }}</h3>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0" :class="tipo.badge">
                                {{ tipo.requerida ? 'Necesaria' : 'Opcional' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ tipo.desc }}</p>
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            <span class="font-medium">Ejemplos:</span> {{ tipo.ejemplos }}
                        </div>
                    </div>
                </div>

                <!-- Gestionar cookies -->
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Cómo gestionar las cookies</h2>
                <div class="space-y-4 mb-10">
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Puedes configurar tu navegador para rechazar todas las cookies, para que te avise cuando se envíe una cookie, o para eliminar las cookies ya existentes. Consulta la ayuda de tu navegador para más información:
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <a v-for="nav in navegadores" :key="nav.nombre" :href="nav.url" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-sm text-primary-600 dark:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            {{ nav.nombre }} →
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                        Ten en cuenta que deshabilitar las cookies puede afectar al funcionamiento de la Plataforma, especialmente las cookies de sesión necesarias para permanecer conectado.
                    </p>
                </div>

                <!-- Contacto -->
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">¿Tienes preguntas?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Si tienes alguna duda sobre nuestra política de cookies, escríbenos a
                        <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>.
                    </p>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Cookie } from 'lucide-vue-next';
useDarkMode();

const fechaActualizacion = '30 de abril de 2025';

const tipos = [
    {
        nombre: 'Cookies estrictamente necesarias',
        requerida: true,
        clase: 'border-green-100 dark:border-green-800 bg-green-50 dark:bg-green-900/10',
        badge: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        desc: 'Son imprescindibles para el funcionamiento básico de la Plataforma. Sin ellas no podrías iniciar sesión, añadir productos al carrito ni completar un pedido.',
        ejemplos: 'Cookie de sesión, token CSRF, preferencia de idioma.',
    },
    {
        nombre: 'Cookies de preferencias',
        requerida: false,
        clase: 'border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10',
        badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        desc: 'Guardan tus preferencias para mejorar tu experiencia, como el tema claro/oscuro o tus filtros de búsqueda habituales.',
        ejemplos: 'Preferencia de tema (dark/light mode), idioma seleccionado.',
    },
    {
        nombre: 'Cookies analíticas',
        requerida: false,
        clase: 'border-purple-100 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/10',
        badge: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        desc: 'Nos permiten analizar el uso de la Plataforma de forma agregada y anónima para mejorar su funcionamiento. No identifican a los usuarios individualmente.',
        ejemplos: 'Páginas más visitadas, tiempo de sesión, flujo de navegación.',
    },
];

const navegadores = [
    { nombre: 'Google Chrome', url: 'https://support.google.com/chrome/answer/95647' },
    { nombre: 'Mozilla Firefox', url: 'https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web' },
    { nombre: 'Safari', url: 'https://support.apple.com/es-es/guide/safari/sfri11471/mac' },
    { nombre: 'Microsoft Edge', url: 'https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09' },
];
</script>
