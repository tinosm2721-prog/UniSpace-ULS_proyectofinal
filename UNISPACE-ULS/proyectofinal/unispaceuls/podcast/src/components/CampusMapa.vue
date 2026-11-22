<template>
  <div class="w-full">
    <div ref="mapContainer" class="w-full h-[500px] rounded-2xl shadow-lg"></div>

    <div v-if="lugarSeleccionado" class="mt-4 p-4 bg-base-100 rounded-xl shadow border">
      <h3 class="font-bold text-lg">{{ lugarSeleccionado.nombre }}</h3>
      <p class="text-sm opacity-70 mb-2">{{ lugarSeleccionado.descripcion }}</p>

      <div v-if="lugarSeleccionado.aulas?.length" class="mt-2">
        <p class="font-semibold text-sm mb-1">Aulas / espacios:</p>
        <div class="flex flex-wrap gap-2">
          <span
            v-for="aula in lugarSeleccionado.aulas"
            :key="aula.id"
            class="badge badge-outline"
          >
            {{ aula.nombre }} (piso {{ aula.piso }})
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { CAMPUS_CENTER, EDIFICIOS } from '@/data/campus.js';

const mapContainer = ref(null);
const lugarSeleccionado = ref(null);

const iconosPorTipo = {
  acceso: '🚪',
  administracion: '🏛️',
  facultad: '📚',
  servicio: '🛠️',
  evento: '🎭',
  recreo: '⚽',
};

onMounted(async () => {
  const L = await import('leaflet');
  await import('leaflet/dist/leaflet.css');

  const map = L.map(mapContainer.value).setView(
    [CAMPUS_CENTER.lat, CAMPUS_CENTER.lng],
    18
  );

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 20,
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map);

  EDIFICIOS.forEach((edificio) => {
    const icon = L.divIcon({
      html: `<div style="font-size:26px;">${iconosPorTipo[edificio.tipo] || '📍'}</div>`,
      className: '',
      iconSize: [30, 30],
    });

    const marker = L.marker([edificio.lat, edificio.lng], { icon }).addTo(map);
    marker.bindPopup(`<strong>${edificio.nombre}</strong><br>${edificio.descripcion}`);
    marker.on('click', () => {
      lugarSeleccionado.value = edificio;
    });
  });
});

// Se expone para que el tour (fase siguiente) pueda centrar el mapa en un edificio
defineExpose({ mapContainer });
</script>
