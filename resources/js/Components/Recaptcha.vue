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
    if (window.grecaptcha && window.grecaptcha.render && container.value) {
        widgetId = window.grecaptcha.render(container.value, {
            sitekey:          props.sitekey,
            callback:         (token) => emit('verify', token),
            'expired-callback': ()    => emit('expire'),
            'error-callback':   ()    => emit('error'),
        });
        clearInterval(pollTimer);
    }
};

onMounted(() => {
    // Poll until the reCAPTCHA script (loaded async in blade) is ready
    pollTimer = setInterval(tryRender, 100);
});

onBeforeUnmount(() => {
    clearInterval(pollTimer);
});
</script>

<template>
    <div ref="container" />
</template>
