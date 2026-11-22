<script setup>
const portal = [
  { href: 'https://academica.uls.edu.sv/sgestu/', label: 'SGE Estudiante' },
  { href: 'http://campus.uls.edu.sv/login/index.php', label: 'Campus virtual' },
  { href: 'http://academica.uls.edu.sv/gde', label: 'Guía estudiantil' },
  { href: 'http://biblioteca.uls.edu.sv/', label: 'Biblioteca ULS' },
]

const anio = new Date().getFullYear()
</script>

<template>
  <footer class="bg-base-100 border-t border-base-300 lg:pl-64">
    <div class="footer sm:footer-horizontal text-base-content p-10">
      <nav>
        <h6 class="footer-title">Portal ULS</h6>
        <a
          v-for="p in portal"
          :key="p.href"
          :href="p.href"
          target="_blank"
          rel="noopener"
          class="link link-hover"
        >
          {{ p.label }} ↗
        </a>
      </nav>

      <nav>
        <h6 class="footer-title">UniSpace</h6>
        <router-link to="/mapa" class="link link-hover">Mapa del campus</router-link>
        <router-link to="/como-llegar" class="link link-hover">Cómo llegar</router-link>
        <router-link to="/objetos-perdidos" class="link link-hover">Objetos perdidos</router-link>
      </nav>
    </div>

    <div class="border-t border-base-300 px-10 py-4 text-center text-sm text-base-content/60">
      © {{ anio }} UniSpace · Universidad Luterana Salvadoreña
    </div>
  </footer>
</template>