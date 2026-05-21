import { ref, computed } from 'vue';

/**
 * Locale state compartido a nivel de módulo.
 * Al ser una ref a nivel de módulo, todos los componentes que importen
 * este composable comparten el mismo estado reactivo.
 */
const locale = ref('es');

const AVAILABLE_LOCALES = [
    { code: 'es', label: 'Español',  countryCode: 'es' },
    { code: 'en', label: 'English',  countryCode: 'gb' },
    { code: 'fr', label: 'Français', countryCode: 'fr' },
    { code: 'de', label: 'Deutsch',  countryCode: 'de' },
    { code: 'it', label: 'Italiano', countryCode: 'it' },
];

const STORAGE_KEY = 'rustikan_locale';

/**
 * Composable para gestionar el idioma de la aplicación.
 *
 * @returns {object} locale, setLocale, initLocale, availableLocales, currentLocale
 */
export function useLocale() {
    /** Inicializa el idioma desde localStorage (llamar en onMounted del root) */
    const initLocale = () => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            const isValid = AVAILABLE_LOCALES.some((l) => l.code === saved);
            if (saved && isValid) {
                locale.value = saved;
            }
        } catch {
            // localStorage no disponible (SSR / modo privado restringido)
        }
    };

    /** Cambia el idioma activo y lo persiste */
    const setLocale = (code) => {
        const isValid = AVAILABLE_LOCALES.some((l) => l.code === code);
        if (!isValid) return;
        locale.value = code;
        try {
            localStorage.setItem(STORAGE_KEY, code);
        } catch {
            // Ignorar si localStorage no está disponible
        }
    };

    /** Datos del idioma actualmente seleccionado */
    const currentLocale = computed(
        () => AVAILABLE_LOCALES.find((l) => l.code === locale.value) ?? AVAILABLE_LOCALES[0]
    );

    return {
        locale,
        setLocale,
        initLocale,
        currentLocale,
        availableLocales: AVAILABLE_LOCALES,
    };
}
