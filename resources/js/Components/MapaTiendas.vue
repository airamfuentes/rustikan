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
const isSatellite = ref(false);
let map = null;
let clusterGroup = null;
let tileNormal = null;
let tileSatellite = null;

// Tiendas que tienen coordenadas
const tiendasConCoords = () =>
    props.tiendas.filter(t => t.latitud && t.longitud);

const crearIcono = (tienda) => {
    const emoji = tienda.categoria?.icono || '📍';
    return L.divIcon({
        html: `
            <div class="mapa-pin">
                <div class="mapa-pin-bubble">
                    <span class="mapa-pin-emoji">${emoji}</span>
                </div>
                <div class="mapa-pin-tail"></div>
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
    const estrellas = '★'.repeat(Math.round(tienda.valoracion)) + '☆'.repeat(5 - Math.round(tienda.valoracion));
    const img = tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png';
    const nombre = escapeHtml(tienda.nombre);
    const direccion = escapeHtml(tienda.direccion);
    return `
        <div class="mapa-popup">
            <img src="${escapeHtml(img)}" alt="" class="mapa-popup-img" loading="lazy" />
            <div class="mapa-popup-body">
                <h3 class="mapa-popup-title">${nombre}</h3>
                <div class="mapa-popup-stars">${estrellas} <span>${Number(tienda.valoracion).toFixed(1)}</span></div>
                <p class="mapa-popup-addr">${direccion}</p>
                <button class="mapa-popup-btn" onclick="window.__mapaNavegar('${escapeHtml(tienda.slug)}')">
                    Ver tienda →
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

    // Tile layers: normal + satélite
    tileNormal = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        maxZoom: 19,
    });
    tileSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
    });
    tileNormal.addTo(map);

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

const toggleSatellite = () => {
    if (!map) return;
    if (isSatellite.value) {
        map.removeLayer(tileSatellite);
        tileNormal.addTo(map);
    } else {
        map.removeLayer(tileNormal);
        tileSatellite.addTo(map);
    }
    isSatellite.value = !isSatellite.value;
};
</script>

<template>
    <div class="mapa-tiendas-wrapper">
        <!-- Mapa -->
        <div
            ref="mapContainer"
            class="mapa-container"
            :style="{ height }"
        />

        <!-- Botón satélite -->
        <button
            @click="toggleSatellite"
            class="mapa-satellite-btn"
            :title="isSatellite ? 'Vista normal' : 'Vista satélite'"
        >
            <!-- Globe (satélite) -->
            <svg v-if="!isSatellite" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21.721 12.752a9.711 9.711 0 00-.945-5.003 12.754 12.754 0 01-4.339 2.708 18.991 18.991 0 01-.214 4.772 17.165 17.165 0 005.498-2.477zM14.634 15.55a17.324 17.324 0 00.332-4.647c-.952.227-1.945.347-2.966.347-1.021 0-2.014-.12-2.966-.347a17.515 17.515 0 00.332 4.647 17.385 17.385 0 005.268 0zM9.772 17.119a18.963 18.963 0 004.456 0A17.182 17.182 0 0112 21.724a17.18 17.18 0 01-2.228-4.605zM7.777 15.23a18.87 18.87 0 01-.214-4.774 12.753 12.753 0 01-4.34-2.708 9.711 9.711 0 00-.944 5.004 17.165 17.165 0 005.498 2.477zM21.356 14.752a9.765 9.765 0 01-7.478 6.817 18.64 18.64 0 001.988-4.718 18.627 18.627 0 005.49-2.098zM2.644 14.752c1.682.971 3.53 1.688 5.49 2.099a18.64 18.64 0 001.988 4.718 9.765 9.765 0 01-7.478-6.816zM13.878 2.43a9.755 9.755 0 016.116 3.986 11.267 11.267 0 01-3.746 2.504 18.63 18.63 0 00-2.37-6.49zM12 2.276a17.152 17.152 0 012.805 7.121c-.897.23-1.837.353-2.805.353-.968 0-1.908-.122-2.805-.353A17.151 17.151 0 0112 2.276zM10.122 2.43a18.629 18.629 0 00-2.37 6.49 11.266 11.266 0 01-3.746-2.504 9.754 9.754 0 016.116-3.985z" />
            </svg>
            <!-- Map (normal) -->
            <svg v-else class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M8.161 2.58a1.875 1.875 0 011.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0121.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 01-1.676 0l-4.994-2.497a.375.375 0 00-.336 0l-3.868 1.935A1.875 1.875 0 012.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437zM9 6a.75.75 0 01.75.75V15a.75.75 0 01-1.5 0V6.75A.75.75 0 019 6zm6.75 3a.75.75 0 00-1.5 0v8.25a.75.75 0 001.5 0V9z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Indicador si no hay tiendas con coords -->
        <div
            v-if="tiendas.filter(t => t.latitud && t.longitud).length === 0"
            class="mapa-empty"
        >
            <div class="text-4xl mb-2">🗺️</div>
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

.mapa-satellite-btn {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.85);
    background: rgba(20, 20, 20, 0.7);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    backdrop-filter: blur(6px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    transition: background 0.2s, transform 0.15s;
}
.mapa-satellite-btn:hover {
    background: rgba(20, 20, 20, 0.9);
    transform: scale(1.04);
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

.mapa-pin-emoji {
    font-size: 1.35rem;
    line-height: 1;
    display: block;
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
    color: #f59e0b;
    font-size: 0.75rem;
    margin-bottom: 0.25rem;
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
