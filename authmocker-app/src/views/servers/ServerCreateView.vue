<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useServersStore } from '../../stores/servers'
import AuthTypeSelector from '../../components/servers/AuthTypeSelector.vue'
import AuthConfigForm from '../../components/servers/AuthConfigForm.vue'
import api from '../../services/api'

const router = useRouter()
const store = useServersStore()

const name = ref('')
const slug = ref('')
const authType = ref('')
const config = ref<Record<string, any>>({})
const description = ref('')
const error = ref('')
const loading = ref(false)

const defaultConfigs: Record<string, Record<string, any>> = {
  basic_auth: { username: 'admin', password: 'password' },
  api_key: { key: '', location: 'header', header_name: 'X-API-Key' },
  jwt: { secret: '', algorithm: 'HS256', expiration_minutes: 60 },
  oauth2: { client_id: '', client_secret: '', redirect_uri: 'http://localhost:3000/callback', access_token_ttl: 3600, refresh_token_ttl: 86400 },
  session: { username: 'admin', password: 'password', session_ttl_minutes: 120, cookie_name: 'mock_session' },
}

watch(authType, async (type) => {
  if (type) {
    try {
      const { data } = await api.get('/auth-types')
      const found = data.data.find((t: any) => t.value === type)
      config.value = found?.default_config || defaultConfigs[type] || {}
    } catch {
      config.value = defaultConfigs[type] || {}
    }
  }
})

watch(name, (val) => {
  slug.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
})

async function handleSubmit() {
  error.value = ''
  loading.value = true
  try {
    const server = await store.createServer({
      name: name.value,
      slug: slug.value,
      auth_type: authType.value,
      config: config.value,
      description: description.value || undefined,
    })
    router.push(`/servers/${server.id}`)
  } catch (e: any) {
    error.value = e.response?.data?.message || e.message || 'Failed to create server'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl">
    <div class="mb-8">
      <RouterLink to="/" class="text-gray-400 hover:text-white text-sm transition-colors">
        &larr; Back to Dashboard
      </RouterLink>
      <h1 class="text-2xl font-bold text-white mt-2">Create Mock Server</h1>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <div v-if="error" class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
        {{ error }}
      </div>

      <!-- Auth Type -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-3">Authentication Type</label>
        <AuthTypeSelector :selected="authType" @select="authType = $event" />
      </div>

      <!-- Name & Slug -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">Server Name</label>
          <input
            v-model="name"
            type="text"
            required
            class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            placeholder="My API Server"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1">Slug (URL)</label>
          <div class="flex">
            <span class="px-3 py-2.5 bg-gray-700 border border-r-0 border-gray-600 rounded-l-lg text-gray-400 text-sm">/mock/</span>
            <input
              v-model="slug"
              type="text"
              required
              class="flex-1 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-r-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="my-api"
            />
          </div>
        </div>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-gray-300 mb-1">Description (optional)</label>
        <textarea
          v-model="description"
          rows="2"
          class="w-full px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
          placeholder="What is this mock server for?"
        />
      </div>

      <!-- Auth Config -->
      <div v-if="authType">
        <label class="block text-sm font-medium text-gray-300 mb-3">Auth Configuration</label>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-5">
          <AuthConfigForm :auth-type="authType" v-model="config" />
        </div>
      </div>

      <button
        type="submit"
        :disabled="loading || !authType || !name || !slug"
        class="w-full px-4 py-2.5 bg-indigo-500 hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors"
      >
        {{ loading ? 'Creating...' : 'Create Server' }}
      </button>
    </form>
  </div>
</template>
