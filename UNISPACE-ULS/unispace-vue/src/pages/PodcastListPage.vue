<script setup>
import { ref, onMounted } from 'vue'
import { getMe } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'
import PodcastView from '@/components/podcast/PodcastView.vue'

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
  <div class="navbar-fixed"><Navbar v-bind="user" /></div>
  <aside class="sidebar-area"><Sidebar /></aside>

  <main class="pagina">
    <PodcastView :current-user-id="user.id" />
  </main>
</template>

<style scoped>
:global(body) { margin: 0; background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%); font-family: Inter, system-ui, sans-serif; }
.navbar-fixed { position: fixed; top: 0; width: 100%; z-index: 1000; background: rgba(255,255,255,.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(226,232,240,.7); }
.sidebar-area { position: fixed; top: 90px; left: 18px; width: 220px; z-index: 50; }
.pagina { max-width: 1000px; margin: 0 auto; padding: 100px 1rem 3rem 270px; }
@media (max-width: 900px) { .sidebar-area { display: none; } .pagina { padding-left: 1rem; } }
</style>
