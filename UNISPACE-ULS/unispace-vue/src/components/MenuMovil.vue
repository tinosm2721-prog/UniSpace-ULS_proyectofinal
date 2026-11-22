<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const abierto = ref(false)

const links = [
  { href: '/dashboard', label: 'Inicio', icono: '🏠' },
  { href: '/publicaciones/publicaciones', label: 'Publicaciones', icono: '📝' },
  { href: '/podcast/podcast', label: 'Podcast', icono: '🎙️' },
  { href: '/mapa', label: 'Mapa del campus', icono: '🗺️' },
  { href: '/como-llegar', label: 'Cómo llegar', icono: '🚌' },
  { href: '/objetos-perdidos', label: 'Objetos perdidos', icono: '🎒' },
]

function abrirTour() {
  abierto.value = false
  window.dispatchEvent(new CustomEvent('unispace:abrir-tour'))
}

// Se cierra solo al cambiar de página
watch(() => route.path, () => { abierto.value = false })
</script>

<template>
  <button
    type="button"
    class="btn btn-ghost btn-square lg:hidden"
    aria-label="Abrir menú"
    @click="abierto = true"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  </button>

  <Teleport to="body">
    <div v-if="abierto" class="fixed inset-0 z-[95] lg:hidden">
      <div class="absolute inset-0 bg-black/50" @click="abierto = false"></div>

      <nav class="absolute left-0 top-0 h-full w-72 max-w-[85%] bg-base-100 shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-4 h-[72px] border-b border-base-300">
          <span class="text-xl font-extrabold text-primary">UniSpace - ULS</span>
          <button type="button" class="btn btn-ghost btn-sm btn-circle" aria-label="Cerrar menú" @click="abierto = false">✕</button>
        </div>

        <ul class="menu w-full p-3 gap-1 flex-1 overflow-y-auto flex-nowrap">
          <li v-for="l in links" :key="l.href">
            <router-link
              :to="l.href"
              class="font-semibold text-base"
              :class="route.path === l.href ? 'bg-primary text-primary-content' : ''"
            >
              <span>{{ l.icono }}</span> {{ l.label }}
            </router-link>
          </li>
          <li>
            <a href="#" class="font-semibold text-base" @click.prevent="abrirTour">
              <span>👋</span> Bienvenida / Tour
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </Teleport>
</template>