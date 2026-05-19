<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { FileText } from 'lucide-vue-next';

const props = defineProps({
    pedidosActivos:     { type: Array,  required: true },
    pedidosHistorial:   { type: Object, required: true },
    reviewableStoreIds: { type: Array,  default: () => [] },
});

const { success: toastSuccess } = useToasts();

// -- Tabs
const tabActivo = ref('activos');

// -- Expandibles
const expandidos = ref(new Set());
const toggleExpand = (id) => {
    if (expandidos.value.has(id)) {
        expandidos.value.delete(id);
    } else {
        expandidos.value.add(id);
    }
};

const { t } = useI18n();

// -- Estado config (labels are translated at render via t())
const estadoConfig = {
    pendiente:      { key: 'status_pendiente',      bg: 'bg-yellow-100 dark:bg-yellow-900/40', text: 'text-yellow-700 dark:text-yellow-300', dot: 'bg-yellow-400' },
    confirmado:     { key: 'status_confirmado',     bg: 'bg-blue-100 dark:bg-blue-900/40',     text: 'text-blue-700 dark:text-blue-300',     dot: 'bg-blue-400'   },
    en_preparacion: { key: 'status_preparando',     bg: 'bg-orange-100 dark:bg-orange-900/40', text: 'text-orange-700 dark:text-orange-300', dot: 'bg-orange-400' },
    preparando:     { key: 'status_preparando',     bg: 'bg-indigo-100 dark:bg-indigo-900/40', text: 'text-indigo-700 dark:text-indigo-300', dot: 'bg-indigo-400' },
    enviado:        { key: 'status_en_camino',      bg: 'bg-purple-100 dark:bg-purple-900/40', text: 'text-purple-700 dark:text-purple-300', dot: 'bg-purple-400' },
    en_camino:      { key: 'status_en_camino',      bg: 'bg-purple-100 dark:bg-purple-900/40', text: 'text-purple-700 dark:text-purple-300', dot: 'bg-purple-400' },
    entregado:      { key: 'status_entregado',      bg: 'bg-green-100 dark:bg-green-900/40',   text: 'text-green-700 dark:text-green-300',   dot: 'bg-green-400'  },
    cancelado:      { key: 'status_cancelado',      bg: 'bg-red-100 dark:bg-red-900/40',       text: 'text-red-700 dark:text-red-300',       dot: 'bg-red-400'    },
    incidencia:     { key: 'status_cancelado', label: 'Incidencia', bg: 'bg-red-100 dark:bg-red-900/40', text: 'text-red-700 dark:text-red-300', dot: 'bg-red-400' },
};

const getEstadoLabel = (pedido) => {
    const cfg = estadoConfig[pedido.estado];
    if (!cfg) return pedido.estado;
    if (cfg.label) return cfg.label;
    return t('orders.' + cfg.key);
};
const getEstado = (e) => estadoConfig[e] ?? estadoConfig.pendiente;
const formatFecha = (dateStr) =>
    new Date(dateStr).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });

// -- Quick rating
const ratingOpen  = ref(null);
const hoverRating = ref(0);
const pendingReviewableIds = ref([...props.reviewableStoreIds]);

const ratingForm = useForm({
    puntuacion: 0,
    titulo:     '',
    comentario: '',
});

const openRating = (tiendaId) => {
    ratingOpen.value = tiendaId;
    hoverRating.value = 0;
    ratingForm.reset();
};
const closeRating = () => {
    ratingOpen.value = null;
    hoverRating.value = 0;
};

// -- Cancelar pedido
const cancelModal = ref(null); // pedido object o null
const cancelando  = ref(false);

const abrirCancelar = (pedido) => { cancelModal.value = pedido; };
const cerrarCancelar = () => { cancelModal.value = null; };

const confirmarCancelar = () => {
    if (!cancelModal.value || cancelando.value) return;
    cancelando.value = true;
    router.post(route('pedidos.cancelar', cancelModal.value.id), { tipo_reembolso: 'rusticoin' }, {
        preserveScroll: true,
        onSuccess: () => {
            toastSuccess('Pedido cancelado', 'El reembolso en RustiCoin ha sido procesado.');
            cerrarCancelar();
        },
        onError: () => {
            useToasts().error('Error', 'No se pudo cancelar el pedido.');
        },
        onFinish: () => { cancelando.value = false; },
    });
};

const submitRating = (tiendaId) => {
    ratingForm.post(route('resenas.store', tiendaId), {
        preserveScroll: true,
        onSuccess: () => {
            pendingReviewableIds.value = pendingReviewableIds.value.filter(id => id !== tiendaId);
            closeRating();
            toastSuccess('¡Gracias!', 'Tu valoración ha sido enviada.');
        },
    });
};
</script>

<template>
    <Head title="Mis pedidos" />

    <!-- Toasts via ToastContainer global -->

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">

        <NavbarPublico />

        <main class="mx-auto max-w-3xl px-4 pt-24 pb-10 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ t('orders.title') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('orders.subtitle') }}</p>
            </div>

            <!-- Tabs -->
            <div class="mb-6 flex gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                <button @click="tabActivo = 'activos'" :class="['flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200', tabActivo === 'activos' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                    <span :class="['flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-xs font-bold text-white', pedidosActivos.length > 0 ? 'bg-primary-500' : 'bg-gray-300']">{{ pedidosActivos.length }}</span>
                    {{ t('orders.active_tab') }}
                </button>
                <button @click="tabActivo = 'historial'" :class="['flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200', tabActivo === 'historial' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                    {{ t('orders.history_tab') }}
                    <span class="text-xs text-gray-400 dark:text-gray-500">({{ pedidosHistorial.total }})</span>
                </button>
            </div>

            <!-- TAB: Pedidos activos -->
            <div v-if="tabActivo === 'activos'">
                <div v-if="pedidosActivos.length === 0" class="flex flex-col items-center py-24 text-center">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ t('orders.no_active') }}</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('orders.no_active_sub') }}</p>
                    <Link href="/" class="mt-6 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">{{ t('orders.explore') }}</Link>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="pedido in pedidosActivos" :key="pedido.id" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition hover:shadow-md">
                        <button class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="toggleExpand(pedido.id)">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                    <span :class="['flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', getEstado(pedido.estado).bg, getEstado(pedido.estado).text]">
                                        <span :class="['inline-block h-1.5 w-1.5 rounded-full animate-pulse', getEstado(pedido.estado).dot]" />
                                        {{ getEstadoLabel(pedido) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatFecha(pedido.created_at) }} · {{ pedido.items_count }} producto{{ pedido.items_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-lg font-extrabold text-primary-600">{{ Number(pedido.total).toFixed(2) }}€</p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': expandidos.has(pedido.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition enter-active-class="transition-all duration-200" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
                            <div v-if="expandidos.has(pedido.id)" class="overflow-hidden border-t border-gray-100 dark:border-gray-700">
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div v-for="item in pedido.items" :key="item.id" class="flex flex-wrap items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3">
                                        <img :src="item.producto_imagen || '/images/logo.png'" :alt="item.producto_nombre" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover" />
                                        <div class="min-w-0 flex-1 text-sm">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ item.producto_nombre }}</p>
                                            <p class="text-gray-500 dark:text-gray-400">{{ item.tienda_nombre }}</p>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.cantidad }} ×</div>
                                        <div class="w-16 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ Number(item.subtotal).toFixed(2) }}€</div>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-6 py-4">
                                    <div class="flex flex-col gap-2 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-start gap-1.5">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>{{ pedido.direccion_envio }}</span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a :href="route('factura.show', pedido.id)" target="_blank"
                                               class="inline-flex items-center gap-1.5 font-medium text-orange-600 dark:text-orange-400 hover:underline whitespace-nowrap"><FileText class="h-3.5 w-3.5" /> {{ t('orders.view_invoice') }}</a>
                                            <span>{{ t('orders.subtotal') }} {{ Number(pedido.subtotal).toFixed(2) }}€ + {{ t('orders.shipping') }} {{ pedido.gastos_envio == 0 ? t('orders.free') : Number(pedido.gastos_envio).toFixed(2) + '€' }}</span>
                                            <button
                                                v-if="['pendiente', 'confirmado'].includes(pedido.estado)"
                                                @click.stop="abrirCancelar(pedido)"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-2.5 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 transition-colors hover:bg-red-100 dark:hover:bg-red-900/40"
                                            >
                                                Cancelar pedido
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- TAB: Historial -->
            <div v-if="tabActivo === 'historial'">
                <div v-if="pedidosHistorial.data.length === 0" class="flex flex-col items-center py-24 text-center">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ t('orders.no_history') }}</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('orders.no_history_sub') }}</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="pedido in pedidosHistorial.data" :key="pedido.id" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm opacity-90 transition hover:opacity-100 hover:shadow-md">
                        <button class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="toggleExpand('h-' + pedido.id)">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                    <span :class="['flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', getEstado(pedido.estado).bg, getEstado(pedido.estado).text]">
                                        <span :class="['inline-block h-1.5 w-1.5 rounded-full', getEstado(pedido.estado).dot]" />
                                        {{ getEstadoLabel(pedido) }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatFecha(pedido.created_at) }} · {{ pedido.items_count }} producto{{ pedido.items_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-lg font-extrabold text-gray-700 dark:text-gray-300">{{ Number(pedido.total).toFixed(2) }}€</p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': expandidos.has('h-' + pedido.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition enter-active-class="transition-all duration-200" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
                            <div v-if="expandidos.has('h-' + pedido.id)" class="overflow-hidden border-t border-gray-100 dark:border-gray-700">
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template v-for="item in pedido.items" :key="item.id">
                                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3">
                                            <img :src="item.producto_imagen || '/images/logo.png'" :alt="item.producto_nombre" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover" />
                                            <div class="min-w-0 flex-1 text-sm">
                                                <p class="font-medium text-gray-900 dark:text-white">{{ item.producto_nombre }}</p>
                                                <p class="text-gray-500 dark:text-gray-400">{{ item.tienda_nombre }}</p>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.cantidad }} ×</div>
                                            <div class="w-16 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ Number(item.subtotal).toFixed(2) }}€</div>
                                            <!-- Botón valorar tienda -->
                                            <button
                                                v-if="pedido.estado === 'entregado' && item.tienda_id && pendingReviewableIds.includes(item.tienda_id)"
                                                @click.stop="openRating(item.tienda_id)"
                                                class="flex items-center gap-1 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 px-2.5 py-1.5 text-xs font-semibold text-yellow-700 dark:text-yellow-300 transition-colors hover:bg-yellow-100 dark:hover:bg-yellow-900/50"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                {{ t('orders.rate') }}
                                            </button>
                                        </div>
                                        <!-- Formulario inline de valoración -->
                                        <div v-if="ratingOpen === item.tienda_id" class="border-t border-yellow-100 dark:border-yellow-900/30 bg-yellow-50/50 dark:bg-yellow-900/10 px-6 py-4">
                                            <p class="mb-3 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ t('orders.rate_store', { name: item.tienda_nombre }) }}</p>
                                            <div class="mb-3 flex gap-1">
                                                <button
                                                    v-for="n in 5" :key="n"
                                                    @click="ratingForm.puntuacion = n"
                                                    @mouseenter="hoverRating = n"
                                                    @mouseleave="hoverRating = 0"
                                                    type="button" class="focus:outline-none"
                                                >
                                                    <svg class="h-7 w-7 transition-colors" fill="currentColor" viewBox="0 0 20 20"
                                                        :class="n <= (hoverRating || ratingForm.puntuacion) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                                    >
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <input v-model="ratingForm.titulo" type="text" :placeholder="t('orders.rate_title_placeholder')" class="mb-2 w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" />
                                            <textarea v-model="ratingForm.comentario" rows="2" :placeholder="t('orders.rate_comment_placeholder')" class="mb-3 w-full resize-none rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" />
                                            <div class="flex gap-2">
                                                <button @click="submitRating(item.tienda_id)" :disabled="ratingForm.puntuacion === 0 || ratingForm.processing" class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed">{{ t('orders.submit_rating') }}</button>
                                                <button @click="closeRating" class="rounded-lg border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">{{ t('orders.cancel') }}</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-6 py-4">
                                    <div class="flex flex-col gap-1 text-xs text-gray-500 sm:flex-row sm:justify-between">
                                        <div class="flex items-start gap-1.5">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>{{ pedido.direccion_envio }}</span>
                                        </div>
                                        <div class="text-right">{{ t('orders.subtotal') }} {{ Number(pedido.subtotal).toFixed(2) }}€ + {{ t('orders.shipping') }} {{ pedido.gastos_envio == 0 ? t('orders.free') : Number(pedido.gastos_envio).toFixed(2) + '€' }}</div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Paginación historial -->
                <div v-if="pedidosHistorial.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                    <Link v-for="link in pedidosHistorial.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="['rounded-lg px-3 py-2 text-sm transition-colors', link.active ? 'bg-primary-500 font-bold text-white' : link.url ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' : 'cursor-not-allowed bg-white dark:bg-gray-800 text-gray-300 dark:text-gray-600 border border-gray-100 dark:border-gray-700']" />
                </div>
            </div>

        </main>
    </div>

    <!-- Modal cancelar pedido -->
    <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="cerrarCancelar">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Cancelar pedido</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                    Pedido <span class="font-semibold text-gray-700 dark:text-gray-300">{{ cancelModal.numero_pedido }}</span> por
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ Number(cancelModal.total).toFixed(2) }}€</span>
                </p>

                <div class="mt-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 text-sm">
                    <p class="font-semibold text-blue-800 dark:text-blue-300 mb-1">Reembolso en RustiCoin</p>
                    <p class="text-blue-700 dark:text-blue-400">El importe total se abonará inmediatamente en tu monedero RustiCoin y podrás usarlo en tu próxima compra.</p>
                </div>

                <div class="mt-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4 text-sm text-gray-600 dark:text-gray-400">
                    <p>¿Prefieres el reembolso a tu tarjeta? Escríbenos a
                        <a href="mailto:info@rustikan.com" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>
                        indicando el número de pedido.
                    </p>
                </div>

                <div class="mt-6 flex gap-3 justify-end">
                    <button @click="cerrarCancelar" class="rounded-lg border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                        Volver
                    </button>
                    <button @click="confirmarCancelar" :disabled="cancelando" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:opacity-50">
                        <svg v-if="cancelando" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        {{ cancelando ? 'Cancelando…' : 'Cancelar y recibir RustiCoin' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
