<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head>
            <title>{{ t('info.cookies.title') }} – Rustikan</title>
            <meta name="robots" content="noindex,follow">
        </Head>
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <Cookie class="h-8 w-8" />
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.cookies.title') }}</h1>
                <p class="mt-3 text-sm text-gray-400">{{ t('info.cookies.last_update_label') }} {{ t('info.cookies.last_update') }}</p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">

                <!-- Intro -->
                <div class="mb-8 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-5">
                    <p class="text-sm text-amber-900 dark:text-amber-300 leading-relaxed">
                        {{ t('info.cookies.intro') }}
                    </p>
                </div>

                <!-- Tipos de cookies -->
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ t('info.cookies.types_title') }}</h2>
                <div class="space-y-4 mb-12">
                    <div v-for="(tipo, idx) in tipos" :key="idx" class="rounded-xl border p-5" :class="estilos[idx].clase">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ tipo.nombre }}</h3>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0" :class="estilos[idx].badge">
                                {{ tipo.requerida ? t('info.cookies.required_label') : t('info.cookies.optional_label') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ tipo.desc }}</p>
                    </div>
                </div>

                <!-- Gestionar cookies -->
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">{{ t('info.cookies.manage_title') }}</h2>
                <div class="space-y-4 mb-10">
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        {{ t('info.cookies.manage_intro') }}
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <a v-for="nav in navegadores" :key="nav.nombre" :href="nav.url" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-2 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-sm text-primary-600 dark:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            {{ nav.nombre }} →
                        </a>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                        {{ t('info.cookies.manage_warning') }}
                    </p>
                </div>

                <!-- Contacto -->
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ t('info.cookies.contact_title') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ t('info.cookies.contact_desc_pre') }}
                        <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>{{ t('info.cookies.contact_desc_post') }}
                    </p>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { Cookie } from 'lucide-vue-next';
useDarkMode();

const { t, tr } = useI18n();

const tipos = computed(() => tr('info.cookies.types', []));

// Estilos visuales por índice (no se traducen)
const estilos = [
    {
        clase: 'border-green-100 dark:border-green-800 bg-green-50 dark:bg-green-900/10',
        badge: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    },
    {
        clase: 'border-blue-100 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/10',
        badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    },
    {
        clase: 'border-purple-100 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/10',
        badge: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    },
];

const navegadores = [
    { nombre: 'Google Chrome', url: 'https://support.google.com/chrome/answer/95647' },
    { nombre: 'Mozilla Firefox', url: 'https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web' },
    { nombre: 'Safari', url: 'https://support.apple.com/es-es/guide/safari/sfri11471/mac' },
    { nombre: 'Microsoft Edge', url: 'https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09' },
];
</script>
