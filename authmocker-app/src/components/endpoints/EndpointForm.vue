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
  <form @submit.prevent="handleSubmit" class="bg-gray-800 border border-gray-700 rounded-xl p-5 space-y-4">
    <div v-if="error" class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
      {{ error }}
    </div>

    <div class="grid grid-cols-4 gap-3">
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Method</label>
        <select
          v-model="method"
          class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        >
          <option v-for="m in methods" :key="m" :value="m">{{ m }}</option>
        </select>
      </div>
      <div class="col-span-2">
        <label class="block text-sm font-medium text-gray-300 mb-1">Path</label>
        <input
          v-model="path"
          type="text"
          required
          placeholder="users"
          class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Status</label>
        <input
          v-model.number="responseStatus"
          type="number"
          min="100"
          max="599"
          class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-300 mb-1">Response Body (JSON)</label>
      <textarea
        v-model="responseBody"
        rows="4"
        class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
      />
    </div>

    <div class="flex items-end gap-3">
      <div class="w-32">
        <label class="block text-sm font-medium text-gray-300 mb-1">Delay (ms)</label>
        <input
          v-model.number="delayMs"
          type="number"
          min="0"
          max="30000"
          class="w-full px-3 py-2.5 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>
      <button
        type="submit"
        :disabled="loading || !path"
        class="px-6 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 text-white font-medium rounded-lg transition-colors"
      >
        {{ loading ? 'Creating...' : 'Add Endpoint' }}
      </button>
    </div>
  </form>
</template>
