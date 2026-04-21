<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useCarrito } from '@/Composables/useCarrito';
import { useDarkMode } from '@/Composables/useDarkMode';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isDark, toggleDark } = useDarkMode();

const props = defineProps({
    tienda: { type: Object, required: true },
});

// ─── Scroll navbar ────────────────────────────────────────────────────────────
const scrolled      = ref(false);
let   scrollTimeout = null;

const handleScroll = () => {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout  = null;
    }, 100);
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (scrollTimeout) clearTimeout(scrollTimeout);
});

// ─── Filtro de categorías ─────────────────────────────────────────────────────
const tabActiva = ref('Todos');

const tabs = computed(() => {
    const mapa = {};
    props.tienda.productos.forEach(p => {
        const cat = p.categoria.nombre;
        mapa[cat] = (mapa[cat] || 0) + 1;
    });
    return [
        { nombre: 'Todos', count: props.tienda.productos.length },
        ...Object.entries(mapa).map(([nombre, count]) => ({ nombre, count })),
    ];
});

const productosFiltrados = computed(() =>
    tabActiva.value === 'Todos'
        ? props.tienda.productos
        : props.tienda.productos.filter(p => p.categoria.nombre === tabActiva.value)
);

const productosDestacados = computed(() =>
    props.tienda.productos.filter(p => p.destacado).length
);

// ─── Carrito ──────────────────────────────────────────────────────────────────
const { agregarItem } = useCarrito();

// Ids de productos con animación "añadido"
const añadidos = ref(new Set());

const agregarAlCarrito = (producto, cantidad = 1) => {
    for (let i = 0; i < cantidad; i++) agregarItem(producto, props.tienda);
    añadidos.value = new Set([...añadidos.value, producto.id]);
    setTimeout(() => {
        const siguiente = new Set(añadidos.value);
        siguiente.delete(producto.id);
        añadidos.value = siguiente;
    }, 1500);
};

// ─── Modal de detalle de producto ────────────────────────────────────────────
const productoModal  = ref(null);
const cantidadModal  = ref(1);

const abrirModal = (producto) => {
    if (producto.stock === 0) return;
    productoModal.value = producto;
    cantidadModal.value = 1;
};

const cerrarModal = () => { productoModal.value = null; };

const incrementar = () => {
    if (cantidadModal.value < (productoModal.value?.stock ?? 99)) cantidadModal.value++;
};
const decrementar = () => {
    if (cantidadModal.value > 1) cantidadModal.value--;
};

const añadirDesdeModal = () => {
    agregarAlCarrito(productoModal.value, cantidadModal.value);
    cerrarModal();
};

const onKeydown = (e) => { if (e.key === 'Escape') cerrarModal(); };
onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Head :title="`${tienda.nombre} – Rustikan`" />

    <div :class="['min-h-screen transition-colors duration-300', isDark ? 'dark bg-gray-950' : 'bg-gray-50']">

        <!-- ── Navbar transformable ───────────────────────────────────────────── -->
        <nav
            :class="[
                'fixed z-50 transition-all duration-300',
                isDark ? 'bg-gray-900 border-gray-700' : 'bg-white border-gray-200',
                scrolled
                    ? 'top-0 left-0 right-0 border-b shadow-md'
                    : 'top-4 left-4 right-4 sm:left-12 sm:right-12 rounded-2xl border shadow-sm',
            ]"
        >
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <Link href="/" class="flex shrink-0 items-center">
                        <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                    </Link>

                    <div class="flex shrink-0 items-center gap-2">
                        <CarritoCompra />
                        <LanguageSwitcher />

                        <!-- Mis pedidos -->
                        <Link
                            v-if="user && user.role !== 'admin'"
                            :href="route('pedidos.index')"
                            :class="['hidden sm:flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                                isDark ? 'text-gray-300 hover:bg-gray-800' : 'text-gray-600 hover:bg-gray-100']"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Mis pedidos
                        </Link>

                        <!-- Toggle dark/light -->
                        <button
                            @click="toggleDark"
                            :class="['flex h-9 w-9 items-center justify-center rounded-full transition-colors',
                                isDark ? 'bg-gray-700 text-yellow-400 hover:bg-gray-600' : 'bg-gray-100 text-gray-500 hover:bg-gray-200']"
                            :aria-label="isDark ? 'Modo claro' : 'Modo oscuro'"
                        >
                            <Transition
                                enter-active-class="transition duration-200" enter-from-class="scale-0 rotate-90 opacity-0" enter-to-class="scale-100 rotate-0 opacity-100"
                                leave-active-class="transition duration-200" leave-from-class="scale-100 rotate-0 opacity-100" leave-to-class="scale-0 rotate-90 opacity-0"
                                mode="out-in"
                            >
                                <svg v-if="isDark" key="sun" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10A5 5 0 0012 7z" />
                                </svg>
                                <svg v-else key="moon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            </Transition>
                        </button>

                        <Link v-if="!user" :href="route('login')"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                        >Acceder</Link>
                        <Link
                            v-else-if="user.role !== 'admin'"
                            :href="route('dashboard')"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500 text-white transition-colors hover:bg-primary-600"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </Link>
                        <Link v-if="user && user.role === 'admin'" :href="route('admin.dashboard')"
                            class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl"
                        >🛡️ Admin</Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ── Hero de la tienda ──────────────────────────────────────────────── -->
        <div class="relative overflow-hidden bg-gray-900 pt-24 pb-0">
            <img
                :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'"
                :alt="tienda.nombre"
                class="absolute inset-0 h-full w-full object-cover opacity-25"
            />
            <div class="absolute inset-0 bg-gradient-to-b from-gray-900/60 via-gray-900/80 to-gray-900"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14">
                <!-- Breadcrumbs -->
                <nav class="mb-6 flex items-center gap-2 text-sm text-gray-400">
                    <Link href="/" class="transition-colors hover:text-primary-400">Inicio</Link>
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <Link v-if="tienda.categoria" :href="`/categoria/${tienda.categoria.slug}`" class="transition-colors hover:text-primary-400">
                        {{ tienda.categoria.nombre }}
                    </Link>
                    <svg v-if="tienda.categoria" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-200">{{ tienda.nombre }}</span>
                </nav>

                <!-- Info tienda -->
                <div class="flex flex-col items-start gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex items-center gap-5">
                        <div v-if="tienda.logo" class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-full bg-white/10 shadow-2xl ring-2 ring-white/20">
                            <img :src="`/storage/${tienda.logo}`" :alt="tienda.nombre" class="h-full w-full object-cover" />
                        </div>
                        <div v-else class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full bg-white/10 text-3xl shadow-2xl ring-2 ring-white/20">
                            {{ tienda.categoria?.icono || '🏪' }}
                        </div>
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span v-if="tienda.categoria" class="rounded-full bg-primary-500/20 px-3 py-1 text-xs font-bold text-primary-300">
                                    {{ tienda.categoria.icono }} {{ tienda.categoria.nombre }}
                                </span>
                                <span class="flex items-center gap-1 rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-bold text-yellow-300">
                                    <svg class="h-3.5 w-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ Number(tienda.valoracion).toFixed(1) }}
                                    <span class="font-normal text-yellow-400/70">({{ tienda.total_resenas }})</span>
                                </span>
                            </div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ tienda.nombre }}</h1>
                            <p v-if="tienda.descripcion" class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-300">{{ tienda.descripcion }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats ribbon -->
                <div class="mt-10 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/10 sm:grid-cols-4">
                    <div v-for="stat in [
                        { valor: tienda.productos.length, label: 'Productos' },
                        { valor: productosDestacados,     label: 'Destacados' },
                        { valor: tienda.total_resenas,    label: 'Reseñas' },
                        { valor: Number(tienda.valoracion).toFixed(1), label: 'Valoración', estrella: true },
                    ]" :key="stat.label" class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="flex items-center gap-1 text-2xl font-extrabold text-white">
                            {{ stat.valor }}
                            <svg v-if="stat.estrella" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">{{ stat.label }}</span>
                    </div>
                </div>

                <!-- Contact info -->
                <div class="mt-6 flex flex-wrap items-center gap-4 text-sm text-gray-400">
                    <span v-if="tienda.direccion" class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ tienda.direccion }}
                    </span>
                    <span v-if="tienda.telefono" class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ tienda.telefono }}
                    </span>
                    <span v-if="tienda.email" class="flex items-center gap-1.5">
                        <svg class="h-4 w-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ tienda.email }}
                    </span>
                </div>
            </div>

            <svg viewBox="0 0 1200 60" preserveAspectRatio="none"
                :class="['block h-12 w-full', isDark ? 'text-gray-950' : 'text-gray-50']"
                fill="currentColor">
                <path d="M0 60C200 20 400 0 600 10C800 20 1000 50 1200 60V60H0Z" />
            </svg>
        </div>

        <!-- ── Barra de categorías sticky ────────────────────────────────────── -->
        <div :class="['sticky top-0 z-40 border-b backdrop-blur-sm', isDark ? 'border-gray-700 bg-gray-900/95' : 'border-gray-200 bg-white/95']">
            <div class="mx-auto flex max-w-7xl items-center gap-3 overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
                <span :class="['hidden text-xs font-medium sm:block', isDark ? 'text-gray-500' : 'text-gray-400']">Filtrar:</span>
                <button
                    v-for="tab in tabs"
                    :key="tab.nombre"
                    @click="tabActiva = tab.nombre"
                    :class="[
                        'whitespace-nowrap rounded-full px-4 py-1.5 text-xs font-semibold transition-all',
                        tabActiva === tab.nombre
                            ? 'bg-primary-500 text-white shadow-sm'
                            : isDark ? 'bg-gray-800 text-gray-300 hover:bg-gray-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                    ]"
                >
                    {{ tab.nombre }} ({{ tab.count }})
                </button>
                <span :class="['ml-auto hidden text-xs sm:block', isDark ? 'text-gray-500' : 'text-gray-400']">
                    {{ productosFiltrados.length }} producto{{ productosFiltrados.length !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>

        <!-- ── Contenido principal ────────────────────────────────────────────── -->
        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Sin productos -->
            <div v-if="productosFiltrados.length === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-4 text-6xl">{{ tienda.categoria?.icono || '📦' }}</div>
                <h2 :class="['text-xl font-semibold', isDark ? 'text-gray-300' : 'text-gray-700']">No hay productos en esta categoría</h2>
                <p :class="['mt-2 text-sm', isDark ? 'text-gray-500' : 'text-gray-400']">Prueba con otra categoría o vuelve más tarde.</p>
            </div>

            <!-- Grid de productos -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="producto in productosFiltrados"
                    :key="producto.id"
                    :class="[
                        'group relative flex flex-col overflow-hidden rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl',
                        isDark ? 'bg-gray-800' : 'bg-white',
                        producto.stock > 0 ? 'cursor-pointer' : 'opacity-75',
                    ]"
                    @click="abrirModal(producto)"
                >
                    <!-- Imagen -->
                    <div class="relative aspect-square overflow-hidden">
                        <img
                            :src="producto.imagen || '/images/logo.png'"
                            :alt="producto.nombre"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />

                        <div class="absolute left-3 top-3 rounded-full bg-primary-500 px-2.5 py-1 text-xs font-bold text-white shadow">Km 0</div>

                        <div v-if="producto.destacado" class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-yellow-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Destacado
                        </div>

                        <div v-if="producto.stock > 0 && producto.stock <= producto.stock_minimo" class="absolute bottom-3 left-3 rounded-full bg-orange-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                            ¡Últimas unidades!
                        </div>

                        <div v-if="producto.stock === 0" class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                            <span class="rounded-full bg-red-600 px-4 py-2 text-sm font-bold text-white">Agotado</span>
                        </div>

                        <!-- Hover "Ver detalle" -->
                        <div v-if="producto.stock > 0" class="absolute inset-0 flex items-center justify-center bg-black/25 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                            <span class="rounded-full bg-white/90 px-4 py-2 text-sm font-bold text-gray-900 shadow-lg backdrop-blur-sm">
                                Ver detalle
                            </span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col p-5">
                        <div :class="['mb-1 text-xs font-medium', isDark ? 'text-gray-500' : 'text-gray-400']">{{ producto.categoria.nombre }}</div>
                        <h3 :class="['text-base font-bold transition-colors group-hover:text-primary-500', isDark ? 'text-gray-100' : 'text-gray-900']">
                            {{ producto.nombre }}
                        </h3>
                        <p v-if="producto.descripcion" :class="['mt-1.5 flex-1 text-sm line-clamp-2', isDark ? 'text-gray-400' : 'text-gray-500']">
                            {{ producto.descripcion }}
                        </p>

                        <div :class="['mt-4 flex items-end justify-between border-t pt-3', isDark ? 'border-gray-700' : 'border-gray-100']">
                            <div>
                                <div v-if="producto.precio_oferta" :class="['text-xs line-through', isDark ? 'text-gray-500' : 'text-gray-400']">
                                    {{ Number(producto.precio).toFixed(2) }}€
                                </div>
                                <span class="text-xl font-extrabold text-primary-500">{{ Number(producto.precio_oferta || producto.precio).toFixed(2) }}€</span>
                                <span :class="['text-xs', isDark ? 'text-gray-500' : 'text-gray-400']">/{{ producto.unidad }}</span>
                            </div>

                            <!-- Botón añadir rápido con feedback -->
                            <button
                                @click.stop="agregarAlCarrito(producto)"
                                :disabled="producto.stock === 0"
                                :class="[
                                    'flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200',
                                    producto.stock === 0
                                        ? 'cursor-not-allowed bg-gray-200 text-gray-400'
                                        : añadidos.has(producto.id)
                                            ? 'scale-95 bg-green-500 text-white shadow-md'
                                            : 'bg-primary-500 text-white shadow-sm hover:bg-primary-600 hover:shadow-md',
                                ]"
                            >
                                <Transition enter-active-class="transition duration-150" enter-from-class="scale-0 opacity-0" enter-to-class="scale-100 opacity-100"
                                    leave-active-class="transition duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0" mode="out-in">
                                    <svg v-if="añadidos.has(producto.id)" key="check" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg v-else key="cart" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                                    </svg>
                                </Transition>
                                {{ producto.stock === 0 ? 'Agotado' : añadidos.has(producto.id) ? '¡Listo!' : 'Añadir' }}
                            </button>
                        </div>

                        <!-- Barra stock bajo -->
                        <div v-if="producto.stock > 0 && producto.stock <= producto.stock_minimo" class="mt-2">
                            <div :class="['h-1.5 w-full overflow-hidden rounded-full', isDark ? 'bg-gray-700' : 'bg-gray-100']">
                                <div class="h-full rounded-full bg-orange-400" :style="{ width: `${Math.min((producto.stock / producto.stock_minimo) * 100, 100)}%` }"></div>
                            </div>
                            <p class="mt-1 text-xs text-orange-500">Quedan {{ producto.stock }} uds.</p>
                        </div>
                    </div>

                    <!-- Línea inferior hover -->
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 origin-left scale-x-0 rounded-b-2xl bg-primary-500 transition-transform duration-300 group-hover:scale-x-100"></div>
                </div>
            </div>
        </main>

        <footer :class="['mt-16 border-t py-8 text-center text-sm', isDark ? 'border-gray-800 bg-gray-900 text-gray-500' : 'border-gray-200 bg-white text-gray-400']">
            <p>&copy; {{ new Date().getFullYear() }} Rustikan · Productos locales de Lanzarote</p>
        </footer>
    </div>

    <!-- ── Modal de detalle de producto ─────────────────────────────────────── -->
    <Transition enter-active-class="transition duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
        leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div
            v-if="productoModal"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 px-4 pb-0 sm:items-center sm:pb-4"
            @click.self="cerrarModal"
        >
            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                enter-to-class="translate-y-0 sm:scale-100 sm:opacity-100"
                leave-active-class="transition duration-200"
                leave-from-class="translate-y-0 sm:scale-100 sm:opacity-100"
                leave-to-class="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                appear
            >
                <div :class="['w-full max-w-lg overflow-hidden rounded-t-3xl shadow-2xl sm:rounded-3xl', isDark ? 'bg-gray-800' : 'bg-white']">

                    <!-- Imagen -->
                    <div class="relative aspect-video overflow-hidden">
                        <img :src="productoModal.imagen || '/images/logo.png'" :alt="productoModal.nombre" class="h-full w-full object-cover" />

                        <!-- Badges -->
                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-primary-500 px-3 py-1 text-xs font-bold text-white shadow">Km 0</span>
                            <span v-if="productoModal.destacado" class="rounded-full bg-yellow-500 px-3 py-1 text-xs font-bold text-white shadow">⭐ Destacado</span>
                            <span v-if="productoModal.precio_oferta" class="rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white shadow">
                                -{{ Math.round((1 - productoModal.precio_oferta / productoModal.precio) * 100) }}%
                            </span>
                        </div>

                        <!-- Cerrar -->
                        <button
                            @click="cerrarModal"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm transition-colors hover:bg-black/60"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Contenido -->
                    <div class="px-6 py-5">
                        <p :class="['mb-1 text-xs font-medium', isDark ? 'text-gray-400' : 'text-gray-400']">{{ productoModal.categoria.nombre }}</p>
                        <h2 :class="['text-xl font-extrabold', isDark ? 'text-white' : 'text-gray-900']">{{ productoModal.nombre }}</h2>
                        <p v-if="productoModal.descripcion" :class="['mt-2 text-sm leading-relaxed', isDark ? 'text-gray-300' : 'text-gray-600']">
                            {{ productoModal.descripcion }}
                        </p>

                        <!-- Precio -->
                        <div class="mt-4 flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-primary-500">
                                {{ Number(productoModal.precio_oferta || productoModal.precio).toFixed(2) }}€
                            </span>
                            <span v-if="productoModal.precio_oferta" :class="['text-lg line-through', isDark ? 'text-gray-500' : 'text-gray-400']">
                                {{ Number(productoModal.precio).toFixed(2) }}€
                            </span>
                            <span :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">/ {{ productoModal.unidad }}</span>
                        </div>

                        <!-- Stock -->
                        <p v-if="productoModal.stock > 0 && productoModal.stock <= productoModal.stock_minimo"
                            class="mt-2 text-xs font-medium text-orange-500">
                            ⚠️ Solo quedan {{ productoModal.stock }} unidades
                        </p>
                        <p v-else class="mt-2 text-xs font-medium text-green-500">
                            ✓ En stock ({{ productoModal.stock }} disponibles)
                        </p>

                        <!-- Selector de cantidad -->
                        <div class="mt-5 flex items-center gap-4">
                            <span :class="['text-sm font-semibold', isDark ? 'text-gray-300' : 'text-gray-700']">Cantidad:</span>
                            <div :class="['flex items-center gap-3 rounded-xl border px-3 py-2', isDark ? 'border-gray-600' : 'border-gray-200']">
                                <button
                                    @click="decrementar"
                                    :disabled="cantidadModal <= 1"
                                    :class="['flex h-7 w-7 items-center justify-center rounded-lg transition-colors',
                                        cantidadModal <= 1 ? 'cursor-not-allowed text-gray-300' : isDark ? 'text-gray-300 hover:bg-gray-700' : 'text-gray-500 hover:bg-gray-100']"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span :class="['w-8 text-center text-lg font-bold', isDark ? 'text-white' : 'text-gray-900']">{{ cantidadModal }}</span>
                                <button
                                    @click="incrementar"
                                    :disabled="cantidadModal >= productoModal.stock"
                                    :class="['flex h-7 w-7 items-center justify-center rounded-lg transition-colors',
                                        cantidadModal >= productoModal.stock ? 'cursor-not-allowed bg-gray-200 text-gray-400' : 'bg-primary-500 text-white hover:bg-primary-600']"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                            <!-- Total selección -->
                            <span class="ml-auto text-xl font-extrabold text-primary-500">
                                {{ (Number(productoModal.precio_oferta || productoModal.precio) * cantidadModal).toFixed(2) }}€
                            </span>
                        </div>
                    </div>

                    <!-- Footer modal -->
                    <div :class="['border-t px-6 py-4', isDark ? 'border-gray-700' : 'border-gray-100']">
                        <button
                            @click="añadirDesdeModal"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-4 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-600 hover:shadow-md active:scale-95"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                            </svg>
                            Añadir {{ cantidadModal > 1 ? cantidadModal + ' al' : 'al' }} carrito ·
                            {{ (Number(productoModal.precio_oferta || productoModal.precio) * cantidadModal).toFixed(2) }}€
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
