// Sistema de toasts global. Singleton: cualquier componente que importe
// useToasts() comparte la misma cola.
//
// Uso:
//   import { useToasts } from '@/Composables/useToasts';
//   const { success, error, info, warning } = useToasts();
//   success('Pedido creado');
//   error('Error al guardar', 'El email ya existe');
//
// El contenedor visual se monta UNA sola vez (ToastContainer.vue) en cada layout.

import { ref } from 'vue';

const toasts = ref([]);
let nextId = 1;

const DEFAULT_DURATION = 5000;

const push = (type, title, message = '', duration = DEFAULT_DURATION) => {
    const id = nextId++;
    toasts.value.push({ id, type, title, message, duration });
    return id;
};

const remove = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
};

const clear = () => {
    toasts.value = [];
};

export function useToasts() {
    return {
        toasts,
        // Métodos abreviados
        success: (title, message = '', duration) => push('success', title, message, duration),
        error:   (title, message = '', duration) => push('error',   title, message, duration ?? 7000),
        info:    (title, message = '', duration) => push('info',    title, message, duration),
        warning: (title, message = '', duration) => push('warning', title, message, duration ?? 6000),
        // Genérico + control
        push,
        remove,
        clear,
    };
}
