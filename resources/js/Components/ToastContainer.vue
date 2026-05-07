<script setup>
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import { useToasts } from '@/Composables/useToasts';

const { toasts, remove, success, error, info, warning } = useToasts();

// Procesar flash messages globales una sola vez por valor único.
// Usamos una huella concatenando los 4 tipos para detectar cambios reales.
const page = usePage();
let lastFingerprint = '';

const fingerprintFlash = (flash) => {
    if (!flash) return '';
    return [flash.success, flash.error, flash.info, flash.warning].map((v) => v ?? '').join('|');
};

watch(
    () => page.props.flash,
    (flash) => {
        const fp = fingerprintFlash(flash);
        if (!fp || fp === lastFingerprint) return;
        lastFingerprint = fp;
        if (flash.success) success('Éxito',         flash.success);
        if (flash.error)   error('Error',           flash.error);
        if (flash.info)    info('Información',      flash.info);
        if (flash.warning) warning('Advertencia',   flash.warning);
    },
    { deep: true, immediate: true }
);
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
               sm:left-auto sm:right-4 sm:bottom-auto sm:top-20
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
