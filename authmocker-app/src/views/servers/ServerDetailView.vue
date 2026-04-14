<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useServersStore } from '../../stores/servers'
import EndpointList from '../../components/endpoints/EndpointList.vue'
import EndpointForm from '../../components/endpoints/EndpointForm.vue'
import CredentialList from '../../components/credentials/CredentialList.vue'
import CredentialForm from '../../components/credentials/CredentialForm.vue'
import RequestLogTable from '../../components/logs/RequestLogTable.vue'
import CodeSnippet from '../../components/ui/CodeSnippet.vue'

const route = useRoute()
const router = useRouter()
const store = useServersStore()
const activeTab = ref('endpoints')
const showEndpointForm = ref(false)
const showCredentialForm = ref(false)
const deleting = ref(false)

const serverId = computed(() => route.params.id as string)

onMounted(async () => {
  await store.fetchServer(serverId.value)
  await store.fetchEndpoints(serverId.value)
  await store.fetchCredentials(serverId.value)
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

function onTabChange(tab: string) {
  if (tab === 'logs') {
    loadLogs()
  } else if (tab === 'credentials') {
    store.fetchCredentials(serverId.value)
  }
}
</script>

<template>
  <div v-if="store.currentServer">
    <!-- Header -->
    <div class="d-flex align-start justify-space-between mb-6">
      <div>
        <v-btn variant="text" to="/" prepend-icon="mdi-arrow-left" size="small" class="mb-2">
          Back to Dashboard
        </v-btn>
        <div class="d-flex align-center ga-3">
          <h1 class="text-h4 font-weight-bold">{{ store.currentServer.name }}</h1>
          <v-chip
            size="small"
            :color="store.currentServer.is_active ? 'success' : 'grey'"
            variant="tonal"
          >
            {{ store.currentServer.is_active ? 'Active' : 'Inactive' }}
          </v-chip>
        </div>
        <code class="text-caption text-medium-emphasis mt-1 d-block">{{ store.currentServer.mock_url }}</code>
      </div>
      <div class="d-flex ga-2">
        <v-btn
          variant="tonal"
          :to="`/servers/${serverId}/edit`"
          size="small"
        >
          Edit
        </v-btn>
        <v-btn
          variant="tonal"
          color="error"
          size="small"
          :loading="deleting"
          @click="handleDelete"
        >
          Delete
        </v-btn>
      </div>
    </div>

    <!-- Tabs -->
    <v-tabs v-model="activeTab" class="mb-6" @update:model-value="onTabChange">
      <v-tab value="endpoints">Endpoints</v-tab>
      <v-tab value="credentials">Credentials</v-tab>
      <v-tab value="logs">Logs</v-tab>
      <v-tab value="usage">Usage</v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <!-- Endpoints Tab -->
      <v-window-item value="endpoints">
        <div class="d-flex align-center justify-space-between mb-4">
          <h2 class="text-h6">Endpoints</h2>
          <v-btn
            color="primary"
            size="small"
            @click="showEndpointForm = !showEndpointForm"
          >
            {{ showEndpointForm ? 'Cancel' : '+ Add Endpoint' }}
          </v-btn>
        </div>

        <EndpointForm
          v-if="showEndpointForm"
          :server-id="serverId"
          @created="showEndpointForm = false"
          class="mb-4"
        />

        <EndpointList :endpoints="store.endpoints" />
      </v-window-item>

      <!-- Credentials Tab -->
      <v-window-item value="credentials">
        <div class="d-flex align-center justify-space-between mb-4">
          <h2 class="text-h6">Credentials</h2>
          <v-btn
            color="primary"
            size="small"
            @click="showCredentialForm = !showCredentialForm"
          >
            {{ showCredentialForm ? 'Cancel' : '+ Add Credential' }}
          </v-btn>
        </div>

        <CredentialForm
          v-if="showCredentialForm"
          :server-id="serverId"
          :auth-type="store.currentServer.auth_type"
          @created="showCredentialForm = false"
          class="mb-4"
        />

        <CredentialList
          :credentials="store.credentials"
          :auth-type="store.currentServer.auth_type"
          :server-id="serverId"
        />
      </v-window-item>

      <!-- Logs Tab -->
      <v-window-item value="logs">
        <RequestLogTable :logs="store.logs" :server-id="serverId" />
      </v-window-item>

      <!-- Usage Tab -->
      <v-window-item value="usage">
        <CodeSnippet :server="store.currentServer" />
      </v-window-item>
    </v-window>
  </div>

  <v-progress-linear v-else-if="store.loading" indeterminate color="primary" />
</template>
