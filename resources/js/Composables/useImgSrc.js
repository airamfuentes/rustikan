/**
 * Returns the correct src for a product/tienda image.
 * If the stored value is already an absolute URL (http/https), use it directly.
 * Otherwise prefix with /storage/.
 */
export function imgSrc(path) {
    if (!path) return null;
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    return `/storage/${path}`;
}

/**
 * @error handler for <img> tags — replaces broken image with a local placeholder.
 * Usage: <img :src="imgSrc(path)" @error="onImgError" />
 */
export function onImgError(e) {
    e.target.src = '/images/logo.png';
    e.target.onerror = null;
}

/**
 * Returns the best available image for a tienda, with fallback chain.
 */
export function tiendaImg(tienda) {
    if (!tienda) return '/images/logo.png';
    const raw = tienda.imagen_portada || tienda.logo;
    return raw ? imgSrc(raw) : '/images/logo.png';
}
