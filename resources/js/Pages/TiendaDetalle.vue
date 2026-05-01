<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import { useCarrito } from '@/Composables/useCarrito';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Store, Package, Star, AlertTriangle, Check } from 'lucide-vue-next';
import NavbarPublico from '@/Components/NavbarPublico.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isDark, toggleDark } = useDarkMode();

const props = defineProps({
    tienda:       { type: Object,  required: true },
    resenas:      { type: Array,   default: () => [] },
    distribucion: { type: Object,  default: () => ({}) },
    canReview:    { type: Boolean, default: false },
    userReview:   { type: Object,  default: null },
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

const busquedaProducto = ref('');

const productosFiltrados = computed(() => {
    let lista = tabActiva.value === 'Todos'
        ? props.tienda.productos
        : props.tienda.productos.filter(p => p.categoria.nombre === tabActiva.value);
    const q = busquedaProducto.value.trim().toLowerCase();
    if (q) {
        lista = lista.filter(p =>
            p.nombre.toLowerCase().includes(q) ||
            (p.descripcion && p.descripcion.toLowerCase().includes(q))
        );
    }
    return lista;
});

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

// ─── Reseñas ──────────────────────────────────────────────────────────────────
const showReviewForm = ref(false);
const hoverStar      = ref(0);
const editMode       = ref(!!props.userReview);

const resenaForm = useForm({
    puntuacion: props.userReview?.puntuacion ?? 0,
    titulo:     props.userReview?.titulo     ?? '',
    comentario: props.userReview?.comentario ?? '',
});

const setRating = (n) => { resenaForm.puntuacion = n; };

const submitResena = () => {
    resenaForm.post(route('resenas.store', props.tienda.id), {
        preserveScroll: true,
        onSuccess: () => {
            showReviewForm.value = false;
            editMode.value = false;
        },
    });
};

const deleteResena = (id) => {
    if (!confirm('¿Eliminar tu reseña?')) return;
    useForm({}).delete(route('resenas.destroy', id), { preserveScroll: true });
};

// ─── Compartir tienda ─────────────────────────────────────────────────────────
const copiado = ref(false);

const copiarEnlace = async () => {
    await navigator.clipboard.writeText(window.location.href);
    copiado.value = true;
    setTimeout(() => { copiado.value = false; }, 2000);
};

const formatPhone = (tel) => {
    if (!tel) return null;
    const digits = tel.replace(/[\s\-\+]/g, '');
    if (/^[67]/.test(digits)) return `34${digits}`;
    return digits;
};

const whatsappUrl = computed(() => {
    const phone = formatPhone(props.tienda.telefono);
    if (!phone) return null;
    const text = encodeURIComponent(`¡Mira esta tienda en Rustikan! ${props.tienda.nombre} - ${window.location.href}`);
    return `https://wa.me/${phone}?text=${text}`;
});
const totalResenas = computed(() => props.tienda.total_resenas ?? props.resenas.length);

const pct = (n) => totalResenas.value > 0
    ? Math.round(((props.distribucion[n] ?? 0) / totalResenas.value) * 100)
    : 0;

// Colores de avatar por inicial
const avatarColors = ['bg-primary-500','bg-tierra-600','bg-blue-500','bg-purple-500','bg-green-500','bg-pink-500','bg-yellow-500','bg-red-500'];
const avatarColor = (inicial) => avatarColors[inicial.charCodeAt(0) % avatarColors.length];
</script>

<template>
    <Head :title="`${tienda.nombre} – Rustikan`" />

    <div :class="['min-h-screen flex flex-col transition-colors duration-300', isDark ? 'dark bg-gray-950' : 'bg-gray-50']">

        <NavbarPublico />
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
                        <div v-else class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full bg-white/10 shadow-2xl ring-2 ring-white/20">
                            <Store class="h-9 w-9 text-white/70" />
                        </div>
                        <div>
                            <div class="mb-2 flex flex-wrap items-center gap-2">
                                <span v-if="tienda.categoria" class="rounded-full bg-primary-500/20 px-3 py-1 text-xs font-bold text-primary-300">
                                    {{ tienda.categoria.nombre }}
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

                <!-- Contact info + Share -->
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
                    <a
                        v-if="whatsappUrl"
                        :href="whatsappUrl"
                        target="_blank"
                        rel="noopener"
                        title="Contactar por WhatsApp"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600/80 text-white transition-all hover:bg-green-600 hover:scale-105"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.549 4.122 1.512 5.863L.057 23.998l6.305-1.654A11.947 11.947 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.89 0-3.659-.518-5.177-1.42l-.371-.22-3.742.981.999-3.651-.242-.376A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                        </svg>
                    </a>
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
                <!-- Buscador de productos + contador -->
                <div class="ml-auto flex shrink-0 items-center gap-3">
                    <span :class="['hidden text-xs sm:block', isDark ? 'text-gray-500' : 'text-gray-400']">
                        {{ productosFiltrados.length }} producto{{ productosFiltrados.length !== 1 ? 's' : '' }}
                    </span>
                    <div class="relative flex shrink-0 items-center">
                        <div class="pointer-events-none absolute left-0 flex items-center pl-3">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="busquedaProducto"
                            type="search"
                            placeholder="Buscar productos..."
                            :class="['w-44 rounded-full border py-2 pl-9 pr-3 text-xs transition-all focus:w-60 focus:outline-none focus:ring-1 focus:ring-primary-400',
                                isDark ? 'bg-gray-800 border-gray-600 text-gray-100 placeholder-gray-500 focus:border-primary-400' : 'bg-gray-100 border-transparent text-gray-800 placeholder-gray-400 focus:border-gray-300 focus:bg-white']"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Contenido principal ────────────────────────────────────────────── -->
        <main class="mx-auto max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8">

            <!-- Sin productos -->
            <div v-if="productosFiltrados.length === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-4">
                    <Package class="h-16 w-16 text-gray-300" />
                </div>
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
                                <div v-if="producto.precio_oferta && producto.oferta_activa" :class="['text-xs line-through', isDark ? 'text-gray-500' : 'text-gray-400']">
                                    {{ Number(producto.precio).toFixed(2) }}€
                                </div>
                                <span class="text-xl font-extrabold text-primary-500">{{ Number((producto.oferta_activa && producto.precio_oferta) ? producto.precio_oferta : producto.precio).toFixed(2) }}€</span>
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

        <!-- ══════════════════ SECCIÓN RESEÑAS ══════════════════ -->
        <section :class="['py-16 px-4 sm:px-6 lg:px-8', isDark ? 'bg-gray-900' : 'bg-gradient-to-b from-gray-50 to-white']">
            <div class="mx-auto max-w-7xl">

                <!-- Cabecera -->
                <div class="mb-10 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h2 :class="['text-2xl font-extrabold tracking-tight', isDark ? 'text-white' : 'text-gray-900']">
                            Lo que dicen nuestros clientes
                        </h2>
                        <p :class="['mt-1 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">
                            {{ totalResenas }} opinión{{ totalResenas !== 1 ? 'es' : '' }} verificada{{ totalResenas !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    <!-- Botón "Escribir reseña" -->
                    <div v-if="canReview && !showReviewForm">
                        <button
                            @click="showReviewForm = true"
                            class="group flex items-center gap-2 rounded-2xl bg-gradient-to-r from-primary-500 to-primary-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition-all hover:-translate-y-0.5 hover:shadow-xl"
                        >
                            <svg class="h-5 w-5 transition-transform group-hover:rotate-12" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Escribir una reseña
                        </button>
                    </div>
                </div>

                <!-- Panel resumen + cards -->
                <div v-if="totalResenas > 0 || canReview || userReview" class="grid gap-8 lg:grid-cols-3">

                    <!-- ── Panel resumen de puntuación ──────────────────────── -->
                    <div :class="['rounded-3xl p-7 shadow-sm', isDark ? 'bg-gray-800' : 'bg-white border border-gray-100']">

                        <!-- Nota grande -->
                        <div class="mb-6 flex items-center gap-4">
                            <span :class="['text-5xl sm:text-6xl lg:text-7xl font-black leading-none', isDark ? 'text-white' : 'text-gray-900']">
                                {{ Number(tienda.valoracion).toFixed(1) }}
                            </span>
                            <div>
                                <div class="flex gap-0.5">
                                    <svg v-for="i in 5" :key="i"
                                         :class="['h-6 w-6', i <= Math.round(tienda.valoracion) ? 'text-yellow-400' : isDark ? 'text-gray-600' : 'text-gray-200']"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                <p :class="['mt-1 text-xs', isDark ? 'text-gray-400' : 'text-gray-500']">
                                    {{ tienda.total_resenas }} opiniones
                                </p>
                            </div>
                        </div>

                        <!-- Barras por estrella -->
                        <div class="space-y-2.5">
                            <div v-for="n in [5, 4, 3, 2, 1]" :key="n" class="flex items-center gap-2.5">
                                <span :class="['w-2 text-right text-xs font-bold', isDark ? 'text-gray-400' : 'text-gray-600']">{{ n }}</span>
                                <svg class="h-3.5 w-3.5 shrink-0 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <div :class="['flex-1 overflow-hidden rounded-full h-2.5', isDark ? 'bg-gray-700' : 'bg-gray-100']">
                                    <div
                                        :style="`width: ${pct(n)}%; transition: width 0.6s ease`"
                                        :class="['h-full rounded-full', n >= 4 ? 'bg-green-400' : n === 3 ? 'bg-yellow-400' : 'bg-red-400']"
                                    ></div>
                                </div>
                                <span :class="['w-7 text-xs', isDark ? 'text-gray-500' : 'text-gray-400']">{{ distribucion[n] ?? 0 }}</span>
                            </div>
                        </div>

                        <!-- Sello verificado -->
                        <div :class="['mt-6 flex items-center gap-2 rounded-xl p-3', isDark ? 'bg-green-900/30' : 'bg-green-50']">
                            <svg class="h-4 w-4 shrink-0 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs font-medium text-green-600 dark:text-green-400">
                                Solo clientes con compra verificada
                            </p>
                        </div>
                    </div>

                    <!-- ── Lista de reseñas ──────────────────────────────────── -->
                    <div class="lg:col-span-2 space-y-4">

                        <!-- Tu reseña (ya publicada) -->
                        <div v-if="userReview && !editMode"
                             :class="['relative rounded-2xl p-5 ring-2 ring-primary-400 shadow-sm', isDark ? 'bg-gray-800' : 'bg-primary-50']">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="rounded-full bg-primary-500 px-3 py-0.5 text-xs font-bold text-white">Tu reseña</span>
                                <div class="flex gap-2">
                                    <button @click="editMode = true; showReviewForm = true"
                                            :class="['rounded-lg px-3 py-1 text-xs font-medium transition-colors', isDark ? 'text-gray-400 hover:bg-gray-700' : 'text-gray-500 hover:bg-white']">
                                        Editar
                                    </button>
                                    <button @click="deleteResena(userReview.id)"
                                            class="rounded-lg px-3 py-1 text-xs font-medium text-red-500 transition-colors hover:bg-red-50 dark:hover:bg-red-900/30">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                            <div class="flex gap-0.5 mb-1">
                                <svg v-for="i in 5" :key="i"
                                     :class="['h-4 w-4', i <= userReview.puntuacion ? 'text-yellow-400' : 'text-gray-300']"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p v-if="userReview.titulo" :class="['font-semibold text-sm', isDark ? 'text-white' : 'text-gray-900']">{{ userReview.titulo }}</p>
                            <p :class="['text-sm mt-1', isDark ? 'text-gray-300' : 'text-gray-700']">{{ userReview.comentario }}</p>
                        </div>

                        <!-- Formulario de reseña -->
                        <Transition
                            enter-active-class="transition duration-300"
                            enter-from-class="opacity-0 -translate-y-4"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-200"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-4"
                        >
                        <form v-if="showReviewForm"
                              @submit.prevent="submitResena"
                              :class="['rounded-3xl p-6 shadow-sm', isDark ? 'bg-gray-800' : 'bg-white border border-gray-100']">

                            <div class="mb-4 flex items-center justify-between">
                                <h3 :class="['text-base font-bold', isDark ? 'text-white' : 'text-gray-900']">
                                    {{ editMode ? 'Editar tu reseña' : 'Escribe tu opinión' }}
                                </h3>
                                <button type="button" @click="showReviewForm = false; editMode = false"
                                        :class="['rounded-full p-1 transition-colors', isDark ? 'hover:bg-gray-700 text-gray-400' : 'hover:bg-gray-100 text-gray-500']">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Selector de estrellas -->
                            <div class="mb-5">
                                <p :class="['mb-2 text-sm font-medium', isDark ? 'text-gray-300' : 'text-gray-700']">Puntuación <span class="text-red-500">*</span></p>
                                <div class="flex gap-1.5">
                                    <button
                                        v-for="n in 5" :key="n"
                                        type="button"
                                        @click="setRating(n)"
                                        @mouseenter="hoverStar = n"
                                        @mouseleave="hoverStar = 0"
                                        :class="['transition-transform hover:scale-110 focus:outline-none']"
                                    >
                                        <svg
                                            :class="['h-9 w-9 transition-colors duration-150',
                                                n <= (hoverStar || resenaForm.puntuacion) ? 'text-yellow-400 drop-shadow-sm' : isDark ? 'text-gray-600' : 'text-gray-200']"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                    <span :class="['ml-2 self-center text-sm font-medium', isDark ? 'text-gray-400' : 'text-gray-500']">
                                        {{ ['', 'Muy malo', 'Regular', 'Bueno', 'Muy bueno', '¡Excelente!'][resenaForm.puntuacion] }}
                                    </span>
                                </div>
                                <p v-if="resenaForm.errors.puntuacion" class="mt-1 text-xs text-red-500">{{ resenaForm.errors.puntuacion }}</p>
                            </div>

                            <!-- Título -->
                            <div class="mb-4">
                                <label :class="['block mb-1 text-sm font-medium', isDark ? 'text-gray-300' : 'text-gray-700']">
                                    Título <span class="text-gray-400 font-normal">(opcional)</span>
                                </label>
                                <input v-model="resenaForm.titulo" type="text" maxlength="120"
                                       :placeholder="`Resumen en pocas palabras...`"
                                       :class="['w-full rounded-xl px-4 py-2.5 text-sm outline-none transition border focus:ring-2',
                                           isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500/20' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-primary-500/20']"/>
                            </div>

                            <!-- Comentario -->
                            <div class="mb-5">
                                <label :class="['block mb-1 text-sm font-medium', isDark ? 'text-gray-300' : 'text-gray-700']">
                                    Tu opinión <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="resenaForm.comentario" rows="4" maxlength="1000" required minlength="10"
                                          placeholder="¿Qué te pareció la tienda? ¿Recomendarías sus productos?"
                                          :class="['w-full rounded-xl px-4 py-2.5 text-sm outline-none transition border focus:ring-2 resize-none',
                                              isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500/20' : 'bg-gray-50 border-gray-200 text-gray-900 focus:border-primary-500 focus:ring-primary-500/20']">
                                </textarea>
                                <div class="mt-1 flex justify-between">
                                    <p v-if="resenaForm.errors.comentario" class="text-xs text-red-500">{{ resenaForm.errors.comentario }}</p>
                                    <span :class="['ml-auto text-xs', isDark ? 'text-gray-500' : 'text-gray-400']">{{ resenaForm.comentario.length }}/1000</span>
                                </div>
                            </div>

                            <!-- Botón enviar -->
                            <div class="flex items-center justify-end gap-3">
                                <button type="button" @click="showReviewForm = false; editMode = false"
                                        :class="['rounded-xl px-4 py-2 text-sm font-medium transition-colors', isDark ? 'text-gray-400 hover:bg-gray-700' : 'text-gray-500 hover:bg-gray-100']">
                                    Cancelar
                                </button>
                                <button type="submit" :disabled="resenaForm.processing || resenaForm.puntuacion === 0"
                                        class="flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-600 hover:shadow-md disabled:opacity-50">
                                    <svg v-if="resenaForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ editMode ? 'Actualizar reseña' : 'Publicar reseña' }}
                                </button>
                            </div>
                        </form>
                        </Transition>

                        <!-- Estado vacío (sin reseñas) -->
                        <div v-if="totalResenas === 0 && !canReview && !userReview"
                             :class="['rounded-3xl p-10 text-center', isDark ? 'bg-gray-800' : 'bg-white border border-gray-100 shadow-sm']">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
                                <svg class="h-8 w-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p :class="['text-base font-semibold', isDark ? 'text-white' : 'text-gray-900']">Aún no hay reseñas</p>
                            <p :class="['mt-1 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Sé el primero en opinar sobre esta tienda.</p>
                        </div>

                        <!-- Cards de reseñas -->
                        <div v-if="totalResenas > 0" class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="r in resenas" :key="r.id"
                                :class="['rounded-2xl p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md',
                                    isDark ? 'bg-gray-800' : 'bg-white border border-gray-100']"
                            >
                                <div class="flex gap-4">
                                    <!-- ── Contenido de la reseña (izquierda) ── -->
                                    <div class="min-w-0 flex-1">
                                        <!-- Estrellas -->
                                        <div class="mb-2 flex gap-0.5">
                                            <svg v-for="i in 5" :key="i"
                                                 :class="['h-4 w-4', i <= r.puntuacion ? 'text-yellow-400' : isDark ? 'text-gray-600' : 'text-gray-200']"
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </div>

                                        <!-- Título -->
                                        <p v-if="r.titulo" :class="['mb-1 text-sm font-semibold', isDark ? 'text-white' : 'text-gray-900']">{{ r.titulo }}</p>

                                        <!-- Comentario -->
                                        <p :class="['text-sm leading-relaxed line-clamp-4', isDark ? 'text-gray-300' : 'text-gray-600']">{{ r.comentario }}</p>

                                        <!-- Badge + fecha -->
                                        <div class="mt-3 flex items-center gap-2">
                                            <span :class="['rounded-full px-2.5 py-0.5 text-xs font-bold',
                                                r.puntuacion >= 4 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' :
                                                r.puntuacion === 3 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400' :
                                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400']">
                                                {{ r.puntuacion }}/5
                                            </span>
                                            <span :class="['text-xs', isDark ? 'text-gray-600' : 'text-gray-400']">{{ r.created_at }}</span>
                                        </div>
                                    </div>

                                    <!-- ── Info del usuario (derecha) ── -->
                                    <div :class="['flex w-20 shrink-0 flex-col items-center gap-1.5 border-l pl-4 pt-0.5 text-center', isDark ? 'border-gray-700' : 'border-gray-100']">
                                        <div :class="['flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white shadow-sm', avatarColor(r.inicial)]">
                                            {{ r.inicial }}
                                        </div>
                                        <p :class="['w-full truncate text-xs font-semibold leading-tight', isDark ? 'text-white' : 'text-gray-900']">{{ r.nombre }}</p>
                                        <p v-if="r.email" :class="['w-full break-all text-[10px] font-mono leading-tight', isDark ? 'text-gray-500' : 'text-gray-400']">{{ r.email }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA para usuarios sin acceso a reseñar -->
                        <div v-if="!canReview && !userReview && user && user.role === 'user' && totalResenas >= 0"
                             :class="['mt-2 rounded-2xl p-4 flex items-center gap-3', isDark ? 'bg-gray-700/50' : 'bg-gray-50 border border-dashed border-gray-200']">
                            <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">
                                Para dejar una reseña necesitas haber recibido un pedido en esta tienda en los últimos 30 días.
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Panel vacío para usuarios no auth -->
                <div v-if="!user && totalResenas === 0"
                     :class="['rounded-3xl p-12 text-center', isDark ? 'bg-gray-800' : 'bg-white border border-gray-100 shadow-sm']">
                    <p :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Inicia sesión para ver y escribir reseñas.</p>
                </div>

            </div>
        </section>

        <footer :class="['border-t py-8 text-center text-sm', isDark ? 'border-gray-800 bg-gray-900 text-gray-500' : 'border-gray-200 bg-white text-gray-400']">
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
                            <span v-if="productoModal.destacado" class="inline-flex items-center gap-1 rounded-full bg-yellow-500 px-3 py-1 text-xs font-bold text-white shadow"><Star class="h-3 w-3 fill-current" /> Destacado</span>
                            <span v-if="productoModal.precio_oferta && productoModal.oferta_activa" class="rounded-full bg-red-500 px-3 py-1 text-xs font-bold text-white shadow">
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
                                {{ Number((productoModal.oferta_activa && productoModal.precio_oferta) ? productoModal.precio_oferta : productoModal.precio).toFixed(2) }}€
                            </span>
                            <span v-if="productoModal.precio_oferta && productoModal.oferta_activa" :class="['text-lg line-through', isDark ? 'text-gray-500' : 'text-gray-400']">
                                {{ Number(productoModal.precio).toFixed(2) }}€
                            </span>
                            <span :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">/ {{ productoModal.unidad }}</span>
                        </div>

                        <!-- Stock -->
                        <p v-if="productoModal.stock > 0 && productoModal.stock <= productoModal.stock_minimo"
                            class="mt-2 flex items-center gap-1 text-xs font-medium text-orange-500">
                            <AlertTriangle class="h-3.5 w-3.5" /> Solo quedan {{ productoModal.stock }} unidades
                        </p>
                        <p v-else class="mt-2 flex items-center gap-1 text-xs font-medium text-green-500">
                            <Check class="h-3.5 w-3.5" /> En stock ({{ productoModal.stock }} disponibles)
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
                                {{ (Number((productoModal.oferta_activa && productoModal.precio_oferta) ? productoModal.precio_oferta : productoModal.precio) * cantidadModal).toFixed(2) }}€
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
                            {{ (Number((productoModal.oferta_activa && productoModal.precio_oferta) ? productoModal.precio_oferta : productoModal.precio) * cantidadModal).toFixed(2) }}€
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
