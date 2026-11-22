<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { EDIFICIOS, googleEmbedUrl, CAMPUS_CENTER } from "../../data/campus.js";

const props = defineProps({
  actividades: { type: Array, default: () => [] },
  initialId: { type: String, default: "" },
});

const selectedId = ref(props.initialId || "ciencias");
const query = ref("");
const selectedAula = ref("");

const selected = computed(
  () => EDIFICIOS.find((e) => e.id === selectedId.value) || EDIFICIOS[0]
);

const filtrados = computed(() => {
  const q = query.value.toLowerCase().trim();
  if (!q) return EDIFICIOS;
  return EDIFICIOS.filter(
    (e) =>
      e.nombre.toLowerCase().includes(q) ||
      e.descripcion.toLowerCase().includes(q) ||
      e.aulas.some((a) => a.nombre.toLowerCase().includes(q))
  );
});

const embed = computed(() =>
  googleEmbedUrl(selected.value.lat, selected.value.lng, 18)
);

const actsHere = computed(() =>
  (props.actividades || []).filter((a) => {
    const blob = `${a.ubicacion || ""} ${a.aula || ""} ${a.lugar_id || ""}`.toLowerCase();
    return blob.includes(selected.value.nombre.toLowerCase()) || blob.includes(selectedId.value) || (selectedAula.value && blob.includes(selectedAula.value.toLowerCase()));
  })
);

watch(selectedId, () => {
  selectedAula.value = "";
});

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  const lugar = params.get("lugar");
  if (lugar && EDIFICIOS.some((e) => e.id === lugar)) selectedId.value = lugar;
});
</script>

<template>
  <div class="grid lg:grid-cols-[1.4fr_1fr] gap-4">
    <div class="card bg-base-100 shadow-xl overflow-hidden min-h-[32rem]">
      <div class="p-4 flex items-center justify-between gap-2 flex-wrap">
        <div>
          <h2 class="text-xl font-black">Mapa del campus ULS</h2>
          <p class="text-sm opacity-70">{{ CAMPUS_CENTER.direccion }}</p>
        </div>
        <span class="badge badge-primary badge-lg">{{ selected.lat }}, {{ selected.lng }}</span>
      </div>
      <iframe
        class="w-full h-[28rem] border-0"
        :src="embed"
        title="map"
        loading="lazy"
      />
      <div class="p-4">
        <h3 class="font-bold">{{ selected.nombre }}</h3>
        <p class="text-sm mt-1">{{ selected.descripcion }}</p>
        <div class="flex flex-wrap gap-2 mt-3">
          <button
            v-for="aula in selected.aulas"
            :key="aula.id"
            class="btn btn-sm"
            :class="selectedAula === aula.nombre ? 'btn-secondary' : 'btn-outline'"
            type="button"
            @click="selectedAula = aula.nombre"
          >
            {{ aula.nombre }}
          </button>
        </div>
        <div v-if="actsHere.length" class="mt-4">
          <p class="font-semibold text-sm">Actividades del foro en este punto</p>
          <ul class="mt-2 space-y-1 text-sm">
            <li v-for="act in actsHere" :key="act.id" class="alert alert-info py-2">
              <span>{{ act.titulo }} · {{ act.ubicacion || act.aula }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="space-y-3">
      <input v-model="query" class="input input-bordered w-full" placeholder="Buscar edificio o aula..." />
      <div class="space-y-2 max-h-[40rem] overflow-y-auto pr-1">
        <button
          v-for="edificio in filtrados"
          :key="edificio.id"
          class="card bg-base-100 shadow-sm w-full text-left p-4 border"
          :class="selectedId === edificio.id ? 'border-primary' : 'border-base-200'"
          type="button"
          @click="selectedId = edificio.id"
        >
          <div class="flex justify-between gap-2">
            <h4 class="font-bold">{{ edificio.nombre }}</h4>
            <span class="badge badge-ghost">{{ edificio.tipo }}</span>
          </div>
          <p class="text-xs opacity-70 mt-1">{{ edificio.lat }}, {{ edificio.lng }}</p>
          <p class="text-sm mt-2">{{ edificio.descripcion }}</p>
          <div class="mt-2 flex flex-wrap gap-1">
            <span v-for="aula in edificio.aulas" :key="aula.id" class="badge badge-outline badge-sm">{{ aula.nombre }}</span>
          </div>
        </button>
      </div>
    </div>
  </div>
</template>
