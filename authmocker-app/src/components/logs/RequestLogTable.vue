<script setup lang="ts">
import { ref } from 'vue'
import { useServersStore } from '../../stores/servers'
import type { RequestLog } from '../../types'

const props = defineProps<{ logs: RequestLog[]; serverId: string }>()

const store = useServersStore()
const refreshing = ref(false)
const clearing = ref(false)
const expanded = ref<string[]>([])

const methodColors: Record<string, string> = {
  GET: 'success',
  POST: 'info',
  PUT: 'warning',
  PATCH: 'orange',
  DELETE: 'error',
  HEAD: 'purple',
  OPTIONS: 'grey',
}

const authColors: Record<string, string> = {
  success: 'success',
  failed: 'error',
  skipped: 'grey',
}

const headers = [
  { title: 'Time', key: 'created_at', width: 180 },
  { title: 'Method', key: 'method', width: 100 },
  { title: 'Path', key: 'path' },
  { title: 'Auth', key: 'auth_status', width: 110 },
  { title: 'Credential', key: 'credential_label', width: 160 },
  { title: 'Status', key: 'response_status', width: 90 },
  { title: '', key: 'data-table-expand', width: 48 },
]

function statusColor(status: number) {
  if (status >= 500) return 'error'
  if (status >= 400) return 'warning'
  return 'success'
}

function formatDate(value: string) {
  return value ? new Date(value).toLocaleString() : ''
}

function pretty(value: unknown) {
  return JSON.stringify(value ?? {}, null, 2)
}

function hasContent(value: unknown) {
  return value != null && Object.keys(value as object).length > 0
}

async function refresh() {
  refreshing.value = true
  try {
    await store.fetchLogs(props.serverId)
  } finally {
    refreshing.value = false
  }
}

async function clear() {
  if (!confirm('Clear all request logs for this server?')) return
  clearing.value = true
  try {
    await store.clearLogs(props.serverId)
  } finally {
    clearing.value = false
  }
}
</script>

<template>
  <div>
    <div class="d-flex align-center px-4 py-3">
      <span class="text-body-2 text-medium-emphasis">{{ logs.length }} request(s)</span>
      <v-spacer />
      <v-btn
        prepend-icon="mdi-refresh"
        variant="text"
        size="small"
        :loading="refreshing"
        @click="refresh"
      >
        Refresh
      </v-btn>
      <v-btn
        prepend-icon="mdi-delete-sweep"
        variant="text"
        size="small"
        color="error"
        :disabled="logs.length === 0"
        :loading="clearing"
        @click="clear"
      >
        Clear
      </v-btn>
    </div>

    <div v-if="logs.length === 0" class="text-center py-8 text-medium-emphasis">
      No requests logged yet. Call one of this server's mock URLs to see it here.
    </div>

    <v-data-table
      v-else
      v-model:expanded="expanded"
      :headers="headers"
      :items="logs"
      item-value="id"
      density="compact"
      show-expand
      :items-per-page="25"
    >
      <template #item.created_at="{ item }">
        <span class="text-caption">{{ formatDate(item.created_at) }}</span>
      </template>

      <template #item.method="{ item }">
        <v-chip
          :color="methodColors[item.method] || 'grey'"
          variant="tonal"
          size="small"
          label
          class="font-weight-bold"
          style="font-family: monospace"
        >
          {{ item.method }}
        </v-chip>
      </template>

      <template #item.path="{ item }">
        <span style="font-family: monospace" class="text-body-2">/{{ item.path }}</span>
      </template>

      <template #item.auth_status="{ item }">
        <v-chip :color="authColors[item.auth_status] || 'grey'" variant="tonal" size="small">
          {{ item.auth_status }}
        </v-chip>
      </template>

      <template #item.credential_label="{ item }">
        <span class="text-body-2">{{ item.credential_label || '—' }}</span>
      </template>

      <template #item.response_status="{ item }">
        <v-chip :color="statusColor(item.response_status)" variant="tonal" size="small" label>
          {{ item.response_status }}
        </v-chip>
      </template>

      <template #expanded-row="{ columns, item }">
        <tr>
          <td :colspan="columns.length" class="py-3">
            <div class="text-caption text-medium-emphasis mb-2">IP: {{ item.ip || '—' }}</div>
            <v-row dense>
              <v-col cols="12" md="4">
                <div class="text-subtitle-2 mb-1">Headers</div>
                <pre class="log-pre">{{ pretty(item.headers) }}</pre>
              </v-col>
              <v-col cols="12" md="4">
                <div class="text-subtitle-2 mb-1">Body</div>
                <pre v-if="hasContent(item.body)" class="log-pre">{{ pretty(item.body) }}</pre>
                <div v-else class="text-caption text-medium-emphasis">empty</div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="text-subtitle-2 mb-1">Query params</div>
                <pre v-if="hasContent(item.query_params)" class="log-pre">{{ pretty(item.query_params) }}</pre>
                <div v-else class="text-caption text-medium-emphasis">empty</div>
              </v-col>
            </v-row>
          </td>
        </tr>
      </template>
    </v-data-table>
  </div>
</template>

<style scoped>
.log-pre {
  font-family: monospace;
  font-size: 12px;
  white-space: pre-wrap;
  word-break: break-all;
  background: rgba(var(--v-theme-on-surface), 0.05);
  border-radius: 4px;
  padding: 8px;
  max-height: 300px;
  overflow: auto;
}
</style>
