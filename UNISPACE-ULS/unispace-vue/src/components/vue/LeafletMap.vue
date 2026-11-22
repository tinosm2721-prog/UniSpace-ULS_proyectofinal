<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { CAMPUS_CENTER, TIPOS, iconoDe } from '@/data/lugares.js'

const props = defineProps({
  lugares: { type: Array, default: () => [] },
  selectedId: { type: String, default: '' },
  orden: { type: Array, default: () => [] }, // ids que se numeran (1, 2, 3...)
  linea: { type: Array, default: () => [] }, // ids que se unen con una línea
  ubicando: { type: Boolean, default: false }, // modo para marcar posiciones con un clic
  pin: { type: Object, default: null }, // {lat, lng} marca temporal
  trazo: { type: Array, default: null }, // [[lat,lng],[lat,lng]] ruta dibujada en azul
})
const emit = defineEmits(['select', 'ubicar'])

// solo se dibujan los lugares que ya tienen coordenadas
const conCoords = () => props.lugares.filter((l) => l.lat != null && l.lng != null)

const el = ref(null)
let map = null
let capa = null
let ruta = null
let marcadores = {}
let pinMarker = null
let trazoLinea = null

function crearIcono(lugar, seleccionado) {
  const n = props.orden.indexOf(lugar.id)
  const color = TIPOS[lugar.tipo]?.color || '#1a4fa0'
  const contenido = n >= 0 ? n + 1 : iconoDe(lugar)
  return L.divIcon({
    className: 'uni-marker-wrap',
    html: `<div class="uni-marker${seleccionado ? ' is-selected' : ''}" style="--c:${color}">${contenido}</div>`,
    iconSize: [36, 36],
    iconAnchor: [18, 18],
  })
}

function dibujarMarcadores() {
  capa.clearLayers()
  marcadores = {}
  for (const l of conCoords()) {
    const m = L.marker([l.lat, l.lng], { icon: crearIcono(l, l.id === props.selectedId), title: l.nombre })
    const tip = document.createElement('span')
    tip.textContent = l.nombre
    m.bindTooltip(tip, { direction: 'top', offset: [0, -18] })
    m.on('click', () => emit('select', l.id))
    m.addTo(capa)
    marcadores[l.id] = m
  }
}

function refrescarIconos() {
  for (const l of conCoords()) {
    const m = marcadores[l.id]
    if (!m) continue
    const sel = l.id === props.selectedId
    m.setIcon(crearIcono(l, sel))
    m.setZIndexOffset(sel ? 1000 : 0)
  }
}

function dibujarLinea() {
  if (ruta) { ruta.remove(); ruta = null }
  const pts = props.linea
    .map((id) => conCoords().find((l) => l.id === id))
    .filter(Boolean)
    .map((l) => [l.lat, l.lng])
  if (pts.length > 1) {
    ruta = L.polyline(pts, { color: '#f0a500', weight: 4, dashArray: '8 8' }).addTo(map)
  }
}

function irASeleccionado(animar = true) {
  const l = conCoords().find((x) => x.id === props.selectedId)
  if (!l || !map) return
  const zoom = Math.max(map.getZoom(), 18)
  animar ? map.flyTo([l.lat, l.lng], zoom, { duration: 0.8 }) : map.setView([l.lat, l.lng], zoom)
}

function dibujarTrazo() {
  if (trazoLinea) { trazoLinea.remove(); trazoLinea = null }
  if (props.trazo && props.trazo.length > 1 && map) {
    trazoLinea = L.polyline(props.trazo, { color: '#2563eb', weight: 5, opacity: 0.85 }).addTo(map)
    map.fitBounds(trazoLinea.getBounds(), { padding: [70, 70], maxZoom: 19 })
  }
}

function invalidar() {
  if (map) map.invalidateSize()
}
defineExpose({ invalidar })

onMounted(() => {
  const inicial = conCoords().find((l) => l.id === props.selectedId)
  map = L.map(el.value, { zoomControl: true, scrollWheelZoom: true })
    .setView(inicial ? [inicial.lat, inicial.lng] : [CAMPUS_CENTER.lat, CAMPUS_CENTER.lng], inicial ? 18 : 17)

  const satelite = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19, attribution: 'Imágenes © Esri, Maxar, Earthstar Geographics' },
  )
  const calles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© colaboradores de OpenStreetMap',
  })
  calles.addTo(map)
  L.control.layers({ Calles: calles, Satélite: satelite }, null, { position: 'topright' }).addTo(map)

  map.on('click', (e) => {
    if (props.ubicando) emit('ubicar', { lat: +e.latlng.lat.toFixed(6), lng: +e.latlng.lng.toFixed(6) })
  })

  capa = L.layerGroup().addTo(map)
  dibujarMarcadores()
  dibujarLinea()
  dibujarTrazo()
  setTimeout(invalidar, 250)
})

watch(() => props.lugares, () => { dibujarMarcadores(); dibujarLinea() })
watch(() => props.orden, () => { dibujarMarcadores() }, { deep: true })
watch(() => props.linea, dibujarLinea, { deep: true })
watch(() => props.pin, (p) => {
  if (pinMarker) { pinMarker.remove(); pinMarker = null }
  if (p && map) {
    pinMarker = L.marker([p.lat, p.lng], {
      icon: L.divIcon({ className: 'uni-marker-wrap', html: '<div class="uni-pin">📌</div>', iconSize: [30, 30], iconAnchor: [15, 28] }),
    }).addTo(map)
  }
})
watch(() => props.selectedId, () => { refrescarIconos(); irASeleccionado() })
watch(() => props.trazo, dibujarTrazo, { deep: true })

onBeforeUnmount(() => {
  if (map) { map.remove(); map = null }
})
</script>

<template>
  <div class="w-full h-full" :class="{ ubicando }">
    <div ref="el" class="w-full h-full min-h-64 isolate rounded-box z-0"></div>
  </div>
</template>

<style>
/* Tailwind (preflight) limita el ancho de las imágenes y rompe los tiles de Leaflet */
.leaflet-container img,
.leaflet-container .leaflet-tile { max-width: none !important; max-height: none !important; }
.uni-marker-wrap { background: none; border: 0; }
.uni-pin { font-size: 26px; line-height: 1; filter: drop-shadow(0 2px 3px rgba(0,0,0,.5)); }
.ubicando .leaflet-container, .ubicando .leaflet-interactive { cursor: crosshair; }
.uni-marker {
  width: 36px; height: 36px; border-radius: 9999px;
  display: flex; align-items: center; justify-content: center;
  background: var(--c, #1a4fa0); color: #fff; font-weight: 800; font-size: 16px;
  border: 3px solid #fff; box-shadow: 0 2px 8px rgba(0,0,0,.4);
  transition: transform .2s;
}
.uni-marker.is-selected { transform: scale(1.3); box-shadow: 0 0 0 4px rgba(240,165,0,.8), 0 2px 10px rgba(0,0,0,.5); }
</style>
