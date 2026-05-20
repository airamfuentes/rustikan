<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Package, AlertTriangle, XCircle, Store, ChevronLeft, ChevronRight, ChevronRight as ArrowRight, Search } from 'lucide-vue-next';

const props = defineProps({
    tiendas: { type: Object, required: true },
    stats:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search ?? '');

const buscar = () => {
    router.get(route('supplier.stock.index'), { search: search.value }, { preserveState: true, replace: true });
};

let searchDebounce = null;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(buscar, 300);
});
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Cabecera -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventario / Stock</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecciona una tienda para ver su inventario</p>
            </div>

            <!-- Stats globales -->
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

            <!-- Lista de tiendas -->
            <div class="rounded-2xl bg-white dark:bg-gray-800 shadow">

                <!-- Buscador -->
                <div class="border-b border-gray-100 dark:border-gray-700 px-4 py-3">
                    <div class="relative max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar tienda…"
                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                        />
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="tiendas.data.length === 0" class="flex flex-col items-center py-16 text-center">
                    <Store class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay tiendas registradas</p>
                </div>

                <!-- Tabla de tiendas -->
                <div v-else class="overflow-x-auto rounded-t-2xl" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tienda</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Productos</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-amber-500">Stock bajo</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-red-500">Sin stock</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr
                                v-for="tienda in tiendas.data"
                                :key="tienda.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                                @click="$inertia.visit(route('supplier.stock.tienda', tienda.id))"
                            >
                                <!-- Nombre tienda -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0">
                                            <img
                                                v-if="tienda.logo"
                                                :src="`/storage/${tienda.logo}`"
                                                :alt="tienda.nombre"
                                                class="h-full w-full object-cover"
                                                @error="(e) => e.target.style.display='none'"
                                            />
                                            <Store v-else class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white text-sm">{{ tienda.nombre }}</span>
                                    </div>
                                </td>

                                <!-- Total productos -->
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ tienda.total_productos ?? 0 }}</span>
                                </td>

                                <!-- Stock bajo -->
                                <td class="px-6 py-4 text-center">
                                    <span v-if="tienda.bajo_stock > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-400">
                                        <AlertTriangle class="h-3 w-3" /> {{ tienda.bajo_stock }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>

                                <!-- Sin stock -->
                                <td class="px-6 py-4 text-center">
                                    <span v-if="tienda.sin_stock > 0" class="inline-flex items-center gap-1 rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-xs font-semibold text-red-700 dark:text-red-400">
                                        <XCircle class="h-3 w-3" /> {{ tienda.sin_stock }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>

                                <!-- Acción -->
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('supplier.stock.tienda', tienda.id)"
                                        class="inline-flex items-center gap-1 rounded-lg bg-primary-50 dark:bg-primary-900/20 px-3 py-1.5 text-xs font-semibold text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors"
                                        @click.stop
                                    >
                                        Ver inventario <ArrowRight class="h-3.5 w-3.5" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="tiendas.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ tiendas.from }}–{{ tiendas.to }} de {{ tiendas.total }} tiendas
                    </p>
                    <div class="flex gap-2">
                        <Link v-if="tiendas.prev_page_url" :href="tiendas.prev_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Anterior
                        </Link>
                        <Link v-if="tiendas.next_page_url" :href="tiendas.next_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            Siguiente <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>

            </div>

        </div>
    </LayoutSupplier>
</template>
