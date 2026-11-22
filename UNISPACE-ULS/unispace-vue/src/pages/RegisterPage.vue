<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '@/lib/auth'

const router = useRouter()

const form = ref({
  nombre: '',
  apellido: '',
  email: '',
  fecha_nacimiento: '',
  password: '',
  password_confirmation: '',
})
const loading = ref(false)
const error = ref('')
const success = ref('')

const strength = computed(() => {
  const p = form.value.password
  let s = 0
  if (p.length >= 6) s++
  if (p.length >= 10) s++
  if (/[a-z]/.test(p) && /[A-Z]/.test(p)) s++
  if (/\d/.test(p)) s++
  if (/[^a-zA-Z0-9]/.test(p)) s++
  return s
})
const strengthLabel = computed(() => {
  if (!form.value.password) return { text: '', cls: '', text_cls: '', pct: 0 }
  if (strength.value <= 2) return { text: 'Débil', cls: 'progress-error', text_cls: 'text-error', pct: 33 }
  if (strength.value <= 4) return { text: 'Media', cls: 'progress-warning', text_cls: 'text-warning', pct: 66 }
  return { text: 'Fuerte', cls: 'progress-success', text_cls: 'text-success', pct: 100 }
})
const confirmColor = computed(() => {
  if (!form.value.password_confirmation) return ''
  return form.value.password === form.value.password_confirmation ? 'input-success' : 'input-error'
})

async function onSubmit() {
  error.value = ''
  success.value = ''

  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Las contraseñas no coinciden'
    return
  }

  loading.value = true
  try {
    await register({ ...form.value })
    success.value = '✨ ¡Registro exitoso! Redirigiendo al dashboard...'
    setTimeout(() => router.push('/dashboard'), 1500)
  } catch (e) {
    error.value = e.message || 'Error al registrarse'
    loading.value = false
    setTimeout(() => (error.value = ''), 5000)
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-secondary to-accent">
    <div class="card bg-base-100 w-full max-w-lg shadow-2xl max-h-[95vh] overflow-y-auto">
      <div class="card-body gap-3">
        <div class="text-center">
          <div class="text-secondary flex justify-center">
            <svg width="64" height="64" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.78168 19.25H13.2183C13.7828 19.25 14.227 18.7817 14.1145 18.2285C13.804 16.7012 12.7897 14 9.5 14C6.21031 14 5.19605 16.7012 4.88549 18.2285C4.773 18.7817 5.21718 19.25 5.78168 19.25Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 14C17.8288 14 18.6802 16.1479 19.0239 17.696C19.2095 18.532 18.5333 19.25 17.6769 19.25H16.75"></path>
            <circle cx="9.5" cy="7.5" r="2.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.75 10.25C16.2688 10.25 17.25 9.01878 17.25 7.5C17.25 5.98122 16.2688 4.75 14.75 4.75"></path>
          </svg>
          </div>
          <h1 class="card-title justify-center text-3xl mt-2">Crear Cuenta</h1>
          <p class="text-base-content/60">Únete a nuestra comunidad</p>
        </div>

        <form class="flex flex-col gap-1" @submit.prevent="onSubmit">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-3">
          <fieldset class="fieldset">
            <legend class="fieldset-legend">Nombre</legend>
            <input v-model="form.nombre" type="text" id="nombre" name="nombre" class="input input-bordered w-full" placeholder="Tu nombre" required autocomplete="given-name" @input="error = ''" />
          </fieldset>
          <fieldset class="fieldset">
            <legend class="fieldset-legend">Apellido</legend>
            <input v-model="form.apellido" type="text" id="apellido" name="apellido" class="input input-bordered w-full" placeholder="Tu apellido" required autocomplete="family-name" @input="error = ''" />
          </fieldset>
          </div>
          <fieldset class="fieldset">
            <legend class="fieldset-legend">Email</legend>
            <input v-model="form.email" type="email" id="email" name="email" class="input input-bordered w-full" placeholder="tu@email.com" required autocomplete="email" @input="error = ''" />
          </fieldset>
          <fieldset class="fieldset">
            <legend class="fieldset-legend">Fecha de Nacimiento</legend>
            <input v-model="form.fecha_nacimiento" type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input input-bordered w-full" required autocomplete="bday" @input="error = ''" />
          </fieldset>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Contraseña</legend>
            <input v-model="form.password" type="password" id="password" name="password" class="input input-bordered w-full" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password" @input="error = ''" />
            <div v-if="form.password" class="flex items-center gap-3 mt-1">
              <progress class="progress w-full" :class="strengthLabel.cls" :value="strengthLabel.pct" max="100"></progress>
              <small class="font-semibold" :class="strengthLabel.text_cls">{{ strengthLabel.text }}</small>
            </div>
          </fieldset>

          <fieldset class="fieldset">
            <legend class="fieldset-legend">Confirmar Contraseña</legend>
            <input v-model="form.password_confirmation" type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full" :class="confirmColor" placeholder="Repite tu contraseña" required minlength="6" autocomplete="new-password" @input="error = ''" />
          </fieldset>

          <button type="submit" class="btn btn-secondary w-full mt-3" :disabled="loading">
            <span v-if="!loading">Crear Cuenta</span>
            <span v-else class="flex items-center gap-2">
              <span class="loading loading-spinner loading-sm"></span>
              Creando cuenta...
            </span>
          </button>
        </form>

        <div v-if="error" role="alert" class="alert alert-error"><span>{{ error }}</span></div>
        <div v-if="success" role="alert" class="alert alert-success"><span>{{ success }}</span></div>

        <p class="text-center text-sm">
          ¿Ya tienes cuenta?
          <router-link to="/login" class="link link-secondary font-semibold">Inicia sesión</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
