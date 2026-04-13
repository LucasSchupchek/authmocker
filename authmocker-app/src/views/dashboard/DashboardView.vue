<script setup lang="ts">
import { onMounted } from 'vue'
import { useServersStore } from '../../stores/servers'
import ServerCard from '../../components/servers/ServerCard.vue'

const store = useServersStore()

onMounted(() => {
  store.fetchServers()
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-white">Mock Servers</h1>
        <p class="text-gray-400 mt-1">Manage your authentication mock servers</p>
      </div>
      <RouterLink
        to="/servers/create"
        class="px-4 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        New Server
      </RouterLink>
    </div>

    <div v-if="store.loading" class="text-gray-400">Loading servers...</div>

    <div v-else-if="store.servers.length === 0" class="text-center py-16">
      <div class="w-16 h-16 bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-300">No mock servers yet</h3>
      <p class="text-gray-500 mt-1">Create your first mock authentication server to get started.</p>
      <RouterLink
        to="/servers/create"
        class="inline-block mt-4 px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white font-medium rounded-lg transition-colors"
      >
        Create Server
      </RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <ServerCard
        v-for="server in store.servers"
        :key="server.id"
        :server="server"
      />
    </div>
  </div>
</template>
