<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { ArrowLeft, PackagePlus, AlertTriangle, Package, Store, Search } from 'lucide-vue-next';

const props = defineProps({
    tiendas:   { type: Array, required: true },
    productos: { type: Array, required: true },
    filters:   { type: Object, default: () => ({}) },
});

const tiendaId       = ref(props.filters.tienda_id ?? props.tiendas[0]?.id ?? '');
const productosList  = ref([...props.productos]);
const loadingProds   = ref(false);
const searchProd     = ref('');

const form = useForm({
    producto_id:      '',
    cantidad_entrada: 1,
    numero_documento: '',
    proveedor:        '',
    notas:            '',
});

// Cuando cambia la tienda, cargar productos via JSON
watch(tiendaId, async (id) => {
    if (!id) { productosList.value = []; return; }
    loadingProds.value = true;
    form.producto_id   = '';
    searchProd.value   = '';
    try {
        const res = await fetch(route('supplier.entradas.productos', id), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        productosList.value = await res.json();
    } catch {
        productosList.value = [];
    } finally {
        loadingProds.value = false;
    }
});

const productosFiltrados = computed(() => {
    const q = searchProd.value.toLowerCase();
    if (!q) return productosList.value;
    return productosList.value.filter(p => p.nombre.toLowerCase().includes(q));
});

const productoSeleccionado = computed(() =>
    productosList.value.find(p => p.id == form.producto_id) ?? null
);

const stockPreview = computed(() => {
    if (!productoSeleccionado.value) return null;
    return (productoSeleccionado.value.stock ?? 0) + Number(form.cantidad_entrada || 0);
});

const tiendaActual = computed(() => props.tiendas.find(t => t.id == tiendaId.value) ?? null);

const submit = () => {
    form.post(route('supplier.entradas.store'));
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

                <!-- Card: Selección de tienda y producto -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Producto</h2>

                    <!-- Tienda -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tienda</label>
                        <div class="relative">
                            <Store class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <select
                                v-model="tiendaId"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            >
                                <option value="" disabled>Selecciona una tienda…</option>
                                <option v-for="t in tiendas" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Buscador de producto -->
                    <div v-if="tiendaId">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Buscar producto</label>
                        <div class="relative mb-2">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <input
                                v-model="searchProd"
                                type="text"
                                placeholder="Filtrar productos…"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            />
                        </div>

                        <!-- Lista de productos -->
                        <div v-if="loadingProds" class="py-6 text-center text-sm text-gray-400">Cargando productos…</div>
                        <div v-else-if="productosFiltrados.length === 0" class="py-6 text-center text-sm text-gray-400">No hay productos</div>
                        <div v-else class="grid gap-2 max-h-64 overflow-y-auto pr-1">
                            <label
                                v-for="prod in productosFiltrados"
                                :key="prod.id"
                                :class="[
                                    'flex items-center gap-3 rounded-xl border p-3 cursor-pointer transition-colors',
                                    form.producto_id == prod.id
                                        ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'
                                ]"
                            >
                                <input type="radio" :value="prod.id" v-model="form.producto_id" class="sr-only" />
                                <!-- Imagen -->
                                <div class="h-12 w-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
                                    <img
                                        v-if="prod.imagen"
                                        :src="`/storage/${prod.imagen}`"
                                        :alt="prod.nombre"
                                        class="h-full w-full object-cover"
                                        @error="(e) => e.target.style.display='none'"
                                    />
                                    <div v-else class="h-full w-full flex items-center justify-center">
                                        <Package class="h-5 w-5 text-gray-400" />
                                    </div>
                                </div>
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ prod.nombre }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Stock actual: <strong>{{ prod.stock }} {{ prod.unidad }}</strong></span>
                                        <span v-if="prod.stock === 0" class="text-xs text-red-500 font-semibold">• Agotado</span>
                                        <span v-else-if="prod.stock <= prod.stock_minimo" class="text-xs text-amber-500 font-semibold flex items-center gap-0.5">
                                            <AlertTriangle class="h-3 w-3" /> Bajo
                                        </span>
                                    </div>
                                </div>
                                <!-- Seleccionado -->
                                <div
                                    :class="[
                                        'h-4 w-4 rounded-full border-2 shrink-0 transition-colors',
                                        form.producto_id == prod.id
                                            ? 'border-primary-500 bg-primary-500'
                                            : 'border-gray-300 dark:border-gray-600'
                                    ]"
                                />
                            </label>
                        </div>
                        <p v-if="form.errors.producto_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.producto_id }}</p>
                    </div>
                </div>

                <!-- Card: Stock preview + cantidad -->
                <div v-if="productoSeleccionado" class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cantidad de entrada</h2>

                    <!-- Preview stock -->
                    <div class="flex items-center gap-4 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-center flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Stock actual</p>
                            <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ productoSeleccionado.stock }}</p>
                            <p class="text-xs text-gray-400">{{ productoSeleccionado.unidad }}</p>
                        </div>
                        <div class="text-2xl font-bold text-green-500">+</div>
                        <div class="text-center flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Entrada</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ form.cantidad_entrada || 0 }}</p>
                            <p class="text-xs text-gray-400">{{ productoSeleccionado.unidad }}</p>
                        </div>
                        <div class="text-2xl font-bold text-gray-400">=</div>
                        <div class="text-center flex-1">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Stock nuevo</p>
                            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ stockPreview }}</p>
                            <p class="text-xs text-gray-400">{{ productoSeleccionado.unidad }}</p>
                        </div>
                    </div>

                    <!-- Input cantidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Cantidad ({{ productoSeleccionado.unidad }})
                        </label>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="form.cantidad_entrada = Math.max(1, Number(form.cantidad_entrada) - 1)"
                                class="h-10 w-10 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-lg font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >−</button>
                            <input
                                v-model.number="form.cantidad_entrada"
                                type="number"
                                min="1"
                                class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-center text-sm text-gray-900 dark:text-white focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            />
                            <button
                                type="button"
                                @click="form.cantidad_entrada = Number(form.cantidad_entrada) + 1"
                                class="h-10 w-10 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-lg font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            >+</button>
                        </div>
                        <p v-if="form.errors.cantidad_entrada" class="mt-1.5 text-xs text-red-500">{{ form.errors.cantidad_entrada }}</p>
                    </div>
                </div>

                <!-- Card: Datos del documento -->
                <div class="rounded-2xl bg-white dark:bg-gray-800 shadow p-6 space-y-5">
                    <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Datos del albarán (opcional)</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Número documento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Número de albarán / factura</label>
                            <input
                                v-model="form.numero_documento"
                                type="text"
                                placeholder="Ej: ALB-2026-001"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            />
                        </div>

                        <!-- Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Proveedor</label>
                            <input
                                v-model="form.proveedor"
                                type="text"
                                placeholder="Nombre del proveedor"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800"
                            />
                        </div>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notas</label>
                        <textarea
                            v-model="form.notas"
                            rows="3"
                            placeholder="Observaciones sobre esta entrada…"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 py-2.5 px-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800 resize-none"
                        />
                    </div>
                </div>

                <!-- Acciones -->
                <div class="flex items-center justify-end gap-3">
                    <Link
                        :href="route('supplier.entradas.index')"
                        class="rounded-xl border border-gray-200 dark:border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        Cancelar
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.producto_id || form.cantidad_entrada < 1"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <PackagePlus class="h-4 w-4" />
                        {{ form.processing ? 'Guardando…' : 'Registrar entrada' }}
                    </button>
                </div>

            </form>
        </div>
    </LayoutSupplier>
</template>
