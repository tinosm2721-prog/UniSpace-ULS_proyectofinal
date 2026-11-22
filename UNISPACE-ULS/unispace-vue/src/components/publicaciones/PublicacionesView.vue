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
  <section class="view-card">
    <div class="view-header">
      <div>
        <h2>📝 Publicaciones</h2>
        <p>Comparte ideas, contenido y novedades</p>
      </div>
      <span class="total-badge">{{ total }} publicaciones</span>
    </div>

    <CrearPublicacion @created="onCreated" />
  </section>

  <section class="feed-section">
    <div class="feed">
      <div v-if="loading" class="empty-feed"><h3>Cargando...</h3></div>
      <div v-else-if="!data.length" class="empty-feed">
        <h3>No hay publicaciones</h3>
        <p>Sé el primero en publicar algo.</p>
      </div>
      <div v-for="pub in data" :key="pub.id" class="item-wrapper" :data-wrapper-id="pub.id">
        <PublicacionCard
          v-bind="pub"
          :is-owner="esDueno(pub)"
          @updated="onUpdated"
          @deleted="onDeleted"
        />
      </div>
    </div>

    <div class="paginacion">
      <button v-if="currentPage > 1" class="btn-pag" @click="cargar(currentPage - 1)">← Anterior</button>
      <div class="info-pag">
        <span>Página</span>
        <strong>{{ currentPage }}</strong>
        <span>de</span>
        <strong>{{ lastPage }}</strong>
      </div>
      <button v-if="currentPage < lastPage" class="btn-pag" @click="cargar(currentPage + 1)">Siguiente →</button>
    </div>
  </section>
</template>

<style scoped>
.view-card,
.feed-section{
  background: rgba(255,255,255,.75);
  backdrop-filter:blur(18px);
  border: 1px solid rgba(255,255,255,.5);
  border-radius:30px;
  padding:2rem;
  margin-bottom:1.5rem;
  box-shadow: 0 12px 35px rgba(15,23,42,.06);
}

.view-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  gap:1rem;
  margin-bottom:2rem;
  flex-wrap:wrap;
}

.view-header h2{
  margin:0;
  font-size:2rem;
  color:#0f172a;
  font-weight:900;
}

.view-header p{
  margin-top:.5rem;
  color:#64748b;
}

.total-badge{
  padding:.7rem 1rem;
  border-radius:999px;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  color:white;
  font-weight:700;
  font-size:.9rem;
  box-shadow: 0 10px 25px rgba(37,99,235,.2);
}

.feed{
  display:flex;
  flex-direction:column;
  gap:1.2rem;
}

.item-wrapper{
  position: relative;
  animation: fadeIn .3s ease;
}

.empty-feed{
  text-align:center;
  padding:4rem 1rem;
}

.empty-feed h3{
  margin-bottom:.5rem;
  font-size:1.5rem;
  color:#0f172a;
}

.empty-feed p{
  color:#64748b;
}

.paginacion{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:1rem;
  flex-wrap:wrap;
  margin-top:2rem;
}

.btn-pag{
  text-decoration:none;
  padding:.9rem 1.4rem;
  border-radius:16px;
  font-weight:700;
  color:white;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  transition:.25s ease;
  box-shadow: 0 10px 20px rgba(37,99,235,.2);
  border: none;
  cursor: pointer;
}

.btn-pag:hover{
  transform: translateY(-3px);
  box-shadow: 0 18px 30px rgba(37,99,235,.28);
}

.info-pag{
  display:flex;
  align-items:center;
  gap:.5rem;
  padding:.9rem 1.2rem;
  border-radius:16px;
  background: rgba(255,255,255,.75);
  border: 1px solid #e2e8f0;
  color:#64748b;
  font-weight:600;
}

.info-pag strong{
  color:#0f172a;
}

@keyframes fadeIn{
  from{
    opacity:0;
    transform: translateY(10px);
  }
  to{
    opacity:1;
    transform: translateY(0);
  }
}

@media (max-width:768px){
  .view-card,
  .feed-section{
    padding:1.3rem;
  }
  .view-header{
    align-items:flex-start;
  }
  .view-header h2{
    font-size:1.6rem;
  }
}
</style>
