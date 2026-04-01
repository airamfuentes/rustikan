<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">💰 Ingresos y Estadísticas</h2>
                <Link :href="route('admin.dashboard')" class="rounded-lg bg-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filtros de Fecha -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Desde</label>
                            <input
                                v-model="form.fecha_desde"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Hasta</label>
                            <input
                                v-model="form.fecha_hasta"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
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
                </div>

                <!-- Estadísticas Principales -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-gradient-to-br from-green-500 to-green-700 p-6 shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Ingresos Totales</p>
                                <p class="mt-2 text-3xl font-bold">{{ formatPrice(stats.ingresos_totales) }}€</p>
                            </div>
                            <div class="text-4xl">💰</div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-blue-500 to-blue-700 p-6 shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Periodo Actual</p>
                                <p class="mt-2 text-3xl font-bold">{{ formatPrice(stats.ingresos_periodo) }}€</p>
                            </div>
                            <div class="text-4xl">📅</div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-purple-500 to-purple-700 p-6 shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Ticket Promedio</p>
                                <p class="mt-2 text-3xl font-bold">{{ formatPrice(stats.ticket_promedio) }}€</p>
                            </div>
                            <div class="text-4xl">🎫</div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-orange-500 to-orange-700 p-6 shadow-lg text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium opacity-90">Este Mes</p>
                                <p class="mt-2 text-3xl font-bold">{{ formatPrice(stats.ingresos_mes_actual) }}€</p>
                                <p class="mt-1 text-xs opacity-75">
                                    {{ stats.crecimiento_mensual >= 0 ? '↗' : '↘' }}
                                    {{ Math.abs(stats.crecimiento_mensual).toFixed(1) }}% vs mes anterior
                                </p>
                            </div>
                            <div class="text-4xl">📈</div>
                        </div>
                    </div>
                </div>

                <!-- Ingresos Mensuales (Gráfico de Tabla) -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-xl font-bold text-gray-900">📊 Ingresos por Mes (Últimos 12 meses)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Mes</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Pedidos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ingresos</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Gráfico</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="mes in ingresos_mensuales" :key="mes.mes">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ formatMes(mes.mes) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500">
                                        {{ mes.cantidad }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
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
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-xl font-bold text-gray-900">🏪 Top 10 Tiendas por Ingresos</h3>
                        <div class="space-y-3">
                            <div v-for="(tienda, index) in ingresos_por_tienda" :key="tienda.id" class="flex items-center gap-3 rounded-lg border p-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-sm">
                                    {{ index + 1 }}
                                </div>
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" :alt="tienda.nombre" class="h-10 w-10 rounded-full object-cover">
                                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-600 font-semibold">
                                        {{ tienda.nombre.charAt(0) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ tienda.nombre }}</p>
                                    <p class="text-xs text-gray-500">{{ tienda.total_pedidos }} pedidos</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-green-600">{{ formatPrice(tienda.total_ingresos) }}€</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos por Categoría -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 text-xl font-bold text-gray-900">📂 Ingresos por Categoría</h3>
                        <div class="space-y-3">
                            <div v-for="categoria in ingresos_por_categoria" :key="categoria.categoria" class="rounded-lg border p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ categoria.icono }}</span>
                                        <span class="font-medium text-gray-900">{{ categoria.categoria }}</span>
                                    </div>
                                    <span class="text-lg font-bold text-green-600">{{ formatPrice(categoria.total_ingresos) }}€</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-full rounded-full bg-gray-200">
                                        <div class="h-4 rounded-full bg-gradient-to-r from-green-400 to-green-600" :style="{ width: calcularPorcentajeCategoria(categoria.total_ingresos) }"></div>
                                    </div>
                                    <span class="text-sm text-gray-500 whitespace-nowrap">{{ categoria.total_pedidos }} pedidos</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos Más Vendidos -->
                <div class="mt-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 text-xl font-bold text-gray-900">⭐ Top 10 Productos Más Vendidos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Producto</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Unidades</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="(producto, index) in productos_top" :key="producto.id">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-yellow-500 text-white font-bold text-sm">
                                            {{ index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img v-if="producto.imagen" :src="producto.imagen" :alt="producto.nombre" class="h-10 w-10 rounded object-cover">
                                            <div v-else class="flex h-10 w-10 items-center justify-center rounded bg-gray-200 text-gray-400">📦</div>
                                            <span class="text-sm font-medium text-gray-900">{{ producto.nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800">
                                            {{ producto.total_vendido }} unidades
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-green-600">
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

const props = defineProps({
    stats: Object,
    ingresos_mensuales: Array,
    ingresos_por_tienda: Array,
    ingresos_por_categoria: Array,
    productos_top: Array,
    filters: Object,
});

const form = reactive({
    fecha_desde: props.filters.fecha_desde,
    fecha_hasta: props.filters.fecha_hasta,
});

const aplicarFiltros = () => {
    router.get(route('admin.ingresos.index'), form, {
        preserveState: true,
    });
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
