import { computed } from 'vue';
import { useLocale } from './useLocale';
import es from '@/i18n/es';
import en from '@/i18n/en';
import fr from '@/i18n/fr';
import de from '@/i18n/de';
import it from '@/i18n/it';

const TRANSLATIONS = { es, en, fr, de, it };

export function useI18n() {
    const { locale } = useLocale();

    const translations = computed(() => TRANSLATIONS[locale.value] ?? TRANSLATIONS.es);

    /**
     * Translate a dot-notation key, e.g. t('nav.login')
     * Supports simple {n} interpolation.
     */
    const t = (key, params = {}) => {
        const parts = key.split('.');
        let value = translations.value;
        for (const part of parts) {
            value = value?.[part];
            if (value === undefined) break;
        }
        if (value === undefined) {
            // Fallback to Spanish
            let fallback = TRANSLATIONS.es;
            for (const part of parts) {
                fallback = fallback?.[part];
                if (fallback === undefined) break;
            }
            value = fallback ?? key;
        }
        if (typeof value !== 'string') return key;
        return Object.entries(params).reduce(
            (str, [k, v]) => str.replaceAll(`{${k}}`, v),
            value
        );
    };

    /**
     * Translate that returns raw values (strings, arrays, objects).
     * Falls back to ES, then to provided fallback, then to {} when nothing matches.
     * Use this when the translation is structured (e.g. an array of sections).
     */
    const tr = (key, fallback = null) => {
        const parts = key.split('.');
        let value = translations.value;
        for (const part of parts) {
            value = value?.[part];
            if (value === undefined) break;
        }
        if (value !== undefined) return value;
        let es = TRANSLATIONS.es;
        for (const part of parts) {
            es = es?.[part];
            if (es === undefined) break;
        }
        return es !== undefined ? es : fallback;
    };

    return { t, tr, locale };
}
