<script setup>
import { ref, computed } from 'vue';

const mostrarCarrito = ref(false);
const itemsCarrito = ref([]);

const totalItems = computed(() => {
    return itemsCarrito.value.reduce((sum, item) => sum + item.cantidad, 0);
});

const totalPrecio = computed(() => {
    return itemsCarrito.value.reduce((sum, item) => sum + (item.precio * item.cantidad), 0).toFixed(2);
});

const toggleCarrito = () => {
    mostrarCarrito.value = !mostrarCarrito.value;
};

const eliminarItem = (index) => {
    itemsCarrito.value.splice(index, 1);
};

const actualizarCantidad = (index, cambio) => {
    const item = itemsCarrito.value[index];
    item.cantidad += cambio;
    if (item.cantidad <= 0) {
        eliminarItem(index);
    }
};
</script>

<template>
    <div class="relative">
        <!-- Botón del carrito -->
        <button 
            @click="toggleCarrito"
            class="relative rounded-full bg-gradient-to-br from-lanzarote-ocean to-lanzarote-verde p-3 shadow-lg transition-all duration-300 hover:scale-110 hover:shadow-xl"
        >
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            
            <!-- Badge contador -->
            <span 
                v-if="totalItems > 0"
                class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-lanzarote-sunset text-xs font-bold text-white shadow-lg"
            >
                {{ totalItems }}
            </span>
        </button>

        <!-- Panel del carrito -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div 
                v-if="mostrarCarrito"
                class="absolute right-0 top-14 z-50 w-96 overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-lanzarote-ocean to-lanzarote-verde p-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white">Mi Carrito</h3>
                        <button 
                            @click="toggleCarrito"
                            class="rounded-full p-1 text-white hover:bg-white/20"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Items del carrito -->
                <div class="max-h-96 overflow-y-auto p-4">
                    <div v-if="itemsCarrito.length === 0" class="py-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-600">Tu carrito está vacío</p>
                        <p class="mt-1 text-xs text-gray-500">¡Añade productos de las tiendas!</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div 
                            v-for="(item, index) in itemsCarrito" 
                            :key="index"
                            class="flex items-center gap-3 rounded-lg border border-gray-200 p-3"
                        >
                            <img :src="item.imagen" :alt="item.nombre" class="h-16 w-16 rounded-lg object-cover" />
                            
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-800">{{ item.nombre }}</h4>
                                <p class="text-sm text-gray-600">{{ item.tienda }}</p>
                                <p class="mt-1 font-bold text-lanzarote-ocean">{{ item.precio }}€</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button 
                                    @click="actualizarCantidad(index, -1)"
                                    class="rounded-full bg-gray-200 p-1 hover:bg-gray-300"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="w-8 text-center font-semibold">{{ item.cantidad }}</span>
                                <button 
                                    @click="actualizarCantidad(index, 1)"
                                    class="rounded-full bg-lanzarote-verde p-1 text-white hover:bg-lanzarote-verde/80"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer con total -->
                <div v-if="itemsCarrito.length > 0" class="border-t border-gray-200 bg-gray-50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="font-semibold text-gray-700">Total:</span>
                        <span class="text-2xl font-bold text-lanzarote-ocean">{{ totalPrecio }}€</span>
                    </div>
                    <button class="w-full rounded-xl bg-gradient-to-r from-lanzarote-ocean to-lanzarote-verde py-3 font-bold text-white shadow-lg transition-all hover:shadow-xl">
                        Proceder al Pago
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>
