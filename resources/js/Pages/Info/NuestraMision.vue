<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head>
            <title>Nuestra misión – Rustikan · Producto local, justo y sostenible</title>
            <meta name="description" content="100% productores locales, 0€ de comisión de alta y hecho en Lanzarote. Apostamos por un modelo de consumo justo, de proximidad y respetuoso con la isla.">
        </Head>
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 pb-20 pt-32 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.mission.title') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-primary-100">
                    {{ t('info.mission.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Impacto — visible nada más entrar -->
        <section class="bg-gradient-to-b from-primary-50 to-white py-10 dark:from-primary-950/40 dark:to-gray-900">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <h2 class="mb-6 text-center text-xl font-bold text-gray-800 dark:text-white">{{ t('info.mission.impact_title') }}</h2>
                <div class="grid gap-4 text-center sm:grid-cols-3">
                    <div ref="statRef" class="rounded-2xl border border-primary-200 bg-white p-6 shadow-sm dark:border-primary-800/50 dark:bg-gray-800">
                        <p class="text-4xl font-extrabold text-primary-600 dark:text-primary-400">{{ count }}%</p>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">{{ t('info.mission.impact_local') }}</p>
                    </div>
                    <div class="rounded-2xl border border-primary-200 bg-white p-6 shadow-sm dark:border-primary-800/50 dark:bg-gray-800">
                        <p class="text-4xl font-extrabold text-primary-600 dark:text-primary-400">0€</p>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">{{ t('info.mission.impact_fee') }}</p>
                    </div>
                    <div class="rounded-2xl border border-primary-200 bg-white p-6 shadow-sm dark:border-primary-800/50 dark:bg-gray-800">
                        <Palmtree class="mx-auto mb-1 h-9 w-9 text-primary-600 dark:text-primary-400" />
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ t('info.mission.impact_made') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">

                <!-- Misión -->
                <div class="mb-8 rounded-2xl border border-primary-100 bg-primary-50 p-8 dark:border-primary-800/60 dark:bg-primary-900/20">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ t('info.mission.mission_label') }}</h2>
                    <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ t('info.mission.mission_text') }}
                    </p>
                </div>

                <!-- Visión -->
                <div class="mb-8 rounded-2xl border border-primary-100 bg-primary-50 p-8 dark:border-primary-800/60 dark:bg-primary-900/20">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ t('info.mission.vision_label') }}</h2>
                    <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                        {{ t('info.mission.vision_text') }}
                    </p>
                </div>

                <!-- Compromisos -->
                <div class="mt-12">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ t('info.mission.commitments') }}</h2>
                    <div class="space-y-4">
                        <div v-for="item in compromisos" :key="item.titulo"
                             class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                                <component :is="item.icon" class="h-5 w-5" />
                            </span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ item.titulo }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { useCountUp } from '@/Composables/useCountUp';
import { Leaf, Coins, Search, Truck, Recycle, Palmtree } from 'lucide-vue-next';
useDarkMode();

const { t } = useI18n();

const statRef = ref(null);
const { count, observe } = useCountUp(100);
onMounted(() => observe(statRef.value));

const compromisos = computed(() => [
    { icon: Leaf,    titulo: t('info.mission.commitment_fresh_title'),        desc: t('info.mission.commitment_fresh_desc') },
    { icon: Coins,   titulo: t('info.mission.commitment_fair_title'),         desc: t('info.mission.commitment_fair_desc') },
    { icon: Search,  titulo: t('info.mission.commitment_transparency_title'), desc: t('info.mission.commitment_transparency_desc') },
    { icon: Truck,   titulo: t('info.mission.commitment_delivery_title'),     desc: t('info.mission.commitment_delivery_desc') },
    { icon: Recycle, titulo: t('info.mission.commitment_circular_title'),     desc: t('info.mission.commitment_circular_desc') },
]);
</script>
