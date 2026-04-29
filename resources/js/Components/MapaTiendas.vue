<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

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

// Mapa de iconos SVG (paths internos Lucide) por slug de categoría
const CATEGORIA_ICONOS = {
    'frutas-y-verduras':   '<path d="M12 6.528V3a1 1 0 0 1 1-1h0"/><path d="M18.237 21A15 15 0 0 0 22 11a6 6 0 0 0-10-4.472A6 6 0 0 0 2 11a15.1 15.1 0 0 0 3.763 10 3 3 0 0 0 3.648.648 5.5 5.5 0 0 1 5.178 0A3 3 0 0 0 18.237 21"/>',
    'carnes':              '<path d="M16.4 13.7A6.5 6.5 0 1 0 6.28 6.6c-1.1 3.13-.78 3.9-3.18 6.08A3 3 0 0 0 5 18c4 0 8.4-1.8 11.4-4.3"/><path d="m18.5 6 2.19 4.5a6.48 6.48 0 0 1-2.29 7.2C15.4 20.2 11 22 7 22a3 3 0 0 1-2.68-1.66L2.4 16.5"/><circle cx="12.5" cy="8.5" r="2.5"/>',
    'pescados-y-mariscos': '<path d="M6.5 12c.94-3.46 4.94-6 8.5-6 3.56 0 6.06 2.54 7 6-.94 3.47-3.44 6-7 6s-7.56-2.53-8.5-6Z"/><path d="M18 12v.5"/><path d="M16 17.93a9.77 9.77 0 0 1 0-11.86"/><path d="M7 10.67C7 8 5.58 5.97 2.73 5.5c-1 1.5-1 5 .23 6.5-1.24 1.5-1.24 5-.23 6.5C5.58 18.03 7 16 7 13.33"/><path d="M10.46 7.26C10.2 5.88 9.17 4.24 8 3h5.8a2 2 0 0 1 1.98 1.67l.23 1.4"/><path d="m16.01 17.93-.23 1.4A2 2 0 0 1 13.8 21H9.5a5.96 5.96 0 0 0 1.49-3.98"/>',
    'panaderia':           '<path d="M2 22 16 8"/><path d="M3.47 12.53 5 11l1.53 1.53a3.5 3.5 0 0 1 0 4.94L5 19l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/><path d="M7.47 8.53 9 7l1.53 1.53a3.5 3.5 0 0 1 0 4.94L9 15l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/><path d="M11.47 4.53 13 3l1.53 1.53a3.5 3.5 0 0 1 0 4.94L13 11l-1.53-1.53a3.5 3.5 0 0 1 0-4.94Z"/><path d="M20 2h2v2a4 4 0 0 1-4 4h-2V6a4 4 0 0 1 4-4Z"/><path d="M11.47 17.47 13 19l-1.53 1.53a3.5 3.5 0 0 1-4.94 0L5 19l1.53-1.53a3.5 3.5 0 0 1 4.94 0Z"/><path d="M15.47 13.47 17 15l-1.53 1.53a3.5 3.5 0 0 1-4.94 0L9 15l1.53-1.53a3.5 3.5 0 0 1 4.94 0Z"/><path d="M19.47 9.47 21 11l-1.53 1.53a3.5 3.5 0 0 1-4.94 0L13 11l1.53-1.53a3.5 3.5 0 0 1 4.94 0Z"/>',
    'lacteos-y-quesos':    '<path d="M8 2h8"/><path d="M9 2v2.789a4 4 0 0 1-.672 2.219l-.656.984A4 4 0 0 0 7 10.212V20a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-9.789a4 4 0 0 0-.672-2.219l-.656-.984A4 4 0 0 1 15 4.788V2"/><path d="M7 15a6.472 6.472 0 0 1 5 0 6.47 6.47 0 0 0 5 0"/>',
    'vinoteca':            '<path d="M8 22h8"/><path d="M7 10h10"/><path d="M12 15v7"/><path d="M12 15a5 5 0 0 0 5-5c0-2-.5-4-2-8H9c-1.5 4-2 6-2 8a5 5 0 0 0 5 5Z"/>',
    'artesania':           '<path d="m14.622 17.897-10.68-2.913"/><path d="M18.376 2.622a1 1 0 1 1 3.002 3.002L17.36 9.643a.5.5 0 0 0 0 .707l.944.944a2.41 2.41 0 0 1 0 3.408l-.944.944a.5.5 0 0 1-.707 0L8.354 7.348a.5.5 0 0 1 0-.707l.944-.944a2.41 2.41 0 0 1 3.408 0l.944.944a.5.5 0 0 0 .707 0z"/><path d="M9 8c-1.804 2.71-3.97 3.46-6.583 3.948a.507.507 0 0 0-.302.819l7.32 8.883a1 1 0 0 0 1.185.204C12.735 20.405 16 16.792 16 15"/>',
};
const ICONO_CASA = '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>';

const crearIcono = (tienda) => {
    const color = tienda.categoria?.color || '#f97316';
    const iconInner = CATEGORIA_ICONOS[tienda.categoria?.slug] ?? ICONO_CASA;
    return L.divIcon({
        html: `
            <div class="mapa-pin">
                <div class="mapa-pin-bubble" style="border-color:${color}">
                    <svg class="mapa-pin-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${iconInner}</svg>
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
                    Ver tienda &rarr;
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

    // Centro por defecto: Lanzarote
    map = L.map(mapContainer.value, {
        center: [29.0469, -13.5800],
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
}

.mapa-pin-svg {
    width: 20px;
    height: 20px;
    color: #f97316;
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
</style>
