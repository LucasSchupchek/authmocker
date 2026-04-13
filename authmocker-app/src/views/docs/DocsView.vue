<script setup lang="ts">
</script>

<template>
  <div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-white mb-6">Documentation</h1>

    <div class="space-y-8">
      <!-- Getting Started -->
      <section class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-3">Getting Started</h2>
        <ol class="list-decimal list-inside text-gray-300 space-y-2 text-sm">
          <li>Create a new mock server by clicking <strong class="text-white">"New Server"</strong> in the sidebar.</li>
          <li>Choose an <strong class="text-white">authentication type</strong> (Basic Auth, API Key, JWT, OAuth2, or Session).</li>
          <li>Configure the authentication parameters as needed.</li>
          <li>Add <strong class="text-white">endpoints</strong> with custom responses.</li>
          <li>Use the generated mock URL in your client application.</li>
        </ol>
      </section>

      <!-- Auth Types -->
      <section class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Authentication Types</h2>

        <div class="space-y-4">
          <div>
            <h3 class="text-white font-medium">Basic Auth</h3>
            <p class="text-gray-400 text-sm mt-1">
              Send credentials via the <code class="text-indigo-400">Authorization: Basic base64(username:password)</code> header.
            </p>
          </div>

          <div>
            <h3 class="text-white font-medium">API Key</h3>
            <p class="text-gray-400 text-sm mt-1">
              Send your API key in a header, query parameter, or request body. Configure the location and key name.
            </p>
          </div>

          <div>
            <h3 class="text-white font-medium">JWT Bearer Token</h3>
            <p class="text-gray-400 text-sm mt-1">
              First, get a token from <code class="text-indigo-400">POST /mock/{'{slug}'}/token</code>.
              Then use it as <code class="text-indigo-400">Authorization: Bearer {'<token>'}</code>.
              Configurable secret, algorithm, expiration, and custom claims.
            </p>
          </div>

          <div>
            <h3 class="text-white font-medium">OAuth2</h3>
            <p class="text-gray-400 text-sm mt-1">
              Supports <strong class="text-white">Client Credentials</strong> and <strong class="text-white">Authorization Code</strong> flows.
              Use <code class="text-indigo-400">/authorize</code> and <code class="text-indigo-400">/token</code> endpoints.
            </p>
          </div>

          <div>
            <h3 class="text-white font-medium">Session / Cookie</h3>
            <p class="text-gray-400 text-sm mt-1">
              Login via <code class="text-indigo-400">POST /mock/{'{slug}'}/login</code> with username/password.
              A session cookie is returned. Include it in subsequent requests.
            </p>
          </div>
        </div>
      </section>

      <!-- API Docs -->
      <section class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-3">API Documentation</h2>
        <p class="text-gray-400 text-sm mb-3">
          Full interactive API documentation is available via Swagger UI:
        </p>
        <a
          :href="`${$route.query.apiUrl || 'http://localhost:8080'}/api/documentation`"
          target="_blank"
          class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 rounded-lg transition-colors text-sm"
        >
          Open Swagger UI
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
          </svg>
        </a>
      </section>

      <!-- Mock URL Structure -->
      <section class="bg-gray-800 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-3">URL Structure</h2>
        <div class="bg-gray-900 rounded-lg p-4 font-mono text-sm space-y-2">
          <p class="text-gray-400"># Your configured endpoints:</p>
          <p class="text-green-400">GET  /mock/{'{slug}'}/{'{endpoint_path}'}</p>
          <p class="text-blue-400">POST /mock/{'{slug}'}/{'{endpoint_path}'}</p>
          <p class="text-gray-400 mt-3"># Special auth endpoints:</p>
          <p class="text-purple-400">POST /mock/{'{slug}'}/token     <span class="text-gray-500"># JWT & OAuth2</span></p>
          <p class="text-purple-400">POST /mock/{'{slug}'}/authorize <span class="text-gray-500"># OAuth2 only</span></p>
          <p class="text-purple-400">POST /mock/{'{slug}'}/login     <span class="text-gray-500"># Session only</span></p>
        </div>
      </section>
    </div>
  </div>
</template>
