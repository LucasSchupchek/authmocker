<script setup lang="ts">
import { useAuthStore } from './stores/auth'
import AppLayout from './components/layout/AppLayout.vue'

const auth = useAuthStore()
</script>

<template>
  <div v-if="auth.loading" class="min-h-screen flex items-center justify-center bg-gray-900">
    <div class="text-white text-lg">Loading...</div>
  </div>
  <AppLayout v-else-if="auth.isAuthenticated">
    <RouterView />
  </AppLayout>
  <RouterView v-else />
</template>
