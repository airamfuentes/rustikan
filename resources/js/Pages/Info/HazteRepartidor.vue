<template>
    <div :class="isDark ? 'dark' : ''">
        <Head title="Hazte repartidor - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-orange-500 via-red-600 to-rose-700 pt-24 pb-20 text-white">
            <div class="mx-auto max-w-5xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.delivery.title') }}</h1>
                <p class="mt-4 text-xl text-orange-100 max-w-2xl mx-auto">
                    {{ t('info.delivery.subtitle') }}
                </p>
                <a href="mailto:info@rustikan.com?subject=Quiero ser repartidor en Rustikan"
                    class="mt-8 inline-block rounded-full bg-white text-orange-600 px-8 py-3 font-bold text-sm hover:bg-orange-50 transition-colors shadow-lg">
                    Unirme al equipo →
                </a>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">

                <!-- Ventajas -->
                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-10">{{ t('info.delivery.advantages_title') }}</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-16">
                    <div v-for="v in ventajas" :key="v.titulo" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                            <component :is="v.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ v.titulo }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ v.desc }}</p>
                    </div>
                </div>

                <!-- Requisitos -->
                <div class="mb-16 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ t('info.delivery.requirements_title') }}</h2>
                    <ul class="space-y-3">
                        <li v-for="req in requisitos" :key="req" class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ req }}</span>
                        </li>
                    </ul>
                </div>

                <!-- CTA -->
                <div class="rounded-2xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ t('info.delivery.contact_title') }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Mándanos un email con tus datos y te explicamos todo el proceso de incorporación.</p>
                    <a href="mailto:info@rustikan.com?subject=Quiero ser repartidor en Rustikan"
                        class="inline-block rounded-full bg-orange-500 text-white px-8 py-3 font-semibold text-sm hover:bg-orange-600 transition-colors">
                        Solicitar información
                    </a>
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
import { Clock, Coins, Map, Smartphone, Handshake, Leaf } from 'lucide-vue-next';
const { isDark } = useDarkMode();

const { t } = useI18n();

const ventajas = [
    { icon: Clock,      titulo: 'Horario flexible',  desc: 'Elige tus horas. Puedes trabajar a tiempo parcial o completo según tu disponibilidad.' },
    { icon: Coins,      titulo: 'Ingresos extra',    desc: 'Cobra por cada entrega completada. Cuantas más rutas hagas, más ganas.' },
    { icon: Map,        titulo: 'Solo en Lanzarote', desc: 'Todas las entregas son dentro de la isla. Sin largos desplazamientos ni viajes interinsulares.' },
    { icon: Smartphone, titulo: 'App sencilla',      desc: 'Recibe y gestiona tus rutas desde tu móvil, con navegación integrada.' },
    { icon: Handshake,  titulo: 'Apoyo continuo',    desc: 'Tendrás soporte del equipo Rustikan ante cualquier incidencia.' },
    { icon: Leaf,       titulo: 'Impacto positivo',  desc: 'Formas parte de una cadena de valor justa que apoya a productores locales.' },
];

const requisitos = [
    'Ser mayor de 18 años y residir en Lanzarote',
    'Disponer de vehículo propio (coche, moto o bicicleta eléctrica)',
    'Carnet de conducir en vigor',
    'Teléfono móvil con Android 8+ o iOS 14+',
    'Alta como autónomo o poder facturar (te asesoramos si lo necesitas)',
    'Ganas de hacer las cosas bien y trato amable con clientes y productores',
];
</script>
