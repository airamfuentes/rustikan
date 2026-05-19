<template>
    <Head title="Pedido confirmado — Rustikan" />
    <NavbarPublico />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-10 text-center">
            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30 mx-auto">
                <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2">¡Pago completado!</h1>

            <template v-if="pedido">
                <p class="text-3xl font-extrabold text-primary-600 mb-3">{{ Number(pedido.total).toFixed(2) }}€</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    Pedido <span class="font-semibold text-gray-700 dark:text-gray-300">{{ pedido.numero_pedido }}</span>
                </p>
            </template>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                Recibirás un correo de confirmación en breve.
            </p>

            <Link
                :href="route('pedidos.index')"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary-600"
            >
                Ver mis pedidos
            </Link>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import { useCarrito } from '@/Composables/useCarrito';

defineProps({
    pedido: Object,
});

const { vaciarCarrito } = useCarrito();

onMounted(() => {
    vaciarCarrito({ silencioso: true });
});
</script>
