<script>
// Estado y listener a nivel de módulo (sobreviven a re-montajes del
// componente). Esto evita que un re-render del root (por ejemplo, cuando
// cambia el rol o se muestra/oculta ChatIA) vuelva a procesar el mismo flash.
let inicialProcesado = false;
let listenerRegistrado = false;
</script>

<script setup>
import { usePage, router } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import { useToasts } from '@/Composables/useToasts';

const { toasts, remove, success, error, info, warning } = useToasts();
const page = usePage();

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
if (!listenerRegistrado) {
    listenerRegistrado = true;
    router.on('success', (event) => {
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
               sm:left-auto sm:right-4 sm:bottom-auto sm:top-24
               sm:w-full sm:max-w-sm
               items-stretch sm:items-end
               pb-[env(safe-area-inset-bottom)]"
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
