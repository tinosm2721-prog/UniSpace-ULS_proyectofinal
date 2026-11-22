<script setup>
import { ref, computed } from 'vue'
import { lugaresPlanos } from '@/data/campus.js'
import { actualizarPublicacion, eliminarPublicacion } from '@/lib/publicaciones.js'
import ComentariosLike from '@/components/common/ComentariosLike.vue'
import api from '@/lib/api.js'
import { notify, confirmar } from '@/lib/ui.js'

const props = defineProps({
  id: { type: [Number, String], required: true },
  tema: { type: String, default: '' },
  titulo: { type: String, default: '' },
  contenido: { type: String, default: '' },
  imagen: { type: String, default: '' },
  autor: { type: String, default: '' },
  user_id: { type: [Number, String], default: null },
  created_at: { type: String, default: '' },
  total_comentarios: { type: Number, default: 0 },
  total_likes: { type: Number, default: 0 },
  user_liked: { type: Boolean, default: false },
  ubicacion: { type: String, default: '' },
  aula: { type: String, default: '' },
  lugar_id: { type: String, default: '' },
  publica: { type: [Number, String, Boolean], default: null },
  isOwner: { type: Boolean, default: false },
})

const emit = defineEmits(['updated', 'deleted'])

// ---------- IMAGEN: arma la URL completa hacia el backend ----------
const backendOrigin = String(api.defaults?.baseURL || '')
  .replace(/\/api\/?$/, '')
  .replace(/\/$/, '')

const imagenSrc = computed(() => {
  const img = props.imagen
  if (!img) return ''
  if (/^(https?:|data:|blob:)/.test(img)) return img
  const ruta = img.replace(/^\/+/, '')
  return ruta.startsWith('storage/')
    ? `${backendOrigin}/${ruta}`
    : `${backendOrigin}/storage/${ruta}`
})

// ---------- FECHA: formato legible ----------
const fechaFormateada = computed(() => {
  if (!props.created_at) return ''
  const d = new Date(props.created_at)
  return isNaN(d)
    ? props.created_at
    : d.toLocaleString('es-SV', { dateStyle: 'medium', timeStyle: 'short' })
})

const lugares = lugaresPlanos()
const menuOpen = ref(false)
const modalOpen = ref(false)

const editTema = ref(props.tema)
const editTitulo = ref(props.titulo)
const editContenido = ref(props.contenido)
const editLugarId = ref(props.lugar_id || '')
const editPublica = ref('1')
const editImagenFile = ref(null)
const guardando = ref(false)

function abrirMenu() { menuOpen.value = !menuOpen.value }

function abrirEditar() {
  menuOpen.value = false
  editTema.value = props.tema
  editTitulo.value = props.titulo
  editContenido.value = props.contenido
  editLugarId.value = props.lugar_id || ''
  editImagenFile.value = null
  if (props.publica === null || props.publica === undefined) {
    editPublica.value = '1'
  } else {
    editPublica.value =
      props.publica === true || props.publica === 1 || props.publica === '1' ? '1' : '0'
  }
  modalOpen.value = true
}

function cerrarModal() { modalOpen.value = false }

function onFile(e) {
  editImagenFile.value = e.target.files?.[0] || null
}

async function guardarCambios() {
  guardando.value = true
  try {
    const formData = new FormData()
    formData.set('tema', editTema.value)
    formData.set('titulo', editTitulo.value)
    formData.set('contenido', editContenido.value)
    formData.set('publica', editPublica.value)
    if (editImagenFile.value) formData.set('imagen', editImagenFile.value)

    const lugar = lugares.find((l) => l.id === editLugarId.value)
    formData.set('lugar_id', editLugarId.value || '')
    formData.set('ubicacion', lugar?.label || '')
    formData.set('aula', lugar?.aula || '')
    formData.set('lat', lugar?.lat ?? '')
    formData.set('lng', lugar?.lng ?? '')

    await actualizarPublicacion(props.id, formData)
    notify('Publicación actualizada')
    modalOpen.value = false
    emit('updated')
  } catch (error) {
    notify(error.message || 'Error al actualizar', 'error')
  } finally {
    guardando.value = false
  }
}

async function eliminar() {
  menuOpen.value = false
  const ok = await confirmar('¿Eliminar esta publicación?', {
    titulo: 'Eliminar publicación',
    aceptar: 'Eliminar',
    peligro: true,
  })
  if (!ok) return
  try {
    await eliminarPublicacion(props.id)
    notify('Publicación eliminada')
    emit('deleted', props.id)
  } catch (error) {
    notify(error.message || 'Error al eliminar', 'error')
  }
}

function copiarEnlace() {
  menuOpen.value = false
  const url = `${window.location.origin}/publicaciones/${props.id}`
  navigator.clipboard?.writeText(url)
  notify('Enlace copiado', 'info')
}
</script>

<template>
  <article class="card bg-base-100 shadow-sm" :data-publicacion-id="id">
    <div class="card-body gap-2">
      <div class="flex justify-between items-start gap-2">
        <div>
          <router-link :to="`/usuario/${user_id}/usuario`" class="link link-primary font-semibold">{{ autor }}</router-link>
          <p v-if="created_at" class="text-xs text-base-content/60">{{ fechaFormateada }}</p>
        </div>

        <div v-if="isOwner" class="relative">
          <button type="button" class="btn btn-ghost btn-sm btn-square" @click="abrirMenu">⋮</button>
          <ul v-if="menuOpen" class="menu bg-base-100 rounded-box shadow-lg absolute right-0 top-10 z-20 w-48 p-2">
            <li><button type="button" @click="abrirEditar">✏️ Editar</button></li>
            <li><button type="button" class="text-error" @click="eliminar">🗑️ Eliminar</button></li>
            <li><button type="button" @click="copiarEnlace">🔗 Copiar enlace</button></li>
          </ul>
        </div>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span v-if="tema" class="badge badge-ghost">Tema: {{ tema }}</span>
        <router-link v-if="ubicacion || aula" to="/mapa" class="badge badge-primary badge-outline">
          📍 {{ aula ? `${aula} · ` : '' }}{{ ubicacion }}
        </router-link>
      </div>

      <h3 v-if="titulo" class="card-title">{{ titulo }}</h3>
      <p class="text-base">{{ contenido ?? '' }}</p>

      <figure v-if="imagen" class="mt-2">
        <img :src="imagenSrc" :alt="titulo || 'Imagen'" class="w-full max-h-[420px] object-cover rounded-box" />
      </figure>

      <ComentariosLike
        :id="id"
        tipo="publicacion"
        :total-comentarios="total_comentarios"
        :total-likes="total_likes"
        :user-liked="user_liked"
      />
    </div>

    <Teleport to="body">
      <dialog class="modal" :class="{ 'modal-open': modalOpen }">
        <div class="modal-box">
          <h3 class="font-bold text-lg mb-4">Editar publicación</h3>
          <form class="flex flex-col gap-3" @submit.prevent="guardarCambios">
            <input v-model="editTema" type="text" class="input input-bordered w-full" placeholder="Tema" required />
            <input v-model="editTitulo" type="text" class="input input-bordered w-full" placeholder="Título" />
            <textarea v-model="editContenido" class="textarea textarea-bordered w-full min-h-32" placeholder="Contenido"></textarea>

            <select v-model="editLugarId" class="select select-bordered w-full">
              <option value="">Sin ubicación</option>
              <option v-for="lugar in lugares" :key="lugar.id" :value="lugar.id">{{ lugar.label }}</option>
            </select>

            <div v-if="imagen" class="text-sm">
              <img :src="imagenSrc" alt="Imagen actual" class="max-w-[200px] rounded-box mb-1" />
              <p class="text-base-content/60">Subir una imagen nueva reemplazará la actual</p>
            </div>

            <input type="file" accept="image/*" class="file-input file-input-bordered w-full" @change="onFile" />

            <div class="flex flex-wrap items-center gap-4 bg-base-200 rounded-box p-4">
              <span class="font-semibold">Visibilidad:</span>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="editPublica" type="radio" value="1" class="radio radio-primary radio-sm" />
                <span>🌍 Público</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="editPublica" type="radio" value="0" class="radio radio-primary radio-sm" />
                <span>🔒 Privado</span>
              </label>
            </div>

            <div class="modal-action">
              <button type="button" class="btn btn-ghost" @click="cerrarModal">Cerrar</button>
              <button type="submit" class="btn btn-primary" :disabled="guardando">
                {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
              </button>
            </div>
          </form>
        </div>
        <div class="modal-backdrop" @click="cerrarModal"></div>
      </dialog>
    </Teleport>
  </article>
</template>