<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.pedidos.index')" class="rounded-lg bg-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                        ← Volver
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Pedido #{{ pedido.id }}
                    </h2>
                </div>
                <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-3 py-1 text-sm font-semibold">
                    {{ estadoLabel(pedido.estado) }}
                </span>
            </div>
        </template>

        <!-- Toast Notifications -->
        <div class="pointer-events-none fixed inset-0 z-[60] flex flex-col items-end justify-start gap-3 p-6">
            <transition-group
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-black/5"
                >
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 mt-0.5">
                                <svg v-if="toast.type === 'success'" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-else-if="toast.type === 'error'" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <svg v-else class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ toast.title }}</p>
                                <p v-if="toast.message" class="mt-0.5 text-sm text-gray-500">{{ toast.message }}</p>
                            </div>
                            <button @click="removeToast(toast.id)" class="ml-2 shrink-0 text-gray-400 hover:text-gray-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">

                    <!-- Columna izquierda: items del pedido -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Productos -->
                        <div class="overflow-hidden rounded-2xl bg-white shadow">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Productos del pedido</h3>
                                <p class="text-sm text-gray-500">{{ pedido.items.length }} artículo{{ pedido.items.length !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <div
                                    v-for="item in pedido.items"
                                    :key="item.id"
                                    class="flex items-center gap-4 px-6 py-4"
                                >
                                    <img
                                        :src="item.producto_imagen || '/images/logo.png'"
                                        :alt="item.producto_nombre"
                                        class="h-14 w-14 flex-shrink-0 rounded-xl object-cover border border-gray-100"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 truncate">{{ item.producto_nombre }}</p>
                                        <p class="text-sm text-gray-500">{{ item.tienda_nombre }}</p>
                                        <p class="text-xs text-gray-400">{{ Number(item.precio_unitario).toFixed(2) }}€ × {{ item.cantidad }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="font-bold text-gray-900">{{ Number(item.subtotal).toFixed(2) }}€</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Totales -->
                            <div class="border-t border-gray-100 bg-gray-50 px-6 py-4 space-y-1.5">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>{{ Number(pedido.subtotal).toFixed(2) }}€</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Gastos de envío</span>
                                    <span :class="pedido.gastos_envio == 0 ? 'text-green-600 font-medium' : ''">
                                        {{ pedido.gastos_envio == 0 ? 'GRATIS' : Number(pedido.gastos_envio).toFixed(2) + '€' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-2 mt-2">
                                    <span>Total</span>
                                    <span>{{ Number(pedido.total).toFixed(2) }}€</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notas del pedido -->
                        <div v-if="pedido.notas" class="rounded-2xl bg-amber-50 border border-amber-100 px-6 py-4">
                            <h4 class="text-sm font-semibold text-amber-800 mb-1">Notas del cliente</h4>
                            <p class="text-sm text-amber-700">{{ pedido.notas }}</p>
                        </div>
                    </div>

                    <!-- Columna derecha: info + cambiar estado -->
                    <div class="space-y-6">

                        <!-- Info del cliente -->
                        <div class="rounded-2xl bg-white shadow overflow-hidden">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Cliente</h3>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-700 text-sm font-bold flex-shrink-0">
                                        {{ pedido.user.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ pedido.user.name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ pedido.user.email }}</p>
                                    </div>
                                </div>
                                <div v-if="pedido.telefono_contacto" class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ pedido.telefono_contacto }}
                                </div>
                                <div class="flex items-start gap-2 text-sm text-gray-600">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ pedido.direccion_envio }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info del pedido -->
                        <div class="rounded-2xl bg-white shadow overflow-hidden">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Detalles del pedido</h3>
                            </div>
                            <div class="px-6 py-4 space-y-2.5 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Nº pedido</span>
                                    <span class="font-medium text-gray-900">{{ pedido.numero_pedido }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Fecha</span>
                                    <span class="text-gray-900">{{ formatFecha(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Hora</span>
                                    <span class="text-gray-900">{{ formatHora(pedido.created_at) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500">Estado actual</span>
                                    <span :class="estadoBadgeClass(pedido.estado)" class="rounded-full px-2 py-0.5 text-xs font-semibold">
                                        {{ estadoLabel(pedido.estado) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Cambiar estado -->
                        <div class="rounded-2xl bg-white shadow overflow-hidden">
                            <div class="border-b border-gray-100 px-6 py-4">
                                <h3 class="font-semibold text-gray-900">Cambiar estado</h3>
                            </div>
                            <div class="px-6 py-4 space-y-3">
                                <p class="text-xs text-gray-500">Selecciona el nuevo estado del pedido</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="estado in estadosDisponibles"
                                        :key="estado.value"
                                        @click="cambiarEstado(estado.value)"
                                        :disabled="pedido.estado === estado.value || procesando"
                                        :class="[
                                            'flex flex-col items-center gap-1 rounded-xl border-2 px-3 py-2.5 text-xs font-semibold transition-all',
                                            pedido.estado === estado.value
                                                ? estado.activeClass + ' border-transparent cursor-default'
                                                : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50 cursor-pointer',
                                            procesando ? 'opacity-50 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        <span class="text-base">{{ estado.icon }}</span>
                                        <span>{{ estado.label }}</span>
                                        <span v-if="pedido.estado === estado.value" class="text-[10px] opacity-70">← Actual</span>
                                    </button>
                                </div>
                                <div v-if="procesando" class="flex items-center gap-2 text-sm text-gray-500">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Actualizando...
                                </div>
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
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    pedido: { type: Object, required: true },
});

// ── Toast ────────────────────────────────────────────────────────────────────
const toasts = ref([]);

const addToast = (type, title, message = '') => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message });
    setTimeout(() => removeToast(id), 5000);
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) addToast('success', '¡Éxito!', flash.success);
        if (flash.error)   addToast('error',   'Error',   flash.error);
        if (flash.info)    addToast('info',     'Info',    flash.info);
    },
    { deep: true, immediate: true },
);

// ── Estado ───────────────────────────────────────────────────────────────────
const procesando = ref(false);

const estadosDisponibles = [
    { value: 'pendiente',   label: 'Pendiente',   icon: '⏳', activeClass: 'bg-yellow-100 text-yellow-800 border-yellow-300' },
    { value: 'en_proceso',  label: 'En proceso',  icon: '🔄', activeClass: 'bg-blue-100 text-blue-800 border-blue-300' },
    { value: 'completado',  label: 'Completado',  icon: '✅', activeClass: 'bg-green-100 text-green-800 border-green-300' },
    { value: 'cancelado',   label: 'Cancelado',   icon: '❌', activeClass: 'bg-red-100 text-red-800 border-red-300' },
];

const estadoBadgeClass = (estado) => {
    const map = {
        pendiente:  'bg-yellow-100 text-yellow-800',
        en_proceso: 'bg-blue-100 text-blue-800',
        completado: 'bg-green-100 text-green-800',
        cancelado:  'bg-red-100 text-red-800',
    };
    return map[estado] ?? 'bg-gray-100 text-gray-800';
};

const estadoLabel = (estado) => {
    const map = {
        pendiente:  'Pendiente',
        en_proceso: 'En proceso',
        completado: 'Completado',
        cancelado:  'Cancelado',
    };
    return map[estado] ?? estado;
};

const cambiarEstado = (nuevoEstado) => {
    if (procesando.value || props.pedido.estado === nuevoEstado) return;

    procesando.value = true;
    router.patch(
        route('admin.pedidos.update', props.pedido.id),
        { estado: nuevoEstado },
        {
            preserveScroll: true,
            onSuccess: () => {
                addToast('success', 'Estado actualizado', `El pedido pasó a "${estadoLabel(nuevoEstado)}"`);
            },
            onError: () => {
                addToast('error', 'Error', 'No se pudo actualizar el estado del pedido.');
            },
            onFinish: () => {
                procesando.value = false;
            },
        }
    );
};

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatFecha = (dateStr) =>
    new Date(dateStr).toLocaleDateString('es-ES', {
        day: '2-digit', month: 'long', year: 'numeric',
    });

const formatHora = (dateStr) =>
    new Date(dateStr).toLocaleTimeString('es-ES', {
        hour: '2-digit', minute: '2-digit',
    });
</script>
