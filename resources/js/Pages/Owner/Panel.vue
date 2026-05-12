<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { ChevronLeft, ChevronRight, Download, FileText } from 'lucide-vue-next';
import { useToasts } from '@/Composables/useToasts';

const props = defineProps({
    tienda:            { type: Object,  required: true },
    stats:             { type: Object,  required: true },
    ingresosGrafica:   { type: Array,   default: () => [] },
    topProductos:      { type: Array,   default: () => [] },
    pedidosRecientes:  { type: Array,   default: () => [] },
    productos:         { type: Array,   default: () => [] },
    categorias:        { type: Array,   default: () => [] },
    solicitudes:       { type: Array,   default: () => [] },
    beneficiosPorMes:  { type: Array,   default: () => [] },
});

const page = usePage();
const { success: toastSuccess, error: toastError, info: toastInfo } = useToasts();

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
    pendiente:   { label: 'Pendiente',   cls: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' },
    en_proceso:  { label: 'En proceso',  cls: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' },
    completado:  { label: 'Completado',  cls: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' },
    cancelado:   { label: 'Cancelado',   cls: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' },
    confirmado:  { label: 'Confirmado',  cls: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' },
    preparando:  { label: 'Preparando',  cls: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300' },
    en_camino:   { label: 'En camino',   cls: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' },
    entregado:   { label: 'Entregado',   cls: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' },
};
const getEstado = (e) => estadoConfig[e] ?? { label: e, cls: 'bg-gray-100 text-gray-700' };

// ── Solicitudes ───────────────────────────────────────────────────────────────
const solicitudEstadoCls = (estado) => ({
    pendiente:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    aprobado:   'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
    rechazado:  'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
})[estado] ?? 'bg-gray-100 text-gray-700';

const solicitudEstadoLabel = (estado) => ({
    pendiente: 'Pendiente',
    aprobado:  'Aprobado',
    rechazado: 'Rechazado',
})[estado] ?? estado;

const pendientesCount = computed(() => props.solicitudes.filter(s => s.estado === 'pendiente').length);

// ════════════════════════════════════════════════════════════════════════════
// FILTROS Y PAGINACIÓN
// ════════════════════════════════════════════════════════════════════════════

const PER_PAGE = 8;

// ── Pedidos: filtros ─────────────────────────────────────────────────────────
const pedidosFiltros = ref({
    busqueda: '',
    estado:   'todos',
    rango:    'todos',   // todos · hoy · semana · mes
    orden:    'fecha_desc',
});
const pedidosPagina = ref(1);

const pedidosFiltrados = computed(() => {
    let arr = [...props.pedidosRecientes];
    const f  = pedidosFiltros.value;
    const q  = f.busqueda.trim().toLowerCase();

    if (q) {
        arr = arr.filter(p =>
            (p.numero_pedido ?? '').toLowerCase().includes(q) ||
            (p.cliente ?? '').toLowerCase().includes(q)
        );
    }
    if (f.estado !== 'todos') arr = arr.filter(p => p.estado === f.estado);

    if (f.rango !== 'todos') {
        const ahora = new Date();
        const limite = new Date();
        if (f.rango === 'hoy')    limite.setHours(0, 0, 0, 0);
        if (f.rango === 'semana') limite.setDate(ahora.getDate() - 7);
        if (f.rango === 'mes')    limite.setMonth(ahora.getMonth() - 1);
        arr = arr.filter(p => p.created_at_iso && new Date(p.created_at_iso) >= limite);
    }

    arr.sort((a, b) => {
        switch (f.orden) {
            case 'fecha_asc':    return new Date(a.created_at_iso) - new Date(b.created_at_iso);
            case 'importe_desc': return Number(b.total_tienda) - Number(a.total_tienda);
            case 'importe_asc':  return Number(a.total_tienda) - Number(b.total_tienda);
            default:             return new Date(b.created_at_iso) - new Date(a.created_at_iso);
        }
    });
    return arr;
});

const pedidosTotalPaginas = computed(() => Math.max(1, Math.ceil(pedidosFiltrados.value.length / PER_PAGE)));
const pedidosPaginados = computed(() => {
    const start = (pedidosPagina.value - 1) * PER_PAGE;
    return pedidosFiltrados.value.slice(start, start + PER_PAGE);
});
const pedidosResetFiltros = () => {
    pedidosFiltros.value = { busqueda: '', estado: 'todos', rango: 'todos', orden: 'fecha_desc' };
};
watch(pedidosFiltros, () => { pedidosPagina.value = 1; }, { deep: true });

// ── Productos: filtros ───────────────────────────────────────────────────────
const productosFiltros = ref({
    busqueda:       '',
    categoria:      'todas',
    disponibilidad: 'todos',  // todos · disponible · no_disponible
    oferta:         'todos',  // todos · activa · inactiva · sin_oferta
    stock:          'todos',  // todos · agotado · bajo · normal
    orden:          'nombre_asc',
});
const productosPagina = ref(1);

const productosFiltrados = computed(() => {
    let arr = [...props.productos];
    const f = productosFiltros.value;
    const q = f.busqueda.trim().toLowerCase();

    if (q) arr = arr.filter(p => (p.nombre ?? '').toLowerCase().includes(q));
    if (f.categoria !== 'todas') arr = arr.filter(p => String(p.categoria_id) === String(f.categoria));
    if (f.disponibilidad === 'disponible')     arr = arr.filter(p => p.disponible);
    if (f.disponibilidad === 'no_disponible')  arr = arr.filter(p => !p.disponible);
    if (f.oferta === 'activa')      arr = arr.filter(p => p.precio_oferta && p.oferta_activa);
    if (f.oferta === 'inactiva')    arr = arr.filter(p => p.precio_oferta && !p.oferta_activa);
    if (f.oferta === 'sin_oferta')  arr = arr.filter(p => !p.precio_oferta);
    if (f.stock === 'agotado') arr = arr.filter(p => Number(p.stock) === 0);
    if (f.stock === 'bajo')    arr = arr.filter(p => Number(p.stock) > 0 && Number(p.stock) <= 5);
    if (f.stock === 'normal')  arr = arr.filter(p => Number(p.stock) > 5);

    arr.sort((a, b) => {
        switch (f.orden) {
            case 'nombre_desc': return (b.nombre ?? '').localeCompare(a.nombre ?? '');
            case 'precio_asc':  return Number(a.precio) - Number(b.precio);
            case 'precio_desc': return Number(b.precio) - Number(a.precio);
            case 'stock_asc':   return Number(a.stock)  - Number(b.stock);
            case 'stock_desc':  return Number(b.stock)  - Number(a.stock);
            default:            return (a.nombre ?? '').localeCompare(b.nombre ?? '');
        }
    });
    return arr;
});

const productosTotalPaginas = computed(() => Math.max(1, Math.ceil(productosFiltrados.value.length / PER_PAGE)));
const productosPaginados = computed(() => {
    const start = (productosPagina.value - 1) * PER_PAGE;
    return productosFiltrados.value.slice(start, start + PER_PAGE);
});
const productosResetFiltros = () => {
    productosFiltros.value = { busqueda: '', categoria: 'todas', disponibilidad: 'todos', oferta: 'todos', stock: 'todos', orden: 'nombre_asc' };
};
watch(productosFiltros, () => { productosPagina.value = 1; }, { deep: true });

// ── Solicitudes: filtros ─────────────────────────────────────────────────────
const solicitudesFiltros = ref({
    busqueda: '',
    tipo:     'todos',  // todos · create_producto · update_producto · delete_producto · update_tienda
    estado:   'todos',  // todos · pendiente · aprobado · rechazado
});
const solicitudesPagina = ref(1);
const SOL_PER_PAGE = 6;

const solicitudesFiltradas = computed(() => {
    let arr = [...props.solicitudes];
    const f = solicitudesFiltros.value;
    const q = f.busqueda.trim().toLowerCase();

    if (q) {
        arr = arr.filter(s =>
            (s.producto?.nombre ?? '').toLowerCase().includes(q) ||
            (s.label_campo ?? '').toLowerCase().includes(q)
        );
    }
    if (f.tipo !== 'todos')   arr = arr.filter(s => s.tipo === f.tipo);
    if (f.estado !== 'todos') arr = arr.filter(s => s.estado === f.estado);
    return arr;
});

const solicitudesTotalPaginas = computed(() => Math.max(1, Math.ceil(solicitudesFiltradas.value.length / SOL_PER_PAGE)));
const solicitudesPaginadas = computed(() => {
    const start = (solicitudesPagina.value - 1) * SOL_PER_PAGE;
    return solicitudesFiltradas.value.slice(start, start + SOL_PER_PAGE);
});
const solicitudesResetFiltros = () => {
    solicitudesFiltros.value = { busqueda: '', tipo: 'todos', estado: 'todos' };
};
watch(solicitudesFiltros, () => { solicitudesPagina.value = 1; }, { deep: true });

// ── Beneficios: filtro mes ────────────────────────────────────────────────────
const mesFmt = (date) => date.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }).toLowerCase();
const mesActualStr = mesFmt(new Date());
const selectedMes = ref(mesActualStr);

const beneficiosFiltrados = computed(() => {
    if (!selectedMes.value) return props.beneficiosPorMes;
    return props.beneficiosPorMes.filter(f => f.mes === selectedMes.value);
});

// ── Producto: añadir form ─────────────────────────────────────────────────────
const showAddForm = ref(false);
const addForm = useForm({
    nombre:        '',
    descripcion:   '',
    precio:        '',
    precio_oferta: '',
    unidad:        'unidad',
    stock:         0,
    imagen:        null,
    imagen_url:    '',
});
const addImagePreview = ref(null);

const onAddImagen = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    addForm.imagen = file;
    addForm.imagen_url = '';
    addImagePreview.value = URL.createObjectURL(file);
};

const submitAddProducto = () => {
    addForm.post(route('owner.solicitar.producto.crear'), {
        forceFormData: true,
        onSuccess: () => {
            showAddForm.value = false;
            addForm.reset();
            addImagePreview.value = null;
        },
    });
};

// ── Producto: editar form ─────────────────────────────────────────────────────
const editProducto = ref(null);
const editOriginal = ref({});
const editForm = useForm({
    nombre:        '',
    descripcion:   '',
    precio:        '',
    precio_oferta: '',
    unidad:        '',
    stock:         0,
    imagen:        null,
    imagen_url:    '',
    _method:       'POST',
});
const editImagePreview = ref(null);

const openEdit = (p) => {
    editProducto.value = p;
    editOriginal.value = {
        nombre:        p.nombre,
        descripcion:   p.descripcion ?? '',
        precio:        String(p.precio),
        precio_oferta: String(p.precio_oferta ?? ''),
        unidad:        p.unidad ?? 'unidad',
        stock:         String(p.stock),
    };
    editForm.nombre        = p.nombre;
    editForm.descripcion   = p.descripcion ?? '';
    editForm.precio        = p.precio;
    editForm.precio_oferta = p.precio_oferta ?? '';
    editForm.unidad        = p.unidad ?? 'unidad';
    editForm.stock         = p.stock;
    editForm.imagen        = null;
    editForm.imagen_url    = '';
    editImagePreview.value = null;
};

// Cualquier cambio (precio, oferta, nombre, descripción, stock, imagen, etc.)
// requiere aprobación del administrador. Aquí sólo detectamos si hay algo
// distinto respecto al producto original para habilitar/deshabilitar el botón.
const editHasAnyChange = computed(() => {
    if (!editProducto.value) return false;
    return String(editForm.precio) !== editOriginal.value.precio ||
           String(editForm.precio_oferta ?? '') !== editOriginal.value.precio_oferta ||
           editForm.nombre !== editOriginal.value.nombre ||
           editForm.descripcion !== editOriginal.value.descripcion ||
           editForm.unidad !== editOriginal.value.unidad ||
           String(editForm.stock) !== editOriginal.value.stock ||
           editForm.imagen !== null ||
           editForm.imagen_url !== '';
});

const onEditImagen = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    editForm.imagen = file;
    editForm.imagen_url = '';
    editImagePreview.value = URL.createObjectURL(file);
};

const submitEditProducto = () => {
    editForm.post(route('owner.solicitar.producto.editar', editProducto.value.id), {
        forceFormData: true,
        onSuccess: () => {
            editProducto.value = null;
            editForm.reset();
            editImagePreview.value = null;
        },
    });
};

// ── Producto: eliminar ────────────────────────────────────────────────────────
const confirmDelete = ref(null);

const submitDeleteProducto = (producto) => {
    router.delete(route('owner.solicitar.producto.eliminar', producto.id), {
        onSuccess: () => { confirmDelete.value = null; },
    });
};

// ── Producto: oferta (solicitud) ─────────────────────────────────────────────
const ofertaProducto = ref(null);
const ofertaForm = useForm({
    activar: true,
    precio_oferta: '',
});

const abrirOferta = (p) => {
    ofertaProducto.value = p;
    ofertaForm.activar = !p.oferta_activa;
    // Sugerencia: precio actual con un 10 % de descuento, redondeado a 2 decimales.
    const sugerido = p.precio_oferta && p.precio_oferta > 0
        ? Number(p.precio_oferta)
        : Math.max(0.01, Math.round((Number(p.precio) * 0.9) * 100) / 100);
    ofertaForm.precio_oferta = ofertaForm.activar ? String(sugerido) : '';
    ofertaForm.clearErrors();
};

const cerrarOferta = () => {
    ofertaProducto.value = null;
    ofertaForm.reset();
    ofertaForm.clearErrors();
};

const submitOferta = () => {
    if (!ofertaProducto.value) return;
    ofertaForm.post(route('owner.producto.oferta', ofertaProducto.value.id), {
        preserveScroll: true,
        onSuccess: () => { cerrarOferta(); },
    });
};
</script>

<template>
    <Head :title="`Panel – ${tienda.nombre}`" />

    <AuthenticatedLayout>
        <!-- Toasts via ToastContainer global -->

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- ── Header del panel (sin borde blanco) ────────────────── -->
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

                <!-- ── Tabs ──────────────────────────────────────────────────── -->
                <div class="flex gap-1 rounded-xl bg-white dark:bg-gray-800 p-1 shadow-sm border border-gray-100 dark:border-gray-700 w-fit flex-wrap">
                    <button v-for="t in [
                        { key:'resumen',     label:'Resumen' },
                        { key:'pedidos',     label:'Pedidos' },
                        { key:'productos',   label:'Productos' },
                        { key:'beneficios',  label:'Beneficios' },
                        { key:'solicitudes', label:'Mis solicitudes' },
                    ]" :key="t.key"
                        @click="tab = t.key"
                        :class="['relative px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            tab === t.key ? 'bg-primary-500 text-white shadow' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700']">
                        {{ t.label }}
                        <span v-if="t.key === 'solicitudes' && pendientesCount > 0"
                              class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-yellow-400 text-[9px] font-bold text-yellow-900">
                            {{ pendientesCount }}
                        </span>
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
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Pedidos de tu tienda</h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ pedidosFiltrados.length }} de {{ pedidosRecientes.length }} pedidos · Mostrando {{ pedidosPaginados.length }} en esta página
                                </p>
                            </div>
                            <a :href="route('owner.exportar.pedidos')" target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                                <FileText class="h-3.5 w-3.5" /> Exportar pedidos (PDF)
                            </a>
                        </div>

                        <!-- ── Barra de filtros ─────────────────────────────────── -->
                        <div class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 px-6 py-4">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                                <!-- Búsqueda -->
                                <div class="relative lg:col-span-2">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <input v-model="pedidosFiltros.busqueda" type="text" placeholder="Buscar nº pedido o cliente..."
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <select v-model="pedidosFiltros.estado"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmado">Confirmado</option>
                                    <option value="preparando">Preparando</option>
                                    <option value="en_camino">En camino</option>
                                    <option value="entregado">Entregado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <select v-model="pedidosFiltros.rango"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Cualquier fecha</option>
                                    <option value="hoy">Hoy</option>
                                    <option value="semana">Última semana</option>
                                    <option value="mes">Último mes</option>
                                </select>
                                <select v-model="pedidosFiltros.orden"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="fecha_desc">Más recientes</option>
                                    <option value="fecha_asc">Más antiguos</option>
                                    <option value="importe_desc">Mayor importe</option>
                                    <option value="importe_asc">Menor importe</option>
                                </select>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" @click="pedidosResetFiltros"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>

                        <!-- ── Sin pedidos en absoluto ─────────────────────────── -->
                        <div v-if="pedidosRecientes.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Aún no hay pedidos
                        </div>
                        <!-- ── Sin resultados tras filtros ─────────────────────── -->
                        <div v-else-if="pedidosFiltrados.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">Ningún pedido coincide con los filtros aplicados</p>
                            <button type="button" @click="pedidosResetFiltros"
                                    class="mt-2 text-xs font-medium text-primary-500 hover:text-primary-600">
                                Limpiar filtros
                            </button>
                        </div>
                        <!-- ── Tabla pedidos ────────────────────────────────────── -->
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
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <tr v-for="p in pedidosPaginados" :key="p.id"
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
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a :href="route('factura.show', p.id)" target="_blank"
                                                   class="text-xs font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Ver factura">
                                                    <FileText class="h-4 w-4" />
                                                </a>
                                                <Link :href="route('owner.pedido.show', p.id)" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300">
                                                    Ver detalle →
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- ── Paginación ───────────────────────────────────────── -->
                        <div v-if="pedidosFiltrados.length > 0 && pedidosTotalPaginas > 1"
                             class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700 px-6 py-3">
                            <p class="text-xs text-gray-500">
                                Página {{ pedidosPagina }} de {{ pedidosTotalPaginas }}
                            </p>
                            <div class="flex items-center gap-1">
                                <button @click="pedidosPagina = Math.max(1, pedidosPagina - 1)"
                                        :disabled="pedidosPagina === 1"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    <ChevronLeft class="h-3.5 w-3.5 inline" /> Anterior
                                </button>
                                <span class="rounded-lg bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 text-xs font-bold text-primary-600 dark:text-primary-300">
                                    {{ pedidosPagina }}
                                </span>
                                <button @click="pedidosPagina = Math.min(pedidosTotalPaginas, pedidosPagina + 1)"
                                        :disabled="pedidosPagina === pedidosTotalPaginas"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    Siguiente <ChevronRight class="h-3.5 w-3.5 inline" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ════════════════ TAB PRODUCTOS ════════════════ -->
                <template v-if="tab === 'productos'">

                    <!-- Añadir producto -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Productos de tu tienda</h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ stats.totalProductos }} total · {{ stats.productosDisponibles }} disponibles ·
                                    {{ productosFiltrados.length }} coinciden con los filtros
                                </p>
                            </div>
                            <button @click="showAddForm = !showAddForm"
                                    class="flex items-center gap-2 rounded-xl bg-primary-500 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-600 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Añadir producto
                            </button>
                        </div>

                        <!-- Formulario añadir -->
                        <div v-if="showAddForm" class="border-b border-gray-100 dark:border-gray-700 bg-primary-50 dark:bg-primary-900/10 px-6 py-6">
                            <h4 class="mb-4 text-sm font-semibold text-gray-800 dark:text-gray-200">Solicitar creación de nuevo producto</h4>
                            <form @submit.prevent="submitAddProducto" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nombre *</label>
                                    <input v-model="addForm.nombre" type="text" required minlength="2" maxlength="255"
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                    <p v-if="addForm.errors.nombre" class="mt-1 text-xs text-red-500">{{ addForm.errors.nombre }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Descripción</label>
                                    <textarea v-model="addForm.descripcion" rows="2"
                                              class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Precio (€) *</label>
                                    <input v-model="addForm.precio" type="number" step="0.01" min="0" max="99999.99" required inputmode="decimal" v-only-decimal
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Precio oferta (€)</label>
                                    <input v-model="addForm.precio_oferta" type="number" step="0.01" min="0" max="99999.99" inputmode="decimal" v-only-decimal
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Unidad *</label>
                                    <input v-model="addForm.unidad" type="text" required placeholder="kg, unidad, litro..."
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Stock *</label>
                                    <input v-model="addForm.stock" type="number" min="0" max="999999" required inputmode="numeric" step="1" v-only-digits
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Imagen</label>
                                    <div class="flex items-center gap-3">
                                        <img v-if="addImagePreview" :src="addImagePreview" class="h-14 w-14 rounded-lg object-cover border border-gray-200" alt="" />
                                        <input type="file" accept="image/*" @change="onAddImagen" class="text-sm text-gray-600 dark:text-gray-400" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">O pega una URL:</p>
                                    <input v-model="addForm.imagen_url" type="url" placeholder="https://..."
                                           class="mt-1 w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <div class="sm:col-span-2 flex justify-end gap-3">
                                    <button type="button" @click="showAddForm = false; addForm.reset();"
                                            class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        Cancelar
                                    </button>
                                    <button type="submit" :disabled="addForm.processing"
                                            class="rounded-xl bg-primary-500 px-5 py-2 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-50 transition-colors">
                                        {{ addForm.processing ? 'Enviando...' : 'Enviar solicitud' }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- ── Barra de filtros productos ──────────────────────── -->
                        <div v-if="productos.length > 0"
                             class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 px-6 py-4">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-6">
                                <!-- Búsqueda -->
                                <div class="relative lg:col-span-2">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <input v-model="productosFiltros.busqueda" type="text" placeholder="Buscar producto..."
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <select v-model="productosFiltros.categoria"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todas">Todas las categorías</option>
                                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                                </select>
                                <select v-model="productosFiltros.disponibilidad"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Disponibilidad</option>
                                    <option value="disponible">Disponibles</option>
                                    <option value="no_disponible">No disponibles</option>
                                </select>
                                <select v-model="productosFiltros.oferta"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Ofertas</option>
                                    <option value="activa">Oferta activa</option>
                                    <option value="inactiva">Oferta sin activar</option>
                                    <option value="sin_oferta">Sin oferta</option>
                                </select>
                                <select v-model="productosFiltros.stock"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Stock</option>
                                    <option value="agotado">Agotado (0)</option>
                                    <option value="bajo">Bajo (1-5)</option>
                                    <option value="normal">Normal (>5)</option>
                                </select>
                            </div>
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <label class="text-xs font-medium text-gray-500">Ordenar:</label>
                                    <select v-model="productosFiltros.orden"
                                            class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                        <option value="nombre_asc">Nombre A-Z</option>
                                        <option value="nombre_desc">Nombre Z-A</option>
                                        <option value="precio_asc">Precio ↑</option>
                                        <option value="precio_desc">Precio ↓</option>
                                        <option value="stock_asc">Stock ↑</option>
                                        <option value="stock_desc">Stock ↓</option>
                                    </select>
                                </div>
                                <button type="button" @click="productosResetFiltros"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>

                        <!-- Lista de productos -->
                        <div v-if="productos.length === 0" class="py-16 text-center text-gray-400">
                            Sin productos registrados
                        </div>
                        <div v-else-if="productosFiltrados.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">Ningún producto coincide con los filtros</p>
                            <button type="button" @click="productosResetFiltros"
                                    class="mt-2 text-xs font-medium text-primary-500 hover:text-primary-600">
                                Limpiar filtros
                            </button>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Producto</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Precio</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Stock</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                                    <template v-for="p in productosPaginados" :key="p.id">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
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
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ Number(p.precio).toFixed(2) }}€</span>
                                                    <span v-if="p.precio_oferta" class="flex items-center gap-1">
                                                        <span :class="['text-xs font-medium', p.oferta_activa ? 'text-green-600 dark:text-green-400' : 'text-gray-400 line-through']">
                                                            {{ Number(p.precio_oferta).toFixed(2) }}€
                                                        </span>
                                                        <span v-if="p.oferta_activa" class="rounded-full bg-green-100 dark:bg-green-900/40 px-1.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400">OFERTA</span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ p.stock }}</td>
                                            <td class="px-6 py-3">
                                                <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                                    p.disponible ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                                                    {{ p.disponible ? 'Disponible' : 'No disponible' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-right">
                                                <div class="flex flex-col items-end gap-1.5">
                                                    <!-- Aviso: oferta pendiente de activar -->
                                                    <span
                                                        v-if="p.precio_oferta && !p.oferta_activa"
                                                        class="inline-flex animate-pulse items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:text-amber-400"
                                                    >
                                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 01.359.852L12.982 9.75h7.268a.75.75 0 01.548 1.262l-10.5 11.25a.75.75 0 01-1.272-.71l1.992-7.302H3.718a.75.75 0 01-.548-1.262l10.5-11.25a.75.75 0 01.945-.143z" clip-rule="evenodd" /></svg>
                                                        Activa tu oferta
                                                    </span>
                                                    <div class="flex items-center gap-2">
                                                    <!-- Toggle oferta -->
                                                    <button
                                                        @click="abrirOferta(p)"
                                                        :title="p.oferta_activa ? 'Solicitar desactivar oferta' : 'Solicitar activar oferta'"
                                                        :class="['inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-xs font-bold transition-all',
                                                            p.oferta_activa
                                                                ? 'border-green-500 bg-green-500 text-white shadow-sm shadow-green-200 dark:shadow-green-900/40 hover:bg-green-600 hover:border-green-600'
                                                                : 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100 dark:border-amber-600 dark:bg-amber-900/20 dark:text-amber-400']"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path v-if="p.oferta_activa" fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                                            <path v-else fill-rule="evenodd" d="M5.25 2.25a3 3 0 00-3 3v4.318a3 3 0 00.879 2.121l9.58 9.581c.92.92 2.39 1.186 3.548.428a18.849 18.849 0 005.441-5.44c.758-1.16.492-2.629-.428-3.548l-9.58-9.581a3 3 0 00-2.121-.879H5.25zM6.375 7.5a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" clip-rule="evenodd" />
                                                        </svg>
                                                        {{ p.oferta_activa ? 'Oferta ON' : 'Oferta OFF' }}
                                                    </button>
                                                    <button @click="openEdit(p)"
                                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-primary-50 hover:border-primary-300 hover:text-primary-700 dark:hover:bg-primary-900/20 transition-colors">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Editar
                                                    </button>
                                                    <button @click="confirmDelete = p"
                                                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Eliminar
                                                    </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <!-- Inline edit form -->
                                        <tr v-if="editProducto?.id === p.id" class="bg-blue-50 dark:bg-blue-900/10">
                                            <td colspan="5" class="px-6 py-5">
                                                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Editar "{{ p.nombre }}"</h4>
                                    <span v-if="editHasAnyChange" class="inline-flex items-center gap-1 rounded-full bg-primary-100 dark:bg-primary-900/40 px-2 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-400">
                                        Pendiente de enviar
                                    </span>
                                </div>
                                                <form @submit.prevent="submitEditProducto" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nombre *</label>
                                                        <input v-model="editForm.nombre" type="text" required minlength="2" maxlength="255"
                                                               class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Unidad</label>
                                                        <select v-model="editForm.unidad"
                                                                class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white">
                                                            <option value="unidad">Unidad</option>
                                                            <option value="kg">Kilogramo (kg)</option>
                                                            <option value="g">Gramo (g)</option>
                                                            <option value="litro">Litro</option>
                                                            <option value="ml">Mililitro (ml)</option>
                                                            <option value="docena">Docena</option>
                                                            <option value="caja">Caja</option>
                                                            <option value="bote">Bote</option>
                                                            <option value="bolsa">Bolsa</option>
                                                        </select>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Descripción</label>
                                                        <textarea v-model="editForm.descripcion" rows="2"
                                                                  class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white"></textarea>
                                                    </div>
                                                    <!-- Sección precios: requiere aprobación como el resto de campos -->
                                                    <div class="sm:col-span-2">
                                                        <div class="grid grid-cols-2 gap-3">
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Precio base (€) *</label>
                                                                <input v-model="editForm.precio" type="number" step="0.01" min="0" max="99999.99" required inputmode="decimal" v-only-decimal
                                                                       class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white" />
                                                            </div>
                                                            <div>
                                                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Precio oferta (€) <span class="font-normal text-gray-400">(opcional)</span></label>
                                                                <input v-model="editForm.precio_oferta" type="number" step="0.01" min="0" max="99999.99" inputmode="decimal" v-only-decimal
                                                                       :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 text-gray-900 dark:text-white',
                                                                           editForm.precio_oferta && +editForm.precio_oferta >= +editForm.precio
                                                                               ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20 focus:ring-red-400'
                                                                               : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-primary-400']" />
                                                            </div>
                                                        </div>
                                                        <!-- Feedback oferta -->
                                                        <div v-if="editForm.precio_oferta && +editForm.precio_oferta > 0 && +editForm.precio > 0" class="mt-2">
                                                            <div v-if="+editForm.precio_oferta < +editForm.precio" class="flex flex-wrap items-center gap-2 text-sm">
                                                                <span class="text-gray-500 dark:text-gray-400">Vista previa:</span>
                                                                <span class="line-through text-gray-400">{{ editForm.precio }}€</span>
                                                                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                                <span class="font-bold text-primary-600 dark:text-primary-400">{{ editForm.precio_oferta }}€</span>
                                                                <span class="rounded-full bg-primary-500 px-2 py-0.5 text-xs font-bold text-white">-{{ Math.round((1 - editForm.precio_oferta / editForm.precio) * 100) }}%</span>
                                                            </div>
                                                            <p v-else class="text-xs text-red-600 dark:text-red-400">El precio de oferta debe ser menor que el precio base.</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Unidad *</label>
                                                        <input v-model="editForm.unidad" type="text" required
                                                               class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Stock *</label>
                                                        <input v-model="editForm.stock" type="number" min="0" max="999999" required inputmode="numeric" step="1" v-only-digits
                                                               class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white" />
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Imagen (opcional, solo si quieres cambiarla)</label>
                                                        <div class="flex items-center gap-3">
                                                            <img v-if="editImagePreview" :src="editImagePreview" class="h-14 w-14 rounded-lg object-cover border" alt="" />
                                                            <img v-else-if="imgUrl(p.imagen)" :src="imgUrl(p.imagen)" class="h-14 w-14 rounded-lg object-cover border opacity-60" alt="" />
                                                            <input type="file" accept="image/*" @change="onEditImagen" class="text-sm text-gray-600 dark:text-gray-400" />
                                                        </div>
                                                        <input v-model="editForm.imagen_url" type="url" placeholder="O pega una URL de imagen..."
                                                               class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 text-gray-900 dark:text-white" />
                                                    </div>
                                                    <!-- Aviso de aprobación -->
                                                    <div class="sm:col-span-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2 text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                                        <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
                                                        </svg>
                                                        <span>Cualquier cambio (incluido el precio) requiere la aprobación del administrador antes de aplicarse.</span>
                                                    </div>
                                                    <div class="sm:col-span-2 flex justify-end gap-3">
                                                        <button type="button" @click="editProducto = null"
                                                                class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                            Cancelar
                                                        </button>
                                                        <button type="submit"
                                                                :disabled="editForm.processing || !editHasAnyChange"
                                                                class="inline-flex items-center gap-2 rounded-xl bg-primary-500 hover:bg-primary-600 px-5 py-2 text-sm font-semibold text-white transition-colors disabled:opacity-50">
                                                            <svg v-if="editForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                            </svg>
                                                            {{ editForm.processing ? 'Enviando...' : 'Enviar solicitud' }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- ── Paginación productos ─────────────────────────────── -->
                        <div v-if="productosFiltrados.length > 0 && productosTotalPaginas > 1"
                             class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700 px-6 py-3">
                            <p class="text-xs text-gray-500">
                                Página {{ productosPagina }} de {{ productosTotalPaginas }}
                            </p>
                            <div class="flex items-center gap-1">
                                <button @click="productosPagina = Math.max(1, productosPagina - 1)"
                                        :disabled="productosPagina === 1"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    <ChevronLeft class="h-3.5 w-3.5 inline" /> Anterior
                                </button>
                                <span class="rounded-lg bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 text-xs font-bold text-primary-600 dark:text-primary-300">
                                    {{ productosPagina }}
                                </span>
                                <button @click="productosPagina = Math.min(productosTotalPaginas, productosPagina + 1)"
                                        :disabled="productosPagina === productosTotalPaginas"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    Siguiente <ChevronRight class="h-3.5 w-3.5 inline" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal confirmar eliminación -->
                    <div v-if="confirmDelete"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                         @click.self="confirmDelete = null">
                        <div class="w-full max-w-sm max-h-[90vh] overflow-y-auto overscroll-contain rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">¿Solicitar eliminación?</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Se enviará una solicitud para eliminar <strong>{{ confirmDelete.nombre }}</strong>.
                                El administrador deberá aprobarla.
                            </p>
                            <div class="mt-5 flex justify-end gap-3">
                                <button @click="confirmDelete = null"
                                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Cancelar
                                </button>
                                <button @click="submitDeleteProducto(confirmDelete)"
                                        class="rounded-xl bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600 transition-colors">
                                    Solicitar eliminación
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal solicitar oferta -->
                    <div v-if="ofertaProducto"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
                         @click.self="cerrarOferta">
                        <div class="w-full max-w-md max-h-[90vh] overflow-y-auto overscroll-contain rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-300">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M5.25 2.25a3 3 0 00-3 3v4.318a3 3 0 00.879 2.121l9.58 9.581c.92.92 2.39 1.186 3.548.428a18.849 18.849 0 005.441-5.44c.758-1.16.492-2.629-.428-3.548l-9.58-9.581a3 3 0 00-2.121-.879H5.25zM6.375 7.5a1.125 1.125 0 100-2.25 1.125 1.125 0 000 2.25z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ ofertaForm.activar ? 'Activar oferta' : 'Desactivar oferta' }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ ofertaProducto.nombre }}</p>
                                </div>
                            </div>

                            <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 p-4 mb-4">
                                <p class="text-xs uppercase tracking-wider font-semibold text-gray-500 dark:text-gray-400 mb-2">Estado actual</p>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">Precio normal</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ Number(ofertaProducto.precio).toFixed(2) }} €</span>
                                </div>
                                <div v-if="ofertaProducto.precio_oferta" class="flex items-center justify-between text-sm mt-1">
                                    <span class="text-gray-700 dark:text-gray-300">Precio en oferta vigente</span>
                                    <span class="font-bold text-amber-600 dark:text-amber-400">{{ Number(ofertaProducto.precio_oferta).toFixed(2) }} €</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-1">
                                    <span class="text-gray-700 dark:text-gray-300">Oferta</span>
                                    <span :class="ofertaProducto.oferta_activa ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-gray-500'">
                                        {{ ofertaProducto.oferta_activa ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="ofertaForm.activar">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nuevo precio de oferta (€) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="ofertaForm.precio_oferta"
                                    type="number" step="0.01" min="0.01"
                                    :max="ofertaProducto.precio"
                                    v-only-decimal
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition"
                                />
                                <p v-if="ofertaForm.errors.precio_oferta" class="mt-1 text-xs text-red-500">
                                    {{ ofertaForm.errors.precio_oferta }}
                                </p>
                                <p v-else class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Debe ser menor que el precio normal ({{ Number(ofertaProducto.precio).toFixed(2) }} €).
                                </p>
                            </div>

                            <div v-else class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 p-3 text-sm text-amber-800 dark:text-amber-200">
                                Se enviará una solicitud para desactivar la oferta de este producto.
                            </div>

                            <div class="mt-5 flex justify-end gap-3">
                                <button type="button" @click="cerrarOferta"
                                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Cancelar
                                </button>
                                <button type="button" @click="submitOferta" :disabled="ofertaForm.processing"
                                        class="rounded-xl bg-primary-600 px-5 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60 transition-colors">
                                    {{ ofertaForm.processing ? 'Enviando...' : 'Enviar solicitud' }}
                                </button>
                            </div>
                        </div>
                    </div>

                </template>

                <!-- ════════════════ TAB SOLICITUDES ════════════════ -->
                <template v-if="tab === 'solicitudes'">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Mis solicitudes de cambio</h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ solicitudesFiltradas.length }} de {{ solicitudes.length }} solicitudes ·
                                Cada solicitud es revisada individualmente por el administrador
                            </p>
                        </div>

                        <!-- ── Barra filtros solicitudes ────────────────────────── -->
                        <div v-if="solicitudes.length > 0"
                             class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 px-6 py-4">
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-4">
                                <div class="relative lg:col-span-2">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                    </svg>
                                    <input v-model="solicitudesFiltros.busqueda" type="text" placeholder="Buscar producto o campo..."
                                           class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400" />
                                </div>
                                <select v-model="solicitudesFiltros.tipo"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Todos los tipos</option>
                                    <option value="create_producto">Crear producto</option>
                                    <option value="update_producto">Editar producto</option>
                                    <option value="delete_producto">Eliminar producto</option>
                                    <option value="update_tienda">Cambio tienda</option>
                                </select>
                                <select v-model="solicitudesFiltros.estado"
                                        class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400">
                                    <option value="todos">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="aprobado">Aprobado</option>
                                    <option value="rechazado">Rechazado</option>
                                </select>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="button" @click="solicitudesResetFiltros"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>

                        <div v-if="solicitudes.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            No hay solicitudes todavía
                        </div>
                        <div v-else-if="solicitudesFiltradas.length === 0" class="py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-3 h-12 w-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm">Ninguna solicitud coincide con los filtros</p>
                            <button type="button" @click="solicitudesResetFiltros"
                                    class="mt-2 text-xs font-medium text-primary-500 hover:text-primary-600">
                                Limpiar filtros
                            </button>
                        </div>
                        <div v-else class="divide-y divide-gray-50 dark:divide-gray-700">
                            <div v-for="s in solicitudesPaginadas" :key="s.id" class="px-6 py-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <!-- Badge tipo -->
                                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                                            <span :class="[solicitudEstadoCls(s.estado), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold']">
                                                {{ solicitudEstadoLabel(s.estado) }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                {{ s.tipo === 'create_producto' ? 'Crear producto' :
                                                   s.tipo === 'delete_producto' ? 'Eliminar producto' :
                                                   s.tipo === 'update_tienda'   ? 'Tienda' :
                                                   'Producto' }}
                                            </span>
                                            <span class="text-xs text-gray-400">{{ s.created_at }}</span>
                                        </div>
                                        <!-- Campo modificado -->
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            <template v-if="s.producto">{{ s.producto.nombre }} — </template>
                                            {{ s.label_campo }}
                                        </p>
                                        <!-- Antes / Después -->
                                        <div v-if="s.tipo !== 'create_producto' && s.tipo !== 'delete_producto'" class="mt-2 flex flex-wrap gap-3 text-xs">
                                            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-red-700 dark:text-red-300 max-w-xs truncate">
                                                <span class="font-semibold">Antes: </span>{{ s.valor_anterior?.value ?? '—' }}
                                            </div>
                                            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 px-3 py-1.5 text-green-700 dark:text-green-300 max-w-xs truncate">
                                                <span class="font-semibold">Nuevo: </span>{{ s.valor_nuevo?.value ?? '—' }}
                                            </div>
                                        </div>
                                        <!-- Crear producto: mostrar resumen -->
                                        <div v-if="s.tipo === 'create_producto' && s.valor_nuevo" class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            Nombre: {{ s.valor_nuevo.nombre }} · Precio: {{ s.valor_nuevo.precio }}€ · Stock: {{ s.valor_nuevo.stock }}
                                        </div>
                                        <!-- Motivo rechazo -->
                                        <p v-if="s.motivo_rechazo" class="mt-2 text-xs text-red-600 dark:text-red-400 italic">
                                            Motivo: {{ s.motivo_rechazo }}
                                        </p>
                                        <!-- Revisor -->
                                        <p v-if="s.revisor" class="mt-1 text-xs text-gray-400">
                                            Revisado por {{ s.revisor }} · {{ s.revisado_at }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Paginación solicitudes ───────────────────────────── -->
                        <div v-if="solicitudesFiltradas.length > 0 && solicitudesTotalPaginas > 1"
                             class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 dark:border-gray-700 px-6 py-3">
                            <p class="text-xs text-gray-500">
                                Página {{ solicitudesPagina }} de {{ solicitudesTotalPaginas }}
                            </p>
                            <div class="flex items-center gap-1">
                                <button @click="solicitudesPagina = Math.max(1, solicitudesPagina - 1)"
                                        :disabled="solicitudesPagina === 1"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    <ChevronLeft class="h-3.5 w-3.5 inline" /> Anterior
                                </button>
                                <span class="rounded-lg bg-primary-50 dark:bg-primary-900/30 px-3 py-1.5 text-xs font-bold text-primary-600 dark:text-primary-300">
                                    {{ solicitudesPagina }}
                                </span>
                                <button @click="solicitudesPagina = Math.min(solicitudesTotalPaginas, solicitudesPagina + 1)"
                                        :disabled="solicitudesPagina === solicitudesTotalPaginas"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                    Siguiente <ChevronRight class="h-3.5 w-3.5 inline" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ════════════════ TAB BENEFICIOS ════════════════ -->
                <template v-if="tab === 'beneficios'">

                    <!-- Resumen tarjetas -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Ingresos brutos totales</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.totalIngresos.toFixed(2) }} €</p>
                            <p class="mt-1 text-xs text-gray-400">Suma de todos los pedidos</p>
                        </div>
                        <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 p-5 shadow-sm">
                            <p class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wide">Comisión Rustikan ({{ stats.comisionPct }}%)</p>
                            <p class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">–{{ stats.totalComision.toFixed(2) }} €</p>
                            <p class="mt-1 text-xs text-red-400">Plataforma de intermediación</p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 p-5 shadow-sm">
                            <p class="text-xs font-medium text-white/80 uppercase tracking-wide">Beneficio neto total</p>
                            <p class="mt-2 text-2xl font-bold text-white">{{ stats.totalNeto.toFixed(2) }} €</p>
                            <p class="mt-1 text-xs text-white/70">Este mes neto: {{ stats.netMesActual.toFixed(2) }} €</p>
                        </div>
                    </div>

                    <!-- Tabla mensual -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Desglose mensual (últimos 12 meses)</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Comisión del {{ stats.comisionPct }}% aplicada sobre ingresos brutos</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">Filtrar mes:</label>
                                <select
                                    v-model="selectedMes"
                                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                                    <option value="">Todos</option>
                                    <option v-for="fila in beneficiosPorMes" :key="fila.mes" :value="fila.mes" class="capitalize">
                                        {{ fila.mes }}
                                    </option>
                                </select>
                                <a :href="route('owner.exportar.beneficios')" target="_blank"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-2.5 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                                    <FileText class="h-3.5 w-3.5" /> PDF
                                </a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Mes</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Bruto</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-red-400 uppercase tracking-wide">Comisión</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-green-500 uppercase tracking-wide">Neto</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="fila in beneficiosFiltrados" :key="fila.mes"
                                        :class="['hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors',
                                            fila.mes === mesActualStr ? 'bg-primary-50/40 dark:bg-primary-900/10 font-semibold' : '']">
                                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-200 capitalize flex items-center gap-2">
                                            {{ fila.mes }}
                                            <span v-if="fila.mes === mesActualStr" class="rounded-full bg-primary-100 dark:bg-primary-900/40 px-1.5 py-0.5 text-[10px] font-bold text-primary-600 dark:text-primary-300">Actual</span>
                                        </td>
                                        <td class="px-6 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ fila.bruto.toFixed(2) }} €</td>
                                        <td class="px-6 py-3 text-sm text-right text-red-500 dark:text-red-400">–{{ fila.comision.toFixed(2) }} €</td>
                                        <td class="px-6 py-3 text-sm text-right font-semibold text-green-600 dark:text-green-400">{{ fila.neto.toFixed(2) }} €</td>
                                    </tr>
                                    <tr v-if="beneficiosFiltrados.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-400">Sin datos para este mes</td>
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
