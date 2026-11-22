<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/lib/api.js'
import { getMe } from '@/lib/auth.js'
import { findLugar } from '@/data/campus.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import CampusMap from '@/components/vue/CampusMap.vue'

const route = useRoute()
const user = ref({})
const actividades = ref([])

// ?lugar=<id de edificio o de aula> -> id del edificio a seleccionar
const initialId = computed(() => {
  const lugar = String(route.query.lugar || '')
  return lugar ? findLugar(lugar)?.edificioId || '' : ''
})

onMounted(async () => {
  try {
    user.value = await getMe()
  } catch (e) {
    console.error(e)
  }
  // Publicaciones del foro que mencionan un lugar del campus
  try {
    const res = await api.get('/publicaciones', { params: { per_page: 100 } })
    const body = res.data
    const lista = Array.isArray(body?.data) ? body.data : Array.isArray(body) ? body : []
    actividades.value = lista.filter((p) => p.ubicacion || p.aula || p.lugar_id)
  } catch (e) {
    console.error(e)
  }
})
</script>

<template>
  <div class="fixed top-0 w-full z-[1000]"><Navbar v-bind="user" /></div>
  <aside class="fixed top-[90px] left-4 w-56 z-50 hidden lg:block"><Sidebar /></aside>

  <main class="max-w-6xl mx-auto pt-24 px-4 pb-12 lg:pl-72">
    <CampusMap :key="route.fullPath" :actividades="actividades" :initial-id="initialId" />
  </main>
</template>
