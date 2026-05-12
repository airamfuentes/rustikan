<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head>
            <title>{{ t('info.privacy.title') }} – Rustikan</title>
            <meta name="robots" content="noindex,follow">
        </Head>
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-slate-700 via-slate-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.privacy.title') }}</h1>
                <p class="mt-3 text-sm text-gray-400">{{ t('info.privacy.last_update_label') }} {{ t('info.privacy.last_update') }}</p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">

                <!-- Introducción -->
                <div class="mb-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-5">
                    <p class="text-sm text-blue-800 dark:text-blue-300 leading-relaxed">
                        {{ t('info.privacy.intro') }}
                    </p>
                </div>

                <div class="space-y-10">
                    <div v-for="(seccion, i) in secciones" :key="i">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="text-primary-600 dark:text-primary-400 text-sm font-mono">{{ String(i + 1).padStart(2, '0') }}</span>
                            {{ seccion.titulo }}
                        </h2>
                        <div class="space-y-3">
                            <p v-for="(p, j) in seccion.contenido" :key="j" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ p }}</p>
                            <ul v-if="seccion.lista" class="mt-2 space-y-1 list-none">
                                <li v-for="(item, k) in seccion.lista" :key="k" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="text-primary-500 mt-0.5">•</span> {{ item }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contacto DPO -->
                <div class="mt-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ t('info.privacy.contact_title') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ t('info.privacy.contact_desc_pre') }}
                        <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>{{ t('info.privacy.contact_desc_post') }}
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
useDarkMode();

const { t, tr } = useI18n();

const secciones = computed(() => tr('info.privacy.sections', []));
</script>
