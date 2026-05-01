<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    solicitudes: { type: Object, default: () => ({ data: [] }) },
    estado:      { type: String, default: 'pendiente' },
    counts:      { type: Object, default: () => ({}) },
});

const page = usePage();

const toasts = ref([]);
const addToast = (type, title, msg) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message: msg });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 4500);
};
watch(() => page.props.flash, (flash) => {
    if (flash?.success) addToast('success', 'Listo', flash.success);
    if (flash?.error)   addToast('error',   'Error',  flash.error);
}, { deep: true });

const cambiarEstado = (est) => {
    router.get(route('admin.solicitudes-creacion.index'), { estado: est }, { preserveState: true });
};

// Modal detalle
const detalle = ref(null);
const abrirDetalle = (s) => { detalle.value = s; };
const cerrarDetalle = () => { detalle.value = null; };

// Modal rechazo
const rechazando   = ref(null);
const notasRechazo = ref('');
const abrirRechazar = (s) => { rechazando.value = s; notasRechazo.value = ''; };
const cerrarRechazar = () => { rechazando.value = null; };

const aprobar = (s) => {
    if (!confirm(`¿Aprobar la solicitud de "${s.nombre_tienda}"?`)) return;
    router.post(route('admin.solicitudes-creacion.aprobar', s.id), {}, { preserveScroll: true });
};

const submitRechazar = () => {
    router.post(route('admin.solicitudes-creacion.rechazar', rechazando.value.id),
        { notas: notasRechazo.value },
        { preserveScroll: true, onSuccess: cerrarRechazar }
    );
};

const badgeClass = (estado) => ({
    pendiente: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    aprobada:  'bg-green-100  text-green-800  dark:bg-green-900/40  dark:text-green-300',
    rechazada: 'bg-red-100    text-red-800    dark:bg-red-900/40    dark:text-red-300',
}[estado] ?? '');

const tabClass = (est) => props.estado === est
    ? 'border-primary-500 text-primary-600 dark:text-primary-400'
    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Solicitudes de nueva tienda" />

        <!-- Toast -->
        <div class="fixed right-4 top-20 z-[9999] space-y-2">
            <Toast v-for="t in toasts" :key="t.id" :type="t.type" :title="t.title" :message="t.message" />
        </div>

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Cabecera -->
            <div class="mb-6 flex items-center gap-4">
                <Link :href="route('admin.dashboard')" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitudes de nueva tienda</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Peticiones de productores que quieren vender en Rustikan</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex gap-6">
                    <button
                        v-for="tab in [{key:'pendiente',label:'Pendientes'},{key:'aprobada',label:'Aprobadas'},{key:'rechazada',label:'Rechazadas'}]"
                        :key="tab.key"
                        @click="cambiarEstado(tab.key)"
                        :class="['border-b-2 pb-3 text-sm font-medium transition-colors', tabClass(tab.key)]"
                    >
                        {{ tab.label }}
                        <span class="ml-1.5 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            {{ counts[tab.key] ?? 0 }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Lista -->
            <div v-if="solicitudes.data.length === 0" class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-16 text-center">
                <p class="text-gray-400 dark:text-gray-500">No hay solicitudes {{ estado === 'pendiente' ? 'pendientes' : estado === 'aprobada' ? 'aprobadas' : 'rechazadas' }}.</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="s in solicitudes.data"
                    :key="s.id"
                    class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm"
                >
                    <div class="flex flex-wrap items-start gap-4">
                        <!-- Info principal -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ s.nombre_tienda }}</h3>
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', badgeClass(s.estado)]">
                                    {{ s.estado }}
                                </span>
                                <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs text-gray-600 dark:text-gray-300">
                                    {{ s.categoria }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ s.descripcion }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                                <span>{{ s.nombre_contacto }}</span>
                                <span>{{ s.email }}</span>
                                <span v-if="s.telefono">{{ s.telefono }}</span>
                                <span v-if="s.municipio">{{ s.municipio }}</span>
                                <span>{{ s.created_at }}</span>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex shrink-0 items-center gap-2">
                            <button
                                @click="abrirDetalle(s)"
                                class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                            >
                                Ver detalle
                            </button>
                            <template v-if="s.estado === 'pendiente'">
                                <button
                                    @click="aprobar(s)"
                                    class="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-600 transition-colors"
                                >
                                    Aprobar
                                </button>
                                <button
                                    @click="abrirRechazar(s)"
                                    class="rounded-lg bg-red-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-600 transition-colors"
                                >
                                    Rechazar
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Notas admin si rechazada/aprobada -->
                    <div v-if="s.notas_admin" class="mt-3 rounded-xl bg-gray-50 dark:bg-gray-700/50 px-4 py-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="font-semibold">Notas admin:</span> {{ s.notas_admin }}
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="solicitudes.last_page > 1" class="mt-6 flex justify-center gap-2">
                <Link
                    v-for="link in solicitudes.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="['rounded-lg px-3 py-1.5 text-sm transition-colors',
                        link.active
                            ? 'bg-primary-500 text-white'
                            : link.url
                                ? 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50'
                                : 'cursor-default opacity-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Modal detalle -->
        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="detalle" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="cerrarDetalle">
                <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto max-h-[90vh]">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                        <h3 class="font-bold text-gray-900 dark:text-white">{{ detalle.nombre_tienda }}</h3>
                        <button @click="cerrarDetalle" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-3">
                            <div><span class="text-gray-400">Categoría</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.categoria }}</p></div>
                            <div><span class="text-gray-400">Municipio</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.municipio || '—' }}</p></div>
                            <div><span class="text-gray-400">Contacto</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.nombre_contacto }}</p></div>
                            <div><span class="text-gray-400">Teléfono</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.telefono || '—' }}</p></div>
                            <div class="col-span-2"><span class="text-gray-400">Email</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.email }}</p></div>
                            <div v-if="detalle.direccion" class="col-span-2"><span class="text-gray-400">Dirección</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.direccion }}</p></div>
                            <div v-if="detalle.web"><span class="text-gray-400">Web</span><a :href="detalle.web" target="_blank" class="font-medium text-primary-600 hover:underline mt-0.5 block truncate">{{ detalle.web }}</a></div>
                            <div v-if="detalle.instagram"><span class="text-gray-400">Instagram</span><p class="font-medium text-gray-900 dark:text-white mt-0.5">@{{ detalle.instagram }}</p></div>
                        </div>
                        <div>
                            <span class="text-gray-400">Descripción del negocio</span>
                            <p class="mt-1 text-gray-700 dark:text-gray-300 leading-relaxed">{{ detalle.descripcion }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Productos que vende</span>
                            <p class="mt-1 text-gray-700 dark:text-gray-300 leading-relaxed">{{ detalle.productos_descripcion }}</p>
                        </div>
                        <div v-if="detalle.notas_admin" class="rounded-xl bg-gray-50 dark:bg-gray-700 p-3">
                            <span class="text-gray-400">Notas admin</span>
                            <p class="mt-1 text-gray-700 dark:text-gray-300">{{ detalle.notas_admin }}</p>
                        </div>
                    </div>
                    <div v-if="detalle.estado === 'pendiente'" class="flex gap-3 border-t border-gray-100 dark:border-gray-700 px-6 py-4">
                        <button @click="aprobar(detalle); cerrarDetalle()"
                            class="flex-1 rounded-xl bg-green-500 py-2 text-sm font-bold text-white hover:bg-green-600 transition-colors">
                            Aprobar
                        </button>
                        <button @click="cerrarDetalle(); abrirRechazar(detalle)"
                            class="flex-1 rounded-xl bg-red-500 py-2 text-sm font-bold text-white hover:bg-red-600 transition-colors">
                            Rechazar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Modal rechazo -->
        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="rechazando" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="cerrarRechazar">
                <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-2xl p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">Rechazar solicitud de "{{ rechazando.nombre_tienda }}"</h3>
                    <textarea
                        v-model="notasRechazo"
                        rows="3"
                        placeholder="Motivo del rechazo (opcional)..."
                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                    ></textarea>
                    <div class="mt-4 flex gap-3">
                        <button @click="cerrarRechazar"
                            class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button @click="submitRechazar"
                            class="flex-1 rounded-xl bg-red-500 py-2 text-sm font-bold text-white hover:bg-red-600 transition-colors">
                            Rechazar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

    </AuthenticatedLayout>
</template>
