<script setup lang="ts">
import { ref } from 'vue'
import { useServersStore } from '../../stores/servers'
import type { MockEndpoint } from '../../types'

defineProps<{ endpoints: MockEndpoint[] }>()

const store = useServersStore()
const deletingId = ref<string | null>(null)

const methodColors: Record<string, string> = {
  GET: 'bg-green-500/10 text-green-400',
  POST: 'bg-blue-500/10 text-blue-400',
  PUT: 'bg-yellow-500/10 text-yellow-400',
  PATCH: 'bg-orange-500/10 text-orange-400',
  DELETE: 'bg-red-500/10 text-red-400',
  HEAD: 'bg-purple-500/10 text-purple-400',
  OPTIONS: 'bg-gray-500/10 text-gray-400',
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
  <div v-if="endpoints.length === 0" class="text-center py-8 text-gray-500">
    No endpoints configured yet. Add one to get started.
  </div>

  <div v-else class="space-y-2">
    <div
      v-for="endpoint in endpoints"
      :key="endpoint.id"
      class="bg-gray-800 border border-gray-700 rounded-lg p-4 flex items-center justify-between"
    >
      <div class="flex items-center gap-3">
        <span
          :class="methodColors[endpoint.method] || 'bg-gray-500/10 text-gray-400'"
          class="px-2.5 py-1 text-xs font-bold rounded font-mono min-w-[60px] text-center"
        >
          {{ endpoint.method }}
        </span>
        <span class="text-white font-mono text-sm">/{{ endpoint.path }}</span>
        <span class="text-gray-500 text-xs">{{ endpoint.response_status }}</span>
        <span v-if="endpoint.delay_ms > 0" class="text-gray-500 text-xs">+{{ endpoint.delay_ms }}ms</span>
        <span v-if="!endpoint.is_active" class="text-yellow-500 text-xs">(inactive)</span>
      </div>
      <button
        @click="handleDelete(endpoint.id)"
        :disabled="deletingId === endpoint.id"
        class="text-gray-500 hover:text-red-400 transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>
