<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head>
            <title>{{ t('info.terms.title') }} – Rustikan</title>
            <meta name="robots" content="noindex,follow">
        </Head>
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.terms.title') }}</h1>
                <p class="mt-3 text-sm text-gray-400">{{ t('info.terms.last_update_label') }} {{ t('info.terms.last_update') }}</p>
            </div>
        </section>

        <!-- Contenido legal -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <div class="prose prose-gray dark:prose-invert max-w-none">

                    <div v-for="(seccion, i) in secciones" :key="i" class="mb-10">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="text-primary-600 dark:text-primary-400 text-sm font-mono">{{ String(i + 1).padStart(2, '0') }}</span>
                            {{ seccion.titulo }}
                        </h2>
                        <div class="space-y-3">
                            <p v-for="(parrafo, j) in seccion.contenido" :key="j" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ parrafo }}
                            </p>
                        </div>
                    </div>

                    <!-- Contacto legal -->
                    <div class="mt-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ t('info.terms.contact_title') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('info.terms.contact_desc') }}
                            <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>
                        </p>
                    </div>

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
useDarkMode();

const { t, tr } = useI18n();

const secciones = computed(() => tr('info.terms.sections', []));
</script>
