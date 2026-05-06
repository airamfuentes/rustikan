<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Quiénes somos - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 pb-20 pt-32 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.about.title') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-primary-100">
                    {{ t('info.about.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">

                <!-- Historia -->
                <div class="mb-16">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">{{ t('info.about.history') }}</h2>
                    <div class="space-y-4 text-base leading-relaxed text-gray-600 dark:text-gray-400">
                        <p>
                            Rustikan nació en Lanzarote con una idea sencilla pero poderosa: hacer que los productos locales lleguen más fácilmente a quienes los buscan. Vivimos en una isla con una riqueza agrícola y artesanal extraordinaria — viñedos en La Geria, quesos artesanales, miel de abeja, aloe vera, verduras de volcán — pero muchos de estos productores tenían dificultades para encontrar a sus clientes más allá del mercadillo del pueblo.
                        </p>
                        <p>
                            Creamos esta plataforma para tender ese puente digital entre el productor que trabaja la tierra y el consumidor que quiere saber de dónde viene lo que come.
                        </p>
                    </div>
                </div>

                <!-- Valores -->
                <div class="mb-16">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ t('info.about.values') }}</h2>
                    <div class="grid gap-6 sm:grid-cols-3">
                        <div class="rounded-2xl bg-primary-50 p-6 text-center dark:bg-primary-900/20">
                            <Sprout class="mx-auto mb-3 h-10 w-10 text-primary-600 dark:text-primary-400" />
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Sostenibilidad</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Promovemos prácticas que cuidan el medioambiente y preservan el paisaje único de Lanzarote.</p>
                        </div>
                        <div class="rounded-2xl bg-amber-50 p-6 text-center dark:bg-amber-900/20">
                            <Handshake class="mx-auto mb-3 h-10 w-10 text-amber-600 dark:text-amber-400" />
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Comercio justo</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">El productor recibe un precio justo por su trabajo y el cliente accede a productos de calidad real.</p>
                        </div>
                        <div class="rounded-2xl bg-green-50 p-6 text-center dark:bg-green-900/20">
                            <Palmtree class="mx-auto mb-3 h-10 w-10 text-green-600 dark:text-green-400" />
                            <h3 class="mb-2 font-semibold text-gray-900 dark:text-white">Identidad local</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Celebramos la cultura y los sabores de Lanzarote, apoyando a quienes hacen isla con sus manos.</p>
                        </div>
                    </div>
                </div>

                <!-- Equipo -->
                <div>
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ t('info.about.team') }}</h2>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="flex flex-col items-center rounded-2xl border border-gray-100 bg-gray-50 p-6 text-center dark:border-gray-700 dark:bg-gray-800">
                            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary-600 text-2xl font-bold text-white">A</div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Airam Fuentes</h3>
                            <p class="mb-2 text-sm text-primary-600 dark:text-primary-400">Fundador &amp; Desarrollador</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Lanzaroteño de corazón, apasionado por la tecnología y el desarrollo local.</p>
                            <a href="https://github.com/airamfuentes" target="_blank" rel="noopener noreferrer"
                               class="mt-3 inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                                </svg>
                                airamfuentes
                            </a>
                        </div>
                    </div>
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
import { useI18n } from '@/Composables/useI18n';
import { Sprout, Handshake, Palmtree } from 'lucide-vue-next';
useDarkMode();

const { t } = useI18n();
</script>
