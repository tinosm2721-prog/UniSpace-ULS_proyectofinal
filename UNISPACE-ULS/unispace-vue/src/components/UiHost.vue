<script setup>
import { ui, responder } from '@/lib/ui.js'

const tipos = {
  success: 'alert-success',
  error: 'alert-error',
  info: 'alert-info',
  warning: 'alert-warning',
}
</script>

<template>
  <Teleport to="body">
    <div class="toast toast-top toast-end z-[100]">
      <div v-for="t in ui.toasts" :key="t.id" class="alert shadow-lg" :class="tipos[t.type] || 'alert-info'">
        <span>{{ t.message }}</span>
      </div>
    </div>

    <dialog class="modal z-[90]" :class="{ 'modal-open': ui.confirm }">
      <div v-if="ui.confirm" class="modal-box max-w-sm">
        <h3 class="font-bold text-lg">{{ ui.confirm.titulo }}</h3>
        <p class="py-3">{{ ui.confirm.message }}</p>
        <div class="modal-action">
          <button type="button" class="btn btn-ghost" @click="responder(false)">{{ ui.confirm.cancelar }}</button>
          <button type="button" class="btn" :class="ui.confirm.peligro ? 'btn-error' : 'btn-primary'" @click="responder(true)">
            {{ ui.confirm.aceptar }}
          </button>
        </div>
      </div>
      <div class="modal-backdrop" @click="responder(false)"></div>
    </dialog>
  </Teleport>
</template>