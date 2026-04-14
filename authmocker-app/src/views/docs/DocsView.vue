<script setup lang="ts">
</script>

<template>
  <div style="max-width: 900px;">
    <h1 class="text-h4 font-weight-bold mb-6">Documentation</h1>

    <v-card class="mb-6">
      <v-card-title>Getting Started</v-card-title>
      <v-card-text>
        <ol class="text-body-2" style="padding-left: 20px">
          <li class="mb-2">Create a new mock server by clicking <strong>"New Server"</strong> in the sidebar.</li>
          <li class="mb-2">Choose an <strong>authentication type</strong> (Basic Auth, API Key, JWT, OAuth2, or Session).</li>
          <li class="mb-2">Configure the authentication parameters as needed.</li>
          <li class="mb-2">Add <strong>endpoints</strong> with custom responses.</li>
          <li>Use the generated mock URL in your client application.</li>
        </ol>
      </v-card-text>
    </v-card>

    <v-card class="mb-6">
      <v-card-title>Authentication Types</v-card-title>
      <v-card-text>
        <v-expansion-panels variant="accordion">
          <v-expansion-panel title="Basic Auth">
            <v-expansion-panel-text>
              Send credentials via the <code class="text-primary">Authorization: Basic base64(username:password)</code> header.
            </v-expansion-panel-text>
          </v-expansion-panel>

          <v-expansion-panel title="API Key">
            <v-expansion-panel-text>
              Send your API key in a header, query parameter, or request body. Configure the location and key name.
            </v-expansion-panel-text>
          </v-expansion-panel>

          <v-expansion-panel title="JWT Bearer Token">
            <v-expansion-panel-text>
              First, get a token from <code class="text-primary">POST /mock/{'{slug}'}/token</code>.
              Then use it as <code class="text-primary">Authorization: Bearer &lt;token&gt;</code>.
              Configurable secret, algorithm, expiration, and custom claims.
            </v-expansion-panel-text>
          </v-expansion-panel>

          <v-expansion-panel title="OAuth2">
            <v-expansion-panel-text>
              Supports <strong>Client Credentials</strong> and <strong>Authorization Code</strong> flows.
              Use <code class="text-primary">/authorize</code> and <code class="text-primary">/token</code> endpoints.
            </v-expansion-panel-text>
          </v-expansion-panel>

          <v-expansion-panel title="Session / Cookie">
            <v-expansion-panel-text>
              Login via <code class="text-primary">POST /mock/{'{slug}'}/login</code> with username/password.
              A session cookie is returned. Include it in subsequent requests.
            </v-expansion-panel-text>
          </v-expansion-panel>
        </v-expansion-panels>
      </v-card-text>
    </v-card>

    <v-card class="mb-6">
      <v-card-title>API Documentation</v-card-title>
      <v-card-text>
        <p class="text-body-2 mb-4">
          Full interactive API documentation is available via Scramble:
        </p>
        <v-btn
          href="http://localhost:8080/docs/api"
          target="_blank"
          color="primary"
          variant="tonal"
          prepend-icon="mdi-open-in-new"
        >
          Open API Docs
        </v-btn>
      </v-card-text>
    </v-card>

    <v-card>
      <v-card-title>URL Structure</v-card-title>
      <v-card-text>
        <v-table density="compact">
          <thead>
            <tr>
              <th>Method</th>
              <th>Endpoint</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><v-chip color="success" size="small" label variant="tonal" class="font-weight-bold">GET</v-chip></td>
              <td><code>/mock/{'{slug}'}/{'{path}'}</code></td>
              <td>Your configured endpoints</td>
            </tr>
            <tr>
              <td><v-chip color="info" size="small" label variant="tonal" class="font-weight-bold">POST</v-chip></td>
              <td><code>/mock/{'{slug}'}/{'{path}'}</code></td>
              <td>Your configured endpoints</td>
            </tr>
            <tr>
              <td><v-chip color="purple" size="small" label variant="tonal" class="font-weight-bold">POST</v-chip></td>
              <td><code>/mock/{'{slug}'}/token</code></td>
              <td>Get access token (JWT &amp; OAuth2)</td>
            </tr>
            <tr>
              <td><v-chip color="purple" size="small" label variant="tonal" class="font-weight-bold">POST</v-chip></td>
              <td><code>/mock/{'{slug}'}/authorize</code></td>
              <td>Authorization endpoint (OAuth2 only)</td>
            </tr>
            <tr>
              <td><v-chip color="purple" size="small" label variant="tonal" class="font-weight-bold">POST</v-chip></td>
              <td><code>/mock/{'{slug}'}/login</code></td>
              <td>Session login (Session only)</td>
            </tr>
          </tbody>
        </v-table>
      </v-card-text>
    </v-card>
  </div>
</template>
