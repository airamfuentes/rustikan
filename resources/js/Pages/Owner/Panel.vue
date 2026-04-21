<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import Toast from '@/Components/Toast.vue';
import { watch } from 'vue';

const props = defineProps({
    tienda:           { type: Object,  required: true },
    stats:            { type: Object,  required: true },
    ingresosGrafica:  { type: Array,   default: () => [] },
    topProductos:     { type: Array,   default: () => [] },
    pedidosRecientes: { type: Array,   default: () => [] },
    productos:        { type: Array,   default: () => [] },
});

const page = usePage();

// ── Toasts ─────────────────────────────────────────────────────────────────
const toasts = ref([]);
const addToast = (type, title, msg) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message: msg });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 4000);
};
watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast('success', 'Éxito', flash.success);
    if (flash?.error)   addToast('error',   'Error',  flash.error);
}, { deep: true });

// ── Imagen helper ───────────────────────────────────────────────────────────
const imgUrl = (path) => {
    if (!path) return null;
    return path.startsWith('http') ? path : `/storage/${path}`;
};

// ── Gráfica ─────────────────────────────────────────────────────────────────
const maxIngresos = computed(() => {
    const vals = props.ingresosGrafica.map(d => d.total);
    return Math.max(...vals, 1);
});

const barHeight = (total) => Math.max((total / maxIngresos.value) * 100, total > 0 ? 4 : 0);

// ── Tab activo ───────────────────────────────────────────────────────────────
const tab = ref('resumen');

// ── Estado label ─────────────────────────────────────────────────────────────
const estadoConfig = {
    pendiente:   { label: 'Pendiente',   cls: 'bg-yellow-100 text-yellow-800' },
    en_proceso:  { label: 'En proceso',  cls: 'bg-blue-100 text-blue-800' },
    completado:  { label: 'Completado',  cls: 'bg-green-100 text-green-800' },
    cancelado:   { label: 'Cancelado',   cls: 'bg-red-100 text-red-800' },
    confirmado:  { label: 'Confirmado',  cls: 'bg-blue-100 text-blue-800' },
    preparando:  { label: 'Preparando',  cls: 'bg-orange-100 text-orange-800' },
    en_camino:   { label: 'En camino',   cls: 'bg-purple-100 text-purple-800' },
    entregado:   { label: 'Entregado',   cls: 'bg-green-100 text-green-800' },
};
const getEstado = (e) => estadoConfig[e] ?? { label: e, cls: 'bg-gray-100 text-gray-700' };
</script>

<template>
    <Head :title="`Panel – ${tienda.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img v-if="imgUrl(tienda.logo)" :src="imgUrl(tienda.logo)"
                         class="h-10 w-10 rounded-full object-cover ring-2 ring-primary-200" alt="Logo" />
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ tienda.nombre }}</h2>
                        <p class="text-xs text-gray-500">Panel del propietario</p>
                    </div>
                </div>
                <Link :href="route('owner.tienda.edit')"
                      class="flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar tienda
                </Link>
            </div>
        </template>

        <!-- Toasts -->
        <div class="pointer-events-none fixed inset-0 z-[60] flex flex-col items-end justify-start space-y-4 p-6">
            <Toast v-for="t in toasts" :key="t.id" :type="t.type" :title="t.title" :message="t.message" @close="toasts = toasts.filter(x => x.id !== t.id)" />
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ── Tabs ──────────────────────────────────────────────────── -->
                <div class="flex gap-1 rounded-xl bg-white dark:bg-gray-800 p-1 shadow-sm border border-gray-100 dark:border-gray-700 w-fit">
                    <button v-for="t in [
                        { key:'resumen',  label:'Resumen' },
                        { key:'pedidos',  label:'Pedidos' },
                        { key:'productos',label:'Productos' },
                    ]" :key="t.key"
                        @click="tab = t.key"
                        :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            tab === t.key ? 'bg-primary-500 text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700']">
                        {{ t.label }}
                    </button>
                </div>

                <!-- ════════════════ TAB RESUMEN ════════════════ -->
                <template v-if="tab === 'resumen'">

                    <!-- Stat cards -->
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <!-- Ingresos totales -->
                        <div class="rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 p-5 text-white shadow-lg">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-medium opacity-90">Ingresos totales</p>
                                <div class="rounded-lg bg-white/20 p-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-3xl font-bold">{{ stats.totalIngresos.toFixed(2) }}€</p>
                            <p class="mt-1 text-xs opacity-75">De todos los pedidos</p>
                        </div>

                        <!-- Este mes -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Este mes</p>
                                <span :class="['text-xs font-bold px-2 py-0.5 rounded-full', stats.crecimiento >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                    {{ stats.crecimiento >= 0 ? '+' : '' }}{{ stats.crecimiento }}%
                                </span>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.ingresosMesActual.toFixed(2) }}€</p>
                            <p class="mt-1 text-xs text-gray-400">vs mes anterior</p>
                        </div>

                        <!-- Pedidos -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pedidos</p>
                                <div class="rounded-lg bg-blue-100 p-2">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.totalPedidos }}</p>
                            <p class="mt-1 text-xs text-gray-400">Total histórico</p>
                        </div>

                        <!-- Valoración -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Valoración</p>
                                <div class="rounded-lg bg-yellow-100 p-2">
                                    <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ Number(stats.valoracion).toFixed(1) }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ stats.totalResenas }} reseñas</p>
                        </div>
                    </div>

                    <!-- Gráfica ingresos + Top productos -->
                    <div class="grid gap-6 lg:grid-cols-3">

                        <!-- Gráfica -->
                        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                            <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-white">Ingresos últimos 30 días</h3>
                            <p class="mb-6 text-xs text-gray-400">Cada barra representa un día</p>

                            <!-- Barras -->
                            <div class="flex items-end gap-0.5 h-40">
                                <div v-for="(day, i) in ingresosGrafica" :key="i"
                                     class="flex-1 flex flex-col items-center gap-1 group">
                                    <div class="relative flex w-full justify-center">
                                        <!-- Tooltip -->
                                        <span class="absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-900 px-2 py-0.5 text-xs text-white opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                            {{ day.fecha }}: {{ day.total.toFixed(2) }}€
                                        </span>
                                        <div
                                            :style="`height: ${barHeight(day.total)}%`"
                                            :class="['w-full rounded-t-sm transition-all duration-300 min-h-[2px]', day.total > 0 ? 'bg-primary-400 hover:bg-primary-500' : 'bg-gray-100 dark:bg-gray-700']"
                                            style="max-height: 100%;"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Eje X labels (cada 5 días) -->
                            <div class="mt-2 flex justify-between text-[10px] text-gray-400 px-1">
                                <span v-for="(day, i) in ingresosGrafica.filter((_, i) => i % 5 === 0)" :key="i">{{ day.fecha }}</span>
                            </div>
                        </div>

                        <!-- Top productos -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Top productos</h3>
                            <div v-if="topProductos.length === 0" class="text-center py-8 text-sm text-gray-400">
                                Sin ventas registradas
                            </div>
                            <ul v-else class="space-y-3">
                                <li v-for="(prod, i) in topProductos" :key="prod.producto_id"
                                    class="flex items-center gap-3">
                                    <span :class="['flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                        i === 0 ? 'bg-yellow-100 text-yellow-700' :
                                        i === 1 ? 'bg-gray-100 text-gray-600' :
                                        i === 2 ? 'bg-amber-100 text-amber-700' : 'bg-gray-50 text-gray-500']">
                                        {{ i + 1 }}
                                    </span>
                                    <img v-if="imgUrl(prod.imagen)" :src="imgUrl(prod.imagen)"
                                         class="h-8 w-8 rounded-lg object-cover" alt="" />
                                    <div v-else class="h-8 w-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ prod.producto_nombre }}</p>
                                        <p class="text-xs text-gray-400">{{ prod.total_vendidos }} uds · {{ Number(prod.total_ingresos).toFixed(2) }}€</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Info tienda + productos rápidos -->
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Info tienda -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                            <!-- Portada -->
                            <div class="relative h-32 bg-gradient-to-br from-primary-400 to-primary-600">
                                <img v-if="imgUrl(tienda.imagen_portada)" :src="imgUrl(tienda.imagen_portada)"
                                     class="absolute inset-0 h-full w-full object-cover" alt="Portada" />
                                <div class="absolute inset-0 bg-black/20"></div>
                                <div class="absolute bottom-0 left-0 p-4">
                                    <img v-if="imgUrl(tienda.logo)" :src="imgUrl(tienda.logo)"
                                         class="h-12 w-12 rounded-full object-cover ring-2 ring-white" alt="Logo" />
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ tienda.nombre }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ tienda.categoria?.nombre }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        {{ tienda.activa ? 'Activa' : 'Inactiva' }}
                                    </div>
                                </div>
                                <p v-if="tienda.descripcion" class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ tienda.descripcion }}</p>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div v-if="tienda.telefono" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ tienda.telefono }}
                                    </div>
                                    <div v-if="tienda.direccion" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ tienda.direccion }}
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <Link :href="route('owner.tienda.edit')"
                                          class="flex-1 rounded-xl bg-primary-50 dark:bg-primary-900/30 py-2 text-center text-sm font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                        Editar tienda
                                    </Link>
                                    <Link :href="`/tienda/${tienda.slug}`"
                                          class="flex-1 rounded-xl bg-gray-50 dark:bg-gray-700 py-2 text-center text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        Ver en web
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Estrellas de valoración -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Valoración de clientes</h3>
                            <!-- Nota grande -->
                            <div class="flex items-center gap-4 mb-6">
                                <span class="text-6xl font-extrabold text-gray-900 dark:text-white">{{ Number(stats.valoracion).toFixed(1) }}</span>
                                <div>
                                    <div class="flex gap-1">
                                        <svg v-for="i in 5" :key="i"
                                             :class="['h-6 w-6', i <= Math.round(stats.valoracion) ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-600']"
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">{{ stats.totalResenas }} reseñas totales</p>
                                </div>
                            </div>
                            <!-- Barra por estrellas (visual únicamente) -->
                            <div class="space-y-2">
                                <div v-for="n in [5,4,3,2,1]" :key="n" class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 w-2">{{ n }}</span>
                                    <svg class="h-3.5 w-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-700 h-2 overflow-hidden">
                                        <div :style="`width: ${n === Math.round(stats.valoracion) && stats.totalResenas > 0 ? 70 : n === Math.floor(stats.valoracion) ? 20 : 5}%`"
                                             class="h-full rounded-full bg-yellow-400 transition-all duration-500"></div>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-4 text-xs text-gray-400 italic">Las reseñas son gestionadas por el sistema automáticamente.</p>
                        </div>
                    </div>

                </template>

                <!-- ════════════════ TAB PEDIDOS ════════════════ -->
                <template v-if="tab === 'pedidos'">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pedidos recientes de tu tienda</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Mostrando los últimos 8 pedidos que incluyen productos de {{ tienda.nombre }}</p>
                        </div>
                        <div v-if="pedidosRecientes.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Aún no hay pedidos
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pedido</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Importe</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Artículos</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <tr v-for="p in pedidosRecientes" :key="p.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-4 text-sm font-mono font-medium text-primary-600 dark:text-primary-400">{{ p.numero_pedido }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ p.cliente }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', getEstado(p.estado).cls]">
                                                {{ getEstado(p.estado).label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ Number(p.total_tienda).toFixed(2) }}€</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ p.items_count }} art.</td>
                                        <td class="px-6 py-4 text-xs text-gray-400">{{ p.created_at }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

                <!-- ════════════════ TAB PRODUCTOS ════════════════ -->
                <template v-if="tab === 'productos'">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Productos de tu tienda</h3>
                                <p class="text-xs text-gray-400 mt-0.5">{{ stats.totalProductos }} total · {{ stats.productosDisponibles }} disponibles</p>
                            </div>

                        </div>
                        <div v-if="productos.length === 0" class="py-16 text-center text-gray-400">
                            Sin productos registrados
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Precio</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Destacado</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <tr v-for="p in productos" :key="p.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-3">
                                                <img v-if="imgUrl(p.imagen)" :src="imgUrl(p.imagen)"
                                                     class="h-9 w-9 rounded-lg object-cover" alt="" />
                                                <div v-else class="h-9 w-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ p.nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ Number(p.precio).toFixed(2) }}€</span>
                                            <span v-if="p.precio_oferta" class="ml-2 text-xs text-red-500 line-through">{{ Number(p.precio_oferta).toFixed(2) }}€</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ p.stock }}</td>
                                        <td class="px-6 py-3">
                                            <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                p.disponible ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                                {{ p.disponible ? 'Disponible' : 'No disponible' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3">
                                            <span v-if="p.destacado" class="inline-flex items-center gap-1 text-xs text-yellow-600 font-medium">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                                Destacado
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right">
                                            <Link :href="route('owner.producto.edit', p.id)"
                                                  class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700 dark:hover:bg-primary-900/20 transition-colors">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
