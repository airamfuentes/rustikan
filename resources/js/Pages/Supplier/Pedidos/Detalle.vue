<template>
    <LayoutSupplier>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Cabecera -->
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-4">
                        <Link :href="route('supplier.pedidos.index')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                            <ArrowLeft class="h-4 w-4" /> Volver
                        </Link>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pedido #{{ pedido.numero_pedido }}</h1>
                            <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                {{ estadoLabel(pedido.estado) }}
                            </span>
                        </div>
                    </div>
                    <a :href="route('supplier.exportar.pedido', pedido.id)" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-2 text-sm font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                        <FileText class="h-4 w-4" /> Hoja de preparación (PDF)
                    </a>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Productos -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Productos del pedido</h3>
                            </div>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <li v-for="item in pedido.items" :key="item.id" class="flex items-center gap-4 px-6 py-4">
                                    <img v-if="item.producto?.imagen"
                                        :src="`/storage/${item.producto.imagen}`"
                                        :alt="item.producto.nombre"
                                        class="h-14 w-14 rounded-xl object-cover flex-shrink-0"
                                        @error="(e) => e.target.style.display='none'" />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ item.producto?.nombre ?? '—' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.tienda?.nombre ?? '' }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">x{{ item.cantidad }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.precio_unitario }} €/ud</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <!-- Dirección -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Dirección de envío</h3>
                            </div>
                            <div class="px-6 py-4 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <p>{{ pedido.direccion_envio }}</p>
                                <p v-if="pedido.telefono_contacto" class="text-gray-500 dark:text-gray-400">Tel: {{ pedido.telefono_contacto }}</p>
                                <p v-if="pedido.notas" class="mt-2 flex items-start gap-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-3 py-2 text-amber-700 dark:text-amber-300">
                                    <StickyNote class="h-4 w-4 mt-0.5 shrink-0" />
                                    <span>{{ pedido.notas }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar: estado + cliente -->
                    <div class="space-y-4">
                        <!-- Cliente -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Cliente</h3>
                            </div>
                            <div class="px-6 py-4">
                                <p class="font-medium text-gray-900 dark:text-white">{{ pedido.user?.name ?? '—' }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ pedido.user?.email ?? '' }}</p>
                            </div>
                        </div>

                        <!-- Cambiar estado -->
                        <div v-if="pedido.estado !== 'cancelado'"
                            class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Actualizar estado</h3>
                            </div>
                            <div class="px-6 py-4 space-y-2">
                                <button
                                    v-for="est in estadosAccion"
                                    :key="est.value"
                                    @click="cambiarEstado(est.value)"
                                    :disabled="pedido.estado === est.value || procesando"
                                    :class="[
                                        'flex w-full items-center gap-2 rounded-xl border-2 px-4 py-2.5 text-sm font-semibold transition-all',
                                        pedido.estado === est.value
                                            ? est.activeClass + ' cursor-default'
                                            : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600',
                                        procesando ? 'opacity-50 cursor-not-allowed' : ''
                                    ]">
                                    <span :class="est.dotColor" class="h-2 w-2 rounded-full flex-shrink-0"></span>
                                    {{ est.label }}
                                    <span v-if="pedido.estado === est.value" class="ml-auto text-xs opacity-60">Actual</span>
                                </button>
                            </div>
                        </div>

                        <!-- Motivo incidencia -->
                        <div v-if="pedido.estado === 'incidencia' && pedido.motivo_incidencia"
                            class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 shadow overflow-hidden">
                            <div class="border-b border-red-200 dark:border-red-800 px-6 py-4 flex items-center gap-2">
                                <AlertTriangle class="h-4 w-4 text-red-600 dark:text-red-400" />
                                <h3 class="font-semibold text-red-800 dark:text-red-300">Motivo incidencia</h3>
                            </div>
                            <div class="px-6 py-4 text-sm text-red-700 dark:text-red-300">{{ pedido.motivo_incidencia }}</div>
                        </div>

                        <!-- Total -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="px-6 py-4 space-y-2 text-sm">
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span>Subtotal</span>
                                    <span>{{ pedido.subtotal }} €</span>
                                </div>
                                <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                    <span>Envío</span>
                                    <span>{{ pedido.gastos_envio }} €</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-900 dark:text-white border-t border-gray-100 dark:border-gray-700 pt-2 mt-2">
                                    <span>Total</span>
                                    <span>{{ pedido.total }} €</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Modal incidencia -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="incidenciaModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-800 shadow-xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl bg-red-100 dark:bg-red-900/30 p-2">
                                <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Registrar incidencia</h3>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Explica brevemente el motivo de la incidencia. Esta información quedará registrada y será visible para el administrador.</p>
                        <textarea
                            v-model="motivoIncidencia"
                            rows="4"
                            maxlength="500"
                            placeholder="Describe el motivo de la incidencia..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"
                        ></textarea>
                        <p class="text-right text-xs text-gray-400">{{ motivoIncidencia.length }}/500</p>
                        <div class="flex gap-3 justify-end">
                            <button @click="incidenciaModal = false"
                                class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Cancelar
                            </button>
                            <button @click="confirmarIncidencia"
                                :disabled="!motivoIncidencia.trim()"
                                class="rounded-xl bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                                Confirmar incidencia
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </LayoutSupplier>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

<script setup>
import LayoutSupplier from '@/Layouts/LayoutSupplier.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { ArrowLeft, StickyNote, FileText, AlertTriangle } from 'lucide-vue-next';
import { useToasts } from '@/Composables/useToasts';

const props = defineProps({
    pedido: { type: Object, required: true },
});

const { success: toastSuccess, error: toastError } = useToasts();
const procesando = ref(false);

const incidenciaModal = ref(false);
const motivoIncidencia = ref('');

const abrirIncidenciaModal = () => {
    motivoIncidencia.value = '';
    incidenciaModal.value = true;
};

const confirmarIncidencia = () => {
    if (!motivoIncidencia.value.trim()) return;
    incidenciaModal.value = false;
    procesando.value = true;
    router.post(
        route('supplier.pedidos.estado', props.pedido.id),
        { estado: 'incidencia', motivo_incidencia: motivoIncidencia.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => toastSuccess('Incidencia registrada', 'El motivo ha quedado guardado.'),
            onError:   () => toastError('Error', 'No se pudo registrar la incidencia.'),
            onFinish:  () => { procesando.value = false; },
        }
    );
};

let pollInterval = null;
onMounted(() => {
    pollInterval = setInterval(() => {
        if (!procesando.value) router.reload({ only: ['pedido'] });
    }, 5000);
});
onUnmounted(() => clearInterval(pollInterval));

const estadosAccion = [
    { value: 'confirmado',     label: 'Confirmado',     dotColor: 'bg-blue-400',   activeClass: 'border-blue-300 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' },
    { value: 'en_preparacion', label: 'En preparación', dotColor: 'bg-orange-400', activeClass: 'border-orange-300 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300' },
    { value: 'enviado',        label: 'Enviado',        dotColor: 'bg-purple-400', activeClass: 'border-purple-300 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300' },
    { value: 'incidencia',     label: 'Incidencia',     dotColor: 'bg-red-400',    activeClass: 'border-red-300 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' },
];

const cambiarEstado = (nuevoEstado) => {
    if (procesando.value || props.pedido.estado === nuevoEstado) return;
    if (nuevoEstado === 'incidencia') {
        abrirIncidenciaModal();
        return;
    }
    procesando.value = true;
    router.post(
        route('supplier.pedidos.estado', props.pedido.id),
        { estado: nuevoEstado },
        {
            preserveScroll: true,
            onSuccess: () => toastSuccess('Estado actualizado', `Pedido marcado como "${estadoLabel(nuevoEstado)}"`),
            onError:   () => toastError('Error', 'No se pudo actualizar el estado.'),
            onFinish:  () => { procesando.value = false; },
        }
    );
};

const estadoBadgeClass = (estado) => {
    const map = {
        pendiente:      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        en_preparacion: 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
        confirmado:     'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        enviado:        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        entregado:      'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        cancelado:      'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        incidencia:     'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
    return map[estado] ?? 'bg-gray-100 text-gray-800';
};

const estadoLabel = (estado) => ({
    pendiente:      'Pendiente',
    en_preparacion: 'En preparación',
    confirmado:     'Confirmado',
    enviado:        'Enviado',
    entregado:      'Entregado',
    cancelado:      'Cancelado',
    incidencia:     'Incidencia',
}[estado] ?? estado.replace('_', ' '));
</script>
