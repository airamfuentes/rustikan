<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useLocale } from '@/Composables/useLocale';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';

const { locale, setLocale, initLocale, currentLocale, availableLocales } = useLocale();
const { t } = useI18n();
const { success } = useToasts();

const isOpen   = ref(false);
const rootRef  = ref(null);

onMounted(() => {
    initLocale();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleClickOutside = (event) => {
    if (rootRef.value && !rootRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const select = (code) => {
    if (code === locale.value) {
        isOpen.value = false;
        return;
    }
    const lang = availableLocales.find((l) => l.code === code);
    setLocale(code);
    isOpen.value = false;
    // Tras el cambio reactivo del locale, t() ya resuelve en el nuevo idioma.
    nextTick(() => {
        success(
            t('nav.language_changed'),
            t('nav.language_changed_to', { lang: lang?.label ?? code }),
        );
    });
};
</script>

<template>
    <div ref="rootRef" class="relative">
        <!-- Círculo del idioma activo -->
        <button
            type="button"
            :title="currentLocale.label"
            :aria-label="`Idioma: ${currentLocale.label}`"
            :aria-expanded="isOpen"
            aria-haspopup="listbox"
            @click.stop="toggle"
            class="flex h-9 w-9 items-center justify-center rounded-full border-2 bg-white dark:bg-gray-800 text-xl shadow-sm transition-all duration-200 hover:scale-110 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary-400"
            :class="isOpen ? 'border-primary-400 scale-110 shadow-md' : 'border-gray-200 dark:border-gray-600 hover:border-primary-300 dark:hover:border-primary-400'"
        >
            <span role="img" :aria-label="currentLocale.label" class="select-none leading-none">{{ currentLocale.flag }}</span>
        </button>

        <!-- Dropdown: círculos apilados hacia abajo -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
        >
            <div
                v-if="isOpen"
                role="listbox"
                :aria-label="`Seleccionar idioma`"
                class="absolute right-0 top-full z-50 mt-2 flex flex-col items-center gap-2"
            >
                <button
                    v-for="lang in availableLocales.filter(l => l.code !== locale)"
                    :key="lang.code"
                    type="button"
                    role="option"
                    :aria-selected="false"
                    :title="lang.label"
                    @click="select(lang.code)"
                    class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-xl shadow-sm transition-all duration-200 hover:scale-110 hover:border-primary-400 dark:hover:border-primary-400 hover:shadow-md focus:outline-none"
                >
                    <span role="img" :aria-label="lang.label" class="select-none leading-none">{{ lang.flag }}</span>
                </button>
            </div>
        </Transition>
    </div>
</template>
