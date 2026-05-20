<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Gestión de Tiendas</h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.dashboard')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                    <Link :href="route('admin.tiendas.create')" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                        Nueva Tienda
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tiendas</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Activas</div>
                        <div class="mt-2 text-3xl font-bold text-green-600">{{ stats.activas }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Inactivas</div>
                        <div class="mt-2 text-3xl font-bold text-red-600">{{ stats.inactivas }}</div>
                    </div>
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Visibles</div>
                        <div class="mt-2 text-3xl font-bold text-blue-600">{{ stats.visibles }}</div>
                    </div>
                </div>

                <!-- Búsqueda y Filtros -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                            <input
                                v-model="form.search"
                                @input="buscar"
                                type="text"
                                placeholder="Nombre, dirección o propietario..."
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                            <select
                                v-model="form.categoria_id"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todas</option>
                                <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                    {{ categoria.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                            <select
                                v-model="form.activa"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todas</option>
                                <option value="1">Activas</option>
                                <option value="0">Inactivas</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Visibilidad</label>
                            <select
                                v-model="form.visible"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todas</option>
                                <option value="1">Visibles</option>
                                <option value="0">Ocultas</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="limpiarFiltros"
                            class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Tabla de tiendas -->
                        <!-- Top scrollbar mirror -->
                        <div ref="topScroll" class="overflow-x-auto -mx-6 px-6 mb-0.5" style="height:12px;" @scroll="onTopScroll">
                            <div :style="{ width: tableWidth + 'px', height: '1px' }"></div>
                        </div>
                        <div ref="tableWrap" class="overflow-x-auto -mx-6 px-6" style="-webkit-overflow-scrolling: touch;" @scroll="onWrapScroll">
                            <table ref="tableEl" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Tienda</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Categoría</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Propietario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Productos</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Valoración</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    <tr v-for="tienda in tiendas.data" :key="tienda.id">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img v-if="tienda.logo" class="h-10 w-10 rounded-full object-cover" :src="`/storage/${tienda.logo}`" :alt="tienda.nombre" />
                                                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                                        {{ tienda.nombre.charAt(0) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ tienda.nombre }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ tienda.direccion }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-semibold leading-5 text-gray-800 dark:text-gray-200">
                                                <CategoriaIcono :slug="tienda.categoria.slug" :icono="tienda.categoria.icono" class="h-3 w-3" />
                                                {{ tienda.categoria.nombre }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ tienda.user.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ tienda.productos_count }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex flex-col gap-1">
                                                <button 
                                                    @click="toggleActive(tienda)"
                                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                    :class="tienda.activa ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'"
                                                >
                                                    {{ tienda.activa ? 'Activa' : 'Inactiva' }}
                                                </button>
                                                <button 
                                                    @click="toggleVisible(tienda)"
                                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                    :class="tienda.visible ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
                                                >
                                                    <Eye v-if="tienda.visible" class="inline h-3.5 w-3.5" /> <EyeOff v-else class="inline h-3.5 w-3.5" />
                                                    {{ tienda.visible ? 'Visible' : 'Oculta' }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm">
                                            <Link
                                                :href="route('admin.tiendas.resenas', tienda.id)"
                                                class="group inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 hover:text-yellow-700 dark:hover:text-yellow-300 transition-colors"
                                                :title="`Ver reseñas de ${tienda.nombre}`"
                                            >
                                                <Star class="h-3.5 w-3.5 fill-yellow-400 text-yellow-400" />
                                                <span class="font-semibold">{{ Number(tienda.valoracion).toFixed(1) }}</span>
                                                <span class="text-gray-400 group-hover:text-yellow-600 dark:group-hover:text-yellow-400">({{ tienda.total_resenas }})</span>
                                            </Link>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex justify-end gap-2">
                                                <Link :href="route('admin.tiendas.albaranes', tienda.id)" class="text-amber-600 hover:text-amber-900">
                                                    Albaranes
                                                </Link>
                                                <Link :href="route('admin.tiendas.productos.index', tienda.id)" class="text-green-600 hover:text-green-900">
                                                    Productos
                                                </Link>
                                                <Link :href="route('admin.tiendas.edit', tienda.id)" class="text-blue-600 hover:text-blue-900">
                                                    Editar
                                                </Link>
                                                <button @click="deleteTienda(tienda)" class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty state -->
                        <div v-if="tiendas.data.length === 0" class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay tiendas</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva tienda.</p>
                            <div class="mt-6">
                                <Link :href="route('admin.tiendas.create')" class="inline-flex items-center rounded-md bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                    Nueva Tienda
                                </Link>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div v-if="tiendas.last_page > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Mostrando <span class="font-medium">{{ tiendas.from }}</span> a <span class="font-medium">{{ tiendas.to }}</span> de <span class="font-medium">{{ tiendas.total }}</span> resultados
                            </p>
                            <div class="flex items-center gap-2">
                                <Link v-if="tiendas.prev_page_url" :href="tiendas.prev_page_url"
                                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    Anterior
                                </Link>
                                <span v-else class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed">Anterior</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Pág. {{ tiendas.current_page }} / {{ tiendas.last_page }}</span>
                                <Link v-if="tiendas.next_page_url" :href="tiendas.next_page_url"
                                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    Siguiente
                                </Link>
                                <span v-else class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed">Siguiente</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref, onMounted } from 'vue';
import { ArrowLeft, Eye, EyeOff, Star } from 'lucide-vue-next';
import CategoriaIcono from '@/Components/CategoriaIcono.vue';

const props = defineProps({
    tiendas: Object,
    stats: Object,
    categorias: Array,
    filters: Object,
});

// Dual scrollbar sync
const topScroll  = ref(null);
const tableWrap  = ref(null);
const tableEl    = ref(null);
const tableWidth = ref(0);

onMounted(() => {
    if (tableEl.value) {
        const ro = new ResizeObserver(() => {
            tableWidth.value = tableEl.value?.scrollWidth ?? 0;
        });
        ro.observe(tableEl.value);
        tableWidth.value = tableEl.value.scrollWidth;
    }
});

let syncingTop = false, syncingWrap = false;

const onTopScroll = () => {
    if (syncingWrap) return;
    syncingTop = true;
    if (tableWrap.value) tableWrap.value.scrollLeft = topScroll.value.scrollLeft;
    syncingTop = false;
};

const onWrapScroll = () => {
    if (syncingTop) return;
    syncingWrap = true;
    if (topScroll.value) topScroll.value.scrollLeft = tableWrap.value.scrollLeft;
    syncingWrap = false;
};

const form = reactive({
    search: props.filters?.search || '',
    categoria_id: props.filters?.categoria_id || '',
    activa: props.filters?.activa || '',
    visible: props.filters?.visible || '',
});

const buscar = () => {
    router.get(route('admin.tiendas.index'), form, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    form.search = '';
    form.categoria_id = '';
    form.activa = '';
    form.visible = '';
    buscar();
};

const toggleActive = (tienda) => {
    router.post(route('admin.tiendas.toggle-active', tienda.id), {}, {
        preserveScroll: true,
    });
};

const toggleVisible = (tienda) => {
    router.post(route('admin.tiendas.toggle-visible', tienda.id), {}, {
        preserveScroll: true,
    });
};

const deleteTienda = (tienda) => {
    if (confirm(`¿Estás seguro de eliminar la tienda "${tienda.nombre}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.tiendas.destroy', tienda.id));
    }
};
</script>
