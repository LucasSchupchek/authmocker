import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

interface AuthUser {
  id: string
  name: string
  email: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const accessToken = ref<string | null>(localStorage.getItem('access_token'))
  const refreshToken = ref<string | null>(localStorage.getItem('refresh_token'))
  const loading = ref(true)

  const isAuthenticated = computed(() => !!accessToken.value)

  async function initialize() {
    loading.value = true
    if (accessToken.value) {
      try {
        await fetchUser()
      } catch {
        await tryRefresh()
      }
    }
    loading.value = false
  }

  async function signIn(email: string, password: string) {
    const { data } = await api.post('/auth/login', { email, password })
    setTokens(data.access_token, data.refresh_token)
    user.value = data.user
  }

  async function signUp(name: string, email: string, password: string, passwordConfirmation: string) {
    await api.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })
  }

  async function signOut() {
    clearTokens()
  }

  async function fetchUser() {
    const { data } = await api.get('/auth/me')
    user.value = data.user
  }

  async function tryRefresh() {
    if (!refreshToken.value) {
      clearTokens()
      return
    }
    try {
      const { data } = await api.post('/auth/refresh', {
        refresh_token: refreshToken.value,
      })
      setTokens(data.access_token, data.refresh_token)
      await fetchUser()
    } catch {
      clearTokens()
    }
  }

  function setTokens(access: string, refresh: string) {
    accessToken.value = access
    refreshToken.value = refresh
    localStorage.setItem('access_token', access)
    localStorage.setItem('refresh_token', refresh)
  }

  function clearTokens() {
    accessToken.value = null
    refreshToken.value = null
    user.value = null
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
  }

  return {
    user, accessToken, loading, isAuthenticated,
    initialize, signIn, signUp, signOut, tryRefresh,
  }
})
