<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Package, AlertTriangle, XCircle } from 'lucide-vue-next';

const props = defineProps({
    productos: { type: Object, required: true },
    tiendas:   { type: Array,  required: true },
    stats:     { type: Object, required: true },
    filters:   { type: Object, default: () => ({}) },
});

const form = ref({
    tienda_id:  props.filters.tienda_id  ?? '',
    search:     props.filters.search     ?? '',
    bajo_stock: props.filters.bajo_stock ?? false,
    sin_stock:  props.filters.sin_stock  ?? false,
});

const buscar = () => {
    router.get(route('supplier.stock'), {
        tienda_id:  form.value.tienda_id,
        search:     form.value.search,
        bajo_stock: form.value.bajo_stock ? '1' : '',
        sin_stock:  form.value.sin_stock  ? '1' : '',
    }, { preserveState: true });
};

const limpiar = () => {
    form.value = { tienda_id: '', search: '', bajo_stock: false, sin_stock: false };
    buscar();
};

const hayFiltros = () => form.value.tienda_id || form.value.search || form.value.bajo_stock || form.value.sin_stock;

const stockClass = (prod) => {
    if (prod.stock === 0) return 'text-red-600 dark:text-red-400 font-bold';
    if (prod.stock <= prod.stock_minimo) return 'text-amber-600 dark:text-amber-400 font-semibold';
    return 'text-gray-900 dark:text-white';
};
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Cabecera -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventario / Stock</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Estado del stock de todos los productos</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total productos</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.disponibles }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Disponibles</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-amber-500">{{ stats.bajo_stock }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Stock bajo</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-red-500">{{ stats.sin_stock }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sin stock</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <input
                        v-model="form.search"
                        @keyup.enter="buscar"
                        type="text"
                        placeholder="Buscar producto..."
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    />
                </div>
                <select
                    v-model="form.tienda_id"
                    @change="buscar"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Todas las tiendas</option>
                    <option v-for="t in tiendas" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                </select>
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <input v-model="form.bajo_stock" @change="buscar" type="checkbox" class="rounded text-amber-500 focus:ring-amber-500" />
                    <span class="text-sm text-amber-600 dark:text-amber-400 font-medium">Stock bajo</span>
                </label>
                <label class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <input v-model="form.sin_stock" @change="buscar" type="checkbox" class="rounded text-red-500 focus:ring-red-500" />
                    <span class="text-sm text-red-600 dark:text-red-400 font-medium">Sin stock</span>
                </label>
                <button
                    v-if="hayFiltros()"
                    @click="limpiar"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    Limpiar
                </button>
            </div>

            <!-- Tabla -->
            <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tienda</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Mínimo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="productos.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <Package class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay productos que mostrar</p>
                            </td>
                        </tr>
                        <tr
                            v-for="prod in productos.data"
                            :key="prod.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="prod.imagen"
                                        :src="`/storage/${prod.imagen}`"
                                        :alt="prod.nombre"
                                        class="h-10 w-10 rounded-lg object-cover flex-shrink-0"
                                        @error="(e) => e.target.style.display='none'"
                                    />
                                    <div v-else class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                        <Package class="h-5 w-5 text-gray-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ prod.nombre }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ prod.precio }} €</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ prod.tienda?.nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ prod.categoria?.nombre ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm" :class="stockClass(prod)">{{ prod.stock }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ prod.stock_minimo ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span v-if="prod.stock === 0" class="flex items-center gap-1 text-xs font-semibold text-red-600 dark:text-red-400">
                                    <XCircle class="h-3.5 w-3.5" /> Sin stock
                                </span>
                                <span v-else-if="prod.stock_minimo && prod.stock <= prod.stock_minimo" class="flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                    <AlertTriangle class="h-3.5 w-3.5" /> Bajo
                                </span>
                                <span v-else class="text-xs font-semibold text-green-600 dark:text-green-400">OK</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="productos.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ productos.from }}–{{ productos.to }} de {{ productos.total }} productos
                    </p>
                    <div class="flex gap-2">
                        <Link v-if="productos.prev_page_url" :href="productos.prev_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                            ← Anterior
                        </Link>
                        <Link v-if="productos.next_page_url" :href="productos.next_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300">
                            Siguiente →
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </LayoutSupplier>
</template>
