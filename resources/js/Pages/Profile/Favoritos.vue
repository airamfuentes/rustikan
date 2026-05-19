<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import MapaTiendas from '@/Components/MapaTiendas.vue';
import { useI18n } from '@/Composables/useI18n';
import { useCategorias } from '@/Composables/useCategorias';
import { useFavoritos } from '@/Composables/useFavoritos';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Heart } from 'lucide-vue-next';

useDarkMode();
const { t } = useI18n();
const { nombre: categoriaNombre } = useCategorias();
const { toggleFavorito, esFavorito } = useFavoritos();

const props = defineProps({
    tiendas: { type: Array, default: () => [] },
});

const total = computed(() => props.tiendas.length);
const tiendasConCoords = computed(() =>
    props.tiendas.filter(t => t.latitud && t.longitud)
);
</script>

<template>
    <Head :title="t('favs.title')" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-rose-500 via-rose-600 to-pink-700 pb-16 pt-32 text-white">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20 backdrop-blur-sm">
                        <Heart class="h-7 w-7" fill="currentColor" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold sm:text-4xl">{{ t('favs.title') }}</h1>
                        <p class="mt-1 text-sm text-rose-100">{{ t('favs.subtitle') }}</p>
                    </div>
                </div>
                <p v-if="total > 0" class="mt-4 inline-block rounded-full bg-white/15 px-4 py-1 text-sm font-semibold backdrop-blur-sm">
                    {{ t(total === 1 ? 'favs.count_one' : 'favs.count_other', { n: total }) }}
                </p>
            </div>
        </section>

        <!-- Contenido -->
        <main class="mx-auto max-w-5xl flex-1 px-4 py-10 sm:px-6">
            <!-- Sin favoritos -->
            <div v-if="total === 0" class="flex flex-col items-center py-20 text-center">
                <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
                    <Heart class="h-10 w-10 text-rose-400" />
                </div>
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-200">{{ t('favs.empty_title') }}</h2>
                <p class="mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">{{ t('favs.empty_sub') }}</p>
                <Link href="/" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-rose-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-rose-600">
                    {{ t('favs.explore') }}
                </Link>
            </div>

            <!-- Lista -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="tienda in tiendas"
                    :key="tienda.id"
                    :href="`/tienda/${tienda.slug}`"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="relative h-48 overflow-hidden">
                        <img
                            :src="tienda.imagen_portada ? (tienda.imagen_portada.startsWith('http') ? tienda.imagen_portada : `/storage/${tienda.imagen_portada}`) : tienda.logo ? (tienda.logo.startsWith('http') ? tienda.logo : `/storage/${tienda.logo}`) : '/images/logo.png'"
                            :alt="tienda.nombre"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <span v-if="tienda.categoria" class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-700 backdrop-blur-sm">
                            {{ categoriaNombre(tienda.categoria) }}
                        </span>
                        <button
                            @click.prevent.stop="toggleFavorito(tienda.id, tienda.nombre)"
                            class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-all hover:scale-110 shadow-sm"
                            :title="t('cat_page.fav_remove')"
                        >
                            <svg class="h-4 w-4 transition-colors" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 :class="esFavorito(tienda.id) ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-gray-400'">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-rose-600 transition-colors">{{ tienda.nombre }}</h3>
                        <p class="mt-1.5 flex-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ tienda.descripcion }}</p>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                            <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="max-w-[140px] truncate">{{ tienda.direccion }}</span>
                            </div>
                            <span class="flex items-center gap-1 text-xs font-semibold text-yellow-600">
                                <svg class="h-3.5 w-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                {{ Number(tienda.valoracion).toFixed(1) }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Mapa de favoritas (solo si hay alguna con coordenadas) -->
            <section v-if="tiendasConCoords.length > 0" class="mt-12">
                <div class="mb-5 text-center">
                    <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                        {{ t('favs.map_title') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ t('favs.map_subtitle') }}
                    </p>
                </div>
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <MapaTiendas
                        :tiendas="tiendasConCoords"
                        :categorias="[]"
                        height="460px"
                    />
                </div>
            </section>
        </main>

        <FooterPublico />
    </div>
</template>
