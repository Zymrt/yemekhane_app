import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import MenuView from '../views/MenuView.vue'
import AdminView from '../views/AdminView.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: LoginView },
  { path: '/register', name: 'Register', component: RegisterView },
  { path: '/menu', name: 'Menu', component: MenuView, meta: { requiresAuth: true } },
  { path: '/admin', name: 'Admin', component: AdminView, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

// Oturum kontrolü
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('user')
  if (to.meta.requiresAuth && !isLoggedIn) {
    next('/login')
  } else {
    next()
  }
})

export default router