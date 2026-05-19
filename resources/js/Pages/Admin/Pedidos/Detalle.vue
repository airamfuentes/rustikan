<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.pedidos.index')" class="inline-flex items-center gap-1.5 rounded-lg bg-gray-200 dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                        <ArrowLeft class="h-4 w-4" /> Volver
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Pedido {{ pedido.numero_pedido ?? '#' + pedido.id }}
                    </h2>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="route('factura.show', pedido.id)" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 dark:border-orange-800 bg-orange-50 dark:bg-orange-900/20 px-3 py-2 text-sm font-medium text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                        <FileText class="h-4 w-4" /> Ver factura
                    </a>
                    <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-3 py-1 text-sm font-semibold">
                        {{ estadoLabel(pedido.estado) }}
                    </span>
                </div>
            </div>
        </template>

        <!-- Toasts via ToastContainer global -->

        <div class="py-12">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">

                    <!-- Columna izquierda: items del pedido -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Productos -->
                        <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Productos del pedido</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ pedido.items.length }} artículo{{ pedido.items.length !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="divide-y divide-gray-50 dark:divide-gray-700">
                                <div v-for="item in pedido.items" :key="item.id" class="flex items-center gap-4 px-6 py-4">
                                    <img
                                        :src="item.producto_imagen
                                            ? (item.producto_imagen.startsWith('http') ? item.producto_imagen : '/storage/' + item.producto_imagen)
                                            : (item.producto?.imagen
                                                ? (item.producto.imagen.startsWith('http') ? item.producto.imagen : '/storage/' + item.producto.imagen)
                                                : '/images/logo.png')"
                                        :alt="item.producto_nombre"
                                        class="h-14 w-14 flex-shrink-0 rounded-xl object-cover border border-gray-100 dark:border-gray-700"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ item.producto_nombre }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ item.tienda?.nombre }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ item.cantidad }} × {{ Number(item.precio_unitario).toFixed(2) }}€</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ (item.cantidad * item.precio_unitario).toFixed(2) }}€</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Totales -->
                            <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 space-y-1.5 text-sm">
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Subtotal</span>
                                    <span>{{ Number(pedido.subtotal).toFixed(2) }}€</span>
                                </div>
                                <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                    <span>Gastos de envío</span>
                                    <span>{{ Number(pedido.gastos_envio).toFixed(2) }}€</span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-900 dark:text-white text-base pt-1 border-t border-gray-100 dark:border-gray-700">
                                    <span>Total</span>
                                    <span>{{ Number(pedido.total).toFixed(2) }}€</span>
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de edición del pedido -->
                        <div class="overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Editar datos del pedido</h3>
                                <button v-if="puedeEditar" @click="mostrarEdit = !mostrarEdit" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ mostrarEdit ? 'Ocultar' : 'Editar' }}
                                </button>
                                <span v-else class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    <Lock class="h-3.5 w-3.5" />
                                    Bloqueado
                                </span>
                            </div>
                            <div v-if="!puedeEditar" class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                Este pedido ya está {{ pedido.estado === 'entregado' ? 'entregado' : 'cancelado' }} y sus datos no pueden modificarse.
                            </div>
                            <div v-else-if="mostrarEdit" class="px-6 py-4">
                                <form @submit.prevent="guardarEdicion" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono de contacto</label>
                                        <input v-model="editForm.telefono_contacto" type="tel"
                                            inputmode="tel" maxlength="20"
                                            v-only-phone
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección de envío</label>
                                        <textarea v-model="editForm.direccion_envio" rows="2"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nota del cliente</label>
                                        <textarea v-model="editForm.notas" rows="2"
                                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition resize-none"></textarea>
                                    </div>
                                    <div class="flex justify-end gap-3 pt-2">
                                        <button type="button" @click="mostrarEdit = false"
                                            class="rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            Cancelar
                                        </button>
                                        <button type="submit" :disabled="procesandoEdit"
                                            class="rounded-xl bg-primary-600 px-5 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-60 transition-colors">
                                            {{ procesandoEdit ? 'Guardando...' : 'Guardar cambios' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- Columna derecha -->
                    <div class="space-y-6">

                        <!-- Cliente -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Cliente</h3>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-sm font-bold flex-shrink-0">
                                        {{ pedido.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ pedido.user?.name ?? '—' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ pedido.user?.email ?? '' }}</p>
                                    </div>
                                </div>
                                <div v-if="pedido.telefono_contacto" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ pedido.telefono_contacto }}
                                </div>
                                <div v-if="pedido.direccion_envio" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ pedido.direccion_envio }}</span>
                                </div>
                                <div v-if="pedido.notas" class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-3 text-xs text-gray-600 dark:text-gray-400 italic">
                                    "{{ pedido.notas }}"
                                </div>
                                <div v-if="pedido.motivo_incidencia" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-xs text-red-700 dark:text-red-300">
                                    <p class="font-semibold mb-1">⚠ Motivo incidencia (almacén):</p>
                                    <p>{{ pedido.motivo_incidencia }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Info del pedido -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Detalles del pedido</h3>
                            </div>
                            <div class="px-6 py-4 space-y-2.5 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Nº pedido</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ pedido.numero_pedido }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Fecha</span>
                                    <span class="text-gray-900 dark:text-gray-200">{{ formatFecha(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Hora</span>
                                    <span class="text-gray-900 dark:text-gray-200">{{ formatHora(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Método de pago</span>
                                    <span class="text-gray-900 dark:text-gray-200 capitalize">{{ pedido.metodo_pago ?? '—' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 dark:text-gray-400">Estado actual</span>
                                    <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ estadoLabel(pedido.estado) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Cancelar pedido -->
                        <div v-if="!['cancelado', 'entregado'].includes(pedido.estado)" class="rounded-2xl bg-white dark:bg-gray-800 shadow overflow-hidden">
                            <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Acciones</h3>
                            </div>
                            <div class="px-6 py-4">
                                <button @click="mostrarModalCancelar = true" :disabled="procesando"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm font-semibold text-red-700 dark:text-red-400 transition-all hover:bg-red-100 dark:hover:bg-red-900/30 disabled:opacity-50">
                                    <XCircle class="h-4 w-4" />
                                    Cancelar este pedido
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Modal cancelar pedido con reembolso -->
    <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="mostrarModalCancelar" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="w-full max-w-md max-h-[90vh] overflow-y-auto overscroll-contain rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <XCircle class="h-6 w-6 text-red-500" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cancelar pedido</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    ¿Confirmas la cancelación del pedido <strong>#{{ pedido.numero_pedido }}</strong>?
                    El cliente recibirá una notificación.
                </p>

                <!-- Selección de reembolso -->
                <div class="mt-4">
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        Reembolso al cliente ({{ Number(pedido.total).toFixed(2) }}€)
                    </p>
                    <div class="space-y-2">
                        <label v-for="opt in reembolsoOpciones" :key="opt.value"
                            :class="['flex items-center gap-3 rounded-xl border-2 p-3 cursor-pointer transition-colors',
                                tipoReembolso === opt.value
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
                                    : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500']">
                            <input type="radio" v-model="tipoReembolso" :value="opt.value" class="text-primary-500" />
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ opt.label }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ opt.desc }}</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button @click="mostrarModalCancelar = false"
                        class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Volver
                    </button>
                    <button @click="cancelarPedido" :disabled="procesando"
                        class="flex-1 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-60 transition-colors">
                        {{ procesando ? 'Cancelando...' : 'Confirmar cancelación' }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { ArrowLeft, XCircle, FileText, Lock } from 'lucide-vue-next';
import { useToasts } from '@/Composables/useToasts';

const props = defineProps({
    pedido: { type: Object, required: true },
});

const { success: toastSuccess, error: toastError } = useToasts();
const addToast = (type, title, message = '') => {
    if (type === 'success') toastSuccess(title, message);
    else toastError(title, message);
};

const procesando       = ref(false);
const procesandoEdit   = ref(false);
const mostrarModalCancelar = ref(false);
const mostrarEdit      = ref(false);
const tipoReembolso    = ref('ninguno');

// Un pedido entregado o cancelado no permite editar sus datos.
const puedeEditar = computed(() => !['entregado', 'cancelado'].includes(props.pedido.estado));

const reembolsoOpciones = [
    { value: 'ninguno',   label: 'Sin reembolso',        desc: 'No se realiza ningún reembolso automático' },
    { value: 'tarjeta',   label: 'Reembolso a tarjeta',  desc: 'Se procesará en 5-7 días hábiles' },
    { value: 'rusticoin', label: 'Reembolso en RustiCoin', desc: 'Instantáneo al monedero del cliente' },
];

const editForm = reactive({
    telefono_contacto: props.pedido.telefono_contacto ?? '',
    direccion_envio:   props.pedido.direccion_envio   ?? '',
    notas:             props.pedido.notas             ?? '',
});

const guardarEdicion = () => {
    if (!puedeEditar.value) {
        addToast('error', 'No se puede editar', 'Este pedido ya está finalizado o cancelado.');
        return;
    }
    procesandoEdit.value = true;
    router.patch(
        route('admin.pedidos.update', props.pedido.id),
        editForm,
        {
            preserveScroll: true,
            onSuccess: () => {
                mostrarEdit.value = false;
                addToast('success', 'Pedido actualizado', 'Los datos del pedido han sido actualizados.');
            },
            onError: () => {
                addToast('error', 'Error', 'No se pudo actualizar el pedido.');
            },
            onFinish: () => { procesandoEdit.value = false; },
        }
    );
};

const cancelarPedido = () => {
    procesando.value = true;
    router.post(
        route('admin.pedidos.cancelar', props.pedido.id),
        { tipo_reembolso: tipoReembolso.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                mostrarModalCancelar.value = false;
                addToast('success', 'Pedido cancelado', 'El pedido ha sido cancelado correctamente.');
            },
            onError: () => {
                addToast('error', 'Error', 'No se pudo cancelar el pedido.');
            },
            onFinish: () => { procesando.value = false; },
        }
    );
};

const estadoBadgeClass = (estado) => {
    const map = {
        pendiente:      'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
        en_preparacion: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        confirmado:     'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        preparando:     'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
        en_camino:      'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        enviado:        'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
        entregado:      'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        cancelado:      'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        incidencia:     'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
    };
    return map[estado] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const estadoLabel = (estado) => {
    const map = {
        pendiente:      'Pendiente',
        en_preparacion: 'En preparación',
        confirmado:     'Confirmado',
        preparando:     'Preparando',
        en_camino:      'En camino',
        enviado:        'Enviado',
        entregado:      'Entregado',
        cancelado:      'Cancelado',
        incidencia:     'Incidencia',
    };
    return map[estado] ?? estado.replace(/_/g, ' ');
};

const formatFecha = (dateStr) =>
    new Date(dateStr).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });

const formatHora = (dateStr) =>
    new Date(dateStr).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
</script>
