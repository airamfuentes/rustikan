<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Nuestra misión" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 pb-20 pt-32 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm ring-1 ring-white/20">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.mission.title') }}</h1>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-emerald-100">
                    {{ t('info.mission.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Impacto — visible nada más entrar -->
        <section class="bg-gradient-to-b from-emerald-50 to-white py-10 dark:from-emerald-950/40 dark:to-gray-900">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <h2 class="mb-6 text-center text-xl font-bold text-gray-800 dark:text-white">Nuestro impacto</h2>
                <div class="grid gap-4 text-center sm:grid-cols-3">
                    <div ref="statRef" class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-800/50 dark:bg-gray-800">
                        <p class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ count }}%</p>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">Productores locales</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-800/50 dark:bg-gray-800">
                        <p class="text-4xl font-extrabold text-emerald-600 dark:text-emerald-400">0€</p>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">Comisión de alta para productores</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-800/50 dark:bg-gray-800">
                        <Palmtree class="mx-auto mb-1 h-9 w-9 text-emerald-600 dark:text-emerald-400" />
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Hecho en Lanzarote</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">

                <!-- Misión -->
                <div class="mb-8 rounded-2xl border border-emerald-100 bg-emerald-50 p-8 dark:border-emerald-800/60 dark:bg-emerald-900/20">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Misión</h2>
                    <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                        Facilitar el acceso al mercado local a pequeños y medianos productores de Lanzarote, ofreciéndoles una plataforma digital donde puedan vender sus productos de forma directa, sin intermediarios y con total transparencia para el consumidor.
                    </p>
                </div>

                <!-- Visión -->
                <div class="mb-8 rounded-2xl border border-primary-100 bg-primary-50 p-8 dark:border-primary-800/60 dark:bg-primary-900/20">
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Visión</h2>
                    <p class="leading-relaxed text-gray-700 dark:text-gray-300">
                        Convertirnos en la plataforma de referencia para el comercio local en Canarias, donde cada compra impulse la economía circular de la isla y contribuya a preservar la identidad cultural y agrícola del archipiélago.
                    </p>
                </div>

                <!-- Compromisos -->
                <div class="mt-12">
                    <h2 class="mb-8 text-2xl font-bold text-gray-900 dark:text-white">{{ t('info.mission.commitments') }}</h2>
                    <div class="space-y-4">
                        <div v-for="item in compromisos" :key="item.titulo"
                             class="flex items-start gap-4 rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
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
import { ref, onMounted } from 'vue';
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

const compromisos = [
    { icon: Leaf,    titulo: 'Producto fresco y de temporada', desc: 'Trabajamos con productores que respetan los ciclos naturales y la estacionalidad de sus cultivos.' },
    { icon: Coins,   titulo: 'Precio justo para todos',         desc: 'El productor fija su precio y recibe el pago íntegro. Nuestra sostenibilidad no depende de quitar a los pequeños.' },
    { icon: Search,  titulo: 'Transparencia total',             desc: 'Sabes quién produce lo que compras, dónde está su tienda y cómo trabaja.' },
    { icon: Truck,   titulo: 'Entrega rápida en Lanzarote',     desc: 'Todos los pedidos se entregan dentro de la isla, minimizando la huella de carbono del transporte.' },
    { icon: Recycle, titulo: 'Economía circular',               desc: 'Fomentamos el uso de embalajes reutilizables y prácticas de producción responsables con el entorno.' },
];
</script>
