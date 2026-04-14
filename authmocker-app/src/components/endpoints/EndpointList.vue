<script setup lang="ts">
import { ref } from 'vue'
import { useServersStore } from '../../stores/servers'
import type { MockEndpoint } from '../../types'

defineProps<{ endpoints: MockEndpoint[] }>()

const store = useServersStore()
const deletingId = ref<string | null>(null)

const methodColors: Record<string, string> = {
  GET: 'success',
  POST: 'info',
  PUT: 'warning',
  PATCH: 'orange',
  DELETE: 'error',
  HEAD: 'purple',
  OPTIONS: 'grey',
}

async function handleDelete(id: string) {
  if (!confirm('Delete this endpoint?')) return
  deletingId.value = id
  try {
    await store.deleteEndpoint(id)
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <div v-if="endpoints.length === 0" class="text-center py-8 text-medium-emphasis">
    No endpoints configured yet. Add one to get started.
  </div>

  <v-list v-else lines="one">
    <v-list-item
      v-for="endpoint in endpoints"
      :key="endpoint.id"
      class="px-4"
    >
      <template #prepend>
        <v-chip
          :color="methodColors[endpoint.method] || 'grey'"
          variant="tonal"
          size="small"
          label
          class="font-weight-bold mr-3"
          style="font-family: monospace; min-width: 70px; justify-content: center"
        >
          {{ endpoint.method }}
        </v-chip>
      </template>

      <v-list-item-title style="font-family: monospace" class="text-body-2">
        /{{ endpoint.path }}
      </v-list-item-title>

      <v-list-item-subtitle>
        Status {{ endpoint.response_status }}
        <span v-if="endpoint.delay_ms > 0"> &middot; +{{ endpoint.delay_ms }}ms</span>
        <v-chip v-if="!endpoint.is_active" size="x-small" color="warning" variant="tonal" class="ml-2">inactive</v-chip>
      </v-list-item-subtitle>

      <template #append>
        <v-btn
          icon="mdi-delete"
          size="small"
          variant="text"
          color="error"
          :loading="deletingId === endpoint.id"
          @click="handleDelete(endpoint.id)"
        />
      </template>
    </v-list-item>
  </v-list>
</template>
