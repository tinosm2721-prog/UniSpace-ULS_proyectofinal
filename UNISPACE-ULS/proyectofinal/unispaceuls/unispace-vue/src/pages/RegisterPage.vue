<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '@/lib/auth'
import '@/styles/register.css'

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
  if (!form.value.password) return { text: '', cls: '', color: '' }
  if (strength.value <= 2) return { text: 'Débil', cls: 'weak', color: '#fc8181' }
  if (strength.value <= 4) return { text: 'Media', cls: 'medium', color: '#f6ad55' }
  return { text: 'Fuerte', cls: 'strong', color: '#68d391' }
})
const confirmColor = computed(() => {
  if (!form.value.password_confirmation) return ''
  return form.value.password === form.value.password_confirmation ? '#68d391' : '#fc8181'
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
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <div class="register-icon">
          <svg width="64" height="64" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.78168 19.25H13.2183C13.7828 19.25 14.227 18.7817 14.1145 18.2285C13.804 16.7012 12.7897 14 9.5 14C6.21031 14 5.19605 16.7012 4.88549 18.2285C4.773 18.7817 5.21718 19.25 5.78168 19.25Z"></path>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 14C17.8288 14 18.6802 16.1479 19.0239 17.696C19.2095 18.532 18.5333 19.25 17.6769 19.25H16.75"></path>
            <circle cx="9.5" cy="7.5" r="2.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></circle>
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.75 10.25C16.2688 10.25 17.25 9.01878 17.25 7.5C17.25 5.98122 16.2688 4.75 14.75 4.75"></path>
          </svg>
        </div>
        <h1>Crear Cuenta</h1>
        <p class="subtitle">Únete a nuestra comunidad</p>
      </div>

      <form @submit.prevent="onSubmit">
        <div class="form-row">
          <div class="form-group">
            <label for="nombre">Nombre</label>
            <input v-model="form.nombre" type="text" id="nombre" name="nombre" placeholder="Tu nombre" required autocomplete="given-name" @input="error = ''" />
          </div>
          <div class="form-group">
            <label for="apellido">Apellido</label>
            <input v-model="form.apellido" type="text" id="apellido" name="apellido" placeholder="Tu apellido" required autocomplete="family-name" @input="error = ''" />
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input v-model="form.email" type="email" id="email" name="email" placeholder="tu@email.com" required autocomplete="email" @input="error = ''" />
        </div>

        <div class="form-group">
          <label for="fecha_nacimiento">Fecha de Nacimiento</label>
          <input v-model="form.fecha_nacimiento" type="date" id="fecha_nacimiento" name="fecha_nacimiento" required autocomplete="bday" @input="error = ''" />
        </div>

        <div class="form-group">
          <label for="password">Contraseña</label>
          <input v-model="form.password" type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6" autocomplete="new-password" @input="error = ''" />
          <div v-if="form.password" class="password-strength">
            <div class="strength-meter">
              <div class="strength-meter-fill" :class="strengthLabel.cls"></div>
            </div>
            <small :style="{ color: strengthLabel.color }">{{ strengthLabel.text }}</small>
          </div>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmar Contraseña</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            placeholder="Repite tu contraseña"
            required
            minlength="6"
            autocomplete="new-password"
            :style="{ borderColor: confirmColor }"
            @input="error = ''"
          />
        </div>

        <button type="submit" class="btn-register" :disabled="loading">
          <span v-if="!loading">Crear Cuenta</span>
          <span v-else>
            <svg class="spinner" viewBox="0 0 50 50" width="20" height="20">
              <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
            </svg>
            Creando cuenta...
          </span>
        </button>
      </form>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-if="success" class="alert alert-success">{{ success }}</div>

      <div class="register-footer">
        <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión</router-link></p>
      </div>
    </div>
  </div>
</template>
