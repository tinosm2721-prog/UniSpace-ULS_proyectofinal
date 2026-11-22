<script setup>
import { ref } from "vue";
import { logout } from "../lib/auth.js";

const saliendo = ref(false);

async function salir() {
  if (saliendo.value) return;
  saliendo.value = true;
  try {
    await logout();
  } finally {
    window.location.href = "/login";
  }
}
</script>

<template>
  <button type="button" class="btn btn-error btn-outline btn-sm" :disabled="saliendo" @click="salir">
    <span v-if="saliendo" class="loading loading-spinner loading-xs"></span>
    {{ saliendo ? "Saliendo..." : "Salir" }}
  </button>
</template>