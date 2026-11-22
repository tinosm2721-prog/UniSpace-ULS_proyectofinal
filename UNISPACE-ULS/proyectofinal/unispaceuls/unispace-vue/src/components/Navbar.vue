<script setup>
defineProps({
  id: { type: [Number, String], default: null },
  nombre: { type: String, default: '' },
  apellido: { type: String, default: '' },
  perfil: { type: Object, default: () => ({}) },
})
</script>

<template>
  <nav class="user-navbar">
    <div class="navbar-content">
      <div class="navbar-left">
        <router-link to="/dashboard" class="logo">UniSpace - ULS</router-link>
      </div>

      <div class="navbar-right">
        <div class="user-info">
          <span class="user-name">{{ nombre ?? '' }}</span>

          <router-link v-if="id" to="/usuario/meperfil" class="user-link">
            <img
              v-if="perfil?.foto_perfil"
              :src="`http://127.0.0.1:8000/storage/${perfil.foto_perfil}`"
              alt="Mi foto"
              class="navbar-avatar"
            />
            <div v-else class="navbar-avatar placeholder">
              {{ nombre?.charAt(0) ?? 'U' }}
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.user-navbar{
  width:100%;
  height:72px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(15, 14, 14, 0.82);
  backdrop-filter: blur(18px);
  border-bottom: 1px solid rgba(226,232,240,.8);
}
.navbar-content{
  width:100%;
  max-width:1500px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding: 0 1.5rem;
}
.logo{
  font-size:2.3rem;
  font-weight:800;
  text-decoration:none;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  -webkit-background-clip:text;
  color:transparent;
}
.navbar-right{
  display:flex;
  align-items:center;
  gap:1rem;
}
.user-info{
  width:100%;
  display:flex;
  align-items:center;
  gap:.9rem;
}
.user-name{
  font-size:30px;
  font-weight:600;
  color:#93a7d6;
}
.user-link{
  text-decoration:none;
}
.navbar-avatar{
  width:42px;
  height:42px;
  border-radius:50%;
  object-fit:cover;
  border: 2px solid #2563eb;
  box-shadow: 0 4px 14px rgba(37,99,235,.2);
}
.placeholder{
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:700;
  color:white;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
}
@media (max-width:768px){
  .navbar-content{ padding:0 1rem; }
  .user-name{ display:none; }
}
</style>
