<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Panel de Administración</h2>
                <div class="text-sm text-gray-600">
                    {{ new Date().toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">
                
                <!-- BOTONES DE GESTIÓN -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Gestionar Usuarios -->
                    <Link :href="route('admin.usuarios.index')" class="group rounded-xl bg-white border-2 border-gray-200 p-6 shadow-sm transition-all hover:shadow-md hover:border-purple-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-purple-100 p-3">
                            <svg class="h-8 w-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Usuarios</h3>
                        <p class="mt-2 text-sm text-gray-600">Gestionar usuarios</p>
                    </Link>

                    <!-- Gestionar Tiendas -->
                    <Link :href="route('admin.tiendas.index')" class="group rounded-xl bg-white border-2 border-gray-200 p-6 shadow-sm transition-all hover:shadow-md hover:border-blue-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-blue-100 p-3">
                            <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Tiendas</h3>
                        <p class="mt-2 text-sm text-gray-600">Gestionar tiendas</p>
                    </Link>

                    <!-- Gestionar Pedidos -->
                    <Link :href="route('admin.pedidos.index')" class="group rounded-xl bg-white border-2 border-gray-200 p-6 shadow-sm transition-all hover:shadow-md hover:border-orange-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-orange-100 p-3">
                            <svg class="h-8 w-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Pedidos</h3>
                        <p class="mt-2 text-sm text-gray-600">Gestionar pedidos</p>
                    </Link>

                    <!-- Gestionar Productos -->
                    <Link :href="route('admin.productos.index')" class="group rounded-xl bg-white border-2 border-gray-200 p-6 shadow-sm transition-all hover:shadow-md hover:border-green-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-green-100 p-3">
                            <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Productos</h3>
                        <p class="mt-2 text-sm text-gray-600">Gestionar productos</p>
                    </Link>

                    <!-- Gestionar Ingresos -->
                    <Link :href="route('admin.ingresos.index')" class="group rounded-xl bg-white border-2 border-gray-200 p-6 shadow-sm transition-all hover:shadow-md hover:border-emerald-400 hover:-translate-y-1">
                        <div class="mb-4 inline-flex rounded-lg bg-emerald-100 p-3">
                            <svg class="h-8 w-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Ingresos</h3>
                        <p class="mt-2 text-sm text-gray-600">Ver estadísticas</p>
                    </Link>
                </div>

                <!-- ACTIVIDAD RECIENTE -->
                <div class="rounded-xl bg-white p-6 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">🕒 Actividad Reciente</h2>
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
                            <button @click="mostrarActividad = !mostrarActividad" class="rounded-lg bg-gray-600 px-4 py-2 text-white font-medium hover:bg-gray-700 transition-colors shadow">
                                {{ mostrarActividad ? 'Ocultar' : 'Desplegar' }}
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="mostrarActividad" class="space-y-4">
                        <!-- Filtros de fecha -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3">Filtrar por Fecha</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                                    <input
                                        v-model="filtros.fecha_desde"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                                    <input
                                        v-model="filtros.fecha_hasta"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
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
                                        class="flex-1 rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors"
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
                                    <span class="text-2xl">{{ actividad.icono }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">{{ actividad.descripcion }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-gray-600">{{ new Date(actividad.fecha).toLocaleString('es-ES') }}</p>
                                        <span v-if="actividad.usuario" class="text-xs text-gray-500">•</span>
                                        <p v-if="actividad.usuario" class="text-xs text-gray-500">Por: {{ actividad.usuario }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 py-8">
                            No se encontraron actividades en el rango de fechas seleccionado
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-4">
                        Haz clic en "Desplegar" para ver la actividad reciente
                    </div>
                </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps({
    usuarios_recientes: Array,
    total_usuarios: Number,
    pedidos_recientes: Array,
    pedidos_stats: Object,
    tiendas_stats: Object,
    productos_stats: Object,
    productos_bajo_stock: Array,
    productos_sin_stock: Array,
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
