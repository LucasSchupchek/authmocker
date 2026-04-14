<script setup lang="ts">
import { computed, ref } from 'vue'
import type { MockServer } from '../../types'

const props = defineProps<{ server: MockServer }>()
const activeLanguage = ref('curl')
const copied = ref(false)

const languages = [
  { value: 'curl', title: 'cURL' },
  { value: 'javascript', title: 'JavaScript' },
  { value: 'python', title: 'Python' },
]

const snippets = computed(() => {
  const url = props.server.mock_url
  const config = props.server.config
  const type = props.server.auth_type

  const curlAuth = (() => {
    switch (type) {
      case 'basic_auth':
        return `-u "${config.username}:${config.password}"`
      case 'api_key':
        if (config.location === 'header') return `-H "${config.header_name}: ${config.key}"`
        if (config.location === 'query') return `"${url}/users?api_key=${config.key}"  # (query param)`
        return `-d '{"api_key": "${config.key}"}'`
      case 'jwt':
        return `-H "Authorization: Bearer <token>"\n# Get token first:\n# curl -X POST ${url}/token`
      case 'oauth2':
        return `-H "Authorization: Bearer <access_token>"\n# Get token:\n# curl -X POST ${url}/token -d "grant_type=client_credentials&client_id=${config.client_id}&client_secret=${config.client_secret}"`
      case 'session':
        return `-b "mock_session=<session_token>"\n# Login first:\n# curl -X POST ${url}/login -d '{"username":"${config.username}","password":"${config.password}"}'`
      default:
        return ''
    }
  })()

  const jsAuth = (() => {
    switch (type) {
      case 'basic_auth':
        return `headers: { 'Authorization': 'Basic ' + btoa('${config.username}:${config.password}') }`
      case 'api_key':
        return `headers: { '${config.header_name || 'X-API-Key'}': '${config.key}' }`
      case 'jwt':
        return `// First get a token from POST ${url}/token\nheaders: { 'Authorization': 'Bearer ' + token }`
      case 'oauth2':
        return `// First get a token from POST ${url}/token\nheaders: { 'Authorization': 'Bearer ' + accessToken }`
      case 'session':
        return `// First login via POST ${url}/login\nheaders: { 'X-Session-Token': sessionToken }`
      default:
        return ''
    }
  })()

  return {
    curl: `curl -X GET ${url}/users \\\n  ${curlAuth}`,
    javascript: `const response = await fetch('${url}/users', {\n  ${jsAuth}\n})\nconst data = await response.json()`,
    python: `import requests\n\nresponse = requests.get('${url}/users',\n  ${type === 'basic_auth' ? `auth=('${config.username}', '${config.password}')` : `headers={'Authorization': 'Bearer <token>'}`}\n)\ndata = response.json()`,
  }
})

function copyToClipboard() {
  navigator.clipboard.writeText(snippets.value[activeLanguage.value as keyof typeof snippets.value])
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}
</script>

<template>
  <div>
    <h2 class="text-h6 mb-4">Usage Examples</h2>

    <v-card>
      <v-card-text class="pa-0">
        <div class="d-flex align-center justify-space-between px-4 py-2">
          <v-tabs v-model="activeLanguage" density="compact">
            <v-tab v-for="lang in languages" :key="lang.value" :value="lang.value" size="small">
              {{ lang.title }}
            </v-tab>
          </v-tabs>
          <v-btn
            variant="text"
            size="small"
            :prepend-icon="copied ? 'mdi-check' : 'mdi-content-copy'"
            @click="copyToClipboard"
          >
            {{ copied ? 'Copied!' : 'Copy' }}
          </v-btn>
        </div>
        <v-divider />
        <pre class="pa-4 text-body-2 overflow-x-auto" style="font-family: monospace; margin: 0"><code>{{ snippets[activeLanguage as keyof typeof snippets] }}</code></pre>
      </v-card-text>
    </v-card>

    <v-card class="mt-4">
      <v-card-text>
        <h3 class="text-subtitle-2 font-weight-medium mb-2">Mock Server URL</h3>
        <code class="text-primary">{{ server.mock_url }}</code>

        <h3 class="text-subtitle-2 font-weight-medium mt-4 mb-2">Special Endpoints</h3>
        <v-list density="compact" class="pa-0">
          <v-list-item v-if="server.auth_type === 'jwt' || server.auth_type === 'oauth2'" density="compact">
            <code class="text-primary">POST {{ server.mock_url }}/token</code>
            <span class="text-medium-emphasis ml-2">- Get access token</span>
          </v-list-item>
          <v-list-item v-if="server.auth_type === 'oauth2'" density="compact">
            <code class="text-primary">POST {{ server.mock_url }}/authorize</code>
            <span class="text-medium-emphasis ml-2">- OAuth2 authorize</span>
          </v-list-item>
          <v-list-item v-if="server.auth_type === 'session'" density="compact">
            <code class="text-primary">POST {{ server.mock_url }}/login</code>
            <span class="text-medium-emphasis ml-2">- Session login</span>
          </v-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </div>
</template>
