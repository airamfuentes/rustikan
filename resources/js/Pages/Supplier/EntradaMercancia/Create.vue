<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { ArrowLeft, PackagePlus, AlertTriangle, Package, Store, Search, Plus, Minus, Trash2, FileText, Truck, X } from 'lucide-vue-next';
import { imgSrc } from '@/Composables/useImgSrc';

const props = defineProps({
    tiendas:   { type: Array, required: true },
    productos: { type: Array, required: true },
    filters:   { type: Object, default: () => ({}) },
});

// ── Tienda search ──────────────────────────────────────────────────────────
const tiendaSearch   = ref('');
const tiendaSelec    = ref(null); // objeto tienda seleccionada
const showTiendaList = ref(false);
const tiendaInput    = ref(null);

const tiendasFiltradas = computed(() => {
    const q = tiendaSearch.value.toLowerCase().trim();
    if (!q) return props.tiendas;
    return props.tiendas.filter(t => t.nombre.toLowerCase().includes(q));
});

const seleccionarTienda = async (tienda) => {
    tiendaSelec.value    = tienda;
    tiendaSearch.value   = tienda.nombre;
    showTiendaList.value = false;
    // Auto-fill proveedor
    proveedor.value      = tienda.nombre;
    // Load productos
    await cargarProductos(tienda.id);
};

const limpiarTienda = () => {
    tiendaSelec.value    = null;
    tiendaSearch.value   = '';
    proveedor.value      = '';
    productosList.value  = [];
    productosSelec.value = [];
    searchProd.value     = '';
    showTiendaList.value = false;
};

// Click fuera cierra el dropdown
const onTiendaBlur = () => {
    setTimeout(() => { showTiendaList.value = false; }, 150);
};

// ── Productos ──────────────────────────────────────────────────────────────
const productosList  = ref([...props.productos]);
const loadingProds   = ref(false);
const searchProd     = ref('');

// productosSelec: [{ producto, cantidad }]
const productosSelec = ref([]);

const cargarProductos = async (tiendaId) => {
    if (!tiendaId) { productosList.value = []; return; }
    loadingProds.value = true;
    productosSelec.value = [];
    searchProd.value = '';
    try {
        const res = await fetch(route('supplier.entradas.productos', tiendaId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        productosList.value = await res.json();
    } catch {
        productosList.value = [];
    } finally {
        loadingProds.value = false;
    }
};

const productosFiltrados = computed(() => {
    const q = searchProd.value.toLowerCase().trim();
    const selIds = new Set(productosSelec.value.map(p => p.producto.id));
    const lista = q
        ? productosList.value.filter(p => p.nombre.toLowerCase().includes(q))
        : productosList.value;
    return lista.filter(p => !selIds.has(p.id));
});

const agregarProducto = (prod) => {
    if (productosSelec.value.find(p => p.producto.id === prod.id)) return;
    productosSelec.value.push({ producto: prod, cantidad: 1 });
    searchProd.value = '';
};

const quitarProducto = (idx) => {
    productosSelec.value.splice(idx, 1);
};

const ajustarCantidad = (idx, delta) => {
    const item = productosSelec.value[idx];
    item.cantidad = Math.max(1, item.cantidad + delta);
};

// ── Albarán ────────────────────────────────────────────────────────────────
const numero_documento = ref('');
const proveedor        = ref('');
const notas            = ref('');

// ── Errors ─────────────────────────────────────────────────────────────────
const errors   = ref({});
const sending  = ref(false);

const canSubmit = computed(() =>
    tiendaSelec.value &&
    productosSelec.value.length > 0 &&
    numero_documento.value.trim() !== '' &&
    !sending.value
);

const submit = async () => {
    errors.value = {};
    if (!tiendaSelec.value) { errors.value.tienda = 'Selecciona una tienda.'; return; }
    if (productosSelec.value.length === 0) { errors.value.productos = 'Agrega al menos un producto.'; return; }
    if (!numero_documento.value.trim()) { errors.value.numero_documento = 'El número de albarán es obligatorio.'; return; }

    sending.value = true;
    router.post(route('supplier.entradas.store'), {
        tienda_id:        tiendaSelec.value.id,
        productos:        productosSelec.value.map(p => ({ producto_id: p.producto.id, cantidad: p.cantidad })),
        numero_documento: numero_documento.value.trim(),
        proveedor:        proveedor.value.trim(),
        notas:            notas.value.trim(),
    }, {
        onError: (errs) => { errors.value = errs; sending.value = false; },
        onFinish: () => { sending.value = false; },
    });
};
</script>

<template>
    <LayoutSupplier>
        <div class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-3xl mx-auto">

            <!-- Cabecera -->
            <div class="flex items-center gap-3">
                <Link
                    :href="route('supplier.entradas.index')"
                    class="flex items-center justify-center h-9 w-9 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-600 dark:text-gray-400"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nueva entrada de mercancía</h1>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Registra la llegada de stock al almacén</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- Card: Albarán (primero) -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                        <FileText class="h-4 w-4 text-primary-500" /> Datos del albarán
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Número albarán -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Número de albarán / factura <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="numero_documento"
                                type="text"
                                placeholder="Ej: ALB-2026-001"
                                class="w-full rounded-xl border py-2.5 px-3 text-sm focus:outline-none focus:ring-2 transition-colors"
                                :class="errors.numero_documento
                                    ? 'border-red-400 bg-red-50 dark:bg-red-900/10 text-gray-900 dark:text-white focus:border-red-400 focus:ring-red-200'
                                    : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:ring-primary-200 dark:focus:ring-primary-800'"
                            />
                            <p v-if="errors.numero_documento" class="mt-1 text-xs text-red-500">{{ errors.numero_documento }}</p>
                        </div>

                        <!-- Proveedor (auto-fill desde tienda) -->
                        <div>
                            <label class="flex items-center gap-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                <Truck class="h-3.5 w-3.5 text-gray-400" /> Proveedor
                            </label>
                            <input
                                v-model="proveedor"
                                type="text"
                                placeholder="Se rellena automáticamente con la tienda"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            />
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notas</label>
                        <textarea
                            v-model="notas"
                            rows="2"
                            placeholder="Observaciones sobre esta entrada…"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 resize-none"
                        />
                    </div>
                </div>

                <!-- Card: Tienda -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                        <Store class="h-4 w-4 text-primary-500" /> Tienda
                    </h2>

                    <div class="relative">
                        <!-- Input de búsqueda tienda -->
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                            <input
                                ref="tiendaInput"
                                v-model="tiendaSearch"
                                type="text"
                                placeholder="Buscar tienda…"
                                autocomplete="off"
                                @focus="showTiendaList = true"
                                @blur="onTiendaBlur"
                                class="w-full rounded-xl border py-2.5 pl-9 pr-10 text-sm focus:outline-none focus:ring-2 transition-colors"
                                :class="[
                                    errors.tienda ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700',
                                    'text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:ring-primary-200 dark:focus:ring-primary-800'
                                ]"
                            />
                            <button v-if="tiendaSelec" type="button" @click="limpiarTienda"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="errors.tienda" class="mt-1 text-xs text-red-500">{{ errors.tienda }}</p>

                        <!-- Dropdown tiendas -->
                        <div v-if="showTiendaList && tiendasFiltradas.length > 0"
                            class="absolute z-10 mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg max-h-60 overflow-y-auto">
                            <button
                                v-for="t in tiendasFiltradas"
                                :key="t.id"
                                type="button"
                                @mousedown.prevent="seleccionarTienda(t)"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                :class="tiendaSelec?.id === t.id ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400' : 'text-gray-900 dark:text-white'"
                            >
                                <Store class="h-4 w-4 text-gray-400 shrink-0" />
                                {{ t.nombre }}
                            </button>
                        </div>
                        <div v-if="showTiendaList && tiendasFiltradas.length === 0"
                            class="absolute z-10 mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-lg px-4 py-3 text-sm text-gray-400">
                            No se encontraron tiendas
                        </div>
                    </div>

                    <!-- Tienda seleccionada badge -->
                    <div v-if="tiendaSelec" class="flex items-center gap-2 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 px-4 py-2.5">
                        <Store class="h-4 w-4 text-primary-600 dark:text-primary-400 shrink-0" />
                        <span class="text-sm font-medium text-primary-700 dark:text-primary-400">{{ tiendaSelec.nombre }}</span>
                        <span class="ml-auto text-xs text-primary-500">Seleccionada</span>
                    </div>
                </div>

                <!-- Card: Productos -->
                <div v-if="tiendaSelec" class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-2">
                        <Package class="h-4 w-4 text-primary-500" /> Productos
                    </h2>

                    <!-- Buscador producto -->
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                        <input
                            v-model="searchProd"
                            type="text"
                            placeholder="Buscar y añadir producto…"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                        />
                    </div>

                    <!-- Lista para agregar -->
                    <div v-if="loadingProds" class="py-4 text-center text-sm text-gray-400">Cargando productos…</div>

                    <div v-else-if="searchProd && productosFiltrados.length > 0"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden max-h-52 overflow-y-auto">
                        <button
                            v-for="prod in productosFiltrados"
                            :key="prod.id"
                            type="button"
                            @click="agregarProducto(prod)"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0 group"
                        >
                            <div class="h-9 w-9 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                <img v-if="prod.imagen" :src="imgSrc(prod.imagen)" :alt="prod.nombre" class="h-full w-full object-cover" @error="(e) => e.target.style.display='none'" />
                                <div v-else class="h-full w-full flex items-center justify-center"><Package class="h-4 w-4 text-gray-400" /></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ prod.nombre }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Stock: {{ prod.stock }} {{ prod.unidad }}</p>
                            </div>
                            <Plus class="h-4 w-4 text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity shrink-0" />
                        </button>
                    </div>

                    <div v-else-if="searchProd && productosFiltrados.length === 0" class="rounded-xl border border-gray-200 dark:border-gray-600 py-4 text-center text-sm text-gray-400">
                        No hay más productos que coincidan
                    </div>

                    <!-- Error productos -->
                    <p v-if="errors.productos" class="text-xs text-red-500">{{ errors.productos }}</p>

                    <!-- Productos seleccionados -->
                    <div v-if="productosSelec.length > 0" class="space-y-3">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ productosSelec.length }} producto{{ productosSelec.length !== 1 ? 's' : '' }} en este albarán
                        </p>

                        <div
                            v-for="(item, idx) in productosSelec"
                            :key="item.producto.id"
                            class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/30 p-4"
                        >
                            <div class="flex items-center gap-3">
                                <!-- Imagen -->
                                <div class="h-11 w-11 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                    <img v-if="item.producto.imagen" :src="imgSrc(item.producto.imagen)" :alt="item.producto.nombre" class="h-full w-full object-cover" @error="(e) => e.target.style.display='none'" />
                                    <div v-else class="h-full w-full flex items-center justify-center"><Package class="h-5 w-5 text-gray-400" /></div>
                                </div>
                                <!-- Nombre + stock -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ item.producto.nombre }}</p>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        <span>Stock actual: <strong>{{ item.producto.stock }}</strong></span>
                                        <span class="text-green-600 dark:text-green-400">→ nuevo: <strong>{{ item.producto.stock + item.cantidad }}</strong></span>
                                        <span class="text-gray-400">{{ item.producto.unidad }}</span>
                                        <span v-if="item.producto.stock === 0" class="text-red-500 font-semibold">Agotado</span>
                                        <span v-else-if="item.producto.stock <= item.producto.stock_minimo" class="text-amber-500 font-semibold flex items-center gap-0.5">
                                            <AlertTriangle class="h-3 w-3" /> Bajo
                                        </span>
                                    </div>
                                </div>
                                <!-- Quitar -->
                                <button type="button" @click="quitarProducto(idx)"
                                    class="flex items-center justify-center h-8 w-8 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors shrink-0">
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>

                            <!-- Control cantidad + preview -->
                            <div class="mt-3 flex items-center gap-3">
                                <button type="button" @click="ajustarCantidad(idx, -1)"
                                    class="h-9 w-9 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors flex items-center justify-center">
                                    <Minus class="h-4 w-4" />
                                </button>
                                <input
                                    v-model.number="item.cantidad"
                                    type="number"
                                    min="1"
                                    class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 px-3 text-center text-sm font-semibold text-gray-900 dark:text-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                                    @change="item.cantidad = Math.max(1, item.cantidad)"
                                />
                                <button type="button" @click="ajustarCantidad(idx, 1)"
                                    class="h-9 w-9 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors flex items-center justify-center">
                                    <Plus class="h-4 w-4" />
                                </button>
                                <div class="ml-auto flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">{{ item.producto.stock }}</span>
                                    <span class="text-green-500 font-bold">+{{ item.cantidad }}</span>
                                    <span class="text-gray-400">=</span>
                                    <span class="font-bold text-primary-600 dark:text-primary-400">{{ item.producto.stock + item.cantidad }}</span>
                                    <span class="text-xs text-gray-400">{{ item.producto.unidad }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="!loadingProds" class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 py-8 text-center">
                        <Package class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600 mb-2" />
                        <p class="text-sm text-gray-400">Busca y añade productos arriba</p>
                    </div>
                </div>

                <!-- Resumen albarán -->
                <div v-if="productosSelec.length > 0" class="rounded-2xl bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4">
                    <p class="text-sm font-semibold text-primary-700 dark:text-primary-400 mb-2">Resumen del albarán</p>
                    <div class="space-y-1">
                        <div class="flex justify-between text-sm text-primary-600 dark:text-primary-300">
                            <span>Albarán</span>
                            <span class="font-medium">{{ numero_documento || '—' }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-primary-600 dark:text-primary-300">
                            <span>Tienda</span>
                            <span class="font-medium">{{ tiendaSelec?.nombre || '—' }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-primary-600 dark:text-primary-300">
                            <span>Productos</span>
                            <span class="font-medium">{{ productosSelec.length }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-primary-600 dark:text-primary-300">
                            <span>Unidades totales</span>
                            <span class="font-bold">{{ productosSelec.reduce((s, p) => s + p.cantidad, 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex items-center justify-end gap-3 pb-4">
                    <Link
                        :href="route('supplier.entradas.index')"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <PackagePlus class="h-4 w-4" />
                        {{ sending ? 'Guardando…' : 'Registrar entrada' }}
                    </button>
                </div>

            </form>
        </div>
    </LayoutSupplier>
</template>
