<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api.js'
import AppLayout from '@/components/AppLayout.vue'

const route = useRoute()
const router = useRouter()

const perPage = 6
const publicacionesRaw = ref([])
const loading = ref(true)

// Estado de la URL (?page=1&tema=...)
const page = computed(() => Number(route.query.page) || 1)
const tema = computed(() => String(route.query.tema || ''))

const buscar = ref(tema.value)
watch(tema, (v) => { buscar.value = v })

async function cargar() {
  loading.value = true
  try {
    const res = await api.get('/publicaciones', { params: { per_page: 100 } })
    const body = res.data
    publicacionesRaw.value = Array.isArray(body?.data)
      ? body.data
      : Array.isArray(body)
        ? body
        : []
  } catch (error) {
    console.error(error)
    publicacionesRaw.value = []
  } finally {
    loading.value = false
  }
}

const filtradas = computed(() => {
  if (!tema.value) return publicacionesRaw.value
  const t = tema.value.toLowerCase()
  return publicacionesRaw.value.filter((p) => (p.tema ?? '').toLowerCase().includes(t))
})

const totalPages = computed(() => Math.ceil(filtradas.value.length / perPage))

const publicacionesPaginadas = computed(() =>
  filtradas.value.slice((page.value - 1) * perPage, page.value * perPage)
)

function enviarBusqueda() {
  const q = buscar.value.trim()
  router.push({ path: '/publicaciones/publicaciones', query: q ? { tema: q } : {} })
}

onMounted(cargar)
</script>

<template>
  <AppLayout>
    <section class="card bg-base-100 shadow-sm mb-6">
      <div class="card-body gap-4">
        <div>
          <h1 class="text-3xl font-black text-primary">Publicaciones</h1>
          <p class="text-base-content/60">Explora temas y opiniones</p>
        </div>

        <form class="flex items-center gap-2" @submit.prevent="enviarBusqueda">
          <div class="join flex-1">
            <input
              v-model="buscar"
              type="text"
              name="tema"
              class="input input-bordered join-item w-full"
              placeholder="Buscar tema..."
            />
            <button type="submit" class="btn btn-primary join-item" aria-label="Buscar">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
            </button>
          </div>

          <router-link v-if="tema" to="/publicaciones/publicaciones" class="btn btn-neutral btn-square" aria-label="Limpiar búsqueda">✕</router-link>
        </form>
      </div>
    </section>

    <div class="flex flex-col gap-4">
      <div v-if="loading" class="text-center py-12">
        <span class="loading loading-spinner loading-lg text-primary"></span>
      </div>

      <template v-else-if="publicacionesPaginadas.length">
        <article v-for="pub in publicacionesPaginadas" :key="pub.id" class="card bg-base-100 shadow-sm hover:shadow-md transition">
          <div class="card-body gap-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <span v-if="pub.tema" class="badge badge-primary badge-soft font-bold">#{{ pub.tema }}</span>
              <span v-if="pub.autor" class="text-sm text-base-content/60">{{ pub.autor }}</span>
            </div>

            <router-link :to="`/publicaciones/${pub.id}`" class="card-title text-xl hover:text-primary">
              {{ pub.titulo || 'Sin título' }}
            </router-link>

            <p v-if="pub.contenido" class="line-clamp-3 text-base-content/80">{{ pub.contenido }}</p>
          </div>
        </article>
      </template>

      <div v-else class="card bg-base-100 shadow-sm">
        <div class="card-body items-center text-center">
          <h3 class="text-2xl font-bold">Sin resultados</h3>
          <p class="text-base-content/60">No encontramos publicaciones.</p>
        </div>
      </div>

      <div v-if="totalPages > 1" class="join justify-center mt-4">
        <router-link v-if="page > 1" :to="{ query: { page: page - 1, tema } }" class="join-item btn">←</router-link>

        <router-link
          v-for="i in totalPages"
          :key="i"
          :to="{ query: { page: i, tema } }"
          class="join-item btn"
          :class="page === i ? 'btn-primary' : ''"
        >
          {{ i }}
        </router-link>

        <router-link v-if="page < totalPages" :to="{ query: { page: page + 1, tema } }" class="join-item btn">→</router-link>
      </div>
    </div>
  </AppLayout>
</template>