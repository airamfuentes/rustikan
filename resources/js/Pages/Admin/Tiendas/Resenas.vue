<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-gray-200">
                    <Link :href="route('admin.tiendas.index')" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Tiendas</Link>
                    <span class="text-gray-300">/</span>
                    <span class="truncate">{{ tienda.nombre }}</span>
                    <span class="text-gray-300">/</span>
                    <span>Reseñas</span>
                </div>
                <Link :href="route('admin.tiendas.index')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <!-- ── Cabecera de la tienda ───────────────────────────────── -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" :alt="tienda.nombre"
                                 class="h-16 w-16 flex-shrink-0 rounded-full object-cover ring-2 ring-primary-100 dark:ring-primary-900/40" />
                            <div v-else class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-xl font-bold text-primary-600 dark:text-primary-400">
                                {{ tienda.nombre.charAt(0) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ tienda.nombre }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                    <CategoriaIcono v-if="tienda.categoria" :slug="tienda.categoria.slug" :icono="tienda.categoria.icono" class="h-3.5 w-3.5" />
                                    {{ tienda.categoria?.nombre }}
                                    <span v-if="tienda.user" class="text-gray-300">·</span>
                                    <span v-if="tienda.user">{{ tienda.user.name }}</span>
                                </p>
                            </div>
                        </div>
                        <Link :href="route('admin.tiendas.edit', tienda.id)"
                              class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                            Editar tienda
                        </Link>
                    </div>
                </div>

                <!-- ── Estadísticas + distribución ─────────────────────────── -->
                <div class="mb-6 grid gap-4 lg:grid-cols-3">
                    <!-- Promedio + total -->
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Valoración media</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-4xl font-extrabold text-gray-900 dark:text-white">
                                {{ Number(stats.promedio).toFixed(1) }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">/ 5</span>
                        </div>
                        <div class="mt-2 flex items-center gap-1">
                            <Star v-for="n in 5" :key="n"
                                  :class="['h-5 w-5',
                                      n <= Math.round(stats.promedio)
                                          ? 'fill-yellow-400 text-yellow-400'
                                          : 'text-gray-300 dark:text-gray-600']" />
                        </div>
                        <p class="mt-2 text-xs text-gray-400">{{ stats.total }} reseña{{ stats.total === 1 ? '' : 's' }} en total</p>
                    </div>

                    <!-- Distribución por estrellas -->
                    <div class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow lg:col-span-2">
                        <p class="mb-3 text-sm font-medium text-gray-500 dark:text-gray-400">Distribución por puntuación</p>
                        <div class="space-y-2">
                            <button
                                v-for="n in [5, 4, 3, 2, 1]"
                                :key="n"
                                type="button"
                                @click="filtrarPorPuntuacion(n)"
                                :class="['group flex w-full items-center gap-3 rounded-md px-2 py-1 transition-colors',
                                    String(form.puntuacion) === String(n)
                                        ? 'bg-primary-50 dark:bg-primary-900/20'
                                        : 'hover:bg-gray-50 dark:hover:bg-gray-700/50']"
                            >
                                <span class="flex w-12 items-center gap-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    {{ n }} <Star class="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                </span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                    <div class="h-full rounded-full bg-yellow-400 transition-all duration-500"
                                         :style="{ width: porcentaje(n) + '%' }"></div>
                                </div>
                                <span class="w-12 text-right text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ stats.distribucion[n] || 0 }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Filtros ─────────────────────────────────────────────── -->
                <div class="mb-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                            <input
                                v-model="form.search"
                                @input="buscarConDebounce"
                                type="text"
                                placeholder="Título, comentario o autor..."
                                maxlength="100"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Puntuación</label>
                            <select v-model="form.puntuacion" @change="buscar"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Todas</option>
                                <option v-for="n in [5, 4, 3, 2, 1]" :key="n" :value="n">{{ n }} estrella{{ n === 1 ? '' : 's' }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Ordenar por</label>
                            <select v-model="form.orden" @change="buscar"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="recientes">Más recientes</option>
                                <option value="antiguas">Más antiguas</option>
                                <option value="mejor">Mejor puntuadas</option>
                                <option value="peor">Peor puntuadas</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="limpiarFiltros"
                            class="rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- ── Listado de reseñas ──────────────────────────────────── -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">

                        <ul v-if="resenas.data.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                            <li v-for="r in resenas.data" :key="r.id" class="flex flex-col gap-3 py-5 first:pt-0 last:pb-0 sm:flex-row sm:items-start sm:gap-4">
                                <!-- Avatar -->
                                <img v-if="r.user?.avatar" :src="`/storage/${r.user.avatar}`" :alt="r.user.name"
                                     class="h-10 w-10 flex-shrink-0 rounded-full object-cover" />
                                <div v-else class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 text-sm font-semibold text-gray-500 dark:text-gray-400">
                                    {{ (r.user?.name || '?').charAt(0).toUpperCase() }}
                                </div>

                                <!-- Contenido -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ r.user?.name || 'Usuario eliminado' }}
                                        </p>
                                        <div class="flex items-center gap-0.5">
                                            <Star v-for="n in 5" :key="n"
                                                  :class="['h-3.5 w-3.5',
                                                      n <= r.puntuacion
                                                          ? 'fill-yellow-400 text-yellow-400'
                                                          : 'text-gray-300 dark:text-gray-600']" />
                                        </div>
                                        <span class="text-xs text-gray-400">·</span>
                                        <time class="text-xs text-gray-400">{{ formatearFecha(r.created_at) }}</time>
                                    </div>
                                    <h4 v-if="r.titulo" class="mt-1 text-sm font-bold text-gray-800 dark:text-gray-200">
                                        {{ r.titulo }}
                                    </h4>
                                    <p class="mt-1 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">
                                        {{ r.comentario }}
                                    </p>
                                </div>

                                <!-- Acciones -->
                                <div class="flex flex-shrink-0 items-start">
                                    <button
                                        @click="eliminarResena(r)"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                                        title="Eliminar reseña"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                        Eliminar
                                    </button>
                                </div>
                            </li>
                        </ul>

                        <!-- Empty -->
                        <div v-else class="py-16 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <MessageSquareOff class="h-6 w-6 text-gray-400" />
                            </div>
                            <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-white">Sin reseñas</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ tieneFiltros ? 'No hay reseñas que coincidan con los filtros aplicados.' : 'Esta tienda todavía no ha recibido reseñas.' }}
                            </p>
                            <button v-if="tieneFiltros" @click="limpiarFiltros"
                                    class="mt-4 inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                Limpiar filtros
                            </button>
                        </div>

                        <!-- Paginación -->
                        <div v-if="resenas.last_page > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Mostrando <span class="font-medium">{{ resenas.from }}</span> a <span class="font-medium">{{ resenas.to }}</span> de <span class="font-medium">{{ resenas.total }}</span> resultados
                            </p>
                            <div class="flex items-center gap-2">
                                <Link v-if="resenas.prev_page_url" :href="resenas.prev_page_url" preserve-scroll
                                      class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    Anterior
                                </Link>
                                <span v-else class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed">Anterior</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Pág. {{ resenas.current_page }} / {{ resenas.last_page }}</span>
                                <Link v-if="resenas.next_page_url" :href="resenas.next_page_url" preserve-scroll
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
import CategoriaIcono from '@/Components/CategoriaIcono.vue';
import { Link, router } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import { ArrowLeft, Star, Trash2, MessageSquareOff } from 'lucide-vue-next';

const props = defineProps({
    tienda:  { type: Object, required: true },
    resenas: { type: Object, required: true },
    stats:   { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const form = reactive({
    search:     props.filters?.search     ?? '',
    puntuacion: props.filters?.puntuacion ?? '',
    orden:      props.filters?.orden      ?? 'recientes',
});

const tieneFiltros = computed(() =>
    !!form.search || form.puntuacion !== '' || (form.orden && form.orden !== 'recientes')
);

const buscar = () => {
    router.get(route('admin.tiendas.resenas', props.tienda.id), { ...form }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

let debounceTimer = null;
const buscarConDebounce = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(buscar, 350);
};

const filtrarPorPuntuacion = (n) => {
    form.puntuacion = String(form.puntuacion) === String(n) ? '' : n;
    buscar();
};

const limpiarFiltros = () => {
    form.search = '';
    form.puntuacion = '';
    form.orden = 'recientes';
    buscar();
};

const porcentaje = (n) => {
    const total = props.stats.total || 0;
    if (!total) return 0;
    return Math.round(((props.stats.distribucion?.[n] || 0) / total) * 100);
};

const formatearFecha = (iso) => {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString('es-ES', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
};

const eliminarResena = (r) => {
    if (!confirm(`¿Eliminar la reseña de "${r.user?.name || 'Usuario'}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route('admin.tiendas.resenas.destroy', [props.tienda.id, r.id]), {
        preserveScroll: true,
    });
};
</script>
