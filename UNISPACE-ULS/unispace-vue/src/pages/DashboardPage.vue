<script setup>
import { ref, onMounted } from 'vue'
import { getMe } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import PublicacionesView from '@/components/publicaciones/PublicacionesView.vue'
import PodcastView from '@/components/podcast/PodcastView.vue'

const user = ref({})
const tab = ref('publicaciones')

onMounted(async () => {
  try {
    user.value = await getMe()
  } catch (err) {
    console.error(err)
  }
})
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
      <div role="tablist" class="tabs tabs-box mb-8 bg-base-100 shadow-sm">
        <button
          role="tab"
          class="tab flex-1 font-bold"
          :class="{ 'tab-active': tab === 'publicaciones' }"
          @click="tab = 'publicaciones'"
        >
          Publicaciones
        </button>
        <button
          role="tab"
          class="tab flex-1 font-bold"
          :class="{ 'tab-active': tab === 'podcast' }"
          @click="tab = 'podcast'"
        >
          Podcast
        </button>
      </div>

      <PublicacionesView v-if="tab === 'publicaciones'" :current-user-id="user.id" />
      <PodcastView v-if="tab === 'podcast'" :current-user-id="user.id" />
    </div>
  </main>
</template>