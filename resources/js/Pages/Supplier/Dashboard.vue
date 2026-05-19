<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link } from '@inertiajs/vue3';
import { LayoutDashboard, PackageSearch, AlertTriangle, CheckCircle, TrendingUp, Clock } from 'lucide-vue-next';

const props = defineProps({
    stats:             { type: Object, required: true },
    pedidos_recientes: { type: Array,  required: true },
    bajo_stock:        { type: Array,  required: true },
});

const statsConfig = [
    { key: 'pendientes',     label: 'Pendientes',      color: 'text-yellow-500', bg: 'bg-yellow-50 dark:bg-yellow-900/20', icon: Clock },
    { key: 'confirmados',    label: 'Confirmados',     color: 'text-blue-500',   bg: 'bg-blue-50 dark:bg-blue-900/20',     icon: CheckCircle },
    { key: 'en_preparacion', label: 'En preparación',  color: 'text-orange-500', bg: 'bg-orange-50 dark:bg-orange-900/20', icon: PackageSearch },
    { key: 'enviados',       label: 'Enviados',        color: 'text-purple-500', bg: 'bg-purple-50 dark:bg-purple-900/20', icon: TrendingUp },
    { key: 'incidencias',    label: 'Incidencias',     color: 'text-red-500',    bg: 'bg-red-50 dark:bg-red-900/20',       icon: AlertTriangle },
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

const formatFecha = (d) => new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6">

            <!-- Cabecera -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Resumen del estado del almacén</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                <div
                    v-for="stat in statsConfig"
                    :key="stat.key"
                    class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 flex flex-col gap-2"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ stat.label }}</span>
                        <div :class="[stat.bg, 'rounded-lg p-1.5']">
                            <component :is="stat.icon" :class="[stat.color, 'h-4 w-4']" />
                        </div>
                    </div>
                    <span class="text-2xl font-bold" :class="stat.color">{{ stats[stat.key] }}</span>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">

                <!-- Pedidos recientes -->
                <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Pedidos pendientes / en preparación</h2>
                        <Link :href="route('supplier.pedidos.index')" class="text-xs text-primary-500 hover:text-primary-600 font-medium">
                            Ver todos →
                        </Link>
                    </div>

                    <div v-if="pedidos_recientes.length === 0" class="flex flex-col items-center justify-center py-12 gap-2 text-center">
                        <PackageSearch class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">No hay pedidos activos.</p>
                    </div>

                    <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li
                            v-for="pedido in pedidos_recientes"
                            :key="pedido.id"
                            class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">#{{ pedido.numero_pedido }}</span>
                                    <span :class="estadoBadge(pedido.estado)" class="rounded-full px-2 py-0.5 text-[10px] font-semibold">{{ estadoLabel(pedido.estado) }}</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ pedido.cliente }} · {{ pedido.items_count }} artículo(s)</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ pedido.total }} €</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ formatFecha(pedido.created_at) }}</p>
                            </div>
                            <Link :href="route('supplier.pedidos.show', pedido.id)" class="shrink-0 text-xs text-primary-500 hover:text-primary-600 font-medium">
                                Ver
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Bajo stock -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Stock bajo</h2>
                        <Link :href="route('supplier.stock')" class="text-xs text-primary-500 hover:text-primary-600 font-medium">
                            Ver todo →
                        </Link>
                    </div>

                    <div v-if="bajo_stock.length === 0" class="flex flex-col items-center justify-center py-12 gap-2 text-center">
                        <CheckCircle class="h-10 w-10 text-green-300 dark:text-green-700" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Todo el stock está al día.</p>
                    </div>

                    <ul v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <li
                            v-for="prod in bajo_stock"
                            :key="prod.id"
                            class="flex items-center gap-3 px-6 py-3"
                        >
                            <AlertTriangle class="h-4 w-4 shrink-0 text-amber-500" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ prod.nombre }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ prod.tienda?.nombre }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ prod.stock }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500"> / {{ prod.stock_minimo }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </LayoutSupplier>
</template>
