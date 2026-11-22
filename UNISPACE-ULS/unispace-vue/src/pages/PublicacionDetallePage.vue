<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getMe } from '@/lib/auth.js'
import { getPublicacion } from '@/lib/publicaciones.js'
import Navbar from '@/components/Navbar.vue'
import PublicacionCard from '@/components/publicaciones/PublicacionCard.vue'

const route = useRoute()
const router = useRouter()

const user = ref({})
const publicacion = ref(null)
const cargando = ref(true)

async function cargar() {
  cargando.value = true
  try {
    const [me, pub] = await Promise.all([
      getMe(),
      getPublicacion(route.params.id),
    ])
    user.value = me
    publicacion.value = pub
  } catch (error) {
    console.error(error)
    router.replace('/dashboard')
    return
  }
  if (!publicacion.value) {
    router.replace('/dashboard')
    return
  }
  cargando.value = false
}

// Solo se pasan al componente los campos que él espera
const cardProps = computed(() => {
  const p = publicacion.value || {}
  return {
    id: p.id,
    tema: p.tema ?? undefined,
    titulo: p.titulo ?? undefined,
    contenido: p.contenido ?? undefined,
    imagen: p.imagen ?? undefined,
    autor: p.autor ?? undefined,
    user_id: p.user_id ?? undefined,
    created_at: p.created_at ?? undefined,
    total_comentarios: p.total_comentarios ?? 0,
    total_likes: p.total_likes ?? 0,
    user_liked: !!p.user_liked,
    ubicacion: p.ubicacion ?? undefined,
    aula: p.aula ?? undefined,
    lugar_id: p.lugar_id ?? undefined,
    publica: p.publica ?? undefined,
  }
})

const esDueno = computed(
  () => Number(user.value?.id) === Number(publicacion.value?.user_id)
)

function onDeleted() {
  router.push('/dashboard')
}

onMounted(cargar)
watch(() => route.params.id, (nuevo, viejo) => {
  if (nuevo && nuevo !== viejo) cargar()
})
</script>

<template>
  <Navbar v-bind="user" />

  <div class="dashboard-container">
    <main class="main-content">
      <div class="detalle-publicacion">
        <div class="volver-container">
          <router-link to="/dashboard" class="btn-volver">← Volver</router-link>
        </div>

        <div v-if="publicacion" class="publicacion-wrapper" :data-wrapper-id="publicacion.id">
          <PublicacionCard
            v-bind="cardProps"
            :is-owner="esDueno"
            @updated="cargar"
            @deleted="onDeleted"
          />
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.detalle-publicacion {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.volver-container {
  margin-bottom: 20px;
}

.btn-volver {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  padding: 10px 16px;
  border-radius: 10px;
  background: #f3f3f3;
  color: #111;
  font-weight: 600;
  transition: .2s ease;
}

.btn-volver:hover {
  background: #ddd;
}
</style>
