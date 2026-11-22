<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { LUGARES_BASE, TIPOS, iconoDe, CAMPUS_CENTER, conCoordenadas, tieneCoordenadas } from '@/data/lugares.js'
import { COORDENADAS } from '@/data/coordenadas.js'
import { INDICACIONES } from '@/data/indicaciones.js'
import { cercanos, distanciaM, minutosAPie, nombreRumbo, rumboGrados } from '@/data/rutas.js'
import { fotosDe } from '@/data/fotos.js'
import LeafletMap from './LeafletMap.vue'
import { useRoute } from 'vue-router'

const props = defineProps({
  actividades: { type: Array, default: () => [] },
  initialId: { type: String, default: '' },
})

const selectedId = ref(props.initialId || 'ciencias')
const selectedAula = ref('')
const query = ref('')
const tipoActivo = ref('todos')

// --- Herramienta para ubicar lugares (solo aparece con "npm run dev") ---
const esDev = import.meta.env.DEV
const CLAVE_PRUEBAS = 'unispace-coords-dev'
const ubicando = ref(false)
const copiado = ref(false)
const pruebas = ref({})
try { pruebas.value = JSON.parse(localStorage.getItem(CLAVE_PRUEBAS) || '{}') } catch { /* sin storage */ }

function guardarPruebas() {
  try { localStorage.setItem(CLAVE_PRUEBAS, JSON.stringify(pruebas.value)) } catch { /* sin storage */ }
}

const LUGARES = computed(() => LUGARES_BASE.map((l) => conCoordenadas(l, pruebas.value)))
const sinUbicar = computed(() => LUGARES.value.filter((l) => !tieneCoordenadas(l)).length)

function ubicar({ lat, lng }) {
  pruebas.value = { ...pruebas.value, [selectedId.value]: [lat, lng] }
  guardarPruebas()
}

const textoCoords = computed(() => {
  const todo = { ...COORDENADAS, ...pruebas.value }
  const filas = Object.entries(todo).map(([id, [a, b]]) => `  ${JSON.stringify(id)}: [${a}, ${b}],`).join('\n')
  return `// Posiciones de los lugares en el mapa: "id": [latitud, longitud]\nexport const COORDENADAS = {\n${filas}\n};\n`
})

async function copiarCoords() {
  try {
    await navigator.clipboard.writeText(textoCoords.value)
    copiado.value = true
    setTimeout(() => { copiado.value = false }, 2000)
  } catch {
    window.prompt('Copia este texto (Ctrl + C) y pégalo en src/data/coordenadas.js:', textoCoords.value)
  }
}

function borrarPruebas() {
  if (!window.confirm('¿Borrar las posiciones de prueba de este navegador? (las que ya pegaste en coordenadas.js no se pierden)')) return
  pruebas.value = {}
  guardarPruebas()
}

const selected = computed(() => LUGARES.value.find((e) => e.id === selectedId.value) || LUGARES.value[0])

const route = useRoute()
watch(
  () => route.query.lugar,
  (id) => {
    if (!id || !LUGARES.value.some((l) => l.id === id)) return
    tipoActivo.value = 'todos'
    query.value = ''
    selectedId.value = String(id)
  },
  { immediate: true },
)

const tiposPresentes = computed(() => [...new Set(LUGARES.value.map((e) => e.tipo))])

const filtrados = computed(() => {
  const q = query.value.toLowerCase().trim()
  return LUGARES.value.filter((e) => {
    if (tipoActivo.value !== 'todos' && e.tipo !== tipoActivo.value) return false
    if (!q) return true
    return (
      e.nombre.toLowerCase().includes(q) ||
      (e.descripcion || '').toLowerCase().includes(q) ||
      e.aulas.some((a) => a.nombre.toLowerCase().includes(q))
    )
  })
})

const aulasPorPiso = computed(() => {
  const grupos = {}
  for (const a of selected.value.aulas) (grupos[a.piso] ||= []).push(a)
  return Object.entries(grupos).sort((a, b) => a[0] - b[0])
})

const actsHere = computed(() =>
  (props.actividades || []).filter((a) => {
    const blob = `${a.ubicacion || ''} ${a.aula || ''} ${a.lugar_id || ''}`.toLowerCase()
    return (
      blob.includes(selected.value.nombre.toLowerCase()) ||
      blob.includes(selectedId.value) ||
      (selectedAula.value && blob.includes(selectedAula.value.toLowerCase()))
    )
  }),
)

// --- Cómo llegar dentro del campus ---
const origenId = ref('entrada')
const copiadoCoord = ref(false)

const origenes = computed(() =>
  LUGARES.value.filter((l) => tieneCoordenadas(l) && l.id !== selectedId.value).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es')),
)
const origen = computed(() => LUGARES.value.find((l) => l.id === origenId.value && tieneCoordenadas(l)) || null)

const guia = computed(() => {
  const d = selected.value
  const o = origen.value
  if (!tieneCoordenadas(d) || !o || o.id === d.id) return null
  const metros = Math.round(distanciaM(o, d))
  return {
    metros,
    minutos: minutosAPie(metros),
    rumbo: nombreRumbo(rumboGrados(o, d)),
    cerca: cercanos(d, LUGARES.value, [o.id]),
  }
})

const trazo = computed(() =>
  guia.value ? [[origen.value.lat, origen.value.lng], [selected.value.lat, selected.value.lng]] : null,
)

const textoCoord = computed(() => (tieneCoordenadas(selected.value) ? `${selected.value.lat}, ${selected.value.lng}` : ''))
const manual = computed(() => INDICACIONES[selectedId.value] || '')
const verEnGoogle = computed(() =>
  tieneCoordenadas(selected.value) ? `https://www.google.com/maps/search/?api=1&query=${selected.value.lat},${selected.value.lng}` : '',
)

async function copiarCoord() {
  try {
    await navigator.clipboard.writeText(textoCoord.value)
    copiadoCoord.value = true
    setTimeout(() => { copiadoCoord.value = false }, 2000)
  } catch {
    window.prompt('Copia las coordenadas (Ctrl + C):', textoCoord.value)
  }
}

const rutaGoogle = computed(() =>
  tieneCoordenadas(selected.value)
    ? `https://www.google.com/maps/dir/?api=1&destination=${selected.value.lat},${selected.value.lng}`
    : '',
)

// Fotos: si hay un aula elegida con fotos se muestran esas; si no, las del lugar.
const rotas = ref(new Set())
const fotoIdx = ref(0)
const visor = ref(false)

const aulaElegida = computed(() => selected.value.aulas.find((a) => a.nombre === selectedAula.value) || null)

const fotosActuales = computed(() => {
  const deAula = aulaElegida.value ? fotosDe(aulaElegida.value.id) : []
  const lista = deAula.length ? deAula : fotosDe(selected.value.id)
  return lista.filter((src) => !rotas.value.has(src))
})

const etiquetaFotos = computed(() =>
  aulaElegida.value && fotosDe(aulaElegida.value.id).length ? aulaElegida.value.nombre : selected.value.nombre,
)

function marcarRota(src) {
  rotas.value = new Set(rotas.value).add(src)
}

const fotoPrincipal = computed(() => fotosActuales.value[fotoIdx.value] || fotosActuales.value[0] || '')

function moverFoto(delta) {
  const n = fotosActuales.value.length
  if (n) fotoIdx.value = (fotoIdx.value + delta + n) % n
}

function onTecla(e) {
  if (!visor.value) return
  if (e.key === 'Escape') visor.value = false
  if (e.key === 'ArrowRight') moverFoto(1)
  if (e.key === 'ArrowLeft') moverFoto(-1)
}

onMounted(() => window.addEventListener('keydown', onTecla))
onBeforeUnmount(() => window.removeEventListener('keydown', onTecla))

watch([selectedId, selectedAula], () => { fotoIdx.value = 0 })
watch(selectedId, () => { selectedAula.value = '' })
</script>

<template>
  <div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h1 class="text-2xl font-black">Mapa del campus ULS</h1>
        <p class="text-sm opacity-70">{{ CAMPUS_CENTER.direccion }}</p>
      </div>
      <input v-model="query" class="input input-bordered w-full sm:w-72" placeholder="Buscar edificio, aula u oficina..." />
    </div>

    <div class="flex flex-wrap gap-2">
      <button class="btn btn-sm" :class="tipoActivo === 'todos' ? 'btn-primary' : 'btn-outline'" type="button" @click="tipoActivo = 'todos'">
        Todos
      </button>
      <button
        v-for="t in tiposPresentes"
        :key="t"
        class="btn btn-sm"
        :class="tipoActivo === t ? 'btn-primary' : 'btn-outline'"
        type="button"
        @click="tipoActivo = t"
      >
        {{ TIPOS[t]?.icono }} {{ TIPOS[t]?.label || t }}
      </button>
    </div>

    <div class="grid lg:grid-cols-[1.6fr_1fr] gap-4">
      <div class="card bg-base-100 shadow-xl overflow-hidden self-start">
        <div class="h-[26rem] lg:h-[34rem]">
          <LeafletMap :lugares="filtrados" :selected-id="selectedId" :ubicando="ubicando" :trazo="trazo" @select="selectedId = $event" @ubicar="ubicar" />
        </div>

        <div v-if="esDev" class="border-t border-base-200 p-3 text-sm space-y-2 bg-base-200/40">
          <div class="flex flex-wrap items-center gap-3">
            <label class="label cursor-pointer gap-2 p-0">
              <input v-model="ubicando" type="checkbox" class="toggle toggle-primary toggle-sm" />
              <span class="font-semibold">Modo ubicar</span>
            </label>
            <span class="opacity-70">{{ sinUbicar }} lugares sin ubicar</span>
            <button class="btn btn-xs btn-primary" type="button" @click="copiarCoords">{{ copiado ? '¡Copiado!' : 'Copiar coordenadas' }}</button>
            <button class="btn btn-xs btn-ghost" type="button" @click="borrarPruebas">Borrar pruebas</button>
          </div>
          <p v-if="ubicando" class="text-xs">
            1) Elige un lugar en la lista. 2) Haz clic en el mapa donde está. Repite con los demás.
            Al terminar pulsa «Copiar coordenadas» y pégalas en <code>src/data/coordenadas.js</code>.
          </p>
        </div>
      </div>

      <div class="flex flex-col gap-3">
        <div class="card bg-base-100 shadow-md">
          <div class="card-body gap-2 p-4">
            <div class="flex items-start justify-between gap-2">
              <h2 class="card-title text-lg">{{ iconoDe(selected) }} {{ selected.nombre }}</h2>
              <span class="badge badge-outline shrink-0">{{ TIPOS[selected.tipo]?.label || selected.tipo }}</span>
            </div>
            <p v-if="selected.descripcion" class="text-sm">{{ selected.descripcion }}</p>
            <div v-if="!tieneCoordenadas(selected)" class="alert alert-warning py-2 text-sm">
              <span>Este lugar todavía no tiene ubicación en el mapa.</span>
            </div>

            <div v-if="fotosActuales.length" class="mt-1">
              <div class="relative">
                <img
                  :src="fotoPrincipal"
                  :alt="`${etiquetaFotos} (foto ${fotoIdx + 1})`"
                  class="w-full h-52 object-cover rounded-box cursor-zoom-in"
                  @click="visor = true"
                  @error="marcarRota(fotoPrincipal)"
                />
                <template v-if="fotosActuales.length > 1">
                  <button type="button" class="btn btn-circle btn-sm absolute left-2 top-1/2 -translate-y-1/2 opacity-80" aria-label="Foto anterior" @click="moverFoto(-1)">❮</button>
                  <button type="button" class="btn btn-circle btn-sm absolute right-2 top-1/2 -translate-y-1/2 opacity-80" aria-label="Foto siguiente" @click="moverFoto(1)">❯</button>
                  <span class="badge badge-neutral absolute bottom-2 right-2">{{ fotoIdx + 1 }} / {{ fotosActuales.length }}</span>
                </template>
                <button type="button" class="btn btn-xs absolute top-2 right-2 opacity-80" @click="visor = true">Ampliar</button>
              </div>
              <div v-if="fotosActuales.length > 1" class="flex gap-2 overflow-x-auto mt-2 pb-1">
                <button
                  v-for="(src, i) in fotosActuales"
                  :key="src"
                  type="button"
                  class="shrink-0 rounded-lg overflow-hidden border-2"
                  :class="i === fotoIdx ? 'border-primary' : 'border-transparent opacity-70 hover:opacity-100'"
                  @click="fotoIdx = i"
                >
                  <img :src="src" :alt="`Miniatura ${i + 1}`" loading="lazy" class="w-20 h-14 object-cover" />
                </button>
              </div>
              <p class="text-xs opacity-60 mt-1">{{ etiquetaFotos }}</p>
            </div>

            <div v-for="[piso, aulas] in aulasPorPiso" :key="piso">
              <p class="text-xs font-bold opacity-60 mt-1">PISO {{ piso }}</p>
              <div class="flex flex-wrap gap-2 mt-1">
                <button
                  v-for="aula in aulas"
                  :key="aula.id"
                  class="btn btn-xs"
                  :class="selectedAula === aula.nombre ? 'btn-secondary' : 'btn-outline'"
                  type="button"
                  @click="selectedAula = selectedAula === aula.nombre ? '' : aula.nombre"
                >
                  {{ aula.nombre }}
                </button>
              </div>
            </div>

            <div class="divider my-1 text-xs opacity-70">Dentro del campus</div>
            <div v-if="tieneCoordenadas(selected)" class="space-y-2 text-sm">
              <div class="flex flex-wrap items-center gap-2">
                <span class="badge badge-ghost font-mono">{{ textoCoord }}</span>
                <button type="button" class="btn btn-xs btn-outline" @click="copiarCoord">{{ copiadoCoord ? '¡Copiado!' : 'Copiar' }}</button>
                <a :href="verEnGoogle" target="_blank" rel="noopener" class="btn btn-xs btn-outline">Ver en Google Maps ↗</a>
              </div>

              <div class="flex flex-wrap items-center gap-2">
                <span class="opacity-70">Cómo llegar desde:</span>
                <select v-model="origenId" class="select select-bordered select-xs max-w-[13rem]">
                  <option value="">(sin ruta)</option>
                  <option v-for="o in origenes" :key="o.id" :value="o.id">{{ o.nombre }}</option>
                </select>
              </div>

              <div v-if="guia" class="alert alert-info py-2 text-sm block">
                <p>
                  Desde <strong>{{ origen.nombre }}</strong>: unos <strong>{{ guia.metros }} m</strong>
                  (≈ {{ guia.minutos }} min a pie) hacia el <strong>{{ guia.rumbo }}</strong>.
                </p>
                <p v-if="aulaElegida" class="mt-1">
                  {{ aulaElegida.nombre }} está en el piso {{ aulaElegida.piso }} de este edificio.
                </p>
                <p v-if="guia.cerca.length" class="mt-1">
                  Puntos de referencia cerca:
                  <template v-for="(c, i) in guia.cerca" :key="c.lugar.id">{{ i ? ', ' : '' }}{{ c.lugar.nombre }} ({{ c.metros }} m)</template>.
                </p>
                <p class="mt-1 text-xs opacity-70">Indicación aproximada en línea recta; el camino real puede variar.</p>
              </div>
              <p v-else-if="origenId && origenId === selectedId" class="text-xs opacity-70">
                Este es el punto de partida elegido. Selecciona otro lugar para ver cómo llegar.
              </p>
              <p v-else-if="origenId && origenId !== selectedId && !origen" class="text-xs opacity-70">
                El punto de partida elegido todavía no tiene ubicación en el mapa.
              </p>
            </div>

            <div v-if="manual" class="bg-base-200 rounded-box p-3 text-sm">
              <p class="font-semibold mb-1">Paso a paso</p>
              <p class="whitespace-pre-line">{{ manual }}</p>
            </div>

            <div v-if="rutaGoogle" class="card-actions mt-2">
              <a :href="rutaGoogle" target="_blank" rel="noopener" class="btn btn-primary btn-sm">Cómo llegar al campus ↗</a>
            </div>

            <div v-if="actsHere.length" class="mt-2">
              <p class="font-semibold text-sm">Actividades del foro en este punto</p>
              <ul class="mt-2 space-y-1">
                <li v-for="act in actsHere" :key="act.id" class="alert alert-info py-2 text-sm">
                  <span>{{ act.titulo }} · {{ act.ubicacion || act.aula }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <ul class="menu bg-base-100 rounded-box shadow-md w-full max-h-72 overflow-y-auto flex-nowrap">
          <li v-if="!filtrados.length" class="menu-title">Sin resultados</li>
          <li v-for="e in filtrados" :key="e.id">
            <button type="button" :class="selectedId === e.id ? 'bg-primary text-primary-content' : ''" @click="selectedId = e.id">
              <span>{{ iconoDe(e) }}</span>
              <span class="flex-1 text-left">{{ e.nombre }}</span>
              <span v-if="!tieneCoordenadas(e)" class="badge badge-ghost badge-sm" title="Sin ubicar en el mapa">sin ubicar</span>
              <span v-if="e.aulas.length" class="badge badge-sm">{{ e.aulas.length }}</span>
            </button>
          </li>
        </ul>
      </div>
    </div>
  
    <Teleport to="body">
      <dialog class="modal z-[1100]" :class="{ 'modal-open': visor && fotoPrincipal }">
        <div class="modal-box max-w-5xl p-2 relative">
          <img :src="fotoPrincipal" :alt="etiquetaFotos" class="w-full max-h-[78vh] object-contain rounded-box" />
          <template v-if="fotosActuales.length > 1">
            <button type="button" class="btn btn-circle absolute left-3 top-1/2 -translate-y-1/2" aria-label="Foto anterior" @click="moverFoto(-1)">❮</button>
            <button type="button" class="btn btn-circle absolute right-3 top-1/2 -translate-y-1/2" aria-label="Foto siguiente" @click="moverFoto(1)">❯</button>
          </template>
          <div class="flex items-center justify-between p-2">
            <span class="text-sm">{{ etiquetaFotos }} · {{ fotoIdx + 1 }} / {{ fotosActuales.length }}</span>
            <button class="btn btn-sm" type="button" @click="visor = false">Cerrar</button>
          </div>
        </div>
        <div class="modal-backdrop" @click="visor = false"></div>
      </dialog>
    </Teleport>
  </div>
</template>
