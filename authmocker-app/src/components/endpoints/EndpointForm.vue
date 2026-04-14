<script setup lang="ts">
import { ref } from 'vue'
import { useServersStore } from '../../stores/servers'

const props = defineProps<{ serverId: string }>()
const emit = defineEmits<{ (e: 'created'): void }>()

const store = useServersStore()

const method = ref('GET')
const path = ref('')
const responseStatus = ref(200)
const responseBody = ref('{\n  "message": "Hello from AuthMocker"\n}')
const delayMs = ref(0)
const error = ref('')
const loading = ref(false)

const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']

async function handleSubmit() {
  error.value = ''
  loading.value = true

  let parsedBody = null
  try {
    parsedBody = responseBody.value.trim() ? JSON.parse(responseBody.value) : null
  } catch {
    error.value = 'Response body must be valid JSON'
    loading.value = false
    return
  }

  try {
    await store.createEndpoint(props.serverId, {
      method: method.value,
      path: path.value,
      response_status: responseStatus.value,
      response_body: parsedBody,
      delay_ms: delayMs.value,
    })
    path.value = ''
    responseBody.value = '{\n  "message": "Hello from AuthMocker"\n}'
    responseStatus.value = 200
    delayMs.value = 0
    emit('created')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to create endpoint'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <v-card class="mb-4">
    <v-card-text>
      <v-form @submit.prevent="handleSubmit">
        <v-alert v-if="error" type="error" variant="tonal" density="compact" class="mb-4">
          {{ error }}
        </v-alert>

        <v-row>
          <v-col cols="3">
            <v-select v-model="method" :items="methods" label="Method" />
          </v-col>
          <v-col cols="5">
            <v-text-field v-model="path" label="Path" placeholder="users" required />
          </v-col>
          <v-col cols="2">
            <v-text-field v-model.number="responseStatus" label="Status" type="number" min="100" max="599" />
          </v-col>
          <v-col cols="2">
            <v-text-field v-model.number="delayMs" label="Delay (ms)" type="number" min="0" max="30000" />
          </v-col>
        </v-row>

        <v-textarea
          v-model="responseBody"
          label="Response Body (JSON)"
          rows="4"
          style="font-family: monospace"
          class="mb-4"
        />

        <v-btn type="submit" color="primary" :loading="loading" :disabled="loading || !path">
          Add Endpoint
        </v-btn>
      </v-form>
    </v-card-text>
  </v-card>
</template>
