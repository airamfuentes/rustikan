<template>
    <div ref="menuRef" class="relative">
        <!-- Botón campana -->
        <button
            @click="toggle"
            class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
            :class="abierto ? 'bg-gray-100 dark:bg-gray-700' : ''"
            title="Notificaciones"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Badge contador -->
            <span
                v-if="count > 0"
                class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white leading-none"
            >{{ count > 9 ? '9+' : count }}</span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 scale-95 translate-y-1"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-1"
        >
            <div
                v-if="abierto"
                class="absolute right-0 top-11 z-50 w-80 sm:w-96 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl overflow-hidden"
            >
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-4 py-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Notificaciones
                        <span v-if="count > 0" class="ml-1.5 rounded-full bg-red-100 dark:bg-red-900/30 px-1.5 py-0.5 text-xs font-bold text-red-600 dark:text-red-400">{{ count }}</span>
                    </h3>
                    <button
                        v-if="count > 0"
                        @click="marcarTodas"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                    >Marcar todas como leídas</button>
                </div>

                <!-- Lista -->
                <div class="max-h-96 overflow-y-auto">
                    <div v-if="cargando" class="flex items-center justify-center py-8">
                        <svg class="h-5 w-5 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                    </div>

                    <div v-else-if="notificaciones.length === 0" class="flex flex-col items-center py-10 text-center">
                        <svg class="mb-2 h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">Sin notificaciones</p>
                    </div>

                    <template v-else>
                        <div
                            v-for="n in notificaciones"
                            :key="n.id"
                            @click="abrirNotificacion(n)"
                            :class="['flex gap-3 px-4 py-3 border-b border-gray-50 dark:border-gray-700/50 cursor-pointer transition-colors last:border-0',
                                n.leida
                                    ? 'hover:bg-gray-50 dark:hover:bg-gray-700/30'
                                    : 'bg-primary-50/60 dark:bg-primary-900/10 hover:bg-primary-50 dark:hover:bg-primary-900/20']"
                        >
                            <!-- Icono colored dot -->
                            <div :class="['mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-sm',
                                n.color === 'green'   ? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400' :
                                n.color === 'red'     ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400' :
                                n.color === 'orange'  ? 'bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-400' :
                                n.color === 'blue'    ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400' :
                                                        'bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400']">
                                <svg v-if="n.icono === 'check'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <svg v-else-if="n.icono === 'x'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <svg v-else-if="n.icono === 'cart'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <svg v-else-if="n.icono === 'store'" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p :class="['text-sm font-medium leading-tight', n.leida ? 'text-gray-700 dark:text-gray-300' : 'text-gray-900 dark:text-white']">
                                        {{ n.titulo }}
                                    </p>
                                    <span v-if="!n.leida" class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-primary-500"></span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ n.mensaje }}</p>
                                <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">{{ formatDate(n.created_at) }}</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page    = usePage();
const abierto = ref(false);
const cargando = ref(false);
const notificaciones = ref([]);
const count   = ref(page.props.notificacionesCount ?? 0);
const menuRef = ref(null);

// Sync count when Inertia shared props update
watch(() => page.props.notificacionesCount, (val) => {
    count.value = val ?? 0;
});

const toggle = async () => {
    abierto.value = !abierto.value;
    if (abierto.value) {
        await cargar();
    } else if (notificaciones.value.length > 0) {
        // Al cerrar tras haber visto las notis: borrarlas todas
        await marcarTodas();
    }
};

const cargar = async () => {
    cargando.value = true;
    try {
        const res = await fetch(route('notificaciones.index'), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await res.json();
        notificaciones.value = data.notificaciones ?? [];
        count.value = data.no_leidas ?? 0;
    } catch (e) {
        // silently fail
    } finally {
        cargando.value = false;
    }
};

const abrirNotificacion = async (n) => {
    // Marcar como leída → la borra del backend
    await fetch(route('notificaciones.leer', n.id), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
    });
    notificaciones.value = notificaciones.value.filter(x => x.id !== n.id);
    count.value = Math.max(0, count.value - 1);
    abierto.value = false;
    if (n.enlace) {
        router.visit(n.enlace);
    }
};

const marcarTodas = async () => {
    await fetch(route('notificaciones.leer-todas'), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
    });
    notificaciones.value = [];
    count.value = 0;
};

const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    const now  = new Date();
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60)   return 'Ahora mismo';
    if (diff < 3600) return `Hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Hace ${Math.floor(diff / 3600)} h`;
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};

// Cerrar al hacer click fuera
const handleOutsideClick = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        abierto.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', handleOutsideClick));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleOutsideClick));
</script>
