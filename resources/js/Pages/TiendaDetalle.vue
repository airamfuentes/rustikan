<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useCarrito } from '@/Composables/useCarrito';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    tienda: { type: Object, required: true },
});

const tabActiva = ref('Todos');
const scrolled  = ref(false);
let scrollTimeout = null;

const handleScroll = () => {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout = null;
    }, 100);
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    if (scrollTimeout) clearTimeout(scrollTimeout);
});

const tabs = computed(() => {
    const categorias = {};
    props.tienda.productos.forEach(p => {
        const cat = p.categoria.nombre;
        categorias[cat] = (categorias[cat] || 0) + 1;
    });
    const result = [{ nombre: 'Todos', count: props.tienda.productos.length }];
    Object.entries(categorias).forEach(([nombre, count]) => result.push({ nombre, count }));
    return result;
});

const productosFiltrados = computed(() => {
    if (tabActiva.value === 'Todos') return props.tienda.productos;
    return props.tienda.productos.filter(p => p.categoria.nombre === tabActiva.value);
});

const productosDestacados = computed(() =>
    props.tienda.productos.filter(p => p.destacado).length
);

const { agregarItem } = useCarrito();
const agregarAlCarrito = (producto) => agregarItem(producto, props.tienda);
</script>

<template>
    <Head :title="`${tienda.nombre} – Rustikan`" />

    <div class="min-h-screen bg-gray-50">

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

        <!-- Hero de la tienda -->
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
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="text-2xl font-extrabold text-white">{{ tienda.productos.length }}</span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Productos</span>
                    </div>
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="text-2xl font-extrabold text-white">{{ productosDestacados }}</span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Destacados</span>
                    </div>
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="text-2xl font-extrabold text-white">{{ tienda.total_resenas }}</span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Reseñas</span>
                    </div>
                    <div class="flex flex-col items-center bg-white/5 px-6 py-5">
                        <span class="flex items-center gap-1 text-2xl font-extrabold text-white">
                            {{ Number(tienda.valoracion).toFixed(1) }}
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </span>
                        <span class="mt-1 text-xs font-medium uppercase tracking-widest text-gray-400">Valoración</span>
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

            <svg viewBox="0 0 1200 60" preserveAspectRatio="none" class="block h-12 w-full text-gray-50" fill="currentColor">
                <path d="M0 60C200 20 400 0 600 10C800 20 1000 50 1200 60V60H0Z" />
            </svg>
        </div>

        <!-- Barra de categorías sticky -->
        <div class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center gap-3 overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
                <span class="hidden text-xs font-medium text-gray-400 sm:block">Filtrar:</span>
                <button
                    v-for="tab in tabs"
                    :key="tab.nombre"
                    @click="tabActiva = tab.nombre"
                    :class="[
                        'whitespace-nowrap rounded-full px-4 py-1.5 text-xs font-semibold transition-all',
                        tabActiva === tab.nombre
                            ? 'bg-primary-500 text-white shadow-sm'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
                    ]"
                >
                    {{ tab.nombre }} ({{ tab.count }})
                </button>
                <span class="ml-auto hidden text-xs text-gray-400 sm:block">
                    {{ productosFiltrados.length }} producto{{ productosFiltrados.length !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>

        <!-- Contenido principal -->
        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Sin productos -->
            <div v-if="productosFiltrados.length === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-4 text-6xl">{{ tienda.categoria?.icono || '📦' }}</div>
                <h2 class="text-xl font-semibold text-gray-700">No hay productos en esta categoría</h2>
                <p class="mt-2 text-sm text-gray-400">Prueba con otra categoría o vuelve más tarde.</p>
            </div>

            <!-- Grid de productos -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="producto in productosFiltrados"
                    :key="producto.id"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    <!-- Imagen -->
                    <div class="relative aspect-square overflow-hidden">
                        <img
                            :src="producto.imagen || '/images/logo.png'"
                            :alt="producto.nombre"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />

                        <div class="absolute left-3 top-3 rounded-full bg-primary-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                            Km 0
                        </div>

                        <div v-if="producto.destacado" class="absolute right-3 top-3 flex items-center gap-1 rounded-full bg-yellow-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            Destacado
                        </div>

                        <div v-if="producto.stock <= producto.stock_minimo && producto.stock > 0" class="absolute bottom-3 left-3 rounded-full bg-orange-500 px-2.5 py-1 text-xs font-bold text-white shadow">
                            ¡Últimas unidades!
                        </div>

                        <div v-if="producto.stock === 0" class="absolute inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                            <span class="rounded-full bg-red-600 px-4 py-2 text-sm font-bold text-white">Agotado</span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                    </div>

                    <!-- Info -->
                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-1 text-xs font-medium text-gray-400">{{ producto.categoria.nombre }}</div>
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-primary-600 transition-colors">{{ producto.nombre }}</h3>
                        <p v-if="producto.descripcion" class="mt-1.5 flex-1 text-sm text-gray-500 line-clamp-2">{{ producto.descripcion }}</p>

                        <div class="mt-4 flex items-end justify-between border-t border-gray-100 pt-3">
                            <div>
                                <div v-if="producto.precio_oferta" class="text-xs text-gray-400 line-through">{{ Number(producto.precio).toFixed(2) }}€</div>
                                <span class="text-xl font-extrabold text-primary-600">{{ Number(producto.precio_oferta || producto.precio).toFixed(2) }}€</span>
                                <span class="text-xs text-gray-400">/{{ producto.unidad }}</span>
                            </div>
                            <button
                                @click="agregarAlCarrito(producto)"
                                :disabled="producto.stock === 0"
                                :class="[
                                    'flex items-center gap-1.5 rounded-xl px-4 py-2 text-sm font-semibold transition-all',
                                    producto.stock === 0
                                        ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        : 'bg-primary-500 text-white hover:bg-primary-600 shadow-sm hover:shadow-md',
                                ]"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                                </svg>
                                {{ producto.stock === 0 ? 'Agotado' : 'Añadir' }}
                            </button>
                        </div>

                        <div v-if="producto.stock > 0 && producto.stock <= producto.stock_minimo" class="mt-2">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-orange-400" :style="{ width: `${Math.min((producto.stock / producto.stock_minimo) * 100, 100)}%` }"></div>
                            </div>
                            <p class="mt-1 text-xs text-orange-500">Quedan {{ producto.stock }} uds.</p>
                        </div>
                    </div>

                    <div class="absolute bottom-0 left-0 right-0 h-0.5 origin-left scale-x-0 rounded-b-2xl bg-primary-500 transition-transform duration-300 group-hover:scale-x-100"></div>
                </div>
            </div>
        </main>

        <footer class="mt-16 border-t border-gray-200 bg-white py-8 text-center text-sm text-gray-400">
            <p>&copy; {{ new Date().getFullYear() }} Rustikan · Productos locales de Lanzarote</p>
        </footer>
    </div>
</template>
