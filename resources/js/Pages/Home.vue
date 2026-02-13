<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TiendaCard from '@/Components/TiendaCard.vue';
import AuthModal from '@/Components/AuthModal.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Props desde el backend
const props = defineProps({
    tiendas: {
        type: Array,
        default: () => []
    }
});

const busqueda = ref('');
const categoriaActiva = ref('Todos');
const scrolled = ref(false);
const showAuthModal = ref(false);
const showDropdown = ref(false);
const sugerenciasTiendas = ref([]);
const searchRef = ref(null);
const categoriesRef = ref(null);
const showCategoriesDropdown = ref(false);
let searchTimeout = null;
let scrollTimeout = null;

// Usar las tiendas del backend
const tiendasFiltradas = ref(props.tiendas);

// Detectar scroll con throttle
const handleScroll = () => {
    if (scrollTimeout) return;
    
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout = null;
    }, 100);
};

// Cerrar dropdown al hacer clic fuera
const handleClickOutside = (event) => {
    if (searchRef.value && !searchRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
    if (categoriesRef.value && !categoriesRef.value.contains(event.target)) {
        showCategoriesDropdown.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    if (scrollTimeout) {
        clearTimeout(scrollTimeout);
    }
});

// Categorías disponibles
const categorias = [
    { nombre: 'Todos', icono: '🏪' },
    { nombre: 'Frutas y Verduras', icono: '🍎' },
    { nombre: 'Carnes', icono: '🥩' },
    { nombre: 'Pescados y Mariscos', icono: '🐟' },
    { nombre: 'Panadería', icono: '🥖' },
    { nombre: 'Lácteos y Quesos', icono: '🧀' },
    { nombre: 'Vinos y Bebidas', icono: '🍷' },
    { nombre: 'Conservas y Mermeladas', icono: '🫙' },
    { nombre: 'Artesanía', icono: '🏺' },
];

const filtrarPorCategoria = (categoria) => {
    categoriaActiva.value = categoria;
    if (categoria === 'Todos') {
        tiendasFiltradas.value = props.tiendas;
    } else {
        tiendasFiltradas.value = props.tiendas.filter(tienda => 
            tienda.categoria.nombre === categoria
        );
    }
    busqueda.value = '';
    showDropdown.value = false;
    showCategoriesDropdown.value = false;
};

const buscarTiendas = () => {
    // Cancelar búsqueda anterior si existe
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    // Ejecutar búsqueda después de 300ms de inactividad
    searchTimeout = setTimeout(() => {
        if (busqueda.value.trim() === '') {
            showDropdown.value = false;
            sugerenciasTiendas.value = [];
        } else {
            sugerenciasTiendas.value = props.tiendas.filter(tienda => 
                tienda.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                tienda.direccion.toLowerCase().includes(busqueda.value.toLowerCase()) ||
                tienda.descripcion.toLowerCase().includes(busqueda.value.toLowerCase())
            );
            showDropdown.value = true;
        }
    }, 300);
};
</script>

<template>
    <Head title="Tiendas - Rustikan" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navbar Transformable -->
        <nav 
            :class="[
                'sticky top-0 z-50 bg-white transition-all duration-300',
                scrolled 
                    ? 'border-b border-gray-200 shadow-md' 
                    : 'mx-12 mt-4 rounded-2xl border border-gray-200 shadow-sm'
            ]"
        >
            <div :class="[
                'mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-300',
                scrolled ? 'max-w-full' : 'max-w-7xl'
            ]">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link href="/" class="flex items-center">
                            <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                        </Link>
                    </div>

                    <!-- Buscador central -->
                    <div class="hidden flex-1 max-w-2xl md:flex">
                        <div ref="searchRef" class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="busqueda"
                                @input="buscarTiendas"
                                type="text"
                                placeholder="Buscar tiendas o ubicaciones..."
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-12 pr-4 text-sm text-gray-900 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            />

                            <!-- Dropdown de Sugerencias -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div
                                    v-if="showDropdown && sugerenciasTiendas.length > 0"
                                    class="absolute left-0 right-0 top-full mt-2 max-h-96 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl will-change-transform"
                                >
                                    <Link
                                        v-for="tienda in sugerenciasTiendas"
                                        :key="tienda.id"
                                        :href="`/tienda/${tienda.slug}`"
                                        class="flex items-center gap-3 border-b border-gray-100 p-3 transition-colors hover:bg-gray-50 last:border-b-0"
                                    >
                                        <!-- Imagen -->
                                        <img
                                            :src="tienda.imagen_portada || tienda.logo || '/images/logo.png'"
                                            :alt="tienda.nombre"
                                            loading="lazy"
                                            class="h-16 w-16 flex-shrink-0 rounded-lg object-cover"
                                        />

                                        <!-- Información -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-gray-900 truncate">
                                                {{ tienda.nombre }}
                                            </h3>
                                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ tienda.direccion }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <!-- Estrellas -->
                                                <div class="flex items-center gap-0.5">
                                                    <svg v-for="i in 5" :key="i" class="h-3 w-3" :class="i <= tienda.valoracion ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700">{{ Number(tienda.valoracion).toFixed(1) }}</span>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500">{{ tienda.total_resenas }} reseñas</span>
                                            </div>
                                        </div>

                                        <!-- Flecha -->
                                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>

                                    <!-- Sin resultados en dropdown -->
                                    <div
                                        v-if="sugerenciasTiendas.length === 0 && busqueda.trim() !== ''"
                                        class="p-4 text-center text-sm text-gray-500"
                                    >
                                        No se encontraron tiendas
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Acciones derecha -->
                    <div class="flex shrink-0 items-center gap-4">
                        <!-- Carrito -->
                        <button class="relative">
                            <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                                0
                            </span>
                        </button>

                        <!-- Botón Acceder (no autenticado) -->
                        <button 
                            v-if="!user"
                            @click="showAuthModal = true"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                        >
                            Acceder
                        </button>

                        <!-- Avatar (autenticado) -->
                        <Link 
                            v-else
                            :href="route('dashboard')"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </Link>

                        <!-- Botón Panel Admin (solo para admin) -->
                        <Link 
                            v-if="user && user.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:from-orange-600 hover:to-red-600"
                        >
                            🛡️ Admin
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sección de Bienvenida / Hero -->
        <div class="relative h-[500px] overflow-hidden">
            <!-- Imagen de fondo -->
            <div 
                class="absolute inset-0 bg-cover bg-no-repeat"
                style="background-image: url('/images/fondo_1.png'); background-position: center 35%;"
            ></div>
            
            <!-- Overlay con gradiente -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-transparent"></div>
            
            <!-- Contenido -->
            <div class="relative z-10 mx-auto flex h-full max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <h1 class="mb-4 text-5xl font-extrabold leading-tight text-white sm:text-6xl">
                        Sabores Auténticos de <span class="text-primary-400">Lanzarote</span>
                    </h1>
                    <p class="mb-8 text-lg text-gray-200 sm:text-xl">
                        Conectamos productores locales con tu mesa. Productos frescos, naturales y de kilómetro cero. 
                        Apoya a los agricultores y pescadores de nuestra tierra.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <button 
                            @click="() => document.getElementById('productores')?.scrollIntoView({ behavior: 'smooth' })"
                            class="rounded-lg bg-primary-500 px-8 py-3 text-base font-semibold text-white shadow-lg transition-all hover:bg-primary-600 hover:shadow-xl"
                        >
                            Explorar Productores
                        </button>
                        <button 
                            class="rounded-lg border-2 border-white bg-white/10 px-8 py-3 text-base font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20"
                        >
                            Cómo Funciona
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div id="productores" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Título sección -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-2xl font-bold text-gray-900">Nuestros Productores</h2>
                    
                    <div class="flex items-center gap-1 text-primary-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span class="text-sm font-medium">{{ tiendasFiltradas.length }} tiendas</span>
                    </div>
                </div>
                
                <!-- Dropdown de categorías horizontal -->
                <div ref="categoriesRef" class="relative">
                    <button
                        @click="showCategoriesDropdown = !showCategoriesDropdown"
                        class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>¿Qué tipo de producto buscas?</span>
                        <svg class="h-4 w-4 transition-transform duration-200" :class="showCategoriesDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown menu horizontal -->
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <div
                            v-if="showCategoriesDropdown"
                            class="absolute left-0 top-full z-30 mt-2 flex gap-2 rounded-lg border border-gray-200 bg-white p-2 shadow-xl"
                        >
                            <button
                                v-for="cat in categorias"
                                :key="cat.nombre"
                                @click="filtrarPorCategoria(cat.nombre); showCategoriesDropdown = false"
                                :class="[
                                    'flex flex-col items-center justify-center gap-1 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 hover:scale-105',
                                    categoriaActiva === cat.nombre
                                        ? 'bg-primary-500 text-white shadow-md'
                                        : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
                                ]"
                            >
                                <span class="text-2xl">{{ cat.icono }}</span>
                                <span class="whitespace-nowrap text-xs">{{ cat.nombre }}</span>
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Grid de tiendas -->
            <div v-if="tiendasFiltradas.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <TiendaCard
                    v-for="tienda in tiendasFiltradas"
                    :key="tienda.id"
                    :tienda="tienda"
                />
            </div>

            <!-- Sin resultados -->
            <div v-else class="py-16 text-center">
                <p class="text-gray-500">No se encontraron tiendas</p>
            </div>
        </div>

        <svg
            class="block h-12 w-full text-primary-400"
            viewBox="0 0 1200 120"
            preserveAspectRatio="none"
            aria-hidden="true"
        >
            <path
                d="M0 96L60 78C120 60 240 24 360 18C480 12 600 36 720 50C840 64 960 68 1080 62C1140 58 1170 54 1200 50V120H0Z"
                fill="currentColor"
            />
        </svg>

        <!-- Sección Colabora con Rustikan -->
        <section class="relative overflow-hidden bg-primary-400 py-16">
            <!-- Ondas decorativas de fondo -->
            <div class="absolute inset-0 overflow-hidden opacity-15 pointer-events-none">
                <svg class="absolute left-1/2 top-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 text-primary-600" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Icono y título -->
                <div class="mb-12 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-full bg-white p-4 shadow-lg">
                            <svg class="h-12 w-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-bold text-white">Colabora con Rustikan</h2>
                </div>

                <!-- Grid de opciones -->
                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Hazte Repartidor -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-8 h-64 w-64 text-white opacity-50" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M43.3,-72.8C54.5,-63.8,60.3,-47.5,65.9,-32.1C71.5,-16.7,76.9,-2.2,75.4,11.5C73.9,25.2,65.5,38.1,54.8,48.7C44.1,59.3,31.1,67.6,16.5,71.8C1.9,76,-14.3,76.1,-28.9,71.5C-43.5,66.9,-56.5,57.6,-65.8,45.2C-75.1,32.8,-80.7,17.3,-81.2,1.5C-81.7,-14.3,-77.1,-30.4,-68.3,-43.3C-59.5,-56.2,-46.5,-65.9,-32.8,-73.5C-19.1,-81.1,-4.7,-86.6,8.7,-84.3C22.1,-82,32.1,-81.8,43.3,-72.8Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="https://images.unsplash.com/photo-1574068468668-a05a11f871da?w=400&h=400&fit=crop"
                                    alt="Repartidor"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Hazte repartidor</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            Realiza entregas a través de Rustikan y disfruta de flexibilidad, libertad y ganancias competitivas en tu zona.
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>

                    <!-- Hazte Productor -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=400&fit=crop"
                                    alt="Productor"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Hazte Productor</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            ¡Crece con Rustikan! Aumenta tus ventas y alcanza más clientes con nuestra tecnología y red de usuarios.
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>

                    <!-- Trabaja con nosotros -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-6 h-60 w-60 text-white opacity-40" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="/images/camiseta.png"
                                    alt="Equipo"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Trabaja con nosotros</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            ¿Buscas un nuevo reto apasionante? Si eres exigente, humilde y te gusta trabajar en equipo, ¡queremos conocerte!
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative bg-gray-900 text-gray-300">
            <svg
                class="pointer-events-none absolute -top-16 left-0 h-16 w-full text-gray-900"
                viewBox="0 0 1200 120"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <path
                    d="M0 10L60 34C120 58 240 106 360 112C480 118 600 78 720 54C840 30 960 22 1080 32C1140 38 1170 46 1200 54V120H0Z"
                    fill="currentColor"
                />
            </svg>
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Columna 1: Logo y descripción -->
                    <div class="md:col-span-1">
                        <img src="/images/logo.png" alt="Rustikan" class="mb-4 h-12 w-auto brightness-0 invert" />
                        <p class="text-sm">
                            Conectamos productores locales con consumidores, promoviendo el comercio justo y sostenible en Lanzarote.
                        </p>
                    </div>

                    <!-- Columna 2: Enlaces -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Sobre Nosotros</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Quiénes somos</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Nuestra misión</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Contacto</a></li>
                        </ul>
                    </div>

                    <!-- Columna 3: Para productores -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Para Productores</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Vende con nosotros</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Hazte repartidor</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Centro de ayuda</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Preguntas frecuentes</a></li>
                        </ul>
                    </div>

                    <!-- Columna 4: Legal y redes -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Legal</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Términos y condiciones</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Política de privacidad</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Cookies</a></li>
                        </ul>
                        
                        <!-- Redes sociales -->
                        <div class="mt-6">
                            <h4 class="mb-3 text-sm font-semibold text-white">Síguenos</h4>
                            <div class="flex gap-3">
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 border-t border-gray-800 pt-8 text-center text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} Rustikan. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>

        <!-- Modal de Autenticación -->
        <AuthModal :show="showAuthModal" @close="showAuthModal = false" />
    </div>
</template>
    {
