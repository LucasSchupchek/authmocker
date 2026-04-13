<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useServersStore } from '../../stores/servers'
import EndpointList from '../../components/endpoints/EndpointList.vue'
import EndpointForm from '../../components/endpoints/EndpointForm.vue'
import RequestLogTable from '../../components/logs/RequestLogTable.vue'
import CodeSnippet from '../../components/ui/CodeSnippet.vue'

const route = useRoute()
const router = useRouter()
const store = useServersStore()
const activeTab = ref<'endpoints' | 'logs' | 'usage'>('endpoints')
const showEndpointForm = ref(false)
const deleting = ref(false)

const serverId = computed(() => route.params.id as string)

onMounted(async () => {
  await store.fetchServer(serverId.value)
  await store.fetchEndpoints(serverId.value)
})

async function handleDelete() {
  if (!confirm('Are you sure you want to delete this server? This cannot be undone.')) return
  deleting.value = true
  try {
    await store.deleteServer(serverId.value)
    router.push('/')
  } finally {
    deleting.value = false
  }
}

async function loadLogs() {
  activeTab.value = 'logs'
  await store.fetchLogs(serverId.value)
}

function switchTab(tab: 'endpoints' | 'logs' | 'usage') {
  if (tab === 'logs') {
    loadLogs()
  } else {
    activeTab.value = tab
  }
}
</script>

<template>
  <div v-if="store.currentServer">
    <!-- Header -->
    <div class="flex items-start justify-between mb-6">
      <div>
        <RouterLink to="/" class="text-gray-400 hover:text-white text-sm transition-colors">
          &larr; Back to Dashboard
        </RouterLink>
        <div class="flex items-center gap-3 mt-2">
          <h1 class="text-2xl font-bold text-white">{{ store.currentServer.name }}</h1>
          <span
            :class="store.currentServer.is_active ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400'"
            class="px-2.5 py-0.5 text-xs font-medium rounded-full"
          >
            {{ store.currentServer.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        <p class="text-gray-400 mt-1 font-mono text-sm">{{ store.currentServer.mock_url }}</p>
      </div>
      <div class="flex gap-2">
        <RouterLink
          :to="`/servers/${serverId}/edit`"
          class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm"
        >
          Edit
        </RouterLink>
        <button
          @click="handleDelete"
          :disabled="deleting"
          class="px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors text-sm"
        >
          Delete
        </button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-6 bg-gray-800 rounded-lg p-1 w-fit">
      <button
        v-for="tab in (['endpoints', 'logs', 'usage'] as const)"
        :key="tab"
        @click="switchTab(tab)"
        :class="[
          'px-4 py-2 rounded-md text-sm font-medium transition-colors capitalize',
          activeTab === tab ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white'
        ]"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Endpoints Tab -->
    <div v-if="activeTab === 'endpoints'">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white">Endpoints</h2>
        <button
          @click="showEndpointForm = !showEndpointForm"
          class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white text-sm rounded-lg transition-colors"
        >
          {{ showEndpointForm ? 'Cancel' : '+ Add Endpoint' }}
        </button>
      </div>

      <EndpointForm
        v-if="showEndpointForm"
        :server-id="serverId"
        @created="showEndpointForm = false"
        class="mb-4"
      />

      <EndpointList :endpoints="store.endpoints" />
    </div>

    <!-- Logs Tab -->
    <div v-else-if="activeTab === 'logs'">
      <RequestLogTable :logs="store.logs" :server-id="serverId" />
    </div>

    <!-- Usage Tab -->
    <div v-else-if="activeTab === 'usage'">
      <CodeSnippet :server="store.currentServer" />
    </div>
  </div>

  <div v-else-if="store.loading" class="text-gray-400">Loading...</div>
</template>
