<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getMe } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import TourGuiado from '@/components/vue/TourGuiado.vue'

const router = useRouter()
const user = ref({})

onMounted(async () => {
  try {
    user.value = await getMe()
  } catch (e) {
    console.error(e)
  }
})
</script>

<template>
  <div class="fixed top-0 w-full z-[1000]"><Navbar v-bind="user" /></div>
  <aside class="fixed top-[90px] left-4 w-56 z-50 hidden lg:block"><Sidebar /></aside>

  <main class="max-w-4xl mx-auto pt-24 px-4 pb-12 lg:pl-72">
    <h1 class="text-2xl font-black">Tour de bienvenida</h1>
    <p class="text-sm opacity-70 mb-4">Conocé el campus paso a paso. Tocá un marcador del mapa para saltar a ese punto.</p>
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <TourGuiado altura-mapa="h-[24rem]" @finish="router.push('/mapa')" />
      </div>
    </div>
  </main>
</template>
