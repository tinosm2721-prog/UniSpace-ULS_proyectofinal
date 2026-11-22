<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import TourGuiado from './TourGuiado.vue'

const props = defineProps({
  auto: { type: Boolean, default: false },
})

const router = useRouter()
const open = ref(false)
const clave = ref(0) // reinicia el tour al abrirlo de nuevo

function abrir() {
  clave.value += 1
  open.value = true
}

function cerrar() {
  try { localStorage.setItem('unispace-tour-done', '1') } catch { /* sin storage */ }
  open.value = false
}

function terminar() {
  open.value = false
  router.push('/mapa')
}

defineExpose({ abrir })

onMounted(() => {
  let visto = false
  try { visto = !!localStorage.getItem('unispace-tour-done') } catch { /* sin storage */ }
  if (!props.auto || !visto) open.value = true
})
</script>

<template>
  <Teleport to="body">
    <dialog class="modal" :class="{ 'modal-open': open }">
      <div class="modal-box max-w-3xl">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-black text-2xl">Bienvenida al campus</h2>
          <button class="btn btn-ghost btn-sm" type="button" @click="cerrar">Cerrar</button>
        </div>
        <TourGuiado v-if="open" :key="clave" @finish="terminar" />
      </div>
      <div class="modal-backdrop" @click="cerrar"></div>
    </dialog>
  </Teleport>
</template>
