<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useCarrito } from '@/Composables/useCarrito';
import { ArrowRight } from 'lucide-vue-next';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const {
    totalItems,
    totalPrecio,
    itemsAgrupadosPorTienda,
    eliminarItem,
    actualizarCantidad,
} = useCarrito();

// ─── Visibilidad del dropdown ────────────────────────────────────────────────
const abierto   = ref(false);
const contenedor = ref(null);

const toggle = () => { abierto.value = !abierto.value; };

const cerrarSiClickFuera = (e) => {
    if (contenedor.value && !contenedor.value.contains(e.target)) {
        abierto.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', cerrarSiClickFuera));
onUnmounted(() => document.removeEventListener('mousedown', cerrarSiClickFuera));
</script>

<template>
    <div ref="contenedor" class="relative">

        <!-- ── Botón icono del nav ──────────────────────────────────────────── -->
        <button
            @click="toggle"
            aria-label="Abrir carrito"
            class="relative rounded-full p-2 text-gray-700 dark:text-gray-100 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>

            <!-- Badge contador de items -->
            <Transition
                enter-active-class="transition duration-150"
                enter-from-class="scale-0 opacity-0"
                enter-to-class="scale-100 opacity-100"
                leave-active-class="transition duration-100"
                leave-from-class="scale-100 opacity-100"
                leave-to-class="scale-0 opacity-0"
            >
                <span
                    v-if="totalItems > 0"
                    class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-900"
                >
                    {{ totalItems > 99 ? '99+' : totalItems }}
                </span>
            </Transition>
        </button>

        
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
        >
            <div
                v-if="abierto"
                class="fixed sm:absolute right-2 sm:right-0 top-16 sm:top-12 z-50 w-[calc(100vw-1rem)] max-w-sm sm:w-96 origin-top-right overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-2xl"
            >
                
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ t('cart.dropdown_title') }}</h3>
                        <span
                            v-if="totalItems > 0"
                            class="rounded-full bg-primary-100 dark:bg-primary-900/40 px-2 py-0.5 text-xs font-semibold text-primary-700 dark:text-primary-300"
                        >
                            {{ totalItems }} {{ totalItems === 1 ? t('cart.product_singular') : t('cart.product_plural') }}
                        </span>
                    </div>
                    <button
                        @click="abierto = false"
                        class="rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-200 dark:hover:bg-gray-600 hover:text-gray-600 dark:hover:text-gray-200"
                        aria-label="Cerrar carrito"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo: lista de productos -->
                <div class="max-h-80 overflow-y-auto">

                    <!-- Estado vacío -->
                    <div v-if="totalItems === 0" class="flex flex-col items-center py-12 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ t('cart.dropdown_empty') }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t('cart.dropdown_empty_sub') }}</p>
                    </div>

                    <!-- Items agrupados por tienda -->
                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                        <div
                            v-for="grupo in itemsAgrupadosPorTienda"
                            :key="grupo.tienda_id"
                            class="px-4 py-3"
                        >
                            <!-- Nombre de la tienda -->
                            <Link
                                :href="`/tienda/${grupo.tienda_slug}`"
                                class="mb-2 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-primary-600 hover:text-primary-700"
                                @click="abierto = false"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ grupo.tienda_nombre }}
                            </Link>

                            <!-- Productos de esta tienda -->
                            <div class="space-y-2">
                                <div
                                    v-for="item in grupo.items"
                                    :key="item.id"
                                    class="flex items-center gap-3 rounded-xl p-2 transition-colors hover:bg-gray-50"
                                >
                                    <!-- Imagen -->
                                    <img
                                        :src="item.imagen || '/images/logo.png'"
                                        :alt="item.nombre"
                                        loading="lazy"
                                        class="h-12 w-12 flex-shrink-0 rounded-lg object-cover"
                                    />

                                    <!-- Nombre y precio -->
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-800">{{ item.nombre }}</p>
                                        <p class="text-sm font-bold text-primary-600">
                                            {{ (item.precio * item.cantidad).toFixed(2) }}€
                                            <span class="text-xs font-normal text-gray-400">
                                                ({{ item.precio.toFixed(2) }}€/{{ item.unidad }})
                                            </span>
                                        </p>
                                    </div>

                                    <!-- Controles de cantidad -->
                                    <div class="flex items-center gap-1">
                                        <button
                                            @click="actualizarCantidad(item.id, -1)"
                                            class="flex h-6 w-6 items-center justify-center rounded-full border border-gray-200 text-gray-500 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600"
                                            aria-label="Reducir cantidad"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-6 text-center text-sm font-semibold text-gray-700">{{ item.cantidad }}</span>
                                        <button
                                            @click="actualizarCantidad(item.id, 1)"
                                            class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-500 text-white transition-colors hover:bg-primary-600"
                                            aria-label="Aumentar cantidad"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Eliminar -->
                                    <button
                                        @click="eliminarItem(item.id)"
                                        class="ml-1 rounded-full p-1 text-gray-300 transition-colors hover:bg-red-50 hover:text-red-500"
                                        aria-label="Eliminar producto"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Subtotal de la tienda -->
                            <div class="mt-2 flex justify-end">
                                <span class="text-xs text-gray-400">
                                    {{ t('cart.dropdown_subtotal') }}: <span class="font-semibold text-gray-600">{{ grupo.subtotal.toFixed(2) }}€</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pie: total + acciones -->
                <div v-if="totalItems > 0" class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4">
                    <div class="mb-3 flex items-baseline justify-between">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ t('cart.dropdown_total') }}</span>
                        <span class="text-xl font-extrabold text-gray-900 dark:text-white">{{ totalPrecio.toFixed(2) }}€</span>
                    </div>
                    <Link
                        href="/carrito"
                        @click="abierto = false"
                        class="block w-full rounded-xl bg-primary-500 py-3 text-center text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-600 hover:shadow-md"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            {{ t('cart.dropdown_checkout') }}
                            <ArrowRight class="h-4 w-4" />
                        </span>
                    </Link>
                </div>
            </div>
        </Transition>

    </div>
</template>

