<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    pedido: { type: Object, required: true },
});

const estadoLabels = {
    pendiente:   { label: 'Pendiente',    bg: 'bg-yellow-100', text: 'text-yellow-700' },
    confirmado:  { label: 'Confirmado',   bg: 'bg-blue-100',   text: 'text-blue-700'   },
    preparando:  { label: 'Preparando',   bg: 'bg-indigo-100', text: 'text-indigo-700' },
    en_camino:   { label: 'En camino',    bg: 'bg-purple-100', text: 'text-purple-700' },
    entregado:   { label: 'Entregado',    bg: 'bg-green-100',  text: 'text-green-700'  },
    cancelado:   { label: 'Cancelado',    bg: 'bg-red-100',    text: 'text-red-700'    },
};

const estadoInfo = estadoLabels[props.pedido.estado] ?? estadoLabels.pendiente;
</script>

<template>
    <Head :title="`Pedido ${pedido.numero_pedido} – Rustikan`" />

    <div class="min-h-screen bg-gray-50">

        <!-- Navbar mínimo -->
        <nav class="sticky top-0 z-50 border-b border-gray-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center">
                    <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                </Link>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('pedidos.index')"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-gray-100 hover:text-gray-900"
                    >
                        Mis pedidos
                    </Link>
                    <Link
                        href="/"
                        class="flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-600"
                    >
                        Seguir comprando
                    </Link>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">

            <!-- Icono de éxito animado -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900">¡Pedido realizado!</h1>
                <p class="mt-2 text-gray-500">Hemos recibido tu pedido y lo estamos procesando.</p>
            </div>

            <!-- Tarjeta de resumen -->
            <div class="mt-10 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-6 py-5">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-gray-400">Número de pedido</p>
                        <p class="mt-1 text-xl font-extrabold tracking-tight text-gray-900">{{ pedido.numero_pedido }}</p>
                    </div>
                    <span :class="['rounded-full px-3 py-1.5 text-xs font-bold', estadoInfo.bg, estadoInfo.text]">
                        {{ estadoInfo.label }}
                    </span>
                </div>

                <!-- Productos -->
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="item in pedido.items"
                        :key="item.id"
                        class="flex items-center gap-4 px-6 py-4"
                    >
                        <img
                            :src="item.producto_imagen || '/images/logo.png'"
                            :alt="item.producto_nombre"
                            class="h-16 w-16 flex-shrink-0 rounded-xl object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900">{{ item.producto_nombre }}</p>
                            <p class="text-sm text-gray-500">{{ item.tienda_nombre }}</p>
                            <p class="mt-0.5 text-sm text-gray-400">{{ item.cantidad }} × {{ Number(item.precio_unitario).toFixed(2) }}€</p>
                        </div>
                        <p class="font-bold text-gray-900">{{ Number(item.subtotal).toFixed(2) }}€</p>
                    </div>
                </div>

                <!-- Totales -->
                <div class="space-y-3 border-t border-gray-100 px-6 py-5 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ Number(pedido.subtotal).toFixed(2) }}€</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Gastos de envío</span>
                        <span :class="pedido.gastos_envio == 0 ? 'text-green-600 font-medium' : ''">
                            {{ pedido.gastos_envio == 0 ? 'GRATIS' : Number(pedido.gastos_envio).toFixed(2) + '€' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="text-xl font-extrabold text-primary-600">{{ Number(pedido.total).toFixed(2) }}€</span>
                    </div>
                </div>

                <!-- Datos de entrega -->
                <div class="border-t border-gray-100 px-6 py-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700">Datos de entrega</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ pedido.direccion_envio }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ pedido.telefono_contacto }}</span>
                        </div>
                        <div v-if="pedido.notas" class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <span>{{ pedido.notas }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link
                    :href="route('pedidos.index')"
                    class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Ver mis pedidos
                </Link>
                <Link
                    href="/"
                    class="flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Volver al inicio
                </Link>
            </div>

        </main>
    </div>
</template>
