<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tienda: {
        type: Object,
        required: true
    }
});

const tabActiva = ref('Todos');
const cantidad = ref(1);
const scrolled = ref(false);

// Detectar scroll
const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

// Obtener categorías únicas de los productos de esta tienda
const tabs = computed(() => {
    const categorias = {};
    
    props.tienda.productos.forEach(producto => {
        const catNombre = producto.categoria.nombre;
        categorias[catNombre] = (categorias[catNombre] || 0) + 1;
    });
    
    const result = [{ nombre: 'Todos', count: props.tienda.productos.length }];
    
    Object.entries(categorias).forEach(([nombre, count]) => {
        result.push({ nombre, count });
    });
    
    return result;
});

// Productos filtrados por categoría
const productosFiltrados = computed(() => {
    if (tabActiva.value === 'Todos') {
        return props.tienda.productos;
    }
    return props.tienda.productos.filter(p => p.categoria.nombre === tabActiva.value);
});

const filtrarPorCategoria = (categoria) => {
    tabActiva.value = categoria;
};

const agregarAlCarrito = (producto) => {
    alert(`${producto.nombre} agregado al carrito`);
};
</script>

<template>
    <Head :title="props.tienda.nombre" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navbar Transformable -->
        <nav 
            :class="[
                'sticky top-0 z-50 transition-all duration-300',
                scrolled 
                    ? 'bg-white border-b border-gray-200 shadow-md' 
                    : 'bg-white mx-12 mt-4 rounded-2xl border border-gray-200 shadow-sm'
            ]"
        >
            <div :class="[
                'mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-300',
                scrolled ? 'max-w-full' : 'max-w-7xl'
            ]">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link href="/" class="flex items-center">
                            <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                        </Link>
                        <Link href="/" class="text-sm text-gray-600 hover:text-gray-900">
                            ← Volver a tiendas
                        </Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <button class="relative">
                            <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                                0
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Banner de la tienda -->
        <div class="relative h-64 overflow-hidden bg-gradient-to-br from-primary-500 to-primary-600">
            <img 
                :src="props.tienda.imagen_portada || props.tienda.logo || '/images/logo.png'" 
                :alt="props.tienda.nombre"
                class="h-full w-full object-cover opacity-30"
            />
        </div>

        <!-- Contenido principal -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Card de perfil de la tienda -->
            <div class="-mt-16 mb-8 rounded-xl bg-white p-6 shadow-lg">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ props.tienda.nombre }}</h1>
                        <div class="mt-2 flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ props.tienda.direccion }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg 
                                    v-for="i in 5" 
                                    :key="i"
                                    :class="[
                                        'h-4 w-4',
                                        i <= Math.round(props.tienda.valoracion) ? 'text-primary-500' : 'text-gray-300'
                                    ]"
                                    fill="currentColor" 
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="ml-1 font-semibold text-gray-900">{{ Number(props.tienda.valoracion).toFixed(1) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ props.tienda.productos.length }}</div>
                            <div class="text-sm text-gray-600">Productos</div>
                        </div>
                        <div class="h-12 w-px bg-gray-300"></div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ props.tienda.total_ventas || 0 }}</div>
                            <div class="text-sm text-gray-600">Ventas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs de categorías -->
            <div class="mb-6 flex gap-2 overflow-x-auto border-b border-gray-200 pb-2">
                <button
                    v-for="tab in tabs"
                    :key="tab.nombre"
                    @click="filtrarPorCategoria(tab.nombre)"
                    :class="[
                        'whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-colors',
                        tabActiva === tab.nombre
                            ? 'bg-primary-500 text-white'
                            : 'bg-white text-gray-700 hover:bg-gray-50'
                    ]"
                >
                    {{ tab.nombre }} ({{ tab.count }})
                </button>
            </div>

            <!-- Grid de productos -->
            <div class="pb-12">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div 
                        v-for="producto in productosFiltrados" 
                        :key="producto.id"
                        class="group overflow-hidden rounded-xl bg-white shadow-sm transition-all duration-300 hover:shadow-lg"
                    >
                        <!-- Imagen del producto -->
                        <div class="relative aspect-square overflow-hidden">
                            <img 
                                :src="producto.imagen || '/images/logo.png'" 
                                :alt="producto.nombre"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                            />
                            
                            <!-- Badge Km 0 -->
                            <div class="absolute left-3 top-3 rounded-full bg-primary-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                Km 0
                            </div>

                            <!-- Badge de categoría -->
                            <div class="absolute right-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-gray-700 backdrop-blur-sm">
                                {{ producto.categoria.nombre }}
                            </div>

                            <!-- Badge destacado -->
                            <div v-if="producto.destacado" class="absolute bottom-3 left-3 rounded-full bg-yellow-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                ⭐ Destacado
                            </div>

                            <!-- Badge stock bajo -->
                            <div v-if="producto.stock <= producto.stock_minimo && producto.stock > 0" class="absolute bottom-3 right-3 rounded-full bg-orange-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                ¡Últimas unidades!
                            </div>

                            <!-- Badge sin stock -->
                            <div v-if="producto.stock === 0" class="absolute inset-0 flex items-center justify-center bg-black/50">
                                <span class="rounded-full bg-red-600 px-4 py-2 text-sm font-bold text-white">Agotado</span>
                            </div>
                        </div>

                        <!-- Info del producto -->
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-gray-900">{{ producto.nombre }}</h3>
                            
                            <p v-if="producto.descripcion" class="mt-1 text-sm text-gray-600 line-clamp-2">
                                {{ producto.descripcion }}
                            </p>

                            <div class="mt-3 flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-primary-600">{{ Number(producto.precio).toFixed(2) }}€</span>
                                    <span class="text-sm text-gray-500">/{{ producto.unidad }}</span>
                                </div>
                            </div>

                            <!-- Stock disponible -->
                            <div v-if="producto.stock > 0" class="mt-2 text-xs text-gray-500">
                                {{ producto.stock }} unidades disponibles
                            </div>

                            <!-- Botón agregar -->
                            <button 
                                @click="agregarAlCarrito(producto)"
                                :disabled="producto.stock === 0"
                                :class="[
                                    'mt-4 w-full rounded-lg py-2 text-sm font-semibold transition-colors',
                                    producto.stock === 0
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        : 'bg-primary-500 text-white hover:bg-primary-600'
                                ]"
                            >
                                {{ producto.stock === 0 ? 'Agotado' : 'Agregar al carrito' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sin resultados -->
                <div v-if="productosFiltrados.length === 0" class="py-16 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-4 text-gray-500">No hay productos en esta categoría</p>
                </div>
            </div>
        </div>
    </div>
</template>
