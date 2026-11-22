<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { getMe } from '@/lib/auth.js'
import { getPodcastById, deletePodcast } from '@/lib/podcast.js'
import { API_URL } from '@/lib/config.js'
import { notify, confirmar } from '@/lib/ui.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'

const route = useRoute()
const router = useRouter()

const user = ref({})
const podcast = ref(null)
const cargando = ref(true)
const error = ref('')

const esDueno = computed(() => Number(user.value?.id) === Number(podcast.value?.user_id))

// El backend guarda el archivo en /storage; se arma la URL completa
const STORAGE = String(API_URL || '').replace(/\/api\/?$/, '')
function urlArchivo(c) {
  if (/^https?:\/\//i.test(c)) return c
  return `${STORAGE}/storage/${c.replace(/^\/?(storage\/)?/, '')}`
}

// Detecta qué tipo de contenido trae el campo "contenido"
const media = computed(() => {
  const c = String(podcast.value?.contenido ?? '').trim()
  if (!c) return { tipo: 'vacio' }

  const yt = c.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/i)
  if (yt) return { tipo: 'iframe', src: `https://www.youtube.com/embed/${yt[1]}`, alto: 360 }

  const sp = c.match(/open\.spotify\.com\/(episode|show|track)\/([\w]+)/i)
  if (sp) return { tipo: 'iframe', src: `https://open.spotify.com/embed/${sp[1]}/${sp[2]}`, alto: 232 }

  const ext = (c.split('?')[0].match(/\.(\w+)$/) || [])[1]?.toLowerCase()
  if (['mp3', 'wav', 'ogg', 'm4a', 'aac'].includes(ext)) return { tipo: 'audio', src: urlArchivo(c) }
  if (['mp4', 'webm', 'mov'].includes(ext)) return { tipo: 'video', src: urlArchivo(c) }
  // .avi no lo reproducen los navegadores: se ofrece como descarga
  if (ext === 'avi') return { tipo: 'enlace', src: urlArchivo(c) }
  if (/^https?:\/\//i.test(c)) return { tipo: 'enlace', src: c }
  return { tipo: 'texto', texto: c }
})

function fecha(iso) {
  if (!iso) return ''
  const d = new Date(iso)
  return isNaN(d) ? '' : d.toLocaleDateString('es-SV', { day: 'numeric', month: 'long', year: 'numeric' })
}

async function cargar() {
  cargando.value = true
  error.value = ''
  try {
    const [me, pod] = await Promise.all([getMe(), getPodcastById(route.params.id)])
    user.value = me
    podcast.value = pod?.data && !pod.titulo ? pod.data : pod
  } catch (e) {
    console.error(e)
    error.value = 'No se encontró este podcast.'
  } finally {
    cargando.value = false
  }
}

async function eliminar() {
  const ok = await confirmar('¿Eliminar este podcast? Esta acción no se puede deshacer.', {
    titulo: 'Eliminar podcast',
    aceptar: 'Eliminar',
    peligro: true,
  })
  if (!ok) return
  try {
    await deletePodcast(podcast.value.id)
    notify('Podcast eliminado')
    router.push('/podcast/podcast')
  } catch (e) {
    console.error(e)
    notify('No se pudo eliminar el podcast.', 'error')
  }
}

onMounted(cargar)
watch(() => route.params.id, (n, o) => { if (n && n !== o) cargar() })
</script>

<template>
  <header class="fixed top-0 inset-x-0 z-50 bg-base-100/85 backdrop-blur-md">
    <Navbar v-bind="user" />
  </header>

  <aside class="hidden lg:block fixed top-[90px] left-4 w-56 z-40">
    <Sidebar />
  </aside>

  <main class="pt-24 pb-16 px-4 lg:pl-64">
    <div class="max-w-3xl mx-auto">
      <router-link to="/podcast/podcast" class="btn btn-ghost btn-sm mb-4">← Volver a podcasts</router-link>

      <div v-if="cargando" class="text-center py-12">
        <span class="loading loading-spinner loading-lg text-primary"></span>
      </div>

      <div v-else-if="error" class="card bg-base-100 shadow-sm">
        <div class="card-body items-center text-center">
          <h3 class="text-xl font-bold">{{ error }}</h3>
        </div>
      </div>

      <article v-else class="card bg-base-100 shadow-sm">
        <div class="card-body gap-3">
          <div class="flex flex-wrap justify-between gap-2 text-sm">
            <span v-if="podcast.tema" class="badge badge-secondary badge-outline">#{{ podcast.tema }}</span>
            <span class="text-base-content/60">
              {{ podcast.autor || 'Autor' }}<template v-if="fecha(podcast.created_at)"> · {{ fecha(podcast.created_at) }}</template>
            </span>
          </div>

          <h1 class="text-3xl font-extrabold">{{ podcast.titulo || 'Sin título' }}</h1>
          <p v-if="podcast.descripcion" class="text-base-content/80">{{ podcast.descripcion }}</p>

          <div class="my-4">
            <audio v-if="media.tipo === 'audio'" :src="media.src" controls preload="metadata" class="w-full" />
            <video v-else-if="media.tipo === 'video'" :src="media.src" controls preload="metadata" class="w-full rounded-box" />
            <iframe
              v-else-if="media.tipo === 'iframe'"
              :src="media.src"
              :height="media.alto"
              allowfullscreen
              loading="lazy"
              title="Reproductor"
              class="w-full rounded-box border-0"
            />
            <a v-else-if="media.tipo === 'enlace'" :href="media.src" target="_blank" rel="noopener" class="btn btn-outline btn-primary">
              Abrir / descargar archivo ↗
            </a>
            <p v-else-if="media.tipo === 'texto'" class="whitespace-pre-wrap leading-relaxed text-base-content/80">{{ media.texto }}</p>
            <p v-else class="text-base-content/60">Este podcast aún no tiene contenido.</p>
          </div>

          <div v-if="esDueno" class="card-actions">
            <button type="button" class="btn btn-outline btn-error btn-sm" @click="eliminar">Eliminar podcast</button>
          </div>
        </div>
      </article>
    </div>
  </main>
</template>