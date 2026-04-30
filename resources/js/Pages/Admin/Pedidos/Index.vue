<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Gestionar Pedidos</h2>
                <Link :href="route('admin.dashboard')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Estadísticas -->
                <div class="mb-6 grid gap-4 sm:grid-cols-6">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-4 shadow">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Pedidos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                    </div>
                    <div class="rounded-lg bg-yellow-100 p-4 shadow">
                        <p class="flex items-center gap-1.5 text-sm text-yellow-800"><Clock class="h-4 w-4" /> Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ stats.pendientes }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-4 shadow">
                        <p class="flex items-center gap-1.5 text-sm text-blue-800"><RefreshCw class="h-4 w-4" /> En Proceso</p>
                        <p class="text-2xl font-bold text-blue-900">{{ stats.en_proceso }}</p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-4 shadow">
                        <p class="text-sm text-green-800">Completados</p>
                        <p class="text-2xl font-bold text-green-900">{{ stats.completados }}</p>
                    </div>
                    <div class="rounded-lg bg-red-100 p-4 shadow">
                        <p class="text-sm text-red-800">Cancelados</p>
                        <p class="text-2xl font-bold text-red-900">{{ stats.cancelados }}</p>
                    </div>
                    <div class="rounded-lg bg-emerald-100 p-4 shadow">
                        <p class="flex items-center gap-1.5 text-sm text-emerald-800"><DollarSign class="h-4 w-4" /> Ingresos</p>
                        <p class="text-2xl font-bold text-emerald-900">{{ Number(stats.ingresos_totales).toFixed(0) }}€</p>
                    </div>
                </div>

                <!-- Búsqueda y Filtros -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-6">
                        <!-- Búsqueda -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                            <input 
                                v-model="form.search" 
                                @input="buscarConDebounce"
                                type="text" 
                                placeholder="ID o usuario..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <!-- Filtro por Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                            <select
                                v-model="form.estado"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="preparando">Preparando</option>
                                <option value="en_camino">En camino</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>

                        <!-- Fecha Desde -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desde</label>
                            <input 
                                v-model="form.fecha_desde" 
                                @change="buscar"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
                            <input 
                                v-model="form.fecha_hasta" 
                                @change="buscar"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <!-- Precio Mínimo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio mín.</label>
                            <input 
                                v-model="form.precio_min" 
                                @change="buscar"
                                type="number" 
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        <!-- Precio Máximo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio máx.</label>
                            <input 
                                v-model="form.precio_max" 
                                @change="buscar"
                                type="number" 
                                step="0.01"
                                placeholder="999.99"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button 
                            type="button" 
                            @click="limpiarFiltros" 
                            class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de Pedidos -->
                <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <tr v-for="pedido in pedidos.data" :key="pedido.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ pedido.numero_pedido ?? '#' + pedido.id }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ pedido.user?.name ?? '—' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ pedido.user?.email ?? '' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300': pedido.estado === 'pendiente',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300': pedido.estado === 'confirmado',
                                        'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300': pedido.estado === 'preparando',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300': pedido.estado === 'en_camino',
                                        'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300': pedido.estado === 'entregado',
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300': pedido.estado === 'cancelado',
                                    }" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize">
                                        {{ pedido.estado.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">{{ pedido.items?.length ?? 0 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ Number(pedido.total ?? 0).toFixed(2) }}€</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ new Date(pedido.created_at).toLocaleDateString('es-ES') }}
                                    <br>
                                    <span class="text-xs">{{ new Date(pedido.created_at).toLocaleTimeString('es-ES') }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <Link :href="route('admin.pedidos.show', pedido.id)" class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                        Ver detalles
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">Mostrando <span class="font-medium">{{ pedidos.from }}</span> a <span class="font-medium">{{ pedidos.to }}</span> de <span class="font-medium">{{ pedidos.total }}</span> resultados
                            </div>
                            <div class="flex gap-2">
                                <component
                                    v-for="link in pedidos.links" 
                                    :key="link.label" 
                                    :is="link.url ? Link : 'span'"
                                    :href="link.url" 
                                    :class="{
                                        'bg-primary-600 text-white': link.active,
                                        'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700': !link.active && link.url,
                                        'cursor-not-allowed opacity-50': !link.url
                                    }"
                                    class="rounded-md border px-3 py-2 text-sm font-medium shadow-sm"
                                    v-html="link.label"
                                />
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
import { ref } from 'vue';
import { ArrowLeft, Clock, RefreshCw, DollarSign } from 'lucide-vue-next';

const props = defineProps({
    pedidos: Object,
    stats: Object,
    filters: Object,
});

const form = ref({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
    fecha_desde: props.filters.fecha_desde || '',
    fecha_hasta: props.filters.fecha_hasta || '',
    precio_min: props.filters.precio_min || '',
    precio_max: props.filters.precio_max || '',
});

let debounceTimer = null;

const buscar = () => {
    router.get(route('admin.pedidos.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const buscarConDebounce = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        buscar();
    }, 500);
};

const limpiarFiltros = () => {
    form.value = {
        search: '',
        estado: '',
        fecha_desde: '',
        fecha_hasta: '',
        precio_min: '',
        precio_max: '',
    };
    buscar();
};
</script>
