<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('owner.panel')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Pedido {{ pedido.numero_pedido ?? '#' + pedido.id }}
                    </h2>
                </div>
                <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-3 py-1 text-sm font-semibold">
                    {{ estadoLabel(pedido.estado) }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">

                    <!-- Columna izquierda: items -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Productos de tu tienda -->
                        <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Tus productos en este pedido</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ pedido.items.length }} artículo{{ pedido.items.length !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                                <div v-for="item in pedido.items" :key="item.id" class="flex items-center gap-4 px-6 py-4">
                                    <img
                                        :src="item.producto_imagen ? '/storage/' + item.producto_imagen : '/images/logo.png'"
                                        :alt="item.producto_nombre"
                                        class="h-14 w-14 flex-shrink-0 rounded-xl object-cover border border-gray-100 dark:border-gray-700"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ item.producto_nombre }}</p>
                                        <p class="text-xs text-gray-400">{{ Number(item.precio_unitario).toFixed(2) }}€ × {{ item.cantidad }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="font-bold text-gray-900 dark:text-white">{{ Number(item.subtotal).toFixed(2) }}€</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Total tienda -->
                            <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-6 py-4">
                                <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white">
                                    <span>Total de tu tienda</span>
                                    <span class="text-primary-600 dark:text-primary-400">{{ Number(pedido.total_tienda).toFixed(2) }}€</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div v-if="pedido.notas" class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 px-6 py-4">
                            <h4 class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-1">Notas del cliente</h4>
                            <p class="text-sm text-amber-700 dark:text-amber-400">{{ pedido.notas }}</p>
                        </div>
                    </div>

                    <!-- Columna derecha: info -->
                    <div class="space-y-6">

                        <!-- Cliente -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Cliente</h3>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-sm font-bold flex-shrink-0">
                                        {{ pedido.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ pedido.user?.name ?? '—' }}</p>
                                    </div>
                                </div>
                                <div v-if="pedido.telefono_contacto" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ pedido.telefono_contacto }}
                                </div>
                                <div v-if="pedido.direccion_envio" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ pedido.direccion_envio }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles pedido -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Detalles</h3>
                            </div>
                            <div class="px-6 py-4 space-y-2.5 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Nº pedido</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Fecha</span>
                                    <span class="text-gray-900 dark:text-gray-200">{{ formatFecha(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Hora</span>
                                    <span class="text-gray-900 dark:text-gray-200">{{ formatHora(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-1 border-t border-gray-100 dark:border-gray-700">
                                    <span class="text-gray-500 dark:text-gray-400">Estado</span>
                                    <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ estadoLabel(pedido.estado) }}
                                    </span>
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
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    pedido: { type: Object, required: true },
});

const estadoBadgeClass = (estado) => {
    const map = {
        pendiente:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        confirmado: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        preparando: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
        en_camino:  'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        entregado:  'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        cancelado:  'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return map[estado] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const estadoLabel = (estado) => {
    const map = {
        pendiente:  'Pendiente',
        confirmado: 'Confirmado',
        preparando: 'Preparando',
        en_camino:  'En camino',
        entregado:  'Entregado',
        cancelado:  'Cancelado',
    };
    return map[estado] ?? estado.replace('_', ' ');
};

const formatFecha = (dateStr) =>
    new Date(dateStr).toLocaleDateString('es-ES', {
        day: '2-digit', month: 'long', year: 'numeric',
    });

const formatHora = (dateStr) =>
    new Date(dateStr).toLocaleTimeString('es-ES', {
        hour: '2-digit', minute: '2-digit',
    });
</script>
