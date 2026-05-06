<script setup>
import { computed } from 'vue';
import { evaluarPassword } from '@/Composables/useValidaciones';

const props = defineProps({
    password: { type: String, default: '' },
});

const resultado = computed(() => evaluarPassword(props.password));

const colorBarra = computed(() => ({
    muy_debil:  'bg-red-500',
    debil:      'bg-orange-500',
    media:      'bg-yellow-500',
    fuerte:     'bg-lime-500',
    muy_fuerte: 'bg-green-500',
}[resultado.value.nivel]));

const textoNivel = computed(() => ({
    muy_debil:  'Muy débil',
    debil:      'Débil',
    media:      'Media',
    fuerte:     'Fuerte',
    muy_fuerte: 'Muy fuerte',
}[resultado.value.nivel]));

const items = computed(() => [
    { ok: resultado.value.checks.longitud,  texto: 'Mínimo 8 caracteres' },
    { ok: resultado.value.checks.mayuscula, texto: 'Una mayúscula (A-Z)' },
    { ok: resultado.value.checks.minuscula, texto: 'Una minúscula (a-z)' },
    { ok: resultado.value.checks.numero,    texto: 'Un número (0-9)' },
    { ok: resultado.value.checks.simbolo,   texto: 'Un símbolo (!@#$...)' },
]);
</script>

<template>
    <div v-if="password" class="mt-2 space-y-2">
        <!-- Barra de fortaleza -->
        <div class="flex items-center gap-2">
            <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div :class="['h-full rounded-full transition-all duration-300', colorBarra]"
                     :style="{ width: (resultado.ok / 5 * 100) + '%' }"></div>
            </div>
            <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ textoNivel }}</span>
        </div>
        <!-- Checklist -->
        <ul class="grid grid-cols-1 gap-1 sm:grid-cols-2">
            <li v-for="(item, i) in items" :key="i"
                :class="['flex items-center gap-1.5 text-xs transition-colors',
                    item.ok ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500']">
                <svg v-if="item.ok" class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" stroke-width="2" />
                </svg>
                {{ item.texto }}
            </li>
        </ul>
    </div>
</template>
