<script setup>
import { ref } from 'vue'
import { lugaresPlanos } from '@/data/campus.js'
import { crearPublicacion } from '@/lib/publicaciones.js'
import { notify } from '@/lib/ui.js'

const lugares = lugaresPlanos()
const emit = defineEmits(['created'])

const tema = ref('')
const titulo = ref('')
const contenido = ref('')
const lugarId = ref('')
const imagenFile = ref(null)
const publica = ref('1')
const formEl = ref(null)

function onFile(e) {
  imagenFile.value = e.target.files?.[0] || null
}

async function onSubmit() {
  try {
    const formData = new FormData()
    formData.set('tema', tema.value)
    formData.set('titulo', titulo.value)
    formData.set('contenido', contenido.value)
    formData.set('publica', publica.value)
    if (imagenFile.value) formData.set('imagen', imagenFile.value)

    const lugar = lugares.find((l) => l.id === lugarId.value)
    formData.set('lugar_id', lugarId.value || '')
    formData.set('ubicacion', lugar?.label || '')
    formData.set('aula', lugar?.aula || '')
    formData.set('lat', lugar?.lat ?? '')
    formData.set('lng', lugar?.lng ?? '')

    await crearPublicacion(formData)

    tema.value = ''
    titulo.value = ''
    contenido.value = ''
    lugarId.value = ''
    imagenFile.value = null
    publica.value = '1'
    if (formEl.value) formEl.value.reset()

    notify('Publicación creada')
    emit('created')
  } catch (error) {
    console.error(error)
    notify(error.message || 'Error al publicar', 'error')
  }
}
</script>

<template>
  <form ref="formEl" class="flex flex-col gap-3" @submit.prevent="onSubmit">
    <input v-model="tema" type="text" name="tema" class="input input-bordered w-full" placeholder="Tema" required />
    <input v-model="titulo" type="text" name="titulo" class="input input-bordered w-full" placeholder="Título" required />
    <textarea v-model="contenido" name="contenido" class="textarea textarea-bordered w-full min-h-32" placeholder="Contenido" required></textarea>

    <label class="font-bold text-sm mt-2">Ubicación de la actividad (campus)</label>
    <select v-model="lugarId" name="lugar_id" class="select select-bordered w-full">
      <option value="">Sin ubicación</option>
      <option v-for="lugar in lugares" :key="lugar.id" :value="lugar.id">{{ lugar.label }}</option>
    </select>

    <input type="file" name="imagen" accept="image/*" class="file-input file-input-bordered w-full" @change="onFile" />

    <div class="flex flex-wrap items-center gap-4 bg-base-200 rounded-box p-4">
      <span class="font-semibold">Visibilidad:</span>
      <label class="flex items-center gap-2 cursor-pointer">
        <input v-model="publica" type="radio" name="publica" value="1" class="radio radio-primary radio-sm" />
        <span>🌍 Público</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input v-model="publica" type="radio" name="publica" value="0" class="radio radio-primary radio-sm" />
        <span>🔒 Privado</span>
      </label>
    </div>

    <button type="submit" class="btn btn-primary w-full">Publicar</button>
  </form>
</template>