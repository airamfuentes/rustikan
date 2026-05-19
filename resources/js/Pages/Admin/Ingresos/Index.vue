<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Ingresos y Estadísticas</h2>
                <Link :href="route('admin.dashboard')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filtros de Fecha -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Desde</label>
                            <input
                                v-model="form.fecha_desde"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Hasta</label>
                            <input
                                v-model="form.fecha_hasta"
                                type="date"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="aplicarFiltros"
                                class="w-full rounded-lg bg-primary-600 px-4 py-2 text-white font-semibold hover:bg-primary-700"
                            >
                                Aplicar Filtros
                            </button>
                        </div>
                    </div>
                    <!-- Botones de descarga -->
                    <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 dark:border-gray-700 pt-4">
                        <button @click="descargarPdf('ingresos')"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                            <FileText class="h-3.5 w-3.5" /> Ingresos mensuales (PDF)
                        </button>
                        <button @click="descargarPdf('tiendas')"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                            <FileText class="h-3.5 w-3.5" /> Ingresos por tienda (PDF)
                        </button>
                        <button @click="descargarPdf('pedidos')"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-1.5 text-xs font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                            <FileText class="h-3.5 w-3.5" /> Todos los pedidos (PDF)
                        </button>
                    </div>
                </div>

                <!-- Estadísticas Principales -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos Totales</p>
                                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ formatPrice(stats.ingresos_totales) }}€</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-green-600">
                                <DollarSign class="h-6 w-6" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Periodo Actual</p>
                                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ formatPrice(stats.ingresos_periodo) }}€</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <Calendar class="h-6 w-6" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket Promedio</p>
                                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ formatPrice(stats.ticket_promedio) }}€</p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                                <Tag class="h-6 w-6" />
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Este Mes</p>
                                <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ formatPrice(stats.ingresos_mes_actual) }}€</p>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                    <component :is="stats.crecimiento_mensual >= 0 ? TrendingUp : TrendingDown"
                                        :class="stats.crecimiento_mensual >= 0 ? 'h-3 w-3 text-green-500' : 'h-3 w-3 text-red-500'" />
                                    {{ Math.abs(stats.crecimiento_mensual).toFixed(1) }}% vs mes anterior
                                </p>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                                <TrendingUp class="h-6 w-6" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ingresos Mensuales (Gráfico de Tabla) -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><BarChart2 class="h-5 w-5 text-gray-500" /> Ingresos por Mes (Últimos 12 meses)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Mes</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Pedidos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Ingresos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Gráfico</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <tr v-for="mes in ingresos_mensuales" :key="mes.mes">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ formatMes(mes.mes) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-400">
                                        {{ mes.cantidad }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatPrice(mes.total) }}€
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-4 rounded-full bg-green-500" :style="{ width: calcularAnchoGrafico(mes.total) }"></div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Top Tiendas -->
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><Store class="h-5 w-5 text-gray-500" /> Top 10 Tiendas por Ingresos</h3>
                        <div class="space-y-3">
                            <div v-for="(tienda, index) in ingresos_por_tienda" :key="tienda.id" class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-sm">
                                    {{ index + 1 }}
                                </div>
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" :alt="sinEmojis(tienda.nombre)" class="h-10 w-10 rounded-full object-cover">
                                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-semibold">
                                        {{ sinEmojis(tienda.nombre).charAt(0) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ sinEmojis(tienda.nombre) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ tienda.total_pedidos }} pedidos</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ formatPrice(tienda.total_ingresos) }}€</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos por Categoría -->
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><FolderOpen class="h-5 w-5 text-gray-500" /> Ingresos por Categoría</h3>
                        <div class="space-y-3">
                            <div v-for="categoria in ingresos_por_categoria" :key="categoria.categoria" class="rounded-lg border dark:border-gray-700 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <img v-if="categoria.imagen" :src="`/storage/${categoria.imagen}`" :alt="categoria.categoria" class="h-8 w-8 rounded-full object-cover" />
                                        <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700 text-sm">{{ categoria.icono || '🏷️' }}</div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ categoria.categoria }}</span>
                                    </div>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatPrice(categoria.total_ingresos) }}€</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-4 rounded-full bg-gradient-to-r from-green-400 to-green-600" :style="{ width: calcularPorcentajeCategoria(categoria.total_ingresos) }"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ categoria.total_pedidos }} pedidos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos Más Vendidos -->
                <div class="mt-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><Star class="h-5 w-5 text-yellow-400 fill-yellow-400" /> Top 10 Productos Más Vendidos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Producto</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Unidades</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <tr v-for="(producto, index) in productos_top" :key="producto.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-500 text-white font-bold text-sm">
                                            {{ index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img v-if="producto.imagen" :src="`/storage/${producto.imagen}`" :alt="sinEmojis(producto.nombre)" class="h-10 w-10 rounded object-cover">
                                            <div v-else class="flex h-10 w-10 items-center justify-center rounded bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500">
                                                <Package class="h-5 w-5" />
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sinEmojis(producto.nombre) }}</span>
                                                <p v-if="producto.tienda_nombre" class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ sinEmojis(producto.tienda_nombre) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <span class="inline-flex rounded-full bg-blue-100 dark:bg-blue-900/30 px-3 py-1 text-sm font-semibold text-blue-800 dark:text-blue-300">
                                            {{ producto.total_vendido }} unidades
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-green-600 dark:text-green-400">
                                        {{ formatPrice(producto.total_ingresos) }}€
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import { ArrowLeft, DollarSign, Calendar, Tag, TrendingUp, TrendingDown, BarChart2, Store, FolderOpen, Star, Package, FileText } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    ingresos_mensuales: Array,
    ingresos_por_tienda: Array,
    ingresos_por_categoria: Array,
    productos_top: Array,
    filters: Object,
});

// Elimina emojis / pictogramas que algún productor pudiera haber metido en
// el nombre del producto en BD. La UI debe mostrar siempre texto limpio.
const sinEmojis = (str) =>
    (str ?? '').replace(/\p{Extended_Pictographic}/gu, '').replace(/\s+/g, ' ').trim();

const form = reactive({
    fecha_desde: props.filters.fecha_desde,
    fecha_hasta: props.filters.fecha_hasta,
});

const aplicarFiltros = () => {
    router.get(route('admin.ingresos.index'), form, {
        preserveState: true,
    });
};

const descargarPdf = (tipo) => {
    const params = new URLSearchParams({ fecha_desde: form.fecha_desde, fecha_hasta: form.fecha_hasta });
    const rutas = {
        ingresos: route('admin.exportar.ingresos') + '?' + params,
        tiendas:  route('admin.exportar.ingresos-tiendas') + '?' + params,
        pedidos:  route('admin.exportar.pedidos'),
    };
    // Abrir en una pestaña nueva para que el usuario pueda imprimir / guardar como PDF
    window.open(rutas[tipo], '_blank');
};

const formatPrice = (price) => {
    return parseFloat(price || 0).toFixed(2);
};

const formatMes = (mesStr) => {
    const [year, month] = mesStr.split('-');
    const fecha = new Date(year, month - 1);
    return fecha.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' });
};

const maxIngresoMensual = computed(() => {
    if (!props.ingresos_mensuales || props.ingresos_mensuales.length === 0) return 1;
    return Math.max(...props.ingresos_mensuales.map(m => parseFloat(m.total)));
});

const calcularAnchoGrafico = (total) => {
    const porcentaje = (parseFloat(total) / maxIngresoMensual.value) * 100;
    return `${porcentaje}%`;
};

const totalIngresosCategoria = computed(() => {
    if (!props.ingresos_por_categoria || props.ingresos_por_categoria.length === 0) return 1;
    return props.ingresos_por_categoria.reduce((sum, cat) => sum + parseFloat(cat.total_ingresos), 0);
});

const calcularPorcentajeCategoria = (ingresos) => {
    const porcentaje = (parseFloat(ingresos) / totalIngresosCategoria.value) * 100;
    return `${porcentaje}%`;
};
</script>
