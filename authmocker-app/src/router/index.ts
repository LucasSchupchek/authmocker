import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/auth/LoginView.vue'),
      meta: { guest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/auth/RegisterView.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      name: 'dashboard',
      component: () => import('../views/dashboard/DashboardView.vue'),
      meta: { auth: true },
    },
    {
      path: '/servers/create',
      name: 'server-create',
      component: () => import('../views/servers/ServerCreateView.vue'),
      meta: { auth: true },
    },
    {
      path: '/servers/:id',
      name: 'server-detail',
      component: () => import('../views/servers/ServerDetailView.vue'),
      meta: { auth: true },
    },
    {
      path: '/servers/:id/edit',
      name: 'server-edit',
      component: () => import('../views/servers/ServerEditView.vue'),
      meta: { auth: true },
    },
    {
      path: '/docs',
      name: 'docs',
      component: () => import('../views/docs/DocsView.vue'),
      meta: { auth: true },
    },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (auth.loading) {
    await new Promise<void>((resolve) => {
      const unwatch = auth.$subscribe(() => {
        if (!auth.loading) { unwatch(); resolve() }
      })
    })
  }
  if (to.meta.auth && !auth.isAuthenticated) return '/login'
  if (to.meta.guest && auth.isAuthenticated) return '/'
})

export default router
