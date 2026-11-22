<script setup>
import { ref, onMounted } from 'vue'
import { getMe } from '@/lib/auth.js'
import Navbar from '@/components/Navbar.vue'
import Sidebar from '@/components/Sidebar.vue'

defineProps({
  ancho: { type: String, default: 'max-w-3xl' },
})

const user = ref({})

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
    <div class="mx-auto" :class="ancho">
      <slot />
    </div>
  </main>
</template>