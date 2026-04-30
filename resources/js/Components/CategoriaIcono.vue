<script setup>
import { computed } from 'vue';
import { Apple, Beef, Fish, Croissant, Milk, Wine, Hammer, Store } from 'lucide-vue-next';

const props = defineProps({
    slug: { type: String, default: null },
    icono: { type: String, default: null },
    class: { type: String, default: 'h-5 w-5' },
});

const ICONOS_POR_SLUG = {
    'frutas-y-verduras': Apple,
    'carnes':            Beef,
    'pescados-y-mariscos': Fish,
    'panaderia':         Croissant,
    'lacteos-y-quesos':  Milk,
    'vinoteca':          Wine,
    'artesania':         Hammer,
};

const ICONOS_POR_EMOJI = {
    '🍎': Apple,
    '🥩': Beef,
    '🐟': Fish,
    '🥖': Croissant,
    '🧀': Milk,
    '🍷': Wine,
    '🏺': Hammer,
};

const componente = computed(() => {
    if (props.slug && ICONOS_POR_SLUG[props.slug]) return ICONOS_POR_SLUG[props.slug];
    if (props.icono && ICONOS_POR_EMOJI[props.icono]) return ICONOS_POR_EMOJI[props.icono];
    return Store;
});
</script>

<template>
    <component :is="componente" :class="$props.class" />
</template>
