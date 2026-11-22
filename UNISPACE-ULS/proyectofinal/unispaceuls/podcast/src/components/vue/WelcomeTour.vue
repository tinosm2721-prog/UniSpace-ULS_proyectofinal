<script setup>
import { computed, onMounted, ref } from "vue";
import { EDIFICIOS, TOUR_PASOS, googleEmbedUrl } from "../../data/campus.js";

const props = defineProps({
  auto: { type: Boolean, default: false },
});

const step = ref(0);
const open = ref(false);
const done = computed(() => step.value >= TOUR_PASOS.length - 1);
const actual = computed(() => TOUR_PASOS[step.value]);
const lugar = computed(() => EDIFICIOS.find((e) => e.id === actual.value.lugarId) || EDIFICIOS[0]);

function next() {
  if (done.value) {
    localStorage.setItem("unispace-tour-done", "1");
    open.value = false;
    window.location.href = "/mapa";
    return;
  }
  step.value += 1;
}

function prev() {
  if (step.value > 0) step.value -= 1;
}

function skip() {
  localStorage.setItem("unispace-tour-done", "1");
  open.value = false;
}

onMounted(() => {
  if (props.auto && !localStorage.getItem("unispace-tour-done")) {
    open.value = true;
  } else if (!props.auto) {
    open.value = true;
  }
});
</script>

<template>
  <button v-if="auto" class="btn btn-outline btn-sm w-full" type="button" @click="open = true">
    Tour de bienvenida
  </button>

  <dialog class="modal" :class="{ 'modal-open': open }">
    <div class="modal-box max-w-3xl">
      <div class="flex justify-between items-start gap-3">
        <div>
          <h3 class="font-black text-2xl">{{ actual.titulo }}</h3>
          <p class="text-sm opacity-70">Paso {{ step + 1 }} de {{ TOUR_PASOS.length }}</p>
        </div>
        <button class="btn btn-ghost btn-sm" type="button" @click="skip">Cerrar</button>
      </div>
      <progress class="progress progress-primary w-full mt-3" :value="step + 1" :max="TOUR_PASOS.length" />
      <p class="py-4">{{ actual.texto }}</p>
      <div class="rounded-2xl overflow-hidden h-64">
        <iframe class="w-full h-full border-0" :src="googleEmbedUrl(lugar.lat, lugar.lng, 18)" title="map" />
      </div>
      <p class="mt-3 text-sm font-semibold">{{ lugar.nombre }} · {{ lugar.lat }}, {{ lugar.lng }}</p>
      <div class="modal-action">
        <button class="btn" type="button" :disabled="step === 0" @click="prev">Anterior</button>
        <button class="btn btn-primary" type="button" @click="next">
          {{ done ? "Ir al mapa" : "Siguiente" }}
        </button>
      </div>
    </div>
    <form method="dialog" class="modal-backdrop" @submit.prevent="skip">
      <button>close</button>
    </form>
  </dialog>
</template>
