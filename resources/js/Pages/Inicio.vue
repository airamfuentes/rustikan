<script setup>
import { computed, onMounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MapaTiendas from '@/Components/MapaTiendas.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import CategoriaIcono from '@/Components/CategoriaIcono.vue';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { useCategorias } from '@/Composables/useCategorias';

const { t } = useI18n();
const { nombre: categoriaNombre } = useCategorias();
const page = usePage();
const user = computed(() => page.props.auth.user);
const { success } = useToasts();

// Imágenes de categorías
const categoriaImagen = {
    'frutas-y-verduras':   '/images/furtas_verduras.png',
    'carnes':              '/images/carnes.png',
    'pescados-y-mariscos': '/images/pescados_mariscos.png',
    'panaderia':           '/images/panaderia.png',
    'lacteos-y-quesos':    '/images/lacteos_quesos.png',
    'vinoteca':            '/images/vinoteca.png',
    'artesania':           '/images/artesania.png',
};

const props = defineProps({
    tiendas: {
        type: Array,
        default: () => [],
    },
    categorias: {
        type: Array,
        default: () => [],
    },
});

onMounted(() => {
    // Mostrar toast si viene de ?verified=1 (por si se llega sin flash de sesión)
    const params = new URLSearchParams(window.location.search);
    if (params.get('verified') === '1' && !page.props.flash?.success) {
        const nombre = user.value?.name ?? '';
        success(
            t('home.verified_title'),
            nombre ? t('home.verified_msg', { name: nombre }) : t('home.verified_msg_generic'),
        );
    }
});
</script>

<template>
    <Head title="" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <NavbarPublico :tiendas="tiendas" />

        <!-- Hero: imagen de portada a pantalla completa -->
        <div class="relative h-[360px] sm:h-[460px] lg:h-[520px] overflow-hidden">
            <div
                class="absolute inset-0 bg-cover bg-no-repeat"
                style="background-image: url('/images/fondo_portada_home.png?v=2'); background-position: center 35%;"
            ></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-transparent"></div>

            <!-- Curva inferior -->
            <div class="absolute bottom-0 left-0 right-0 z-10">
                <svg viewBox="0 0 1200 50" preserveAspectRatio="none" class="h-12 w-full text-gray-50 dark:text-gray-900" fill="currentColor">
                    <path d="M0 50C300 10 900 10 1200 50V50H0Z" />
                </svg>
            </div>
        </div>

        <!-- Sección de Categorías -->
        <section id="categorias" class="py-12 sm:py-16" v-reveal>
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Encabezado -->
                <div class="mb-10 text-center">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ t('home.categories_title') }}</h2>
                </div>

                <!-- Grid en móvil (3 por fila), fila única centrada en desktop.
                     Si la última fila tiene un solo huérfano, lo centramos saltando 1 columna. -->
                <div class="grid grid-cols-3 gap-x-4 gap-y-6 sm:flex sm:flex-nowrap sm:justify-center sm:gap-8">
                    <Link
                        v-for="(cat, index) in categorias"
                        :key="cat.id"
                        :href="route('categoria.tiendas', cat.slug)"
                        :class="[
                            'group flex flex-col items-center',
                            // Centrar el último elemento si queda huérfano (resto = 1 al dividir entre 3) en móvil
                            index === categorias.length - 1 && categorias.length % 3 === 1
                                ? 'col-start-2 sm:col-auto'
                                : '',
                            // Si quedan dos huérfanos en la última fila, centrarlos como pareja en móvil
                            index === categorias.length - 2 && categorias.length % 3 === 2
                                ? 'col-start-1 col-end-2 justify-self-end sm:justify-self-auto sm:col-auto'
                                : '',
                            index === categorias.length - 1 && categorias.length % 3 === 2
                                ? 'col-start-2 col-end-3 justify-self-start sm:justify-self-auto sm:col-auto'
                                : '',
                        ]"
                    >
                        <!-- Círculo -->
                        <div class="flex h-20 w-20 sm:h-24 sm:w-24 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-600 bg-[#f0ddb8] shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:border-primary-400 group-hover:shadow-xl overflow-hidden">
                            <img
                                v-if="categoriaImagen[cat.slug]"
                                :src="categoriaImagen[cat.slug]"
                                :alt="categoriaNombre(cat)"
                                class="h-full w-full object-cover scale-125"
                            />
                            <CategoriaIcono v-else :slug="cat.slug" :icono="cat.icono" class="h-7 w-7 sm:h-9 sm:w-9 text-gray-700 dark:text-gray-200 group-hover:text-primary-500 transition-colors" />
                        </div>

                        <!-- Etiqueta debajo -->
                        <span class="mt-2 text-[10px] sm:text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 transition-colors group-hover:text-primary-500 text-center leading-tight w-full">
                            {{ categoriaNombre(cat) }}
                        </span>
                    </Link>
                </div>

                <!-- Estado vacío -->
                <div v-if="categorias.length === 0" class="py-12 text-center text-gray-400">
                    {{ t('home.categories_loading') }}
                </div>
            </div>
        </section>

        <!-- Sección Mapa de Tiendas -->
        <section id="mapa" class="relative pt-12 sm:pt-16 pb-24 sm:pb-28 bg-white dark:bg-gray-800" v-reveal>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        {{ t('home.map_title') }}
                    </h2>
                    <p class="mt-3 text-base sm:text-lg text-gray-500 dark:text-gray-400">
                        {{ t('home.map_subtitle') }}
                    </p>
                </div>

                <MapaTiendas
                    :tiendas="tiendas"
                    :categorias="categorias"
                    height="480px"
                />
            </div>

            <!-- Curva inferior: transición a sección naranja -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg class="block w-full h-20 text-primary-400" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,80 C480,0 960,0 1440,80 L1440,80 L0,80 Z" fill="currentColor"/>
                </svg>
            </div>
        </section>

        <!-- Sección Colabora con Rustikan -->
        <section class="relative overflow-hidden bg-primary-400 pt-4 pb-24 sm:pb-28">
            <div class="absolute inset-0 overflow-hidden opacity-15 pointer-events-none">
                <svg class="absolute left-1/2 top-1/2 h-32 w-32 -translate-x-1/2 -translate-y-1/2 text-primary-600" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Icono y título -->
                <div class="mb-10 sm:mb-12 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-full bg-white p-4 shadow-lg">
                            <svg class="h-10 w-10 sm:h-12 sm:w-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white">{{ t('home.collaborate_title') }}</h2>
                </div>

                <!-- Grid de opciones -->
                <div class="grid gap-12 sm:gap-8 sm:grid-cols-2 md:grid-cols-3">
                    <!-- Conoce nuestra misión -->
                    <Link :href="route('info.mision')" class="group flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-6 sm:-inset-8 h-52 w-52 sm:h-64 sm:w-64 text-white opacity-55 transition-opacity group-hover:opacity-75" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M43.3,-72.8C54.5,-63.8,60.3,-47.5,65.9,-32.1C71.5,-16.7,76.9,-2.2,75.4,11.5C73.9,25.2,65.5,38.1,54.8,48.7C44.1,59.3,31.1,67.6,16.5,71.8C1.9,76,-14.3,76.1,-28.9,71.5C-43.5,66.9,-56.5,57.6,-65.8,45.2C-75.1,32.8,-80.7,17.3,-81.2,1.5C-81.7,-14.3,-77.1,-30.4,-68.3,-43.3C-59.5,-56.2,-46.5,-65.9,-32.8,-73.5C-19.1,-81.1,-4.7,-86.6,8.7,-84.3C22.1,-82,32.1,-81.8,43.3,-72.8Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-40 w-40 sm:h-48 sm:w-48 overflow-hidden rounded-full border-4 border-white shadow-xl transition-transform group-hover:scale-105">
                                <img
                                    src="https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&h=400&fit=crop"
                                    :alt="t('home.mission_alt')"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg sm:text-xl font-bold text-white">{{ t('home.mission_title') }}</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            {{ t('home.mission_desc') }}
                        </p>
                        <span class="rounded-full bg-white px-6 sm:px-8 py-3 text-sm font-semibold text-primary-600 transition-colors group-hover:bg-gray-100">
                            {{ t('home.discover_more') }}
                        </span>
                    </Link>

                    <!-- Hazte Productor -->
                    <Link :href="route('info.vende')" class="group flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <svg class="absolute -inset-6 sm:-inset-8 h-52 w-52 sm:h-64 sm:w-64 text-white opacity-50 transition-opacity group-hover:opacity-75" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M40.9,-68.9C52.5,-60.5,60.9,-47.2,67.4,-32.7C73.9,-18.2,78.5,-2.5,77.1,13C75.7,28.5,68.3,43.8,57.7,55.5C47.1,67.2,33.3,75.3,17.8,79.8C2.3,84.3,-15,85.2,-29.5,79.6C-44,74,-55.7,62,-65.2,47.7C-74.7,33.4,-82,16.7,-82.3,-0.2C-82.6,-17.1,-75.9,-34.2,-65.6,-47.5C-55.3,-60.8,-41.4,-70.3,-27,-75.2C-12.6,-80.1,2.3,-80.4,16.3,-76.1C30.3,-71.8,29.3,-77.3,40.9,-68.9Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-40 w-40 sm:h-48 sm:w-48 overflow-hidden rounded-full border-4 border-white shadow-xl transition-transform group-hover:scale-105">
                                <img
                                    src="https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=400&h=400&fit=crop"
                                    :alt="t('home.producer_title')"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg sm:text-xl font-bold text-white">{{ t('home.producer_title') }}</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            {{ t('home.producer_desc') }}
                        </p>
                        <span class="rounded-full bg-white px-6 sm:px-8 py-3 text-sm font-semibold text-primary-600 transition-colors group-hover:bg-gray-100">
                            {{ t('home.open_store') }}
                        </span>
                    </Link>

                    <!-- Trabaja con nosotros -->
                    <Link :href="route('info.trabaja')" class="group flex flex-col items-center text-center sm:col-span-2 md:col-span-1">
                        <div class="relative mb-6">
                            <svg class="absolute -inset-6 sm:-inset-8 h-52 w-52 sm:h-64 sm:w-64 text-white opacity-45 transition-opacity group-hover:opacity-75" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-40 w-40 sm:h-48 sm:w-48 overflow-hidden rounded-full border-4 border-white shadow-xl transition-transform group-hover:scale-105">
                                <img
                                    src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&h=400&fit=crop"
                                    :alt="t('home.work_title')"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-lg sm:text-xl font-bold text-white">{{ t('home.work_title') }}</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            {{ t('home.work_desc') }}
                        </p>
                        <span class="rounded-full bg-white px-6 sm:px-8 py-3 text-sm font-semibold text-primary-600 transition-colors group-hover:bg-gray-100">
                            {{ t('home.send_cv') }}
                        </span>
                    </Link>
                </div>
            </div>

            <!-- Curva inferior: transición al footer -->
            <div class="absolute left-0 right-0 z-10" style="bottom:-2px">
                <svg class="block w-full h-24 text-gray-900" viewBox="0 0 1440 96" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,96 L0,40 C120,70 240,90 360,72 C480,54 600,10 720,4 C840,-2 960,30 1080,52 C1200,74 1320,86 1440,60 L1440,96 Z" fill="currentColor"/>
                </svg>
            </div>
        </section>

        <!-- Footer -->
        <FooterPublico />

    </div>
</template>
