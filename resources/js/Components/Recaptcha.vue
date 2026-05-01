<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    sitekey: { type: String, required: true },
});

const emit = defineEmits(['verify', 'expire', 'error']);

const container = ref(null);
let widgetId  = null;
let pollTimer = null;

const tryRender = () => {
    if (window.turnstile && container.value) {
        widgetId = window.turnstile.render(container.value, {
            sitekey:          props.sitekey,
            callback:         (token) => emit('verify', token),
            'expired-callback': ()    => emit('expire'),
            'error-callback':   ()    => emit('error'),
        });
        clearInterval(pollTimer);
    }
};

onMounted(() => {
    pollTimer = setInterval(tryRender, 100);
});

onBeforeUnmount(() => {
    clearInterval(pollTimer);
    if (window.turnstile && widgetId !== null) {
        window.turnstile.remove(widgetId);
    }
});

const reset = () => {
    if (window.turnstile && widgetId !== null) {
        window.turnstile.reset(widgetId);
    }
};

defineExpose({ reset });
</script>

<template>
    <div ref="container" />
</template>
