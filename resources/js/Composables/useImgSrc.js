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
