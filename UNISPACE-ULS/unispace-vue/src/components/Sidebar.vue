<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRoute } from 'vue-router'
import WelcomeTour from '@/components/vue/WelcomeTour.vue'

const route = useRoute()
const tourRef = ref(null)

const links = [
  { href: '/dashboard', label: 'Inicio' },
  { href: '/publicaciones/publicaciones', label: 'Publicaciones' },
  { href: '/podcast/podcast', label: 'Podcast' },
  { href: '/mapa', label: 'Mapa del campus' },
  { href: '/como-llegar', label: 'Cómo llegar' },
  { href: '/objetos-perdidos', label: 'Objetos perdidos' },
]

function abrirTour() { tourRef.value?.abrir() }

onMounted(() => window.addEventListener('unispace:abrir-tour', abrirTour))
onBeforeUnmount(() => window.removeEventListener('unispace:abrir-tour', abrirTour))
</script>

<template>
  <aside class="w-full hidden lg:block">
    <h2 class="text-lg font-extrabold mb-3 px-2">Panel</h2>
    <ul class="menu bg-base-100 rounded-box w-full gap-1 p-2 shadow-sm">
      <li v-for="link in links" :key="link.href">
        <router-link :to="link.href" :class="route.path === link.href ? 'bg-primary text-primary-content' : ''" class="font-semibold">
          {{ link.label }}
        </router-link>
      </li>
      <li>
        <a href="#" class="font-semibold" @click.prevent="abrirTour">Bienvenida / Tour</a>
      </li>
    </ul>
  </aside>

  <WelcomeTour ref="tourRef" auto />
</template>