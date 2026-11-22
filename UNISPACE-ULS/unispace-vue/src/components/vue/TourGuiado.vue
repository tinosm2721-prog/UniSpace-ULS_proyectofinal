<script setup>
import { computed, ref } from 'vue'
import { EDIFICIOS, TOUR_PASOS, iconoDe } from '@/data/campus.js'
import LeafletMap from './LeafletMap.vue'

defineProps({
  alturaMapa: { type: String, default: 'h-72' },
})
const emit = defineEmits(['finish'])

const step = ref(0)
const actual = computed(() => TOUR_PASOS[step.value])
const esUltimo = computed(() => step.value >= TOUR_PASOS.length - 1)

const ordenIds = TOUR_PASOS.map((p) => p.lugarId)
const lugares = ordenIds.map((id) => EDIFICIOS.find((e) => e.id === id)).filter(Boolean)
const lineaHasta = computed(() => ordenIds.slice(0, step.value + 1))
const lugar = computed(() => lugares.find((l) => l.id === actual.value.lugarId))

function siguiente() {
  if (esUltimo.value) {
    try { localStorage.setItem('unispace-tour-done', '1') } catch { /* sin storage */ }
    emit('finish')
    return
  }
  step.value += 1
}
function anterior() {
  if (step.value > 0) step.value -= 1
}
function irAlLugar(id) {
  const i = TOUR_PASOS.findIndex((p) => p.lugarId === id)
  if (i >= 0) step.value = i
}
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="rounded-box overflow-hidden border border-base-300" :class="alturaMapa">
      <LeafletMap
        :lugares="lugares"
        :selected-id="lugar?.id || ''"
        :orden="ordenIds"
        :linea="lineaHasta"
        @select="irAlLugar"
      />
    </div>

    <progress class="progress progress-primary w-full" :value="step + 1" :max="TOUR_PASOS.length"></progress>

    <div class="flex flex-wrap gap-1">
      <button
        v-for="(p, i) in TOUR_PASOS"
        :key="p.id"
        type="button"
        class="btn btn-circle btn-xs"
        :class="i === step ? 'btn-primary' : i < step ? 'btn-outline btn-primary' : 'btn-ghost border border-base-300'"
        :aria-label="`Paso ${i + 1}: ${p.titulo}`"
        @click="step = i"
      >
        {{ i + 1 }}
      </button>
    </div>

    <div>
      <p class="text-xs font-bold opacity-60">PASO {{ step + 1 }} DE {{ TOUR_PASOS.length }}</p>
      <h3 class="text-xl font-black mt-1">{{ lugar ? iconoDe(lugar) : '' }} {{ actual.titulo }}</h3>
      <p class="mt-2">{{ actual.texto }}</p>
      <p v-if="lugar" class="text-sm opacity-60 mt-1">{{ lugar.nombre }}</p>
    </div>

    <div class="flex justify-between gap-2">
      <button class="btn" type="button" :disabled="step === 0" @click="anterior">Anterior</button>
      <button class="btn btn-primary" type="button" @click="siguiente">
        {{ esUltimo ? 'Ir al mapa' : 'Siguiente' }}
      </button>
    </div>
  </div>
</template>
