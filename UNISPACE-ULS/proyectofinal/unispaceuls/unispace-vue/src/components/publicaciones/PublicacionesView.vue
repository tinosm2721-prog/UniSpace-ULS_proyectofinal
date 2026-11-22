<script setup>
import { ref, onMounted } from 'vue'
import { getPublicaciones } from '@/lib/publicaciones.js'
import CrearPublicacion from './CrearPublicacion.vue'
import PublicacionCard from './PublicacionCard.vue'

const props = defineProps({
  currentUserId: { type: [Number, String], default: null },
})

const data = ref([])
const total = ref(0)
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(true)

async function cargar(page = 1) {
  loading.value = true
  try {
    const res = await getPublicaciones(page)
    data.value = Array.isArray(res?.data) ? res.data : Array.isArray(res) ? res : []
    total.value = res?.total ?? data.value.length
    currentPage.value = res?.current_page ?? page
    lastPage.value = res?.last_page ?? 1
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

function esDueno(pub) {
  return Number(props.currentUserId) === Number(pub.user_id)
}

function onCreated() { cargar(1) }
function onUpdated() { cargar(currentPage.value) }
function onDeleted(id) {
  data.value = data.value.filter((p) => p.id !== id)
}

onMounted(() => cargar(1))
</script>

<template>
  <section class="card bg-base-100 shadow-sm mb-6">
    <div class="card-body">
      <div class="flex flex-wrap justify-between items-center gap-4 mb-2">
        <div>
          <h2 class="text-3xl font-black">📝 Publicaciones</h2>
          <p class="text-base-content/60">Comparte ideas, contenido y novedades</p>
        </div>
        <span class="badge badge-primary badge-lg font-bold">{{ total }} publicaciones</span>
      </div>

      <CrearPublicacion @created="onCreated" />
    </div>
  </section>

  <section class="flex flex-col gap-4">
    <div v-if="loading" class="text-center py-12">
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>
    <div v-else-if="!data.length" class="card bg-base-100 shadow-sm">
      <div class="card-body items-center text-center">
        <h3 class="text-2xl font-bold">No hay publicaciones</h3>
        <p class="text-base-content/60">Sé el primero en publicar algo.</p>
      </div>
    </div>

    <PublicacionCard
      v-for="pub in data"
      :key="pub.id"
      v-bind="pub"
      :is-owner="esDueno(pub)"
      @updated="onUpdated"
      @deleted="onDeleted"
    />

    <div class="flex justify-center items-center gap-3 flex-wrap mt-4">
      <button v-if="currentPage > 1" class="btn btn-primary" @click="cargar(currentPage - 1)">← Anterior</button>
      <span class="badge badge-ghost badge-lg">Página {{ currentPage }} de {{ lastPage }}</span>
      <button v-if="currentPage < lastPage" class="btn btn-primary" @click="cargar(currentPage + 1)">Siguiente →</button>
    </div>
  </section>
</template>