<script setup lang="ts">
import { useAuthStore } from './stores/auth'
import AppLayout from './components/layout/AppLayout.vue'

const auth = useAuthStore()
</script>

<template>
  <v-app>
    <div v-if="auth.loading" class="d-flex align-center justify-center" style="min-height: 100vh">
      <v-progress-circular indeterminate color="primary" size="48" />
    </div>
    <AppLayout v-else-if="auth.isAuthenticated">
      <RouterView />
    </AppLayout>
    <RouterView v-else />
  </v-app>
</template>
