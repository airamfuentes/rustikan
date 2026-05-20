<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useToasts } from '@/Composables/useToasts';
import { imgSrc } from '@/Composables/useImgSrc';
import {
    ChevronLeft, ChevronRight, FileText, X, Package,
    MapPin, Phone, StickyNote, AlertTriangle, CheckCircle2,
    Clock, Truck, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    pedidos: { type: Object, required: true },
    stats:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { success: toastSuccess, error: toastError } = useToasts();

const form = ref({
    search: props.filters.search ?? '',
    estado: props.filters.estado ?? '',
});

// ── Drawer ──────────────────────────────────────────────────────────────────
const drawerPedido  = ref(null);
const drawerOpen    = ref(false);
const procesando    = ref(false);
const incModal      = ref(false);
const motivoInc     = ref('');

const abrirDrawer = (pedido) => {
    drawerPedido.value = pedido;
    drawerOpen.value   = true;
};
const cerrarDrawer = () => {
    drawerOpen.value  = false;
    drawerPedido.value = null;
};

const confirmarPedido = () => {
    if (!drawerPedido.value || procesando.value) return;
    procesando.value = true;
    router.post(
        route('supplier.pedidos.estado', drawerPedido.value.id),
        { estado: 'confirmado' },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastSuccess('Pedido confirmado', 'El pedido pasa a "En preparación" automáticamente.');
                cerrarDrawer();
            },
            onError: () => toastError('Error', 'No se pudo confirmar el pedido.'),
            onFinish: () => { procesando.value = false; },
        }
    );
};

const abrirIncidencia = () => {
    motivoInc.value = '';
    incModal.value  = true;
};

const confirmarIncidencia = () => {
    if (!motivoInc.value.trim() || !drawerPedido.value) return;
    incModal.value  = false;
    procesando.value = true;
    router.post(
        route('supplier.pedidos.estado', drawerPedido.value.id),
        { estado: 'incidencia', motivo_incidencia: motivoInc.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                toastSuccess('Incidencia registrada', 'Quedará notificado el administrador.');
                cerrarDrawer();
            },
            onError: () => toastError('Error', 'No se pudo registrar la incidencia.'),
            onFinish: () => { procesando.value = false; },
        }
    );
};

// ── Filtros ──────────────────────────────────────────────────────────────────
const pulsing     = ref(false);
let pollInterval  = null;

const poll = () => {
    pulsing.value = true;
    router.reload({ only: ['pedidos', 'stats'], onFinish: () => { pulsing.value = false; } });
};

onMounted(() => { pollInterval = setInterval(poll, 5000); });
onUnmounted(() => { clearInterval(pollInterval); });

const statsConfig = [
    { key: 'pendientes',     value: 'pendiente',      label: 'Pendientes',     color: 'text-yellow-500' },
    { key: 'en_preparacion', value: 'en_preparacion', label: 'En preparación', color: 'text-orange-500' },
    { key: 'confirmados',    value: 'confirmado',     label: 'Confirmados',    color: 'text-blue-500' },
    { key: 'enviados',       value: 'enviado',        label: 'Enviados',       color: 'text-purple-500' },
    { key: 'incidencias',    value: 'incidencia',     label: 'Incidencias',    color: 'text-red-500' },
];

const estadosFiltro = [
    { value: 'pendiente',      label: 'Pendiente' },
    { value: 'en_preparacion', label: 'En preparación' },
    { value: 'confirmado',     label: 'Confirmado' },
    { value: 'enviado',        label: 'Enviado' },
    { value: 'incidencia',     label: 'Incidencia' },
    { value: 'entregado',      label: 'Entregado' },
];

const buscar = () => {
    router.get(route('supplier.pedidos.index'), { search: form.value.search, estado: form.value.estado }, { preserveState: true, preserveScroll: true });
};

let searchDebounce = null;
watch(() => form.value.search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(buscar, 300);
});

const urlExportar = computed(() => {
    const base = route('supplier.exportar.pedidos');
    return form.value.estado ? `${base}?estado=${encodeURIComponent(form.value.estado)}` : base;
});

const filtrarPor = (value) => {
    form.value.estado = form.value.estado === value ? '' : value;
    buscar();
};

const limpiar = () => {
    form.value.search = '';
    form.value.estado = '';
    buscar();
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const estadoBadgeClass = (estado) => ({
    pendiente:      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    en_preparacion: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
    confirmado:     'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
    enviado:        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
    entregado:      'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    cancelado:      'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    incidencia:     'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
}[estado] ?? 'bg-gray-100 text-gray-800');

const estadoLabel = (estado) => ({
    pendiente:      'Pendiente',
    en_preparacion: 'En preparación',
    confirmado:     'Confirmado',
    enviado:        'Enviado',
    entregado:      'Entregado',
    cancelado:      'Cancelado',
    incidencia:     'Incidencia',
}[estado] ?? estado.replace('_', ' '));

const stockClass = (producto) => {
    if (!producto) return '';
    if (producto.stock === 0)                                  return 'text-red-600 dark:text-red-400';
    if (producto.stock <= (producto.stock_minimo ?? 0))        return 'text-amber-600 dark:text-amber-400';
    return 'text-green-600 dark:text-green-400';
};

const formatFecha = (d) => new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });

// Pedido confirmable = pendiente o confirmado (antes de en_preparacion)
const puedeConfirmar = (estado) => ['pendiente', 'confirmado'].includes(estado);
</script>

<template>
    <LayoutSupplier>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Cabecera -->
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pedidos entrantes</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona y actualiza el estado de los pedidos</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            :href="route('supplier.salidas.index')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 px-3 py-2 text-sm font-semibold text-white transition-colors"
                        >
                            <Truck class="h-4 w-4" /> Dar salida
                        </Link>
                        <a :href="urlExportar" target="_blank"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-2 text-sm font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                            <FileText class="h-4 w-4" /> Exportar PDF
                        </a>
                        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                            <span :class="pulsing ? 'text-green-500' : 'text-gray-400'" class="transition-colors">●</span>
                            <span>Actualiza cada 5s</span>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                    <div v-for="stat in statsConfig" :key="stat.key"
                        class="rounded-2xl bg-white dark:bg-gray-800 shadow px-5 py-4 flex flex-col gap-1 cursor-pointer transition-all hover:-translate-y-0.5"
                        :class="form.estado === stat.value ? 'ring-2 ring-primary-500' : ''"
                        @click="filtrarPor(stat.value)">
                        <span class="text-2xl font-bold" :class="stat.color">{{ stats[stat.key] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ stat.label }}</span>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-48">
                        <input v-model="form.search" type="text"
                            placeholder="Buscar por nº pedido o cliente..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <select v-model="form.estado" @change="buscar"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Todos los estados</option>
                        <option v-for="e in estadosFiltro" :key="e.value" :value="e.value">{{ e.label }}</option>
                    </select>
                    <button v-if="form.estado || form.search" @click="limpiar"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Limpiar
                    </button>
                </div>

                <!-- Tabla pedidos -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow">
                    <div class="overflow-x-auto rounded-t-2xl" style="-webkit-overflow-scrolling: touch;">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pedido</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-if="pedidos.data.length === 0">
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No hay pedidos que mostrar
                                    </td>
                                </tr>
                                <tr v-for="pedido in pedidos.data" :key="pedido.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
                                    @click="abrirDrawer(pedido)">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">#{{ pedido.numero_pedido }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ pedido.user?.name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ pedido.items?.length ?? 0 }} artículo(s)
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ pedido.total }} €
                                    </td>
                                    <td class="px-6 py-4">
                                        <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                            {{ estadoLabel(pedido.estado) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatFecha(pedido.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-right" @click.stop>
                                        <button
                                            @click="abrirDrawer(pedido)"
                                            class="text-primary-600 hover:text-primary-800 dark:text-primary-400 text-sm font-medium"
                                        >
                                            Ver detalle
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="pedidos.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-6 py-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ pedidos.from }}–{{ pedidos.to }} de {{ pedidos.total }} pedidos
                        </p>
                        <div class="flex gap-2">
                            <Link v-if="pedidos.prev_page_url" :href="pedidos.prev_page_url"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                                <ChevronLeft class="h-4 w-4" /> Anterior
                            </Link>
                            <Link v-if="pedidos.next_page_url" :href="pedidos.next_page_url"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 inline-flex items-center gap-1">
                                Siguiente <ChevronRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── DRAWER ─────────────────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="drawerOpen" class="fixed inset-0 z-40 bg-black/50" @click="cerrarDrawer" />
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300"
                enter-from-class="translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300"
                leave-from-class="translate-x-0"
                leave-to-class="translate-x-full"
            >
                <div
                    v-if="drawerOpen && drawerPedido"
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-lg flex-col bg-white dark:bg-gray-900 shadow-2xl"
                >
                    <!-- Header drawer -->
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4 shrink-0">
                        <div>
                            <p class="font-mono text-lg font-bold text-gray-900 dark:text-white">#{{ drawerPedido.numero_pedido }}</p>
                            <span :class="estadoBadgeClass(drawerPedido.estado)" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                {{ estadoLabel(drawerPedido.estado) }}
                            </span>
                        </div>
                        <button @click="cerrarDrawer" class="rounded-xl p-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Cuerpo scrollable -->
                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                        <!-- Cliente -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Cliente</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ drawerPedido.user?.name ?? '—' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ drawerPedido.user?.email ?? '' }}</p>
                        </div>

                        <!-- Dirección -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Envío</p>
                            <div class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <MapPin class="h-4 w-4 shrink-0 mt-0.5 text-gray-400" />
                                <span>{{ drawerPedido.direccion_envio }}</span>
                            </div>
                            <div v-if="drawerPedido.telefono_contacto" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <Phone class="h-4 w-4 shrink-0 text-gray-400" />
                                <span>{{ drawerPedido.telefono_contacto }}</span>
                            </div>
                            <div v-if="drawerPedido.notas" class="flex items-start gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2 text-sm text-amber-700 dark:text-amber-300 mt-2">
                                <StickyNote class="h-4 w-4 shrink-0 mt-0.5" />
                                <span>{{ drawerPedido.notas }}</span>
                            </div>
                        </div>

                        <!-- Productos + stock -->
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Productos del pedido</p>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-for="item in drawerPedido.items" :key="item.id" class="flex items-center gap-3 px-4 py-3">
                                    <div class="h-12 w-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                        <img
                                            v-if="item.producto?.imagen"
                                            :src="imgSrc(item.producto?.imagen)"
                                            :alt="item.producto?.nombre"
                                            class="h-full w-full object-cover"
                                            @error="(e) => e.target.style.display='none'"
                                        />
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <Package class="h-5 w-5 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ item.producto?.nombre ?? item.producto_nombre }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.tienda?.nombre ?? item.tienda_nombre }} · x{{ item.cantidad }}</p>
                                    </div>
                                    <!-- Stock actual -->
                                    <div class="text-right shrink-0">
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Stock actual</p>
                                        <p class="text-sm font-bold" :class="stockClass(item.producto)">
                                            {{ item.producto?.stock ?? '—' }} {{ item.producto?.unidad ?? '' }}
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Totales -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 px-4 py-3 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Subtotal</span><span>{{ drawerPedido.subtotal }} €</span>
                            </div>
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Envío</span><span>{{ drawerPedido.gastos_envio }} €</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 dark:text-white border-t border-gray-200 dark:border-gray-700 pt-2">
                                <span>Total</span><span>{{ drawerPedido.total }} €</span>
                            </div>
                        </div>

                        <!-- Incidencia activa -->
                        <div v-if="drawerPedido.estado === 'incidencia' && drawerPedido.motivo_incidencia"
                            class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <AlertTriangle class="h-4 w-4 text-red-500" />
                                <p class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider">Incidencia</p>
                            </div>
                            <p class="text-sm text-red-700 dark:text-red-300">{{ drawerPedido.motivo_incidencia }}</p>
                        </div>
                    </div>

                    <!-- Footer acciones -->
                    <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-6 py-4 space-y-3">
                        <!-- Descargar factura -->
                        <a
                            :href="route('supplier.exportar.pedido', drawerPedido.id)"
                            target="_blank"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-4 py-2.5 text-sm font-semibold text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors"
                        >
                            <FileText class="h-4 w-4" /> Hoja de preparación (PDF)
                        </a>

                        <!-- Confirmar pedido -->
                        <button
                            v-if="puedeConfirmar(drawerPedido.estado)"
                            @click="confirmarPedido"
                            :disabled="procesando"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 hover:bg-green-700 px-4 py-3 text-sm font-bold text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <Loader2 v-if="procesando" class="h-4 w-4 animate-spin" />
                            <CheckCircle2 v-else class="h-4 w-4" />
                            {{ procesando ? 'Procesando…' : 'Confirmar pedido → En preparación' }}
                        </button>

                        <!-- Botón incidencia -->
                        <button
                            v-if="!['enviado', 'entregado', 'cancelado', 'incidencia'].includes(drawerPedido.estado)"
                            @click="abrirIncidencia"
                            :disabled="procesando"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-2.5 text-sm font-semibold text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors disabled:opacity-50"
                        >
                            <AlertTriangle class="h-4 w-4" /> Registrar incidencia
                        </button>

                        <!-- Ver detalle completo -->
                        <Link
                            :href="route('supplier.pedidos.show', drawerPedido.id)"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            Ver detalle completo
                        </Link>
                    </div>
                </div>
            </Transition>

            <!-- Modal incidencia -->
            <Transition
                enter-active-class="transition-opacity duration-150"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="incModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4">
                    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-red-100 dark:bg-red-900/30 p-2">
                                <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar incidencia</h3>
                        </div>
                        <textarea
                            v-model="motivoInc"
                            rows="4"
                            maxlength="500"
                            placeholder="Describe el motivo de la incidencia..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                        />
                        <p class="text-right text-xs text-gray-400">{{ motivoInc.length }}/500</p>
                        <div class="flex gap-3 justify-end">
                            <button @click="incModal = false"
                                class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button @click="confirmarIncidencia"
                                :disabled="!motivoInc.trim()"
                                class="rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                Confirmar incidencia
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </LayoutSupplier>
</template>
