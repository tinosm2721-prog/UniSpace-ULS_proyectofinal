import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: () => import('../pages/LoginPage.vue'), meta: { guest: true } },
  { path: '/register', component: () => import('../pages/RegisterPage.vue'), meta: { guest: true } },
  { path: '/dashboard', component: () => import('../pages/DashboardPage.vue') },
  { path: '/editarperfil', component: () => import('../pages/EditarPerfilPage.vue') },
  { path: '/mapa', component: () => import('../pages/MapaPage.vue') },
  { path: '/tour', component: () => import('../pages/TourPage.vue') },
  { path: '/objetos-perdidos', component: () => import('../pages/ObjetosPerdidosPage.vue') },
  { path: '/podcast/podcast', component: () => import('../pages/PodcastListPage.vue') },
  { path: '/podcast/:id', component: () => import('../pages/PodcastDetallePage.vue') },
  { path: '/publicaciones/publicaciones', component: () => import('../pages/PublicacionesListPage.vue') },
  { path: '/publicaciones/:id', component: () => import('../pages/PublicacionDetallePage.vue') },
  { path: '/usuario/meperfil', component: () => import('../pages/MiPerfilPage.vue') },
  { path: '/usuario/:id/usuario', component: () => import('../pages/UsuarioPage.vue') },
  { path: '/usuario/:id/podcast', component: () => import('../pages/UsuarioPodcastPage.vue') },
  { path: '/usuario/:id/publicaciones', component: () => import('../pages/UsuarioPublicacionesPage.vue') },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (!to.meta.guest && !token) return '/login'
  if (to.meta.guest && token) return '/dashboard'
})

export default router
