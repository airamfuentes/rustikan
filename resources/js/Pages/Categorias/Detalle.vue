<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import MapaTiendas from '@/Components/MapaTiendas.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    categoria: { type: Object, required: true },
    tiendas:   { type: Array,  default: () => [] },
});

const scrolled    = ref(false);
const searchRef   = ref(null);
let scrollTimeout = null;

const handleScroll = () => {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout  = null;
    }, 100);
};

const busqueda    = ref('');
const ordenActivo = ref('valoracion');
const vistaActiva = ref('grid');
const showMap     = ref(false);
const cardRefs    = ref([]);

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
    window.addEventListener('scroll', handleScroll);
    setupObserver();
    observeCards();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (scrollTimeout) clearTimeout(scrollTimeout);
    if (observer) observer.disconnect();
});
</script>

<template>
    <Head :title="`${categoria.nombre} – Rustikan`" />

    <div class="min-h-screen flex flex-col bg-gray-50">

        <!-- Navbar transformable -->
        <nav
            :class="[
                'fixed z-50 bg-white transition-all duration-300',
                scrolled
                    ? 'top-0 left-0 right-0 border-b border-gray-200 shadow-md'
                    : 'top-4 left-12 right-12 rounded-2xl border border-gray-200 shadow-sm',
            ]"
        >
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <Link href="/" class="flex shrink-0 items-center">
                        <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                    </Link>

                    <div ref="searchRef" class="hidden flex-1 max-w-2xl md:block">
                        <div class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="busqueda"
                                @input="observeCards"
                                type="text"
                                :placeholder="`Buscar en ${categoria.nombre}...`"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-12 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-4">
                        <CarritoCompra />
                        <LanguageSwitcher />
                        <Link
                            v-if="!user"
                            :href="route('login')"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                        >Acceder</Link>
                        <Link
                            v-else-if="user && user.role !== 'admin'"
                            :href="route('dashboard')"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <Link
                            v-if="user && user.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl"
                        >🛡️ Admin</Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero de categoría -->
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
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-white/10 text-5xl shadow-2xl backdrop-blur-sm ring-1 ring-white/20">
                            {{ categoria.icono }}
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

            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="block h-12 w-full text-gray-50" fill="currentColor">
                <path d="M0 60C200 20 400 0 600 10C800 20 1000 50 1200 60V60H0Z" />
            </svg>
        </div>

        <!-- Barra de controles -->
        <div class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <!-- Buscador móvil -->
                <div class="relative md:hidden">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="busqueda"
                        @input="observeCards"
                        type="text"
                        :placeholder="`Buscar en ${categoria.nombre}...`"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                    />
                </div>

                <div class="flex items-center gap-2 overflow-x-auto">
                    <span class="hidden text-xs font-medium text-gray-400 sm:block">Ordenar:</span>
                    <button
                        v-for="op in [
                            { key: 'valoracion', label: '⭐ Valoración' },
                            { key: 'resenas',    label: '💬 Reseñas' },
                            { key: 'productos',  label: '📦 Productos' },
                            { key: 'nombre',     label: '🔤 Nombre' },
                        ]"
                        :key="op.key"
                        @click="ordenActivo = op.key; observeCards()"
                        :class="[
                            'whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold transition-all',
                            ordenActivo === op.key
                                ? 'bg-primary-500 text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                        ]"
                    >{{ op.label }}</button>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Botón mapa -->
                    <button
                        v-if="tiendasConMapa"
                        @click="showMap = !showMap"
                        :class="[
                            'rounded-full px-3 py-1.5 text-xs font-semibold transition-all',
                            showMap
                                ? 'bg-primary-500 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                        ]"
                    >🗺️ Mapa</button>

                    <!-- Toggle vista -->
                    <div class="hidden items-center gap-1 rounded-lg border border-gray-200 bg-white p-0.5 sm:flex">
                        <button
                            @click="vistaActiva = 'grid'"
                            :class="['rounded-md p-1.5 transition-colors', vistaActiva === 'grid' ? 'bg-primary-500 text-white' : 'text-gray-400 hover:text-gray-600']"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 13.5v-3z"/>
                            </svg>
                        </button>
                        <button
                            @click="vistaActiva = 'list'"
                            :class="['rounded-md p-1.5 transition-colors', vistaActiva === 'list' ? 'bg-primary-500 text-white' : 'text-gray-400 hover:text-gray-600']"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/>
                            </svg>
                        </button>
                    </div>

                    <span class="hidden text-xs text-gray-400 sm:block">
                        {{ tiendasOrdenadas.length }} resultado{{ tiendasOrdenadas.length !== 1 ? 's' : '' }}
                    </span>
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
            <div v-if="showMap" class="overflow-hidden bg-white border-b border-gray-200">
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
                <div class="mb-4 text-6xl">{{ categoria.icono }}</div>
                <h2 class="text-xl font-semibold text-gray-700">
                    <template v-if="busqueda.trim()">Sin coincidencias para "{{ busqueda }}"</template>
                    <template v-else>Aún no hay establecimientos en esta categoría</template>
                </h2>
                <p class="mt-2 text-sm text-gray-400">Pronto encontrarás productores locales de {{ categoria.nombre }} aquí.</p>
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
                        class="group relative flex flex-col overflow-hidden rounded-3xl bg-white shadow-md transition-all duration-300 hover:shadow-xl sm:flex-row"
                    >
                        <div class="relative h-56 overflow-hidden sm:h-72 sm:w-2/5">
                            <img
                                :src="tiendaDestacada.imagen_portada ? `/storage/${tiendaDestacada.imagen_portada}` : tiendaDestacada.logo ? `/storage/${tiendaDestacada.logo}` : '/images/logo.png'"
                                :alt="tiendaDestacada.nombre"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white/50 hidden sm:block"></div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent sm:hidden"></div>
                        </div>

                        <div class="flex flex-1 flex-col justify-between p-6 sm:p-8">
                            <div>
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-primary-100 px-3 py-1 text-xs font-bold text-primary-700">
                                        {{ categoria.icono }} {{ categoria.nombre }}
                                    </span>
                                    <span class="flex items-center gap-1 rounded-full bg-yellow-50 px-3 py-1 text-xs font-bold text-yellow-700">
                                        <svg class="h-3.5 w-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        {{ Number(tiendaDestacada.valoracion).toFixed(1) }}
                                        <span class="font-normal text-yellow-600">({{ tiendaDestacada.total_resenas }})</span>
                                    </span>
                                    <span v-if="tiendaDestacada.productos_count" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                        📦 {{ tiendaDestacada.productos_count }} productos
                                    </span>
                                </div>
                                <h2 class="text-2xl font-extrabold text-gray-900 sm:text-3xl">{{ tiendaDestacada.nombre }}</h2>
                                <p class="mt-2 text-sm leading-relaxed text-gray-500 line-clamp-3">{{ tiendaDestacada.descripcion }}</p>
                            </div>

                            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-1.5 text-sm text-gray-500">
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

                <!-- Resto de tiendas -->
                <div v-if="restoTiendas.length > 0">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="inline-block h-1 w-8 rounded-full bg-gray-300"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Todas las tiendas</span>
                    </div>

                    <!-- Vista Cuadrícula -->
                    <div v-if="vistaActiva === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="(tienda, idx) in restoTiendas"
                            :key="tienda.id"
                            :href="`/tienda/${tienda.slug}`"
                            class="card-animate group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
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
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            </div>

                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="text-base font-bold text-gray-900 group-hover:text-primary-600 transition-colors">{{ tienda.nombre }}</h3>
                                <p class="mt-1.5 flex-1 text-sm text-gray-500 line-clamp-2">{{ tienda.descripcion }}</p>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                                    <div class="flex items-center gap-1 text-xs text-gray-400">
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

                    <!-- Vista Lista -->
                    <div v-else class="space-y-4">
                        <Link
                            v-for="(tienda, idx) in restoTiendas"
                            :key="tienda.id"
                            :href="`/tienda/${tienda.slug}`"
                            class="card-animate group relative flex overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:shadow-lg"
                            :style="{ '--delay': `${(idx + 1) * 60}ms` }"
                        >
                            <div class="relative h-auto w-28 flex-shrink-0 overflow-hidden sm:w-40">
                                <img
                                    :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'"
                                    :alt="tienda.nombre"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                />
                            </div>

                            <div class="flex flex-1 flex-col justify-between p-4 sm:p-5">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h3 class="font-bold text-gray-900 group-hover:text-primary-600 transition-colors">{{ tienda.nombre }}</h3>
                                        <div class="flex items-center gap-1 rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-bold text-yellow-700">
                                            <svg class="h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            {{ Number(tienda.valoracion).toFixed(1) }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-500 line-clamp-2">{{ tienda.descripcion }}</p>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ tienda.direccion }}
                                    </span>
                                    <span v-if="tienda.productos_count" class="flex items-center gap-1">
                                        📦 {{ tienda.productos_count }} productos
                                    </span>
                                    <span class="flex items-center gap-1">
                                        💬 {{ tienda.total_resenas }} reseñas
                                    </span>
                                </div>
                            </div>

                            <div class="hidden items-center pr-5 sm:flex">
                                <svg class="h-5 w-5 text-gray-300 transition-colors group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>

                            <div class="absolute bottom-0 left-0 right-0 h-0.5 origin-left scale-x-0 rounded-b-2xl bg-primary-500 transition-transform duration-300 group-hover:scale-x-100"></div>
                        </Link>
                    </div>
                </div>
            </template>
        </main>

        <footer class="mt-16 border-t border-gray-200 bg-white py-8 text-center text-sm text-gray-400">
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