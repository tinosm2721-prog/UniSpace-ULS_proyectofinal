<script setup>
import { ref, computed, onMounted } from 'vue'
import { getPodcast, createPodcast, deletePodcast } from '@/lib/podcast.js'
import { notify, confirmar } from '@/lib/ui.js'

const props = defineProps({
  currentUserId: { type: [Number, String], default: null },
})

const podcasts = ref([])
const loading = ref(true)
const error = ref('')
const buscar = ref('')

const form = ref({ tema: '', titulo: '', descripcion: '' })
const modo = ref('archivo') // 'archivo' | 'enlace'
const enlace = ref('')
const archivo = ref(null)
const archivoEl = ref(null)
const EXTENSIONES = ['mp3', 'wav', 'ogg', 'mp4', 'mov', 'avi']
const enviando = ref(false)
const errorForm = ref('')

const filtrados = computed(() => {
  const q = buscar.value.toLowerCase().trim()
  if (!q) return podcasts.value
  return podcasts.value.filter((p) =>
    `${p.titulo ?? ''} ${p.tema ?? ''} ${p.descripcion ?? ''} ${p.autor ?? ''}`.toLowerCase().includes(q)
  )
})

function esDueno(p) {
  return props.currentUserId != null && Number(props.currentUserId) === Number(p.user_id)
}

function fecha(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return isNaN(d) ? '' : d.toLocaleDateString('es-SV', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function cargar() {
  loading.value = true
  error.value = ''
  try {
    const res = await getPodcast()
    podcasts.value = Array.isArray(res?.data) ? res.data : Array.isArray(res) ? res : []
  } catch (e) {
    console.error(e)
    error.value = 'No se pudieron cargar los podcasts.'
  } finally {
    loading.value = false
  }
}

function onArchivo(e) {
  errorForm.value = ''
  const f = e.target.files?.[0] || null
  if (f) {
    const ext = f.name.split('.').pop().toLowerCase()
    if (!EXTENSIONES.includes(ext)) {
      errorForm.value = `Formato no permitido. Usa: ${EXTENSIONES.join(', ')}.`
      e.target.value = ''
      archivo.value = null
      return
    }
  }
  archivo.value = f
}

async function publicar() {
  errorForm.value = ''
  if (!form.value.titulo.trim()) {
    errorForm.value = 'El título es obligatorio.'
    return
  }
  if (modo.value === 'archivo' && !archivo.value) {
    errorForm.value = 'Selecciona un archivo de audio o video.'
    return
  }
  if (modo.value === 'enlace' && !/^https?:\/\/\S+$/i.test(enlace.value.trim())) {
    errorForm.value = 'Pega un enlace válido que empiece con http:// o https://'
    return
  }
  enviando.value = true
  try {
    // El backend exige "contenido" como archivo, por eso se envía como FormData
    const datos = new FormData()
    datos.append('tema', form.value.tema.trim())
    datos.append('titulo', form.value.titulo.trim())
    datos.append('descripcion', form.value.descripcion.trim())
    datos.append('contenido', modo.value === 'archivo' ? archivo.value : enlace.value.trim())

    await createPodcast(datos)
    form.value = { tema: '', titulo: '', descripcion: '' }
    archivo.value = null
    enlace.value = ''
    if (archivoEl.value) archivoEl.value.value = ''
    notify('Podcast publicado')
    await cargar()
  } catch (e) {
    errorForm.value = e.message || 'Error al crear el podcast'
    if (modo.value === 'enlace' && /must be a file/i.test(errorForm.value)) {
      errorForm.value = 'El servidor todavía solo acepta archivos en este campo. Hay que habilitar enlaces en el backend (validación de "contenido"). Mientras tanto, sube un archivo.'
    }
  } finally {
    enviando.value = false
  }
}

async function eliminar(p) {
  const ok = await confirmar(`¿Eliminar "${p.titulo}"?`, {
    titulo: 'Eliminar podcast',
    aceptar: 'Eliminar',
    peligro: true,
  })
  if (!ok) return
  try {
    await deletePodcast(p.id)
    podcasts.value = podcasts.value.filter((x) => x.id !== p.id)
    notify('Podcast eliminado')
  } catch (e) {
    console.error(e)
    notify('No se pudo eliminar el podcast.', 'error')
  }
}

onMounted(cargar)
</script>

<template>
  <section class="card bg-base-100 shadow-sm mb-6">
    <div class="card-body">
      <div class="flex flex-wrap justify-between items-center gap-4 mb-2">
        <div>
          <h2 class="text-3xl font-black">🎙️ Podcast</h2>
          <p class="text-base-content/60">Comparte episodios, charlas y audios de la comunidad</p>
        </div>
        <span class="badge badge-primary badge-lg font-bold">{{ podcasts.length }} podcasts</span>
      </div>

      <form class="flex flex-col gap-3" @submit.prevent="publicar">
        <div class="grid gap-3 sm:grid-cols-[2fr_1fr]">
          <input v-model="form.titulo" type="text" class="input input-bordered w-full" placeholder="Título del podcast" required />
          <input v-model="form.tema" type="text" class="input input-bordered w-full" placeholder="Tema (ej. tecnología)" />
        </div>
        <textarea v-model="form.descripcion" rows="2" class="textarea textarea-bordered w-full" placeholder="Descripción corta"></textarea>

        <div role="tablist" class="tabs tabs-box w-fit">
          <button type="button" role="tab" class="tab" :class="{ 'tab-active': modo === 'archivo' }" @click="modo = 'archivo'">📁 Subir archivo</button>
          <button type="button" role="tab" class="tab" :class="{ 'tab-active': modo === 'enlace' }" @click="modo = 'enlace'">🔗 Pegar enlace</button>
        </div>

        <label v-if="modo === 'archivo'" class="flex flex-col gap-1">
          <span class="text-sm text-base-content/60">Archivo de audio o video (mp3, wav, ogg, mp4, mov, avi)</span>
          <input ref="archivoEl" type="file" class="file-input file-input-bordered w-full" accept=".mp3,.wav,.ogg,.mp4,.mov,.avi,audio/*,video/*" @change="onArchivo" />
        </label>
        <label v-else class="flex flex-col gap-1">
          <span class="text-sm text-base-content/60">Enlace de YouTube, Spotify o un archivo de audio/video</span>
          <input v-model="enlace" type="url" class="input input-bordered w-full" placeholder="https://www.youtube.com/watch?v=..." />
        </label>

        <div v-if="errorForm" role="alert" class="alert alert-error"><span>{{ errorForm }}</span></div>

        <button type="submit" class="btn btn-primary self-start" :disabled="enviando">
          <span v-if="enviando" class="loading loading-spinner loading-sm"></span>
          {{ enviando ? 'Publicando...' : 'Publicar podcast' }}
        </button>
      </form>
    </div>
  </section>

  <section class="card bg-base-100 shadow-sm">
    <div class="card-body">
      <input v-model="buscar" type="search" class="input input-bordered w-full mb-2" placeholder="Buscar por título, tema o autor..." />

      <div v-if="loading" class="text-center py-8"><span class="loading loading-spinner loading-lg text-primary"></span></div>
      <div v-else-if="error" class="text-center py-8">
        <h3 class="font-bold text-lg mb-2">{{ error }}</h3>
        <button type="button" class="btn btn-outline btn-primary btn-sm" @click="cargar">Reintentar</button>
      </div>
      <div v-else-if="!filtrados.length" class="text-center py-8">
        <h3 class="font-bold text-lg">{{ buscar ? 'Sin resultados' : 'Aún no hay podcasts' }}</h3>
        <p v-if="!buscar" class="text-base-content/60">Sé la primera persona en publicar uno.</p>
      </div>

      <article v-for="p in filtrados" :key="p.id" class="card bg-base-200 mt-3">
        <div class="card-body gap-1 p-5">
          <div class="flex flex-wrap justify-between gap-2 text-xs">
            <span v-if="p.tema" class="badge badge-secondary badge-outline">#{{ p.tema }}</span>
            <span class="text-base-content/60">
              {{ p.autor || 'Autor' }}<template v-if="fecha(p.created_at)"> · {{ fecha(p.created_at) }}</template>
            </span>
          </div>
          <router-link :to="`/podcast/${p.id}`" class="text-xl font-extrabold hover:text-primary">{{ p.titulo || 'Sin título' }}</router-link>
          <p v-if="p.descripcion" class="text-base-content/80">{{ p.descripcion }}</p>
          <div class="card-actions mt-2">
            <router-link :to="`/podcast/${p.id}`" class="btn btn-outline btn-primary btn-sm">▶ Escuchar / ver</router-link>
            <button v-if="esDueno(p)" type="button" class="btn btn-outline btn-error btn-sm" @click="eliminar(p)">Eliminar</button>
          </div>
        </div>
      </article>
    </div>
  </section>
</template>