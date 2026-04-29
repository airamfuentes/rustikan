<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import Toast from '@/Components/Toast.vue';
import { ArrowLeft } from 'lucide-vue-next';

const props = defineProps({
    tiendas: { type: Array, default: () => [] },
    estado:  { type: String, default: 'pendiente' },
    counts:  { type: Object, default: () => ({}) },
});

const page = usePage();

// ── Toasts ─────────────────────────────────────────────────────────────────
const toasts = ref([]);
const addToast = (type, title, msg) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message: msg });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 4500);
};
watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast('success', 'Listo', flash.success);
    if (flash?.error)   addToast('error',   'Error',  flash.error);
    if (flash?.info)    addToast('info',    'Info',   flash.info);
}, { deep: true });

// ── Filtro por estado ─────────────────────────────────────────────────────
const setEstado = (e) => router.get(route('admin.solicitudes.index'), { estado: e }, { preserveState: true });

const estadoTabCls = (e) => e === props.estado
    ? 'bg-primary-500 text-white shadow'
    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700';

// ── Imagen helper ────────────────────────────────────────────────────────
const imgUrl = (path) => {
    if (!path) return null;
    return path.startsWith('http') ? path : `/storage/${path}`;
};

// ── Rechazo modal ─────────────────────────────────────────────────────────
const rechazando = ref(null); // { solicitud } | null
const rechazandoTodas = ref(null); // tienda | null
const motivoRechazo = ref('');

const abrirRechazar = (solicitud) => {
    rechazando.value = solicitud;
    rechazandoTodas.value = null;
    motivoRechazo.value = '';
};
const abrirRechazarTodas = (tienda) => {
    rechazandoTodas.value = tienda;
    rechazando.value = null;
    motivoRechazo.value = '';
};
const cerrarModal = () => {
    rechazando.value = null;
    rechazandoTodas.value = null;
    motivoRechazo.value = '';
};

const submitRechazar = () => {
    router.post(route('admin.solicitudes.rechazar', rechazando.value.id), {
        motivo: motivoRechazo.value || null,
    }, { onSuccess: cerrarModal });
};
const submitRechazarTodas = () => {
    router.post(route('admin.solicitudes.rechazar-todas', rechazandoTodas.value.id), {
        motivo: motivoRechazo.value || null,
    }, { onSuccess: cerrarModal });
};

// ── Aprobar ───────────────────────────────────────────────────────────────
const aprobar = (solicitud) => {
    router.post(route('admin.solicitudes.aprobar', solicitud.id));
};
const aprobarTodas = (tienda) => {
    router.post(route('admin.solicitudes.aprobar-todas', tienda.id));
};

// ── Helpers display ───────────────────────────────────────────────────────
const tipoLabel = (tipo) => ({
    update_tienda:   'Cambio tienda',
    create_producto: 'Nuevo producto',
    update_producto: 'Cambio producto',
    delete_producto: 'Eliminar producto',
})[tipo] ?? tipo;

const tipoCls = (tipo) => ({
    update_tienda:   'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    create_producto: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
    update_producto: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
    delete_producto: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
})[tipo] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300';

const isImage = (campo) => ['logo', 'imagen_portada', 'imagen'].includes(campo);

// ── Filtros y paginación ─────────────────────────────────────────────────
const filtros = ref({
    busqueda: '',
    tipo:     'todos',  // todos · update_tienda · create_producto · update_producto · delete_producto
    rango:    'todos',  // todos · hoy · semana · mes
});
const tiendaPagina = ref(1);
const TIENDAS_PER_PAGE = 5;

const tiendasFiltradas = computed(() => {
    const f = filtros.value;
    const q = f.busqueda.trim().toLowerCase();

    return props.tiendas
        .map(t => {
            let sols = [...t.solicitudes];

            if (f.tipo !== 'todos') sols = sols.filter(s => s.tipo === f.tipo);

            if (f.rango !== 'todos') {
                const limite = new Date();
                if (f.rango === 'hoy')    limite.setHours(0, 0, 0, 0);
                if (f.rango === 'semana') limite.setDate(limite.getDate() - 7);
                if (f.rango === 'mes')    limite.setMonth(limite.getMonth() - 1);
                sols = sols.filter(s => s.created_at_iso && new Date(s.created_at_iso) >= limite);
            }

            if (q) {
                const tiendaMatch =
                    (t.nombre ?? '').toLowerCase().includes(q) ||
                    (t.owner ?? '').toLowerCase().includes(q);

                if (!tiendaMatch) {
                    sols = sols.filter(s =>
                        (s.label_campo ?? '').toLowerCase().includes(q) ||
                        (s.producto?.nombre ?? '').toLowerCase().includes(q) ||
                        (s.solicitante ?? '').toLowerCase().includes(q)
                    );
                }
            }

            return { ...t, solicitudes: sols, total: sols.length };
        })
        .filter(t => t.solicitudes.length > 0);
});

const tiendasTotalPaginas = computed(() =>
    Math.max(1, Math.ceil(tiendasFiltradas.value.length / TIENDAS_PER_PAGE))
);
const tiendasPaginadas = computed(() => {
    const start = (tiendaPagina.value - 1) * TIENDAS_PER_PAGE;
    return tiendasFiltradas.value.slice(start, start + TIENDAS_PER_PAGE);
});

const totalSolicitudesFiltradas = computed(() =>
    tiendasFiltradas.value.reduce((sum, t) => sum + t.solicitudes.length, 0)
);
const totalSolicitudesOriginales = computed(() =>
    props.tiendas.reduce((sum, t) => sum + t.solicitudes.length, 0)
);

const resetFiltros = () => {
    filtros.value = { busqueda: '', tipo: 'todos', rango: 'todos' };
};

watch(filtros, () => { tiendaPagina.value = 1; }, { deep: true });
watch(() => props.estado, () => { tiendaPagina.value = 1; });
</script>

<template>
    <Head title="Solicitudes de cambio" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Solicitudes de cambio</h2>
                <Link :href="route('admin.dashboard')" class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:underline dark:text-primary-400">
                    <ArrowLeft class="h-3.5 w-3.5" /> Volver al panel
                </Link>
            </div>
        </template>

        <!-- Toasts -->
        <div class="pointer-events-none fixed inset-0 z-[60] flex flex-col items-end justify-start space-y-4 p-6">
            <Toast v-for="t in toasts" :key="t.id" :type="t.type" :title="t.title" :message="t.message"
                   @close="toasts = toasts.filter(x => x.id !== t.id)" />
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ── Tabs de estado ─────────────────────────────────────── -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex gap-1 rounded-xl bg-white dark:bg-gray-800 p-1 shadow-sm border border-gray-100 dark:border-gray-700 w-fit">
                        <button v-for="tab in [
                            { key: 'pendiente', label: 'Pendientes' },
                            { key: 'aprobado',  label: 'Aprobadas' },
                            { key: 'rechazado', label: 'Rechazadas' },
                        ]" :key="tab.key"
                            @click="setEstado(tab.key)"
                            :class="['relative px-4 py-2 rounded-lg text-sm font-medium transition-colors', estadoTabCls(tab.key)]">
                            {{ tab.label }}
                            <span v-if="counts[tab.key] > 0"
                                  :class="['ml-1.5 inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold',
                                      tab.key === estado ? 'bg-white/30 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300']">
                                {{ counts[tab.key] }}
                            </span>
                        </button>
                    </div>
                    <p v-if="tiendas.length > 0" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ totalSolicitudesFiltradas }} de {{ totalSolicitudesOriginales }} solicitudes ·
                        {{ tiendasFiltradas.length }} tienda{{ tiendasFiltradas.length !== 1 ? 's' : '' }}
                    </p>
                </div>

                <!-- ── Barra de filtros ──────────────────────────────────── -->
                <div v-if="tiendas.length > 0"
                     class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4 shadow-sm">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                        <div class="relative lg:col-span-2">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                            </svg>
                            <input v-model="filtros.busqueda" type="text" placeholder="Buscar por tienda, owner, producto, campo..."
                                   class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                        </div>
                        <select v-model="filtros.tipo"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                            <option value="todos">Todos los tipos</option>
                            <option value="update_tienda">Cambio tienda</option>
                            <option value="create_producto">Crear producto</option>
                            <option value="update_producto">Cambio producto</option>
                            <option value="delete_producto">Eliminar producto</option>
                        </select>
                        <select v-model="filtros.rango"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                            <option value="todos">Cualquier fecha</option>
                            <option value="hoy">Hoy</option>
                            <option value="semana">Última semana</option>
                            <option value="mes">Último mes</option>
                        </select>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" @click="resetFiltros"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- ── Sin resultados ─────────────────────────────────────── -->
                <div v-if="tiendas.length === 0"
                     class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 py-20 text-center text-gray-400 shadow-sm">
                    <svg class="mx-auto mb-3 h-14 w-14 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-lg font-medium">No hay solicitudes {{ estado === 'pendiente' ? 'pendientes' : estado === 'aprobado' ? 'aprobadas' : 'rechazadas' }}</p>
                </div>

                <!-- ── Sin coincidencias en filtros ───────────────────────── -->
                <div v-else-if="tiendasFiltradas.length === 0"
                     class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 py-16 text-center text-gray-400 shadow-sm">
                    <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm">Ninguna solicitud coincide con los filtros aplicados</p>
                    <button type="button" @click="resetFiltros"
                            class="mt-3 text-xs font-medium text-primary-500 hover:text-primary-600">
                        Limpiar filtros
                    </button>
                </div>

                <!-- ── Tiendas con solicitudes ────────────────────────────── -->
                <div v-for="tienda in tiendasPaginadas" :key="tienda.id"
                     class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">

                    <!-- Cabecera tienda -->
                    <div class="flex items-center justify-between px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <img v-if="imgUrl(tienda.logo)" :src="imgUrl(tienda.logo)"
                                 class="h-9 w-9 rounded-full object-cover ring-2 ring-primary-200" alt="" />
                            <div v-else class="h-9 w-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-sm">
                                {{ tienda.nombre.charAt(0) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ tienda.nombre }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ tienda.owner }} · {{ tienda.total }} solicitud{{ tienda.total !== 1 ? 'es' : '' }}</p>
                            </div>
                        </div>
                        <div v-if="estado === 'pendiente'" class="flex items-center gap-2">
                            <button @click="aprobarTodas(tienda)"
                                    class="flex items-center gap-1.5 rounded-xl bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Aprobar todas
                            </button>
                            <button @click="abrirRechazarTodas(tienda)"
                                    class="flex items-center gap-1.5 rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Rechazar todas
                            </button>
                        </div>
                    </div>

                    <!-- Lista solicitudes -->
                    <div class="divide-y divide-gray-50 dark:divide-gray-700">
                        <div v-for="s in tienda.solicitudes" :key="s.id"
                             class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <!-- Badges -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span :class="[tipoCls(s.tipo), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold']">
                                            {{ tipoLabel(s.tipo) }}
                                        </span>
                                        <span v-if="s.producto" class="text-xs text-gray-500 dark:text-gray-400">
                                            Producto: {{ s.producto.nombre }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ s.created_at }} · {{ s.solicitante }}</span>
                                    </div>

                                    <!-- Campo -->
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ s.label_campo }}</p>

                                    <!-- Diff para update_tienda / update_producto -->
                                    <template v-if="s.tipo === 'update_tienda' || s.tipo === 'update_producto'">
                                        <!-- Imagen diff -->
                                        <div v-if="isImage(s.campo)" class="flex flex-wrap gap-4 mt-2">
                                            <div>
                                                <p class="text-xs text-gray-400 mb-1">Actual</p>
                                                <img v-if="imgUrl(s.valor_anterior?.value)"
                                                     :src="imgUrl(s.valor_anterior.value)"
                                                     class="h-20 w-20 rounded-lg object-cover border border-gray-200 dark:border-gray-600" alt="Antes" />
                                                <div v-else class="h-20 w-20 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-xs text-gray-400">Sin imagen</div>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-400 mb-1">Nuevo</p>
                                                <img v-if="imgUrl(s.valor_nuevo?.value)"
                                                     :src="imgUrl(s.valor_nuevo.value)"
                                                     class="h-20 w-20 rounded-lg object-cover border-2 border-green-400" alt="Nuevo" />
                                            </div>
                                        </div>
                                        <!-- Texto diff -->
                                        <div v-else class="flex flex-wrap gap-3 text-xs mt-2">
                                            <div class="flex items-center gap-1 rounded-lg bg-red-50 dark:bg-red-900/20 px-3 py-2 text-red-700 dark:text-red-300 max-w-sm">
                                                <span class="font-semibold shrink-0">Antes:</span>
                                                <span class="truncate">{{ s.valor_anterior?.value ?? '—' }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 rounded-lg bg-green-50 dark:bg-green-900/20 px-3 py-2 text-green-700 dark:text-green-300 max-w-sm">
                                                <span class="font-semibold shrink-0">Nuevo:</span>
                                                <span class="truncate">{{ s.valor_nuevo?.value ?? '—' }}</span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Crear producto: resumen -->
                                    <template v-if="s.tipo === 'create_producto' && s.valor_nuevo">
                                        <div class="mt-2 rounded-xl bg-green-50 dark:bg-green-900/10 p-3 text-xs text-green-800 dark:text-green-300 grid grid-cols-2 gap-x-4 gap-y-1">
                                            <span><strong>Nombre:</strong> {{ s.valor_nuevo.nombre }}</span>
                                            <span><strong>Precio:</strong> {{ s.valor_nuevo.precio }}€</span>
                                            <span><strong>Stock:</strong> {{ s.valor_nuevo.stock }}</span>
                                            <span><strong>Unidad:</strong> {{ s.valor_nuevo.unidad }}</span>
                                            <span v-if="s.valor_nuevo.descripcion" class="col-span-2"><strong>Desc:</strong> {{ s.valor_nuevo.descripcion }}</span>
                                            <div v-if="s.valor_nuevo.imagen" class="col-span-2 mt-2">
                                                <img :src="imgUrl(s.valor_nuevo.imagen)"
                                                     class="h-16 w-16 rounded-lg object-cover border border-green-300" alt="Imagen producto" />
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Eliminar producto: snapshot -->
                                    <template v-if="s.tipo === 'delete_producto' && s.valor_anterior">
                                        <div class="mt-2 rounded-xl bg-red-50 dark:bg-red-900/10 p-3 text-xs text-red-800 dark:text-red-300">
                                            <p>Producto: <strong>{{ s.valor_anterior.nombre }}</strong> · {{ s.valor_anterior.precio }}€ · Stock: {{ s.valor_anterior.stock }}</p>
                                            <img v-if="s.valor_anterior.imagen" :src="imgUrl(s.valor_anterior.imagen)"
                                                 class="mt-2 h-12 w-12 rounded-lg object-cover border border-red-200" alt="" />
                                        </div>
                                    </template>

                                    <!-- Motivo rechazo (si aplica) -->
                                    <p v-if="s.motivo_rechazo" class="mt-2 text-xs text-red-600 dark:text-red-400 italic">
                                        Motivo rechazo: {{ s.motivo_rechazo }}
                                    </p>
                                </div>

                                <!-- Acciones (solo pendiente) -->
                                <div v-if="estado === 'pendiente'" class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-start">
                                    <button @click="aprobar(s)"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-green-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-600 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Aprobar
                                    </button>
                                    <button @click="abrirRechazar(s)"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Rechazar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Paginación tiendas ─────────────────────────────────── -->
                <div v-if="tiendasFiltradas.length > 0 && tiendasTotalPaginas > 1"
                     class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-6 py-3 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Página {{ tiendaPagina }} de {{ tiendasTotalPaginas }}
                    </p>
                    <div class="flex items-center gap-1">
                        <button @click="tiendaPagina = Math.max(1, tiendaPagina - 1)"
                                :disabled="tiendaPagina === 1"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            ← Anterior
                        </button>
                        <span class="rounded-lg bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 text-xs font-bold text-primary-600 dark:text-primary-300">
                            {{ tiendaPagina }}
                        </span>
                        <button @click="tiendaPagina = Math.min(tiendasTotalPaginas, tiendaPagina + 1)"
                                :disabled="tiendaPagina === tiendasTotalPaginas"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                            Siguiente →
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Modal rechazo individual ───────────────────────────────────── -->
        <div v-if="rechazando || rechazandoTodas"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             @click.self="cerrarModal">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ rechazandoTodas ? `Rechazar todas las solicitudes de ${rechazandoTodas.nombre}` : `Rechazar: ${rechazando?.label_campo}` }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">El motivo se mostrará al propietario (opcional).</p>
                <textarea v-model="motivoRechazo"
                          rows="3"
                          placeholder="Motivo del rechazo (opcional)..."
                          class="mt-4 w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400 resize-none">
                </textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="cerrarModal"
                            class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancelar
                    </button>
                    <button @click="rechazandoTodas ? submitRechazarTodas() : submitRechazar()"
                            class="rounded-xl bg-red-500 px-5 py-2 text-sm font-semibold text-white hover:bg-red-600 transition-colors">
                        Confirmar rechazo
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
