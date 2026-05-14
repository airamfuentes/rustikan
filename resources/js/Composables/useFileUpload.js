// Helpers compartidos para subir archivos.
// Centraliza la validación de tamaño y formato y devuelve toasts coherentes
// en el idioma activo.

import { useToasts } from '@/Composables/useToasts';
import { useI18n } from '@/Composables/useI18n';

const MB = 1024 * 1024;
export const MAX_FILE_MB = 5;
const MAX_FILE_BYTES = MAX_FILE_MB * MB;

export function useFileUpload() {
    const { error: toastError } = useToasts();
    const { t } = useI18n();

    /**
     * Devuelve true si el archivo cumple el límite. Si no, dispara un toast
     * de error y devuelve false.
     *
     * @param {File} file
     * @param {object} [opts]
     * @param {number} [opts.maxMb=5] Tamaño máximo en MB.
     * @param {string[]} [opts.accept] Extensiones permitidas (sin el punto).
     *                                 Si se pasa y no encaja, también rechaza.
     * @returns {boolean}
     */
    const validate = (file, opts = {}) => {
        if (!file) return false;
        const maxMb = opts.maxMb ?? MAX_FILE_MB;
        const maxBytes = maxMb * MB;

        // Extensión
        if (opts.accept?.length) {
            const ext = (file.name.split('.').pop() ?? '').toLowerCase();
            if (!opts.accept.includes(ext)) {
                toastError(
                    t('upload.invalid_title'),
                    t('upload.invalid_msg', { exts: opts.accept.join(', ').toUpperCase() }),
                );
                return false;
            }
        }

        // Tamaño
        if (file.size > maxBytes) {
            const actual = (file.size / MB).toFixed(1);
            toastError(
                t('upload.too_big_title'),
                t('upload.too_big_msg', { max: maxMb, actual }),
            );
            return false;
        }
        return true;
    };

    return { validate, MAX_FILE_MB };
}
