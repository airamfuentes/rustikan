<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pedidos: { type: Object, required: true },
});

const expandidos = ref(new Set());

const toggleExpand = (id) => {
    expandidos.value.has(id)
        ? expandidos.value.delete(id)
        : expandidos.value.add(id);
};

const estadoConfig = {
    pendiente:  { label: 'Pendiente',  bg: 'bg-yellow-100', text: 'text-yellow-700', dot: 'bg-yellow-400'  },
    confirmado: { label: 'Confirmado', bg: 'bg-blue-100',   text: 'text-blue-700',   dot: 'bg-blue-400'    },
    preparando: { label: 'Preparando', bg: 'bg-indigo-100', text: 'text-indigo-700', dot: 'bg-indigo-400'  },
    en_camino:  { label: 'En camino',  bg: 'bg-purple-100', text: 'text-purple-700', dot: 'bg-purple-400'  },
    entregado:  { label: 'Entregado',  bg: 'bg-green-100',  text: 'text-green-700',  dot: 'bg-green-400'   },
    cancelado:  { label: 'Cancelado',  bg: 'bg-red-100',    text: 'text-red-700',    dot: 'bg-red-400'     },
};

const getEstado = (e) => estadoConfig[e] ?? estadoConfig.pendiente;

const formatFecha = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('es-ES', {
        day: '2-digit', month: 'long', year: 'numeric',
    });
};
</script>

<template>
    <Head title="Mis pedidos – Rustikan" />

    <div class="min-h-screen bg-gray-50">

        <!-- Navbar mínimo -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center">
                    <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                </Link>
                <Link
                    href="/"
                    class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Inicio
                </Link>
            </div>
        </nav>

        <main class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Mis pedidos</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ pedidos.total }} pedido{{ pedidos.total !== 1 ? 's' : '' }} realizados
                </p>
            </div>

            <!-- Sin pedidos -->
            <div v-if="pedidos.data.length === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Aún no tienes pedidos</h2>
                <p class="mt-2 text-sm text-gray-500">Explora las tiendas y haz tu primer pedido.</p>
                <Link
                    href="/"
                    class="mt-6 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    Explorar tiendas
                </Link>
            </div>

            <!-- Lista de pedidos -->
            <div v-else class="space-y-4">
                <div
                    v-for="pedido in pedidos.data"
                    :key="pedido.id"
                    class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                >
                    <!-- Cabecera del pedido (siempre visible) -->
                    <button
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-gray-50"
                        @click="toggleExpand(pedido.id)"
                    >
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-gray-900">{{ pedido.numero_pedido }}</span>
                                <span :class="['flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', getEstado(pedido.estado).bg, getEstado(pedido.estado).text]">
                                    <span :class="['inline-block h-1.5 w-1.5 rounded-full', getEstado(pedido.estado).dot]" />
                                    {{ getEstado(pedido.estado).label }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ formatFecha(pedido.created_at) }} ·
                                {{ pedido.items_count }} producto{{ pedido.items_count !== 1 ? 's' : '' }}
                            </p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-lg font-extrabold text-primary-600">{{ Number(pedido.total).toFixed(2) }}€</p>
                        </div>
                        <svg
                            class="h-5 w-5 shrink-0 text-gray-400 transition-transform duration-200"
                            :class="{ 'rotate-180': expandidos.has(pedido.id) }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Detalle expandible -->
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="max-h-0 opacity-0"
                        enter-to-class="max-h-screen opacity-100"
                        leave-active-class="transition-all duration-200"
                        leave-from-class="max-h-screen opacity-100"
                        leave-to-class="max-h-0 opacity-0"
                    >
                        <div v-if="expandidos.has(pedido.id)" class="overflow-hidden border-t border-gray-100">

                            <!-- Items del pedido -->
                            <div class="divide-y divide-gray-100">
                                <div
                                    v-for="item in pedido.items"
                                    :key="item.id"
                                    class="flex items-center gap-4 px-6 py-3"
                                >
                                    <img
                                        :src="item.producto_imagen || '/images/logo.png'"
                                        :alt="item.producto_nombre"
                                        class="h-12 w-12 flex-shrink-0 rounded-lg object-cover"
                                    />
                                    <div class="min-w-0 flex-1 text-sm">
                                        <p class="font-medium text-gray-900">{{ item.producto_nombre }}</p>
                                        <p class="text-gray-500">{{ item.tienda_nombre }}</p>
                                    </div>
                                    <div class="text-sm text-gray-500">{{ item.cantidad }} ×</div>
                                    <div class="w-16 text-right text-sm font-semibold text-gray-800">
                                        {{ Number(item.subtotal).toFixed(2) }}€
                                    </div>
                                </div>
                            </div>

                            <!-- Totales y dirección -->
                            <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                                <div class="flex flex-col gap-1 text-xs text-gray-500 sm:flex-row sm:justify-between">
                                    <div class="flex items-start gap-1.5">
                                        <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>{{ pedido.direccion_envio }}</span>
                                    </div>
                                    <div class="text-right">
                                        Subtotal {{ Number(pedido.subtotal).toFixed(2) }}€ +
                                        Envío {{ pedido.gastos_envio == 0 ? 'GRATIS' : Number(pedido.gastos_envio).toFixed(2) + '€' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="pedidos.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <Link
                    v-for="link in pedidos.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="[
                        'rounded-lg px-3 py-2 text-sm transition-colors',
                        link.active
                            ? 'bg-primary-500 font-bold text-white'
                            : link.url
                                ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'
                                : 'cursor-not-allowed bg-white text-gray-300 border border-gray-100',
                    ]"
                />
            </div>

        </main>
    </div>
</template>
