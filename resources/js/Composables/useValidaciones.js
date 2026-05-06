// Helpers de validación que reflejan las reglas del backend.
// Devuelven null si el valor es válido o un string con el mensaje de error.

export const validarEmail = (valor) => {
    if (!valor) return null;
    const PATRON = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!PATRON.test(valor)) return 'Email no válido.';
    if (valor.length > 255) return 'Email demasiado largo.';
    const dominio = valor.split('@')[1]?.toLowerCase();
    const PERMITIDOS = [
        'gmail.com', 'googlemail.com',
        'outlook.com', 'outlook.es', 'hotmail.com', 'hotmail.es', 'live.com', 'msn.com',
        'yahoo.com', 'yahoo.es',
        'icloud.com', 'me.com', 'mac.com',
        'protonmail.com', 'proton.me',
        'aol.com', 'gmx.com', 'gmx.es',
        'rustikan.com',
    ];
    if (dominio && !PERMITIDOS.includes(dominio)) {
        return 'Solo se aceptan correos de proveedores conocidos (Gmail, Outlook, Yahoo, iCloud, Proton...).';
    }
    return null;
};

export const validarNombre = (valor, { min = 2, max = 60 } = {}) => {
    if (!valor) return 'Este campo es obligatorio.';
    const v = valor.trim();
    if (v.length < min) return `Mínimo ${min} caracteres.`;
    if (v.length > max) return `Máximo ${max} caracteres.`;
    if (!/^[\p{L}\s'\-]+$/u.test(v)) return 'Solo se permiten letras y espacios.';
    return null;
};

// Teléfono español: 9 dígitos comenzando por 6, 7, 8 o 9
export const validarTelefonoEs = (valor) => {
    if (!valor) return 'El teléfono es obligatorio.';
    const limpio = valor.replace(/\D/g, '');
    if (!/^[6-9]\d{8}$/.test(limpio)) {
        return 'Teléfono no válido. Debe tener 9 dígitos y empezar por 6, 7, 8 o 9.';
    }
    return null;
};

export const validarDireccion = (valor, { min = 5, max = 500 } = {}) => {
    if (!valor) return 'La dirección es obligatoria.';
    const v = valor.trim();
    if (v.length < min) return `Dirección demasiado corta (mínimo ${min} caracteres).`;
    if (v.length > max) return `Dirección demasiado larga (máximo ${max} caracteres).`;
    return null;
};

export const validarEdad = (valor, { min = 14, max = 120 } = {}) => {
    if (valor === '' || valor === null || valor === undefined) return null;
    const n = parseInt(valor, 10);
    if (isNaN(n)) return 'Edad no válida.';
    if (n < min) return `Debes tener al menos ${min} años.`;
    if (n > max) return `Edad no válida (máximo ${max}).`;
    return null;
};

export const validarPrecio = (valor, { min = 0, max = 99999.99 } = {}) => {
    if (valor === '' || valor === null || valor === undefined) return 'El precio es obligatorio.';
    const n = parseFloat(String(valor).replace(',', '.'));
    if (isNaN(n)) return 'Precio no válido.';
    if (n < min) return `El precio no puede ser menor que ${min}.`;
    if (n > max) return `Precio demasiado alto.`;
    return null;
};

export const validarStock = (valor, { min = 0, max = 999999 } = {}) => {
    if (valor === '' || valor === null || valor === undefined) return 'El stock es obligatorio.';
    const n = parseInt(valor, 10);
    if (isNaN(n)) return 'Stock no válido.';
    if (n < min) return `El stock no puede ser menor que ${min}.`;
    if (n > max) return `Stock demasiado alto.`;
    return null;
};

export const validarLatitud = (valor) => {
    if (valor === '' || valor === null || valor === undefined) return null;
    const n = parseFloat(String(valor).replace(',', '.'));
    if (isNaN(n)) return 'Latitud no válida.';
    if (n < -90 || n > 90) return 'La latitud debe estar entre -90 y 90.';
    return null;
};

export const validarLongitud = (valor) => {
    if (valor === '' || valor === null || valor === undefined) return null;
    const n = parseFloat(String(valor).replace(',', '.'));
    if (isNaN(n)) return 'Longitud no válida.';
    if (n < -180 || n > 180) return 'La longitud debe estar entre -180 y 180.';
    return null;
};

// CP español: 5 dígitos. Para Lanzarote: 35500–35599.
export const validarCP = (valor, { soloLanzarote = false } = {}) => {
    if (!valor) return 'El código postal es obligatorio.';
    if (!/^\d{5}$/.test(valor)) return 'El código postal debe tener 5 dígitos.';
    if (soloLanzarote) {
        const n = parseInt(valor, 10);
        if (n < 35500 || n > 35599) return 'Solo enviamos a Lanzarote (35500–35599).';
    }
    return null;
};

// URL básica
export const validarUrl = (valor) => {
    if (!valor) return null;
    try {
        const u = new URL(valor);
        if (!['http:', 'https:'].includes(u.protocol)) return 'La URL debe empezar por http:// o https://.';
    } catch {
        return 'URL no válida.';
    }
    if (valor.length > 200) return 'URL demasiado larga.';
    return null;
};

// Fortaleza de contraseña: min 8, mayúscula + minúscula + número + símbolo
export const evaluarPassword = (valor) => {
    const checks = {
        longitud:   (valor || '').length >= 8,
        mayuscula:  /[A-Z]/.test(valor || ''),
        minuscula:  /[a-z]/.test(valor || ''),
        numero:     /\d/.test(valor || ''),
        simbolo:    /[^A-Za-z0-9]/.test(valor || ''),
    };
    const ok = Object.values(checks).filter(Boolean).length;
    let nivel = 'muy_debil';
    if (ok === 5) nivel = 'muy_fuerte';
    else if (ok === 4) nivel = 'fuerte';
    else if (ok === 3) nivel = 'media';
    else if (ok === 2) nivel = 'debil';
    return { checks, ok, nivel, valida: ok === 5 };
};

// Coincidencia de número de tarjeta con algoritmo Luhn + longitud
export const validarTarjeta = (valor) => {
    if (!valor) return 'Número de tarjeta obligatorio.';
    const digits = valor.replace(/\D/g, '');
    if (digits.length < 13 || digits.length > 19) return 'Número de tarjeta no válido.';
    let sum = 0;
    let alt = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let n = parseInt(digits[i]);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n; alt = !alt;
    }
    if (sum % 10 !== 0) return 'Número de tarjeta no válido.';
    return null;
};

// Caducidad MM/AA: válida y no caducada
export const validarCaducidad = (valor) => {
    if (!valor) return 'Fecha de caducidad obligatoria.';
    const m = valor.match(/^(\d{2})\/(\d{2})$/);
    if (!m) return 'Formato debe ser MM/AA.';
    const mes = parseInt(m[1], 10);
    const anio = 2000 + parseInt(m[2], 10);
    if (mes < 1 || mes > 12) return 'Mes no válido.';
    const ahora = new Date();
    if (anio < ahora.getFullYear() ||
        (anio === ahora.getFullYear() && mes < ahora.getMonth() + 1)) {
        return 'La tarjeta está caducada.';
    }
    if (anio > ahora.getFullYear() + 20) return 'Año demasiado lejano.';
    return null;
};

export const validarCVV = (valor) => {
    if (!valor) return 'CVV obligatorio.';
    if (!/^\d{3,4}$/.test(valor)) return 'CVV no válido (3 o 4 dígitos).';
    return null;
};
