<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import MapaTiendas from '@/Components/MapaTiendas.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import SkeletonTiendaCard from '@/Components/SkeletonTiendaCard.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useFavoritos } from '@/Composables/useFavoritos';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import CategoriaIcono from '@/Components/CategoriaIcono.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isDark, toggleDark } = useDarkMode();
const { toggleFavorito, esFavorito } = useFavoritos();
const isNavigating = ref(false);

const props = defineProps({
    categoria: { type: Object, required: true },
    tiendas:   { type: Array,  default: () => [] },
});

const busqueda    = ref('');
const ordenActivo = ref('valoracion');
const showMap     = ref(true);
const showSortMenu = ref(false);
const sortMenuRef  = ref(null);
const cardRefs    = ref([]);

const sortOpciones = [
    { key: 'valoracion', label: 'Valoración',  icon: 'M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z' },
    { key: 'resenas',    label: 'Reseñas',     icon: 'M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z' },
    { key: 'productos',  label: 'Productos',   icon: 'M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z' },
    { key: 'nombre',     label: 'Nombre',      icon: 'M3.75 6.75h16.5M3.75 12h16.5M12 17.25h8.25' },
];

const normalizar = (str) =>
    (str ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

const tiendasOrdenadas = computed(() => {
    const q = normalizar(busqueda.value.trim());
    let lista = q
        ? props.tiendas.filter(t =>
              normalizar(t.nombre).includes(q) ||
              normalizar(t.direccion).includes(q) ||
              normalizar(t.descripcion).includes(q)
          )
        : [...props.tiendas];
    switch (ordenActivo.value) {
        case 'nombre':    return lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
        case 'resenas':   return lista.sort((a, b) => b.total_resenas - a.total_resenas);
        case 'productos': return lista.sort((a, b) => (b.productos_count ?? 0) - (a.productos_count ?? 0));
        default:          return lista.sort((a, b) => b.valoracion - a.valoracion);
    }
});

const tiendaDestacada = computed(() => tiendasOrdenadas.value[0] ?? null);
const restoTiendas    = computed(() => tiendasOrdenadas.value.slice(1));

const totalProductos = computed(() =>
    props.tiendas.reduce((s, t) => s + (t.productos_count ?? 0), 0)
);
const avgValoracion = computed(() => {
    if (!props.tiendas.length) return '0.0';
    return (props.tiendas.reduce((s, t) => s + Number(t.valoracion), 0) / props.tiendas.length).toFixed(1);
});

const tiendasConMapa = computed(() =>
    props.tiendas.filter(t => t.latitud && t.longitud).length > 0
);

const handleClickOutsideSort = (e) => {
    if (sortMenuRef.value && !sortMenuRef.value.contains(e.target)) {
        showSortMenu.value = false;
    }
};

// Intersection Observer para animaciones de entrada
let observer = null;

const setupObserver = () => {
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('card-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '50px' }
    );
};

const observeCards = () => {
    nextTick(() => {
        document.querySelectorAll('.card-animate').forEach((el) => {
            if (observer) observer.observe(el);
        });
    });
};

onMounted(() => {
    setupObserver();
    observeCards();
    document.addEventListener('click', handleClickOutsideSort);
    router.on('start', () => { isNavigating.value = true; });
    router.on('finish', () => { isNavigating.value = false; });
});

onUnmounted(() => {
    if (observer) observer.disconnect();
    document.removeEventListener('click', handleClickOutsideSort);
});
</script>

<template>
    <Head :title="`${categoria.nombre} – Rustikan`" />

    <div :class="['min-h-screen flex flex-col', isDark ? 'bg-gray-950' : 'bg-gray-50']">

        <NavbarPublico :tiendas="tiendas" />
        <div class="relative overflow-hidden bg-gray-900 pt-24 pb-0">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(249,115,22,0.18)_0%,_rgba(17,24,39,1)_70%)]"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
                <!-- Miga de pan -->
                <nav class="mb-6 flex items-center gap-2 text-sm text-gray-400">
                    <Link href="/" class="transition-colors hover:text-primary-400">Inicio</Link>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-200">{{ categoria.nombre }}</span>
                </nav>

                <!-- Icono + título -->
                <div class="flex flex-col items-center gap-5 text-center sm:flex-row sm:text-left">
                    <div class="relative flex-shrink-0">
                        <div class="absolute inset-0 animate-ping-slow rounded-full bg-primary-500/20"></div>
                        <div class="absolute -inset-3 animate-ping-slow rounded-full bg-primary-500/10" style="animation-delay:0.4s"></div>
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-white/10 shadow-2xl backdrop-blur-sm ring-1 ring-white/20">
                            <CategoriaIcono :slug="categoria.slug" :icono="categoria.icono" class="h-12 w-12 text-white" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">
                            {{ categoria.nombre }}
                        </h1>
                        <p v-if="categoria.descripcion" class="mt-2 max-w-xl text-lg text-gray-300">
                            {{ categoria.descripcion }}
                        </p>
                    </div>
                </div>

                <!-- Stats ribbon -->
                <div class="mt-10 grid grid-cols-3 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10">
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="text-3xl font-extrabold text-white">{{ tiendas.length }}</span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Tiendas</span>
                    </div>
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="text-3xl font-extrabold text-white">{{ totalProductos }}</span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Productos</span>
                    </div>
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="flex items-center gap-1 text-3xl font-extrabold text-white">
                            {{ avgValoracion }}
                            <svg class="h-6 w-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Valoración media</span>
                    </div>
                </div>
            </div>

            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" :class="['block h-12 w-full', isDark ? 'text-gray-950' : 'text-gray-50']" fill="currentColor">
                <path d="M0 60C200 20 400 0 600 10C800 20 1000 50 1200 60V60H0Z" />
            </svg>
        </div>

        <!-- Barra de controles -->
        <div :class="['sticky top-0 z-40 border-b backdrop-blur-sm', isDark ? 'border-gray-700 bg-gray-900/95' : 'border-gray-200 bg-white/95']">
            <div class="mx-auto flex max-w-7xl items-center gap-2 px-4 py-3 sm:px-6 lg:px-8">

                <!-- Izquierda: Sort dropdown + Mapa toggle -->
                <div class="flex items-center gap-2">
                    <!-- Sort dropdown -->
                    <div class="relative" ref="sortMenuRef">
                        <button
                            @click.stop="showSortMenu = !showSortMenu"
                            :class="['inline-flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition-all',
                                isDark ? 'bg-gray-800 text-gray-300 hover:bg-gray-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                            </svg>
                            <span class="hidden sm:inline">Ordenar: </span>{{ sortOpciones.find(o => o.key === ordenActivo)?.label }}
                            <svg class="h-3 w-3 transition-transform" :class="showSortMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition
                            enter-active-class="transition-all duration-150 ease-out"
                            enter-from-class="opacity-0 scale-95 -translate-y-1"
                            enter-to-class="opacity-100 scale-100 translate-y-0"
                            leave-active-class="transition-all duration-100 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95 -translate-y-1"
                        >
                            <div v-if="showSortMenu" :class="['absolute left-0 top-full mt-1.5 w-44 rounded-xl border py-1 shadow-xl z-50', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                                <button
                                    v-for="op in sortOpciones"
                                    :key="op.key"
                                    @click="ordenActivo = op.key; showSortMenu = false; observeCards()"
                                    :class="['flex w-full items-center gap-2 px-3.5 py-2 text-xs font-medium transition-colors',
                                        ordenActivo === op.key
                                            ? isDark ? 'text-primary-400 bg-primary-900/30' : 'text-primary-600 bg-primary-50'
                                            : isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-600 hover:bg-gray-50']"
                                >
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path :d="op.icon" /></svg>
                                    {{ op.label }}
                                    <svg v-if="ordenActivo === op.key" class="ml-auto h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                            </div>
                        </Transition>
                    </div>

                    <!-- Mapa toggle -->
                    <button
                        v-if="tiendasConMapa"
                        @click="showMap = !showMap"
                        :class="['inline-flex items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition-all',
                            showMap
                                ? 'bg-primary-500 text-white shadow-sm'
                                : isDark ? 'bg-gray-800 text-gray-300 hover:bg-gray-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span class="hidden sm:inline">{{ showMap ? 'Ocultar mapa' : 'Mapa' }}</span>
                    </button>
                </div>

                <!-- Derecha: búsqueda compacta + contador -->
                <div class="ml-auto flex items-center gap-3">
                    <span :class="['hidden text-xs sm:block', isDark ? 'text-gray-500' : 'text-gray-400']">
                        {{ tiendasOrdenadas.length }} resultado{{ tiendasOrdenadas.length !== 1 ? 's' : '' }}
                    </span>
                    <div class="relative flex shrink-0 items-center">
                        <div class="pointer-events-none absolute left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="busqueda"
                            @input="observeCards"
                            type="search"
                            :placeholder="`Buscar en ${categoria.nombre}...`"
                            :class="['w-44 rounded-full border py-2 pl-9 pr-3 text-xs transition-all focus:w-60 focus:outline-none focus:ring-1 focus:ring-primary-400',
                                isDark ? 'bg-gray-800 border-gray-600 text-gray-100 placeholder-gray-500 focus:border-primary-400' : 'bg-gray-100 border-transparent text-gray-800 placeholder-gray-400 focus:border-gray-300 focus:bg-white']"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa expandible -->
        <Transition
            enter-active-class="transition-all duration-500 ease-out"
            enter-from-class="max-h-0 opacity-0"
            enter-to-class="max-h-[600px] opacity-100"
            leave-active-class="transition-all duration-300 ease-in"
            leave-from-class="max-h-[600px] opacity-100"
            leave-to-class="max-h-0 opacity-0"
        >
            <div v-if="showMap" :class="['overflow-hidden border-b', isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200']">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <MapaTiendas
                        :tiendas="tiendasOrdenadas"
                        :categorias="[]"
                        height="400px"
                    />
                </div>
            </div>
        </Transition>

        <!-- Contenido principal -->
        <main class="mx-auto max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8">

            <!-- Sin resultados -->
            <div v-if="tiendasOrdenadas.length === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-4 flex justify-center">
                    <CategoriaIcono :slug="categoria.slug" :icono="categoria.icono" class="h-16 w-16 text-gray-400 dark:text-gray-500" />
                </div>
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                    <template v-if="busqueda.trim()">Sin coincidencias para "{{ busqueda }}"</template>
                    <template v-else>Aún no hay establecimientos en esta categoría</template>
                </h2>
                <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">Pronto encontrarás productores locales de {{ categoria.nombre }} aquí.</p>
                <Link href="/" class="mt-6 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-600">
                    ← Volver al inicio
                </Link>
            </div>

            <template v-else>
                <!-- Tienda Destacada -->
                <div class="mb-10 card-animate" style="--delay: 0ms">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="inline-block h-1 w-8 rounded-full bg-primary-500"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-primary-500">Destacada</span>
                    </div>

                    <Link
                        :href="`/tienda/${tiendaDestacada.slug}`"
                        class="group relative flex flex-col overflow-hidden rounded-3xl bg-white dark:bg-gray-800 shadow-md transition-all duration-300 hover:shadow-xl sm:flex-row"
                    >
                        <div class="relative h-56 overflow-hidden sm:h-72 sm:w-2/5">
                            <img
                                :src="tiendaDestacada.imagen_portada ? `/storage/${tiendaDestacada.imagen_portada}` : tiendaDestacada.logo ? `/storage/${tiendaDestacada.logo}` : '/images/logo.png'"
                                :alt="tiendaDestacada.nombre"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white/50 dark:to-gray-800/50 hidden sm:block"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent sm:hidden"></div>
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-6 sm:p-8">
                            <div>
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-100 dark:bg-primary-900/40 px-3 py-1 text-xs font-bold text-primary-700 dark:text-primary-300">
                                        <CategoriaIcono :slug="categoria.slug" :icono="categoria.icono" class="h-3.5 w-3.5" />
                                        {{ categoria.nombre }}
                                    </span>
                                    <span class="flex items-center gap-1 rounded-full bg-yellow-50 dark:bg-yellow-900/30 px-3 py-1 text-xs font-bold text-yellow-700 dark:text-yellow-300">
                                        <svg class="h-3.5 w-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        {{ Number(tiendaDestacada.valoracion).toFixed(1) }}
                                        <span class="font-normal text-yellow-600">({{ tiendaDestacada.total_resenas }})</span>
                                    </span>
                                    <span v-if="tiendaDestacada.productos_count" class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z" /></svg>
                                        {{ tiendaDestacada.productos_count }} productos
                                    </span>
                                </div>
                                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white sm:text-3xl">{{ tiendaDestacada.nombre }}</h2>
                                <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400 line-clamp-3">{{ tiendaDestacada.descripcion }}</p>
                            </div>

                            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ tiendaDestacada.direccion }}
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all group-hover:bg-primary-600 group-hover:shadow-md">
                                    Ver tienda
                                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Skeleton durante navegación -->
                <div v-if="isNavigating" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <SkeletonTiendaCard v-for="n in 8" :key="n" />
                </div>

                <!-- Resto de tiendas -->
                <div v-else-if="restoTiendas.length > 0">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="inline-block h-1 w-8 rounded-full bg-gray-300"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Todas las tiendas</span>
                    </div>

                    <!-- Vista Cuadrícula -->
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="(tienda, idx) in restoTiendas"
                            :key="tienda.id"
                            :href="`/tienda/${tienda.slug}`"
                            class="card-animate group relative flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                            :style="{ '--delay': `${(idx + 1) * 80}ms` }"
                        >
                            <div class="relative h-48 overflow-hidden">
                                <img
                                    :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'"
                                    :alt="tienda.nombre"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                                <div v-if="tienda.productos_count" class="absolute left-3 top-3 flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-semibold text-gray-700 backdrop-blur-sm">
                                    <svg class="h-3.5 w-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    {{ tienda.productos_count }}
                                </div>
                                <div class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-gray-800 backdrop-blur-sm">
                                    <svg class="h-3.5 w-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ Number(tienda.valoracion).toFixed(1) }}
                                </div>
                                <!-- Botón favorito -->
                                <button
                                    @click.prevent="toggleFavorito(tienda.id)"
                                    class="absolute bottom-3 right-3 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-all hover:scale-110 shadow-sm"
                                    :title="esFavorito(tienda.id) ? 'Quitar de favoritos' : 'Añadir a favoritos'"
                                >
                                    <svg class="h-4 w-4 transition-colors" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        :class="esFavorito(tienda.id) ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-gray-400'"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                </button>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors">{{ tienda.nombre }}</h3>
                                <p class="mt-1.5 flex-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ tienda.descripcion }}</p>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-3">
                                    <div class="flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="max-w-[140px] truncate">{{ tienda.direccion }}</span>
                                    </div>
                                    <span class="flex items-center gap-1 text-xs text-gray-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                        {{ tienda.total_resenas }}
                                    </span>
                                </div>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 h-0.5 origin-left scale-x-0 rounded-b-2xl bg-primary-500 transition-transform duration-300 group-hover:scale-x-100"></div>
                        </Link>
                    </div>
                </div>
            </template>
        </main>

        <footer :class="['mt-16 border-t py-8 text-center text-sm', isDark ? 'border-gray-800 bg-gray-950 text-gray-600' : 'border-gray-200 bg-white text-gray-400']">
            <p>&copy; {{ new Date().getFullYear() }} Rustikan · Productos locales de Lanzarote</p>
        </footer>
    </div>
</template>

<style scoped>
@keyframes ping-slow {
    0%   { transform: scale(1);   opacity: 0.6; }
    100% { transform: scale(1.8); opacity: 0; }
}
.animate-ping-slow { animation: ping-slow 2.5s ease-out infinite; }

/* Animaciones de entrada escalonadas */
.card-animate {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-out, transform 0.5s ease-out;
    transition-delay: var(--delay, 0ms);
}
.card-animate.card-visible {
    opacity: 1;
    transform: translateY(0);
}
</style>