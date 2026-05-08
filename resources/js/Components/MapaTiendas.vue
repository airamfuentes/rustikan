<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import { useI18n } from '@/Composables/useI18n';
import { useCategorias } from '@/Composables/useCategorias';

const { t } = useI18n();
const { nombre: categoriaNombre } = useCategorias();

const props = defineProps({
    tiendas:    { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
    height:     { type: String, default: '500px' },
});

const emit = defineEmits(['tienda-click']);

const mapContainer = ref(null);
const activePopupId = ref(null);
let map = null;
let clusterGroup = null;

// Tiendas que tienen coordenadas
const tiendasConCoords = () =>
    props.tiendas.filter(t => t.latitud && t.longitud);

// Mapa de imágenes (en /public/images) por slug de categoría
const CATEGORIA_IMG = {
    'frutas-y-verduras':   '/images/furtas_verduras.png',
    'carnes':              '/images/carnes.png',
    'pescados-y-mariscos': '/images/pescados_mariscos.png',
    'panaderia':           '/images/panaderia.png',
    'lacteos-y-quesos':    '/images/lacteos_quesos.png',
    'vinoteca':            '/images/vinoteca.png',
    'artesania':           '/images/artesania.png',
};
const IMG_FALLBACK = '/images/logo.png';

const crearIcono = (tienda) => {
    const color = tienda.categoria?.color || '#f97316';
    const img = CATEGORIA_IMG[tienda.categoria?.slug] ?? IMG_FALLBACK;
    const alt = escapeHtml(categoriaNombre(tienda.categoria) || t('cat_page.stores'));
    return L.divIcon({
        html: `
            <div class="mapa-pin">
                <div class="mapa-pin-bubble" style="border-color:${color}">
                    <img src="${img}" alt="${alt}" class="mapa-pin-img" loading="lazy" />
                </div>
                <div class="mapa-pin-tail" style="border-top-color:${color}"></div>
            </div>
        `,
        className: 'mapa-pin-container',
        iconSize:    [44, 52],
        iconAnchor:  [22, 52],
        popupAnchor: [0, -56],
    });
};

const escapeHtml = (str) =>
    (str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

const crearPopup = (tienda) => {
    const rating = Number(tienda.valoracion).toFixed(1);
    const starsHtml = Array.from({ length: 5 }, (_, i) =>
        `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="${i < Math.round(tienda.valoracion) ? '#f59e0b' : 'none'}" stroke="#f59e0b" stroke-width="2" class="mapa-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`
    ).join('');
    const img = tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png';
    const nombre = escapeHtml(tienda.nombre);
    const direccion = escapeHtml(tienda.direccion);
    return `
        <div class="mapa-popup">
            <img src="${escapeHtml(img)}" alt="" class="mapa-popup-img" loading="lazy" />
            <div class="mapa-popup-body">
                <h3 class="mapa-popup-title">${nombre}</h3>
                <div class="mapa-popup-stars">${starsHtml} <span>${rating}</span></div>
                <p class="mapa-popup-addr">${direccion}</p>
                <button class="mapa-popup-btn" onclick="window.__mapaNavegar('${escapeHtml(tienda.slug)}')">
                    ${escapeHtml(t('cat_page.view_store'))} &rarr;
                </button>
            </div>
        </div>
    `;
};

const cargarMarcadores = () => {
    if (!map || !clusterGroup) return;
    clusterGroup.clearLayers();

    const tiendas = tiendasConCoords();
    if (!tiendas.length) return;

    tiendas.forEach(tienda => {
        const marker = L.marker([tienda.latitud, tienda.longitud], {
            icon: crearIcono(tienda),
        });
        marker.bindPopup(crearPopup(tienda), {
            maxWidth: 280,
            minWidth: 240,
            className: 'mapa-popup-wrapper',
        });
        marker.on('click', () => {
            activePopupId.value = tienda.id;
        });
        clusterGroup.addLayer(marker);
    });

    // Ajustar vista al bounds
    const bounds = clusterGroup.getBounds();
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
};

const initMap = () => {
    if (!mapContainer.value) return;

    // Límites geográficos de la isla de Lanzarote (con pequeño margen)
    const lanzaroteBounds = L.latLngBounds(
        [28.72, -14.12],  // SW — un poco al suroeste de Punta de Papagayo
        [29.38, -13.28],  // NE — un poco al noreste de Punta Fariones
    );

    // Centro por defecto: Lanzarote centrado visualmente
    map = L.map(mapContainer.value, {
        center: [29.05, -13.77],
        zoom: 11,
        minZoom: 10,                      // no se puede alejar más de la isla
        maxBounds: lanzaroteBounds,       // no se puede panear fuera de Lanzarote
        maxBoundsViscosity: 1.0,          // rebote duro al llegar al borde
        zoomControl: false,
        scrollWheelZoom: false,
        attributionControl: false,
    });

    // Control de zoom en la esquina derecha
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Tile: satélite fijo
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
    }).addTo(map);

    // Habilitar scroll zoom con Ctrl
    map.on('focus', () => { map.scrollWheelZoom.enable(); });
    map.on('blur', () => { map.scrollWheelZoom.disable(); });

    clusterGroup = L.markerClusterGroup({
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        iconCreateFunction: (cluster) => {
            const count = cluster.getChildCount();
            return L.divIcon({
                html: `<div class="mapa-cluster"><span>${count}</span></div>`,
                className: 'mapa-cluster-container',
                iconSize: [44, 44],
            });
        },
    });
    map.addLayer(clusterGroup);

    // Navegación global desde popups
    window.__mapaNavegar = (slug) => {
        router.visit(`/tienda/${slug}`);
    };

    cargarMarcadores();
};

watch(() => props.tiendas, () => {
    nextTick(() => cargarMarcadores());
}, { deep: true });

onMounted(() => {
    nextTick(() => initMap());
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
    delete window.__mapaNavegar;
});


</script>

<template>
    <div class="mapa-tiendas-wrapper">
        <!-- Mapa -->
        <div
            ref="mapContainer"
            class="mapa-container"
            :style="{ height }"
        />

        <!-- Indicador si no hay tiendas con coords -->
        <div
            v-if="tiendas.filter(t => t.latitud && t.longitud).length === 0"
            class="mapa-empty"
        >
            <svg class="mb-2 h-10 w-10 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
            </svg>
            <p class="text-gray-500 text-sm">Las ubicaciones de las tiendas se están configurando</p>
        </div>
    </div>
</template>

<style scoped>
.mapa-tiendas-wrapper {
    position: relative;
    border-radius: 1.5rem;
    overflow: hidden;
    background: #f3f4f6;
}

.mapa-container {
    width: 100%;
    z-index: 1;
}

.mapa-empty {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgb(243 244 246 / 0.85);
    z-index: 500;
    pointer-events: none;
}


</style>

<style>
/* ── Contenedor del pin (sin fondo de Leaflet) ─────────────────────────────── */
.mapa-pin-container {
    background: transparent !important;
    border: none !important;
}

/* ── Pin completo (burbuja + cola) ──────────────────────────────────────────── */
.mapa-pin {
    display: flex;
    flex-direction: column;
    align-items: center;
    filter: drop-shadow(0 4px 10px rgb(0 0 0 / 0.22));
    transition: transform 0.15s ease;
}

.mapa-pin:hover {
    transform: translateY(-3px) scale(1.08);
}

/* Burbuja circular blanca */
.mapa-pin-bubble {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    border: 2.5px solid #f97316;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.mapa-pin-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    pointer-events: none;
    user-select: none;
}

/* Cola triangular del pin */
.mapa-pin-tail {
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 8px solid #f97316;
    margin-top: -1px;
}

/* ── Cluster ────────────────────────────────────────────────────────────────── */
.mapa-cluster-container {
    background: transparent !important;
    border: none !important;
}

.mapa-cluster {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f97316, #ea580c);
    border-radius: 50%;
    box-shadow: 0 2px 10px rgb(249 115 22 / 0.4);
    animation: cluster-pulse 2s ease-in-out infinite;
}

.mapa-cluster span {
    color: white;
    font-size: 0.875rem;
    font-weight: 700;
}

@keyframes cluster-pulse {
    0%, 100% { box-shadow: 0 2px 10px rgb(249 115 22 / 0.4); }
    50%       { box-shadow: 0 2px 22px rgb(249 115 22 / 0.65); }
}

/* ── Popup ──────────────────────────────────────────────────────────────────── */
.mapa-popup-wrapper .leaflet-popup-content-wrapper {
    border-radius: 1rem;
    padding: 0;
    overflow: hidden;
    box-shadow: 0 10px 25px rgb(0 0 0 / 0.15);
}

.mapa-popup-wrapper .leaflet-popup-content {
    margin: 0;
    width: 260px !important;
}

.mapa-popup-wrapper .leaflet-popup-tip {
    box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
}

.mapa-popup {
    font-family: 'Figtree', system-ui, sans-serif;
}

.mapa-popup-img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.mapa-popup-body {
    padding: 0.75rem 1rem 1rem;
}

.mapa-popup-title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.25rem;
    line-height: 1.3;
}

.mapa-popup-stars {
    display: flex;
    align-items: center;
    gap: 0.125rem;
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
}

.mapa-star {
    display: inline-block;
    vertical-align: middle;
}

.mapa-popup-stars span {
    color: #6b7280;
    font-weight: 600;
    margin-left: 0.25rem;
}

.mapa-popup-addr {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0 0 0.75rem;
    line-height: 1.4;
}

.mapa-popup-btn {
    display: block;
    width: 100%;
    padding: 0.5rem;
    border: none;
    border-radius: 0.5rem;
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
    text-align: center;
}

.mapa-popup-btn:hover {
    opacity: 0.9;
}

/* ── Dark mode popups ───────────────────────────────────────────────────────── */
html.dark .mapa-popup-wrapper .leaflet-popup-content-wrapper {
    background: #1f2937;
    border: 1px solid #374151;
    box-shadow: 0 10px 25px rgb(0 0 0 / 0.4);
}

html.dark .mapa-popup-wrapper .leaflet-popup-tip {
    background: #1f2937;
}

html.dark .mapa-pin-bubble {
    background: #1f2937;
}

html.dark .mapa-popup-body {
    background: #1f2937;
}

html.dark .mapa-popup-title {
    color: #f9fafb;
}

html.dark .mapa-popup-stars span {
    color: #9ca3af;
}

html.dark .mapa-popup-addr {
    color: #9ca3af;
}
</style>
