<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToasts } from '@/Composables/useToasts';
import { imgSrc } from '@/Composables/useImgSrc';
import {
    Truck, Search, X, CheckCircle2, Circle, PackageCheck,
    MapPin, Phone, StickyNote, Package, ChevronRight,
    AlertTriangle, Loader2, ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    pedidos: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
});

const { success: toastSuccess, error: toastError } = useToasts();

// ── Selección ────────────────────────────────────────────────────────────────
const seleccionados = ref(new Set());

const togglePedido = (id) => {
    const s = new Set(seleccionados.value);
    s.has(id) ? s.delete(id) : s.add(id);
    seleccionados.value = s;
};

const seleccionarTodos = () => {
    seleccionados.value = new Set(pedidosFiltrados.value.map(p => p.id));
};

const deseleccionarTodos = () => {
    seleccionados.value = new Set();
};

const todosSeleccionados = computed(() =>
    pedidosFiltrados.value.length > 0 &&
    pedidosFiltrados.value.every(p => seleccionados.value.has(p.id))
);

// ── Búsqueda local (sin server) ──────────────────────────────────────────────
const search = ref(props.filters.search ?? '');

const pedidosFiltrados = computed(() => {
    const q = search.value.toLowerCase();
    if (!q) return props.pedidos;
    return props.pedidos.filter(p =>
        p.numero_pedido.toLowerCase().includes(q) ||
        (p.user?.name ?? '').toLowerCase().includes(q)
    );
});

// Limpiar selección cuando cambia filtro
watch(search, () => {
    seleccionados.value = new Set();
});

// ── Drawer de detalle ─────────────────────────────────────────────────────────
const drawerPedido = ref(null);
const drawerOpen   = ref(false);

const abrirDrawer = (pedido) => {
    drawerPedido.value = pedido;
    drawerOpen.value   = true;
};
const cerrarDrawer = () => {
    drawerOpen.value   = false;
    drawerPedido.value = null;
};

// ── Dar salida ────────────────────────────────────────────────────────────────
const procesando   = ref(false);
const confirModal  = ref(false);

const pedidosADarSalida = computed(() =>
    props.pedidos.filter(p => seleccionados.value.has(p.id))
);

const abrirConfirm = () => {
    if (seleccionados.value.size === 0) return;
    confirModal.value = true;
};

const ejecutarSalida = () => {
    confirModal.value = false;
    procesando.value  = true;
    router.post(
        route('supplier.salidas.dar'),
        { pedido_ids: [...seleccionados.value] },
        {
            preserveScroll: true,
            onSuccess: () => {
                seleccionados.value = new Set();
                toastSuccess(
                    '¡Salida registrada!',
                    `${pedidosADarSalida.value.length === 1 ? 'El pedido ha' : 'Los pedidos han'} sido marcados como enviados.`
                );
            },
            onError: () => toastError('Error', 'No se pudo dar salida a los pedidos.'),
            onFinish: () => { procesando.value = false; },
        }
    );
};

// ── Helpers ──────────────────────────────────────────────────────────────────
const estadoBadgeClass = () =>
    'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300';

const estadoLabel = () => 'En preparación';

const formatFecha = (d) => new Date(d).toLocaleDateString('es-ES', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
});

const stockClass = (prod) => {
    if (!prod) return '';
    if (prod.stock === 0)                              return 'text-red-600 dark:text-red-400';
    if (prod.stock <= (prod.stock_minimo ?? 0))        return 'text-amber-600 dark:text-amber-400';
    return 'text-green-600 dark:text-green-400';
};
</script>

<template>
    <LayoutSupplier>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Cabecera -->
                <div class="flex items-center gap-3 flex-wrap">
                    <Link
                        :href="route('supplier.pedidos.index')"
                        class="flex items-center justify-center h-9 w-9 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Salida de pedidos</h1>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Selecciona los pedidos preparados para entregarlos al repartidor</p>
                    </div>
                </div>

                <!-- Banner selección -->
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="seleccionados.size > 0"
                        class="sticky top-4 z-10 flex items-center justify-between rounded-2xl bg-purple-600 shadow-lg px-5 py-3.5 text-white"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center h-8 w-8 rounded-full bg-white/20 font-bold text-sm">
                                {{ seleccionados.size }}
                            </div>
                            <span class="text-sm font-semibold">
                                {{ seleccionados.size === 1 ? 'pedido seleccionado' : 'pedidos seleccionados' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="deseleccionarTodos" class="rounded-lg px-3 py-1.5 text-xs font-medium bg-white/10 hover:bg-white/20 transition-colors">
                                Deseleccionar
                            </button>
                            <button
                                @click="abrirConfirm"
                                :disabled="procesando"
                                class="inline-flex items-center gap-2 rounded-lg bg-white text-purple-700 px-4 py-1.5 text-sm font-bold hover:bg-purple-50 transition-colors disabled:opacity-50"
                            >
                                <Loader2 v-if="procesando" class="h-4 w-4 animate-spin" />
                                <Truck v-else class="h-4 w-4" />
                                Dar salida
                            </button>
                        </div>
                    </div>
                </Transition>

                <!-- Buscador + seleccionar todos -->
                <div class="flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-48">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nº pedido o cliente…"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                        />
                    </div>
                    <button
                        v-if="!todosSeleccionados && pedidosFiltrados.length > 0"
                        @click="seleccionarTodos"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        Seleccionar todos
                    </button>
                    <button
                        v-else-if="todosSeleccionados"
                        @click="deseleccionarTodos"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        Deseleccionar todos
                    </button>
                </div>

                <!-- Empty state -->
                <div v-if="pedidosFiltrados.length === 0" class="rounded-2xl bg-white dark:bg-gray-800 shadow flex flex-col items-center py-20 text-center">
                    <PackageCheck class="mx-auto h-14 w-14 text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="font-semibold text-gray-700 dark:text-gray-300">No hay pedidos listos para salida</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Solo aparecen pedidos en estado <strong>En preparación</strong>.</p>
                    <Link
                        :href="route('supplier.pedidos.index')"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 transition-colors"
                    >
                        <ArrowLeft class="h-4 w-4" /> Ver pedidos entrantes
                    </Link>
                </div>

                <!-- Lista de pedidos -->
                <div v-else class="space-y-3">
                    <div
                        v-for="pedido in pedidosFiltrados"
                        :key="pedido.id"
                        :class="[
                            'rounded-2xl bg-white dark:bg-gray-800 shadow transition-all duration-150 border-2',
                            seleccionados.has(pedido.id)
                                ? 'border-purple-400 dark:border-purple-500 shadow-purple-100 dark:shadow-none'
                                : 'border-transparent hover:border-gray-200 dark:hover:border-gray-600'
                        ]"
                    >
                        <div class="flex items-center gap-4 px-5 py-4">
                            <!-- Checkbox -->
                            <button
                                @click="togglePedido(pedido.id)"
                                class="shrink-0 transition-colors"
                                :aria-label="seleccionados.has(pedido.id) ? 'Deseleccionar' : 'Seleccionar'"
                            >
                                <CheckCircle2
                                    v-if="seleccionados.has(pedido.id)"
                                    class="h-7 w-7 text-purple-600 dark:text-purple-400"
                                />
                                <Circle v-else class="h-7 w-7 text-gray-300 dark:text-gray-600" />
                            </button>

                            <!-- Info pedido -->
                            <div class="flex-1 min-w-0 cursor-pointer" @click="togglePedido(pedido.id)">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-mono font-bold text-gray-900 dark:text-white">#{{ pedido.numero_pedido }}</span>
                                    <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ estadoLabel(pedido.estado) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ pedido.user?.name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ formatFecha(pedido.created_at) }}</p>
                            </div>

                            <!-- Items preview -->
                            <div class="hidden sm:flex items-center gap-1 shrink-0">
                                <div
                                    v-for="(item, idx) in pedido.items.slice(0, 3)"
                                    :key="item.id"
                                    class="h-10 w-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-white dark:border-gray-800"
                                    :style="{ marginLeft: idx > 0 ? '-8px' : '0', zIndex: 3 - idx }"
                                >
                                    <img
                                        v-if="item.producto?.imagen"
                                        :src="imgSrc(item.producto?.imagen)"
                                        class="h-full w-full object-cover"
                                        @error="(e) => e.target.style.display='none'"
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center">
                                        <Package class="h-4 w-4 text-gray-400" />
                                    </div>
                                </div>
                                <span v-if="pedido.items.length > 3" class="text-xs text-gray-400 dark:text-gray-500 ml-2">+{{ pedido.items.length - 3 }}</span>
                            </div>

                            <!-- Total + ver -->
                            <div class="text-right shrink-0 space-y-1">
                                <p class="font-bold text-gray-900 dark:text-white">{{ pedido.total }} €</p>
                                <button
                                    @click.stop="abrirDrawer(pedido)"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-0.5"
                                >
                                    Ver detalle <ChevronRight class="h-3 w-3" />
                                </button>
                            </div>
                        </div>

                        <!-- Dirección compacta -->
                        <div class="flex items-center gap-2 px-5 pb-3 text-xs text-gray-400 dark:text-gray-500 border-t border-gray-100 dark:border-gray-700 pt-2">
                            <MapPin class="h-3 w-3 shrink-0" />
                            <span class="truncate">{{ pedido.direccion_envio }}</span>
                            <span v-if="pedido.telefono_contacto" class="ml-2 flex items-center gap-1 shrink-0">
                                <Phone class="h-3 w-3" /> {{ pedido.telefono_contacto }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Botón flotante dar salida (bottom) -->
                <div v-if="seleccionados.size > 0" class="fixed bottom-6 left-0 right-0 flex justify-center pointer-events-none z-20 px-4">
                    <button
                        @click="abrirConfirm"
                        :disabled="procesando"
                        class="pointer-events-auto inline-flex items-center gap-3 rounded-2xl bg-purple-600 hover:bg-purple-700 px-8 py-4 text-base font-bold text-white shadow-xl transition-colors disabled:opacity-50"
                    >
                        <Loader2 v-if="procesando" class="h-5 w-5 animate-spin" />
                        <Truck v-else class="h-5 w-5" />
                        Dar salida a {{ seleccionados.size }} {{ seleccionados.size === 1 ? 'pedido' : 'pedidos' }}
                    </button>
                </div>

            </div>
        </div>

        <!-- ── DRAWER DETALLE ─────────────────────────────────────────────────── -->
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
                    class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col bg-white dark:bg-gray-900 shadow-2xl"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4 shrink-0">
                        <div>
                            <p class="font-mono text-lg font-bold text-gray-900 dark:text-white">#{{ drawerPedido.numero_pedido }}</p>
                            <span :class="estadoBadgeClass(drawerPedido.estado)" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                {{ estadoLabel(drawerPedido.estado) }}
                            </span>
                        </div>
                        <button @click="cerrarDrawer" class="rounded-xl p-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                        <!-- Cliente + dirección -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Entrega</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ drawerPedido.user?.name ?? '—' }}</p>
                            <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <MapPin class="h-4 w-4 shrink-0 mt-0.5 text-gray-400" />
                                <span>{{ drawerPedido.direccion_envio }}</span>
                            </div>
                            <div v-if="drawerPedido.telefono_contacto" class="flex items-center gap-2 text-sm text-gray-500">
                                <Phone class="h-4 w-4 shrink-0 text-gray-400" /> {{ drawerPedido.telefono_contacto }}
                            </div>
                            <div v-if="drawerPedido.notas" class="flex items-start gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2 text-sm text-amber-700 dark:text-amber-300 mt-1">
                                <StickyNote class="h-4 w-4 shrink-0 mt-0.5" /> <span>{{ drawerPedido.notas }}</span>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Artículos</p>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-for="item in drawerPedido.items" :key="item.id" class="flex items-center gap-3 px-4 py-3">
                                    <div class="h-12 w-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                        <img
                                            v-if="item.producto?.imagen"
                                            :src="imgSrc(item.producto?.imagen)"
                                            class="h-full w-full object-cover"
                                            @error="(e) => e.target.style.display='none'"
                                        />
                                        <div v-else class="h-full w-full flex items-center justify-center">
                                            <Package class="h-5 w-5 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ item.producto?.nombre ?? item.producto_nombre }}</p>
                                        <p class="text-xs text-gray-400">x{{ item.cantidad }} {{ item.producto?.unidad ?? '' }} · {{ item.tienda?.nombre ?? item.tienda_nombre }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs text-gray-400">Stock actual</p>
                                        <p class="text-sm font-bold" :class="stockClass(item.producto)">
                                            {{ item.producto?.stock ?? '—' }} {{ item.producto?.unidad ?? '' }}
                                        </p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Total -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 px-4 py-3 flex justify-between font-bold text-gray-900 dark:text-white">
                            <span>Total</span><span>{{ drawerPedido.total }} €</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="shrink-0 border-t border-gray-200 dark:border-gray-700 px-6 py-4 space-y-3">
                        <button
                            @click="() => { togglePedido(drawerPedido.id); cerrarDrawer(); }"
                            :class="[
                                'flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition-colors',
                                seleccionados.has(drawerPedido.id)
                                    ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border-2 border-purple-400'
                                    : 'bg-purple-600 hover:bg-purple-700 text-white'
                            ]"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            {{ seleccionados.has(drawerPedido.id) ? 'Quitar de la selección' : 'Añadir a la selección' }}
                        </button>
                        <a
                            :href="route('supplier.exportar.pedido', drawerPedido.id)"
                            target="_blank"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-4 py-2.5 text-sm font-semibold text-orange-700 dark:text-orange-300 hover:bg-orange-100 transition-colors"
                        >
                            Hoja de preparación (PDF)
                        </a>
                    </div>
                </div>
            </Transition>

            <!-- Modal confirmación salida -->
            <Transition
                enter-active-class="transition-opacity duration-150"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4">
                    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl p-6 space-y-5">
                        <!-- Icon -->
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-purple-100 dark:bg-purple-900/30 p-3">
                                <Truck class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirmar salida</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ seleccionados.size }} {{ seleccionados.size === 1 ? 'pedido' : 'pedidos' }} serán marcados como <strong class="text-purple-600 dark:text-purple-400">Enviado</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Lista pedidos a dar salida -->
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 max-h-48 overflow-y-auto">
                            <div v-for="p in pedidosADarSalida" :key="p.id" class="flex items-center justify-between px-4 py-2.5">
                                <div>
                                    <p class="font-mono text-sm font-bold text-gray-900 dark:text-white">#{{ p.numero_pedido }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ p.user?.name ?? '—' }}</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ p.total }} €</span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            Se notificará a cada cliente por email y en su panel de pedidos que su pedido está en camino.
                        </p>

                        <div class="flex gap-3">
                            <button @click="confirModal = false"
                                class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button @click="ejecutarSalida"
                                class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-purple-600 hover:bg-purple-700 px-4 py-2.5 text-sm font-bold text-white transition-colors">
                                <Truck class="h-4 w-4" /> Dar salida
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </LayoutSupplier>
</template>
