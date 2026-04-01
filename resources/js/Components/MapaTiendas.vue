<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';

const props = defineProps({
    tiendas: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
    height: { type: String, default: '500px' },
});

const emit = defineEmits(['tienda-click']);

const mapContainer = ref(null);
const activePopupId = ref(null);
const filtroCategoria = ref(null);
let map = null;
let clusterGroup = null;

// Tiendas que tienen coordenadas
const tiendasConCoords = () =>
    props.tiendas.filter(t => t.latitud && t.longitud && (filtroCategoria.value === null || t.categoria_id === filtroCategoria.value));

const crearIcono = (tienda) => {
    const emoji = tienda.categoria?.icono || '📍';
    return L.divIcon({
        html: `<div class="mapa-marker"><span class="mapa-marker-emoji">${emoji}</span></div>`,
        className: 'mapa-marker-container',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -42],
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

    // Centro por defecto: Lanzarote
    map = L.map(mapContainer.value, {
        center: [29.0469, -13.6328],
        zoom: 11,
        zoomControl: false,
        scrollWheelZoom: false,
    });

    // Control de zoom en la esquina derecha
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Tile layer de OpenStreetMap con estilo limpio
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18,
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

watch(filtroCategoria, () => {
    cargarMarcadores();
});

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
        <!-- Filtros por categoría -->
        <div v-if="categorias.length > 0" class="mapa-filtros">
            <button
                :class="['mapa-filtro-btn', filtroCategoria === null && 'activo']"
                @click="filtroCategoria = null"
            >
                📍 Todas
            </button>
            <button
                v-for="cat in categorias"
                :key="cat.id"
                :class="['mapa-filtro-btn', filtroCategoria === cat.id && 'activo']"
                @click="filtroCategoria = filtroCategoria === cat.id ? null : cat.id"
            >
                {{ cat.icono }} {{ cat.nombre }}
            </button>
        </div>

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

.mapa-filtros {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 1000;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    max-width: calc(100% - 5rem);
}

.mapa-filtro-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    background: white;
    color: #374151;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.mapa-filtro-btn:hover {
    background: #f9fafb;
    border-color: #f97316;
}

.mapa-filtro-btn.activo {
    background: #f97316;
    color: white;
    border-color: #f97316;
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
/* Estilos globales para marcadores y popups de Leaflet */
.mapa-marker-container {
    background: transparent !important;
    border: none !important;
}

.mapa-marker {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50% 50% 50% 0;
    transform: rotate(-45deg);
    box-shadow: 0 2px 8px rgb(0 0 0 / 0.2);
    border: 2px solid #f97316;
    transition: transform 0.2s;
}

.mapa-marker:hover {
    transform: rotate(-45deg) scale(1.15);
}

.mapa-marker-emoji {
    transform: rotate(45deg);
    font-size: 1.1rem;
    line-height: 1;
}

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
    50% { box-shadow: 0 2px 20px rgb(249 115 22 / 0.6); }
}

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
