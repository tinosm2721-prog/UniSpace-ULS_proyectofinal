import { reactive } from 'vue'

export const ui = reactive({ toasts: [], confirm: null })
let nextId = 1

export function notify(message, type = 'success', ms = 3000) {
  const id = nextId++
  ui.toasts.push({ id, message, type })
  setTimeout(() => {
    ui.toasts = ui.toasts.filter((t) => t.id !== id)
  }, ms)
}

export function confirmar(message, opts = {}) {
  return new Promise((resolve) => {
    ui.confirm = {
      message,
      titulo: opts.titulo || 'Confirmar',
      aceptar: opts.aceptar || 'Aceptar',
      cancelar: opts.cancelar || 'Cancelar',
      peligro: opts.peligro ?? false,
      resolve,
    }
  })
}

export function responder(valor) {
  if (!ui.confirm) return
  ui.confirm.resolve(valor)
  ui.confirm = null
}