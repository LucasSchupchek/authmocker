<script setup lang="ts">
import { computed, ref } from 'vue'
import type { MockServer } from '../../types'

const props = defineProps<{ server: MockServer }>()
const activeLanguage = ref<'curl' | 'javascript' | 'python'>('curl')
const copied = ref(false)

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
  navigator.clipboard.writeText(snippets.value[activeLanguage.value])
  copied.value = true
  setTimeout(() => { copied.value = false }, 2000)
}
</script>

<template>
  <div>
    <h2 class="text-lg font-semibold text-white mb-4">Usage Examples</h2>

    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
      <div class="flex items-center justify-between px-4 py-2 border-b border-gray-700">
        <div class="flex gap-1">
          <button
            v-for="lang in (['curl', 'javascript', 'python'] as const)"
            :key="lang"
            @click="activeLanguage = lang"
            :class="[
              'px-3 py-1 rounded text-xs font-medium transition-colors capitalize',
              activeLanguage === lang ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white'
            ]"
          >
            {{ lang }}
          </button>
        </div>
        <button
          @click="copyToClipboard"
          class="text-gray-400 hover:text-white text-xs transition-colors"
        >
          {{ copied ? 'Copied!' : 'Copy' }}
        </button>
      </div>
      <pre class="p-4 text-sm text-gray-300 overflow-x-auto font-mono"><code>{{ snippets[activeLanguage] }}</code></pre>
    </div>

    <div class="mt-4 p-4 bg-gray-800 border border-gray-700 rounded-xl">
      <h3 class="text-sm font-medium text-white mb-2">Mock Server URL</h3>
      <code class="text-indigo-400 text-sm">{{ server.mock_url }}</code>

      <h3 class="text-sm font-medium text-white mt-4 mb-2">Special Endpoints</h3>
      <ul class="text-sm text-gray-400 space-y-1">
        <li v-if="server.auth_type === 'jwt' || server.auth_type === 'oauth2'">
          <code class="text-indigo-400">POST {{ server.mock_url }}/token</code> - Get access token
        </li>
        <li v-if="server.auth_type === 'oauth2'">
          <code class="text-indigo-400">POST {{ server.mock_url }}/authorize</code> - OAuth2 authorize
        </li>
        <li v-if="server.auth_type === 'session'">
          <code class="text-indigo-400">POST {{ server.mock_url }}/login</code> - Session login
        </li>
      </ul>
    </div>
  </div>
</template>
