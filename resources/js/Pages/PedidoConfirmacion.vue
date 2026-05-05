<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import confetti from 'canvas-confetti';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
const props = defineProps({
    pedido: { type: Object, required: true },
});

onMounted(() => {
    if (props.pedido.estado !== 'cancelado') {
        confetti({
            particleCount: 120,
            spread: 70,
            origin: { y: 0.55 },
            colors: ['#7c3aed', '#10b981', '#f59e0b', '#3b82f6', '#ec4899'],
        });
    }
});

const estadoLabels = {
    pendiente:   { key: 'status_pendiente', bg: 'bg-yellow-100 dark:bg-yellow-900/40', text: 'text-yellow-700 dark:text-yellow-300' },
    confirmado:  { key: 'status_confirmado', bg: 'bg-blue-100 dark:bg-blue-900/40',   text: 'text-blue-700 dark:text-blue-300'   },
    preparando:  { key: 'status_preparando', bg: 'bg-indigo-100 dark:bg-indigo-900/40', text: 'text-indigo-700 dark:text-indigo-300' },
    en_camino:   { key: 'status_en_camino',  bg: 'bg-purple-100 dark:bg-purple-900/40', text: 'text-purple-700 dark:text-purple-300' },
    entregado:   { key: 'status_entregado',  bg: 'bg-green-100 dark:bg-green-900/40',  text: 'text-green-700 dark:text-green-300'  },
    cancelado:   { key: 'status_cancelado',  bg: 'bg-red-100 dark:bg-red-900/40',    text: 'text-red-700 dark:text-red-300'    },
};

const estadoInfo = estadoLabels[props.pedido.estado] ?? estadoLabels.pendiente;

const imgSrc = (path) => {
    if (!path) return '/images/logo.png';
    return path.startsWith('http') ? path : '/storage/' + path;
};

// ── Cancelación ────────────────────────────────────────────────────────────
const mostrarModalCancelar = ref(false);
const tipoReembolso = ref('tarjeta');
const cancelando = ref(false);

const cancelarPedido = () => {
    cancelando.value = true;
    router.post(route('pedidos.cancelar', props.pedido.id), {
        tipo_reembolso: tipoReembolso.value,
    }, {
        onFinish: () => {
            cancelando.value = false;
            mostrarModalCancelar.value = false;
        },
    });
};

const puedeCancelar = ['pendiente', 'confirmado'].includes(props.pedido.estado);
</script>

<template>
    <Head :title="`Pedido ${pedido.numero_pedido} – Rustikan`" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">

        <NavbarPublico />

        <main class="mx-auto max-w-2xl px-4 pt-24 pb-12 sm:px-6 lg:px-8">

            <!-- Icono de éxito animado -->
            <div class="flex flex-col items-center text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ t('confirmation.order_placed') }}</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">{{ t('confirmation.order_desc') }}</p>
            </div>

            <!-- Tarjeta de resumen -->
            <div class="mt-10 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-6 py-5">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ t('confirmation.order_number') }}</p>
                        <p class="mt-1 text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</p>
                    </div>
                    <span :class="['rounded-full px-3 py-1.5 text-xs font-bold', estadoInfo.bg, estadoInfo.text]">
                        {{ t('orders.' + estadoInfo.key) }}
                    </span>
                </div>

                <!-- Productos -->
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div
                        v-for="item in pedido.items"
                        :key="item.id"
                        class="flex items-center gap-4 px-6 py-4"
                    >
                        <img
                            :src="imgSrc(item.producto_imagen)"
                            :alt="item.producto_nombre"
                            class="h-16 w-16 flex-shrink-0 rounded-xl object-cover"
                        />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ item.producto_nombre }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.tienda_nombre }}</p>
                            <p class="mt-0.5 text-sm text-gray-400 dark:text-gray-500">{{ item.cantidad }} × {{ Number(item.precio_unitario).toFixed(2) }}€</p>
                        </div>
                        <p class="font-bold text-gray-900 dark:text-white">{{ Number(item.subtotal).toFixed(2) }}€</p>
                    </div>
                </div>

                <!-- Totales -->
                <div class="space-y-3 border-t border-gray-100 dark:border-gray-700 px-6 py-5 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ t('confirmation.subtotal') }}</span>
                        <span>{{ Number(pedido.subtotal).toFixed(2) }}€</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>{{ t('confirmation.shipping') }}</span>
                        <span :class="pedido.gastos_envio == 0 ? 'text-green-600 font-medium' : ''">
                            {{ pedido.gastos_envio == 0 ? t('confirmation.free') : Number(pedido.gastos_envio).toFixed(2) + '€' }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-3">
                        <span class="font-bold text-gray-900 dark:text-white">{{ t('confirmation.total') }}</span>
                        <span class="text-xl font-extrabold text-primary-600">{{ Number(pedido.total).toFixed(2) }}€</span>
                    </div>
                </div>

                <!-- Datos de entrega -->
                <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-5">
                    <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('confirmation.delivery_section') }}</h3>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
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
                    class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 shadow-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    {{ t('confirmation.view_orders') }}
                </Link>
                <Link
                    href="/"
                    class="flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ t('confirmation.back_home') }}
                </Link>
            </div>

            <!-- Cancelar pedido -->
            <div v-if="puedeCancelar" class="mt-6 flex justify-center">
                <button
                    @click="mostrarModalCancelar = true"
                    class="text-sm text-red-500 hover:text-red-700 dark:hover:text-red-400 underline underline-offset-2 transition-colors"
                >
                    {{ t('confirmation.cancel_btn') }}
                </button>
            </div>

        </main>
    </div>

    <!-- Modal cancelación -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="mostrarModalCancelar" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('confirmation.cancel_title') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('confirmation.cancel_desc') }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button
                        @click="tipoReembolso = 'tarjeta'"
                        :class="['flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-center transition-all',
                            tipoReembolso === 'tarjeta'
                                ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-300']"
                    >
                        <svg class="h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ t('confirmation.refund_card') }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ t('confirmation.refund_card_desc') }}</span>
                    </button>
                    <button
                        @click="tipoReembolso = 'rusticoin'"
                        :class="['flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-center transition-all',
                            tipoReembolso === 'rusticoin'
                                ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20'
                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-300']"
                    >
                        <span class="text-2xl">🪙</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ t('confirmation.refund_rc') }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ t('confirmation.refund_rc_desc') }}</span>
                    </button>
                </div>

                <div class="mt-6 flex gap-3">
                    <button
                        @click="mostrarModalCancelar = false"
                        class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        {{ t('confirmation.back') }}
                    </button>
                    <button
                        @click="cancelarPedido"
                        :disabled="cancelando"
                        class="flex-1 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-60 transition-colors"
                    >
                        {{ cancelando ? t('confirmation.cancelling') : t('confirmation.confirm_cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
