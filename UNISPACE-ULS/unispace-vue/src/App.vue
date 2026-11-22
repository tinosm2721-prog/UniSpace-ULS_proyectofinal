<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Chatbot from './components/vue/Chatbot.vue'
import AppFooter from '@/components/AppFooter.vue'
import UiHost from '@/components/UiHost.vue'

const route = useRoute()
const sinFooter = computed(() => ['/login', '/register'].includes(route.path))
</script>

<template>
  <div class="flex flex-col min-h-screen bg-base-200">
    <main class="flex-1"><router-view /></main>
    <AppFooter v-if="!sinFooter" />
    <Chatbot v-if="route.path !== '/login'" />
    <UiHost />
  </div>
</template>