// Directivas para validar inputs y bloquear caracteres no permitidos.
// Cubren los casos en que <input type="number"> no impide escribir 'e', '+', '-', letras, etc.

const KEYS_PERMITIDAS = ['Backspace', 'Tab', 'Escape', 'Enter', 'Delete',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];

const esTeclaControl = (e) => {
    if (KEYS_PERMITIDAS.includes(e.key)) return true;
    // Permitir combinaciones Ctrl/Cmd (copiar, pegar, seleccionar todo, etc.)
    if (e.ctrlKey || e.metaKey) return true;
    return false;
};

const dispararInput = (el) => {
    el.dispatchEvent(new Event('input', { bubbles: true }));
};

// ─── Solo dígitos enteros (no permite signos, decimales, letras) ─────────────
export const vOnlyDigits = {
    mounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const onKeydown = (e) => {
            if (esTeclaControl(e)) return;
            if (!/^\d$/.test(e.key)) e.preventDefault();
        };

        const onInput = (e) => {
            const limpio = e.target.value.replace(/\D/g, '');
            if (e.target.value !== limpio) {
                e.target.value = limpio;
                dispararInput(e.target);
            }
        };

        const onPaste = (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const limpio = texto.replace(/\D/g, '');
            const start = e.target.selectionStart ?? e.target.value.length;
            const end   = e.target.selectionEnd   ?? e.target.value.length;
            e.target.value = e.target.value.slice(0, start) + limpio + e.target.value.slice(end);
            dispararInput(e.target);
        };

        const onWheel = (e) => { if (document.activeElement === e.target) e.target.blur(); };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('input', onInput);
        input.addEventListener('paste', onPaste);
        input.addEventListener('wheel', onWheel);

        el._onlyDigits = { input, onKeydown, onInput, onPaste, onWheel };
    },
    unmounted(el) {
        const r = el._onlyDigits;
        if (!r) return;
        r.input.removeEventListener('keydown', r.onKeydown);
        r.input.removeEventListener('input', r.onInput);
        r.input.removeEventListener('paste', r.onPaste);
        r.input.removeEventListener('wheel', r.onWheel);
    },
};

// ─── Decimales positivos: dígitos + un único separador (. o ,) ────────────────
export const vOnlyDecimal = {
    mounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const sanitizar = (valor) => {
            // Convertir coma a punto para uniformidad
            let v = valor.replace(/,/g, '.');
            // Solo dígitos y puntos
            v = v.replace(/[^\d.]/g, '');
            // Un único punto: el primero que aparezca
            const primerPunto = v.indexOf('.');
            if (primerPunto !== -1) {
                v = v.slice(0, primerPunto + 1) + v.slice(primerPunto + 1).replace(/\./g, '');
            }
            return v;
        };

        const onKeydown = (e) => {
            if (esTeclaControl(e)) return;
            // Permitir dígitos y un único punto/coma
            if (/^\d$/.test(e.key)) return;
            if (e.key === '.' || e.key === ',') {
                if (e.target.value.includes('.') || e.target.value.includes(',')) {
                    e.preventDefault();
                }
                return;
            }
            e.preventDefault();
        };

        const onInput = (e) => {
            const limpio = sanitizar(e.target.value);
            if (e.target.value !== limpio) {
                e.target.value = limpio;
                dispararInput(e.target);
            }
        };

        const onPaste = (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const limpio = sanitizar(texto);
            const start = e.target.selectionStart ?? e.target.value.length;
            const end   = e.target.selectionEnd   ?? e.target.value.length;
            const nuevo = e.target.value.slice(0, start) + limpio + e.target.value.slice(end);
            e.target.value = sanitizar(nuevo);
            dispararInput(e.target);
        };

        const onWheel = (e) => { if (document.activeElement === e.target) e.target.blur(); };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('input', onInput);
        input.addEventListener('paste', onPaste);
        input.addEventListener('wheel', onWheel);

        el._onlyDecimal = { input, onKeydown, onInput, onPaste, onWheel };
    },
    unmounted(el) {
        const r = el._onlyDecimal;
        if (!r) return;
        r.input.removeEventListener('keydown', r.onKeydown);
        r.input.removeEventListener('input', r.onInput);
        r.input.removeEventListener('paste', r.onPaste);
        r.input.removeEventListener('wheel', r.onWheel);
    },
};

// ─── Decimales con signo (coordenadas: -13.6328, 29.0469) ────────────────────
export const vOnlySignedDecimal = {
    mounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const sanitizar = (valor) => {
            let v = valor.replace(/,/g, '.');
            // Conservar signo solo al inicio
            const negativo = v.startsWith('-');
            v = v.replace(/-/g, '');
            v = v.replace(/[^\d.]/g, '');
            const primerPunto = v.indexOf('.');
            if (primerPunto !== -1) {
                v = v.slice(0, primerPunto + 1) + v.slice(primerPunto + 1).replace(/\./g, '');
            }
            return negativo ? '-' + v : v;
        };

        const onKeydown = (e) => {
            if (esTeclaControl(e)) return;
            if (/^\d$/.test(e.key)) return;
            if (e.key === '.' || e.key === ',') {
                if (e.target.value.includes('.') || e.target.value.includes(',')) e.preventDefault();
                return;
            }
            if (e.key === '-') {
                // selectionStart es null en type="number" — bloquear solo si ya hay signo
                if (e.target.value.startsWith('-')) e.preventDefault();
                return;
            }
            e.preventDefault();
        };

        const onInput = (e) => {
            const limpio = sanitizar(e.target.value);
            if (e.target.value !== limpio) {
                e.target.value = limpio;
                dispararInput(e.target);
            }
        };

        const onPaste = (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const limpio = sanitizar(texto);
            e.target.value = limpio;
            dispararInput(e.target);
        };

        const onWheel = (e) => { if (document.activeElement === e.target) e.target.blur(); };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('input', onInput);
        input.addEventListener('paste', onPaste);
        input.addEventListener('wheel', onWheel);

        el._onlySignedDecimal = { input, onKeydown, onInput, onPaste, onWheel };
    },
    unmounted(el) {
        const r = el._onlySignedDecimal;
        if (!r) return;
        r.input.removeEventListener('keydown', r.onKeydown);
        r.input.removeEventListener('input', r.onInput);
        r.input.removeEventListener('paste', r.onPaste);
        r.input.removeEventListener('wheel', r.onWheel);
    },
};

// ─── Solo letras y espacios (nombres, ciudades, etc.) ─────────────────────────
export const vOnlyLetters = {
    mounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        // Letras Unicode (incluye acentos, ñ, etc.) + espacios + apóstrofo + guion
        const PATRON = /^[\p{L}\s'\-]$/u;
        const LIMPIA = /[^\p{L}\s'\-]/gu;

        const onKeydown = (e) => {
            if (esTeclaControl(e)) return;
            if (!PATRON.test(e.key)) e.preventDefault();
        };

        const onInput = (e) => {
            const limpio = e.target.value.replace(LIMPIA, '');
            if (e.target.value !== limpio) {
                e.target.value = limpio;
                dispararInput(e.target);
            }
        };

        const onPaste = (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const limpio = texto.replace(LIMPIA, '');
            const start = e.target.selectionStart ?? e.target.value.length;
            const end   = e.target.selectionEnd   ?? e.target.value.length;
            e.target.value = e.target.value.slice(0, start) + limpio + e.target.value.slice(end);
            dispararInput(e.target);
        };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('input', onInput);
        input.addEventListener('paste', onPaste);

        el._onlyLetters = { input, onKeydown, onInput, onPaste };
    },
    unmounted(el) {
        const r = el._onlyLetters;
        if (!r) return;
        r.input.removeEventListener('keydown', r.onKeydown);
        r.input.removeEventListener('input', r.onInput);
        r.input.removeEventListener('paste', r.onPaste);
    },
};

// ─── Teléfono: dígitos, +, espacios, guiones, paréntesis ─────────────────────
export const vOnlyPhone = {
    mounted(el) {
        const input = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!input) return;

        const PATRON = /^[\d+\s\-().]$/;
        const LIMPIA = /[^\d+\s\-().]/g;

        const onKeydown = (e) => {
            if (esTeclaControl(e)) return;
            if (!PATRON.test(e.key)) e.preventDefault();
        };

        const onInput = (e) => {
            const limpio = e.target.value.replace(LIMPIA, '');
            if (e.target.value !== limpio) {
                e.target.value = limpio;
                dispararInput(e.target);
            }
        };

        const onPaste = (e) => {
            e.preventDefault();
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            const limpio = texto.replace(LIMPIA, '');
            const start = e.target.selectionStart ?? e.target.value.length;
            const end   = e.target.selectionEnd   ?? e.target.value.length;
            e.target.value = e.target.value.slice(0, start) + limpio + e.target.value.slice(end);
            dispararInput(e.target);
        };

        input.addEventListener('keydown', onKeydown);
        input.addEventListener('input', onInput);
        input.addEventListener('paste', onPaste);

        el._onlyPhone = { input, onKeydown, onInput, onPaste };
    },
    unmounted(el) {
        const r = el._onlyPhone;
        if (!r) return;
        r.input.removeEventListener('keydown', r.onKeydown);
        r.input.removeEventListener('input', r.onInput);
        r.input.removeEventListener('paste', r.onPaste);
    },
};
