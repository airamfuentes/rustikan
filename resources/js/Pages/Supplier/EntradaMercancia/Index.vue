<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { imgSrc, onImgError } from '@/Composables/useImgSrc';
import {
    PackagePlus, Search, SlidersHorizontal, Store, ChevronLeft, ChevronRight,
    CalendarDays, FileText, Truck, AlertTriangle, Plus
} from 'lucide-vue-next';

const props = defineProps({
    entradas:          { type: Object,  required: true },
    tiendas:           { type: Array,   required: true },
    stats:             { type: Object,  required: true },
    filters:           { type: Object,  default: () => ({}) },
    _migrationPending: { type: Boolean, default: false },
});

const form = ref({
    search:    props.filters.search    ?? '',
    tienda_id: props.filters.tienda_id ?? '',
    desde:     props.filters.desde     ?? '',
    hasta:     props.filters.hasta     ?? '',
});

const hayFiltros = computed(() =>
    form.value.search || form.value.tienda_id || form.value.desde || form.value.hasta
);

const buscar = () => {
    router.get(route('supplier.entradas.index'), {
        search:    form.value.search,
        tienda_id: form.value.tienda_id,
        desde:     form.value.desde,
        hasta:     form.value.hasta,
    }, { preserveState: true, preserveScroll: true });
};

const limpiar = () => {
    form.value = { search: '', tienda_id: '', desde: '', hasta: '' };
    buscar();
};

let searchDebounce = null;
watch(() => form.value.search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(buscar, 300);
});

const formatDate = (str) => {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Cabecera -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Entrada de mercancía</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Historial de entradas de stock al almacén</p>
                </div>
                <Link
                    :href="route('supplier.entradas.create')"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-primary-600 transition-colors"
                >
                    <Plus class="h-4 w-4" /> Nueva entrada
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_entradas }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total entradas</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stats.hoy }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Hoy</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.total_unidades.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Unidades totales</p>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                    <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ stats.proveedores }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Proveedores distintos</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-wrap gap-3 items-center">
                <!-- Buscador -->
                <div class="relative flex-1 min-w-48">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="form.search"
                        type="text"
                        placeholder="Buscar producto, tienda, documento…"
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                    />
                </div>

                <!-- Filtro tienda -->
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                    <Store class="h-4 w-4 text-gray-400 shrink-0" />
                    <select
                        v-model="form.tienda_id"
                        @change="buscar"
                        class="text-sm text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none cursor-pointer"
                    >
                        <option value="">Todas las tiendas</option>
                        <option v-for="t in tiendas" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                    </select>
                </div>

                <!-- Fecha desde -->
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                    <CalendarDays class="h-4 w-4 text-gray-400 shrink-0" />
                    <input
                        v-model="form.desde"
                        @change="buscar"
                        type="date"
                        class="text-sm text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none cursor-pointer"
                    />
                </div>

                <!-- Fecha hasta -->
                <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                    <CalendarDays class="h-4 w-4 text-gray-400 shrink-0" />
                    <input
                        v-model="form.hasta"
                        @change="buscar"
                        type="date"
                        class="text-sm text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none cursor-pointer"
                    />
                </div>

                <!-- Limpiar -->
                <button
                    v-if="hayFiltros"
                    @click="limpiar"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                    Limpiar
                </button>
            </div>

            <!-- Tabla -->
            <div class="rounded-2xl bg-white dark:bg-gray-800 shadow">

                <!-- Empty state -->
                <div v-if="entradas.data.length === 0" class="flex flex-col items-center py-16 text-center">
                    <PackagePlus class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay entradas registradas</p>
                    <Link
                        :href="route('supplier.entradas.create')"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 transition-colors"
                    >
                        <Plus class="h-4 w-4" /> Registrar primera entrada
                    </Link>
                </div>

                <div v-else class="overflow-x-auto rounded-t-2xl" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tienda</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock anterior</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">Entrada</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-primary-600 dark:text-primary-400">Stock nuevo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Documento / Proveedor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="entrada in entradas.data"
                                :key="entrada.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                            >
                                <!-- Producto -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                            <img
                                                v-if="entrada.producto?.imagen"
                                                :src="imgSrc(entrada.producto.imagen)"
                                                :alt="entrada.producto.nombre"
                                                class="h-full w-full object-cover"
                                                @error="onImgError"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white text-sm">{{ entrada.producto?.nombre ?? '—' }}</p>
                                            <p class="text-xs text-gray-400">por {{ entrada.usuario?.name ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tienda -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ entrada.tienda?.nombre ?? '—' }}</span>
                                </td>

                                <!-- Stock anterior -->
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ entrada.stock_anterior }} {{ entrada.producto?.unidad }}</span>
                                </td>

                                <!-- Entrada -->
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">
                                        +{{ entrada.cantidad_entrada }} {{ entrada.producto?.unidad }}
                                    </span>
                                </td>

                                <!-- Stock nuevo -->
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ entrada.stock_nuevo }} {{ entrada.producto?.unidad }}</span>
                                </td>

                                <!-- Documento / Proveedor -->
                                <td class="px-6 py-4">
                                    <div class="space-y-0.5">
                                        <div v-if="entrada.numero_documento" class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <FileText class="h-3 w-3 shrink-0" /> {{ entrada.numero_documento }}
                                        </div>
                                        <div v-if="entrada.proveedor" class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <Truck class="h-3 w-3 shrink-0" /> {{ entrada.proveedor }}
                                        </div>
                                        <span v-if="!entrada.numero_documento && !entrada.proveedor" class="text-xs text-gray-400">—</span>
                                    </div>
                                </td>

                                <!-- Fecha -->
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ formatDate(entrada.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="entradas.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ entradas.from }}–{{ entradas.to }} de {{ entradas.total }} entradas
                    </p>
                    <div class="flex gap-2">
                        <Link v-if="entradas.prev_page_url" :href="entradas.prev_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Anterior
                        </Link>
                        <Link v-if="entradas.next_page_url" :href="entradas.next_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            Siguiente <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </LayoutSupplier>
</template>
