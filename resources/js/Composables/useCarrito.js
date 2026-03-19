import { ref, computed, watch } from 'vue';

// ─── Estado singleton ───────────────────────────────────────────────────────
// La instancia es única: todos los componentes comparten el mismo carrito.
const STORAGE_KEY = 'rustikan_carrito';

const leerStorage = () => {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]');
    } catch {
        return [];
    }
};

const items = ref(leerStorage());

// Persistir automáticamente en localStorage ante cualquier cambio
watch(items, (valor) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(valor));
}, { deep: true });

// ─── Composable ─────────────────────────────────────────────────────────────
export function useCarrito() {

    /** Número total de unidades en el carrito */
    const totalItems = computed(() =>
        items.value.reduce((sum, item) => sum + item.cantidad, 0)
    );

    /** Precio total del carrito */
    const totalPrecio = computed(() =>
        items.value.reduce((sum, item) => sum + item.precio * item.cantidad, 0)
    );

    /** Items agrupados por tienda para mostrar secciones */
    const itemsAgrupadosPorTienda = computed(() => {
        const grupos = {};

        items.value.forEach((item) => {
            if (!grupos[item.tienda_id]) {
                grupos[item.tienda_id] = {
                    tienda_id:     item.tienda_id,
                    tienda_nombre: item.tienda_nombre,
                    tienda_slug:   item.tienda_slug,
                    items:         [],
                    subtotal:      0,
                };
            }
            grupos[item.tienda_id].items.push(item);
            grupos[item.tienda_id].subtotal += item.precio * item.cantidad;
        });

        return Object.values(grupos);
    });

    /**
     * Agrega un producto al carrito (o incrementa su cantidad si ya existe).
     * @param {Object} producto – Objeto producto de la BD
     * @param {Object} tienda   – { id, nombre, slug }
     */
    const agregarItem = (producto, tienda) => {
        const existente = items.value.find((i) => i.id === producto.id);

        if (existente) {
            existente.cantidad += 1;
        } else {
            items.value.push({
                id:            producto.id,
                nombre:        producto.nombre,
                precio:        Number(producto.precio),
                imagen:        producto.imagen ?? null,
                unidad:        producto.unidad ?? 'ud.',
                cantidad:      1,
                tienda_id:     tienda.id,
                tienda_nombre: tienda.nombre,
                tienda_slug:   tienda.slug,
            });
        }
    };

    /**
     * Elimina un producto del carrito por su id.
     * @param {number} productoId
     */
    const eliminarItem = (productoId) => {
        const idx = items.value.findIndex((i) => i.id === productoId);
        if (idx !== -1) items.value.splice(idx, 1);
    };

    /**
     * Modifica la cantidad de un item. Si llega a 0 o menos, se elimina.
     * @param {number} productoId
     * @param {number} delta  (+1 / -1)
     */
    const actualizarCantidad = (productoId, delta) => {
        const item = items.value.find((i) => i.id === productoId);
        if (!item) return;

        item.cantidad += delta;
        if (item.cantidad <= 0) eliminarItem(productoId);
    };

    /** Vacía el carrito por completo */
    const vaciarCarrito = () => {
        items.value = [];
    };

    return {
        items,
        totalItems,
        totalPrecio,
        itemsAgrupadosPorTienda,
        agregarItem,
        eliminarItem,
        actualizarCantidad,
        vaciarCarrito,
    };
}
