<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-gray-200">
                    <Link :href="route('admin.tiendas.index')" class="text-gray-400 hover:text-gray-700">Tiendas</Link>
                    <span class="text-gray-300">/</span>
                    <span>{{ tienda.nombre }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.tiendas.index')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                    <Link :href="route('admin.tiendas.productos.create', tienda.id)" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                        Nuevo Producto
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Estadísticas de la tienda -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Productos</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Disponibles</div>
                        <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.disponibles }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">No Disponibles</div>
                        <div class="mt-2 text-3xl font-bold text-red-600">{{ stats.no_disponibles }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Destacados</div>
                        <div class="mt-2 text-3xl font-bold text-yellow-600">{{ stats.destacados }}</div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                            <input
                                v-model="form.search"
                                @input="buscarConDebounce"
                                type="text"
                                placeholder="Nombre de producto..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Disponibilidad</label>
                            <select
                                v-model="form.disponible"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todos</option>
                                <option value="1">Disponibles</option>
                                <option value="0">No disponibles</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button @click="limpiarFiltros" class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Precio</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <tr v-for="producto in productos.data" :key="producto.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img v-if="producto.imagen" class="h-10 w-10 rounded object-cover" :src="producto.imagen" :alt="producto.nombre" />
                                                    <div v-else class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500">
                                        <Package class="h-5 w-5" />
                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ producto.nombre }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ producto.unidad }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ formatPrice(producto.precio) }}€</div>
                                            <div v-if="producto.precio_oferta" class="text-sm text-green-600 dark:text-green-400">
                                                Oferta: {{ formatPrice(producto.precio_oferta) }}€
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    v-model.number="producto.stock"
                                                    @blur="updateStock(producto)"
                                                    type="number"
                                                    min="0"
                                                    class="w-20 rounded border-gray-300 text-sm"
                                                />
                                                <span v-if="producto.stock <= producto.stock_minimo" class="text-red-600" title="Stock bajo"><AlertTriangle class="inline h-4 w-4" /></span>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">Mín: {{ producto.stock_minimo }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex flex-col gap-1">
                                                <span
                                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                    :class="producto.disponible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                >
                                                    {{ producto.disponible ? 'Disponible' : 'No disponible' }}
                                                </span>
                                                <span v-if="producto.destacado" class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">
                                                    <Star class="h-3 w-3 fill-yellow-500 text-yellow-500" /> Destacado
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('admin.productos.edit', producto.id)" class="text-blue-600 hover:text-blue-900">
                                                    Editar
                                                </Link>
                                                <button @click="deleteProducto(producto)" class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty state -->
                        <div v-if="productos.data.length === 0" class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay productos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Esta tienda no tiene productos aún.</p>
                            <div class="mt-6">
                                <Link :href="route('admin.tiendas.productos.create', tienda.id)" class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                    Nuevo Producto
                                </Link>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div v-if="productos.data.length > 0" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <Link v-if="productos.prev_page_url" :href="productos.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">Anterior</Link>
                                <Link v-if="productos.next_page_url" :href="productos.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">Siguiente</Link>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando
                                        <span class="font-medium">{{ productos.from }}</span>
                                        a
                                        <span class="font-medium">{{ productos.to }}</span>
                                        de
                                        <span class="font-medium">{{ productos.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                        <component
                                            v-for="(link, index) in productos.links.filter(l => !l.label.includes('&laquo;') && !l.label.includes('&raquo;'))"
                                            :key="index"
                                            :is="link.url ? Link : 'span'"
                                            :href="link.url"
                                            v-html="link.label"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 text-sm font-medium',
                                                link.active ? 'z-10 bg-primary-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                                                index === 0 ? 'rounded-l-md' : '',
                                                index === productos.links.length - 1 ? 'rounded-r-md' : '',
                                                !link.url ? 'cursor-not-allowed opacity-50' : ''
                                            ]"
                                        />
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { ArrowLeft, Package, AlertTriangle, Star } from 'lucide-vue-next';

const props = defineProps({
    tienda: Object,
    productos: Object,
    stats: Object,
    filters: Object,
});

const form = reactive({
    search: props.filters?.search || '',
    disponible: props.filters?.disponible || '',
});

let debounceTimer = null;

const buscar = () => {
    const params = {};
    if (form.search && form.search.trim()) params.search = form.search.trim();
    if (form.disponible !== '') params.disponible = form.disponible;

    router.get(route('admin.tiendas.productos.index', props.tienda.id), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const buscarConDebounce = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => buscar(), 500);
};

const limpiarFiltros = () => {
    form.search = '';
    form.disponible = '';
    buscar();
};

const formatPrice = (price) => parseFloat(price).toFixed(2);

const updateStock = (producto) => {
    router.post(route('admin.productos.update-stock', producto.id), {
        stock: producto.stock,
    }, { preserveScroll: true });
};

const deleteProducto = (producto) => {
    if (confirm(`¿Estás seguro de eliminar el producto "${producto.nombre}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.productos.destroy', producto.id));
    }
};
</script>
