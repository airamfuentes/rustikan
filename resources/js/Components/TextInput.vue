<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-tierra-300 bg-white shadow-sm transition-all duration-200 focus:border-tierra-500 focus:ring-2 focus:ring-tierra-400"
        v-model="model"
        ref="input"
    />
</template>
