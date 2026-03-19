<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useCarrito } from '@/Composables/useCarrito';

const {
    items,
    totalItems,
    totalPrecio,
    itemsAgrupadosPorTienda,
    eliminarItem,
    actualizarCantidad,
    vaciarCarrito,
} = useCarrito();

/** Gastos de envío estimados (lógica futura) */
const gastosEnvio = computed(() => (totalPrecio.value > 0 ? 2.5 : 0));

/** Total final incluyendo envío */
const totalFinal = computed(() => totalPrecio.value + gastosEnvio.value);

/** Navegar a inicio si el carrito está vacío tras vaciar */
const handleVaciar = () => {
    vaciarCarrito();
};

/** Proceder al pago — en el futuro conectar con pasarela */
const procederAlPago = () => {
    // TODO: Integrar pasarela de pago
    alert('El proceso de pago se implementará próximamente.');
};
</script>

<template>
    <Head title="Mi carrito – Rustikan" />

    <div class="min-h-screen bg-gray-50">

        <!-- ── Navbar mínimo ────────────────────────────────────────────────── -->
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
                    Seguir comprando
                </Link>
            </div>
        </nav>

        <!-- ── Contenido principal ─────────────────────────────────────────── -->
        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Título de página -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">Mi carrito</h1>
                <p v-if="totalItems > 0" class="mt-1 text-sm text-gray-500">
                    {{ totalItems }} {{ totalItems === 1 ? 'producto' : 'productos' }} de
                    {{ itemsAgrupadosPorTienda.length }} {{ itemsAgrupadosPorTienda.length === 1 ? 'tienda' : 'tiendas' }}
                </p>
            </div>

            <!-- ── Carrito vacío ─────────────────────────────────────────── -->
            <div v-if="totalItems === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800">Tu carrito está vacío</h2>
                <p class="mt-2 text-sm text-gray-500">Explora las tiendas y añade los productos que más te gusten.</p>
                <Link
                    href="/"
                    class="mt-6 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    Explorar tiendas
                </Link>
            </div>

            <!-- ── Layout con carrito y resumen ────────────────────────────── -->
            <div v-else class="grid gap-8 lg:grid-cols-3">

                <!-- Columna izquierda: productos -->
                <div class="space-y-6 lg:col-span-2">

                    <!-- Bloque por tienda -->
                    <div
                        v-for="grupo in itemsAgrupadosPorTienda"
                        :key="grupo.tienda_id"
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                    >
                        <!-- Cabecera de la tienda -->
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-5 py-4">
                            <Link
                                :href="`/tienda/${grupo.tienda_slug}`"
                                class="flex items-center gap-2 text-sm font-bold text-gray-800 hover:text-primary-600"
                            >
                                <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ grupo.tienda_nombre }}
                            </Link>
                            <span class="text-sm font-semibold text-gray-500">
                                Subtotal: <span class="text-gray-800">{{ grupo.subtotal.toFixed(2) }}€</span>
                            </span>
                        </div>

                        <!-- Filas de producto -->
                        <div class="divide-y divide-gray-100">
                            <div
                                v-for="item in grupo.items"
                                :key="item.id"
                                class="flex items-center gap-4 px-5 py-4 transition-colors hover:bg-gray-50"
                            >
                                <!-- Imagen -->
                                <img
                                    :src="item.imagen || '/images/logo.png'"
                                    :alt="item.nombre"
                                    class="h-20 w-20 flex-shrink-0 rounded-xl object-cover shadow-sm"
                                />

                                <!-- Info -->
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ item.nombre }}</h3>
                                    <p class="mt-0.5 text-sm text-gray-500">{{ item.precio.toFixed(2) }}€ / {{ item.unidad }}</p>
                                </div>

                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-1.5">
                                    <button
                                        @click="actualizarCantidad(item.id, -1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800"
                                        aria-label="Reducir cantidad"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold text-gray-800">{{ item.cantidad }}</span>
                                    <button
                                        @click="actualizarCantidad(item.id, 1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-500 text-white transition-colors hover:bg-primary-600"
                                        aria-label="Aumentar cantidad"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Precio de línea -->
                                <div class="w-20 text-right">
                                    <span class="font-bold text-gray-900">{{ (item.precio * item.cantidad).toFixed(2) }}€</span>
                                </div>

                                <!-- Eliminar -->
                                <button
                                    @click="eliminarItem(item.id)"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-300 transition-colors hover:bg-red-50 hover:text-red-500"
                                    aria-label="Eliminar producto"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Vaciar carrito -->
                    <div class="flex justify-end">
                        <button
                            @click="handleVaciar"
                            class="flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Vaciar carrito
                        </button>
                    </div>
                </div>

                <!-- Columna derecha: resumen del pedido -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                        <div class="border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">Resumen del pedido</h2>
                        </div>

                        <div class="space-y-4 px-6 py-5">
                            <!-- Línea de subtotal -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium text-gray-800">{{ totalPrecio.toFixed(2) }}€</span>
                            </div>

                            <!-- Gastos de envío -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Gastos de envío</span>
                                <span class="font-medium text-gray-800">{{ gastosEnvio.toFixed(2) }}€</span>
                            </div>

                            <!-- Separador -->
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-baseline justify-between">
                                    <span class="font-bold text-gray-900">Total</span>
                                    <span class="text-2xl font-extrabold text-primary-600">{{ totalFinal.toFixed(2) }}€</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">IVA incluido</p>
                            </div>
                        </div>

                        <!-- CTA: Proceder al pago -->
                        <div class="px-6 pb-6">
                            <button
                                @click="procederAlPago"
                                class="group flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-4 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-600 hover:shadow-md"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Proceder al pago
                            </button>

                            <!-- Garantías -->
                            <ul class="mt-4 space-y-2 text-xs text-gray-400">
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Productos locales de Lanzarote
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Pago 100% seguro
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Envío a domicilio
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>
