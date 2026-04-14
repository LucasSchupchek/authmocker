<script setup lang="ts">
import type { MockServer } from '../../types'

defineProps<{ server: MockServer }>()

const authTypeColors: Record<string, string> = {
  basic_auth: 'blue',
  api_key: 'green',
  jwt: 'purple',
  oauth2: 'orange',
  session: 'cyan',
}
</script>

<template>
  <v-card hover :to="'/servers/' + server.id">
    <v-card-title class="d-flex align-center justify-space-between">
      <span class="text-truncate">{{ server.name }}</span>
      <v-chip
        size="x-small"
        :color="server.is_active ? 'success' : 'grey'"
        variant="flat"
      >
        {{ server.is_active ? 'Active' : 'Inactive' }}
      </v-chip>
    </v-card-title>

    <v-card-text>
      <v-chip
        size="small"
        :color="authTypeColors[server.auth_type] || 'grey'"
        variant="tonal"
        class="mb-3"
      >
        {{ server.auth_type_label }}
      </v-chip>

      <p v-if="server.description" class="text-body-2 text-medium-emphasis mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
        {{ server.description }}
      </p>
    </v-card-text>

    <v-card-actions class="d-flex justify-space-between px-4 pb-3">
      <code class="text-caption">/mock/{{ server.slug }}</code>
      <span class="text-caption text-medium-emphasis">{{ server.endpoints_count ?? 0 }} endpoints</span>
    </v-card-actions>
  </v-card>
</template>
