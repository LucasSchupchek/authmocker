<script setup lang="ts">
import type { MockServer } from '../../types'

defineProps<{ server: MockServer }>()

const authTypeColors: Record<string, string> = {
  basic_auth: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
  api_key: 'bg-green-500/10 text-green-400 border-green-500/20',
  jwt: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
  oauth2: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
  session: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
}
</script>

<template>
  <RouterLink
    :to="`/servers/${server.id}`"
    class="block bg-gray-800 border border-gray-700 rounded-xl p-5 hover:border-gray-600 transition-colors group"
  >
    <div class="flex items-start justify-between mb-3">
      <h3 class="text-white font-semibold group-hover:text-indigo-400 transition-colors">
        {{ server.name }}
      </h3>
      <span
        :class="server.is_active ? 'bg-green-500' : 'bg-gray-500'"
        class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1.5"
      />
    </div>

    <span
      :class="authTypeColors[server.auth_type] || 'bg-gray-500/10 text-gray-400'"
      class="inline-block px-2.5 py-1 text-xs font-medium rounded-full border mb-3"
    >
      {{ server.auth_type_label }}
    </span>

    <p v-if="server.description" class="text-gray-400 text-sm mb-3 line-clamp-2">
      {{ server.description }}
    </p>

    <div class="flex items-center justify-between text-xs text-gray-500">
      <span class="font-mono">/mock/{{ server.slug }}</span>
      <span>{{ server.endpoints_count ?? 0 }} endpoints</span>
    </div>
  </RouterLink>
</template>
