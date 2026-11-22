<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/lib/api.js'
import Sidebar from '@/components/Sidebar.vue'

const route = useRoute()
const router = useRouter()

const perPage = 6
const publicacionesRaw = ref([])
const loading = ref(true)

// Estado de la URL (?page=1&tema=...), igual que en Astro
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
  <section class="sidebar-area">
    <Sidebar />
  </section>

  <section class="publicaciones">
    <div class="header">
      <div>
        <h1>Publicaciones</h1>
        <p>Explora temas y opiniones</p>
      </div>

      <form class="buscador" @submit.prevent="enviarBusqueda">
        <div class="input-box">
          <input v-model="buscar" type="text" name="tema" placeholder="Buscar tema..." />
          <button type="submit" aria-label="Buscar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7" /><line x1="21" y1="21" x2="16.65" y2="16.65" /></svg>
          </button>
        </div>

        <router-link v-if="tema" to="/publicaciones/publicaciones" class="limpiar">✕</router-link>
      </form>
    </div>

    <div class="card lista">
      <template v-if="loading">
        <div class="empty">
          <h3>Cargando...</h3>
        </div>
      </template>

      <template v-else-if="publicacionesPaginadas.length">
        <article v-for="pub in publicacionesPaginadas" :key="pub.id" class="card">
          <div class="card-top">
            <span v-if="pub.tema" class="tema">#{{ pub.tema }}</span>
            <span v-if="pub.autor" class="autor">{{ pub.autor }}</span>
          </div>

          <router-link :to="`/publicaciones/${pub.id}`" class="titulo">
            {{ pub.titulo || 'Sin título' }}
          </router-link>

          <p v-if="pub.contenido" class="contenido">{{ pub.contenido }}</p>
        </article>
      </template>

      <div v-else class="empty">
        <h3>Sin resultados</h3>
        <p>No encontramos publicaciones.</p>
      </div>

      <div v-if="totalPages > 1" class="paginador">
        <router-link v-if="page > 1" :to="{ query: { page: page - 1, tema } }">←</router-link>

        <router-link
          v-for="i in totalPages"
          :key="i"
          :to="{ query: { page: i, tema } }"
          :class="page === i ? 'active' : ''"
        >
          {{ i }}
        </router-link>

        <router-link v-if="page < totalPages" :to="{ query: { page: page + 1, tema } }">→</router-link>
      </div>
    </div>
  </section>
</template>

<style scoped>
:global(:root) {
  --primary: #2563eb;
  --secondary: #7c3aed;
  --dark: #0f172a;
  --muted: #64748b;
  --border: #e2e8f0;
  --white: #ffffff;
  --text: #334155;
  --shadow: 0 10px 30px rgba(15,23,42,.08);
}

.sidebar-area {
  position: fixed;
  width: 20%;
  margin: auto;
  top: 90px;
  height: fit-content;
  background: rgba(255,255,255,.7);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(255,255,255,.5);
  border-radius: 28px;
  padding: 1.2rem;
  box-shadow: 0 10px 30px rgba(15,23,42,.06);
}

.publicaciones {
  max-width: 850px;
  margin: auto;
  padding: 2.5rem 1.2rem;
}

/* HEADER */
.header {
  position: fixed;
  top: 1rem;
  left: 50%;
  transform: translateX(-50%);
  width: min(850px, calc(100% - 2.4rem));
  box-sizing: border-box;
  display: flex;
  z-index: 100;
  justify-content: space-between;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  padding: 1.1rem 1.8rem;
  background: rgba(255,255,255,.85);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255,255,255,.6);
  border-radius: 28px;
  box-shadow: var(--shadow);
}

.header h1 {
  margin: 0;
  font-size: 2.4rem;
  font-weight: 800;
  letter-spacing: -1px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.header p {
  margin: .2rem 0 0;
  color: var(--muted);
  font-size: 1.05rem;
  line-height: 1.5;
}

/* SEARCH */
.buscador {
  display: flex;
  align-items: center;
  gap: .8rem;
}

.input-box {
  display: flex;
  align-items: stretch;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 6px 20px rgba(15,23,42,.08);
  transition: .25s ease;
}

.input-box:focus-within {
  transform: translateY(-2px);
  border-color: rgba(37,99,235,.35);
  box-shadow: 0 12px 30px rgba(37,99,235,.12);
}

.input-box input {
  border: none;
  outline: none;
  padding: 1rem 1.1rem;
  width: 250px;
  font-size: .95rem;
  background: transparent;
  color: var(--dark);
}

.input-box input::placeholder {
  color: #94a3b8;
}

.input-box button {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  border: none;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  padding: 0 1.2rem;
  cursor: pointer;
  font-weight: 700;
  transition: .25s ease;
}

.input-box button:hover {
  filter: brightness(1.05);
}

.limpiar {
  width: 46px;
  height: 46px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  background: var(--dark);
  color: white;
  font-weight: 700;
  transition: .25s ease;
  box-shadow: 0 6px 18px rgba(15,23,42,.15);
}

.limpiar:hover {
  transform: translateY(-3px) scale(1.04);
  background: #020617;
}

/* LISTA (contenedor) */
.lista {
  margin-top: 130px;
}

.lista::before {
  display: none;
}

.lista:hover {
  transform: none;
  box-shadow: 0 10px 35px rgba(15,23,42,.06);
}

/* CARDS */
.card {
  position: relative;
  overflow: hidden;
  background: rgba(255,255,255,.85);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255,255,255,.6);
  border-radius: 28px;
  padding: 1.5rem;
  margin-bottom: 1.3rem;
  transition: .28s ease;
  box-shadow: 0 10px 35px rgba(15,23,42,.06);
}

.card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
}

.card:hover {
  transform: translateY(-5px) scale(1.01);
  box-shadow: 0 20px 45px rgba(15,23,42,.12);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.tema {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  background: linear-gradient(135deg, rgba(37,99,235,.14), rgba(124,58,237,.14));
  color: var(--primary);
  padding: .45rem .95rem;
  border-radius: 999px;
  font-size: .82rem;
  font-weight: 700;
  border: 1px solid rgba(37,99,235,.12);
}

.autor {
  color: var(--muted);
  font-size: .88rem;
  display: flex;
  align-items: center;
  gap: .35rem;
}

.titulo {
  display: block;
  font-size: 1.45rem;
  font-weight: 800;
  color: var(--dark);
  text-decoration: none;
  margin-bottom: .75rem;
  line-height: 1.3;
  transition: .2s ease;
}

.titulo:hover {
  color: var(--primary);
}

.contenido {
  color: var(--text);
  line-height: 1.8;
  font-size: .97rem;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
}

/* PAGINADOR */
.paginador {
  display: flex;
  justify-content: center;
  gap: .7rem;
  margin-top: 2rem;
  flex-wrap: wrap;
}

.paginador a {
  min-width: 46px;
  height: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 1rem;
  border-radius: 16px;
  text-decoration: none;
  background: rgba(255,255,255,.85);
  color: var(--dark);
  border: 1px solid rgba(255,255,255,.5);
  box-shadow: 0 6px 20px rgba(15,23,42,.06);
  font-weight: 700;
  transition: .25s ease;
}

.paginador a:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(15,23,42,.12);
}

.paginador .active {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
}

/* EMPTY */
.empty {
  text-align: center;
  padding: 4rem 2rem;
  border-radius: 28px;
  background: rgba(255,255,255,.8);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,.5);
  box-shadow: var(--shadow);
}

.empty h3 {
  margin-bottom: .7rem;
  color: var(--dark);
  font-size: 1.6rem;
}

.empty p {
  color: var(--muted);
  max-width: 500px;
  margin: auto;
  line-height: 1.7;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .header {
    padding: 1rem 1.2rem;
  }

  .header h1 {
    font-size: 1.8rem;
  }

  .input-box input {
    width: 100%;
  }

  .buscador {
    width: 100%;
  }

  .input-box {
    flex: 1;
  }

  .lista {
    margin-top: 190px;
  }

  .card {
    padding: 1.2rem;
  }

  .titulo {
    font-size: 1.2rem;
  }
}
</style>
