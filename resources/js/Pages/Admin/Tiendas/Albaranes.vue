<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-gray-200">
                    <Link :href="route('admin.tiendas.index')" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Tiendas</Link>
                    <span class="text-gray-300">/</span>
                    <span class="truncate">{{ tienda.nombre }}</span>
                    <span class="text-gray-300">/</span>
                    <span>Albaranes</span>
                </div>
                <Link :href="route('admin.tiendas.index')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Cabecera tienda -->
                <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                    <div class="flex items-center gap-4">
                        <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" :alt="tienda.nombre"
                             class="h-14 w-14 rounded-full object-cover ring-2 ring-primary-100 dark:ring-primary-900/40 shrink-0" />
                        <div v-else class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-xl font-bold text-primary-600 dark:text-primary-400 shrink-0">
                            {{ tienda.nombre.charAt(0) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ tienda.nombre }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ tienda.direccion }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div v-if="stats" class="grid grid-cols-3 gap-4">
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_entradas }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total albaranes</p>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.total_unidades.toLocaleString() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unidades totales</p>
                    </div>
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow px-5 py-4 space-y-1">
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stats.hoy }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hoy</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-48">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Buscar producto, albarán, proveedor…"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200"
                        />
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                        <CalendarDays class="h-4 w-4 text-gray-400 shrink-0" />
                        <input v-model="form.desde" @change="buscar" type="date"
                            class="text-sm text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none cursor-pointer" />
                    </div>
                    <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2.5">
                        <CalendarDays class="h-4 w-4 text-gray-400 shrink-0" />
                        <input v-model="form.hasta" @change="buscar" type="date"
                            class="text-sm text-gray-700 dark:text-gray-300 bg-transparent focus:outline-none cursor-pointer" />
                    </div>
                    <button v-if="hayFiltros" @click="limpiar"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Limpiar
                    </button>
                </div>

                <!-- Tabla -->
                <div class="rounded-xl bg-white dark:bg-gray-800 shadow">
                    <div v-if="entradas.data.length === 0" class="flex flex-col items-center py-16 text-center">
                        <FileText class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay albaranes registrados para esta tienda</p>
                    </div>

                    <div v-else class="overflow-x-auto rounded-t-xl" style="-webkit-overflow-scrolling: touch;">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock ant.</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">Entrada</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-primary-600 dark:text-primary-400">Stock nuevo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Albarán / Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Registrado por</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="entrada in entradas.data" :key="entrada.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                                <img v-if="entrada.producto?.imagen" :src="imgSrc(entrada.producto.imagen)"
                                                    :alt="entrada.producto.nombre" class="h-full w-full object-cover"
                                                    @error="(e) => e.target.style.display='none'" />
                                            </div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ entrada.producto?.nombre ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ entrada.stock_anterior }} {{ entrada.producto?.unidad }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">
                                            +{{ entrada.cantidad_entrada }} {{ entrada.producto?.unidad }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">{{ entrada.stock_nuevo }} {{ entrada.producto?.unidad }}</span>
                                    </td>
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
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ entrada.usuario?.name ?? '—' }}
                                    </td>
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
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { ArrowLeft, Search, CalendarDays, FileText, Truck, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { imgSrc } from '@/Composables/useImgSrc';

const props = defineProps({
    tienda:  { type: Object, required: true },
    entradas: { type: Object, required: true },
    stats:   { type: Object, default: null },
    filters: { type: Object, default: () => ({}) },
    _migrationPending: { type: Boolean, default: false },
});

const form = ref({
    search: props.filters.search ?? '',
    desde:  props.filters.desde  ?? '',
    hasta:  props.filters.hasta  ?? '',
});

const hayFiltros = computed(() => form.value.search || form.value.desde || form.value.hasta);

const buscar = () => {
    router.get(route('admin.tiendas.albaranes', props.tienda.id), {
        search: form.value.search,
        desde:  form.value.desde,
        hasta:  form.value.hasta,
    }, { preserveState: true, preserveScroll: true });
};

const limpiar = () => {
    form.value = { search: '', desde: '', hasta: '' };
    buscar();
};

let debounce = null;
watch(() => form.value.search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(buscar, 300);
});

const formatDate = (str) => {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>
