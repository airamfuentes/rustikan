<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ClipboardList, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    pedidos: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const form = ref({
    search:      props.filters.search      ?? '',
    estado:      props.filters.estado      ?? '',
    fecha_desde: props.filters.fecha_desde ?? '',
    fecha_hasta: props.filters.fecha_hasta ?? '',
});

const buscar = () => {
    router.get(route('supplier.historial'), {
        search:      form.value.search,
        estado:      form.value.estado,
        fecha_desde: form.value.fecha_desde,
        fecha_hasta: form.value.fecha_hasta,
    }, { preserveState: true });
};

const limpiar = () => {
    form.value = { search: '', estado: '', fecha_desde: '', fecha_hasta: '' };
    buscar();
};

const estadosFiltro = [
    { value: 'pendiente',      label: 'Pendiente' },
    { value: 'en_preparacion', label: 'En preparación' },
    { value: 'confirmado',     label: 'Confirmado' },
    { value: 'enviado',        label: 'Enviado' },
    { value: 'entregado',      label: 'Entregado' },
    { value: 'cancelado',      label: 'Cancelado' },
    { value: 'incidencia',     label: 'Incidencia' },
];

const estadoBadge = (estado) => {
    const map = {
        pendiente:      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        en_preparacion: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
        confirmado:     'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        enviado:        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        entregado:      'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        cancelado:      'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        incidencia:     'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return map[estado] ?? 'bg-gray-100 text-gray-800';
};

const estadoLabel = (estado) => ({
    pendiente:      'Pendiente',
    en_preparacion: 'En preparación',
    confirmado:     'Confirmado',
    enviado:        'Enviado',
    entregado:      'Entregado',
    cancelado:      'Cancelado',
    incidencia:     'Incidencia',
}[estado] ?? estado.replace('_', ' '));

const formatFecha = (d) => new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Cabecera -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Historial de pedidos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Todos los pedidos registrados en el sistema</p>
            </div>

            <!-- Filtros -->
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <input
                        v-model="form.search"
                        @keyup.enter="buscar"
                        type="text"
                        placeholder="Buscar por nº pedido o cliente..."
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    />
                </div>
                <select
                    v-model="form.estado"
                    @change="buscar"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Todos los estados</option>
                    <option v-for="e in estadosFiltro" :key="e.value" :value="e.value">{{ e.label }}</option>
                </select>
                <input
                    v-model="form.fecha_desde"
                    @change="buscar"
                    type="date"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <input
                    v-model="form.fecha_hasta"
                    @change="buscar"
                    type="date"
                    class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                />
                <button
                    v-if="form.estado || form.search || form.fecha_desde || form.fecha_hasta"
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
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pedido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-if="pedidos.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <ClipboardList class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">No hay pedidos que mostrar</p>
                            </td>
                        </tr>
                        <tr
                            v-for="pedido in pedidos.data"
                            :key="pedido.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">#{{ pedido.numero_pedido }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ pedido.user?.name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ pedido.items?.length ?? 0 }} artículo(s)</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ pedido.total }} €</td>
                            <td class="px-6 py-4">
                                <span :class="estadoBadge(pedido.estado)" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                    {{ estadoLabel(pedido.estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ formatFecha(pedido.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('supplier.pedidos.show', pedido.id)" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 text-sm font-medium">
                                    Ver detalle
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="pedidos.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ pedidos.from }}–{{ pedidos.to }} de {{ pedidos.total }} pedidos
                    </p>
                    <div class="flex gap-2">
                        <Link v-if="pedidos.prev_page_url" :href="pedidos.prev_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Anterior
                        </Link>
                        <Link v-if="pedidos.next_page_url" :href="pedidos.next_page_url"
                            class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                            Siguiente <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>

        </div>
    </LayoutSupplier>
</template>
