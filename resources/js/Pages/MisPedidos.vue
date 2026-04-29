<script setup>
import { ref, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import NavbarPublico from '@/Components/NavbarPublico.vue';

const props = defineProps({
    pedidosActivos:   { type: Array,  required: true },
    pedidosHistorial: { type: Object, required: true },
});

// -- Toast
const toasts = ref([]);
const addToast = (type, title, message = '') => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message });
    setTimeout(() => removeToast(id), 5000);
};
const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};
const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) addToast('success', '��xito!',     flash.success);
        if (flash.error)   addToast('error',   'Error',       flash.error);
        if (flash.info)    addToast('info',     'Informaci�n', flash.info);
        if (flash.warning) addToast('warning',  'Aviso',       flash.warning);
    },
    { deep: true, immediate: true },
);

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

// -- Estado config
const estadoConfig = {
    pendiente:  { label: 'Pendiente',  bg: 'bg-yellow-100 dark:bg-yellow-900/40', text: 'text-yellow-700 dark:text-yellow-300', dot: 'bg-yellow-400' },
    confirmado: { label: 'Confirmado', bg: 'bg-blue-100 dark:bg-blue-900/40',   text: 'text-blue-700 dark:text-blue-300',   dot: 'bg-blue-400'   },
    preparando: { label: 'Preparando', bg: 'bg-indigo-100 dark:bg-indigo-900/40', text: 'text-indigo-700 dark:text-indigo-300', dot: 'bg-indigo-400' },
    en_camino:  { label: 'En camino',  bg: 'bg-purple-100 dark:bg-purple-900/40', text: 'text-purple-700 dark:text-purple-300', dot: 'bg-purple-400' },
    entregado:  { label: 'Entregado',  bg: 'bg-green-100 dark:bg-green-900/40',  text: 'text-green-700 dark:text-green-300',  dot: 'bg-green-400'  },
    cancelado:  { label: 'Cancelado',  bg: 'bg-red-100 dark:bg-red-900/40',    text: 'text-red-700 dark:text-red-300',    dot: 'bg-red-400'    },
};
const getEstado = (e) => estadoConfig[e] ?? estadoConfig.pendiente;
const formatFecha = (dateStr) =>
    new Date(dateStr).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Mis pedidos � Rustikan" />

    <!-- Toasts -->
    <div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-start gap-3 p-6">
        <Toast v-for="toast in toasts" :key="toast.id" :type="toast.type" :title="toast.title" :message="toast.message" @close="removeToast(toast.id)" />
    </div>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">

        <NavbarPublico />

        <main class="mx-auto max-w-3xl px-4 pt-24 pb-10 sm:px-6 lg:px-8">

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Mis pedidos</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Consulta el estado de tus pedidos y tu historial de compras</p>
            </div>

            <!-- Tabs -->
            <div class="mb-6 flex gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                <button @click="tabActivo = 'activos'" :class="['flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200', tabActivo === 'activos' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                    <span :class="['flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1.5 text-xs font-bold text-white', pedidosActivos.length > 0 ? 'bg-primary-500' : 'bg-gray-300']">{{ pedidosActivos.length }}</span>
                    Pedidos activos
                </button>
                <button @click="tabActivo = 'historial'" :class="['flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200', tabActivo === 'historial' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300']">
                    Historial
                    <span class="text-xs text-gray-400 dark:text-gray-500">({{ pedidosHistorial.total }})</span>
                </button>
            </div>

            <!-- -- TAB: Pedidos activos -->
            <div v-if="tabActivo === 'activos'">
                <div v-if="pedidosActivos.length === 0" class="flex flex-col items-center py-24 text-center">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">No tienes pedidos activos</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">�Explora las tiendas y realiza tu pr�ximo pedido!</p>
                    <Link href="/" class="mt-6 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600">Explorar tiendas</Link>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="pedido in pedidosActivos" :key="pedido.id" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm transition hover:shadow-md">
                        <button class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="toggleExpand(pedido.id)">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                    <span :class="['flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', getEstado(pedido.estado).bg, getEstado(pedido.estado).text]">
                                        <span :class="['inline-block h-1.5 w-1.5 rounded-full animate-pulse', getEstado(pedido.estado).dot]" />
                                        {{ getEstado(pedido.estado).label }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatFecha(pedido.created_at) }} � {{ pedido.items_count }} producto{{ pedido.items_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-lg font-extrabold text-primary-600">{{ Number(pedido.total).toFixed(2) }}�</p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': expandidos.has(pedido.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition enter-active-class="transition-all duration-200" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
                            <div v-if="expandidos.has(pedido.id)" class="overflow-hidden border-t border-gray-100 dark:border-gray-700">
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div v-for="item in pedido.items" :key="item.id" class="flex items-center gap-4 px-6 py-3">
                                        <img :src="item.producto_imagen || '/images/logo.png'" :alt="item.producto_nombre" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover" />
                                        <div class="min-w-0 flex-1 text-sm">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ item.producto_nombre }}</p>
                                            <p class="text-gray-500 dark:text-gray-400">{{ item.tienda_nombre }}</p>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.cantidad }} �</div>
                                        <div class="w-16 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ Number(item.subtotal).toFixed(2) }}�</div>
                                    </div>
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
                                        <div class="text-right">Subtotal {{ Number(pedido.subtotal).toFixed(2) }}� + Env�o {{ pedido.gastos_envio == 0 ? 'GRATIS' : Number(pedido.gastos_envio).toFixed(2) + '�' }}</div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- -- TAB: Historial -->
            <div v-if="tabActivo === 'historial'">
                <div v-if="pedidosHistorial.data.length === 0" class="flex flex-col items-center py-24 text-center">
                    <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Sin historial a�n</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Aqu� ver�s los pedidos entregados y cancelados.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="pedido in pedidosHistorial.data" :key="pedido.id" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm opacity-90 transition hover:opacity-100 hover:shadow-md">
                        <button class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50" @click="toggleExpand('h-' + pedido.id)">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                    <span :class="['flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold', getEstado(pedido.estado).bg, getEstado(pedido.estado).text]">
                                        <span :class="['inline-block h-1.5 w-1.5 rounded-full', getEstado(pedido.estado).dot]" />
                                        {{ getEstado(pedido.estado).label }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatFecha(pedido.created_at) }} � {{ pedido.items_count }} producto{{ pedido.items_count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-lg font-extrabold text-gray-700 dark:text-gray-300">{{ Number(pedido.total).toFixed(2) }}�</p>
                            </div>
                            <svg class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': expandidos.has('h-' + pedido.id) }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition enter-active-class="transition-all duration-200" enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100" leave-active-class="transition-all duration-200" leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
                            <div v-if="expandidos.has('h-' + pedido.id)" class="overflow-hidden border-t border-gray-100 dark:border-gray-700">
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div v-for="item in pedido.items" :key="item.id" class="flex items-center gap-4 px-6 py-3">
                                        <img :src="item.producto_imagen || '/images/logo.png'" :alt="item.producto_nombre" class="h-12 w-12 flex-shrink-0 rounded-lg object-cover" />
                                        <div class="min-w-0 flex-1 text-sm">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ item.producto_nombre }}</p>
                                            <p class="text-gray-500 dark:text-gray-400">{{ item.tienda_nombre }}</p>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.cantidad }} �</div>
                                        <div class="w-16 text-right text-sm font-semibold text-gray-800 dark:text-gray-200">{{ Number(item.subtotal).toFixed(2) }}�</div>
                                    </div>
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
                                        <div class="text-right">Subtotal {{ Number(pedido.subtotal).toFixed(2) }}� + Env�o {{ pedido.gastos_envio == 0 ? 'GRATIS' : Number(pedido.gastos_envio).toFixed(2) + '�' }}</div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Paginaci�n historial -->
                <div v-if="pedidosHistorial.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                    <Link v-for="link in pedidosHistorial.links" :key="link.label" :href="link.url || '#'" v-html="link.label" :class="['rounded-lg px-3 py-2 text-sm transition-colors', link.active ? 'bg-primary-500 font-bold text-white' : link.url ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' : 'cursor-not-allowed bg-white dark:bg-gray-800 text-gray-300 dark:text-gray-600 border border-gray-100 dark:border-gray-700']" />
                </div>
            </div>

        </main>
    </div>
</template>
