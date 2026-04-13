<script setup lang="ts">
const emit = defineEmits<{ (e: 'select', type: string): void }>()
defineProps<{ selected: string }>()

const authTypes = [
  { value: 'basic_auth', label: 'Basic Auth', icon: '🔑', description: 'Username & password via Authorization header' },
  { value: 'api_key', label: 'API Key', icon: '🗝️', description: 'Key validation in header, query or body' },
  { value: 'jwt', label: 'JWT Bearer', icon: '🎟️', description: 'JSON Web Tokens with configurable claims' },
  { value: 'oauth2', label: 'OAuth2', icon: '🔐', description: 'Authorization Code & Client Credentials flows' },
  { value: 'session', label: 'Session', icon: '🍪', description: 'Cookie-based session authentication' },
]
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <button
      v-for="type in authTypes"
      :key="type.value"
      @click="emit('select', type.value)"
      :class="[
        'p-4 rounded-xl border text-left transition-all',
        selected === type.value
          ? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500'
          : 'border-gray-700 bg-gray-800 hover:border-gray-600'
      ]"
    >
      <div class="text-2xl mb-2">{{ type.icon }}</div>
      <div class="text-white font-medium text-sm">{{ type.label }}</div>
      <div class="text-gray-400 text-xs mt-1">{{ type.description }}</div>
    </button>
  </div>
</template>
