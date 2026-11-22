<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '@/lib/auth'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

async function onSubmit() {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    await login(email.value, password.value)
    success.value = '¡Inicio de sesión exitoso! Redirigiendo...'
    setTimeout(() => router.push('/dashboard'), 1000)
  } catch (e) {
    error.value = e.message || 'Error al iniciar sesión'
    loading.value = false
    setTimeout(() => (error.value = ''), 5000)
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-primary to-secondary">
    <div class="card bg-base-100 w-full max-w-md shadow-2xl">
      <div class="card-body gap-4">
        <div class="text-center">
          <div class="text-primary flex justify-center">
            <svg width="64" height="64" fill="none" viewBox="0 0 24 24">
              <circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12.25 19.25H6.94953C5.77004 19.25 4.88989 18.2103 5.49085 17.1954C6.36247 15.7234 8.23935 14 12.25 14C16.2606 14 18.1375 15.7234 19.0091 17.1954C19.6101 18.2103 18.73 19.25 17.5505 19.25H16.75"></path>
            </svg>
          </div>
          <h1 class="card-title justify-center text-3xl mt-2">Iniciar Sesión</h1>
          <p class="text-base-content/60">Bienvenido de nuevo</p>
        </div>

        <form class="flex flex-col gap-3" @submit.prevent="onSubmit">
          <label class="input input-bordered flex items-center gap-2 w-full">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" class="opacity-60">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.75 7.75C4.75 6.64543 5.64543 5.75 6.75 5.75H17.25C18.3546 5.75 19.25 6.64543 19.25 7.75V16.25C19.25 17.3546 18.3546 18.25 17.25 18.25H6.75C5.64543 18.25 4.75 17.3546 4.75 16.25V7.75Z"></path>
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.5 6.5L12 12.25L18.5 6.5"></path>
            </svg>
            <input v-model="email" type="email" id="email" name="email" class="grow" placeholder="tu@email.com" required autocomplete="email" @input="error = ''" />
          </label>

          <label class="input input-bordered flex items-center gap-2 w-full">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" class="opacity-60">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.75 11.75C5.75 11.1977 6.19772 10.75 6.75 10.75H17.25C17.8023 10.75 18.25 11.1977 18.25 11.75V17.25C18.25 18.3546 17.3546 19.25 16.25 19.25H7.75C6.64543 19.25 5.75 18.3546 5.75 17.25V11.75Z"></path>
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.75 10.5V10.3427C7.75 8.78147 7.65607 7.04125 8.74646 5.9239C9.36829 5.2867 10.3745 4.75 12 4.75C13.6255 4.75 14.6317 5.2867 15.2535 5.9239C16.3439 7.04125 16.25 8.78147 16.25 10.3427V10.5"></path>
            </svg>
            <input v-model="password" type="password" id="password" name="password" class="grow" placeholder="••••••••" required autocomplete="current-password" @input="error = ''" />
          </label>

          <label class="label cursor-pointer justify-start gap-2">
            <input type="checkbox" name="remember" class="checkbox checkbox-sm checkbox-primary" />
            <span class="label-text">Recordarme</span>
          </label>

          <button type="submit" class="btn btn-primary w-full" :disabled="loading">
            <span v-if="!loading">Iniciar Sesión</span>
            <span v-else class="flex items-center gap-2">
              <span class="loading loading-spinner loading-sm"></span>
              Cargando...
            </span>
          </button>
        </form>

        <div v-if="error" role="alert" class="alert alert-error"><span>{{ error }}</span></div>
        <div v-if="success" role="alert" class="alert alert-success"><span>{{ success }}</span></div>

        <p class="text-center text-sm">
          ¿No tienes cuenta?
          <router-link to="/register" class="link link-primary font-semibold">Regístrate aquí</router-link>
        </p>
      </div>
    </div>
  </div>
</template>
