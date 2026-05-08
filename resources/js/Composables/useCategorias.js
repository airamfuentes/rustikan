import { useI18n } from './useI18n';

/**
 * Devuelve helpers para mostrar el nombre y la descripción de una categoría
 * traducidos según el idioma activo. Si no hay traducción para el slug,
 * cae al valor original guardado en BBDD.
 */
export function useCategorias() {
    const { t } = useI18n();

    const nombre = (cat) => {
        if (!cat) return '';
        const key = `categories.${cat.slug}.nombre`;
        const tr = t(key);
        return tr === key ? (cat.nombre ?? '') : tr;
    };

    const descripcion = (cat) => {
        if (!cat) return '';
        const key = `categories.${cat.slug}.desc`;
        const tr = t(key);
        return tr === key ? (cat.descripcion ?? '') : tr;
    };

    return { nombre, descripcion };
}
