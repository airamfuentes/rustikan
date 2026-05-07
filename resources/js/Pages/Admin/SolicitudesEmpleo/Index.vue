<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { ArrowLeft, FileText, Mail, Phone, Calendar, Trash2, Download, Briefcase } from 'lucide-vue-next';

const props = defineProps({
    solicitudes: { type: Object, default: () => ({ data: [] }) },
    estado:      { type: String, default: 'pendiente' },
    counts:      { type: Object, default: () => ({}) },
});

const cambiarEstado = (est) => {
    router.get(route('admin.solicitudes-empleo.index'), { estado: est }, { preserveState: true });
};

// Modal detalle
const detalle = ref(null);
const abrirDetalle = (s) => { detalle.value = s; };
const cerrarDetalle = () => { detalle.value = null; };

const cambiarSituacion = (s, nuevoEstado) => {
    // Si transitamos a "rechazada" desde otro estado, avisamos al admin que se enviará email
    if (nuevoEstado === 'rechazada' && s.estado !== 'rechazada') {
        const ok = confirm(
            `Vas a marcar como RECHAZADA la candidatura de "${s.nombre} ${s.apellidos}".\n\n` +
            `Se enviará automáticamente un email de notificación al candidato (${s.email}).\n\n` +
            `¿Continuar?`
        );
        if (!ok) return;
    }

    router.patch(route('admin.solicitudes-empleo.estado', s.id), { estado: nuevoEstado }, {
        preserveScroll: true,
        onSuccess: () => { if (detalle.value?.id === s.id) cerrarDetalle(); },
    });
};

const eliminar = (s) => {
    if (!confirm(`¿Eliminar la solicitud de "${s.nombre} ${s.apellidos}"? También se borrará el CV adjunto. Esta acción no se puede deshacer.`)) return;
    router.delete(route('admin.solicitudes-empleo.destroy', s.id), {
        preserveScroll: true,
        onSuccess: () => { if (detalle.value?.id === s.id) cerrarDetalle(); },
    });
};

const badgeClass = (estado) => ({
    pendiente:  'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
    revisada:   'bg-blue-100   text-blue-800   dark:bg-blue-900/40   dark:text-blue-300',
    contactada: 'bg-green-100  text-green-800  dark:bg-green-900/40  dark:text-green-300',
    rechazada:  'bg-red-100    text-red-800    dark:bg-red-900/40    dark:text-red-300',
}[estado] ?? '');

const tabClass = (est) => props.estado === est
    ? 'border-primary-500 text-primary-600 dark:text-primary-400'
    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400';

const initials = (s) => {
    return ((s.nombre?.[0] || '') + (s.apellidos?.[0] || '')).toUpperCase() || '?';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Solicitudes de empleo" />

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Cabecera -->
            <div class="mb-6 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400">
                        <Briefcase class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitudes de empleo</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Candidaturas recibidas desde "Trabaja con nosotros"</p>
                    </div>
                </div>
                <Link :href="route('admin.dashboard')" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                    <ArrowLeft class="h-4 w-4" /> Volver
                </Link>
            </div>

            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex gap-6 overflow-x-auto">
                    <button
                        v-for="tab in [
                            { key: 'pendiente',  label: 'Pendientes' },
                            { key: 'revisada',   label: 'Revisadas' },
                            { key: 'contactada', label: 'Contactadas' },
                            { key: 'rechazada',  label: 'Rechazadas' },
                        ]"
                        :key="tab.key"
                        @click="cambiarEstado(tab.key)"
                        :class="['whitespace-nowrap border-b-2 pb-3 text-sm font-medium transition-colors', tabClass(tab.key)]"
                    >
                        {{ tab.label }}
                        <span class="ml-1.5 rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            {{ counts[tab.key] ?? 0 }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Empty state -->
            <div v-if="solicitudes.data.length === 0" class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-16 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                    <Briefcase class="h-6 w-6 text-gray-400" />
                </div>
                <p class="text-gray-400 dark:text-gray-500">No hay solicitudes
                    {{
                        estado === 'pendiente' ? 'pendientes' :
                        estado === 'revisada' ? 'revisadas' :
                        estado === 'contactada' ? 'contactadas' : 'rechazadas'
                    }}.
                </p>
            </div>

            <!-- Lista -->
            <div v-else class="space-y-4">
                <div
                    v-for="s in solicitudes.data"
                    :key="s.id"
                    class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-shadow"
                >
                    <div class="flex flex-wrap items-start gap-4">
                        <!-- Avatar iniciales -->
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-sm font-bold text-primary-600 dark:text-primary-400">
                            {{ initials(s) }}
                        </div>

                        <!-- Info principal -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ s.nombre }} {{ s.apellidos }}</h3>
                                <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', badgeClass(s.estado)]">
                                    {{ s.estado }}
                                </span>
                                <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs text-gray-600 dark:text-gray-300">
                                    {{ s.puesto_label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ s.mensaje }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
                                <span class="inline-flex items-center gap-1"><Mail class="h-3 w-3" /> {{ s.email }}</span>
                                <span v-if="s.telefono" class="inline-flex items-center gap-1"><Phone class="h-3 w-3" /> {{ s.telefono }}</span>
                                <span class="inline-flex items-center gap-1"><Calendar class="h-3 w-3" /> {{ s.created_at }}</span>
                                <a v-if="s.cv_path"
                                   :href="route('admin.solicitudes-empleo.cv', s.id)"
                                   class="inline-flex items-center gap-1 text-primary-600 dark:text-primary-400 hover:underline">
                                    <FileText class="h-3 w-3" /> {{ s.cv_nombre_original }}
                                </a>
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
                            <a v-if="s.cv_path"
                               :href="route('admin.solicitudes-empleo.cv', s.id)"
                               class="inline-flex items-center gap-1 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-600 transition-colors"
                               title="Descargar CV"
                            >
                                <Download class="h-3.5 w-3.5" /> CV
                            </a>
                            <button
                                @click="eliminar(s)"
                                class="rounded-lg border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                                title="Eliminar solicitud"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="solicitudes.last_page > 1" class="mt-6 flex items-center justify-center gap-4">
                <Link v-if="solicitudes.prev_page_url" :href="solicitudes.prev_page_url" preserve-scroll
                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Anterior
                </Link>
                <span v-else class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed">Anterior</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">Pág. {{ solicitudes.current_page }} / {{ solicitudes.last_page }}</span>
                <Link v-if="solicitudes.next_page_url" :href="solicitudes.next_page_url" preserve-scroll
                    class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    Siguiente
                </Link>
                <span v-else class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 cursor-not-allowed">Siguiente</span>
            </div>
        </div>

        <!-- Modal detalle -->
        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="transition duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="detalle" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="cerrarDetalle">
                <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl overflow-y-auto max-h-[90vh]">

                    <!-- Header del modal -->
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-sm font-bold text-primary-600 dark:text-primary-400">
                                {{ initials(detalle) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white">{{ detalle.nombre }} {{ detalle.apellidos }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ detalle.puesto_label }}</p>
                            </div>
                        </div>
                        <button @click="cerrarDetalle" class="rounded-full p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4 text-sm">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <span class="text-gray-400">Email</span>
                                <a :href="`mailto:${detalle.email}`" class="block font-medium text-primary-600 dark:text-primary-400 hover:underline mt-0.5 truncate">
                                    {{ detalle.email }}
                                </a>
                            </div>
                            <div>
                                <span class="text-gray-400">Teléfono</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">
                                    <a v-if="detalle.telefono" :href="`tel:${detalle.telefono}`" class="hover:underline">{{ detalle.telefono }}</a>
                                    <span v-else>—</span>
                                </p>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-400">Recibida</span>
                                <p class="font-medium text-gray-900 dark:text-white mt-0.5">{{ detalle.created_at }}</p>
                            </div>
                        </div>

                        <div>
                            <span class="text-gray-400">Mensaje del candidato</span>
                            <p class="mt-1 whitespace-pre-line text-gray-700 dark:text-gray-300 leading-relaxed">{{ detalle.mensaje }}</p>
                        </div>

                        <div v-if="detalle.cv_path" class="rounded-xl border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/20 p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <FileText class="h-8 w-8 text-primary-500 flex-shrink-0" />
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ detalle.cv_nombre_original }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">CV adjunto</p>
                                </div>
                            </div>
                            <a :href="route('admin.solicitudes-empleo.cv', detalle.id)"
                               class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-600">
                                <Download class="h-3.5 w-3.5" /> Descargar
                            </a>
                        </div>
                        <div v-else class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-4 text-sm text-gray-500 dark:text-gray-400">
                            Sin CV adjunto.
                        </div>
                    </div>

                    <!-- Acciones de cambio de estado -->
                    <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-4">
                        <p class="mb-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cambiar estado</p>
                        <div class="flex flex-wrap gap-2">
                            <button v-if="detalle.estado !== 'pendiente'"
                                @click="cambiarSituacion(detalle, 'pendiente')"
                                class="rounded-lg bg-yellow-100 dark:bg-yellow-900/40 px-3 py-1.5 text-xs font-bold text-yellow-800 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/60 transition-colors">
                                Marcar pendiente
                            </button>
                            <button v-if="detalle.estado !== 'revisada'"
                                @click="cambiarSituacion(detalle, 'revisada')"
                                class="rounded-lg bg-blue-100 dark:bg-blue-900/40 px-3 py-1.5 text-xs font-bold text-blue-800 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/60 transition-colors">
                                Marcar revisada
                            </button>
                            <button v-if="detalle.estado !== 'contactada'"
                                @click="cambiarSituacion(detalle, 'contactada')"
                                class="rounded-lg bg-green-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-600 transition-colors">
                                Marcar contactada
                            </button>
                            <button v-if="detalle.estado !== 'rechazada'"
                                @click="cambiarSituacion(detalle, 'rechazada')"
                                class="inline-flex items-center gap-1 rounded-lg bg-red-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-600 transition-colors"
                                title="Marcar como rechazada — enviará email automático al candidato"
                            >
                                <Mail class="h-3.5 w-3.5" /> Rechazar
                            </button>
                            <button @click="eliminar(detalle)"
                                class="ml-auto inline-flex items-center gap-1 rounded-lg border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">
                                <Trash2 class="h-3.5 w-3.5" /> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </AuthenticatedLayout>
</template>
