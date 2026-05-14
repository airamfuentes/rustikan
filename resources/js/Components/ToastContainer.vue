<script>
// Estado y listener a nivel de módulo (sobreviven a re-montajes del
// componente). Esto evita que un re-render del root (por ejemplo, cuando
// cambia el rol o se muestra/oculta ChatIA) vuelva a procesar el mismo flash.
let inicialProcesado = false;
let listenerRegistrado = false;
</script>

<script setup>
import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import { useToasts } from '@/Composables/useToasts';

const { toasts, remove, success, error, info, warning } = useToasts();
const page = usePage();

// En páginas de auth (login/register/forgot/reset/verify) no hay navbar y el
// LanguageSwitcher está fijo arriba-derecha. Para que el toast no choque con
// el dropdown de banderas, bajamos su posición vertical solo en esas rutas.
const esPaginaAuth = computed(() => {
    const url = page.url ?? '';
    return /^\/(login|register|forgot-password|reset-password|verify-email|confirm-password)/.test(url);
});

const procesarFlash = (flash) => {
    if (!flash) return;
    if (flash.success) success('Éxito',        flash.success);
    if (flash.error)   error('Error',          flash.error);
    if (flash.info)    info('Información',     flash.info);
    if (flash.warning) warning('Advertencia',  flash.warning);
};

// 1) Flash inicial (de la primera carga): se procesa una sola vez en toda
// la vida de la app. Las posteriores navegaciones de back/forward no
// disparan el evento `success` de Inertia, por lo que el flash restaurado
// del cache de historial NO genera toasts duplicados.
if (!inicialProcesado) {
    inicialProcesado = true;
    procesarFlash(page.props.flash);
}

// 2) Listener global de navegaciones de Inertia exitosas. Solo se registra
// una vez a nivel de módulo, pase lo que pase con el montaje del componente.
//
// IMPORTANTE: cuando Inertia hace un partial reload (`router.reload({ only: [..] })`
// o `router.get(url, params, { only: [..] })`), el servidor NO incluye los
// shared props (entre ellos `flash`), pero Inertia fusiona la respuesta con
// el cache anterior. Por eso `event.detail.page.props.flash` viene del cache
// y dispararía un toast duplicado en pantalla.
//
// Para evitarlo, ignoramos las navegaciones partial salvo que `flash` se haya
// pedido explícitamente.
if (!listenerRegistrado) {
    listenerRegistrado = true;
    router.on('success', (event) => {
        const only = event.detail?.visit?.only ?? [];
        const esPartial = Array.isArray(only) && only.length > 0;
        if (esPartial && !only.includes('flash')) return;
        procesarFlash(event.detail?.page?.props?.flash ?? null);
    });
}
</script>

<template>
    <!--
        Posicionamiento:
        - Móvil: ocupa todo el ancho con padding 0.75rem y se ancla abajo, pero
          DEJANDO espacio para el botón flotante de chat (~5rem).
        - Desktop (sm+): esquina superior derecha como hasta ahora.
    -->
    <div
        class="pointer-events-none fixed z-[9999] flex flex-col gap-3
               left-3 right-3 bottom-20
               sm:left-auto sm:right-4 sm:bottom-auto
               sm:w-full sm:max-w-sm
               items-stretch sm:items-end
               pb-[env(safe-area-inset-bottom)]"
        :class="esPaginaAuth ? 'sm:top-48' : 'sm:top-24'"
    >
        <Toast
            v-for="(toast, index) in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            :duration="toast.duration"
            :active="index === 0"
            @close="remove(toast.id)"
        />
    </div>
</template>
