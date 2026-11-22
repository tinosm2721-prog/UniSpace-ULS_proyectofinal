<script setup>
import Chatbot from './components/vue/Chatbot.vue'
</script>

<template>
  <div class="app-layout bg-slate-100 min-h-screen">
    <main class="app-content"><router-view /></main>
    <Chatbot />
  </div>
</template>

<style>
.app-layout { display: flex; min-height: 100vh; }
.app-content { flex: 1; overflow-y: auto; }
</style>
