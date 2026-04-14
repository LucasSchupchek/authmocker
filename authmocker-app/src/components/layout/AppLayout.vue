<script setup lang="ts">
import { watch } from 'vue'
import { useTheme } from 'vuetify'
import { useAuthStore } from '../../stores/auth'
import { useThemeStore } from '../../stores/theme'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const themeStore = useThemeStore()
const router = useRouter()
const vuetifyTheme = useTheme()

// Sync store with Vuetify theme
vuetifyTheme.global.name.value = themeStore.isDark ? 'dark' : 'light'
watch(() => themeStore.isDark, (dark) => {
  vuetifyTheme.global.name.value = dark ? 'dark' : 'light'
})

async function handleSignOut() {
  await auth.signOut()
  router.push('/login')
}
</script>

<template>
  <v-navigation-drawer permanent rail-width="72" width="280">
    <div class="d-flex align-center ga-3 pa-4" style="border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))">
      <v-avatar color="primary" size="36" rounded="lg">
        <span class="text-body-1 font-weight-bold">AM</span>
      </v-avatar>
      <span class="text-h6 font-weight-bold">AuthMocker</span>
    </div>

    <v-list nav density="compact" class="mt-2">
      <v-list-item
        to="/"
        prepend-icon="mdi-view-dashboard"
        title="Dashboard"
        exact
      />
      <v-list-item
        to="/servers/create"
        prepend-icon="mdi-plus-circle"
        title="New Server"
      />
      <v-list-item
        to="/docs"
        prepend-icon="mdi-file-document"
        title="Docs"
      />
    </v-list>

    <template #append>
      <div class="pa-4" style="border-top: 1px solid rgba(var(--v-border-color), var(--v-border-opacity))">
        <div class="d-flex align-center justify-space-between">
          <span class="text-body-2 text-medium-emphasis text-truncate">{{ auth.user?.email }}</span>
          <v-btn
            icon="mdi-logout"
            size="small"
            variant="text"
            color="error"
            @click="handleSignOut"
            title="Sign out"
          />
        </div>
      </div>
    </template>
  </v-navigation-drawer>

  <v-app-bar flat density="compact">
    <v-spacer />
    <v-btn
      :icon="themeStore.isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'"
      variant="text"
      @click="themeStore.toggle()"
    />
  </v-app-bar>

  <v-main>
    <v-container>
      <slot />
    </v-container>
  </v-main>
</template>
