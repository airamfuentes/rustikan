<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Panel de Administración</h2>
                <div class="text-sm text-gray-600 dark:text-gray-400">{{ new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">
                
                <!-- BOTONES DE GESTIÓN -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Gestionar Usuarios -->
                    <Link :href="route('admin.usuarios.index')" class="group rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-purple-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-purple-100 p-3">
                            <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Usuarios</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Gestionar usuarios</p>
                    </Link>

                    <!-- Gestionar Tiendas -->
                    <Link :href="route('admin.tiendas.index')" class="group rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-blue-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-blue-100 p-3">
                            <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tiendas</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Gestionar tiendas</p>
                    </Link>

                    <!-- Gestionar Pedidos -->
                    <Link :href="route('admin.pedidos.index')" class="group rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-orange-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-orange-100 p-3">
                            <svg class="h-8 w-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pedidos</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Gestionar pedidos</p>
                    </Link>

                    <!-- Solicitudes de cambio -->
                    <Link :href="route('admin.solicitudes.index')" class="group relative rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-yellow-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-yellow-100 p-3">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Solicitudes</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Aprobar cambios de tiendas</p>
                    </Link>

                    <!-- Solicitudes de nueva tienda -->
                    <Link :href="route('admin.solicitudes-creacion.index')" class="group relative rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-green-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-green-100 p-3">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Nuevas tiendas</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Solicitudes de vendedores</p>
                    </Link>

                    <!-- Gestionar Ingresos -->
                    <Link :href="route('admin.ingresos.index')" class="group rounded-xl bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 p-6 shadow-sm transition-all hover:shadow-md hover:border-emerald-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-emerald-100 p-3">
                            <svg class="h-8 w-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ingresos</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Ver estadísticas</p>
                    </Link>
                </div>

                <!-- ACTIVIDAD RECIENTE -->
                <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2"><Clock class="h-5 w-5 text-gray-500" /> Actividad Reciente</h2>
                        <div class="flex gap-2">
                            <button @click="refrescarActividad" :disabled="cargandoActividad" class="rounded-lg bg-primary-600 px-4 py-2 text-white font-medium hover:bg-primary-700 transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg v-if="!cargandoActividad" class="h-5 w-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <svg v-else class="animate-spin h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Refrescar
                            </button>
                            <button @click="mostrarActividad = !mostrarActividad" class="rounded-lg bg-gray-600 dark:bg-gray-700 px-4 py-2 text-white font-medium hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors shadow">
                                {{ mostrarActividad ? 'Ocultar' : 'Desplegar' }}
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="mostrarActividad" class="space-y-4">
                        <!-- Filtros de fecha -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Filtrar por Fecha</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Desde</label>
                                    <input
                                        v-model="filtros.fecha_desde"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hasta</label>
                                    <input
                                        v-model="filtros.fecha_hasta"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                </div>
                                <div class="flex items-end gap-2">
                                    <button
                                        @click="aplicarFiltros"
                                        class="flex-1 rounded-md bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 transition-colors"
                                    >
                                        Filtrar
                                    </button>
                                    <button
                                        @click="limpiarFiltros"
                                        class="flex-1 rounded-md bg-gray-200 dark:bg-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                                    >
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de actividades -->
                        <div v-if="actividad_reciente.length > 0" class="space-y-3">
                            <div v-for="(actividad, index) in actividad_reciente" :key="index" class="flex items-start gap-3 rounded-lg border-l-4 p-4 transition-all hover:shadow-md" :class="`border-${actividad.color}-500 bg-${actividad.color}-50 hover:bg-${actividad.color}-100`">
                                <div class="flex-shrink-0 mt-0.5">
                                    <ActivityIcono :icono="actividad.icono" :class="`h-6 w-6 text-${actividad.color}-600 dark:text-${actividad.color}-400`" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ actividad.descripcion }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ new Date(actividad.fecha).toLocaleString('es-ES') }}</p>
                                        <span v-if="actividad.usuario" class="text-xs text-gray-500">•</span>
                                        <p v-if="actividad.usuario" class="text-xs text-gray-500 dark:text-gray-400">Por: {{ actividad.usuario }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 dark:text-gray-400 py-8">
                            No se encontraron actividades en el rango de fechas seleccionado
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
                        Haz clic en "Desplegar" para ver la actividad reciente
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
import { ref, reactive } from 'vue';
import { Clock } from 'lucide-vue-next';
import ActivityIcono from '@/Components/ActivityIcono.vue';

const props = defineProps({
    usuarios_recientes: Array,
    total_usuarios: Number,
    pedidos_recientes: Array,
    pedidos_stats: Object,
    tiendas_stats: Object,
    actividad_reciente: Array,
    filtros_aplicados: Object,
});

const mostrarActividad = ref(false);
const cargandoActividad = ref(false);

const filtros = reactive({
    fecha_desde: props.filtros_aplicados?.fecha_desde || '',
    fecha_hasta: props.filtros_aplicados?.fecha_hasta || '',
});

const refrescarActividad = () => {
    cargandoActividad.value = true;
    router.reload({ 
        only: ['actividad_reciente'],
        onFinish: () => {
            cargandoActividad.value = false;
        }
    });
};

const aplicarFiltros = () => {
    cargandoActividad.value = true;
    
    const params = {};
    if (filtros.fecha_desde) params.fecha_desde = filtros.fecha_desde;
    if (filtros.fecha_hasta) params.fecha_hasta = filtros.fecha_hasta;
    
    router.get(route('admin.dashboard'), params, {
        only: ['actividad_reciente', 'filtros_aplicados'],
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            cargandoActividad.value = false;
        }
    });
};

const limpiarFiltros = () => {
    filtros.fecha_desde = '';
    filtros.fecha_hasta = '';
    aplicarFiltros();
};
</script>
