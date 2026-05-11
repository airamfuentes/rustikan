<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import CategoriaIcono from '@/Components/CategoriaIcono.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { useCategorias } from '@/Composables/useCategorias';
import { useFavoritos } from '@/Composables/useFavoritos';
import { Search, ArrowLeft, Store, Package, Star, X } from 'lucide-vue-next';

useDarkMode();
const { t } = useI18n();
const { nombre: categoriaNombre } = useCategorias();
const { toggleFavorito, esFavorito } = useFavoritos();

const props = defineProps({
    q:                  { type: String, default: '' },
    tiendas:            { type: Array,  default: () => [] },
    productos:          { type: Array,  default: () => [] },
    sugerenciasTiendas: { type: Array,  default: () => [] },
    categorias:         { type: Array,  default: () => [] },
});

const hayResultados = computed(() => props.tiendas.length > 0 || props.productos.length > 0);

const refinado = ref(props.q);
const enviarRefinado = () => {
    const q = refinado.value.trim();
    if (q.length < 2) return;
    router.visit(`/buscar?q=${encodeURIComponent(q)}`);
};

// Imágenes de categorías (mismo mapeo que el resto del proyecto)
const categoriaImagen = {
    'frutas-y-verduras':   '/images/furtas_verduras.png',
    'carnes':              '/images/carnes.png',
    'pescados-y-mariscos': '/images/pescados_mariscos.png',
    'panaderia':           '/images/panaderia.png',
    'lacteos-y-quesos':    '/images/lacteos_quesos.png',
    'vinoteca':            '/images/vinoteca.png',
    'artesania':           '/images/artesania.png',
};

const tiendaImagen = (t) =>
    t.imagen_portada ? `/storage/${t.imagen_portada}` :
    t.logo ? `/storage/${t.logo}` : '/images/logo.png';
</script>

<template>
    <Head :title="t('search_page.title_with_query', { q })" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col">
        <NavbarPublico />

        <!-- Hero limpio: solo título + chip con la query actual -->
        <section class="relative overflow-hidden bg-gradient-to-br from-primary-500 via-primary-600 to-primary-800 pb-24 pt-28 text-white sm:pt-32">
            <!-- Decoración sutil -->
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-48 w-48 rounded-full bg-tierra-500/30 blur-3xl"></div>

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6">
                <div class="flex items-center justify-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-primary-100/90">
                    <Search class="h-4 w-4" />
                    <span>{{ t('search_page.section_stores') }} · {{ t('search_page.section_products') }}</span>
                </div>

                <h1 class="mt-4 text-center text-3xl font-extrabold leading-tight sm:text-4xl">
                    <template v-if="hayResultados">
                        {{ t('search_page.title_with_query', { q }) }}
                    </template>
                    <template v-else>
                        {{ t('search_page.no_match_title', { q }) }}
                    </template>
                </h1>

                <!-- Chip con la query actual: clic = volver al inicio -->
                <div class="mt-4 flex justify-center">
                    <Link
                        href="/"
                        class="group inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-sm font-medium text-white ring-1 ring-white/20 backdrop-blur-sm transition-colors hover:bg-white/25"
                        :title="t('search_page.back_home')"
                    >
                        <span class="opacity-80">"{{ q }}"</span>
                        <X class="h-3.5 w-3.5 opacity-70 transition-opacity group-hover:opacity-100" />
                    </Link>
                </div>

                <p class="mx-auto mt-3 max-w-xl text-center text-sm text-primary-50/90 sm:text-base">
                    <template v-if="hayResultados">
                        {{ t('search_page.subtitle_results', { n_t: tiendas.length, n_p: productos.length }) }}
                    </template>
                    <template v-else>
                        {{ t('search_page.subtitle_empty') }}
                    </template>
                </p>
            </div>
        </section>

        <!-- Barra de búsqueda flotante que se solapa con el hero -->
        <div class="relative z-10 -mt-10 px-4 sm:px-6">
            <form @submit.prevent="enviarRefinado"
                  class="mx-auto flex max-w-3xl items-center gap-2 rounded-2xl bg-white p-2 shadow-xl ring-1 ring-gray-200/60 dark:bg-gray-800 dark:ring-gray-700">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                    <Search class="h-5 w-5" />
                </div>
                <input
                    v-model="refinado"
                    type="search"
                    :placeholder="t('search_page.refine_placeholder')"
                    maxlength="100"
                    class="min-w-0 flex-1 border-0 bg-transparent text-sm text-gray-900 outline-none placeholder:text-gray-400 dark:text-white"
                />
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-primary-700 hover:shadow-md disabled:opacity-50"
                    :disabled="refinado.trim().length < 2 || refinado.trim() === q"
                >
                    {{ t('home.search_button') }}
                </button>
            </form>
        </div>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-12 sm:px-6 lg:px-8">

            <!-- ───── Con resultados ───── -->
            <template v-if="hayResultados">

                <!-- Tiendas -->
                <section v-if="tiendas.length > 0" class="mb-12">
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <Store class="h-5 w-5 text-primary-500" />
                                <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                                    {{ t('search_page.section_stores') }}
                                </h2>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ t(tiendas.length === 1 ? 'search_page.count_one_t' : 'search_page.count_other_t', { n: tiendas.length }) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="tienda in tiendas"
                            :key="`t-${tienda.id}`"
                            :href="`/tienda/${tienda.slug}`"
                            class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-gray-800"
                        >
                            <div class="relative h-44 overflow-hidden">
                                <img
                                    :src="tiendaImagen(tienda)"
                                    :alt="tienda.nombre"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <span v-if="tienda.categoria" class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-700 backdrop-blur-sm">
                                    {{ categoriaNombre(tienda.categoria) }}
                                </span>
                                <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-800 backdrop-blur-sm">
                                    <Star class="h-3.5 w-3.5 fill-yellow-500 text-yellow-500" />
                                    {{ Number(tienda.valoracion).toFixed(1) }}
                                </span>
                                <button
                                    @click.prevent.stop="toggleFavorito(tienda.id, tienda.nombre)"
                                    class="absolute bottom-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 shadow-sm backdrop-blur-sm transition-all hover:scale-110"
                                    :title="esFavorito(tienda.id) ? t('cat_page.fav_remove') : t('cat_page.fav_add')"
                                >
                                    <svg class="h-4 w-4 transition-colors" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                         :class="esFavorito(tienda.id) ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-gray-400'">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="text-base font-bold text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white">{{ tienda.nombre }}</h3>
                                <p class="mt-1.5 line-clamp-2 flex-1 text-sm text-gray-500 dark:text-gray-400">{{ tienda.descripcion }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                                    <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                        <Package class="h-3.5 w-3.5" />
                                        {{ t('search_page.n_products', { n: tienda.productos_count ?? 0 }) }}
                                    </span>
                                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                                        {{ t('search_page.view_store') }} →
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- Productos -->
                <section v-if="productos.length > 0">
                    <div class="mb-5 flex items-end justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <Package class="h-5 w-5 text-primary-500" />
                                <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                                    {{ t('search_page.section_products') }}
                                </h2>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ t(productos.length === 1 ? 'search_page.count_one_p' : 'search_page.count_other_p', { n: productos.length }) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 grid-cols-2 sm:grid-cols-3 lg:grid-cols-4">
                        <Link
                            v-for="producto in productos"
                            :key="`p-${producto.id}`"
                            :href="`/tienda/${producto.tienda?.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-gray-800"
                        >
                            <div class="relative aspect-square overflow-hidden">
                                <img
                                    :src="producto.imagen || '/images/logo.png'"
                                    :alt="producto.nombre"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div class="absolute left-2 top-2 rounded-full bg-primary-500 px-2 py-0.5 text-[10px] font-bold text-white shadow">Km 0</div>
                            </div>
                            <div class="flex flex-1 flex-col gap-1 p-4">
                                <h3 class="line-clamp-2 text-sm font-bold text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white">
                                    {{ producto.nombre }}
                                </h3>
                                <p v-if="producto.tienda" class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ producto.tienda.nombre }}
                                </p>
                                <div class="mt-auto flex items-center justify-between pt-2">
                                    <span class="text-base font-extrabold text-primary-600 dark:text-primary-400">
                                        {{ Number(producto.precio).toFixed(2) }}€
                                    </span>
                                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 group-hover:text-primary-600 dark:group-hover:text-primary-400">
                                        →
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>
            </template>

            <!-- ───── Sin resultados — sugerencias ───── -->
            <template v-else>

                <!-- Estado vacío principal -->
                <section class="mb-12 rounded-3xl bg-white p-10 text-center shadow-sm dark:bg-gray-800 sm:p-14">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40">
                        <Search class="h-8 w-8 text-primary-500" />
                    </div>
                    <h2 class="text-xl font-extrabold text-gray-900 dark:text-white sm:text-2xl">
                        {{ t('search_page.no_match_title', { q }) }}
                    </h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                        {{ t('search_page.no_match_sub') }}
                    </p>
                    <Link href="/" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">
                        <ArrowLeft class="h-4 w-4" />
                        {{ t('search_page.back_home') }}
                    </Link>
                </section>

                <!-- Categorías -->
                <section v-if="categorias.length > 0" class="mb-12">
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                            {{ t('search_page.categories_title') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ t('search_page.categories_sub') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-3 gap-x-4 gap-y-6 sm:flex sm:flex-nowrap sm:justify-center sm:gap-8">
                        <Link
                            v-for="(cat, index) in categorias"
                            :key="cat.id"
                            :href="`/categoria/${cat.slug}`"
                            :class="[
                                'group flex flex-col items-center',
                                index === categorias.length - 1 && categorias.length % 3 === 1 ? 'col-start-2 sm:col-auto' : '',
                            ]"
                        >
                            <div class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-2 border-gray-200 bg-[#f0ddb8] shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:border-primary-400 group-hover:shadow-xl dark:border-gray-600 sm:h-24 sm:w-24">
                                <img
                                    v-if="categoriaImagen[cat.slug]"
                                    :src="categoriaImagen[cat.slug]"
                                    :alt="categoriaNombre(cat)"
                                    class="h-full w-full scale-125 object-cover"
                                />
                                <CategoriaIcono v-else :slug="cat.slug" :icono="cat.icono" class="h-7 w-7 text-gray-700 transition-colors group-hover:text-primary-500 dark:text-gray-200 sm:h-9 sm:w-9" />
                            </div>
                            <span class="mt-2 w-full text-center text-[10px] font-semibold uppercase leading-tight tracking-wide text-gray-600 transition-colors group-hover:text-primary-500 dark:text-gray-300 sm:text-xs">
                                {{ categoriaNombre(cat) }}
                            </span>
                        </Link>
                    </div>
                </section>

                <!-- Sugerencias de tiendas -->
                <section v-if="sugerenciasTiendas.length > 0">
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-2xl">
                            {{ t('search_page.suggestions_title') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ t('search_page.suggestions_sub') }}
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="tienda in sugerenciasTiendas"
                            :key="`s-${tienda.id}`"
                            :href="`/tienda/${tienda.slug}`"
                            class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-gray-800"
                        >
                            <div class="relative h-44 overflow-hidden">
                                <img
                                    :src="tiendaImagen(tienda)"
                                    :alt="tienda.nombre"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <span v-if="tienda.categoria" class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-700 backdrop-blur-sm">
                                    {{ categoriaNombre(tienda.categoria) }}
                                </span>
                                <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-800 backdrop-blur-sm">
                                    <Star class="h-3.5 w-3.5 fill-yellow-500 text-yellow-500" />
                                    {{ Number(tienda.valoracion).toFixed(1) }}
                                </span>
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="text-base font-bold text-gray-900 transition-colors group-hover:text-primary-600 dark:text-white">{{ tienda.nombre }}</h3>
                                <p class="mt-1.5 line-clamp-2 flex-1 text-sm text-gray-500 dark:text-gray-400">{{ tienda.descripcion }}</p>
                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                                    <span class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                        <Package class="h-3.5 w-3.5" />
                                        {{ t('search_page.n_products', { n: tienda.productos_count ?? 0 }) }}
                                    </span>
                                    <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                                        {{ t('search_page.view_store') }} →
                                    </span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </section>
            </template>
        </main>

        <FooterPublico />
    </div>
</template>
