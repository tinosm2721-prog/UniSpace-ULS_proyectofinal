<script setup>
import { ref, computed, onMounted } from 'vue'
import { getMe } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import { CAMPUS_CENTER, googleEmbedUrl } from '@/data/campus.js'

const DIRECCION = 'Intersección NorOriente Carretera a Los Planes de Renderos Km. No.3. y Autopista a Comalapa. Bo. San Jacinto. San Salvador. El Salvador.'
const { lat, lng } = CAMPUS_CENTER
const verMapa = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`
const comoIr = `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`

const ORIGENES = [
  { id: 'centro', nombre: 'Centro de San Salvador', rutas: ['138', 'A1', '12', '17', '400', '138A', '21 (autopista)', '11B'] },
  { id: 'modelo', nombre: 'El Modelo y Zoológico', rutas: [ '17', '12'] },
  { id: 'sur', nombre: 'Terminal del Sur', rutas: ['138', 'A1', '400', '138A'] },
  { id: 'gobierno', nombre: 'Centro de Gobierno (Alameda Juan Pablo II)', rutas: ['11B'] },
]
const origen = ref('centro')
const actual = computed(() => ORIGENES.find((o) => o.id === origen.value))
const copiado = ref(false)
const user = ref({})

async function copiar() {
  try {
    await navigator.clipboard.writeText(DIRECCION)
    copiado.value = true
    setTimeout(() => (copiado.value = false), 2000)
  } catch (e) {
    console.error(e)
  }
}

onMounted(async () => {
  try {
    user.value = await getMe()
  } catch (e) {
    console.error(e)
  }
})
</script>

<template>
  <div class="navbar-fixed"><Navbar v-bind="user" /></div>
  <aside class="sidebar-area"><Sidebar /></aside>

  <main class="pagina">
    <h1>Cómo llegar</h1>
    <p class="sub">Todo lo que necesitas para llegar a la ULS.</p>

    <section class="card">
      <h2>📍 Dirección</h2>
      <p>{{ DIRECCION }}</p>
      <div class="fila">
        <a :href="comoIr" target="_blank" rel="noopener" class="b on">Cómo llegar desde mi ubicación</a>
        <a :href="verMapa" target="_blank" rel="noopener" class="b">Abrir en Google Maps</a>
        <button type="button" class="b" @click="copiar">{{ copiado ? '¡Copiada!' : 'Copiar dirección' }}</button>
      </div>
      <div class="mapa"><iframe :src="googleEmbedUrl(lat, lng, 16)" title="Mapa de la ULS" loading="lazy" /></div>
      <a href="https://uls.edu.sv/sitioweb/ubicacion-de-la-uls/" target="_blank" rel="noopener" class="oficial">Ver la página oficial de ubicación de la ULS ↗</a>
    </section>

    <section class="card">
      <h2>🚌 Rutas de buses y microbuses</h2>
      <p>¿Desde dónde sales?</p>
      <div class="fila">
        <button v-for="o in ORIGENES" :key="o.id" type="button" class="b" :class="{ on: origen === o.id }" @click="origen = o.id">{{ o.nombre }}</button>
      </div>
      <p>Toma alguna de estas rutas:</p>
      <div class="fila"><span v-for="r in actual.rutas" :key="r" class="ruta">{{ r }}</span></div>
    </section>
  </main>
</template>

<style scoped>
:global(body) { margin: 0; background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); font-family: Inter, system-ui, sans-serif; }
.navbar-fixed { position: fixed; top: 0; width: 100%; z-index: 1000; background: rgba(255,255,255,.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(226,232,240,.7); }
.sidebar-area { position: fixed; top: 90px; left: 18px; width: 220px; z-index: 50; }
.pagina { max-width: 860px; margin: auto; padding: 100px 1rem 3rem; }
h1 { margin: 0 0 .25rem; font-size: 2rem; font-weight: 800; color: #0f172a; }
.sub { margin: 0 0 1.5rem; color: #64748b; }
.card { background: #fff; border-radius: 24px; padding: 1.5rem; margin-bottom: 1.2rem; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
.card h2 { margin: 0 0 .6rem; font-size: 1.2rem; font-weight: 800; color: #0f172a; }
.card p { margin: .3rem 0; color: #334155; }
.fila { display: flex; flex-wrap: wrap; gap: .5rem; margin: 1rem 0; }
.b { border: 1px solid #2563eb; color: #2563eb; background: #fff; padding: .55rem 1rem; border-radius: 12px; font-weight: 600; font-size: .85rem; cursor: pointer; text-decoration: none; }
.b.on { background: linear-gradient(135deg, #2563eb, #7c3aed); border-color: transparent; color: #fff; }
.ruta { background: #eef2ff; color: #2563eb; padding: .45rem .9rem; border-radius: 999px; font-weight: 800; }
.mapa iframe { width: 100%; height: 320px; border: 0; border-radius: 16px; }
.oficial { display: inline-block; margin-top: 1rem; color: #2563eb; font-weight: 600; font-size: .9rem; text-decoration: none; }
@media (max-width: 900px) { .sidebar-area { display: none; } }
</style>
