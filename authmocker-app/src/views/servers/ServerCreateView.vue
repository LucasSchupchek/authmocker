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
  basic_auth: {},
  api_key: { location: 'header', header_name: 'X-API-Key' },
  jwt: { secret: '', algorithm: 'HS256', expiration_minutes: 60 },
  oauth2: { access_token_ttl: 3600, refresh_token_ttl: 86400 },
  session: { session_ttl_minutes: 120, cookie_name: 'mock_session' },
}

watch(authType, async (type) => {
  if (type) {
    try {
      const { data } = await api.get('/auth-types')
      const found = data.data.find((t: any) => t.value === type)
      config.value = found?.default_server_config || defaultConfigs[type] || {}
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
  <div style="max-width: 720px;">
    <div class="mb-8">
      <v-btn variant="text" to="/" prepend-icon="mdi-arrow-left" size="small" class="mb-2">
        Back to Dashboard
      </v-btn>
      <h1 class="text-h4 font-weight-bold">Create Mock Server</h1>
    </div>

    <v-form @submit.prevent="handleSubmit">
      <v-alert v-if="error" type="error" variant="tonal" closable class="mb-6" @click:close="error = ''">
        {{ error }}
      </v-alert>

      <!-- Auth Type -->
      <div class="mb-6">
        <div class="text-subtitle-2 font-weight-medium mb-3">Authentication Type</div>
        <AuthTypeSelector :selected="authType" @select="authType = $event" />
      </div>

      <!-- Name & Slug -->
      <v-row class="mb-2">
        <v-col cols="12" sm="6">
          <v-text-field
            v-model="name"
            label="Server Name"
            placeholder="My API Server"
            variant="outlined"
            density="comfortable"
            required
          />
        </v-col>
        <v-col cols="12" sm="6">
          <v-text-field
            v-model="slug"
            label="Slug (URL)"
            placeholder="my-api"
            variant="outlined"
            density="comfortable"
            required
          >
            <template #prepend-inner>
              <span class="text-medium-emphasis text-body-2">/mock/</span>
            </template>
          </v-text-field>
        </v-col>
      </v-row>

      <!-- Description -->
      <v-textarea
        v-model="description"
        label="Description (optional)"
        placeholder="What is this mock server for?"
        variant="outlined"
        density="comfortable"
        rows="2"
        no-resize
        class="mb-2"
      />

      <!-- Auth Config -->
      <v-card v-if="authType" variant="outlined" class="mb-6 pa-5">
        <div class="text-subtitle-2 font-weight-medium mb-3">Auth Configuration</div>
        <AuthConfigForm :auth-type="authType" v-model="config" />
      </v-card>

      <v-btn
        type="submit"
        color="primary"
        block
        size="large"
        :loading="loading"
        :disabled="!authType || !name || !slug"
      >
        Create Server
      </v-btn>
    </v-form>
  </div>
</template>
